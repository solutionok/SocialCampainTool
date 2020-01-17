<?php
/**
 * Configuration file
 * Loads mysql and other hard coded configuration values
 *
 * @package Social Ninja
 * @version 1.8
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
error_reporting(E_ALL);
define('S_NINJA', 1);
define('__ROOT__', rtrim(str_replace('\\', '/',dirname(__FILE__)), '/').'/');
define('__STORAGE__', __ROOT__.'/storage');
define('__BASEURI__', dirname($_SERVER['PHP_SELF']) == '/' ? '' : dirname($_SERVER['PHP_SELF']));
define('DB_HOST', '--DB_HOST--');
define('DB_USER', '--DB_USER--');
define('DB_PASS', '--DB_PASS--');
define('DB_NAME', '--DB_NAME--');
define('SESSION_NAME', '--SESSION_NAME--');
define('API_KEY', '--API_KEY--');

/**
 * Upload sizes
 */
$max_img = 5*1024*1024; //5MB
$max_video = 512*1024*1024; //512MB
$up_size = toBytes(ini_get('upload_max_filesize'));
if($max_img > $up_size)$max_img = $up_size;
if($max_video > $up_size)$max_video = $up_size;

define('UPLOAD_MAX_SIZE', $max_video);
define('IMAGE_UPLOAD_MAX_SIZE', $max_img);


/** 
 * Common error messages when blocked
 */
define('BLOCKED_TERMS','App does not have permission to post to target|misusing this feature by going too fast|our security systems detected to be unsafe|Please update your API calls to the new ID|Application does not have permission for this action|Insufficient permission to post to target|The user must be an administrator of the page|Permissions error|Subject does not have permission|The validation of media ids failed');

/** 
 * Common error messages when invalid token
 */
define('INVALID_TOKEN_TERMS','Error validating access token|Session has expired at unix time|The session has been invalidated|Requires extended permission|permission must be granted before|Bad Authentication data|login_required|Invalid Credentials|Error refreshing the OAuth2 token|Login Required|This request looks like it might be automated');

/** 
 * List of accepted images and video file types
 */
$allowed_image_ext = array('jpg','png','jpeg','gif');
$allowed_video_ext = array('wmv','flv','mp4','avi','mpeg','m4v','mpg','mkv','3gp','mov','webm');

?>