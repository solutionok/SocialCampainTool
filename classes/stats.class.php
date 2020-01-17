<?php
/**
 * @package Social Ninja
 * @version 1.2
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
class stats
{
	public $user_id;
	public $social_id;
	public $page_id;
	public $post_id;
	public $site;
	public $access_token;
	
	public $log_file;
	public $success;
	
	public function __construct($user_id, $social_id ,$page_id, $post_id, $site ,$token)
	{
		global $settings;
		load_app_settings($user_id);
		
		$this->user_id = $user_id;
		$this->post_id = $post_id;
		$this->social_id = $social_id;
		$this->site = $site;
		$this->access_token = $token;
		$this->page_id = $page_id;
		//$this->action = $action;
		
		$this->success = false;
		$this->log_file = __ROOT__.'/logs/stats-log.txt';
		
		$this->log('-----------------------------');
		$this->log("USERID: $user_id | SOCIALID: $social_id | PAGEID: $page_id | POSTID: $post_id | SITE: $site");
		
		$data = array();
		
		if($this->site == 'twitter'){
			require_once __ROOT__."/sdk/twitter/twitter.php";
			$token = explode(':::', $this->access_token);
			$tw = new TwitterOAuth($settings['tw_app_id'], $settings['tw_app_secret'], $token[0], $token[1]);
			$tw->decode_json = false;
			$info2 = $tw->get('statuses/show/'.$this->post_id);
			$info = json_decode($info2, true);
			$error = '';
			if(!empty($info['errors']))$error = end($info['errors']);
			
			if(is_array($info)){
				if(!empty($info['favorite_count']))$data['Favorites'] = $info['favorite_count'];
				if(!empty($info['retweet_count']))$data['Retweet'] = $info['retweet_count'];	
			}
			if(!empty($error)){
				$data['error'] = array();
				$data['error']['message'] = $error;	
			}
			$this->success = $this->update_entry($info2, $data, $error);
		}
		else if($this->site == 'youtube'){
			require_once __ROOT__."/sdk/youtube/youtube.php";
			$token = $this->access_token;
			$yt = new Youtube($token);			
			$info = $yt->getInsights($this->post_id);
			
			if(is_array($info)){				
				if(!empty($info['yt$statistics']['viewCount']))$data['Views'] = $info['yt$statistics']['viewCount'];
				if(!empty($info['yt$rating']['numLikes']))$data['Likes'] = $info['yt$rating']['numLikes'];
				if(!empty($info['yt$rating']['numDislikes']))$data['Dislikes'] = $info['yt$rating']['numDislikes'];
				if(!empty($info['yt$statistics']['favoriteCount']))$data['Favorites'] = $info['yt$statistics']['favoriteCount'];				
			}
						
			if(empty($yt->yt_token) || !empty($yt->error)){
				$data['error'] = array();
				$data['error']['message'] = $yt->error;	
			}
			$this->success = $this->update_entry(json_encode($info), $data, $yt->error);
		}
		else if($this->site == 'fbprofile' || $this->site == 'fbpage' || $this->site == 'fbgroup' || $this->site == 'fbevent'){
			
			$url = 'https://graph.facebook.com/'.$this->post_id.'/?access_token='.$this->access_token.'&fields=likes.summary(true).limit(0),comments.summary(true).limit(0)';
			$info = curl_single($url);
			
			$info2 = json_decode($info, true);
			
			if(!empty($info2['likes']['summary']['total_count'])){
				$data['Likes'] = $info2['likes']['summary']['total_count'];	
			}
			else if(!empty($info2['likes']['count'])){
				$data['Likes'] = $info2['likes']['count'];	
			}
			
			if(!empty($info2['comments']['summary']['total_count'])){
				$data['Comments'] = $info2['comments']['summary']['total_count'];	
			}
			else if(!empty($info2['comments']['count'])){
				$data['Comments'] = $info2['comments']['count'];	
			}
			
			if($this->site == 'fbpage'){
				
				$url = 'https://graph.facebook.com/'.$this->post_id.'/insights?access_token='.$this->access_token;
				$mm = curl_single($url);
				$s = 0;
				$mm = json_decode($mm, true);
				if(!empty($mm['data'])){
					$mm = $mm['data'];
					foreach($mm as $m){
						if($m['name'] == 'post_impressions_unique' && !empty($m['values'][0]['value'])){
							$data['Views'] = $m['values'][0]['value'];
							$s++;
						}
						else{
							if($m['name'] == 'post_negative_feedback_unique' && !empty($m['values'][0]['value'])){
								$data['Negative_Feedback'] = $m['values'][0]['value'];
								$s++;
							}	
						}
						if($s >= 2)break;
					}
				}
			}
			$this->success = $this->update_entry($info, $data);
		}
		
		$this->log('-----------------------------');
	}
	
	/**
	 * Function to check server response and insert stats record into database
	 * @param string $rr is server response in html/text format which is json decoded later
	 * @param array $data is stats data only inserted if the response is error free
	 * @param string $error is any additional error message -> this is used by youtube and twitter
	 * @return bool true on success, false on failure
	 */
	public function update_entry($rr, $data ,$error = '')
	{
		$response = json_decode($rr, true);
		
		$ok = check_errors_in_response($response, $error, $this->user_id, $this->social_id, $this->site, $this->page_id, $this->post_id, '', $this->log_file, $this->access_token);
		
		if($ok === true){
			$this->log('Stats successfully received');
			$data = json_encode($data);
			$data = sql_real_escape_string($data);
			sql_query("UPDATE post_log SET insights = '$data', next_insight = DATE_ADD(NOW(), INTERVAL 3 HOUR) WHERE post_id = '$this->post_id' AND site = '$this->site' AND next_insight != '0000-00-00 00:00:00'");
			
			/**
			 * Delete based on stats
			 */
			$this->check_stats_settings($data);
			return true;	
		}
		
		sql_query("UPDATE post_log SET next_insight = '0000-00-00 00:00:00' WHERE post_id = '$this->post_id' AND site = '$this->site'");
		$this->log($rr);
		$this->log('Failed to get insights');
		return false;
	}
	
	public function check_stats_settings($data)
	{
		list($allowed) = sql_fetch_row(sql_query("SELECT membership_plans.use_advanced_scheduling FROM users LEFT JOIN membership_plans ON membership_plans.plan_id = users.plan_id WHERE users.user_id = '$this->user_id'"));
		
		if(!$allowed){
			$this->log('Users membership plan does not allow advanced scheduling');
			return false;	
		}
		
		$this->log('Checking stats settings...');
		$stats_settings = '';
		list($log_id, $posted_ago, $stats_settings) = sql_fetch_row(sql_query("SELECT post_log.post_log_id, TIMESTAMPDIFF(SECOND, post_log.posted_at, NOW()) AS posted_ago, schedules.stats_settings FROM post_log LEFT JOIN schedules ON schedules.schedule_id = post_log.schedule_id WHERE post_log.post_id = '$this->post_id' AND post_log.site = '$this->site'"));	
		
		if(empty($stats_settings))return true;
		$ss = json_decode($stats_settings, true);
		
		foreach($ss as $s){
			$t = $s['time']*3600;
			if($t > $posted_ago)continue;
			
			$v = @$data[$s['name']];
			$v = (int)$v;
			
			$op = $s['op'];
			$this->log("Time: ".$t.' Posted ago: '.$posted_ago.' '.$s['name'].': '.$v.' Threshold: '.$s['am']);
			
			if(($op == 'below' && $v < $s['am']) || ($op == 'above' && $v > $s['am'])){
				$this->log("Post is scheduled to delete as per settings...");
				sql_query("UPDATE post_log SET delete_at = NOW(), hid_action = 'DELETE' WHERE post_log_id = '$log_id'");
				break;	
			}
			else $this->log("Post is not deleted as per settings");
		}
	}
	
	public function log($str)
	{
		$fp = fopen($this->log_file, "a");
		fwrite($fp,date('[d-M-Y H:i:s]')." [".$this->post_id."|".$this->site."] $str\r\n");
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