<?php
/**
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
$root = dirname(dirname(__FILE__));
include($root.'/functions.php');
error_reporting(0);

function _sql_conn($db_host, $db_user, $db_pwd, $db_name)
{				
	global $mysqli;	
	if($mysqli)@mysqli_close($mysqli);
	
	$mysqli = @mysqli_connect($db_host, $db_user, $db_pwd);
	if(!$mysqli)return false;
	
	$db = @mysqli_select_db($mysqli, $db_name);
	if(!$db)return false;
	
	$charset = @mysqli_set_charset($mysqli, 'utf8mb4');
	if(!$charset)$charset = @mysqli_set_charset($mysqli, 'utf8');
	if(!$charset)return false;
	
	return $mysqli;
}
function auto_db_connect($file)
{
	global $response, $mysqli;
	$d = @file_get_contents($file);
	if(preg_match('/define\(\'DB_HOST\', \'(.*)\'\);/', $d, $m))$db_host = $m[1];
	else{
		$response['error'] = 'Mysql data parse failed';
		output();
	}
	if(preg_match('/define\(\'DB_USER\', \'(.*)\'\);/', $d, $m))$db_user = $m[1];
	else{
		$response['error'] = 'Mysql data parse failed';
		output();
	}
	if(preg_match('/define\(\'DB_PASS\', \'(.*)\'\);/', $d, $m))$db_pwd = $m[1];
	else{
		$response['error'] = 'Mysql data parse failed';
		output();
	}
	if(preg_match('/define\(\'DB_NAME\', \'(.*)\'\);/', $d, $m))$db_name = $m[1];
	else{
		$response['error'] = 'Mysql data parse failed';
		output();
	}
	$mysqli = _sql_conn($db_host, $db_user, $db_pwd, $db_name);
	
	if(!$mysqli){
		$response['error'] = 'Mysql connection failed';
		output();
	}
}

$mysqli = false;
$response = array();
$response['error'] = '';

if(!empty($_POST['db_test'])){
	$db_host = $_POST['db_host'];
	$db_user = $_POST['db_user'];
	$db_pwd = $_POST['db_pwd'];
	$db_name = $_POST['db_name'];
	
	$mysqli = @sql_connect($db_host, $db_user, $db_pwd);
	if(!$mysqli){
		$response['error'] = 'Mysql connection failed. '.sql_connect_error();
		output();	
	}
	
	$r = @sql_select_db($db_name);
	if(!$r){
		$response['error'] = 'Mysql database selection failed. '.sql_error();
		output();	
	}
	output();
}
if(!empty($_POST['db_setup'])){
	$db_host = $_POST['db_host'];
	$db_user = $_POST['db_user'];
	$db_pwd = $_POST['db_pwd'];
	$db_name = $_POST['db_name'];
	
	$mysqli = @sql_connect($db_host, $db_user, $db_pwd);
	if(!$mysqli){
		$response['error'] = 'Mysql connection failed';
		output();	
	}
	
	$r = @sql_select_db($db_name);
	if(!$r){
		$response['error'] = 'Mysql database selection failed';
		output();	
	}
	
	$sql = file_get_contents(dirname(__FILE__).'/db.sql');
	$d = explode('-- --------------------------------------------------------', $sql);	
	$j = 0;
	$table_no = 25;
	
	foreach($d as $dd){
		$dd = trim($dd);
		if(empty($dd))continue;
		sql_query($dd);
		$err = sql_error();
		if($err){
			$response['error'] = 'Mysql error: '.$err;
			output();	
		}
		$j++;
	}
	
	if($j != $table_no){
		$response['error'] = 'Database import failed';
		output();	
	}
	
	$config = file_get_contents(dirname(__FILE__).'/config.sample.php');
	$config = str_replace(array('--DB_HOST--', '--DB_USER--', '--DB_PASS--', '--DB_NAME--'), array($db_host, $db_user, $db_pwd, $db_name), $config);
	file_put_contents(dirname(__FILE__).'/config1.build.php', $config);
	file_put_contents(dirname(__FILE__).'/step.txt', 2);
	
	output();
}
else if(!empty($_POST['misc_setup'])){
	$cf = dirname(__FILE__).'/config1.build.php';
	if(!file_exists($cf)){
		$response['error'] = 'Failed to load previous settings';
		@unlink(dirname(__FILE__).'/step.txt');
		output();	
	}
	auto_db_connect($cf);
	
	$site_url = sql_real_escape_string($_POST['site_url']);
	$site_name = sql_real_escape_string($_POST['site_name']);
	$ffmpeg = $_POST['ffmpeg'];
	
	$seo_url = sql_real_escape_string((int)$_POST['seo_url']);
	$fb_enabled = sql_real_escape_string((int)$_POST['fb_enable']);
	$tw_enabled = sql_real_escape_string((int)$_POST['tw_enable']);
	$yt_enabled = sql_real_escape_string((int)$_POST['yt_enable']);
	$downloader_plugin = sql_real_escape_string((int)$_POST['downloader_plugin']);
	$media_plugin = sql_real_escape_string((int)$_POST['media_plugin']);
	$img_wm = sql_real_escape_string((int)$_POST['img_wm']);
	$video_wm = sql_real_escape_string((int)$_POST['video_wm']);
	$img_edit = sql_real_escape_string((int)$_POST['img_edit']);
	$video_edit = sql_real_escape_string((int)$_POST['video_edit']);
	$enable_signup = sql_real_escape_string((int)$_POST['enable_signup']);
	$meta_desc = sql_real_escape_string($_POST['meta_desc']);
	$meta_keys = sql_real_escape_string($_POST['meta_keys']);
	$paypal_email = sql_real_escape_string($_POST['paypal_email']);
	$admin_email = sql_real_escape_string($_POST['admin_email']);
	
	if(!empty($ffmpeg)){
		exec($ffmpeg.' -version', $o, $c);
		if($c){
			$response['error'] = 'ffmpeg call failed. error code: '.$c;
			output();	
		}
	}
	
	$ffmpeg = sql_real_escape_string($ffmpeg);
	
	sql_query("INSERT INTO global_config (site_url, site_theme, site_name, ffmpeg, seo_url, fb_enabled, tw_enabled, yt_enabled, downloader_plugin_enabled, media_plugin_enabled, image_watermarking_enabled, video_watermarking_enabled, image_editor_enabled, video_editor_enabled, enable_signup, meta_description, meta_keywords, paypal_email, admin_email) VALUES('$site_url', 'united', '$site_name', '$ffmpeg', '$seo_url', '$fb_enabled', '$tw_enabled', '$yt_enabled', '$downloader_plugin', '$media_plugin', '$img_wm', '$video_wm', '$img_edit', '$video_edit', '$enable_signup', '$meta_desc', '$meta_keys', '$paypal_email', '$admin_email')");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = 'Mysql entry failed '.sql_error();	
	}
	else{
		$sname = 'pro_uid_'.rand(1111, 9999);
		$api_key = sha1(rand().time().rand());
		$config = file_get_contents($cf);
		$config = str_replace(array('--SESSION_NAME--', '--API_KEY--'), array($sname, $api_key), $config);
		file_put_contents(dirname(__FILE__).'/config2.build.php', $config);
		file_put_contents(dirname(__FILE__).'/step.txt', 3);	
	}
	output();
}
else if(!empty($_POST['app_setup'])){
	$cf = dirname(__FILE__).'/config2.build.php';
	if(!file_exists($cf)){
		$response['error'] = 'Failed to load previous settings';
		@unlink(dirname(__FILE__).'/step.txt');
		output();	
	}
	auto_db_connect($cf);
	
	$settings = sql_fetch_assoc(sql_query("SELECT * FROM global_config"));
	if(empty($settings)){
		$response['error'] = 'Failed to load config';
		output();	
	}
	
	$fb_app_id = sql_real_escape_string($_POST['fb_app_id']);
	$fb_app_secret = sql_real_escape_string($_POST['fb_app_secret']);
	$fb_app_token = '';
	
	if((empty($fb_app_id) && !empty($fb_app_secret)) || (!empty($fb_app_id) && empty($fb_app_secret))){
		$response['error'] = 'Facebook app id and secret both required';
		output();	
	}
	
	if(!empty($fb_app_id) && !empty($fb_app_secret)){
		$fb_app_token = configure_fb_app($fb_app_id, $fb_app_secret);
		if(empty($fb_app_token)){
			$response['error'] = 'Failed to configure facebook app';
			output();	
		}
	}
	
	$tw_app_id = sql_real_escape_string($_POST['tw_app_id']);
	$tw_app_secret = sql_real_escape_string($_POST['tw_app_secret']);
	
	if((!empty($tw_app_id) && empty($tw_app_secret)) || (empty($tw_app_id) && !empty($tw_app_secret))){
		$response['error'] = 'Twitter app id and secret both required';
		output();
	}
	
	$yt_client_id = sql_real_escape_string($_POST['yt_client_id']);
	$yt_client_secret = sql_real_escape_string($_POST['yt_client_secret']);
	$yt_dev_key = sql_real_escape_string($_POST['yt_dev_key']);
	
	$ee = 0;
	$ne = 0;
	$aa = array('ytc' => $yt_client_id, 'yts' => $yt_client_secret, 'ytd' => $yt_dev_key);
	foreach($aa as $a){
		if(!empty($a))$ne = 1;
		else $ee = 1;	
	}
	
	if($ne && $ee){
		$response['error'] = 'Youtube app id, secret and dev key all required';
		output();	
	}
	
	$fb_scope = 'email,manage_pages,publish_actions,publish_pages,read_insights,user_managed_groups,user_posts,user_events,user_photos,user_videos';
	
	sql_query("UPDATE global_config SET fb_app_id = '$fb_app_id', fb_app_secret = '$fb_app_secret', tw_app_id = '$tw_app_id', tw_app_secret = '$tw_app_secret', yt_client_id = '$yt_client_id', yt_client_secret = '$yt_client_secret', yt_dev_token = '$yt_dev_key', fb_scope = '$fb_scope'");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = 'Mysql entry failed '.sql_error();	
	}
	else{
		file_put_contents(dirname(__FILE__).'/step.txt', 4);	
	}
	output();
}
else if(!empty($_POST['add_user'])){
	$cf = dirname(__FILE__).'/config2.build.php';
	if(!file_exists($cf)){
		$response['error'] = 'Failed to load previous settings';
		@unlink(dirname(__FILE__).'/step.txt');
		output();	
	}
	auto_db_connect($cf);
	
	$email = sql_real_escape_string(trim(strtolower($_POST['email'])));
	$password = sql_real_escape_string($_POST['password']);
	
	if(empty($email) || empty($password)){
		$response['error'] = 'Email and password both required';
		output();	
	}
	
	if(!preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $email)){
		$response['error'] = 'Invalid Email';
		output();	
	}
	
	if(strlen($password) < 6){
		$response['error'] = 'Password must be at least 6 characters long';
		output();
	}
	
	if(sql_num_rows(sql_query("SELECT NULL FROM users WHERE email = '$email'"))){
		$response['error'] = 'This email is already registered with another account';
		output();
	}
	
	$plan_features = array(	'Maximum storage {$allowed_storage}|0',
							'Maximum {$post_per_day} posts per day|0',
							'Maximum {$facebook_post_per_day} facebook posts  per day|0',
							'Maximum {$twitter_post_per_day} twitter posts  per day|0',
							'Maximum {$youtube_post_per_day} youtube posts  per day|0',
							'Maximum {$social_profile_limit_per_site} profile per site|0',
							'Create upto {$folder_limit} folders|0',
							'Maximum allowed schedule {$schedule_limit}|0',
							'Add upto {$rss_feed_limit} rss feeds|1',
							'Manage upto {$page_group_event_limit} pages|1'
						);
						
	$plan_features = sql_real_escape_string(json_encode($plan_features));
	
	sql_query("INSERT INTO `membership_plans` 
				(`plan_id`, 
				`plan_name`, 
				`folder_limit`, 
				`schedule_limit`, 
				`schedule_group_limit`, 
				`social_profile_limit_per_site`, 
				`page_group_event_limit`, 
				`rss_feed_limit`, 
				`use_feed_cleaner`, 
				`allowed_storage`, 
				`use_advanced_scheduling`, 
				`use_image_editor`, 
				`use_video_editor`, 
				`use_image_watermark`, 
				`use_video_watermark`, 
				`use_image_downloader`, 
				`use_video_downloader`, 
				`use_meme_generator`, 
				`use_html_image_creator`, 
				`use_album_post`, 
				`use_slideshow`, 
				`post_per_day`, 
				`facebook_post_per_day`, 
				`twitter_post_per_day`, 
				`youtube_post_per_day`, 
				`use_facebook`, 
				`use_twitter`, 
				`use_youtube`, 
				`use_group_event_importer`,
				`plan_subtitle`,
				`display_on_site`,
				`plan_features`) 
				VALUES 
				(1, 'Basic', 50, 100, 50, 3, 200, 50, 1, 314572800, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 300, 100, 100, 100, 1, 1, 1, 1, 'Best for starter', 1, '$plan_features')"
	);

	if(sql_affected_rows() <= 0){
		$response['error'] = 'Mysql entry failed '.sql_error();
		output();	
	}
	
	sql_query("INSERT INTO users (email, password, is_admin, fb_posting, yt_posting, tw_posting, account_status, plan_id) VALUES('$email', SHA1('$password'), 1, 1, 1, 1, 1, 1)");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = 'Mysql entry failed '.sql_error();	
	}
	else{
		file_put_contents(dirname(__FILE__).'/step.txt', 5);
		$id = sql_insert_id();
		$storage = $id.'_'.rand().rand().rand();
		sql_query("UPDATE users SET storage = '$storage' WHERE user_id = '$id'");	
	}
	output();
}
else if(!empty($_POST['finish_setup'])){
	
	/**
	 * htaccess copy
	 */
	$htacess = file_get_contents(dirname(__FILE__).'/htaccess.txt');
	
	$d = dirname(dirname($_SERVER['PHP_SELF']));
	$d = rtrim($d, '/');
	$d = rtrim($d, '\\');
	$d = str_replace('\\', '/', $d);
	if(empty($d))$d = '/';
	
	$htacess = str_replace('__BASE__', $d, $htacess);
	
	file_put_contents($root.'/.htaccess', $htacess);
	
	/**
	 * Safety when htaccess is disabled
	 */
	unlink(dirname(__FILE__).'/index.php');
	unlink(dirname(__FILE__).'/ajax.php');
	file_put_contents(dirname(__FILE__).'/index.php', 'Disabled');
	
	/**
	 * Astalavista 
	 */
	$a = copy(dirname(__FILE__).'/config2.build.php', $root.'/config.php');
	if(!$a){
		$response['error'] = 'Config file copy failed';
		output();	
	}
	copy(dirname(__FILE__).'/deny.txt', dirname(__FILE__).'/.htaccess');
	
	/**
	 * Cron setup
	 */
	 
	require(dirname(__FILE__).'/config2.build.php');
	
	$cron = get_cron_task_list();
	file_put_contents(dirname(__FILE__).'/crons.txt', implode(PHP_EOL, $cron));

	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
		$response['error'] = 'Cannot add cron task on windows server. Please use task scheduler to configure the tasks manually';
		$response['cron'] = $cron;
	} 
	else {
		$lines = array();
		foreach($cron as $i => $c){
			$lines[] = 'php '.$cron[$i].' >/dev/null 2>&1';	
		}
		
		file_put_contents(dirname(__FILE__).'/crons.txt', PHP_EOL.PHP_EOL.implode(PHP_EOL, $lines), FILE_APPEND);
		file_put_contents(dirname(__FILE__).'/crons.txt', PHP_EOL.PHP_EOL.'* * * * * '.implode(PHP_EOL.'* * * * * ', $lines), FILE_APPEND);
		rename(dirname(__FILE__).'/crons.txt', dirname(__FILE__).'/crons_'.rand(11111, 99999).rand().rand().'.txt');
		
		$path = get_path_to_crontab();
		if(empty($path)){
			$response['error'] = 'Failed to fetch cron tasks';
			$response['cron'] = $cron;
			$response['lines'] = $lines;
			output();
		}
		
		$output = shell_exec($path.' -l');
		
		/**
		 * If cron tasks already added remove them - disabled to allow multiple installation on same server
		 */
		$output = preg_replace('/###SOCIALNINJA_CRONS_START_'.SESSION_NAME.'###(.*)###SOCIALNINJA_CRONS_END_'.SESSION_NAME.'###/s', '', $output);
		
		/**
		 * Add new tasks
		 */
		$output .= "###SOCIALNINJA_CRONS_START_".SESSION_NAME."###".PHP_EOL;
		foreach($lines as $l){
			$output = $output.'* * * * * '.$l.PHP_EOL;
		}
		$output .= "###SOCIALNINJA_CRONS_END_".SESSION_NAME."###".PHP_EOL;
		
		file_put_contents(dirname(__FILE__).'/crontab_new.txt', $output);
		exec($path.' '.dirname(__FILE__).'/crontab_new.txt', $o, $c);
		rename(dirname(__FILE__).'/crontab_new.txt', dirname(__FILE__).'/crontab_new_'.rand(11111, 99999).rand().rand().'.txt');
		
		if($c){
			$response['error'] = 'Failed to add cron tasks';
			$response['cron'] = $cron;
			$response['lines'] = $lines;
		}
		output();
	}
	output();
}

$response['error'] = 'Data missing or invalid request';
output();
?>