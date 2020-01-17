<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$login_required = true;
include(dirname(__FILE__).'/loader.php');
$storage = __CURDIR__.'/tmp';
if(!is_dir(__CURDIR__))mkdir(__CURDIR__) or die('Failed to create storage dir');

if(!$settings['media_plugin_enabled']){
	$response['error'] = $lang['common'][48];
	output();
}

if(!$user_data['use_video_editor'] && !$user_data['use_image_editor']){
	$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['js']['editor'];
	output();
}

/**
 * verify storage
 */
$max_space = $user_data['allowed_storage'];
$used_space = $auth->get_user_used_space($user_id);

if($used_space >= $max_space){
	$response['error'] = $lang['ajax']['disk_consumed'].' : '.formatSize($max_space);
	output();	
}


/**
 * prepare variables
 */
$response = array();
$response['error'] = '';
$up_dir = $storage.'/';
if(!is_dir($up_dir))mkdir($up_dir);
/**
 * unlock session
 */
session_write_close();


/**
 * initialize uploader
 */
$uploader = new fileuploader();
$img = $allowed_image_ext;
$vid = $allowed_video_ext;

if(!empty($_POST['meme']))$vid = array();

$uploader->allowedExtensions = array_merge($img, $vid);
$uploader->sizeLimit = UPLOAD_MAX_SIZE;
$uploader->inputName = 'file';
$uploader->chunksFolder = 'chunks';

/**
 * check filesize with used space limits
 */
$size = $uploader->getSize();
if($used_space + $size >= $max_space){
	$response['error'] = $lang['ajax']['not_enough_space'].' : '.formatSize($max_space);
	output();	
}

/**
 * Check if edited image is requested to save
 */ 
if(!empty($_POST['saveCroppedImg'])){
	$plugin = $_POST['plugin'];
	$useName = strtok(basename($_POST['path']), '?');
	
	if($plugin != 'media'){
		$response['error'] = $lang['js']['invalid_request'];
		output();	
	}
	if(preg_match('/[^0-9a-z\.\_]/i', $useName)){
		$response['error'] = $lang['js']['invalid_request'];
		output();	
	}
	$up_dir = __ROOT__.'/plugins/media/tmp/';
}

/**
 * verify extension
 */
$org_name = $name = $uploader->getName();
if(!empty($useName))$org_name = $name = $useName;
$ext = strtolower(substr($name,strrpos($name,'.'),strlen($name)));

if(preg_match('/[^a-zA-Z0-9\.]/', $ext)){
	$response['error'] = $lang['ajax']['inv_file'];
	output();
}

/**
 * set a random name
 */
$jj = 0;
$name = rand(1,99999).'_'.rand(1,99999).'_'.rand(1,99999).$ext;
if(!empty($useName))$name = $useName;

if(in_array(trim($ext, '.'), $img)){
	$uploader->sizeLimit = 5*1024*1024; //5MB
	$file_type = 'image';
}
else if(in_array(trim($ext, '.'), $vid)){
	$file_type = 'video';
	if(!$user_data['use_video_editor']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['v_editor'];
		output();
	}
}
else{
	$response['error'] = $lang['ajax']['inv_file'];
	output();
}

/**
 * Check settings before upload
 */
if($file_type == 'image' && !$settings['image_editor_enabled']){
	$response['error'] = $lang['ajax']['i_edit_dis'];
	output();
}
if($file_type == 'image' && !$user_data['use_image_editor']){
	$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['i_editor'];
	output();
}
if($file_type == 'video' && !$settings['video_editor_enabled']){
	$response['error'] = $lang['ajax']['v_edit_dis'];
	output();
}
if($file_type == 'video' && !$user_data['use_video_editor']){
	$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['i_editor'];
	output();
}
if($file_type == 'video' && empty($settings['ffmpeg'])){
	$response['error'] = $lang['ajax']['ffmpeg_no_config'];
	output();
}

@unlink($up_dir.'/'.$name);
$response = $uploader->handleUpload($up_dir, $name);
$response["uploadName"] = $uploader->getUploadName();
$response["file_link"] = '';
$response["file_type"] = $file_type;
$response['orgName'] = $org_name;
if(empty($response['error']))$response['error'] = '';
/**
 * output
 */
output();