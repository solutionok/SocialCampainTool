<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
include(dirname(dirname(__FILE__)).'/loader.php');

if(!empty($settings['disable_all_crons']) || !empty($settings['disable_hide_delete_cron']) || !empty($settings['enable_maintenance_mode'])){
	exit(0);
	//die('Cron tasks disabled');
}
$set = 'hcron';
if(check_running($set)){
	exit(0);
	//die('Another cron is already running');
}
$lock = lock_process($set);
if(empty($lock)){
	do_log('Failed to lock process');
	exit(0);
	//die('Failed to lock process');
}

/**
 * select pending posts to be deleted
 */
$url = array();
$post = array();
$batch_size = 25;
$delay = 5;

$fb_enabled = $settings['fb_enabled'];
$tw_enabled = $settings['tw_enabled'];
$yt_enabled = $settings['yt_enabled'];

$q = sql_query("SELECT post_log_id FROM post_log 
					  LEFT JOIN users ON users.user_id = post_log.user_id AND users.account_status = 1
					  LEFT JOIN membership_plans ON membership_plans.plan_id = users.plan_id
					  LEFT JOIN token_expiry ON
					  token_expiry.social_id = post_log.social_id AND
					  (token_expiry.page_id = post_log.page_id OR token_expiry.page_id = post_log.social_id) AND
					  SUBSTRING(token_expiry.site, 1, 2) = SUBSTRING(post_log.site, 1, 2)
				  WHERE 
				  	  (	
					  	IF(users.fb_posting = 1 AND $fb_enabled AND post_log.site LIKE 'fb%' AND membership_plans.use_facebook = 1, 1, 0) = 1 OR 
					  	IF(users.tw_posting = 1 AND $tw_enabled AND post_log.site LIKE 'twitter' AND membership_plans.use_twitter = 1, 1, 0) = 1 OR 
						IF(users.yt_posting = 1 AND $yt_enabled AND post_log.site LIKE 'youtube' AND membership_plans.use_youtube = 1, 1, 0) = 1
					  ) AND
					  post_log.delete_at != '0000-00-00 00:00:00' AND
					  post_log.delete_at < NOW() AND
					  users.user_id IS NOT NULL AND
					  token_expiry.social_id IS NULL
				  LIMIT 500");
				  
while($res = sql_fetch_assoc($q)){
	$url[] = $settings['site_url'].'/api.php';
	$post[] = array('api_key' => API_KEY, 'cmd' => 'delete_post', 'log_id' => $res['post_log_id']);
	
	if(count($url) >= $batch_size){
		curl_multi($url, $post, 5);
		$url = array();
		$post = array();	
		sleep($delay);
	}
}

if(count($url)){
	curl_multi($url, $post, 5);
	$url = array();
	$post = array();				
}

sql_close();
unlock_process($lock);
exit(0);
?>