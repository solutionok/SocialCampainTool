<?php
/**
 * @package Social Ninja
 * @version 1.3
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
include(dirname(dirname(__FILE__)).'/loader.php');

if(!empty($settings['disable_all_crons']) || !empty($settings['disable_videoeditor_bumping_cron']) || !empty($settings['enable_maintenance_mode'])){
	exit(0);
	//die('Cron tasks disabled');
}
$set = 'msccron';
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

sql_query("DELETE FROM post_counter WHERE today != CURDATE()");

/**
 * limit schedules with high errors in last hour
 */
/*
$n = sql_real_escape_string("High number of API errors. Please view error logs and fix before posting");
$t = microtime(true);
sql_query("UPDATE schedules SET rate_limited = 1, rate_limited_at = NOW(), notes = '$n' WHERE
			(SELECT COUNT(*) 
			FROM error_msg 
			WHERE post_id = CONCAT('SCHEDULE_ID:', schedules.schedule_id) AND added_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)) >= 4 AND rate_limited = 0");
$e = microtime(true);
*/
//do_log(sql_affected_rows()." locked schedules unlocked in ".($e - $t).' seconds');

/**
 * Clear completed ones
 */
$t = microtime(true);
sql_query("UPDATE post_log LEFT JOIN schedules ON schedules.schedule_id = post_log.schedule_id AND (schedules.comment_bumps = '' OR schedules.comment_bumps = '[]') SET post_log.next_bump = '0000-00-00 00:00:00' WHERE schedules.schedule_id IS NOT NULL AND post_log.next_bump != '0000-00-00 00:00:00'");
$e = microtime(true);
//do_log(sql_affected_rows()." logs cleared in ".($e - $t).' seconds');

/**
 * end all campaigns that were scheduled for end
 */
$t = microtime(true);
sql_query("UPDATE schedule_groups LEFT JOIN schedules ON schedule_groups.schedule_group_id = schedules.schedule_group_id SET schedule_groups.is_done = 1, schedules.is_done = 1, schedule_groups.next_post = '0000-00-00 00:00:00', schedules.next_post = '0000-00-00 00:00:00' WHERE schedule_groups.post_end_at <= NOW() AND schedule_groups.post_end_at != '0000-00-00' AND schedule_groups.post_end_at != '0000-00-00 00:00:00'");
$e = microtime(true);
//do_log(sql_affected_rows()." locked schedules marked done in ".($e - $t).' seconds');

/**
 * select pending schedules to comment bump
 */
if(!empty($settings['fb_enabled'])){
	$url = array();
	$post = array();
	$batch_size = 25;
	$delay = 5;
	
	$q = sql_query("SELECT post_log.post_log_id FROM schedules
						  LEFT JOIN post_log ON post_log.schedule_id = schedules.schedule_id
						  LEFT JOIN users ON users.user_id = schedules.user_id AND users.account_status = 1
						  LEFT JOIN membership_plans ON membership_plans.plan_id = users.plan_id
						  LEFT JOIN token_expiry ON
						  token_expiry.social_id = schedules.social_id AND
						  (token_expiry.page_id = schedules.page_id OR token_expiry.page_id = schedules.social_id) AND
						  SUBSTRING(token_expiry.site, 1, 2) = SUBSTRING(schedules.site, 1, 2)
					  WHERE 
						  users.fb_posting = 1 AND
						  (schedules.site = 'fbgroup' OR schedules.site = 'fbevent') AND 
						  post_log.next_bump <= NOW() AND 
						  post_log.next_bump != '0000-00-00 00:00:00' AND 
						  schedules.comment_bumps != '' AND
						  schedules.comment_bumps != '[]' AND
						  schedules.is_active = 1 AND 
						  token_expiry.social_id IS NULL AND
						  post_log.hid_status = 0 AND
						  users.user_id IS NOT NULL AND
						  membership_plans.use_advanced_scheduling = 1 AND
						  membership_plans.use_facebook = 1
					  LIMIT 500");
					  
	while($res = sql_fetch_assoc($q)){
		$url[] = $settings['site_url'].'/api.php';
		$post[] = array('api_key' => API_KEY, 'cmd' => 'process_comment_bump', 'post_log_id' => $res['post_log_id']);
		
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
}
/**
 * Video editing
 */
if($settings['video_editor_enabled'] && $settings['media_plugin_enabled']){
	sql_query("UPDATE video_editor_queue SET is_locked = 0 WHERE is_locked = 1 AND locked_at < DATE_SUB(NOW(), INTERVAL 2 HOUR)");
	
	$q = sql_query("SELECT 
					video_editor_queue.*, users.storage  
					FROM video_editor_queue 
					LEFT JOIN users ON users.user_id = video_editor_queue.user_id AND users.account_status = 1 
					LEFT JOIN membership_plans ON membership_plans.plan_id = users.plan_id 
					WHERE video_editor_queue.is_locked = 0 AND video_editor_queue.is_done = 0 AND membership_plans.use_video_editor = 1 AND users.user_id IS NOT NULL 
					ORDER BY video_editor_queue.added_at ASC LIMIT 50");
	
	while($res = sql_fetch_assoc($q)){
		$qid = $res['queue_id'];
		
		sql_query("UPDATE video_editor_queue SET is_locked = 1, locked_at = NOW() WHERE queue_id = '$qid'");
		
		$video = $res['video_file'];	
		$tasks = json_decode($res['tasks'], true);
		$chunks = json_decode($res['chunks'], true);
		$storage = $res['storage'];
		
		if(empty($video))$v = 'slideshow';
		else $v = $video;
		
		$ffmpeg = new ffmpeg($v);
		
		if($video){
			$ffmpeg->check_video();
			if($ffmpeg->error){
				sql_query("UPDATE video_editor_queue SET is_done = 2, notes = '$ffmpeg->error' WHERE queue_id = '$qid'");
				continue;	
			}
		}
		foreach($tasks as $t){
			if($t['type'] == 'screenshot' && $video){
				$ffmpeg->create_screenshot(array($t['rel']));
			}
			else if($t['type'] == 'tile' && $video){
				$tsize = explode('x', $t['rel']);
				$ffmpeg->create_tiles($tsize[0], $tsize[1]);
			}
			else if($t['type'] == 'cut' && $video){
				$cuts = array();
				$segs = explode(',', $t['rel']);
				foreach($segs as $seg)$cuts[] = array($chunks[$seg]['start'], $chunks[$seg]['end']);
				if(!empty($cuts))$ffmpeg->create_chunks($cuts);
			}
			else if($t['type'] == 'join' && $video){
				$joins = array();
				$segs = explode(',', $t['rel']);
				foreach($segs as $seg)$joins[] = array($chunks[$seg]['start'], $chunks[$seg]['end']);
				if(!empty($joins))$ffmpeg->join_segments($joins);
			}
			else if($t['type'] == 'slideshow'){
				$slide_params = explode('|', $t['rel']);
				$fid = sql_real_escape_string($slide_params[0]);
				
				$files = array();
				$q = sql_query("SELECT filename FROM files WHERE folder_id = '$fid' AND file_type = 'image' LIMIT 200");
				while($res2 = sql_fetch_assoc($q)){
					$files[] = __ROOT__.'/storage/'.$storage.'/'.$res2['filename'];	
				}
				
				if(empty($slide_params[2])){
					$ss = get_available_slideshow_type(-1);
					$ss = shuffle_assoc($ss);
					$slide_params[2] = key($ss);	
				}
				
				if(!empty($files)){
					$v = $ffmpeg->create_slideshow($files, $slide_params[1], $slide_params[2]);	
					if(!$v)sql_query("UPDATE video_editor_queue SET notes = 'SLIDESHOW_FAILED' WHERE queue_id = '$qid'");
				}
				else sql_query("UPDATE video_editor_queue SET notes = 'SLIDESHOW_NO_IMAGE' WHERE queue_id = '$qid'");
			}	
		}
		
		/**
		 * Reconnect to mysql in case connection is dropped after a delay
		 */
		sql_conn();
		
		if($video && !empty($res['delete_source']))@unlink($video);
		$z = '';
		if(is_dir($ffmpeg->ot_dir) && !preg_match('/[^0-9]/', basename($ffmpeg->ot_dir))){
			$ffmpeg->clean_logs();
			$zip = __STORAGE__.'/'.$storage.'/'.$res['user_id'].'_'.$qid.'_video_edit.zip';
			create_zip($ffmpeg->ot_dir, $zip);
			
			if(file_exists($zip)){
				$z = basename($zip);	
				$auth->get_user_used_space($res['user_id']);	
			}
			rrmdir($ffmpeg->ot_dir);	
		}
		
		sql_query("UPDATE video_editor_queue SET download_file = '$z', is_done = 1 WHERE queue_id = '$qid'");
	}
}

/**
 * Clear temp files
 */
$d = dirname(dirname(__FILE__)).'/tmp';
$f = scandir($d);
foreach($f as $ff){
	if($ff == '.' || $ff == '..' || $ff == '.htaccess' || $ff == 'index.php')continue;
	$c = filectime($d.'/'.$ff);
	if($c > time() - 1*3600)continue;
	if(preg_match('/_wm_\.jpg/i', $ff)){
		if($c > time() - 3*24*3600)continue;	
	}
	else if(preg_match('/_wm_\.mp4/i', $ff)){
		if($c > time() - 7*24*3600)continue;	
	}
	else if(preg_match('/_slideshow/i', $ff)){
		if($c > time() - 7*24*3600)continue;
	}
	@unlink($d.'/'.$ff);
}

$d = dirname(dirname(__FILE__)).'/logs';
$f = scandir($d);
foreach($f as $ff){
	if($ff == '.' || $ff == '..' || $ff == '.htaccess' || $ff == 'index.php')continue;
	$s = filesize($d.'/'.$ff);
	if($s < 1*1024*1024)continue;
	@unlink($d.'/'.$ff);
}

$d = dirname(dirname(__FILE__)).'/plugins/media/tmp';
$f = scandir($d);
foreach($f as $ff){
	if($ff == '.' || $ff == '..' || $ff == '.htaccess' || $ff == 'index.php')continue;
	$c = filectime($d.'/'.$ff);
	if($c > time() - 3*3600)continue;
	@unlink($d.'/'.$ff);
}

sql_close();
unlock_process($lock);
exit(0);
?>