<?php
/**
 * @package Social Ninja
 * @version 1.1
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
$login_required = true;
include(dirname(__FILE__).'/loader.php');

@session_write_close();
@ini_set('display_errors', false);

$folder = basename($_GET['folder']);
$file = basename($_GET['file']);

if(empty($folder) || empty($file)){
	header("HTTP/1.1 400 Bad Request");
	exit();	
}
if(preg_match('/[^0-9\_]/', $folder) || preg_match('/[^a-z0-9\_\.\-]/i', $file)){
	header('HTTP/1.1 400 Bad Request');
	exit();		
}

$f = explode('_', $folder);
if(count($f) != 2){
	header("HTTP/1.1 400 Bad Request");
	exit();		
}

$owner = $f[0];
if($user_id != $owner && empty($user_data['is_admin'])){
	header('HTTP/1.1 403 Forbidden');
	exit();		
}

$base = dirname(__FILE__).'/storage/';
$file = dirname(__FILE__).'/storage/'.$folder.'/'.$file;
//$file = realpath($file);

$f = str_replace('\\', '/' , dirname(dirname($file)));
$b = str_replace('\\', '/' ,$base);
$f = rtrim($f, '/').'/';

if($f != $b){
	header('HTTP/1.1 403 Forbidden');
	exit();			
}
if(!file_exists($file)){
	header("HTTP/1.1 404 Not Found");
	exit();		
}

$path_parts = pathinfo($file);
$file_name = $path_parts['basename'];
$file_ext = $path_parts['extension'];
$file_size  = filesize($file);
$mime = get_mime_from_ext($file_ext);

$file = @fopen($file, "rb");

if($file){
	$t = 3600*24;
	header('Accept-Ranges: bytes');
	header('Pragma: public');
	header('Cache-Control: max-age='.$t);
	header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + $t));
	header('Content-Transfer-Encoding: binary');
	header("Content-Type: " . $mime);
	
	if(preg_match('/image/i', $mime)){	
		header('Content-Disposition: inline');
	}
	else{
		header("Content-Disposition: attachment; filename=\"$file_name\"");	
	}
	
	if(isset($_SERVER['HTTP_RANGE'])){
		list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
		if ($size_unit == 'bytes'){
			list($range, $extra_ranges) = explode(',', $range_orig, 2);
		}
		else{
			$range = '';
			header('HTTP/1.1 416 Requested Range Not Satisfiable');
			exit();
		}
	}
	else{
		$range = '';
	}
	
	@list($seek_start, $seek_end) = explode('-', $range, 2);
	
	$seek_end   = (empty($seek_end)) ? ($file_size - 1) : min(abs(intval($seek_end)),($file_size - 1));
	$seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);
	
	if ($seek_start > 0 || $seek_end < ($file_size - 1)){
		header('HTTP/1.1 206 Partial Content');
		header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$file_size);
		header('Content-Length: '.($seek_end - $seek_start + 1));
	}
	else header("Content-Length: $file_size");

	set_time_limit(0);
	fseek($file, $seek_start);
	
	while(!feof($file)) 
	{
		print(@fread($file, 1024*8));
		ob_flush();
		flush();
		if (connection_status() != 0){
			@fclose($file);
			exit;
		}			
	}
	
	@fclose($file);
	exit;
}
else {
	header("HTTP/1.1 500 Internal Server Error");
	exit;
}
?>