<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
class hid
{
	public $user_id;
	public $social_id;
	public $page_id;
	public $post_id;
	public $site;
	public $access_token;
	public $action;
	
	public $log_file;
	public $success;
	
	public function __construct($user_id, $social_id ,$page_id, $post_id, $site ,$token, $action = 'delete')
	{
		global $settings;
		load_app_settings($user_id);
		
		$this->user_id = $user_id;
		$this->post_id = $post_id;
		$this->social_id = $social_id;
		$this->site = $site;
		$this->access_token = $token;
		$this->page_id = $page_id;
		$this->action = $action;
		
		$this->success = false;
		$this->log_file = __ROOT__.'/logs/hid-log.txt';
		
		$this->log('-----------------------------');
		$this->log("USERID: $user_id | SOCIALID: $social_id | PAGEID: $page_id | POSTID: $post_id | SITE: $site | ACTION: $action");
		
		if($this->site == 'twitter'){
			require_once __ROOT__."/sdk/twitter/twitter.php";
			$token = explode(':::', $this->access_token);
			$tw = new TwitterOAuth($settings['tw_app_id'], $settings['tw_app_secret'], $token[0], $token[1]);
			$tw->decode_json = false;
			$data = $tw->delete('statuses/destroy/'.$this->post_id);
			$this->success = $this->update_entry($data);
			$this->log('statuses/destroy/'.$this->post_id);
		}
		else if($this->site == 'youtube'){
			require_once __ROOT__."/sdk/youtube/youtube.php";
			$token = $this->access_token;
			$yt = new Youtube($token);
			$data = array('site' => 'youtube');
			$yt->deleteVideo($this->post_id);
			if(empty($yt->yt_token) || !empty($yt->error)){
				$data['error'] = array();
				$data['error']['message'] = $yt->error;	
			}
			$data = json_encode($data);
			$this->success = $this->update_entry($data, $yt->error);
		}
		else if($this->site == 'fbprofile' || $this->site == 'fbpage' || $this->site == 'fbgroup' || $this->site == 'fbevent'){
			$url = 'https://graph.facebook.com/'.$this->post_id.'?access_token='.$this->access_token;
			
			if($action == 'delete')$post = array('method' => 'delete');
			else $post = array('timeline_visibility' => 'hidden');
			
			$data = curl_single($url, $post);
			$this->success = $this->update_entry($data);
		}
		
		$this->log('-----------------------------');
	}
	
	/**
	 * Function to check server response and insert deletion record into database
	 * @param string $rr is server response in html/text format which is json decoded later
	 * @param string $error is any additional error message -> this is used by youtube
	 * @return bool true on success, false on failure
	 */
	public function update_entry($rr, $error = '')
	{
		$response = json_decode($rr,true);
		
		$ok = check_errors_in_response($response, $error, $this->user_id, $this->social_id, $this->site, $this->page_id, $this->post_id, '', $this->log_file, $this->access_token);
		
		/**
		 * set delete_at to EMPTY if a post is set to delete in post_log | this prevents the app to try to delete a post again
		 */
		$post_id2 = $post_id = $this->post_id;
		if(preg_match('/\_/', $this->post_id) && preg_match('/^fb/', $this->site))list(,$post_id) = explode('_', $post_id);
		$post_id = sql_real_escape_string($post_id);
		$post_id2 = sql_real_escape_string($post_id2);
		
		if($this->action == 'delete'){
			/**
			 * keep record of deleted post
			 */
			sql_query("INSERT INTO delete_log (post_id, site, deleted_at) VALUES('$post_id', '$this->site', NOW())");
			
			sql_query("UPDATE post_log SET delete_at = '0000-00-00 00:00:00', next_bump = '0000-00-00 00:00:00', hid_status = 1, next_insight = '0000-00-00 00:00:00' WHERE (post_id = '$post_id' OR post_id = '$post_id2') AND site = '$this->site'");	
		}
		else if($this->action == 'hide'){
			sql_query("UPDATE post_log SET delete_at = '0000-00-00 00:00:00', next_bump = '0000-00-00 00:00:00', hid_status = 2, next_insight = '0000-00-00 00:00:00' WHERE (post_id = '$post_id' OR post_id = '$post_id2') AND site = '$this->site' AND hid_action = 'HIDE'");	
		}
		
		/**
		 * Check if post was successfully deleted or hidden
		 */
		if($this->site == 'twitter' && preg_match('/id_str/i', $rr)){
			$this->log('Post successfully deleted');
			return true;
		}
		else if($this->site == 'youtube' && empty($error)){
			$this->log('Post successfully deleted');
			return true;
		}
		else if(preg_match('/true/i', $rr) && preg_match('/^fb/i', $this->site)){
			$this->log('Post successfully deleted');
			return true;	
		}
		
		$this->log($rr);
		$this->log('Failed to delete/hide post');
		return false;
	}
	
	public function log($str)
	{
		$fp = fopen($this->log_file, "a");
		fwrite($fp,date('[d-M-Y H:i:s]')." $str\r\n");
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