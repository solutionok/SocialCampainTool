<?php
/**
 * Comments class
 * Processes comment bumps
 *
 * @package Social Ninja
 * @version 1.3
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

require_once dirname(dirname(__FILE__))."/sdk/facebook/facebook.php";

class comment
{
	public $post_log_id;
	public $schedule_id;
	public $error;
	public $data;
	public $access_token;
	public $comment_id;
	public $comment;
	
	public function __construct($post_log_id)
	{
		$this->log_file = dirname(dirname(__FILE__)).'/logs/comments-log.txt';
		$this->post_log_id = $post_log_id;
		
		$sql = "SELECT schedules.user_id, schedules.social_id, schedules.schedule_id, schedules.page_id, schedules.comment_bumps, schedules.comment_bumping_freq, schedules.bump_type, post_log.post_id, post_log.site, UNIX_TIMESTAMP(post_log.posted_at) AS posted_at_unix
				FROM post_log 
				LEFT JOIN schedules ON schedules.schedule_id = post_log.schedule_id
				WHERE post_log.post_log_id = '$this->post_log_id' AND
				schedules.schedule_id IS NOT NULL";
					
		$data = sql_fetch_assoc(sql_query($sql));
		
		if(empty($data)){
			//maybe locked
			$this->log('Unable to fetch schedule data');
			return false;	
		}
		
		$this->data = $data;
		if(empty($this->data['comment_bumps']) || empty($this->data['comment_bumping_freq']) || empty($this->data['bump_type'])){
			$this->log('No more comments left to bump. Ending bumping...');
			$this->end_bumping();
			return false;	
		}
		
		$this->schedule_id = $data['schedule_id'];
		$this->init();
	}
	
	public function init()
	{
		/**
		 * Load access token
		 */
		$this->load_access_token();
		if(empty($this->access_token)){
			$this->set_next_bump_time();
			return true;
		}
		
		$this->do_post();
		$response = json_decode($this->response, true);
		
		/**
		 * If post id found
		 */
		if(!empty($response['id'])){
			$this->log('Successfully posted comment...');
			$this->comment_id = $response['id'];
			/**
			 * Set next bump time
			 */
			$this->set_next_bump_time();
		}		
		/**
		 * Otherwise post failed
		 */
		else{
			/**
			 * End bump
			 */
			$this->end_bumping();
			$this->log('Failed to create comment');
			$this->log($this->response);
			/**
			 * Check errors : token expiry|blocks etc
			 */
			$this->check_errors();
		}
	}
	
	public function load_access_token()
	{
		$this->log('Loading access token...');
		list($table, $col, $tt, $ttt, $sid) = get_site_params($this->data['site']);
		
		list($this->access_token) = sql_fetch_row(sql_query("SELECT access_token FROM $table WHERE $col = '".$this->data['page_id']."' AND user_id = '".$this->data['user_id']."' AND $sid = '".$this->data['social_id']."' AND account_status = 1"));
		if(empty($this->access_token)){
			$this->log("Failed to load access token");
			$this->limit_schedule();
			return false;	
		}
		$this->log('Access token loaded...');
		return true;
	}
	
	public function do_post()
	{
		$comments = json_decode($this->data['comment_bumps'], true);
		shuffle($comments);
		$comment = array_pop($comments);
	
		if(empty($comment)){
			$this->log('No comment left to use. Ending bumping...');
			$this->end_bumping();
			return false;	
		}
	
		if($this->data['bump_type'] == 'onetime'){
			if(empty($comments)){
				$this->log('All comments used. Ending bumping...');
				$this->end_bumping();	
			}	
			
			if(empty($comments))$comments = '';
			else $comments = sql_real_escape_string(json_encode($comments));
			sql_query("UPDATE schedules SET comment_bumps = '$comments' WHERE schedule_id = '$this->schedule_id'");	
		}
		
		if(!preg_match('/.*_.*/', $this->data['post_id'])){
			
			$url = 'https://graph.facebook.com/'.$this->data['page_id'].'/feed?fields=object_id&access_token='.$this->access_token.'&until='.($this->data['posted_at_unix'] + 120).'&limit=500';
			$ddd = curl_single($url);			
			$data = json_decode($ddd, true);
			foreach($data['data'] as $p){
				if($p['object_id'] == $this->data['post_id']){
					$this->data['post_id'] = $p['id'];
					$ppp = sql_real_escape_string($p['id']);
					sql_query("UPDATE post_log SET post_id = '$ppp' WHERE post_log_id = '$this->post_log_id' AND site = 'fbgroup'");
					break;	
				}
			}		
		}
		
		/**
		 * Spintax support
		 */
		$spintax = new spintax();
		$comment = $spintax->process($comment);
		/** 
		 * Send comment
		 */
		$this->comment = $comment; 
		$timeout = 60;
		$url = 'https://graph.facebook.com/'.$this->data['post_id'].'/comments?access_token='.$this->access_token;
		$post = array('message' => html_entity_decode($comment));
		$this->response = curl_single($url, $post, $timeout);
	}
	
	public function check_errors()
	{
		$response = json_decode($this->response, true);
		
		check_errors_in_response($response, '', $this->data['user_id'], $this->data['social_id'], $this->data['site'], $this->data['page_id'], 'SCHEDULE_ID:'.$this->schedule_id, 'POST_ID:'.$this->data['post_id'], $this->log_file, $this->access_token);
	}
	
	public function limit_schedule()
	{
		sql_query("UPDATE schedules SET rate_limited = 1, rate_limited_at = NOW() WHERE schedule_id = '$this->schedule_id'");
	}
	
	public function set_next_bump_time()
	{
		$this->log("Setting next bump time...");
		$next_bump = next_comment_bump_time($this->data['comment_bumping_freq']);
		
		sql_query("UPDATE post_log SET last_bump = NOW(), last_bump_message = '".sql_real_escape_string($this->comment)."' WHERE post_log_id = '$this->post_log_id'");
		sql_query("UPDATE post_log SET next_bump = FROM_UNIXTIME('$next_bump') WHERE post_log_id = '$this->post_log_id' AND next_bump != '0000-00-00 00:00:00'");
	}
	
	public function end_bumping()
	{
		$this->log("Ending bumping...");
		sql_query("UPDATE post_log SET next_bump = '0000-00-00 00:00:00' WHERE post_log_id = '$this->post_log_id'");
	}
	
	public function log($str, $clear = 0)
	{
		$master_log_file = $this->log_file;
		if($clear)$fp = fopen($master_log_file, "w");
		else $fp = fopen($master_log_file, "a");
		fwrite($fp,date('[d-M-Y H:i:s] [SC#'.$this->schedule_id.' LOG#'.$this->post_log_id.']')." $str\r\n");
		fclose($fp);
		
		if(!empty($_SERVER['HTTP_USER_AGENT']))echo $str."<br/>";
		else echo $str."\n";
		@flush();
		@ob_flush();
	}
}

?>