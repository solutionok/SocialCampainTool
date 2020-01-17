<?php
/**
 * @package Social Ninja
 * @version 1.2
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
$require_login = 1;
include(dirname(__FILE__)."/loader.php");
if(!is_dir(__STORAGE__))mkdir(__STORAGE__) or die('Failed to create storage dir');

/**
 * prepare variables
 */
$response = array();
$response['error'] = '';
$folder_id = sql_real_escape_string(@$_POST['folder_id']);
$caption = @$_POST['caption'];
$up_dir = __STORAGE__.'/'.$user_data['storage'].'/';
if(!is_dir($up_dir))mkdir($up_dir);

/**
 * whether uploaded file is watermark or frame
 */
$wm = empty($_POST['wm']) ? 0 : 1;
$frame = empty($_POST['frame']) ? 0 : 1;

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
 * verify folder ownership
 */
if(!$wm && !$frame && !$auth->is_folder_owner($user_id, $folder_id)){
	$response['error'] = $lang['ajax']['edit_not_allowed'].' '.$lang['ajax']['folder'];
	output();		
}
else{
	/**
	 * Max 100 watermark or frames allowed
	 */
	if($wm){
		list($t) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM creator_tools WHERE tool_type = 'watermark' AND user_id = '$user_id'"));
	}
	else{
		list($t) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM creator_tools WHERE tool_type = 'frame' AND user_id = '$user_id'"));
	}
	if($t >= 100){
		$response['error'] = $lang['ajax']['max_wm_frame_added'];
		output();
	}
}

/**
 * unlock session
 */
session_write_close();


/**
 * initialize uploader
 */
$uploader = new fileuploader();

if(!$wm && !$frame)$uploader->allowedExtensions = array_merge($allowed_image_ext, $allowed_video_ext);
else $uploader->allowedExtensions = array_merge($allowed_image_ext);

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
 * verify extension
 */
$org_name = $name = $uploader->getName();
$ext = strtolower(substr($name,strrpos($name,'.'),strlen($name)));

if(preg_match('/[^a-zA-Z0-9\.]/', $ext)){
	$response['error'] = $lang['ajax']['inv_file_name'];
	output();
}

/**
 * set a random name
 */
$jj = 0;
$name = rand(1,99999).'_'.rand(1,99999).'_'.rand(1,99999).$ext;
if(empty($caption) && !empty($_POST['use_name_as_cap']))$caption = str_replace($ext, '', $org_name);
$caption = purify_text($caption);
$org_name = purify_text($org_name);

if(in_array(trim($ext, '.'), $allowed_image_ext)){
	$uploader->sizeLimit = IMAGE_UPLOAD_MAX_SIZE; //5MB
	$file_type = 'image';
}
else if(in_array(trim($ext, '.'), $allowed_video_ext))$file_type = 'video';
else{
	$response['error'] = $lang['ajax']['inv_file'];
	output();
}

list($exists) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM files WHERE filename = '".sql_real_escape_string($name)."' AND user_id = '$user_id'"));
while($exists){
	$name = rand(1,99999).'_'.rand(1,99999).'_'.rand(1,99999).$ext;
	list($exists) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM files WHERE filename = '".sql_real_escape_string($name)."' AND user_id = '$user_id'"));
	usleep(100);
	if($jj++ > 100){
		$response['error'] = $lang['ajax']['un_err'];
		output();	
	}
}

$response = $uploader->handleUpload($up_dir, $name);
$response["uploadName"] = $uploader->getUploadName();
$response["fileId"] = '';
$response["thumb"] = '';
$response["file_link"] = '';
$response["file_type"] = $file_type;
$response["folderId"] = $folder_id;
$response['orgName'] = $org_name;
if(empty($response['error']))$response['error'] = '';

/**
 * output
 */
if(empty($response['error'])){
	
	$sfile_link = $user_data['storage'].'/'.$name;	
	$response["thumb"] = $response["file_link"] = 'storage/'.$sfile_link;
	
	/**
	 * Regular upload
	 */
	if(!$wm && !$frame){	
		list($pos) = sql_fetch_row(sql_query("SELECT MAX(position)+1 FROM files WHERE folder_id = '$folder_id'"));
		if(empty($pos))$pos = 1;
		
		/**
		 * mysql insert
		 */
		sql_query("INSERT INTO files (user_id, folder_id, filename, original_name, caption, file_type, added_at, position) 
								VALUES ('$user_id', 
										'$folder_id', 
										'".sql_real_escape_string($name)."', 
										'".sql_real_escape_string($org_name)."', 
										'".sql_real_escape_string($caption)."', 
										'$file_type', 
										NOW(), 
										'$pos'
										)
					");
					
		$response["fileId"] = sql_insert_id();
		sql_query("UPDATE folders SET file_count = file_count + 1 WHERE folder_id = '$folder_id'");	
	}
	
	/**
	 * wm/frame upload
	 */
	else{
		/**
		 * mysql insert
		 */
		sql_query("INSERT INTO creator_tools (user_id, filename, tool_type, original_name) 
								VALUES ('$user_id', 
										'".sql_real_escape_string($name)."', 
										'".($wm ? 'watermark' : 'frame')."'	,
										'".sql_real_escape_string($org_name)."'									
										)
					");
					
		$response["fileId"] = sql_insert_id();
		output();	
	}
	
	/**
	 * image files should get the file as thumb
	 */
	if($file_type == 'image'){
		sql_query("UPDATE folders SET thumb = '".sql_real_escape_string($sfile_link)."' WHERE folder_id = '$folder_id'");
	}
	/**
	 * create thumb using ffmpeg for video
	 */
	else if(!empty($settings['ffmpeg'])){		
		$in_path = $up_dir.'/'.$name;
		$out_path = $in_path.'.png';
		
		$ff = new ffmpeg('');
		$data = $ff->analyze_video($in_path);
		$duration = (float)$data['duration'];
		$response["duration"] = pretty_time($duration);
		
		if($duration){
			$thumb = '';
			$cmd = $settings['ffmpeg'].' -ss '.rand(1, (int)$duration).' -i '.$in_path.' -y -f image2 -vcodec mjpeg -vframes 1 '.$out_path;
			exec($cmd, $o, $c);		
			if(!$c && file_exists($out_path)){
				$sthumb_link = $user_data['storage'].'/'.basename($in_path).'.png';
				sql_query("UPDATE folders SET thumb = '".sql_real_escape_string($sthumb_link)."' WHERE folder_id = '$folder_id'");
				$response["thumb"] = 'storage/'.$sthumb_link;
			}
			else $response["thumb"] = 'images/video.png';
			sql_query("UPDATE files SET duration = '$duration' WHERE file_id = '".$response["fileId"]."'");
		}
		else $response["thumb"] = 'images/video.png';
	}
	else{
		$response["thumb"] = 'images/video.png';
	}
	
	$response['caption'] = nl2br(wordwrap($caption, 20));
	if(strlen($response['caption']) > 100)$response['caption'] = substr($response['caption'], 0, 100).'...';
	$response['org_caption'] = $caption;
	$response['org_name'] = $org_name;
}
output();