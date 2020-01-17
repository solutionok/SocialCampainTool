<?php
/**
 * API file
 * This script accepts post data to process single schedule
 * To protect from unauthorized post, we use API_KEY
 *
 * @package Social Ninja
 * @version 1.2
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
ignore_user_abort(true);
set_time_limit(1800);
ob_end_clean();
ob_start();
$size = ob_get_length();
header("Content-Length: $size");
header("Content-Encoding: none");
header("Connection: close");
ob_end_flush();
ob_flush();
flush();


include(dirname(__FILE__).'/loader.php');
session_write_close();
/**
 * PHP-CLI
 */
if(PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR']))
{
	$str = $argv[1];
	parse_str($str, $_POST);
}

/**
 * Check for valid api key
 */
if(empty($_POST['api_key']) || $_POST['api_key'] != API_KEY)die('Invalid api key');

switch($_POST['cmd']){
	case "process_schedule":
		$schedule_id = sql_real_escape_string($_POST['schedule_id']);
		new schedule($schedule_id);
	break;
	
	case "process_comment_bump":
		$post_log_id = sql_real_escape_string($_POST['post_log_id']);
		new comment($post_log_id);
	break;
	
	case "delete_post":
		$log_id = sql_real_escape_string($_POST['log_id']);
		$log_data = sql_fetch_assoc(sql_query("SELECT * FROM post_log WHERE post_log_id = '$log_id'"));
		
		$user_id = $log_data['user_id'];
		$page_id = $log_data['page_id'];
		$post_id = $log_data['post_id'];
		$site = $log_data['site'];
		if(empty($log_data['hid_action']))$log_data['hid_action'] = 'DELETE';
		
		if($site == 'fbpage' && !preg_match('/\_/', $post_id))$post_id = $page_id.'_'.$post_id;
		
		list($sid, $access_token, $uname) = $auth->is_id_owner($user_id, $page_id, $site);
		if(!empty($sid))$hid = new hid($user_id, $sid, $page_id, $post_id, $site, $access_token, strtolower($log_data['hid_action']));
	break;
	
	case "scrape_insights":
		$log_id = sql_real_escape_string($_POST['log_id']);
		$log_data = sql_fetch_assoc(sql_query("SELECT * FROM post_log WHERE post_log_id = '$log_id'"));
		
		$user_id = $log_data['user_id'];
		$page_id = $log_data['page_id'];
		$post_id = $log_data['post_id'];
		$site = $log_data['site'];
				
		list($sid, $access_token, $uname) = $auth->is_id_owner($user_id, $page_id, $site);
		if(!empty($sid))$stats = new stats($user_id, $sid, $page_id, $post_id, $site, $access_token);
	break;
}
?>