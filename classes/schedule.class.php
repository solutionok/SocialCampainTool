<?php
/**
 * Schedule class
 * Processes schedules
 *
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

require_once dirname(dirname(__FILE__))."/sdk/facebook/facebook.php";
require_once dirname(dirname(__FILE__))."/sdk/twitter/twitter.php";
require_once dirname(dirname(__FILE__))."/sdk/youtube/youtube.php";

class schedule
{
	public $schedule_id;
	public $data;
	public $error;
	
	public $table;
	public $col;
	public $access_token;
	public $response;
	
	public $post_id;
	public $object_id;
	
	public $file;
	public $file_orig_name;
	public $file_id;
	public $file_type;
	public $status;
	public $file_link;
	public $desc;
	
	public $to_delete;
	
	public $log_file;
	public $folder_type;
	public $raw_folder_id;
	
	public $tag_me;
	
	public $start_time;
	public $next_post_time_selected;
	
	public function __construct($schedule_id)
	{
		$this->next_post_time_selected = 0;
		$this->start_time = time();
		$this->log_file = dirname(dirname(__FILE__)).'/logs/log.txt';
		$this->to_delete = array();
		
		if(!empty($schedule_id)){
			$this->schedule_id = $schedule_id;
			
			$sql = "SELECT 
					schedules.schedule_id, schedules.social_id, schedules.page_id, schedules.site, schedules.comment_bumping_freq, schedules.comment_bumps, schedules.bump_type, schedules.notes,
					schedule_groups.* , users.*, post_counter.post_count, membership_plans.*, TIMESTAMPDIFF(SECOND, NOW(), post_end_at) AS post_end_in
					FROM schedules 
					LEFT JOIN schedule_groups ON schedule_groups.schedule_group_id = schedules.schedule_group_id AND schedule_groups.is_active = 1
					LEFT JOIN users ON users.user_id = schedules.user_id AND users.account_status = 1
					LEFT JOIN membership_plans ON membership_plans.plan_id = users.plan_id
					LEFT JOIN post_counter ON post_counter.user_id = schedules.user_id AND post_counter.today = DATE(NOW()) AND post_counter.site = ''
					WHERE schedules.is_done = 0 AND schedules.is_active = 1 AND schedules.rate_limited = 0 AND schedules.is_locked = 0 AND schedules.schedule_id = '$schedule_id'
					AND schedule_groups.schedule_group_id IS NOT NULL 
					AND users.user_id IS NOT NULL";
					
			$data = sql_fetch_assoc(sql_query($sql));
			
			if(empty($data)){
				//maybe locked
				$this->error = 'Unable to fetch schedule data';
				$this->log($this->error);
				return false;	
			}
			
			/**
			 * check schedule end_at timings
			 */
			if($data['post_end_in'] <= 0 && $data['post_end_at'] != '0000-00-00' && $data['post_end_at'] != '0000-00-00 00:00:00'){
				sql_query("UPDATE schedules SET is_done = 1, next_post = '0000-00-00 00:00:00' WHERE schedule_group_id = '".$data['schedule_group_id']."'");
				sql_query("UPDATE schedule_groups SET is_done = 1, next_post = '0000-00-00 00:00:00' WHERE schedule_group_id = '".$data['schedule_group_id']."'");	
				return false;
			}
			
			$this->data = $data;
			$this->init();
		}
	}
	
	public function init()
	{
		global $settings;
		
		/**
		 * Load user app settings
		 */
		load_app_settings($this->data['user_id']);
		
		/**
		 * Check if user exceeded todays post limit
		 */
		if(!$this->check_post_counter())return false;
		
		/**
		 * Lock schedule so no other script processes it
		 */
		$this->lock_schedule();
		
		/**
		 * Load access token
		 */
		$this->load_access_token();
		if(empty($this->access_token)){
			$this->set_next_schedule_time();
			$this->unlock_schedule();
			return true;
		}
		
		$folder = explode(':', $this->data['folder_id']);
		$folder_id = $folder[1];
		$this->raw_folder_id = $folder_id;
		
		if($this->data['post_sequence'] == 'album'){
			$this->log('Processing album...');
			if(!in_array($this->data['site'], array('fbprofile', 'fbgroup', 'fbpage', 'twitter')) || $folder[0] != 'FOLDER'){
				$this->log('Post sequence album not applicable to selected site');
				$this->mark_done(1, 'Invalid post sequence for album');
				return false;	
			}
			
			if(!$this->data['use_album_post']){
				$this->log('Album post not supported for this membership');
				$this->mark_done(1, 'Album post not supported for your membership');
				return false;	
			}
			
			$files = array();
			$folder_data = sql_fetch_assoc(sql_query("SELECT * FROM folders WHERE folder_id = '$this->raw_folder_id'"));
			$q = sql_query("SELECT filename, caption FROM files WHERE folder_id = '$this->raw_folder_id' AND file_type = 'image' ".($this->data['site'] == 'twitter' ? " LIMIT 4 " : " LIMIT 100 "));
			
			/**
			 * Set temp file_type to image to help with watermarking
			 */
			$this->file_type = 'image';
			
			while($res = sql_fetch_assoc($q)){
				$this->file_link = __ROOT__.'/storage/'.$this->data['storage'].'/'.$res['filename'];	
				if($this->data['watermark']){
					$this->file_link = $this->add_watermark();
				}
				$files[] = array('link' => $this->file_link, 'caption' => $res['caption']);
			}
			
			if(empty($files)){
				$this->log("No file found for album making");
				$this->mark_done(1, 'No file found to post album');
				return false;	
			}
			
			$this->file_link = $files;
			$this->status = $folder_data['folder_name'];
			$this->file_type = 'album';
			$this->file_id = 'album';
		}
		
		/**
		 * Slideshow creation
		 */
		else if(preg_match('/^slideshow/', $this->data['post_sequence'])){
			$this->log('Processing slideshow...');
			$slide = explode('|', $this->data['post_sequence']);
			$slide_duration = (int)$slide[1];
			$slide_type = $slide[2];
			
			if(!$this->data['use_slideshow']){
				$this->log('Slideshow not supported for this membership');
				$this->mark_done(1, 'Slideshow not supported for your membership');
				return false;	
			}
			
			if(empty($slide_type)){
				$ss = get_available_slideshow_type(-1);
				$ss = shuffle_assoc($ss);
				$slide_type = key($ss);	
			}
			
			if(!in_array($this->data['site'], array('fbprofile', 'fbgroup', 'fbpage', 'fbevent', 'youtube')) || $folder[0] != 'FOLDER' || !$slide_duration || empty($settings['ffmpeg']) || empty($settings['video_editor_enabled']) || empty($settings['media_plugin_enabled'])){
				$this->log('Post sequence slideshow not applicable to selected site or disabled');
				$this->mark_done(1, 'Invalid post sequence for slideshow');
				return false;	
			}
			
			$files = array();
			$folder_data = sql_fetch_assoc(sql_query("SELECT * FROM folders WHERE folder_id = '$this->raw_folder_id'"));
			$q = sql_query("SELECT filename FROM files WHERE folder_id = '$this->raw_folder_id' AND file_type = 'image' LIMIT 200");
			while($res = sql_fetch_assoc($q)){
				$files[] = __ROOT__.'/storage/'.$this->data['storage'].'/'.$res['filename'];	
			}
			
			if(empty($files)){
				$this->log("No file found for sildeshow making");
				$this->mark_done(1, 'No file found for slideshow making');
				return false;	
			}
			
			$video = $this->create_slideshow($files, $slide_duration, $slide_type);
			
			/**
			 * Reconnect to mysql in case connection is dropped after a delay
			 */
			sql_conn();
			
			if(empty($video)){
				$this->log('Failed to create slideshow');
				/**
				 * limiting schedule because it may be a result from lock timeout etc..
				 */
				$this->set_next_schedule_time(); 
				$this->limit_schedule('Slideshow creation failed');
				return false;	
			}
			
			$this->file_link = $video;
			$this->status = $folder_data['folder_name'];
			$this->file_type = 'video';
			$this->file_id = 'slideshow';
		}
		
		/**
		 * Choose file or rss
		 */
		else{
			$this->log('Processing file selection...');
			$this->choose_file_rss();
			if(empty($this->file_id)){
				$this->set_next_schedule_time();
				$this->unlock_schedule();
				return true;
			}
		}
		
		/**
		 * Add watermark if required
		 */
		if($this->data['watermark'] && ($this->file_type == 'image' || $this->file_type == 'video')){
			$this->file_link = $this->add_watermark();
			
			/**
			 * Reconnect to mysql in case connection is dropped after a delay
			 */
			sql_conn();
		}
		
		/**
		 * Create post
		 */
		$this->do_post();
		$response = json_decode($this->response, true);
		
		/**
		 * Reconnect to mysql in case connection is dropped after a delay
		 */
		sql_conn();
		
		/**
		 * If post id found
		 */
		if(!empty($response['id'])){
			$this->log('Successfully posted...');
			
			if($this->data['site'] == 'twitter'){
				$this->post_id = $this->object_id = $response['id_str'];	
			}
			else if(($this->data['site'] == 'fbgroup' || $this->data['site'] == 'fbevent') && !empty($response['post_id'])){
				$this->post_id = $this->object_id = $this->data['page_id'].'_'.end(explode('_', $response['post_id']));	
			}
			else if(($this->data['site'] == 'fbgroup' || $this->data['site'] == 'fbevent') && ($this->file_type == 'video' || $this->file_type == 'album')){
				$url = 'https://graph.facebook.com/'.$this->data['page_id'].'/feed?fields=object_id&access_token='.$this->access_token;
				$ddd = curl_single($url);
				
				if(empty($this->object_id))$obj = $response['id'];
				else $obj = $this->object_id;
				
				$data = json_decode($ddd, true);
				foreach($data['data'] as $p){
					if($p['object_id'] == $obj){
						$this->post_id = $this->object_id = $p['id'];
						break;	
					}
				}	
			}
			
			if(empty($this->post_id)){
				$this->post_id = $response['id'];
				$this->object_id = $response['id'];
			}
			
			/**
			 * workaround of facebook group photo no caption bug
			 */
			if($this->data['site'] == 'fbgroup' && $this->file_type == 'image' && !empty($this->status)){
				$url = 'https://graph.facebook.com/'.$this->post_id.'/?access_token='.$this->access_token;
				$post = array('message' => $this->status);		
				$ddd = curl_single($url, $post);		
			}
			
			$this->insert_post_log();
		}		
		/**
		 * Otherwise post failed
		 */
		else{
			$this->log('Failed to create post');
			$this->log($this->response);
			/**
			 * Check errors : token expiry|blocks etc
			 */
			$this->check_errors();
			$this->post_id = '';
			$this->object_id = '';
			//$this->insert_post_log(1);
			$this->insert_error_post_log( 0, 'Failed to create post' );
		}
		
		/**
		 * Set next schedule time
		 */
		$this->set_next_schedule_time();
		
		/**
		 * slideshow and albums are posted only once
		 */
		if($this->file_id == 'album' || $this->file_id == 'slideshow'){
			$this->mark_done();	
		}
		
		/**
		 * Cleanup if any file needs to be deleted
		 */
		$this->cleanup();
		
		/**
		 * check again if any file left for post
		 */
		if($this->folder_type == 'FOLDER')$this->choose_file_rss();
		
		/**
		 * Unlock this schedule
		 */
		$this->unlock_schedule();
	}
	
	public function lock_schedule()
	{
		sql_query("UPDATE schedules SET is_locked = 1, locked_at = NOW() WHERE schedule_id = '$this->schedule_id'");
	}
	
	public function unlock_schedule()
	{
		sql_query("UPDATE schedules SET is_locked = 0 WHERE schedule_id = '$this->schedule_id'");
	}
	
	public function limit_schedule($reason, $next_day = 0)
	{
		sql_query("UPDATE schedules SET notes = '$reason' WHERE schedule_id = '$this->schedule_id'");
		$this->insert_error_post_log( 0, $reason );
		$this->set_next_schedule_time();
	}
	
	public function check_post_counter()
	{
		if(!empty($this->data['post_count'])){
			if($this->data['post_count'] >= $this->data['post_per_day'] && $this->data['post_per_day']){
				$this->limit_schedule('Post limit exceeded', 1);
				$this->log('User has exceeded post limits...');
				return false;	
			}	
		}
		
		$site = $this->get_post_counter_site();
		list($posted) = sql_fetch_row(sql_query("SELECT post_count FROM post_counter WHERE user_id = '".$this->data['user_id']."' AND post_counter.today = DATE(NOW()) AND post_counter.site = '$site' LIMIT 1"));
		
		$posted = (int)$posted;		
		$s = $site;
		if( preg_match('/fb/siU', $site ) ) $s = 'facebook';
		if($posted >= $this->data[$s.'_post_per_day']){
			$this->limit_schedule($site.' post limit exceeded', 1);
			$this->log('User has exceeded '.$site.' post limits...');
			return false;	
		}		
		
		$this->log('User has not exceeded post limits... ');
		return true;
	}
	
	public function get_post_counter_site()
	{
		$site = $this->data['site'];
		if($site == 'fbprofile' || $site == 'fbpage' || $site == 'fbevent' || $site == 'fbgroup')$site = 'facebook';
		return $site;
	}
	
	public function load_access_token()
	{
		$this->log('Loading access token...');
		list($table, $col, $uname_col, $name_col, $sid_col) = get_site_params($this->data['site']);
		$this->table = $table;
		$this->col = $col;
		list($this->access_token, $uname, $name) = sql_fetch_row(sql_query("SELECT access_token, $uname_col, $name_col FROM $table WHERE $col = '".$this->data['page_id']."' AND user_id = '".$this->data['user_id']."' AND account_status = 1 AND $sid_col = '".$this->data['social_id']."'"));
		if(empty($this->access_token)){
			$this->log("Failed to load access token");
			$this->limit_schedule('Token Failure');
			return false;	
		}
		
		if(!is_numeric($uname))$this->data['page_name'] = $uname;
		else $this->data['page_name'] = $name;
		
		/**
		 * load owner name
		 */
		if(preg_match('/^fb/i', $this->data['site'])){
			list($this->data['first_name'], $this->data['last_name']) = sql_fetch_row(sql_query("SELECT first_name, last_name FROM fb_accounts WHERE fb_id = '".$this->data['social_id']."' AND user_id = '".$this->data['user_id']."'"));	
		}
		else{
			list($this->data['first_name'], $this->data['last_name']) = sql_fetch_row(sql_query("SELECT first_name, last_name FROM $table WHERE $col = '".$this->data['social_id']."' AND user_id = '".$this->data['user_id']."'"));	
		}
		
		$this->log('Access token loaded...');
		return true;
	}
	
	public function create_slideshow($files, $slide_duration, $slide_type)
	{
		$out = dirname(dirname(__FILE__)).'/tmp/'.$this->raw_folder_id.'_'.$this->data['schedule_group_id'].'_slideshow.mp4';
		$lock = $out.'.lock';
		$running = check_running_file($lock);
		$t = time();
		do{
			$running = check_running_file($lock);
			sleep(1);
			/**
			 * Allow 30 minutes
			 */
			if(time() - $t > 1800){
				$this->log('Slideshow wait timeout...');
				return false;
			}
		}while($running);
		
		if(file_exists($out) && filesize($out) > 100 && !file_exists($lock)){
			$this->log('Using cached slideshow...');
			return $out;
		}
		$flock = lock_file($lock);
					
		$f = new ffmpeg('slideshow');
		$video = $f->create_slideshow($files, $slide_duration, $slide_type);
		
		if(!$video){
			/**
			 * Reconnect to mysql in case connection is dropped after a delay
			 */
			sql_conn();
			
			$this->log("Failed to create slideshow");
			$this->set_next_schedule_time();
			$this->limit_schedule('Slideshow creation failed');
			unlock_process($flock);
			@unlink($lock);
			rrmdir($f->ot_dir);
			return false;	
		}
		
		/**
		 * Copy video to tmp folder
		 */
		$this->log("New slideshow created");
		rename($video, $out);
		unlock_process($flock);
		@unlink($lock);
		rrmdir($f->ot_dir);
		return $out;			
	}
	
	public function choose_file_rss()
	{
		$this->log("Choosing file/rss...");
		$folder = explode(':', $this->data['folder_id']);
		$folder_id = $folder[1];
		
		if($folder[0] == 'FOLDER'){
			$this->folder_type = 'FOLDER';
			
			$filter = ' 1 ';
			$sort = '';
			/**
			 * No video for twitter
			 */
			if($this->data['site'] == 'twitter'){
				$filter .= " AND files.file_type != 'video' ";			
			}
			/**
			 * Only video for youtube
			 */
			else if($this->data['site'] == 'youtube'){
				$filter .= " AND files.file_type = 'video' ";
			}
			
			if(preg_match('/\|/', $this->data['post_sequence'])){
				$p = preg_replace('/[^image|text|video|\|]/', '', $this->data['post_sequence']);
				if(!empty($p)){
					$p = "'".str_replace('|', "','", $p)."'";
					$sort .= " ORDER BY field(`file_type`, $p) LIMIT 1 ";	
				}
			}
			else if($this->data['post_sequence'] == 'random'){
				$sort .= " LIMIT 100 ";
			}
			else if($this->data['post_sequence'] == 'ordered'){
				$sort .= " ORDER BY position ASC LIMIT 1 ";
			}
			
			/**
			 * find files that were not posted yet
			 */
			$where =  " post_log.schedule_id = '$this->schedule_id' ";
			if(!empty($this->data['onetime_post']))$where =  " post_log.schedule_group_id = '".$this->data['schedule_group_id']."' ";
			
			$this->log("Choosing a file...");
			$sql = "SELECT files.* FROM files 
					LEFT JOIN post_log ON post_log.file_id = files.file_id 
						AND post_log.folder_id = 'FOLDER:$folder_id' AND $where AND post_log.is_blind = 0
					WHERE files.folder_id = '$folder_id' AND
					post_log.post_log_id IS NULL AND
					$filter
					$sort";
			
			$s = microtime(true);
			$q = sql_query($sql);		
			$total = sql_num_rows($q);
			$this->log("Total $total files found to post in ".(microtime(true) - $s).' seconds');
			/**
			 * if no file found, check if repeat schedule is enabled? If yes clear log and reset schedules
			 */
			if(!$total){
				if($this->data['do_repeat']){
					$this->log("Resetting schedule for repost...");
					sql_query("UPDATE post_log SET is_blind = 1 WHERE schedule_id = '$this->schedule_id'");	
					
					/**
					 * Fetch again
					 */ 
					$s = microtime(true);
					$q = sql_query($sql);		
					$total = sql_num_rows($q);
					$this->log("Total $total files found to post in ".(microtime(true) - $s).' seconds');
				}
				/**
				 * Mark schedule as completed if no file to post
				 */
				if(!$total){
					$this->error = 'Schedule completed';
					$this->mark_done(0, $this->data['notes']);
					return true;
				}	
			}
			
			if($this->data['post_sequence'] == 'random'){
				$rand = rand(0, $total - 1);
				sql_data_seek($q, $rand);	
			}
			
			$s = microtime(true);
			$file = sql_fetch_assoc($q);
			if(empty($file)){
				$this->error = 'File fetch error';
				$this->log($this->error);
				return false;	
			}
			
			$this->log("File #".$file['file_id']." fetched in ".(microtime(true) - $s).' seconds');
			
			$this->file_orig_name = $file['original_name'];
			$this->file_id = $file['file_id'];
			$this->status = $file['caption'];
			$this->file_link = '';
			$this->file_type = $file['file_type'];
			$this->file = $file;
			
			/**
			 * Spintax support
			 */
			$spintax = new spintax();
			$this->status = $this->replace_variables($this->status);
			$this->status = $spintax->process($this->status);
			
			if($this->file_type != 'text')$this->file_link = __STORAGE__.'/'.$this->data['storage'].'/'.$file['filename'];
		}
		else{
			$this->folder_type = 'RSS';
			sql_query("UPDATE schedules SET notes = '' WHERE schedule_id = '".$this->data['schedule_id']."'");
			
			$feed_details = sql_fetch_assoc(sql_query("SELECT * FROM rss_feeds WHERE rss_feed_id = '$folder_id'"));	
			$feed = new xml_feed( htmlspecialchars_decode( $feed_details['rss_url'] ) );
			if(!empty($feed->error) || empty($feed->posts))$this->limit_schedule('No rss feed found');
			else{
				$posts = $feed->posts;
				if($this->data['post_sequence'] == 'random')shuffle($posts);
				$this->log(count($posts).' feeds found in rss link');
				
				$done = 0;
				foreach($posts as $post){
					$id = md5($post['link']);
					if(sql_num_rows(sql_query(
						"SELECT NULL FROM post_log WHERE folder_id = 'RSS:".$folder_id."' AND file_id = '$id' AND schedule_id = '$this->schedule_id' AND post_log.is_blind = 0")))continue;
					
					$this->log('New rss feed selected');
					$done = 1;	
				
					$this->file_id = $id;
					$this->file_type = 'link';
					$this->status = $post['title'];
					if(empty($this->status))$this->status = $post['summary'];
					$this->file_link = $post['link'];
					$this->desc = strip_tags($post['desc']);
					
					$this->file = array();
					if( !empty($post['image']))$this->file['feed_image'] = $post['image'];
					
					if($this->data['site'] == 'twitter'){
						$this->status = $feed->summarizeText($this->status).' '.$this->file_link;
					}
					
					break;	
				}
				
				if(!$done){
					if($this->data['do_repeat']){
						$this->log('Schedule is selected to be repeated...');
						sql_query("UPDATE post_log SET is_blind = 1 WHERE schedule_id = '$this->schedule_id'");	
					}
					else{
						$this->log("No new rss feed found to post");
						sql_query("UPDATE schedules SET notes = 'No new rss feed found' WHERE schedule_id = '".$this->data['schedule_id']."'");
						//$this->limit_schedule('No new rss feed found');	
					}
				}
			}
		}
	}
	
	public function do_post()
	{
		global $settings, $auth;
		$this->error = '';
		
		$spintax = new spintax();
		
		/**
		 * Fetch video file meta
		 */
		$file_meta = array();
		if($this->file_type == 'video'){
			$file_meta = $auth->get_user_file_meta($this->data['user_id'], $this->file_id);
			if(empty($file_meta))$file_meta = $auth->get_user_file_meta($this->data['user_id'], 0);	
		}
		/**
		 * Process albums
		 */
		else if($this->file_type == 'album'){
			if($this->data['site'] == 'twitter'){
				
				$token = explode(':::', $this->access_token);
				$tw = new TwitterOAuth($settings['tw_app_id'], $settings['tw_app_secret'], $token[0], $token[1]);
				$tw->decode_json = false;
			
				/**
				 * Upload all images first
				 */
				$images = array();
				foreach($this->file_link as $file){
					$post = array('media_data' => base64_encode(file_get_contents($file['link'])));
					$this->response = $tw->upload('https://upload.twitter.com/1.1/media/upload.json', $post);
					$d = json_decode($this->response, true);
					
					if(!empty($d['media_id_string'])){
						$this->log("Image successfully uploaded ".$d['media_id_string']);
						$images[] = $d['media_id_string'];
					}
					else $this->log('Failed to upload image '.$this->response);
				}
				
				/**
				 * Now post album
				 */
				if(!empty($images)){
					$post = array('status' => /*substr(*/ $this->decode_html_text($this->status)/*, 0, 139 )*/, 'media_ids' => implode(',', $images));
					$this->log('Posting album...');
					$this->response = $tw->post('statuses/update', $post);
				}
				else{
					$this->log('No image found to upload');
				}
			}
			else{
				$url = 'https://graph.facebook.com/'.$this->data['page_id'].'/albums?access_token='.$this->access_token;
				$post = array(
						'message' => $this->decode_html_text($this->status),
						'name' => $this->decode_html_text($this->status),
						'access_token' => $this->access_token
						);
				
				$this->response = curl_single($url, $post);
				
				$data = json_decode($this->response, true);
				if(empty($data['id'])){
					$this->log('Failed to create album');
					$this->log($this->response);
					return true;
				}
				
				/**
				 * Now post photos to this album
				 */
				$k = 0;
				$this->log('Album successfully created '.$data['id']);
				foreach($this->file_link as $file){
					$file['caption'] = $spintax->process($this->replace_variables($file['caption']));
					$url = 'https://graph.facebook.com/'.$data['id'].'/photos?access_token='.$this->access_token;
					$post = array(
							'message' => $this->decode_html_text($file['caption']),
							'access_token' => $this->access_token,
							'source' => '@'.$file['link']
							);
					$d = curl_single($url, $post);	
					
					$d = json_decode($d, true);
					
					if(empty($d['id'])){
						$this->log('Failed to post photo '.basename($file['link']).' to album');
					}
					else{
						$this->log('Photo '.basename($file['link']).'('.$d['id'].') successfully posted to album');
						if(!$k)$this->object_id = $d['id'];
						$k++;
					}
				} 
			}
			return true;
		}
		
		
		if($this->data['site'] == 'twitter'){
		
			if(!empty($this->tag_me))$status = '@'.$this->data['page_name'].' '.$this->status;
			else $status = $this->status;
			
			$post = array('status' => /*substr(*/ $this->decode_html_text($status)/*, 0, 139 )*/);
			
			$token = explode(':::', $this->access_token);
			$tw = new TwitterOAuth($settings['tw_app_id'], $settings['tw_app_secret'], $token[0], $token[1]);
			$tw->decode_json = false;
			if($this->file_type == 'image'){
				
				/**
				 * Upload image first
				 */
				$post = array('media_data' => base64_encode(file_get_contents($this->file_link)));
				$this->response = $tw->upload('https://upload.twitter.com/1.1/media/upload.json', $post);
				$d = json_decode($this->response, true);
				
				if(!empty($d['media_id_string'])){
					$this->log("Image successfully uploaded ".$d['media_id_string']);
					$images = $d['media_id_string'];
					
					/**
					 * Now status with photo
					 */
					$post = array('status' => /*substr(*/ $this->decode_html_text($status)/*, 0, 139 )*/, 'media_ids' => $images);
					$this->log('Posting photo...');
					$this->response = $tw->post('statuses/update', $post);
					
				}
				else{
					$this->log('Failed to upload image '.$this->response);
				}
			}
			else{
				$this->response = $tw->post('statuses/update', $post);
			}
		}
		else if($this->data['site'] == 'youtube'){
			$yt = new Youtube($this->access_token);
			
			if(empty($yt->yt_token) || !empty($yt->error)){
				$data['error']['message'] = $yt->error;
				$this->error = $yt->error;
				$data = json_encode($data);	
			}
			else{
				/**
				 * Set file meta
				 */
				
				$title = $this->status;
				if(empty($title) && !empty($file_meta['description']))$title = $file_meta['description'];
				if(empty($title))$title = $this->file['original_name'];
				if(empty($title))$title = basename($this->file_link);
				
				if(!empty($file_meta['description']))$desc = $file_meta['description'];
				else $desc = $title;
				
				if(!empty($file_meta['tags']))$tags = $file_meta['tags'];
				else $tags = '';
				
				if(!empty($file_meta['category']))$category = $file_meta['category'];
				else $category = 'Entertainment|24';
				
				if(!empty($file_meta['privacy']))$privacy = $file_meta['privacy'];
				else $privacy = 'public';
				
				$title = $this->replace_variables($title);
				$desc = $this->replace_variables($desc);
				$title = $spintax->process($title);
				$desc = $spintax->process($desc);
				
				$title = substr($title, 0, 99);
				$desc = substr($desc, 0, 499);
					
				$yt->uploadVideo($this->file_link, $this->decode_html_text($title), $this->decode_html_text($desc), $category ,$privacy, $this->decode_html_text($tags));	
				if(!empty($yt->yt_video_id)){
					$aa = array();
					$aa['id'] = $aa['post_id'] = $aa['object_id'] = $yt->yt_video_id;
					$data = json_encode($aa);	
				}
				else{
					$data['error']['message'] = $yt->error;
					$data = json_encode($data);		
				}
			}
			$this->response = $data;
		}
		else{
			$timeout = 60;
			if($this->file_type == 'image'){
				$url = 'https://graph.facebook.com/'.$this->data['page_id'].'/photos?access_token='.$this->access_token;
				$post = array(
						'message' => $this->decode_html_text($this->status),
						'source' => '@'.$this->file_link,
						'access_token' => $this->access_token
						);	
			}
			else if($this->file_type == 'video'){
				
				if(!empty($file_meta['description']))$message = $file_meta['description'];
				else $message = $this->status;
				$timeout = 1800;
				$url = 'https://graph.facebook.com/'.$this->data['page_id'].'/videos?access_token='.$this->access_token;
				$post = array(
						'title' => $this->decode_html_text($this->status),
						'description' => $this->decode_html_text($message),
						'source' => '@'.$this->file_link,
						'access_token' => $this->access_token
						);	
			}
			else if($this->file_type == 'text'){
				list($c, $l) = extract_caption_links($this->status);
				$url = 'https://graph.facebook.com/'.$this->data['page_id'].'/feed?access_token='.$this->access_token;
				$post = array(
						'message' => $this->decode_html_text($this->status),
						'access_token' => $this->access_token
						);
				if(!empty($l)){
					$post['link'] = $l;	
					$post['message'] = $this->decode_html_text($c);
					
					/**
					 * fetch link meta if any
					 */
					list($link_title, $link_desc, $link_image) = sql_fetch_row(sql_query("SELECT link_title, link_desc, link_image FROM link_meta WHERE file_id = '$this->file_id'"));
					if(!empty($link_title)){
						$link_title = $this->replace_variables($link_title);
						$link_title = $spintax->process($link_title);	
						$post['name'] = $link_title;
					}
					
					if(!empty($link_desc)){
						$link_desc = $this->replace_variables($link_desc);
						$link_desc = $spintax->process($link_desc);	
						$post['description'] = $link_desc;	
					}
					
					if(!empty($link_image)){
						$post['picture'] = $link_image;	
					}
				}
				else if( !empty( $this->file['feed_image'] ) ) {
					$post['picture'] = 	$this->file['feed_image'];
				}
			}
			else if($this->file_type == 'link'){
				$url = 'https://graph.facebook.com/'.$this->data['page_id'].'/feed?access_token='.$this->access_token;
				$post = array(
						'description' => $this->decode_html_text($this->desc),
						'name' => $this->decode_html_text($this->status),
						'message' => $this->decode_html_text($this->status),
						'link' => $this->file_link,
						'access_token' => $this->access_token
						);
				if( !empty( $this->file['feed_image'] ) ) {
					$post['picture'] = 	$this->file['feed_image'];
				}	
			}
			
			if(!empty($this->tag_me)){
				if($this->file_type == 'image')$post['tags'] = '[{"tag_uid":"'.$this->data['social_id'].'"}]';
				//else if($this->file_type == 'video')$post['tags'] = '[{"tag_uid":"'.$this->data['social_id'].'"}]';
				else if($this->file_type == 'text')$post['tags'] = $this->data['social_id'];
			}
			
			$this->response = curl_single($url, $post, $timeout);
		}
	}
	
	public function add_watermark()
	{
		global $settings;
		
		$ffmpeg = $settings['ffmpeg'];
		
		if($this->file_type == 'video' && (empty($ffmpeg) || !$settings['video_watermarking_enabled'] || !$this->data['use_video_watermark']))return $this->file_link;
		if($this->file_type == 'image' && (!$settings['image_watermarking_enabled'] || !$this->data['use_image_watermark']))return $this->file_link;
		
		$this->log('Watermarking file...');
		$in = $this->file_link;
		
		if($this->file_type == 'image')
			$out = __ROOT__.'/tmp/'.basename($this->file_link).'_'.$this->data['watermark'].'_'.$this->data['watermark_position'].'_'.$this->data['schedule_group_id'].'_wm_.jpg';
		
		else if($this->file_type == 'video')
			$out = __ROOT__.'/tmp/'.basename($this->file_link).'_'.$this->data['watermark'].'_'.$this->data['watermark_position'].'_'.$this->data['schedule_group_id'].'_wm_.mp4';
		
		/**
		 * If the file was already converted for another schedule
		 */
		$t = time();
		$lock = $out.'.lock';
		do{
			$running = check_running_file($lock);
			sleep(1);
			/**
			 * Allow 30 minutes
			 */
			if(time() - $t > 1800){
				$this->log('Watermark wait timeout...');
				return $this->file_link;
			}
		}while($running);
		
		if(file_exists($out) && filesize($out) > 100 && !file_exists($lock)){
			$this->log('Using cached watermarked photo...');
			return $out;
		}
		$flock = lock_file($lock);
		
		$wm_pos = $this->data['watermark_position'];
		$wm_img = __STORAGE__.'/'.$this->data['storage'].'/'.$this->data['watermark'];
		
		if($this->file_type == 'image'){
			$this->log("Watermarking image...");
			
			$image = imagecreatefromfile($in);
			$watermark = imagecreatefromfile($wm_img);
			
			if(empty($image) || empty($watermark)){
				$this->log('Failed to create an instance of image');
				return $this->file_link;	
			}
			
			$img_w = imagesx($image);
			$img_h = imagesy($image);
			
			$wm_w = imagesx($watermark);
			$wm_h = imagesy($watermark);
			
			$wm_w_new = (int)($img_w*0.15);
			$wm_h_new = (int)(($wm_w_new/$wm_w) * $wm_h);
 
 			/** 
			 * BOTTOMRIGHT
			 */ 
			$watermark_pos_x = $img_w - $wm_w_new - 5;
			$watermark_pos_y = $img_h - $wm_h_new - 5;
			
			if($wm_pos == 'TOPRIGHT'){
				$watermark_pos_x = $img_w - $wm_w_new - 5;
				$watermark_pos_y = 5;	
			}
			else if($wm_pos == 'TOPLEFT'){
				$watermark_pos_x = 5;
				$watermark_pos_y = 5;	
			}
			else if($wm_pos == 'BOTTOMLEFT'){
				$watermark_pos_x = 5;
				$watermark_pos_y = $img_h - $wm_h_new - 5;	
			}
			else if($wm_pos == 'CENTER'){
				$watermark_pos_x = ($img_w - $wm_w_new)/2;
				$watermark_pos_y = ($img_h - $wm_h_new)/2;	
			}
						
			$wm_resized = imagecreatetruecolor($wm_w_new, $wm_h_new);
			imagealphablending($wm_resized, false);
        	imagesavealpha($wm_resized, true);
			imagecopyresampled($wm_resized, $watermark, 0, 0, 0, 0, $wm_w_new, $wm_h_new, $wm_w, $wm_h);
			imagecopy($image, $wm_resized, $watermark_pos_x, $watermark_pos_y, 0, 0, $wm_w_new, $wm_h_new);
			imagejpeg($image, $out, 100);
			
			if(@filesize($out) < 100){
				@unlink($out);
				$this->log('Watermarking failed');
				unlock_process($flock);
				@unlink($lock);
				return $this->file_link;	
			}
			$this->log('Image successfully watermarked');
			unlock_process($flock);
			@unlink($lock);
			return $out;
		}
		else{
			$f = new ffmpeg('');
			$data = $f->analyze_video($in);
			
			if(empty($data['width']) || empty($data['height'])){
				$this->log("Failed to get width and height of video");
				return $this->file_link;	
			}
			
			list($wm_w, $wm_h) = getimagesize($wm_img);
			
			if(empty($wm_w) || empty($wm_h)){
				$this->log("Failed to get width and height of watermark");
				return $this->file_link;	
			}
			
			$wm_w_new = (int)($data['width']*0.15);
			$wm_h_new = (int)(($wm_w_new/$wm_w) * $wm_h);
			
			$slideshow = $this->file_id == 'slideshow' ? 1 : 0;
			
			$cmd = $ffmpeg.' -i '.$in.' -i '.$wm_img.' -filter_complex ';
			if($wm_pos == 'TOPLEFT')
				$cmd .= ' "[1:v] scale='.$wm_w_new.':'.$wm_h_new.' [wm]; [0:v][wm] overlay=3:3 [v]'.($slideshow ? '' : "; [0:a]anull[a]").'" ';
			else if($wm_pos == 'TOPRIGHT')
				$cmd .= ' "[1:v] scale='.$wm_w_new.':'.$wm_h_new.' [wm]; [0:v][wm] overlay=x=main_w-overlay_w-3:y=3  [v]'.($slideshow ? '' : "; [0:a]anull[a]").'" ';
			else if($wm_pos == 'BOTTOMLEFT')
				$cmd .= ' "[1:v] scale='.$wm_w_new.':'.$wm_h_new.' [wm]; [0:v][wm] overlay=x=3:y=main_h-overlay_h-3  [v]'.($slideshow ? '' : "; [0:a]anull[a]").'" ';
			else if($wm_pos == 'BOTTOMRIGHT')
				$cmd .= ' "[1:v] scale='.$wm_w_new.':'.$wm_h_new.' [wm]; [0:v][wm] overlay=x=main_w-overlay_w-3:y=main_h-overlay_h-3 [v]'.($slideshow ? '' : "; [0:a]anull[a]").'" ';
			else if($wm_pos == 'CENTER')
				$cmd .= ' "[1:v] scale='.$wm_w_new.':'.$wm_h_new.' [wm]; [0:v][wm] overlay=x=(main_w-overlay_w)/2:y=(main_h-overlay_h)/2 [v]'.($slideshow ? '' : "; [0:a]anull[a]").'" ';	
			
			$cmd .= ' -map "[v]" '.( $slideshow ? '' : ' -map "[a]" -acodec libmp3lame ' ).' -vcodec libx264 -threads 1 -y '.$out;
			
			$this->log("Watermarking video with : ".$cmd);
			exec($cmd, $o, $c);
			if(filesize($out) < 100 || $c){
				@unlink($out);
				$this->log('Command failed '.$cmd);
				unlock_process($flock);
				@unlink($lock);
				return $this->file_link;	
			}
			$this->log('Video successfully watermarked');
			unlock_process($flock);
			@unlink($lock);
			return $out;
		}
		unlock_process($flock);
		@unlink($lock);
	}
	
	public function mark_done($force = 0, $notes = '')
	{
		/**
		 * if do repeat is selected
		 */
		if($this->data['do_repeat'] && !$force){
			$this->log('Schedule is selected to be repeated...');
			sql_query("UPDATE post_log SET is_blind = 1 WHERE schedule_id = '$this->schedule_id'");
			$this->set_next_schedule_time();
			return true;	
		}
		
		$this->log("Marking schedule as done...");
		sql_query("UPDATE schedules SET is_done = 1, completed_at = NOW(), is_locked = 0, notes = '$notes', next_post = '0000-00-00 00:00:00' WHERE schedule_id = '$this->schedule_id'");
		
		/**
		 * check if all schedules in this group is done
		 */
		$sc_group_id = $this->data['schedule_group_id'];
		if(!sql_num_rows(sql_query("SELECT NULL FROM schedules WHERE is_done = 0 AND schedule_group_id = '$sc_group_id' LIMIT 1"))){
			/**
			 * reset and restart when schedule group is done and repeat_campaign is set
			 */
			if(!empty($this->data['repeat_campaign'])){
				$this->log("Repeating campaign. Resetting all schedules...");
				reset_schedule_group($sc_group_id, 0, $this->data['time_zone']);
			}
			/**
			 * mark as done when schedule group is complete
			 */
			else{
				$this->log("Marking schedule group as done...");
				sql_query("UPDATE schedule_groups SET is_done = 1, next_post = '0000-00-00 00:00:00' WHERE schedule_group_id = '$sc_group_id'");	
				
				/*
				 * Clear slideshows and all watermarks for this schedule
				 */
				clear_tmp_sgid($sc_group_id);
			}
		}
		
		/**
		 * notify
		 */
		if($this->data['fb_noti'] || ($this->data['email_noti'] && $this->data['email'])){ 
		
			global $auth, $settings;
			list($table, $col, $ucol, $ncol) = get_site_params($this->data['site']);
			list($s, $access_token) = $auth->is_id_owner($this->data['user_id'], $this->data['fb_noti'], 'fbprofile');
			list($name, $uname) = sql_fetch_row(sql_query("SELECT $ncol, $ucol FROM $table WHERE $col = '".$this->data['page_id']."'"));
			
			$s = str_replace('fb', 'facebook ', $this->data['site']);
			if($site == 'twitter' || $site == 'youtube')$s = $this->data['site'].' profile';
			$u = get_profile_url($this->data['page_id'], $uname, $this->data['site']);
		
			if($this->data['fb_noti']){
				$this->log("Sending fb notification to ".$this->data['fb_noti']);
				$params = array('page_name' => $s.' '.$name);
				$m = fb_noti($this->data['fb_noti'], $params, $access_token, $settings['fb_app_token']);	
				$this->log("Noti send result: ".(int)$m);
			}
			if($this->data['email_noti'] && $this->data['email']){
				$this->log("Sending email to ".$this->data['email']);
				$params = array('page_name' => $s.' <a href="'.$u.'">'.$name.'</a>');
				$m = send_email($this->data['email'], 'sch_done' ,$params);	
				$this->log("Mail send result: ".(int)$m);	
			}	
		}
	}
	
	public function set_next_schedule_time( $jump_to = 0 )
	{
		if( $this->next_post_time_selected ) {
			$this->log("Setting next schedule time already selected...");	
			return;	
		}
		
		$this->next_post_time_selected = 1;
		$this->log("Setting next schedule time...");
		$elapsed = time() - $this->start_time;
		if($elapsed < 60) $elapsed = 0;
		else $this->log('More than 60 seconds elapsed. Increasing next_post time...');
		
		set_next_schedule_time( $this->data, $elapsed, $jump_to );	
	}
	
	public function insert_error_post_log( $hidden = 0, $error )
	{
		$this->log("Inserting error post log...");
		$hid = "'0000-00-00 00:00:00'";
		
		$next_ins = "'0000-00-00 00:00:00'";	
		$hid = "'0000-00-00 00:00:00'";
		
		sql_query("INSERT INTO post_log 
					(user_id, post_id, social_id, page_id, site, schedule_group_id, schedule_id, folder_id, file_id, posted_at, delete_at, hid_action, next_insight, is_hidden, next_bump, last_bump_message)
					 VALUES
					 ('".$this->data['user_id']."',
					 '-1',
					 '".$this->data['social_id']."',
					 '".$this->data['page_id']."',
					 '".$this->data['site']."',
					 '".$this->data['schedule_group_id']."',
					 '".$this->schedule_id."',
					 '".$this->data['folder_id']."',
					 '".$this->file_id."',
					 NOW(),
					 $hid,
					 '".$this->data['post_delete_action']."',
					 $next_ins,
					 '$hidden',
					 $next_ins,
					 '".sql_real_escape_string( $error )."')");	
	}
	
	public function insert_post_log( $hidden = 0 )
	{
		$this->log("Inserting post log...");
		$hid = "'0000-00-00 00:00:00'";
		if($this->data['post_delete_after'] && $this->file_type != 'album'){
			$hid = get_next_post_time($this->data['post_delete_after'], '', '', '', $this->data['time_zone']);
			$hid = "FROM_UNIXTIME('$hid')";
			
			if($this->data['site'] != 'fbpage' && $this->data['post_delete_action'] == 'HIDE')$this->data['post_delete_action'] = 'DELETE';	
		}
		
		if($hidden){
			$next_ins = "'0000-00-00 00:00:00'";	
			$hid = "'0000-00-00 00:00:00'";
		}
		else{
			$next_ins = "DATE_ADD(NOW(), INTERVAL 3 HOUR)";	
		}
		
		sql_query("INSERT INTO post_log 
					(user_id, post_id, social_id, page_id, site, schedule_group_id, schedule_id, folder_id, file_id, posted_at, delete_at, hid_action, next_insight, is_hidden)
					 VALUES
					 ('".$this->data['user_id']."',
					 '".sql_real_escape_string($this->post_id)."',
					 '".$this->data['social_id']."',
					 '".$this->data['page_id']."',
					 '".$this->data['site']."',
					 '".$this->data['schedule_group_id']."',
					 '".$this->schedule_id."',
					 '".$this->data['folder_id']."',
					 '".$this->file_id."',
					 NOW(),
					 $hid,
					 '".$this->data['post_delete_action']."',
					 $next_ins,
					 '$hidden')");	
		
		$post_log_id = sql_insert_id();
		
		/**
		 * keep post count for today
		 */
		sql_query("DELETE FROM post_counter WHERE today != DATE(NOW())");
		
		sql_query("INSERT INTO post_counter (user_id, today, post_count, site) 
					 VALUES
					 ('".$this->data['user_id']."', 
					 NOW(), 
					 1,
					 '') 
					 ON DUPLICATE KEY UPDATE post_count =  post_count + 1");
					 
		sql_query("INSERT INTO post_counter (user_id, today, post_count, site) 
					 VALUES
					 ('".$this->data['user_id']."', 
					 NOW(), 
					 1,
					 '".$this->get_post_counter_site()."') 
					 ON DUPLICATE KEY UPDATE post_count =  post_count + 1");
		
		sql_query("UPDATE schedule_groups SET last_post = NOW() WHERE schedule_group_id = '".$this->data['schedule_group_id']."'");
		
		if($hidden){
			$n = 'Some posts could not be sent due to errors. <br/>Please check logs tab in dashboard for more info';
			sql_query("UPDATE schedules SET notes = '$n' WHERE schedule_id = '".$this->data['schedule_id']."'");	
		}
		
		/**
		 * Delete file if configured
		 */			 
		if($this->data['auto_delete_file'] && $this->folder_type == 'FOLDER'){
			global $auth;
			delete_file(basename($this->file_link), $this->data['storage'], $this->raw_folder_id, $this->file_id);	
			$auth->get_user_used_space($this->data['user_id']);	
		}
		
		if($post_log_id && !$hidden && !empty($this->data['comment_bumping_freq']) && !empty($this->data['comment_bumps']) && !empty($this->data['bump_type']) && $this->data['use_advanced_scheduling']){
			$this->log('Setting next bump time...');
			$next_bump = next_comment_bump_time($this->data['comment_bumping_freq']);
			sql_query("UPDATE post_log SET next_bump = FROM_UNIXTIME('$next_bump') WHERE post_log_id = '$post_log_id'");	
		}
	}
	
	public function replace_variables($text)
	{
		if(empty($this->data['time_zone']))date_default_timezone_set('UTC');
		else date_default_timezone_set($this->data['time_zone']);
			
		$text = str_replace('[SCHEDULE_NAME]', $this->data['schedule_group_name'], $text);	
		$text = str_replace('[FIRST_NAME]', $this->data['first_name'], $text);
		$text = str_replace('[LAST_NAME]', $this->data['last_name'], $text);
		$text = str_replace('[FULL_NAME]', $this->data['first_name'].' '.$this->data['last_name'], $text);
		$text = str_replace('[TIME]', date('h:i A'), $text);
		$text = str_replace('[DATE]', date('d-M-Y'), $text);
		$text = str_replace('[DATE_TIME]', date('d-M-Y h:i:s A'), $text);
		$text = str_replace('[PAGE_NAME]', $this->data['page_name'], $text);
		$text = str_replace('[FILE_NAME]', $this->file_orig_name, $text);
		
		//tags do not work with api currently
		//if(preg_match('/^fb/', $this->data['site']))$text = str_replace('[TAG_ME]', '@['.$this->data['social_id'].']', $text, $c);
		//else 
		
		$text = str_replace('[TAG_ME]', '', $text, $c);
		if($c)$this->tag_me = 1;
		
		if(preg_match('/\[GREETINGS\]/', $text)){
			global $lang;
	
			$hour = (int)date('G');
			if($hour >= 5 && $hour <= 11){
				$g = $lang['greetings']['good_morning'].' '.$lang['greetings']['everyone'];
			} 
			else if($hour >= 12 && $hour <= 18){
				$g = $lang['greetings']['good_afternoon'].' '.$lang['greetings']['everyone'];
			} 
			else if($hour >= 19 && $hour <= 22){
				$g = $lang['greetings']['good_evening'].' '.$lang['greetings']['everyone'];
			}
			else{
				$g = $lang['greetings']['good_night'].' '.$lang['greetings']['everyone'];
			}
			$text = str_replace('[GREETINGS]', $g, $text);
		}
		
		return $text;
	}
	
	public function check_errors()
	{
		$response = json_decode($this->response, true);
		
		check_errors_in_response($response, $this->error, $this->data['user_id'], $this->data['social_id'], $this->data['site'], $this->data['page_id'], 'SCHEDULE_ID:'.$this->schedule_id, $this->data['folder_id'].'|FILE:'.$this->file_id, $this->log_file, $this->access_token);
	}
	
	public function cleanup()
	{
		foreach($this->to_delete as $f){
			$this->log("Deleting $f ...");
			unlink($f);
		}
		
		$total = sql_num_rows(sql_query("SELECT NULL FROM schedules WHERE schedule_group_id = '".$this->data['schedule_group_id']."' LIMIT 5000"));
		sql_query("UPDATE schedule_groups SET total_schedules = '$total' WHERE schedule_group_id = '".$this->data['schedule_group_id']."'");
	}
	
	public function decode_html_text($text)
	{
		$text = stripslashes(htmlspecialchars_decode($text, ENT_QUOTES));
		return $text;
	}
	
	public function log($str, $clear = 0)
	{
		$master_log_file = $this->log_file;
		if($clear)$fp = fopen($master_log_file, "w");
		else $fp = fopen($master_log_file, "a");
		fwrite($fp,date('[d-M-Y H:i:s]').'[SC#'.$this->schedule_id.']'." $str\r\n");
		fclose($fp);
		
		/*
		if(!empty($_SERVER['HTTP_USER_AGENT']))echo $str."<br/>";
		else echo $str."\n";
		@flush();
		@ob_flush();
		*/
	}
}

?>