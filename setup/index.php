<?php
/**
 * @package Social Ninja
 * @version 1.4
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
$root = dirname(dirname(__FILE__));
function toBytes($str){
	$val = preg_replace('/\s/', '', trim($str));
	$last = strtolower($str[strlen($str)-1]);
	switch($last) {
		case 'g': $val *= 1024;
		case 'm': $val *= 1024;
		case 'k': $val *= 1024;
	}
	return $val;
}
function get_cron_task_list()
{
	$d = dirname(__FILE__);
	$d = rtrim($d, '/');
	$d = rtrim($d, '\\');
	$d = str_replace('\\', '/', $d);
	
	$cron = array();
	$cron[0] = $d.'/cron/poster.php';
	$cron[1] = $d.'/cron/hid.php';
	$cron[2] = $d.'/cron/misc.php';
	$cron[3] = $d.'/cron/stats.php';
	
	return $cron;
}

/**
 * check ffmpeg to autocomplete
 */
$ffmpeg = ''; 
exec('/usr/local/bin/ffmpeg -version', $o, $c);
if(!$c)$ffmpeg = '/usr/local/bin/ffmpeg';

exec('/usr/bin/ffmpeg -version', $o, $c);
if(!$c)$ffmpeg = '/usr/bin/ffmpeg';

exec('/bin/ffmpeg -version', $o, $c);
if(!$c)$ffmpeg = '/bin/ffmpeg';

exec('ffmpeg -version', $o, $c);
if(!$c)$ffmpeg = 'ffmpeg';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome to Setup</title>
<link rel="stylesheet" href="../css/custom.css" />
<link rel="stylesheet" href="../css/themes/united/bootstrap.min.css" media="screen">
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<style>
.error{color:red}
.ok{color:green}
.warning{color:brown}
.step0, .step1, .step2, .step3, .step4, .step5{display:none}
</style>
</head>

<body>
<div class="container body">
	<div class="row">
    	<div class="col-lg-12">
        	<h3 class="text-center">Welcome to Setup</h3>
            <h5 class="text-center">Before you proceed please read the setup guide carefully</h5>
            <br/>
            <div class="row">
            	<div class="col-lg-7 setup_div">
                	<div id="fail" class="step0">
                		<h4 class="error">Please fix the red marked errors and reload this page to continue</h4>
                    </div>
                    
                    <div id="db" class="step1">
                		<h4>Database Configurations</h4>
                        <table class="table">
                        	<tr><td>Database Host</td><td><input type="text" class="form-control" id="db_host" value="localhost"/></td></tr>
                            <tr><td>Database Username</td><td><input type="text" class="form-control" id="db_user"/></td></tr>
                            <tr><td>Database Password</td><td><input type="password" class="form-control" id="db_pwd"/></td></tr>
                            <tr><td>Database Name</td><td><input type="text" class="form-control" id="db_name"/></td></tr>
                            <tr><td></td><td><button class="btn btn-info db_test">Test connection</button></td></tr>
                        </table>
                        <button class="btn btn-warning pull-right db_setup">Next >></button>
                    </div>
                    
                    <div id="misc" class="step2">
                		<h4>Misc Configurations</h4>
                        <small>* ffmpeg is needed for video watermarking and slideshow creation. Please view installation guide for more info</small>
                        <small>* if ffmpeg is not automatically filled, please check and insert the full path to ffmpeg installation</small>
                        <table class="table">
                        	<tr><td>Site URL</td><td><input type="text" class="form-control" id="site_url" value=""/></td></tr>
                            <tr><td>Site Name</td><td><input type="text" class="form-control" id="site_name" value="Social Ninja"/></td></tr>
                            <tr><td>Meta Description</td><td><input type="text" class="form-control" id="meta_desc" value="Social Ninja is an app that can be used to schedule campaigns and create contents for your Facebook, Twitter and Youtube accounts"/></td></tr>
                            <tr><td>Meta Keywords</td><td><input type="text" class="form-control" id="meta_keys" value="facebook autoposter, social media manager, facebook page poster, twitter autoposter, youtube autoposter, facebook group poster, facebook group manager"/></td></tr>
                            <tr><td>ffmpeg command path</td><td><input type="text" class="form-control" id="ffmpeg" value="<?php echo $ffmpeg?>"/></td></tr>
                            <tr><td>seo url</td><td><input type="checkbox" id="seo_url" checked="checked"/></td></tr>
                            <tr><td>Enable Facebook</td><td><input type="checkbox" id="fb_enable" checked="checked"/></td></tr>
                            <tr><td>Enable Twitter</td><td><input type="checkbox" id="tw_enable" checked="checked"/></td></tr>
                            <tr><td>Enable Youtube</td><td><input type="checkbox" id="yt_enable" checked="checked"/></td></tr>
                            <tr><td>Enable Media Plugin</td><td><input type="checkbox" id="media_plugin" checked="checked"/></td></tr>
                            <tr><td>Enable Downloader Plugin</td><td><input type="checkbox" id="downloader_plugin" checked="checked"/></td></tr>
                            <tr><td>Enable Image Watermarking</td><td><input type="checkbox" id="img_wm" checked="checked"/></td></tr>
                            <tr><td>Enable Video Watermarking</td><td><input type="checkbox" id="video_wm" checked="checked"/></td></tr>
                            <tr><td>Enable Image Editor</td><td><input type="checkbox" id="img_edit" checked="checked"/></td></tr>
                            <tr><td>Enable Video Editor</td><td><input type="checkbox" id="video_edit" checked="checked"/></td></tr>
                            <tr><td>Enable Public Signup</td><td><input type="checkbox" id="enable_signup"/></td></tr>
                            <tr><td>Admin/Support Email</td><td><input type="text" class="form-control" id="admin_email"/></td></tr>
                            <tr><td>Paypal Email (Optional)</td><td><input type="text" class="form-control" id="paypal_email"/></td></tr>
                        </table>
                        <button class="btn btn-warning pull-right misc_setup">Next >></button>
                    </div>
                    
                    <div id="app" class="step3">
                		<h4>App Configurations</h4>
                        <small>* Leave these fields blank if you want to force users to use their own app. You can also configure these apps later in admin panel</small>
                        <table class="table">
                        	<tr><td>Facebook App Id<td><input type="text" class="form-control" id="fb_app_id"/></td></tr>
                            <tr><td>Facebook App Secret</td><td><input type="text" class="form-control" id="fb_app_secret"/></td></tr>
                            <tr><td>Twitter Consumer Key</td><td><input type="text" class="form-control" id="tw_app_id"/></td></tr>
                            <tr><td>Twitter Consumer Secret</td><td><input type="text" class="form-control" id="tw_app_secret"/></td></tr>
                            <tr><td>Youtube Client Id</td><td><input type="text" class="form-control" id="yt_client_id"/></td></tr>
                            <tr><td>Youtube Client Secret</td><td><input type="text" class="form-control" id="yt_client_secret"/></td></tr>
                            <tr><td>Youtube Developer Key</td><td><input type="text" class="form-control" id="yt_dev_key"/></td></tr>
                        </table>
                        <button class="btn btn-info app_skip">Skip</button>&nbsp;&nbsp;
                        <button class="btn btn-warning pull-right app_setup">Next >></button>&nbsp;&nbsp;
                    </div>
                    
                    <div id="admin" class="step4">
                		<h4>Add an Admin</h4>
                        <table class="table">
                        	<tr><td>Email</td><td><input type="text" class="form-control" id="email" value=""/></td></tr>
                            <tr><td>Password</td><td><input type="password" class="form-control" id="password" value=""/></td></tr>
                        </table>
                        <button class="btn btn-warning pull-right add_user">Next >></button>
                    </div>
                    
                    <div id="last" class="step5">
                		<h4>Final Step</h4>
                        <p class="last_msg">
                        	In this step we will try to add cron task automatically and apply seo settings. The script will notify you if cron setup fails and in that case you will have to manually add the cron tasks or try again later from admin panel.<br/><br/>
                            The following scripts will be added. Please copy the paths because you may need them later.<br/><br/>
                            <?php echo implode('<br/>', get_cron_task_list());?>
                            <br/><br/>
                            <small>Note: If cron tasks are automatically added but you do not find them from your server control panel, first remove cron task from your Social Ninja admin panel (login with your admin email and password you just created). Then add cron jobs manually from your server control panel.</small>
                        </p>
                        <button class="btn btn-warning pull-right finish_setup">Finish</button>
                    </div>
                </div>
                <div class="col-lg-1"></div>
                <div class="col-lg-4">
                	<h4>Server Checklist</h4>
					<?php
                    $ok = 1;
					
					//check version
					if(version_compare(PHP_VERSION, '5.2.0', '<')){
						echo '<h5 class="error">PHP Version 5.2 or higher required</h5>';
						$ok = 0;
					}
					else echo '<h5 class="ok">PHP Version is 5.2 or higher</h5>';
					
					//check sample config file
                    if(!file_exists(dirname(__FILE__).'/config.sample.php') || !file_exists(dirname(__FILE__).'/db.sql') || !file_exists(dirname(__FILE__).'/htaccess.txt')){
						echo '<h5 class="error">Necessary files for setup are missing</h5>';
						$ok = 0;
					}
					else echo '<h5 class="ok">All files necessary for setup exist</h5>';
                    
					//check file/folder perms
					if(!mkdir($root.'/test123/') || !file_put_contents($root.'/test123.txt', rand())){
						echo '<h5 class="error">Folder not writable.</h5>';
						$ok = 0;	
					}
					else echo '<h5 class="ok">Folder writable</h5>';
					@rmdir($root.'/test123/');
					@unlink($root.'/test123.txt');
					
					//check exec
					if(false !== strpos(ini_get("disable_functions"), "exec") || ini_get('safe_mode')){
						echo '<h5 class="warning">exec() function is not available. Video editor and watermarking will not function</h5>';
					}
					else{
						exec("echo 1", $o, $c);
						if(!$c)echo '<h5 class="ok">exec() function working</h5>';	
						else echo '<h5 class="warning">call to exec() failed. Video editor and watermarking will not function</h5>';
					}
					
					//check curl
					if(!is_callable('curl_init') || !function_exists('curl_init') || false !== strpos(ini_get("disable_functions"), "curl_init") || false !== strpos(ini_get("disable_functions"), "curl_exec")){
						echo '<h5 class="error">curl library not found or disabled.</h5>';
						$ok = 0;
					}
					else echo '<h5 class="ok">curl library exists and callable.</h5>';
					
					//check mysql
					if(!is_callable('mysqli_connect') || !function_exists('mysqli_connect')){
						echo '<h5 class="error">mysqli library not found.</h5>';
						$ok = 0;
					}
					else echo '<h5 class="ok">mysqli library exists and callable.</h5>';
					
					//open basedir
					if(ini_get('open_basedir') || ini_get('safe_mode')){
						echo '<h5 class="warning">open_basedir or safe mode enabled. Some functions may not work</h5>';	
					}
					else echo '<h5 class="ok">open_basedir and safe mode disabled.</h5>';
					
					//upload max size
					$up_size_h = ini_get('upload_max_filesize');
					$up_size = toBytes($up_size_h);
					$rec = 512*1024*1024;
					$rec_h = '512MB';
					$min = 10*1024*1024;
					$min_h = '10MB';
					
					if($up_size < $min){
						echo '<h5 class="error">Server allows maximum upload size of '.$up_size_h.'. Min. requirement is '.$min_h.' and recommended size is '.$rec_h.'</h5>';
						$ok = 0;	
					}
					else if($up_size < $rec){
						echo '<h5 class="warning">Server allows maximum upload size of '.$up_size_h.'. This will still work but recommended size is '.$rec_h.'</h5>';	
					}
					else echo '<h5 class="ok">Server allows maximum upload size of '.$up_size_h.'</h5>';	
					
					//check max execution time
					set_time_limit(1800);
					$max_exec = ini_get('max_execution_time');
					if($max_exec != 1800){
						echo '<h5 class="warning">PHP maximum execution time cannot be overwritten. Long scripts like slideshow creation, video watermarking may fail</h5>';
					}
					else echo '<h5 class="ok">PHP maximum execution time can be configured as needed.</h5>';
                    ?>
            	</div>
            </div>
        </div>
    </div>
</div>
<br/><br/><br/>
<?php
$step = 1;
if(!$ok)$step = 0;
else{
	if(file_exists(dirname(__FILE__).'/step.txt'))$step = (int)file_get_contents(dirname(__FILE__).'/step.txt');	
}
?>
<script>
$('.ok').prepend('<i class="glyphicon glyphicon-ok"></i>&nbsp;&nbsp;');
$('.error').prepend('<i class="glyphicon glyphicon-remove"></i>&nbsp;&nbsp;');
$('.warning').prepend('<i class="glyphicon glyphicon-exclamation-sign"></i>&nbsp;&nbsp;');

$('#site_url').val(getDirName(getDirName(document.location.href))+'/');
function getDirName(e)
{
     if(e === null) return '/';
     if(e.indexOf("/") !== -1){
         e = e.split('/')            //break the string into an array
         e.pop()                     //remove its last element
         e= e.join('/')              //join the array back into a string
         if(e === '')return '/';
         return e;
     }
     return "/";
}
$('.step<?php echo $step?>').show();

$(document).on('click', '.db_test', function(){
	
	var db_host = $('#db_host').val();
	var db_user = $('#db_user').val();
	var db_pwd = $('#db_pwd').val();
	var db_name = $('#db_name').val();
	
	if(db_host == '' || db_user == '' || db_pwd == '' || db_name == '')return notify('error', 'All the fields are required for database connection');
	
	notify('wait', 'Testing connection...');
	$.post('ajax.php', {
		'db_host': db_host,
		'db_user': db_user,
		'db_pwd': db_pwd,
		'db_name': db_name,
		'db_test': 1
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != '')return notify('error', data.error);
		else{ 
			notify('success', 'Database connection successful');
		}
	});
});

$(document).on('click', '.db_setup', function(){
	
	var elem = $(this);
	var db_host = $('#db_host').val();
	var db_user = $('#db_user').val();
	var db_pwd = $('#db_pwd').val();
	var db_name = $('#db_name').val();
	
	if(db_host == '' || db_user == '' || db_pwd == '' || db_name == '')return notify('error', 'All the fields are required for database connection');
	elem.hide();
	notify('wait', 'Setting up database...');
	$.post('ajax.php', {
		'db_host': db_host,
		'db_user': db_user,
		'db_pwd': db_pwd,
		'db_name': db_name,
		'db_setup': 1
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			elem.show();
			return notify('error', data.error);
		}
		else{ 
			notify('success', 'Database setup successful');
			$('.step1').slideUp();
			$('.step2').slideDown();
		}
	});
});

$(document).on('click', '.misc_setup', function(){
	
	var elem = $(this);
	var site_url = $('#site_url').val();
	var site_name = $('#site_name').val();
	var meta_desc = $('#meta_desc').val();
	var meta_keys = $('#meta_keys').val();
	var ffmpeg = $('#ffmpeg').val();
	var seo_url = $('#seo_url').is(':checked') == true ? 1 : 0;
	var fb_enable = $('#fb_enable').is(':checked') == true ? 1 : 0;
	var tw_enable = $('#tw_enable').is(':checked') == true ? 1 : 0;
	var yt_enable = $('#yt_enable').is(':checked') == true ? 1 : 0;
	var media_plugin = $('#media_plugin').is(':checked') == true ? 1 : 0;
	var downloader_plugin = $('#downloader_plugin').is(':checked') == true ? 1 : 0;
	var img_wm = $('#img_wm').is(':checked') == true ? 1 : 0;
	var video_wm = $('#video_wm').is(':checked') == true ? 1 : 0;
	var img_edit = $('#img_edit').is(':checked') == true ? 1 : 0;
	var video_edit = $('#video_edit').is(':checked') == true ? 1 : 0;
	var enable_signup = $('#enable_signup').is(':checked') == true ? 1 : 0;
	var paypal_email = $('#paypal_email').val();
	var admin_email = $('#admin_email').val();
	
	if(site_url == '' || site_name == '')return notify('error', 'Site URL and Site name required');
	elem.hide();
	notify('wait', 'Setting up...');
	$.post('ajax.php', {
		'site_url': site_url,
		'site_name': site_name,
		'meta_desc': meta_desc,
		'meta_keys': meta_keys,
		'ffmpeg': ffmpeg,
		'seo_url': seo_url,
		'fb_enable': fb_enable,
		'tw_enable': tw_enable,
		'yt_enable': yt_enable,
		'media_plugin': media_plugin,
		'downloader_plugin': downloader_plugin,
		'img_wm': img_wm,
		'video_wm': video_wm,
		'img_edit': img_edit,
		'video_edit': video_edit,
		'enable_signup': enable_signup,
		'paypal_email': paypal_email,
		'admin_email': admin_email,
		'misc_setup': 1
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			elem.show();
			return notify('error', data.error);
		}
		else{ 
			notify('success', 'Misc setup successful');
			$('.step2').slideUp();
			$('.step3').slideDown();
		}
	});
});

$(document).on('click', '.app_skip', function(){
	$('.step3').slideUp();
	$('.step4').slideDown();
});

function notify(type, message)
{
	$('.gritter-item-wrapper').remove();
	
	time = 5000;
	image = '';
	sticky = false;
	class_name = '';
	
	if(type == 'wait'){
		title = 'Please wait...';
		sticky = true;
	}
	else if(type == 'success'){
		title = 'Success!';
	}
	else if(type == 'error'){
		title = 'Error!';
	}
	
	$.gritter.add({
		title: title,
		text: message,
		image: image,
		sticky: sticky,
		time: time,
		class_name : class_name
	});
}

$(document).on('click', '.app_setup', function(){
	
	var elem = $(this);
	var fb_app_id = $('#fb_app_id').val();
	var fb_app_secret = $('#fb_app_secret').val();
	var tw_app_id = $('#tw_app_id').val();
	var tw_app_secret = $('#tw_app_secret').val();
	var yt_client_id = $('#yt_client_id').val();
	var yt_client_secret = $('#yt_client_secret').val();
	var yt_dev_key = $('#yt_dev_key').val();
	
	elem.hide();
	notify('wait', 'Setting up...');
	$.post('ajax.php', {
		'fb_app_id': fb_app_id,
		'fb_app_secret': fb_app_secret,
		'tw_app_id': tw_app_id,
		'tw_app_secret': tw_app_secret,
		'yt_client_id': yt_client_id,
		'yt_client_secret' : yt_client_secret,
		'yt_dev_key': yt_dev_key,
		'app_setup': 1
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			elem.show();
			return notify('error', data.error);
		}
		else{ 
			notify('success', 'App setup successful');
			$('.step3').slideUp();
			$('.step4').slideDown();
		}
	});
});

$(document).on('click', '.add_user', function(){
	
	var elem = $(this);
	var email = $('#email').val();
	var password = $('#password').val();

	if(email == '' || password == '')return notify('error', 'Email and password both required');
	if(password.length < 6)return notify('error', 'Password must be at least 6 characters long');
	
	elem.hide();
	notify('wait', 'Setting up...');
	$.post('ajax.php', {
		'email': email,
		'password': password,
		'add_user': 1
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			elem.show();
			return notify('error', data.error);
		}
		else{ 
			notify('success', 'User setup successful');
			$('.step4').slideUp();
			$('.step5').slideDown();
		}
	});
});

$(document).on('click', '.finish_setup', function(){
	var elem = $(this);
	elem.hide();
	notify('wait', 'Setting up...');
	$.post('ajax.php', {
		'finish_setup': 1
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			$('.last_msg').html('<h4 style="color:green">Setup complete except the following-</h4><h5>The following php script should be added as cron task to run <span style="color:brown">EVERY MINUTE</span>. Please do not refresh this page because setup is complete and this page has been disabled. Copy the following commands before you navigate away from this page-</h5>');
			
			for(i = 0 ; i < data.cron.length; i++)$('.last_msg').append("<b>"+data.cron[i]+"</b><br/>");
			
			if(data.lines != null){
				$('.last_msg').append('<h5>For linux or unix the full cron commands are given below</h5>');
				for(i = 0 ; i < data.lines.length; i++)$('.last_msg').append("<b>"+data.lines[i]+"</b><br/>");
				
				$('.last_msg').append('<h5>If you are editing crontab txt, directly add the following commands into crontab txt file</h5>');
				for(i = 0 ; i < data.lines.length; i++)$('.last_msg').append("<b>* * * * * "+data.lines[i]+"</b><br/>");
			}
			
			$('.last_msg').append('<br/><br/><b>NOTE: Access to this setup file has been disabled. You do not need to run this setup again. Please fix the cron task and the server should be ready to run the software. PLEASE REMOVE setup folder from server for security</b>');
			return notify('error', data.error);
		}
		else{ 
			notify('success', 'Setup successful');
			$('.last_msg').html('<h4 style="color:green">Setup complete successfully</h4>');
			$('.last_msg').append('<br/><br/><b>NOTE: Access to this setup file has been disabled. You do not need to run this setup again. The software should be running fine now. PLEASE REMOVE setup folder from server for security</b>');
		}
	});
});


/**
 * gritter js code
 */
 (function(b){b.gritter={};b.gritter.options={position:"",class_name:"",fade_in_speed:"medium",fade_out_speed:1000,time:6000};b.gritter.add=function(f){try{return a.add(f||{})}catch(d){var c="Gritter Error: "+d;(typeof(console)!="undefined"&&console.error)?console.error(c,f):alert(c)}};b.gritter.remove=function(d,c){a.removeSpecific(d,c||{})};b.gritter.removeAll=function(c){a.stop(c||{})};var a={position:"",fade_in_speed:"",fade_out_speed:"",time:"",_custom_timer:0,_item_count:0,_is_setup:0,_tpl_close:'<a class="gritter-close" href="#" tabindex="1">Close Notification</a>',_tpl_title:'<span class="gritter-title">[[title]]</span>',_tpl_item:'<div id="gritter-item-[[number]]" class="gritter-item-wrapper [[item_class]]" style="display:none" role="alert"><div class="gritter-top"></div><div class="gritter-item">[[close]][[image]]<div class="[[class_name]]">[[title]]<p>[[text]]</p></div><div style="clear:both"></div></div><div class="gritter-bottom"></div></div>',_tpl_wrap:'<div id="gritter-notice-wrapper"></div>',add:function(g){if(typeof(g)=="string"){g={text:g}}if(g.text===null){throw'You must supply "text" parameter.'}if(!this._is_setup){this._runSetup()}var k=g.title,n=g.text,e=g.image||"",l=g.sticky||false,m=g.class_name||b.gritter.options.class_name,j=b.gritter.options.position,d=g.time||"";this._verifyWrapper();this._item_count++;var f=this._item_count,i=this._tpl_item;b(["before_open","after_open","before_close","after_close"]).each(function(p,q){a["_"+q+"_"+f]=(b.isFunction(g[q]))?g[q]:function(){}});this._custom_timer=0;if(d){this._custom_timer=d}var c=(e!="")?'<img src="'+e+'" class="gritter-image" />':"",h=(e!="")?"gritter-with-image":"gritter-without-image";if(k){k=this._str_replace("[[title]]",k,this._tpl_title)}else{k=""}i=this._str_replace(["[[title]]","[[text]]","[[close]]","[[image]]","[[number]]","[[class_name]]","[[item_class]]"],[k,n,this._tpl_close,c,this._item_count,h,m],i);if(this["_before_open_"+f]()===false){return false}b("#gritter-notice-wrapper").addClass(j).append(i);var o=b("#gritter-item-"+this._item_count);o.fadeIn(this.fade_in_speed,function(){a["_after_open_"+f](b(this))});if(!l){this._setFadeTimer(o,f)}b(o).bind("mouseenter mouseleave",function(p){if(p.type=="mouseenter"){if(!l){a._restoreItemIfFading(b(this),f)}}else{if(!l){a._setFadeTimer(b(this),f)}}a._hoverState(b(this),p.type)});b(o).find(".gritter-close").click(function(){a.removeSpecific(f,{},null,true);return false;});return f},_countRemoveWrapper:function(c,d,f){d.remove();this["_after_close_"+c](d,f);if(b(".gritter-item-wrapper").length==0){b("#gritter-notice-wrapper").remove()}},_fade:function(g,d,j,f){var j=j||{},i=(typeof(j.fade)!="undefined")?j.fade:true,c=j.speed||this.fade_out_speed,h=f;this["_before_close_"+d](g,h);if(f){g.unbind("mouseenter mouseleave")}if(i){g.animate({opacity:0},c,function(){g.animate({height:0},300,function(){a._countRemoveWrapper(d,g,h)})})}else{this._countRemoveWrapper(d,g)}},_hoverState:function(d,c){if(c=="mouseenter"){d.addClass("hover");d.find(".gritter-close").show()}else{d.removeClass("hover");d.find(".gritter-close").hide()}},removeSpecific:function(c,g,f,d){if(!f){var f=b("#gritter-item-"+c)}this._fade(f,c,g||{},d)},_restoreItemIfFading:function(d,c){clearTimeout(this["_int_id_"+c]);d.stop().css({opacity:"",height:""})},_runSetup:function(){for(opt in b.gritter.options){this[opt]=b.gritter.options[opt]}this._is_setup=1},_setFadeTimer:function(f,d){var c=(this._custom_timer)?this._custom_timer:this.time;this["_int_id_"+d]=setTimeout(function(){a._fade(f,d)},c)},stop:function(e){var c=(b.isFunction(e.before_close))?e.before_close:function(){};var f=(b.isFunction(e.after_close))?e.after_close:function(){};var d=b("#gritter-notice-wrapper");c(d);d.fadeOut(function(){b(this).remove();f()})},_str_replace:function(v,e,o,n){var k=0,h=0,t="",m="",g=0,q=0,l=[].concat(v),c=[].concat(e),u=o,d=c instanceof Array,p=u instanceof Array;u=[].concat(u);if(n){this.window[n]=0}for(k=0,g=u.length;k<g;k++){if(u[k]===""){continue}for(h=0,q=l.length;h<q;h++){t=u[k]+"";m=d?(c[h]!==undefined?c[h]:""):c[0];u[k]=(t).split(l[h]).join(m);if(n&&u[k]!==t){this.window[n]+=(t.length-u[k].length)/l[h].length}}}return p?u:u[0]},_verifyWrapper:function(){if(b("#gritter-notice-wrapper").length==0){b("body").append(this._tpl_wrap)}}}})(jQuery);

</script>
</body>
</html>