<?php
/**
 * @package Social Ninja
 * @version 1.3
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
include(dirname(dirname(__FILE__)).'/loader.php');

if(!empty($settings['disable_all_crons']) || !empty($settings['disable_poster_cron']) || !empty($settings['enable_maintenance_mode'])){
	exit(0);
	//die('Cron tasks disabled');
}
$set = 'pcron';
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
 * unlock locked schedules
 */
$t = microtime(true);
sql_query("UPDATE schedules SET is_locked = 0 WHERE locked_at <= DATE_SUB(NOW(), INTERVAL 60 MINUTE) AND is_locked = 1");
$e = microtime(true);
//do_log(sql_affected_rows()." locked schedules unlocked in ".($e - $t).' seconds');

/**
 * unlock limited schedules
 */
$t = microtime(true);
sql_query("UPDATE schedules SET next_post = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE next_post <= NOW() AND rate_limited_at <= DATE_SUB(NOW(), INTERVAL 60 MINUTE) AND rate_limited = 1 AND next_post != '0000-00-00 00:00:00'");
sql_query("UPDATE schedules SET rate_limited = 0, notes = '' WHERE rate_limited_at <= DATE_SUB(NOW(), INTERVAL 60 MINUTE) AND rate_limited = 1");
$e = microtime(true);
//do_log(sql_affected_rows()." limited schedules unlocked in ".($e - $t).' seconds');

//suspend missing schedules
sql_query("UPDATE schedules LEFT JOIN schedule_groups ON schedule_groups.schedule_group_id = schedules.schedule_group_id SET schedules.is_active = 2 WHERE schedule_groups.schedule_group_id IS NULL AND schedules.is_active != 2");

/**
 * select pending schedules to post
 */
$url = array();
$post = array();
$batch_size = 25;
$delay = 5;
$fb_enabled = $settings['fb_enabled'];
$tw_enabled = $settings['tw_enabled'];
$yt_enabled = $settings['yt_enabled'];

$q = sql_query("SELECT schedule_id FROM schedules 
					  LEFT JOIN users ON users.user_id = schedules.user_id AND users.account_status = 1
					  LEFT JOIN membership_plans ON membership_plans.plan_id = users.plan_id
					  LEFT JOIN post_counter ON 
					  	post_counter.user_id = users.user_id AND post_counter.post_count >= membership_plans.post_per_day AND 
						post_counter.today = CURDATE() AND post_counter.site = ''
					  LEFT JOIN token_expiry ON
					  token_expiry.social_id = schedules.social_id AND
					  (token_expiry.page_id = schedules.page_id OR token_expiry.page_id = schedules.social_id) AND
					  SUBSTRING(token_expiry.site, 1, 2) = SUBSTRING(schedules.site, 1, 2)
				  WHERE 
				  	  (	
					  	IF(users.fb_posting = 1 AND $fb_enabled AND schedules.site LIKE 'fb%' AND membership_plans.use_facebook = 1, 1, 0) = 1 OR 
					  	IF(users.tw_posting = 1 AND $tw_enabled AND schedules.site LIKE 'twitter' AND membership_plans.use_twitter = 1, 1, 0) = 1 OR 
						IF(users.yt_posting = 1 AND $yt_enabled AND schedules.site LIKE 'youtube' AND membership_plans.use_youtube = 1, 1, 0) = 1
					  ) AND
					  schedules.next_post <= NOW() AND schedules.next_post != '0000-00-00 00:00:00' AND
					  -- post_counter.user_id IS NULL AND
					  schedules.is_done = 0 AND 
					  schedules.is_active = 1 AND
					  schedules.rate_limited = 0 AND 
					  schedules.is_locked = 0 AND
					  users.user_id IS NOT NULL AND
					  token_expiry.social_id IS NULL
				  LIMIT 200");
				  
while($res = sql_fetch_assoc($q)){
	$url[] = $settings['site_url'].'/api.php';
	$post[] = array('api_key' => API_KEY, 'cmd' => 'process_schedule', 'schedule_id' => $res['schedule_id']);
	
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