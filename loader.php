<?php
/**
 * Configurations and includes loader
 *
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

/**
 * start php session
 */
@session_start();
@ob_start();
/**
 * check if config file exists otherwise redirect to setup
 */
if(!file_exists(dirname(__FILE__).'/config.php')){
	header('location: setup');
	exit();	
}

/**
 * load config file and other necessary files and settings
 */
include(dirname(__FILE__).'/functions.php');
include(dirname(__FILE__).'/config.php');

$mysqli = false;
$mysqli = sql_conn();
if(empty($mysqli)){
	die("<h1>Mysql connection failed</h1>");	
}
$settings = load_settings();
/**
 * class autoloader register
 */
spl_autoload_register('spl_autoloader');

/**
 * check authentication
 */
$user_id = 0;
$auth = new auth();
$user_data = $auth->check_login();
if(empty($user_data) || is_numeric($user_data)){
	$user_data = array();
	$is_logged_in = 0;
}
else{
	$is_logged_in = 1;
	$user_id = $_SESSION[SESSION_NAME];
	load_app_settings($user_id);
}
/**
 * check authentication requires or not and take actions accordingly
 */
if(!empty($login_required) && !$is_logged_in){
	redirect('login.php?r='.base64_encode($_SERVER['REQUEST_URI']));	
}
if(!empty($logout_required) && $is_logged_in){
	redirect('dashboard.php');	
}
if(!empty($admin_required)){
	if(!$is_logged_in)redirect('login.php?r='.base64_encode($_SERVER['REQUEST_URI']));
	else if(empty($user_data['is_admin']))redirect('dashboard.php');	
}

/**
 * Set timezone
 */
if(empty($user_data['time_zone'])){
	date_default_timezone_set('UTC');
}
else{
	date_default_timezone_set($user_data['time_zone']);
}

/**
 * Set language
 */
$default_lang_exists = file_exists(dirname(__FILE__).'/lang/default.php');
if(!empty($_COOKIE['ninja_lang'])){
	$lang_ok = 0;
	if(!preg_match('/[^a-z0-9\_]/i', $_COOKIE['ninja_lang'])){
		$lang_ok = list_lang_files($_COOKIE['ninja_lang']);
		if($lang_ok){
			require_once(dirname(__FILE__).'/lang/'.$_COOKIE['ninja_lang'].'.php');	
		}
	}
	if(!$lang_ok){
		if($default_lang_exists)require_once(dirname(__FILE__).'/lang/default.php');
		else require_once(dirname(__FILE__).'/lang/en.php');
	}
}
else{
	if($default_lang_exists)require_once(dirname(__FILE__).'/lang/default.php');
	else require_once(dirname(__FILE__).'/lang/en.php');	
}

sql_query("UPDATE users SET plan_id = 1 WHERE membership_expiry_time <= NOW() AND plan_id != 1");	
$uri = $_SERVER['PHP_SELF'];

$is_index_page = 0;
$is_login_page = 0;
if(preg_match('/index\.php/i', $uri))$is_index_page = 1;
if(preg_match('/login\.php|logout\.php/i', $uri))$is_login_page = 1;

if(!empty($settings['enable_maintenance_mode'])){
	if(!$is_index_page && !$is_login_page && empty($user_data['is_admin'])){
		redirect(makeuri('index.php', 1));	
	}
}
?>