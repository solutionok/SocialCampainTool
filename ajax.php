<?php
/**
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$login_required = true;
include(dirname(__FILE__).'/loader.php');
session_write_close();

error_reporting(0);

$response = array();
$response['error'] = '';

/** 
 * AJAX check  
 */
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest'){
	$response['error'] = $lang['ajax']['req_failed'];
	output();
}

if(!empty($_POST['load_tab'])){
	$tab = $_POST['load_tab'];
	$get_params = $_POST['get_params'];
	parse_str($get_params, $_GET);
	
	ob_start();
	switch($tab){
		case "accounts":
			include(__ROOT__.'/templates/dashboard/accounts.php');
		break;
		
		case "folders":
			include(__ROOT__.'/templates/dashboard/folders.php');
		break;
		
		case "fanpages":
			include(__ROOT__.'/templates/dashboard/fanpages.php');
		break;
		
		case "groups":
			include(__ROOT__.'/templates/dashboard/groups.php');
		break;
		
		case "events":
			include(__ROOT__.'/templates/dashboard/events.php');
		break;
		
		case "schedules":
			include(__ROOT__.'/templates/dashboard/schedules.php');
		break;
		
		case "rss":
			include(__ROOT__.'/templates/dashboard/rss.php');
		break;
		
		case "logs":
			include(__ROOT__.'/templates/dashboard/logs.php');
		break;
		
		case "categories":
			include(__ROOT__.'/templates/dashboard/categories.php');
		break;	
		
		default:
			$response['error'] = $lang['ajax']['unknown_module'];
	}
	
	$response['html'] = ob_get_contents();
	ob_end_clean();

	output();	
}
else if(!empty($_POST['createFolder'])){
	$total = $auth->count_user_folders($user_id);
	if($total >= $user_data['folder_limit']){
		$response['error'] = $lang['ajax']['limit_exceeded'].' : '.$user_data['folder_limit'].' '.$lang['ajax']['folders'];
		output();	
	}
	
	$folder_name = sql_real_escape_string(purify_text($_POST['createFolder']));
	sql_query("INSERT INTO folders (user_id, folder_name) VALUES('$user_id', '$folder_name')");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['new_folder_fail'];
	}	
	output();	
}
else if(!empty($_POST['addRSS'])){
	$total = $auth->count_user_rss($user_id);
	if($total >= $user_data['rss_feed_limit']){
		$response['error'] = $lang['ajax']['limit_exceeded'].' : '.$user_data['rss_feed_limit'].' '.$lang['ajax']['rss_feeds'];
		output();	
	}
	
	$rss_name = sql_real_escape_string(purify_text($_POST['rssName']));
	$rss_url = sql_real_escape_string(purify_text($_POST['rssURL']));
	
	/**
	 * Verify RSS feed
	 */
	$r = verify_rss_feed($rss_url);
	if($r === true){
		sql_query("INSERT INTO rss_feeds (user_id, feed_name, rss_url) VALUES('$user_id', '$rss_name', '$rss_url')");
		if(sql_affected_rows() <= 0){
			$response['error'] = $lang['ajax']['rss_failed'];
		}	
	}
	else{
		$response['error'] = $r;	
	}
	output();	
}
else if(!empty($_POST['renameFolder'])){
	$folder_id = sql_real_escape_string($_POST['renameFolder']);
	$folder_name = sql_real_escape_string(purify_text($_POST['folderName']));
	if(!$auth->is_folder_owner($user_id, $folder_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['folder'];
		output();	
	}
	sql_query("UPDATE folders SET folder_name = '$folder_name' WHERE folder_id = '$folder_id'");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['edit_failed'].' : '.$lang['ajax']['folder'];;
	}	
	output();	
}
else if(!empty($_POST['updateRSS'])){
	$rss_id = sql_real_escape_string($_POST['rssId']);
	$rss_name = sql_real_escape_string(purify_text($_POST['rssName']));
	$rss_url = sql_real_escape_string(purify_text($_POST['rssURL']));
	if(!$auth->is_rss_owner($user_id, $rss_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['rss_feed'];
		output();	
	}
	$r = verify_rss_feed($rss_url);
	if($r === true){
		sql_query("UPDATE rss_feeds SET feed_name = '$rss_name', rss_url = '$rss_url' WHERE rss_feed_id = '$rss_id'");
		if(sql_affected_rows() <= 0){
			$response['error'] = $lang['ajax']['edit_failed'].' : '.$lang['ajax']['rss_feed'];
		}	
	}
	else{
		$response['error'] = $r;
	}
	output();	
}
else if(!empty($_POST['deleteFolder'])){
	$folder_id = sql_real_escape_string($_POST['deleteFolder']);
	if(!$auth->is_folder_owner($user_id, $folder_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['folder'];
		output();	
	}
	$q = sql_query("SELECT file_type, filename FROM files WHERE folder_id = '$folder_id'");
	while($res = sql_fetch_assoc($q)){
		$fname = $res['filename'];
		@unlink(dirname(__FILE__).'/storage/'.$user_data['storage'].'/'.$fname);
		if($res['file_type'] == 'video')@unlink(dirname(__FILE__).'/storage/'.$user_data['storage'].'/'.$fname.'.png');	
	}
	sql_query("DELETE FROM files WHERE folder_id = '$folder_id'");
	/**
	 * Do not delete folder if empty folder is requested
	 */
	if(empty($_POST['skipFolder'])){
		clear_schedules('folder', $folder_id);			 
		sql_query("DELETE FROM folders WHERE folder_id = '$folder_id'");
	}
	else sql_query("UPDATE folders SET thumb = '', file_count = 0 WHERE folder_id = '$folder_id'");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['delete_failed'].' : '.$lang['ajax']['folder'];
	}
	$auth->get_user_used_space($user_id);	
	output();		
}
else if(!empty($_POST['deleteRSS'])){
	$rss_id = sql_real_escape_string($_POST['deleteRSS']);
	if(!$auth->is_rss_owner($user_id, $rss_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['rss_feed'];;
		output();	
	}
	
	sql_query("DELETE FROM rss_feeds WHERE rss_feed_id = '$rss_id'");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['delete_failed'].' : '.$lang['ajax']['rss_feed'];;
	}
	else{
		clear_schedules('rss', $rss_id);
	}
	output();		
}
else if(!empty($_POST['deleteFile'])){
	$file_id = sql_real_escape_string($_POST['deleteFile']);
	$file = file_details($file_id);
	
	if($file['user_id'] != $user_id){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['file'];
		output();	
	}
	$f = $file['filename'];
	$storage = $user_data['storage'];
	$folder_id = $file['folder_id'];
	
	delete_file($f, $storage, $folder_id, $file_id, $file['file_type']);	
	
	$auth->get_user_used_space($user_id);	
	output();		
}
else if(!empty($_POST['saveCaption'])){
	$file_id = sql_real_escape_string($_POST['fileId']);
	$folder_id = sql_real_escape_string($_POST['folderId']);
	$caption = sql_real_escape_string(purify_text($_POST['caption']));
	
	if(empty($caption) && empty($file_id)){
		$response['error'] = $lang['ajax']['caption_fid_empty'];
		output();	
	}
	/**
	 * Save as text status
	 */
	if(!empty($caption) && empty($file_id)){
		list($pos) = sql_fetch_row(sql_query("SELECT MAX(position)+1 FROM files WHERE folder_id = '$folder_id'"));
		if(empty($pos))$pos = 1;
	
		/**
		 * mysql insert
		 */
		sql_query("INSERT INTO files (user_id, folder_id, caption, file_type, added_at, position) 
					VALUES ('$user_id', 
							'$folder_id', 
							'$caption', 
							'text', 
							NOW(), 
							'$pos')");
		
		$response['fileId'] = sql_insert_id();
		sql_query("UPDATE folders SET file_count = file_count + 1 WHERE folder_id = '$folder_id'");					
		output();
	}
	
	$response['fileId'] = $file_id;
	$file = file_details($file_id);
	
	if($file['user_id'] != $user_id){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['file'];
		output();	
	}
	if($file['file_type'] == 'text' && empty($caption)){
		$response['error'] = $lang['ajax']['text_status_must_cap'];
		output();	
	}
	
	sql_query("UPDATE files SET caption = '$caption' WHERE file_id = '$file_id'");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['cap_failed'];
	}	
	output();
}
else if(!empty($_POST['reposition_files']) && !empty($_POST['folder_id']) && !empty($_POST['offset'])){
	$folder_id = sql_real_escape_string($_POST['folder_id']);

	if(!$auth->is_folder_owner($user_id, $folder_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['folder'];
		output();	
	}
	
	$offset = sql_real_escape_string((int)$_POST['offset']);
	$files = explode(',', $_POST['reposition_files']);
	$files = array_filter($files, 'strlen');
	
	$i = 0;
	foreach($files as $file){
		if(empty($file))continue;
		$file = sql_real_escape_string($file);
		$pos = $offset+($i++);
		sql_query("UPDATE files SET position = '$pos' WHERE file_id = '$file' AND folder_id = '$folder_id'");
		if($i >= 500)break;	
	}	
	output();	
}
else if(!empty($_POST['sync_pages'])){
	ignore_user_abort(true);
	set_time_limit(1800);

	$fb_id = sql_real_escape_string($_POST['fb_id']);
	$data = sql_fetch_assoc(sql_query("SELECT * FROM fb_accounts WHERE fb_id = '$fb_id' AND user_id = '$user_id'"));
	if(empty($data)){
		$response['error'] = $lang['ajax']['inv_fb_acc'];
		output();	
	}
	$response['fail'] = array();
	$fail = $auth->save_fan_pages($data['access_token'], $fb_id, $user_id);
	if(!empty($fail)){
		$response['error'] = $lang['ajax']['some_page_not_up'];
		$response['fail'] = $fail;
		output();	
	}
	output();
}
else if(!empty($_POST['sync_groups'])){
	ignore_user_abort(true);
	set_time_limit(1800);
	
	$fb_id = sql_real_escape_string($_POST['fb_id']);
	$data = sql_fetch_assoc(sql_query("SELECT * FROM fb_accounts WHERE fb_id = '$fb_id' AND user_id = '$user_id'"));
	if(empty($data)){
		$response['error'] = $lang['ajax']['inv_fb_acc'];
		output();	
	}
	$response['fail'] = array();
	$fail = $auth->save_fan_groups($data['access_token'], $fb_id, $user_id);
	if(!empty($fail)){
		$response['error'] = $lang['ajax']['some_gr_not_up'];
		$response['fail'] = $fail;
		output();	
	}
	output();
}
else if(!empty($_POST['sync_events'])){
	ignore_user_abort(true);
	set_time_limit(1800);

	$fb_id = sql_real_escape_string($_POST['fb_id']);
	$data = sql_fetch_assoc(sql_query("SELECT * FROM fb_accounts WHERE fb_id = '$fb_id' AND user_id = '$user_id'"));
	if(empty($data)){
		$response['error'] = $lang['ajax']['inv_fb_acc'];
		output();	
	}
	$response['fail'] = array();
	$fail = $auth->save_events($data['access_token'], $fb_id, $user_id);
	if(!empty($fail)){
		$response['error'] = $lang['ajax']['some_ev_not_up'];
		$response['fail'] = $fail;
		output();	
	}
	output();
}
else if(!empty($_POST['importEdited'])){
	if(!$settings['media_plugin_enabled'] || !$settings['image_editor_enabled']){
		$response['error'] = $lang['ajax']['img_editor_off'];
		output();
	}	
	$image = strtok($_POST['importEdited'], '?');
	$caption = sql_real_escape_string(purify_text($_POST['caption']));
	$name = sql_real_escape_string(purify_text($_POST['name']));
	$folder_id = sql_real_escape_string($_POST['folder']);
	
	$auth->import_file_to_folder($user_id, $image, $folder_id, $name, $caption);
	if($auth->error){
		$response['error'] = $auth->error;
		output();	
	}
	output();
}
else if(!empty($_POST['delete_tool'])){
	$tool_id = sql_real_escape_string($_POST['delete_tool']);
	$tool = tool_details($tool_id);
	if($tool['user_id'] != $user_id){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['tools'];
		output();	
	}
	
	$link = __ROOT__.'/storage/'.$user_data['storage'].'/'.$tool['filename'];
	@unlink($link);
	
	sql_query("DELETE FROM creator_tools WHERE tool_id = '$tool_id'");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['delete_failed'].' : '.$lang['ajax']['tools'];
	}
	output();
}
else if(!empty($_POST['saveTimeZone'])){
	$timezone = sql_real_escape_string($_POST['saveTimeZone']);
	if(preg_match('/[^a-zA-Z0-9\/\-\_]/', $timezone)){
		$response['error'] = $lang['ajax']['inv_timezone'];
		output();	
	}
	sql_query("UPDATE users SET time_zone = '$timezone' WHERE user_id = '$user_id'");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['update_failed'].' : '.$lang['ajax']['timezone'];
	}
	else{
		date_default_timezone_set($timezone);
		$response['timestamp'] = (time() + date('Z')) * 1000;	
	}
	output();	
}
else if(!empty($_POST['schedule_save'])){
	$gid = sql_real_escape_string($_POST['schedule_save']);
	
	if(empty($_POST['selected_pages'])){
		$response['error'] = $lang['ajax']['at_least_one_page'];
		output();	
	}
	
	if($gid <= 0){
		$total = $auth->count_user_schedule_groups($user_id);
		if($total >= $user_data['schedule_group_limit']){
			$response['error'] = $lang['ajax']['limit_exceeded'].' : '.$user_data['schedule_group_limit'].' '.$lang['ajax']['sch_groups'];
			output();	
		}
		
		$total_sch = $auth->count_user_schedules($user_id);
		if((count($_POST['selected_pages']) + $total_sch) > $user_data['schedule_limit']){
			$response['error'] = $lang['ajax']['limit_exceeded'].' : '.$user_data['schedule_limit'].' '.$lang['ajax']['schs'];
			output();
		}
	}
	
	
	$restrict_sites = array();
	$name = sql_real_escape_string(purify_text($_POST['schedule_group_name']));
	
	/**
	 * $_POST['folder_id'] is either RSS:id or FOLDER:id
	 */
	$fid = explode(':', $_POST['folder_id']);
	$folder_id = sql_real_escape_string(@$fid[1]);
	if(empty($folder_id)){
		$response['error'] = $lang['ajax']['no_folder_sel'];
		output();
	}
	
	$post_freq_sec = '';
	$post_freq = sql_real_escape_string($_POST['post_freq']);
	$post_freq_type = sql_real_escape_string($_POST['post_freq_type']);
	
	$slide_duration = sql_real_escape_string((int)$_POST['slide_duration']);
	$slide_type = sql_real_escape_string($_POST['slide_type']);
	
	$sc_next_post = '';
	$next_post_calculate = 1;
	
	$comment_bumping_freq = '';
	$comment_bumps = '';
	$bump_type = '';
	$stats_settings = '';
	
	if($fid[0] == 'FOLDER'){
		if(!$auth->is_folder_owner($user_id, $folder_id)){
			$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['folder'];
			output();
		}
		$folder_id = 'FOLDER:'.$folder_id;
	}
	else if($fid[0] == 'RSS'){
		if(!$auth->is_rss_owner($user_id, $folder_id)){
			$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['rss_feed'];
			output();
		}
		$folder_id = 'RSS:'.$folder_id;
	}
	
	if(empty($name)){
		$response['error'] = $lang['ajax']['sch_name_req'];
		output();	
	}
	
	if(empty($folder_id)){
		$response['error'] = $lang['ajax']['no_folder_sel'];
		output();	
	}
	
	if(!get_valid_schedule_intervals($post_freq_type, $post_freq) || empty($post_freq) || empty($post_freq_type)){
		$response['error'] = $lang['ajax']['inv_post_freq'];
		output();	
	}
	
	$post_freq_sec = convert_post_freq($post_freq_type, $post_freq);
	
	$post_sequence = sql_real_escape_string($_POST['post_sequence']);
	if(preg_match('/[^album|slideshow|image|text|video|random|ordered|\|]/i', $post_sequence)){
		$response['error'] = $lang['ajax']['inv_post_seq'];
		output();	
	}
	
	if($fid[0] == 'RSS' && $post_sequence != 'random' && $post_sequence != 'ordered'){	
		$response['error'] = $lang['ajax']['only_rand_ord'];
		output();
	}
	
	if($post_sequence == 'album'){
		if(empty($user_data['use_album_post'])){
			$response['error'] = $lang['ajax']['no_album_mem'];
			output();	
		}
		$restrict_sites = array('youtube', 'fbevent');
	}
	else if($post_sequence == 'slideshow'){
		$restrict_sites = array('twitter');
		
		if(empty($settings['ffmpeg'])){
			$response['error'] = $lang['ajax']['no_slide_ff'];
			output();	
		}
		
		if(empty($settings['video_editor_enabled']) || empty($settings['media_plugin_enabled']) || empty($user_data['use_slideshow'])){
			$response['error'] = $lang['ajax']['no_slide_conf'];
			output();	
		}
		
		if(!get_available_slideshow_type($slide_type)){
			$response['error'] = $lang['ajax']['inv_slide_type'];
			output();	
		}
		if($slide_duration < 3 || $slide_duration > 10){
			$response['error'] = $lang['ajax']['inv_slide_dur'];
			output();	
		}
		$post_sequence = sql_real_escape_string($_POST['post_sequence'].'|'.$slide_duration.'|'.$slide_type);
	}
	else if($fid[0] == 'RSS')$restrict_sites = array('youtube');
	
	$repeat_schedule = empty($_POST['do_repeat']) ? 0 : 1;
	$repeat_campaign = empty($_POST['repeat_campaign']) ? 0 : 1;
	$delete_file = empty($_POST['auto_delete_file']) ? 0 : 1;
	$enable_schedule = empty($_POST['is_active']) ? 0 : 1;
	$onetime_post = empty($_POST['onetime_post']) ? 0 : 1;
	$sync_post = empty($_POST['sync_post']) ? 0 : 1;
	
	
	if($repeat_campaign && $repeat_schedule){
		$response['error'] = $lang['ajax']['camp_sch_both_repeat'];
		output();	
	}
	
	if(!isset($_POST['post_only_from']) || !isset($_POST['post_only_to'])){
		$post_only_from = '';
		$post_only_to = '';
	}
	else{
		if($_POST['post_only_from'] != '')$post_only_from = (int)strtok($_POST['post_only_from'], ':');
		else $post_only_from = '';
		
		if($_POST['post_only_to'] != '')$post_only_to = (int)strtok($_POST['post_only_to'], ':');
		else $post_only_to = '';
	}
	
	if(!empty($_POST['watermark'])){
		$watermark = sql_real_escape_string(basename($_POST['watermark']));
		if(!$auth->is_tool_owner_byname($user_id, $watermark)){
			$response['error'] = $lang['ajax']['inv_wm_file'];
			output();	
		}
	}
	else $watermark = '';
	
	if(!empty($_POST['watermark_position'])){
		$watermark_position = sql_real_escape_string($_POST['watermark_position']);
		if(!in_array($watermark_position, array('TOPLEFT', 'TOPRIGHT', 'BOTTOMLEFT', 'BOTTOMRIGHT', 'CENTER'))){
			$response['error'] = $lang['ajax']['inv_wm_pos'];
			output();	
		}
	}
	else{
		$watermark_position = '';
		if(!empty($watermark)){
			$response['error'] = $lang['ajax']['wm_pos_req'];
			output();	
		}
	}
	
	if(is_numeric($post_only_from) && is_numeric($post_only_to)){
		if($post_only_from > 23 || $post_only_from < 0){
			$response['error'] = $lang['ajax']['inv_post_from'];
			output();	
		}	
		if($post_only_to > 23 || $post_only_to < 0){
			$response['error'] = $lang['ajax']['inv_post_to'];
			output();	
		}
		if($post_only_to == $post_only_from){
			$response['error'] = $lang['ajax']['post_from_to_same'];
			output();	
		}	
	}
	
	$post_start_from = sql_real_escape_string($_POST['post_start_from']);
	if(!empty($post_start_from)){
		if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $post_start_from)){
			$response['error'] = $lang['ajax']['inv_post_start_from'];
			output();
		}
	}
	else $post_start_from = '';
	
	$post_end_at = sql_real_escape_string($_POST['post_end_at']);
	if(!empty($post_end_at)){
		if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $post_end_at)){
			$response['error'] = $lang['ajax']['inv_post_end_at'];
			output();
		}
	}
	else $post_end_at = '';
		
	if($post_end_at && $post_start_from){
		$a = strtotime($post_start_from);
		$b = strtotime($post_end_at);
		
		if($a >= $b){
			$response['error'] = $lang['ajax']['post_start_end_same'];
			output();	
		}
	}
	
	if($post_start_from){
		$post_start_from = strtotime($post_start_from);	
		$post_start_from_og = $post_start_from;
		$post_start_from = "FROM_UNIXTIME('$post_start_from')";
	}
	else $post_start_from = "''";
	
	if($post_end_at){
		$post_end_at = strtotime($post_end_at);	
		$post_end_at = "FROM_UNIXTIME('$post_end_at')";
	}
	else $post_end_at = "''";	

	
	if($gid <= 0){
		if($auth->count_user_schedule_groups($user_id) > 5000){
			$response['error'] = $lang['ajax']['too_many_sch'];
			output();	
		}	
	}
	else{
		if(!$auth->is_schedule_group_owner($user_id, $gid)){
			$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
			output();	
		}
		
		list(
			$sc_post_freq, 
			$sc_post_freq_type, 
			$sc_next_post, 
			$comment_bumping_freq, 
			$comment_bumps, 
			$bump_type, 
			$stats_settings, 
			$sc_post_only_from, 
			$sc_post_only_to, 
			$sc_post_start_from,
			$sc_enable_schedule
		) 
		= sql_fetch_row(sql_query("SELECT 
									post_freq, 
									post_freq_type, 
									UNIX_TIMESTAMP(next_post), 
									comment_bumping_freq, 
									comment_bumps, 
									bump_type, 
									stats_settings, 
									post_only_from, 
									post_only_to, 
									UNIX_TIMESTAMP(post_start_from),
									is_active
									FROM schedule_groups WHERE schedule_group_id = '$gid'"
								)
							);
							
		if($sc_post_freq == $post_freq && $sc_post_freq_type == $post_freq_type && $sc_post_only_from == (int)$post_only_from && $sc_post_only_to == (int)$post_only_to && $sc_post_start_from == $post_start_from_og){
			$next_post_calculate = 0;	
		}
		if(!$next_post_calculate && $sc_enable_schedule == 0 && $enable_schedule == 1 ){
			$next_post_calculate = 1;	
		}
		
		$comment_bumping_freq = sql_real_escape_string($comment_bumping_freq);
		$comment_bumps = sql_real_escape_string($comment_bumps);
		$bump_type = sql_real_escape_string($bump_type);
		$stats_settings = empty($stats_settings) ? '' : json_decode($stats_settings, true);
	}
	
	$post_delete_freq_sec = '';
	$post_delete_action = '';
	$post_delete_freq = $_POST['post_delete_freq'];
	$post_delete_freq_type = $_POST['post_delete_freq_type'];
	if(!empty($post_delete_freq) && !empty($post_delete_freq_type)){
		if(!get_valid_schedule_intervals($post_delete_freq_type, $post_delete_freq)){
			$response['error'] = $lang['ajax']['inv_post_del_freq'];
			output();	
		}
		$post_delete_freq_sec = convert_post_freq($post_delete_freq_type, $post_delete_freq);
		$post_delete_action = @$_POST['post_delete_action'] == 'DELETE' ? 'DELETE' : 'HIDE';
	}
	else $post_delete_freq = $post_delete_freq_type = '';
	
	if($next_post_calculate){
		$next_post = get_next_post_time($post_freq_sec, $post_only_from, $post_only_to, $post_start_from_og, $user_data['time_zone']);
	}
	else $next_post = $sc_next_post;
		
	if($gid <= 0){
		sql_query("INSERT INTO schedule_groups (
						user_id, 
						folder_id, 
						schedule_group_name,
						schedule_interval, 
						post_freq,
						post_freq_type,
						post_only_from, 
						post_only_to, 
						post_start_from,
						post_end_at,
						do_repeat, 
						repeat_campaign,
						post_delete_after,
						post_delete_freq,
						post_delete_freq_type, 
						post_delete_action, 
						auto_delete_file, 
						watermark, 
						watermark_position,
						post_sequence, 
						next_post,
						is_active,
						onetime_post,
						sync_post) 
						VALUES(
							'$user_id',
							'$folder_id',
							'$name',
							'$post_freq_sec',
							'$post_freq',
							'$post_freq_type',
							'$post_only_from',
							'$post_only_to',
							$post_start_from,
							$post_end_at,
							'$repeat_schedule',
							'$repeat_campaign',
							'$post_delete_freq_sec',
							'$post_delete_freq',
							'$post_delete_freq_type',
							'$post_delete_action',
							'$delete_file',
							'$watermark',
							'$watermark_position',
							'$post_sequence',
							FROM_UNIXTIME('$next_post'),
							'$enable_schedule',
							'$onetime_post',
							'$sync_post')");
		$group_id = sql_insert_id();
	}
	else{
		sql_query("UPDATE schedule_groups SET 
					folder_id = '$folder_id', 
					schedule_group_name = '$name',
					schedule_interval = '$post_freq_sec', 
					post_freq = '$post_freq',
					post_freq_type = '$post_freq_type',
					post_only_from = '$post_only_from', 
					post_only_to = '$post_only_to', 
					post_start_from = $post_start_from,
					post_end_at = $post_end_at,
					do_repeat = '$repeat_schedule', 
					repeat_campaign = '$repeat_campaign',
					post_delete_after = '$post_delete_freq_sec',
					post_delete_freq = '$post_delete_freq',
					post_delete_freq_type = '$post_delete_freq_type', 
					post_delete_action = '$post_delete_action', 
					auto_delete_file = '$delete_file', 
					watermark = '$watermark', 
					watermark_position = '$watermark_position',
					post_sequence = '$post_sequence', 
					".($next_post_calculate ? "next_post = FROM_UNIXTIME('$next_post')," : "")."
					is_active = '$enable_schedule',
					last_update = NOW(),
					onetime_post = '$onetime_post',
					sync_post = '$sync_post'
					WHERE 
					user_id = '$user_id' AND schedule_group_id = '$gid' AND is_active != 2");
					
		if(sql_affected_rows() <= 0){
			$response['error'] = $lang['ajax']['up_sch_fail'];
			output();		
		}
		
		$group_id = $gid;				
	}
	
	if(empty($group_id)){
		$response['error'] = $lang['ajax']['db_err'];
		output();	
	}
	
	/**
	 * Update all schedules to added_at = 0000-00-00 00:00:00 so we can identify schedules that were not updated i.e. removed
	 */
	sql_query("UPDATE schedules SET added_at = '0000-00-00 00:00:00' WHERE schedule_group_id = '$group_id' AND user_id = '$user_id'");
	
	$added = 0;
	$requested = 0;
	$add_minute = 0;
	
	foreach($_POST['selected_pages'] as $page){
		$page = explode('|', $page);
		$site = sql_real_escape_string($page[0]);
		$id = sql_real_escape_string($page[1]);	
		$sid = sql_real_escape_string($page[2]);	
		
		$stats_settings_t = array();
		if(!empty($stats_settings)){
			foreach($stats_settings as $sts){
				if($sts['site'] == $site)$stats_settings_t[] = $sts;
				else if($sts['site'] == 'fb' && preg_match('/^fb/', $site))$stats_settings_t[] = $sts;
			}	
		}
		if(empty($stats_settings_t))$stats_settings_t = '';
		else $stats_settings_t = sql_real_escape_string(json_encode($stats_settings_t));
		
		$requested++;
		
		if(!empty($is_demo) && $site == 'fbprofile')continue;
		$adata = $auth->is_id_owner($user_id, $id, $site, $sid);
		
		if(empty($adata))continue;
		if(in_array($site, $restrict_sites))continue;
		/*
		 * Maximum 10 allowed running schedules at a time for a page
		 */
		 
		//$sid = $adata[0];
		
		if(sql_num_rows(sql_query("SELECT NULL FROM schedules WHERE page_id = '$id' AND site = '$site' AND is_done = 0")) > 50)continue;
		
		sql_query("INSERT INTO schedules 
						(schedule_group_id, 
							user_id,
							social_id, 
							page_id, 
							site, 
							is_done, 
							added_at, 
							next_post, 
							is_active,
							comment_bumping_freq,
							comment_bumps,
							bump_type,
							stats_settings
						) 
						VALUES(
							'$group_id', 
							'$user_id', 
							'$sid' ,
							'$id', 
							'$site', 
							0, 
							NOW(), 
							DATE_ADD(FROM_UNIXTIME('$next_post'), INTERVAL 0 MINUTE), 
							'$enable_schedule',
							'$comment_bumping_freq',
							'$comment_bumps',
							'$bump_type',
							'$stats_settings_t'
						) 
						ON DUPLICATE KEY UPDATE 
							is_active = IF(is_active != 2, '$enable_schedule', is_active), 
							added_at = NOW()
							".($next_post_calculate ? ", next_post = DATE_ADD(FROM_UNIXTIME('$next_post'), INTERVAL 0 MINUTE)" : ""));
		
		if(sql_affected_rows() > 0){
			$add_minute += (int)($post_freq_sec/60)+1;
			$added++;
			
			if($next_post_calculate){
				$next_post = get_next_post_time($post_freq_sec, $post_only_from, $post_only_to, $post_start_from_og, $user_data['time_zone'], $next_post + 60 );
			}
		}
	}
	
	/**
	 * When no page added
	 */
	if(!$added){
		/**
		 * Delete only new schedule
		 */
		if($gid <= 0){
			
			list($ll) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM schedules WHERE schedule_group_id = '$group_id'"));
			if(empty($ll)){
				sql_query("DELETE FROM schedule_groups WHERE schedule_group_id = '$group_id'");
				clear_schedules('schedule_group', $group_id);
			}
			
			$response['error'] = $lang['ajax']['could_not_add_page_sch'];
			output();	
		}
		else sql_query("UPDATE schedules SET added_at = NOW() WHERE schedule_group_id = '$group_id' AND user_id = '$user_id'");
	}
	
	/**
	 * Delete all schedules that were not updated
	 */
	sql_query("DELETE post_log FROM schedules LEFT JOIN post_log ON post_log.schedule_id = schedules.schedule_id WHERE schedules.schedule_group_id = '$group_id' AND schedules.user_id = '$user_id' AND schedules.added_at = '0000-00-00 00:00:00'");
	sql_query("DELETE FROM schedules WHERE schedule_group_id = '$group_id' AND user_id = '$user_id' AND added_at = '0000-00-00 00:00:00'");
	$deleted = sql_affected_rows();
	$deleted = $deleted < 0 ? 0 : $deleted;
	
	$response['notice'] = $added.' out of '.$requested.' '.$lang['ajax']['pages_scheduled'].' 
						  '.($deleted ? ' & '.$deleted.' '.$lang['ajax']['pages_deleted'] : '');
	
	if($added != $requested)
		$response['notice'] .= $lang['ajax']['could_not_add_some_page_sch'];
 
 	sql_query("UPDATE schedule_groups SET total_schedules = '$added' WHERE schedule_group_id = '$group_id'");
	
	$d = sql_fetch_assoc(sql_query("SELECT *, UNIX_TIMESTAMP(post_start_from) AS post_start_from, UNIX_TIMESTAMP(post_end_at) AS post_end_at FROM schedule_groups WHERE schedule_group_id = '$group_id'"));
	
	if($d['post_start_from'])$d['post_start_from'] = date('Y-m-d', $d['post_start_from']);
	if($d['post_end_at'])$d['post_end_at'] = date('Y-m-d', $d['post_end_at']);
	
	$n = $d['next_post'];
	$d['next_post'] = get_formatted_time($n);
	$d['next_post2'] = get_formatted_time($n, 0, 2);
	$d['last_post'] = $d['last_post'] == '0000-00-00 00:00:00' ? 'N/A' : get_formatted_time($d['last_post']);
	$d['explore_url'] = makeuri('schedule.php?gid='.$group_id);
	$d['log_url'] = makeuri('post_log.php?gid='.$group_id);
	
	$response['data'] = $d;
	
	output();
}
else if(!empty($_POST['delete_schedule_group'])){
	$gid = sql_real_escape_string($_POST['delete_schedule_group']);
	if(!$auth->is_schedule_group_owner($user_id, $gid)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
		output();	
	}
	sql_query("DELETE FROM schedules WHERE schedule_group_id = '$gid'");
	sql_query("DELETE FROM schedule_groups WHERE schedule_group_id = '$gid'");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['delete_failed'].' : '.$lang['ajax']['sch'];
	}
	else{
		clear_schedules('schedule_group', $gid);	
	}	
	output();	
}
else if(!empty($_POST['view_scheduled_pages'])){
	$gid = sql_real_escape_string($_POST['view_scheduled_pages']);
	if(!$auth->is_schedule_group_owner($user_id, $gid)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
		output();	
	}
	$response['pages'] = array();
	$q = sql_query("SELECT schedules.social_id, schedules.page_id, schedules.site FROM schedules WHERE schedules.schedule_group_id = '$gid' AND schedules.user_id = '$user_id'");
	while($res = sql_fetch_assoc($q)){
		list($table, $col, $u, $uname, $ss) = get_site_params($res['site']);
		list($name, $ss) = sql_fetch_row(sql_query("SELECT $uname, $ss FROM $table WHERE $col = '".$res['page_id']."' AND user_id = '$user_id' AND $ss = '".$res['social_id']."'"));
		if(preg_match('/^fb/', $res['site'])){
			$res['owner_name'] = sql_fetch_row(sql_query("SELECT CONCAT(first_name, ' ', last_name) FROM fb_accounts WHERE fb_id = '$ss' AND user_id = '$user_id'"));
		}
		$res['name'] = $name;
		$response['pages'][] = $res;	
	}
	output();	
}
else if(!empty($_POST['view_page_feed'])){
	if(!$user_data['use_feed_cleaner']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['feed_cleaner'];
		output();	
	}
	$page_id = sql_real_escape_string($_POST['view_page_feed']); 	
	$site = sql_real_escape_string($_POST['site']);
	list($sid, $access_token, $uname) = $auth->is_id_owner($user_id, $page_id, $site);
	
	if(!$sid){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['page'];
		output();
	} 	
	
	$d = get_profile_feed($page_id, $site, $access_token, $sid, $uname);
	$response['data'] = !empty($d) && is_array($d) ? $d : array();
	output();
}
else if(!empty($_POST['hide_delete_feeds'])){
	if(!$user_data['use_feed_cleaner']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['feed_cleaner'];;
		output();	
	}
	$page_id = sql_real_escape_string($_POST['page_id']); 	
	$site = sql_real_escape_string($_POST['site']);
	$action = @$_POST['action'] == 'hide' ? 'hide' : 'delete';
	
	if($site != 'fbpage' && $action == 'hide'){
		$response['error'] = $lang['ajax']['only_fb_hid'];
		output();	
	}
	
	list($sid, $access_token, $uname) = $auth->is_id_owner($user_id, $page_id, $site);
	
	if(!$sid){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['page'];
		output();
	} 	
	
	session_write_close();
	$s = 0;
	$r = 0;
	foreach(explode(',', $_POST['feed_ids']) as $post_id){
		$post_id = sql_real_escape_string($post_id);
		$hid = new hid($user_id, $sid, $page_id, $post_id, $site, $access_token, $action);
		if($hid->success)$s++;
		$r++;
	}
	
	$response['notice'] = $s.' out of '.$r.' '.$lang['ajax']['posts_deleted'];
	output();
}
else if(!empty($_POST['set_theme'])){
	$theme = sql_real_escape_string($_POST['theme_name']); 
	if(preg_match('/[^a-z]/i', $theme)){
		$response['error'] = $lang['ajax']['inv_theme'];
		output();	
	}
	
	if(!file_exists(__ROOT__.'/css/themes/'.$theme)){
		$response['error'] = $lang['ajax']['inv_theme'];
		output();	
	}
	
	sql_query("UPDATE users SET theme = '$theme' WHERE user_id = '$user_id'");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['theme_up_fail'];
	}	
	output();
}
else if(!empty($_POST['get_file_meta'])){
	$file_id = sql_real_escape_string($_POST['file_id']);
	 
	if($file_id != 0){
		if(!$auth->is_file_owner($user_id, $file_id)){
			$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['file'];
			output();	
		}	
	}
	
	$response['data'] = $auth->get_user_file_meta($user_id, $file_id);
	$response['no_data'] =  empty($response['data']) ? 1 : 0;	
	$response['data']['file_id'] = $file_id;
	output();
}
else if(!empty($_POST['get_link_meta'])){
	$file_id = sql_real_escape_string($_POST['file_id']);
	 
	if($file_id != 0){
		if(!$auth->is_file_owner($user_id, $file_id)){
			$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['file'];
			output();	
		}	
	}
	else{
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['file'];
		output();	
	}
	
	$response['data'] = $auth->get_user_link_meta($user_id, $file_id);
	$response['no_data'] =  empty($response['data']) ? 1 : 0;	
	$response['data']['file_id'] = $file_id;
	output();
}
else if(!empty($_POST['save_file_meta'])){
	$file_id = sql_real_escape_string($_POST['file_meta_id']);
	 
	if($file_id != 0){
		if(!$auth->is_file_owner($user_id, $file_id)){
			$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['file'];
			output();	
		}	
	}
	
	$desc = sql_real_escape_string(purify_text($_POST['file_meta_desc']));
	$tags = sql_real_escape_string(purify_text($_POST['file_meta_tags']));
	$category = $_POST['file_meta_category'];
	$privacy = sql_real_escape_string($_POST['file_meta_privacy']);
	
	if(strlen($desc) > 499){
		//$response['error'] = $lang['ajax']['desc_limit'];
		//output();	
	}
	
	if(count(explode(',', $tags)) > 5){
		$response['error'] = $lang['ajax']['max_tags'];
		output();	
	}
	
	if(!empty($category)){
		$cat_name = get_yt_cats($category);
		if(!$cat_name){
			$response['error'] = $lang['ajax']['inv_cat_sel'];
			output();
		}
		$category = sql_real_escape_string($cat_name.'|'.$category);
	}
	else $category = '';
	
	if(!get_yt_privacy($privacy)){
		$response['error'] = $lang['ajax']['inv_priv_sel'];
		output();
	}
	
	sql_query("INSERT INTO file_meta (user_id, file_id, description, category, tags, privacy) 
				 VALUES(
				 '$user_id',
				 '$file_id',
				 '$desc',
				 '$category',
				 '$tags',
				 '$privacy'
				 ) ON DUPLICATE KEY UPDATE
				 description = '$desc',
				 category = '$category',
				 tags = '$tags',
				 privacy = '$privacy'
				 ");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['meta_failed'];
	}
	output();
}
else if(!empty($_POST['save_link_meta'])){
	$file_id = sql_real_escape_string($_POST['link_meta_id']);
	 
	if($file_id != 0){
		if(!$auth->is_file_owner($user_id, $file_id)){
			$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['file'];
			output();	
		}	
	}
	else{
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['file'];
		output();	
	}
	
	$desc = sql_real_escape_string(purify_text($_POST['link_meta_desc']));
	$title = sql_real_escape_string(purify_text($_POST['link_meta_title']));
	$image = sql_real_escape_string(purify_text($_POST['link_meta_image']));
	
	sql_query("INSERT INTO link_meta (user_id, file_id, link_title, link_desc, link_image) 
				 VALUES(
				 '$user_id',
				 '$file_id',
				 '$title',
				 '$desc',
				 '$image'
				 ) ON DUPLICATE KEY UPDATE
				 link_title = '$title',
				 link_desc = '$desc',
				 link_image = '$image'
				 ");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['meta_failed'];
	}
	output();
}
else if(!empty($_POST['fb_app_id']) && !empty($_POST['fb_app_secret'])){
	
	if(!empty($is_demo)){
		$response['error'] = "App cannot be changed in demo mode";
		output();	
	}
	
	$app_id = trim($_POST['fb_app_id']);
	$app_secret = trim($_POST['fb_app_secret']);
	
	if(empty($app_id) || empty($app_secret)){
		$response['error'] = $lang['ajax']['f_missing'];
		output();	
	}
	
	if(empty($user_id)){
		$response['error'] = $lang['ajax']['u_error'];
		output();	
	}
	
	if($settings['fb_app_id'] == $app_id){
		$response['error'] = $lang['ajax']['app_being_used'];
		output();	
	}
	
	$app_token = configure_fb_app($app_id, $app_secret);
	if(empty($app_token)){
		$response['error'] = $lang['ajax']['inv_fb_app'];
		output();	
	}
	
	$d = array('fb_app_id' => $app_id, 'fb_app_secret' => $app_secret, 'fb_app_token' => $app_token);
	$d = json_encode($d);
	
	session_start();
	$_SESSION['pending_fb_app'] = $d;
	
	/*if(save_fb_app($user_id, $app_id, $app_secret) != 'SUCCESS'){
		$response['error'] = 'Invalid facebook app';
		output();	
	}*/
	
	output();
}
else if(!empty($_POST['tw_app_id']) && !empty($_POST['tw_app_secret'])){
	
	if(!empty($is_demo)){
		$response['error'] = "App cannot be changed in demo mode";
		output();	
	}
	
	$app_id = trim($_POST['tw_app_id']);
	$app_secret = trim($_POST['tw_app_secret']);
	
	if(empty($app_id) || empty($app_secret)){
		$response['error'] = $lang['ajax']['f_missing'];
		output();	
	}
	
	if(empty($user_id)){
		$response['error'] = $lang['ajax']['u_error'];
		output();	
	}
	
	if($settings['tw_app_id'] == $app_id){
		$response['error'] = $lang['ajax']['app_being_used'];
		output();	
	}
	
	$d = array('tw_app_id' => $app_id, 'tw_app_secret' => $app_secret, 'global' => 0);
	$d = json_encode($d);
	
	session_start();
	$_SESSION['pending_tw_app'] = $d;
		
	/*if(save_tw_app($user_id, $app_id, $app_secret) != 'SUCCESS'){
		$response['error'] = 'Invalid twitter app';
		output();	
	}*/
	output();
}
else if(!empty($_POST['yt_client_id']) && !empty($_POST['yt_client_secret']) && !empty($_POST['yt_dev_token'])){
	
	if(!empty($is_demo)){
		$response['error'] = "App cannot be changed in demo mode";
		output();	
	}
	
	$app_id = trim($_POST['yt_client_id']);
	$app_secret = trim($_POST['yt_client_secret']);
	$dev_key = trim($_POST['yt_dev_token']);
	
	if(empty($app_id) || empty($app_secret) || empty($dev_key)){
		$response['error'] = $lang['ajax']['f_missing'];
		output();	
	}
	
	if(empty($user_id)){
		$response['error'] = $lang['ajax']['u_error'];
		output();	
	}
	
	if($settings['yt_client_id'] == $app_id){
		$response['error'] = $lang['ajax']['app_being_used'];
		output();	
	}
	
	$d = array('yt_client_id' => $app_id, 'yt_client_secret' => $app_secret, 'yt_dev_token' => $dev_key, 'global' => 0);
	
	session_start();
	$d = json_encode($d);
	$_SESSION['pending_yt_app'] = $d;
	
	/*if(save_yt_app($user_id, $app_id, $app_secret, $dev_key) != 'SUCCESS'){
		$response['error'] = 'Invalid youtube app';
		output();	
	}*/
	output();
}
else if(!empty($_POST['delete_profile'])){
	$page_id = sql_real_escape_string($_POST['delete_profile']);
	$site = sql_real_escape_string($_POST['site']);
	
	if(!empty($is_demo) && $site == 'fbprofile'){
		//$response['error'] = $lang['ajax']['no_fb_del_demo'];
		//output();	
	}
	
	list($sid) = $auth->is_id_owner($user_id, $page_id, $site);
	
	if(!$sid){
		$response['error'] = $lang['ajax']['not_del_fb'];
		output();
	}
	
	delete_profile($user_id, $page_id, $site);
	output();
}
else if(!empty($_POST['delete_schedule'])){
	$sch_id = sql_real_escape_string($_POST['delete_schedule']);
	
	if(!$auth->is_schedule_owner($user_id, $sch_id)){
		$response['error'] = $lang['ajax']['not_all_del_sch'];
		output();
	}
	
	delete_schedule($sch_id);
	output();
}
else if(!empty($_POST['stop_schedule'])){
	$sch_id = sql_real_escape_string($_POST['stop_schedule']);
	
	if(!$auth->is_schedule_owner($user_id, $sch_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
		output();
	}
	
	sql_query("UPDATE schedules SET is_active = 0 WHERE is_active != 2 AND schedule_id = '$sch_id'");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['sch_stop_fail'];
	}
	output();
}
else if(!empty($_POST['resume_schedule'])){
	$sch_id = sql_real_escape_string($_POST['resume_schedule']);
	
	if(!$auth->is_schedule_owner($user_id, $sch_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
		output();
	}
	
	sql_query("UPDATE schedules SET is_active = 1 WHERE is_active != 2 AND schedule_id = '$sch_id'");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['sch_res_fail'];
	}
	output();
}
else if(!empty($_POST['cancel_post_deletion'])){
	$log_id = sql_real_escape_string($_POST['cancel_post_deletion']);
	
	if(!$auth->is_post_log_owner($user_id, $log_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['post'];
		output();
	}
	
	sql_query("UPDATE post_log SET delete_at = '0000-00-00 00:00:00' WHERE user_id = '$user_id' AND post_log_id = '$log_id'");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['del_can_fail'];
	}
	output();
}

else if(!empty($_POST['delete_post_log'])){
	$log_id = sql_real_escape_string($_POST['delete_post_log']);
	
	if(!$auth->is_post_log_owner($user_id, $log_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['post'];
		output();
	}
	
	$log_data = sql_fetch_assoc(sql_query("SELECT * FROM post_log WHERE post_log_id = '$log_id'"));
	sql_query("UPDATE post_log SET is_hidden = 1, next_insight = '0000-00-00 00:00:00', next_bump = '0000-00-00 00:00:00' WHERE user_id = '$user_id' AND post_log_id = '$log_id'");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['del_db_fail'];
	}
	else{
		if(!empty($_POST['remove_from_site'])){
			$page_id = $log_data['page_id'];
			$post_id = $log_data['post_id'];
			$site = $log_data['site'];
			
			list($sid, $access_token, $uname) = $auth->is_id_owner($user_id, $page_id, $site);
			
			$hid = new hid($user_id, $sid, $page_id, $post_id, $site, $access_token, 'delete');
			if(!$hid->success){
				$response['error'] = $lang['ajax']['del_ss_fail'];
			}
				
		}	
	}
	output();
}
else if(!empty($_POST['merge_fb_acc'])){
	$acc_1 = sql_real_escape_string($_POST['acc_1']);
	$acc_2 = sql_real_escape_string($_POST['acc_2']);	
	
	if($acc_1 == $acc_2){
		$response['error'] = $lang['ajax']['same_id_no_merge'];
		output();	
	}
	
	if(!$auth->is_id_owner($user_id, $acc_1, 'fbprofile')){
		$response['error'] = $lang['ajax']['inv_first_acc'];
		output();	
	}
	
	if(!$auth->is_id_owner($user_id, $acc_2, 'fbprofile')){
		$response['error'] = $lang['ajax']['inv_sec_acc'];
		output();	
	}
	
	delete_profile($user_id, $acc_2, 'fbprofile');
	
	sql_query("UPDATE fb_accounts SET fb_id = '$acc_2' WHERE fb_id = '$acc_1' AND user_id = '$user_id'");
	sql_query("UPDATE fb_pages SET fb_id = '$acc_2' WHERE fb_id = '$acc_1' AND user_id = '$user_id'");
	sql_query("UPDATE fb_groups SET fb_id = '$acc_2' WHERE fb_id = '$acc_1' AND user_id = '$user_id'");
	sql_query("UPDATE fb_events SET fb_id = '$acc_2' WHERE fb_id = '$acc_1' AND user_id = '$user_id'");
	sql_query("UPDATE schedules SET social_id = '$acc_2' WHERE social_id = '$acc_1' AND user_id = '$user_id' AND site LIKE 'fb%'");
	sql_query("UPDATE schedules SET page_id = '$acc_2' WHERE page_id = '$acc_1' AND user_id = '$user_id' AND site LIKE 'fb%'");
	sql_query("UPDATE error_msg SET social_id = '$acc_2' WHERE social_id = '$acc_1' AND user_id = '$user_id' AND site LIKE 'fb%'");
	sql_query("UPDATE error_msg SET page_id = '$acc_2' WHERE page_id = '$acc_1' AND user_id = '$user_id' AND site LIKE 'fb%'");
	sql_query("UPDATE post_log SET social_id = '$acc_2' WHERE social_id = '$acc_1' AND user_id = '$user_id' AND site LIKE 'fb%'");
	sql_query("UPDATE post_log SET page_id = '$acc_2' WHERE page_id = '$acc_1' AND user_id = '$user_id' AND site LIKE 'fb%'");
	sql_query("DELETE FROM token_expiry WHERE (social_id = '$acc_2' OR page_id = '$acc_2' OR social_id = '$acc_1' OR page_id = '$acc_1') AND user_id = '$user_id' AND site LIKE 'fb%'");
	
	output();
}
else if(!empty($_POST['pwd_change'])){
	if(!empty($is_demo)){
		$response['error'] = 'Password cannot be changed in demo mode';
		output();	
	}
	
	$old_password = sql_real_escape_string($_POST['old_password']);
	$new_password = sql_real_escape_string($_POST['new_password']);
	$new_password2 = sql_real_escape_string($_POST['new_password2']);
	
	$q = sql_query("SELECT NULL FROM users WHERE user_id = '$user_id' AND password = SHA1('$old_password')");	
	if(!sql_num_rows($q)){
		$response['error'] = $lang['ajax']['old_pwd_no_match'];
		output();	
	}
	
	if(strlen($new_password) < 6){
		$response['error'] = $lang['ajax']['pwd_six_char'];
		output();
	}
	
	if($new_password != $new_password2){
		$response['error'] = $lang['ajax']['new_pwd_no_match'];
		output();
	}
	
	session_start();
	$_SESSION['pwd_no_logout'] = 1;
	sql_query("UPDATE users SET password = SHA1('$new_password'), login_required = 1 WHERE user_id = '$user_id'");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['pwd_up_fail'];
	}	
	output();
}
else if(!empty($_POST['email_change'])){
	if(!empty($is_demo)){
		$response['error'] = 'Email cannot be changed in demo mode';
		output();	
	}
	
	$password = sql_real_escape_string($_POST['password']);
	$email = sql_real_escape_string(trim(strtolower($_POST['new_email'])));
	
	$q = sql_query("SELECT NULL FROM users WHERE user_id = '$user_id' AND password = SHA1('$password')");	
	if(!sql_num_rows($q)){
		$response['error'] = $lang['ajax']['old_pwd_no_match'];
		output();	
	}
	
	if(!preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $email)){
		$response['error'] = $lang['ajax']['inv_email'];
		output();	
	}
	
	if(sql_num_rows(sql_query("SELECT NULL FROM users WHERE email = '$email'"))){
		$response['error'] = $lang['ajax']['email_taken'];
		output();
	}
	
	session_start();
	
	$_SESSION['new_email'] = $email;
	$_SESSION['new_email_code'] = rand().rand().rand();
	
	
	$p = send_email($email, 'email_change', array('code' => $_SESSION['new_email_code']));
	if(!$p)$response['error'] = $lang['ajax']['send_email_fail'];
	
	output();
}
else if(!empty($_POST['email_code_verify'])){
	if(empty($_SESSION['new_email']) || empty($_SESSION['new_email_code'])){
		$response['error'] = $lang['ajax']['sess_expired'];
		output();
	}
	if($_POST['email_code_verify'] != $_SESSION['new_email_code']){
		$response['error'] = $lang['ajax']['inv_code'];
		output();	
	}
	
	$email = sql_real_escape_string(trim(strtolower($_SESSION['new_email'])));
	
	if(!preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $email)){
		$response['error'] = $lang['ajax']['inv_email'];
		output();	
	}
	
	if(sql_num_rows(sql_query("SELECT NULL FROM users WHERE email = '$email'"))){
		$response['error'] = $lang['ajax']['email_taken'];
		output();
	}
	
	session_start();
	$_SESSION['pwd_no_logout'] = 1;
	
	sql_query("UPDATE users SET email = '$email', login_required = 1 WHERE user_id = '$user_id'");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['email_up_fail'];
	}	
	else{
		unset($_SESSION['new_email']);
		unset($_SESSION['new_email_code']);	
	}
	output();
}
else if(!empty($_POST['toggle_posting'])){
	$val = $_POST['value'] == 1 ? 1 : 0; 
	switch($_POST['type']){
		case "fb_posting":
			sql_query("UPDATE users SET fb_posting = '$val' WHERE user_id = '$user_id' AND fb_posting <= 1");
		break;
		case "tw_posting":
			sql_query("UPDATE users SET tw_posting = '$val' WHERE user_id = '$user_id' AND tw_posting <= 1");
		break;
		case "yt_posting":
			sql_query("UPDATE users SET yt_posting = '$val' WHERE user_id = '$user_id' AND yt_posting <= 1");
		break;	
	}	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['up_settings_fail_either'];
	}
	output();
}
else if(!empty($_POST['toggle_noti'])){
	$val = sql_real_escape_string($_POST['value']); 
	switch($_POST['type']){
		case "email_noti":
			sql_query("UPDATE users SET email_noti = '$val' WHERE user_id = '$user_id'");
		break;
		case "fb_noti":
			if($val){
				$ii = $auth->is_id_owner($user_id, $val, 'fbprofile');
				if(empty($ii)){
					$response['error'] = $lang['ajax']['inv_fb_id_chosen'];
					output();	
				}
			}
			sql_query("UPDATE users SET fb_noti = '$val' WHERE user_id = '$user_id'");
		break;
	}	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['up_settings_fail'];
	}
	output();
}
else if(!empty($_POST['show_adv_settings'])){
	if(!$user_data['use_advanced_scheduling']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['adv_sch'];
		output();
	}
	$sch_id = sql_real_escape_string($_POST['show_adv_settings']);
	
	if(!$auth->is_schedule_owner($user_id, $sch_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
		output();
	}	
	
	list($response['comment_bumps'], $response['stats_settings'], $response['comment_bumping_freq'], $response['bump_type']) = sql_fetch_row(sql_query("SELECT comment_bumps, stats_settings, comment_bumping_freq, bump_type FROM schedules WHERE schedule_id = '$sch_id'"));
	
	output();
	
}
else if(!empty($_POST['show_adv_settings_group'])){
	if(!$user_data['use_advanced_scheduling']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['adv_sch'];
		output();
	}
	$gid = sql_real_escape_string($_POST['show_adv_settings_group']);
	
	if(!$auth->is_schedule_group_owner($user_id, $gid)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch_group'];
		output();
	}	
	
	list($response['comment_bumps'], $response['stats_settings'], $response['comment_bumping_freq'], $response['bump_type']) = sql_fetch_row(sql_query("SELECT comment_bumps, stats_settings, comment_bumping_freq, bump_type FROM schedule_groups WHERE schedule_group_id = '$gid'"));
	
	output();
	
}
else if(!empty($_POST['adv_settings_sch_id'])){
	if(!$user_data['use_advanced_scheduling']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['adv_sch'];
		output();
	}
	$sch_id = sql_real_escape_string($_POST['adv_settings_sch_id']);
	
	if(!$auth->is_schedule_owner($user_id, $sch_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
		output();
	}	
	
	list($site, $old_freq) = sql_fetch_row(sql_query("SELECT site, comment_bumping_freq FROM schedules WHERE schedule_id = '$sch_id'"));
	
	if($site == 'fbgroup' || $site == 'fbevent'){
		
		$c_freq = sql_real_escape_string($_POST['comment_delay']);
		if($c_freq == 'disable'){
			sql_query("UPDATE post_log SET next_bump = '0000-00-00 00:00:00' WHERE schedule_id = '$sch_id'");
			sql_query("UPDATE schedules SET comment_bumping_freq = '' WHERE schedule_id = '$sch_id'");
			$c_freq = '';	
		}
		else{
		
			if(!empty($c_freq) && empty($_POST['comments'])){
				$response['error'] = $lang['ajax']['comm_req'];
				output();	
			}
			
			if(empty($c_freq) && !empty($_POST['comments'])){
				$response['error'] = $lang['ajax']['comm_delay_req'];
				output();	
			}
			
			if(!empty($_POST['comments']) && !empty($c_freq)){
				
				if(!in_array($c_freq, array('1-4', '4-8', '8-12', '12-18', '18-24', '24-48', '48-72', '168-172'))){
					$response['error'] = $lang['ajax']['inv_comm_delay'].' '.$c_freq;
					output();	
				}
				
				$comments = preg_split('/$\R?^/m', $_POST['comments'], 200);
				$comments = array_filter($comments, 'strlen');
				foreach($comments as &$c)$c = trim(purify_text($c));
				$comments = sql_real_escape_string(json_encode($comments));
				
				$usage = 'onetime';
				if(@$_POST['bump_type'] == 'repeat')$usage = 'repeat';
				sql_query("UPDATE schedules SET comment_bumps = '$comments', comment_bumping_freq = '$c_freq', bump_type = '$usage' WHERE schedule_id = '$sch_id'");
				
				if($old_freq != $c_freq){
					$next_bump = next_comment_bump_time($c_freq);
					$extra = ' 1 ';
					if($old_freq != '')$extra = " next_bump != '0000-00-00 00:00:00' ";
					sql_query("UPDATE post_log SET next_bump = FROM_UNIXTIME($next_bump + (FLOOR(RAND() * (60 - 1 + 1)) + 1) * 60) WHERE schedule_id = '$sch_id' AND hid_status = 0 AND $extra");	
				}	
			}
		}
	}
	
	$stats_settings = array();
	
	if(!empty($_POST['stats_settings']))
	foreach($_POST['stats_settings'] as $stats){
		$ss = explode('|', $stats);
		$d = array();
		$d['name'] = $ss[0];
		$d['op'] = $ss[1];
		$d['am'] = (int)$ss[2];
		$d['time'] = (int)$ss[3];
		
		if(!validate_stats_name($ss[0], $site))continue;
		if(!in_array($ss[1], array('below', 'above')))continue;
		if(empty($d['time']) || !in_array($d['time'], array(3, 6, 9, 12, 18, 24, 48, 72, 96)))continue;
		
		$stats_settings[] = $d;
	}
	
	if(!empty($stats_settings)){
		$stats_settings = sql_real_escape_string(json_encode($stats_settings));
		sql_query("UPDATE schedules SET stats_settings = '$stats_settings' WHERE schedule_id = '$sch_id'");	
	}
	else sql_query("UPDATE schedules SET stats_settings = '' WHERE schedule_id = '$sch_id'");	
	output();	
}
else if(!empty($_POST['adv_settings_sch_group_id'])){
	if(!$user_data['use_advanced_scheduling']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['adv_sch'];
		output();
	}
	$gid = sql_real_escape_string($_POST['adv_settings_sch_group_id']);
	
	if(!$auth->is_schedule_group_owner($user_id, $gid)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch_group'];
		output();
	}	
	
	$c_freq = sql_real_escape_string($_POST['comment_delay']);
	if($c_freq == 'disable'){
		sql_query("UPDATE post_log SET next_bump = '0000-00-00 00:00:00' WHERE schedule_group_id = '$gid'");
		sql_query("UPDATE schedules SET comment_bumping_freq = '' WHERE schedule_group_id = '$gid'");
		$c_freq = '';	
	}
	else{
	
		if(!empty($c_freq) && empty($_POST['comments'])){
			$response['error'] = $lang['ajax']['comm_req'];
			output();	
		}
		
		if(empty($c_freq) && !empty($_POST['comments'])){
			$response['error'] = $lang['ajax']['comm_delay_req'];
			output();	
		}
		
		if(!empty($_POST['comments']) && !empty($c_freq)){
			
			if(!in_array($c_freq, array('1-4', '4-8', '8-12', '12-18', '18-24', '24-48', '48-72', '168-172'))){
				$response['error'] = $lang['ajax']['inv_comm_delay'].' '.$c_freq;
				output();	
			}
			
			$comments = preg_split('/$\R?^/m', $_POST['comments'], 200);
			$comments = array_filter($comments, 'strlen');
			foreach($comments as &$c)$c = trim(purify_text($c));
			$comments = sql_real_escape_string(json_encode($comments));
			
			$usage = 'onetime';
			if(@$_POST['bump_type'] == 'repeat')$usage = 'repeat';
			
			sql_query("UPDATE schedule_groups SET comment_bumps = '$comments', comment_bumping_freq = '$c_freq', bump_type = '$usage' WHERE schedule_group_id = '$gid'");
			sql_query("UPDATE schedules SET comment_bumps = '$comments', comment_bumping_freq = '$c_freq', bump_type = '$usage' WHERE schedule_group_id = '$gid' AND (site = 'fbgroup' OR site = 'fbevent')");
			
			$next_bump = next_comment_bump_time($c_freq);
			$extra = " next_bump != '0000-00-00 00:00:00' ";
			sql_query("UPDATE post_log SET next_bump = FROM_UNIXTIME($next_bump + (FLOOR(RAND() * (60 - 1 + 1)) + 1) * 60) WHERE schedule_group_id = '$gid' AND hid_status = 0 AND $extra AND (site = 'fbgroup' OR site = 'fbevent')");	
		}
	}
	
	$stats_settings = array();
	$stats_settings_t = array();
	
	if(!empty($_POST['stats_settings']))
	foreach($_POST['stats_settings'] as $stats){
		$ss = explode('|', $stats);
		$d = array();
		$kk = explode(':', $ss[0]);
		$site = sql_real_escape_string(@$kk[0]);
		$d['name'] = @$kk[1];
		$d['op'] = $ss[1];
		$d['am'] = (int)$ss[2];
		$d['time'] = (int)$ss[3];
		$d['site'] = $site;
		
		if(!validate_stats_name(@$kk[1], $site))continue;
		if(!in_array($ss[1], array('below', 'above')))continue;
		if(empty($d['time']) || !in_array($d['time'], array(3, 6, 9, 12, 18, 24, 48, 72, 96)))continue;
		
		/**
		 * because fb settings Like/Comments are also applied to pages
		 */
		if($site == 'fb')$stats_settings['fbpage'][] = $d;
		$stats_settings[$site][] = $d;
		$stats_settings_t[] = $d;
	}
	
	if(!empty($stats_settings)){
		asort($stats_settings);
		foreach($stats_settings as $site => $d){
			$d = sql_real_escape_string(json_encode($d));
			sql_query("UPDATE schedules SET stats_settings = '$d' WHERE schedule_group_id = '$gid' AND site LIKE '%$site%'");	
		}
		$d = sql_real_escape_string(json_encode($stats_settings_t));
		sql_query("UPDATE schedule_groups SET stats_settings = '$d' WHERE schedule_group_id = '$gid'");
	}	
	output();	
}
else if(!empty($_POST['stop_bumping'])){
	$log_id = sql_real_escape_string($_POST['stop_bumping']);
	
	if(!$auth->is_post_log_owner($user_id, $log_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['post'];
		output();
	}
	
	sql_query("UPDATE post_log SET next_bump = '0000-00-00 00:00:00' WHERE post_log_id = '$log_id'");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['stop_bump_fail'];
	}	
	output();	
}
else if(!empty($_POST['schedule_reset'])){
	$sch_id = sql_real_escape_string($_POST['schedule_reset']);
	
	if(!$auth->is_schedule_owner($user_id, $sch_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
		output();
	}
	
	sql_query("DELETE FROM post_log WHERE schedule_id = '$sch_id'");
	
	list($post_freq_sec, $post_only_from, $post_only_to, $post_start_from) = sql_fetch_row(sql_query("SELECT schedule_groups.schedule_interval, schedule_groups.post_only_from, schedule_groups.post_only_to, schedule_groups.post_start_from FROM schedules LEFT JOIN schedule_groups ON schedule_groups.schedule_group_id = schedules.schedule_group_id WHERE schedules.schedule_id = '$sch_id'"));
	
	$next_post = get_next_post_time($post_freq_sec, $post_only_from, $post_only_to, $post_start_from, $user_data['time_zone']);
	sql_query("UPDATE schedules SET is_done = 0, next_post = FROM_UNIXTIME('$next_post'),  notes = '' WHERE schedule_id = '$sch_id'");
	
	sql_query("UPDATE schedule_groups LEFT JOIN schedules ON schedules.schedule_group_id = schedule_groups.schedule_group_id AND schedules.schedule_id = '$sch_id' SET schedule_groups.next_post = FROM_UNIXTIME('$next_post') WHERE schedules.schedule_id IS NOT NULL");	
	
	reset_schedule_group_status();
	output();	
}
else if(!empty($_POST['schedule_group_reset'])){
	$gid = sql_real_escape_string($_POST['schedule_group_reset']);
	
	if(!$auth->is_schedule_group_owner($user_id, $gid)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch_group'];
		output();
	}
	
	reset_schedule_group($gid, 0, $user_data['time_zone']);
	output();	
}
else if(!empty($_POST['schedule_group_stats_remove'])){
	$gid = sql_real_escape_string($_POST['schedule_group_stats_remove']);
	
	if(!$auth->is_schedule_group_owner($user_id, $gid)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch_group'];
		output();
	}
	
	sql_query("UPDATE schedules SET stats_settings = '' WHERE schedule_group_id = '$gid'");
	output();	
}
else if(!empty($_POST['schedule_stats_remove'])){
	$sch_id = sql_real_escape_string($_POST['schedule_stats_remove']);
	
	if(!$auth->is_schedule_owner($user_id, $sch_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
		output();
	}
	
	sql_query("UPDATE schedules SET stats_settings = '' WHERE schedule_id = '$sch_id'");
	output();	
}
else if(!empty($_POST['fetch_url'])){
	if(!$user_data['use_image_downloader'] && !$user_data['use_video_downloader']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['downloader'];
		output();
	}
	$url = $_POST['fetch_url'];
	
	$d = new download($url);
	
	if(!empty($d->error)){
		$response['error'] = $d->error;
		output();
	}
	if($d->is_video){
		if(!$user_data['use_video_downloader']){
			$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['video_downloader'];
			output();
		}
		$response['is_video'] = 1;
		$response['video'] = array('info' => $d->video_info, 'links' => $d->links, 'hash' => $d->hash);
		$f = dirname(__FILE__).'/tmp/'.$d->hash.'.txt';
		file_put_contents($f, json_encode($d->links));	
	}
	else{
		if(!$user_data['use_image_downloader']){
			$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['image_downloader'];
			output();
		}
		$image = array('thumb' => site_url().'/tmp/'.basename($d->links), 'size' => formatSize(filesize($d->links)), 'name' => purify_text(basename($url)));
		$response['image'] = $image;
	}
	output();	
}
else if(!empty($_POST['download_image'])){
	if(!$user_data['use_image_downloader']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['image_downloader'];
		output();
	}
	$file = $_POST['download_image'];
	$caption = sql_real_escape_string(purify_text($_POST['d_caption']));
	$name = sql_real_escape_string(purify_text($_POST['d_name']));
	$folder_id = sql_real_escape_string($_POST['d_folder']);
	
	if($folder_id == 'WATERMARK' || $folder_id == 'FRAME'){
		$response['error'] = $lang['ajax']['imp_not_allowed'];
		output();
	}
	if(!$auth->is_folder_owner($user_id, $folder_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['folder'];
		output();	
	}
	
	$auth->import_file_to_folder($user_id, $file, $folder_id, $name, $caption);
	@unlink($file);
	if($auth->error){
		$response['error'] = $auth->error;
		output();	
	}
	output();
}
else if(!empty($_POST['download_video'])){
	if(!$user_data['use_video_downloader']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['video_downloader'];
		output();
	}
	$dfile = $_POST['download_video'];
	$dfile_meta = $_POST['download_video_meta'];
	$caption = sql_real_escape_string(purify_text($_POST['d_caption']));
	$name = sql_real_escape_string(purify_text($_POST['d_name']));
	$folder_id = sql_real_escape_string($_POST['d_folder']);
	
	if($folder_id == 'WATERMARK' || $folder_id == 'FRAME'){
		$response['error'] = $lang['ajax']['imp_not_allowed'];
		output();
	}
	if(!$auth->is_folder_owner($user_id, $folder_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['folder'];
		output();	
	}
	
	if(preg_match('/[^a-z0-9]/i', $dfile) || preg_match('/[^a-z0-9]/i', $dfile_meta)){
		$response['error'] = $lang['ajax']['inv_fail_hash'];
		output();
	}
	
	$f = dirname(__FILE__).'/tmp/'.$dfile_meta.'.txt';
	if(!file_exists($f)){
		$response['error'] = $lang['ajax']['inv_fail_hash'];
		output();	
	}
	
	$found = 0;
	$f = json_decode(file_get_contents($f), true);
	foreach($f as $file){
		if($file['hash'] == $dfile){
			$found = 1;
			
			$filename = dirname(__FILE__).'/tmp/'.rand().time().rand().'.'.$file['ext'];
			$log_file = dirname(__FILE__).'/tmp/'.sha1($user_id.$dfile_meta.$dfile).'.txt';
			
			/**
			 * Put filename in a log file for tracking
			 */
			$data = array('is_done' => 0, 'path' => $filename, 'size' => $file['bsize'], 'error' => '');
			file_put_contents($log_file, json_encode($data));
			
			if(empty($file['bsize'])){
				$response['error'] = $lang['ajax']['fsize_fail'].' '.$lang['ajax']['inv_fail_hash'];
				output();	
			}
			
			if($file['bsize'] > 500*1024*1024){
				$response['error'] = $lang['ajax']['max_vsize_downloader'];
				output();	
			}
			
			$max_space = $user_data['allowed_storage'];
			$used_space = $auth->get_user_used_space($user_id);
			
			if($used_space + $file['bsize'] >= $max_space){
				$response['error'] = $lang['ajax']['not_enough_space'].' : '.formatSize($max_space);
				output();	
			}
			
			$d = new download('');
			$d->downloadFile($file['url'], $filename);
			if(!empty($d->error)){
				$data['is_done'] = 2;
				$data['error'] = $d->error;	
			}
			else{
				/**
				 * Now import to folder
				 */
				$data['is_done'] = 1;
				$auth->import_file_to_folder($user_id, $filename, $folder_id, $name, $caption);
				@unlink($filename);
				if($auth->error){
					$response['error'] = $auth->error;
					output();	
				}
			}
			file_put_contents($log_file, json_encode($data));
			break;
		}
	}
	
	if(!$found){
		$response['error'] = $lang['ajax']['inv_fail_hash'];
		output();	
	}
	$response['filename'] = basename($filename);
	output();
}
else if(!empty($_POST['download_file_progress'])){
	$dfile = $_POST['download_file_progress'];
	$dfile_meta = $_POST['download_file_meta_progress'];
	
	if(preg_match('/[^a-z0-9]/i', $dfile) || preg_match('/[^a-z0-9]/i', $dfile_meta)){
		$response['error'] = $lang['ajax']['inv_fail_hash'];
		output();
	}
	
	$log_file = dirname(__FILE__).'/tmp/'.sha1($user_id.$dfile_meta.$dfile).'.txt';		
	if(!file_exists($log_file)){
		$response['error'] = $lang['ajax']['inv_fail_hash'];
		output();	
	}
	$data = json_decode(file_get_contents($log_file), true);
	
	clearstatcache();
	$response['doneBytes'] = filesize($data['path']);
	$response['sizeBytes'] = $data['size'];
	$response['isDone'] = $data['is_done'];
	$response['errorMsg'] = $data['error'];
	
	if($response['isDone'])unlink($log_file);
	
	output();
}
else if(!empty($_POST['fb_group_event_ids'])){
	
	if(!$user_data['use_group_event_importer']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['importer'];
		output();
	}
	
	if(!$settings['fb_enabled']){
		$response['error'] = $lang['ajax']['fb_mod_disabled'];
		output();
	}
	
	$fb_id = sql_real_escape_string($_POST['owner_fb_id']);
	
	$ii = $auth->is_id_owner($user_id, $fb_id, 'fbprofile');
	if(empty($ii)){
		$response['error'] = $lang['ajax']['inv_fb_id_chosen'];
		output();	
	}

	$token = @$ii[1];
	
	if(empty($token)){
		$response['error'] = $lang['ajax']['inv_fb_id_chosen'];
		output();
	}
	
	$ids = preg_split('/$\R?^/m', $_POST['fb_group_event_ids']);
	$ids = array_filter(array_unique($ids), 'strlen');
	$c_ids = array_chunk($ids, 50);
	
	$total = count($ids);
	$success = 0;
	$g = 0;
	$e = 0;
		
	
	foreach($c_ids as $ids){
		$params = array();
		foreach($ids as $id){
			$params[] = array('method' => 'GET', 'relative_url' => $id);	
		}
		if(empty($params)){
			$response['error'] = $lang['ajax']['no_valid_id_f'];
			output();	
		}
		
		$post = array('access_token' => $token, 'batch' => json_encode($params), 'include_headers' => 'false');
		$url = 'https://graph.facebook.com';
		
		$data = curl_single($url, $post, 120);
		$data = json_decode($data, true);
		
		foreach($data as $dd){
			
			if($dd['code'] != 200)continue;
			
			$d = json_decode($dd['body'], true);
			
			$name = sql_real_escape_string($d['name']);
			$id = sql_real_escape_string($d['id']);
			
			if(empty($name) || empty($id))continue;
			
			if(!empty($d['start_time']))$type = 'event';
			else if(!empty($d['privacy']))$type = 'group';
			else continue;
			
			if($type == 'event'){
				$start_time = sql_real_escape_string($d['start_time']);
				
				sql_query("INSERT INTO fb_events (user_id, fb_id, event_id, event_name, start_time, access_token ,last_update, account_status)
								VALUES
								('$user_id',
								'$fb_id',
								'$id',
								'$name',
								'$start_time',
								'$token',
								NOW(),
								1) 
								ON DUPLICATE KEY UPDATE
								event_name = '$name',
								start_time = '$start_time',
								access_token = '$token',
								last_update = NOW()");
				
				if(sql_affected_rows() > 0){
					$e++;
					$success++;
				}
			}
			else{
				$privacy = sql_real_escape_string($d['privacy']);
								
				sql_query("INSERT INTO fb_groups (user_id, fb_id, group_id, group_name, privacy, access_token ,last_update, account_status)
							VALUES
							('$user_id',
							'$fb_id',
							'$id',
							'$name',
							'$privacy',
							'$token',
							NOW(),
							1) 
							ON DUPLICATE KEY UPDATE
							group_name = '$name',
							privacy = '$privacy',
							access_token = '$token',
							last_update = NOW()");	
							
				if(sql_affected_rows() > 0){
					$g++;
					$success++;
				}
			}
		}
	}
	$response['msg'] = $success.' out of '.$total.' ids '.$lang['ajax']['imported'].'. '.$lang['ajax']['img_grp'].': '.$g.', '.$lang['ajax']['imp_ev'].': '.$e;
	output();
}
else if(!empty($_POST['add_video_task'])){
	
	if(!$user_data['use_video_editor']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['v_editor'];
		output();
	}
	
	if(!$settings['media_plugin_enabled'] || !$settings['video_editor_enabled']){
		$response['error'] = $lang['ajax']['v_plugin_dis'];
		output();
	}
	
	/**
	 * Allow max 5 pending task per user to save server
	 */
	if($auth->count_user_pending_videos($user_id) >= 3){
		$response['error'] = $lang['ajax']['already_queued_task'];
		output();	
	}
	
	$tasks = json_decode($_POST['tasks'], true);
	$segments = json_decode($_POST['segments'], true);	
	$video = basename($_POST['video']);
	
	if(preg_match('/[^a-z0-9_\.]/i', $video)){
		$response['error'] = $lang['ajax']['inv_file_name'];
		output();	
	}
	
	$delete_source = 0;
	if(preg_match('/tmp\//i', $_POST['video'])){
		$delete_source = 1;
		$video = __ROOT__.'/plugins/media/tmp/'.$video;
		if(!file_exists($video)){
			$response['error'] = $lang['ajax']['f_not_found'];
			output();	
		}
		$size = filesize($video);
		$max_space = $user_data['allowed_storage'];
		$used_space = $auth->get_user_used_space($user_id);
		
		if($used_space >= $max_space){
			$response['error'] = $lang['ajax']['disk_consumed'].' '.formatSize($max_space);
			output();	
		}
		if($used_space + $size >= $max_space){
			$response['error'] = $lang['ajax']['not_enough_space'].' '.formatSize($max_space);
			output();	
		}
	}
	else if(preg_match('/storage\//i', $_POST['video'])){
		$video = __STORAGE__.'/'.$user_data['storage'].'/'.$video;
		if(!file_exists($video)){
			$response['error'] = $lang['ajax']['f_not_found'];
			output();	
		}	
	}
	
	$tasks_v = array();
	$segments_v = array();
	
	if(!empty($segments)){
		foreach($segments as $s){
			if(!isset($s['start']) || !isset($s['end']))continue;
			$i = @(int)$s['index'];
			$j = @(int)$s['start'];
			$k = @(int)$s['end'];
			
			if($k - $j < 5 || $i < 0)continue;
			
			$segments_v[$i] = array('start' => $j, 'end' => $k);	
		}	
	}
	
	if(!empty($tasks)){
		foreach($tasks as $i => &$t){
			$skip = 0;
			if($t['type'] == 'join' || $t['type'] == 'cut'){
				$segs = explode(',', $t['rel']);
				/**
				 * Check if all segments are defined. If something is not defined skip it
				 */
				 
				foreach(@$segs as $seg){
					if(empty($segments_v[$seg])){
						$skip = 1;
						break;
					}	
				}
			}
			else if($t['type'] == 'screenshot'){
				$t['rel'] = (int)$t['rel'];	
			}
			else if($t['type'] == 'tile'){
				$ss = explode('x', $t['rel']);
				$j = @(int)$ss[0];
				$k = @(int)$ss[1];
				
				if(empty($j) || empty($k) || $j > 10 || $k > 10 || $j < 0 || $k < 0){
					$skip = 1;
				}	
			}
			else $skip = 1;
			
			if($skip)continue;
			
			$tasks_v[] = array('type' => $t['type'], 'rel' => $t['rel']);	
		}	
	}
	
	if(empty($tasks_v)){
		$response['error'] = $lang['ajax']['add_task_fail'];
		output();	
	}	
	
	if($delete_source){
		$new_video = __STORAGE__.'/'.$user_data['storage'].'/'.rand().'_'.basename($video);
		if(!copy($video, $new_video)){
			$response['error'] = $lang['ajax']['cpy_video_fail'];
			output();		
		}
	}
	else{
		$new_video = $video;	
	}
	
	$new_video1 = $new_video;
	$new_video = sql_real_escape_string($new_video);
	$tasks_v = sql_real_escape_string(json_encode($tasks_v));
	$segments_v = sql_real_escape_string(json_encode($segments_v));
	
	$title = sql_real_escape_string(purify_text($_POST['title']));
	if(empty($title))$title = sql_real_escape_string('Project '.date('d-M-Y H:i:s'));
	
	sql_query("INSERT INTO video_editor_queue (title, user_id, video_file, tasks, chunks, added_at, delete_source) VALUES('$title', '$user_id', '$new_video', '$tasks_v', '$segments_v', NOW(), '$delete_source')");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['add_q_fail'];
		if($delete_source)@unlink($new_video1);
	}
	output();
}
else if(!empty($_POST['video_queue_delete'])){
	$qid = sql_real_escape_string($_POST['video_queue_delete']);
	
	if(!$auth->is_video_queue_owner($user_id, $qid)){
		$response['error'] = $lang['ajax']['video_not_del_all'];
		output();
	}
	
	list($lock, $done ,$file, $dfile, $delete_source) = sql_fetch_row(sql_query("SELECT is_locked, is_done, video_file, download_file, delete_source FROM video_editor_queue WHERE queue_id = '$qid'"));
	if(!empty($lock) && empty($done)){
		$response['error'] = $lang['ajax']['q_locked'];
		output();
	}
	
	if($delete_source)@unlink($file);
	if(!empty($dfile)){
		@unlink(__STORAGE__.'/'.$user_data['storage'].'/'.$dfile);
	}
	
	sql_query("DELETE FROM video_editor_queue WHERE queue_id = '$qid'");
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['q_del_failed'];
	}
	output();
}
else if(!empty($_POST['queue_slideshow'])){
	if(!$user_data['use_slideshow'] || !$user_data['use_video_editor']){
		$response['error'] = $lang['ajax']['mem_not_allow'].' : '.$lang['ajax']['v_editor'];
		output();
	}
	/**
	 * Allow max 5 pending task per user to save server
	 */
	if($auth->count_user_pending_videos($user_id) >= 3){
		$response['error'] = $lang['ajax']['already_queued_task'];
		output();	
	}
	
	$folder = explode(':', $_POST['s_folder_id']);
	
	if($folder[0] != 'FOLDER'){
		$response['error'] = $lang['ajax']['rss_no_slideshow'];
		output();	
	}
	
	$folder_id = sql_real_escape_string($folder[1]);
	$slide_duration = sql_real_escape_string((int)$_POST['slide_duration']);
	$slide_type = sql_real_escape_string($_POST['slide_type']);
	
	if(!$auth->is_folder_owner($user_id, $folder_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['folder'];
		output();	
	}	
	
	if(!get_available_slideshow_type($slide_type)){
		$response['error'] = $lang['ajax']['inv_slide_type'];
		output();	
	}
	if($slide_duration < 3 || $slide_duration > 10){
		$response['error'] = $lang['ajax']['inv_slide_dur'];
		output();	
	}
	
	$task = sql_real_escape_string(json_encode(array(array('type' => 'slideshow', 'rel' => $folder_id.'|'.$slide_duration.'|'.$slide_type))));
	$title = sql_real_escape_string('Slideshow of folder #'.$folder_id);
	
	sql_query("INSERT INTO video_editor_queue (title, user_id, video_file, tasks, chunks, added_at, delete_source) VALUES('$title', '$user_id', '', '$task', '', NOW(), 0)");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['add_q_fail'];
	}
	output();
}
else if(!empty($_POST['delete_pages'])){
	$page_id = sql_real_escape_string($_POST['delete_pages']);
	$site = sql_real_escape_string($_POST['site']);
	$fb_id =  sql_real_escape_string($_POST['owner']);	
	
	if(!in_array($site, array('fbpage', 'fbgroup', 'fbevent'))){
		$response['error'] = $lang['js']['invalid_request'];
		output();	
	}
	
	list($table, $col) = get_site_params($site);
	if(sql_num_rows(sql_query("SELECT NULL FROM $table WHERE user_id = '$user_id' AND fb_id = '$fb_id' AND $col = '$page_id'"))){
		delete_fb_pages($site, $user_id, $fb_id, $page_id);
		output();
	}
	else{
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['page'];
		output();	
	}
	output();
}
else if(!empty($_POST['get_cat'])){
	$cat_id = sql_real_escape_string($_POST['get_cat']);
	if(!$auth->is_category_owner($user_id, $cat_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['category'];
		output();	
	}
	list($dd) = sql_fetch_row(sql_query("SELECT selected_pages FROM user_categories WHERE category_id = '$cat_id'"));
	$ddd = json_decode($dd, true);
	
	$data = array();
	foreach($ddd as $pp){
		$page = explode('|', $pp);
		$site = sql_real_escape_string($page[0]);
		$id = sql_real_escape_string($page[1]);	
		$sid = sql_real_escape_string($page[2]);	
		$adata = $auth->is_id_owner($user_id, $id, $site, $sid);
		if(!$adata)continue;
		$data[] = $pp;
	}
	
	$response['data'] = $data;
	if(count($ddd) != count($data)){
		$data = sql_real_escape_string(json_encode($data));
		sql_query("UPDATE user_categories SET selected_pages = '$data' WHERE category_id = '$cat_id' AND user_id = '$user_id'");
	}
	output();
}
else if(!empty($_POST['delete_cat'])){
	$cat_id = sql_real_escape_string($_POST['delete_cat']);
	if(!$auth->is_category_owner($user_id, $cat_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['category'];
		output();	
	}
	sql_query("DELETE FROM user_categories WHERE category_id = '$cat_id'");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['cat_upadd_fail'];
	}
	output();
}
else if(!empty($_POST['add_edit_cats'])){
	$cat_name = sql_real_escape_string(purify_text($_POST['new_cat_name']));
	$cat_id = sql_real_escape_string($_POST['save_cat']);
	if($cat_id && !$auth->is_category_owner($user_id, $cat_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['category'];
		output();	
	}

	if(empty($cat_name)){
		$response['error'] = $lang['ajax']['cat_name_req'];
		output();
	}
	
	if(empty($_POST['selected_pages'])){
		$response['error'] = $lang['ajax']['at_least_one_page'];
		output();	
	}
	
	$data = array();
	foreach($_POST['selected_pages'] as $pp){
		$page = explode('|', $pp);
		
		$site = sql_real_escape_string($page[0]);
		$id = sql_real_escape_string($page[1]);	
		$sid = sql_real_escape_string($page[2]);
			
		$adata = $auth->is_id_owner($user_id, $id, $site, $sid);
		if(!$adata)continue;
		$data[] = $pp; 
	}
	
	$data = sql_real_escape_string(json_encode($data));
	if($cat_id <= 0){
		sql_query("INSERT INTO user_categories (user_id, category_name, selected_pages) VALUES('$user_id', '$cat_name', '$data')");
		$cat_id = sql_insert_id();
	}
	else sql_query("UPDATE user_categories SET selected_pages = '$data', category_name = '$cat_name' WHERE category_id = '$cat_id' AND user_id = '$user_id'");
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['cat_upadd_fail'];
	}
	else{
		$response['cat_id'] = $cat_id;
		$response['cat_name'] = purify_text($_POST['new_cat_name']);	
	}
	output();
}
else if(!empty($_POST['clear_logs'])){
	sql_query("DELETE FROM error_msg WHERE user_id = '$user_id'");
	output();	
}
else if(!empty($_POST['bulk_stop_resume'])){
	$ids = sql_real_escape_string($_POST['ids']);
	$op = $_POST['operation'] == 'resume' ? 1 : 0;
	
	if(preg_match('/[^0-9\,]/', $ids) || empty($ids)){
		$response['error'] = $lang['js']['inv_input'];
		output();	
	}
	if($_POST['type'] == 'schedule_groups'){
		sql_query("UPDATE schedule_groups SET is_active = '$op' WHERE schedule_group_id IN ($ids) AND user_id = '$user_id' AND is_active IN (0,1)");
		sql_query("UPDATE schedules SET is_active = '$op' WHERE schedule_group_id IN ($ids) AND user_id = '$user_id' AND is_active IN (0,1)");
	}
	else{
		sql_query("UPDATE schedules SET  is_active = '$op' WHERE schedule_id IN ($ids) AND user_id = '$user_id' AND is_active IN (0,1)");
	}
	
	if(sql_affected_rows() <= 0){
		$response['error'] = $lang['ajax']['update_failed'];
	}	
	output();
}
else if(!empty($_POST['post_now'])){
	$file_id = sql_real_escape_string($_POST['file_id']);	
	if(!$auth->is_file_owner($user_id, $file_id)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['file'];
		output();	
	}
	
	$file = sql_fetch_assoc(sql_query("SELECT * FROM files WHERE file_id = '$file_id'"));
	
	$page = explode('|', $_POST['page_id']);
	$site = sql_real_escape_string($page[0]);
	$id = sql_real_escape_string($page[1]);	
	$sid = sql_real_escape_string($page[2]);	
	
	if(!empty($is_demo) && $site == 'fbprofile'){
		$response['error'] = $lang['ajax']['fbprofile_nopost_demo'];
		output();	
	}
	
	if($file['file_type'] != 'video' && $site == 'youtube'){
		$response['error'] = $lang['ajax']['file_not_supported'];
		output();	
	}
	else if($file['file_type'] == 'video'  && $site == 'twitter'){
		$response['error'] = $lang['ajax']['file_not_supported'];
		output();	
	}
	
	$adata = $auth->is_id_owner($user_id, $id, $site, $sid);
	
	if(empty($adata)){
		$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['page'];
		output();
	} 
	
	if(!empty($_POST['watermark'])){
		$watermark = sql_real_escape_string(basename($_POST['watermark']));
		if(!$auth->is_tool_owner_byname($user_id, $watermark)){
			$response['error'] = $lang['ajax']['inv_wm_file'];
			output();	
		}
	}
	else $watermark = '';
	
	if(!empty($_POST['watermark_position'])){
		$watermark_position = sql_real_escape_string($_POST['watermark_position']);
		if(!in_array($watermark_position, array('TOPLEFT', 'TOPRIGHT', 'BOTTOMLEFT', 'BOTTOMRIGHT', 'CENTER'))){
			$response['error'] = $lang['ajax']['inv_wm_pos'];
			output();	
		}
	}
	else{
		$watermark_position = '';
		if(!empty($watermark)){
			$response['error'] = $lang['ajax']['wm_pos_req'];
			output();	
		}
	}
	
	$post_delete_freq_sec = '';
	$post_delete_action = '';
	$post_delete_freq = $_POST['post_delete_freq'];
	$post_delete_freq_type = $_POST['post_delete_freq_type'];
	
	if(!empty($post_delete_freq) && !empty($post_delete_freq_type)){
		if(!get_valid_schedule_intervals($post_delete_freq_type, $post_delete_freq)){
			$response['error'] = $lang['ajax']['inv_post_del_freq'];
			output();	
		}
		$post_delete_freq_sec = convert_post_freq($post_delete_freq_type, $post_delete_freq);
		$post_delete_action = @$_POST['post_delete_action'] == 'DELETE' ? 'DELETE' : 'HIDE';
	}
	else $post_delete_freq = $post_delete_freq_type = '';
	
	$sql = "SELECT 
			users.*, post_counter.post_count, membership_plans.*
			FROM users 
			LEFT JOIN membership_plans ON membership_plans.plan_id = users.plan_id
			LEFT JOIN post_counter ON post_counter.user_id = users.user_id AND post_counter.today = DATE(NOW())
			WHERE users.user_id  = '$user_id'";
	
	$data = sql_fetch_assoc(sql_query($sql));
	
	if(preg_match('/^fb_/', $site)){
		if(empty($data['use_facebook'])){
			$response['error'] = $lang['ajax']['mem_not_allow'];
			output();	
		}
		else if(empty($settings['fb_enabled'])){
			$response['error'] = $lang['dologin']['fbdis'];
			output();	
		}
	}
	else if($site == 'twitter'){
		if(empty($data['use_twitter'])){
			$response['error'] = $lang['ajax']['mem_not_allow'];
			output();	
		}
		else if(empty($settings['tw_enabled'])){
			$response['error'] = $lang['dologin']['twdis'];
			output();	
		}
	}
	else if($site == 'youtube'){
		if(empty($data['use_youtube'])){
			$response['error'] = $lang['ajax']['mem_not_allow'];
			output();	
		}
		else if(empty($settings['yt_enabled'])){
			$response['error'] = $lang['dologin']['ytdis'];
			output();	
		}
	}
	
	$data['schedule_id'] = -1;
	$data['social_id'] = $sid;
	$data['page_id'] = $id;
	$data['site'] = $site;
	$data['post_sequence'] = 'random';
	$data['watermark'] = $watermark;
	$data['watermark_position'] = $watermark_position;	
	$data['post_delete_after'] = $post_delete_freq_sec;
	$data['post_delete_freq'] = $post_delete_freq;
	$data['post_delete_freq_type'] = $post_delete_freq_type;
	$data['post_delete_action'] = $post_delete_action;
	$data['schedule_group_name'] = '';
	$data['schedule_group_id'] = -1;
	$data['auto_delete_file'] = 0;
	
	$sch = new schedule('');
	
	$sch->schedule_id = -1;
	$sch->data = $data;
	
	if(!$sch->check_post_counter()){
		$response['error'] = $lang['browse']['post_limit_exceeded'];
		output();	
	}		
	$sch->load_access_token();
	if(empty($sch->access_token)){
		$response['error'] = $lang['browse']['token_fail'];
		output();
	}
	
	$sch->file = $file;
	$sch->file_orig_name = $file['original_name'];
	$sch->file_id = $file['file_id'];
	$sch->status = $file['caption'];
	$sch->file_link = '';
	$sch->file_type = $file['file_type'];
	$sch->file = $file;
	
	$sch->data['folder_id'] = $file['folder_id'];
	
	if($sch->file_type != 'text')$sch->file_link = __STORAGE__.'/'.$sch->data['storage'].'/'.$file['filename'];
	
	$spintax = new spintax();
	$sch->status = $sch->replace_variables($sch->status);
	$sch->status = $spintax->process($sch->status);
	
	$delete_now = 0;
	
	if($sch->data['watermark'] && ($sch->file_type == 'image' || $sch->file_type == 'video')){
		$sch->file_link = $sch->add_watermark();
		sql_conn();
	}

	if(preg_match('/\/tmp\//', $sch->file_link) && !empty($_POST['delete_now']))$delete_now = 1;

	$sch->do_post();
	$response = json_decode($sch->response, true);
	$response['error'] = '';
	if($delete_now)@unlink($sch->file_link);
	sql_conn();
	
	if(!empty($response['id'])){
		$sch->post_id = $response['id'];
		$sch->object_id = $response['id'];
		if($sch->data['site'] == 'twitter'){
			$sch->post_id = $sch->object_id = $response['id_str'];	
		}
		else if(($sch->data['site'] == 'fbgroup' || $sch->data['site'] == 'fbevent') && !empty($response['post_id'])){
			$sch->post_id = $sch->object_id = $sch->data['page_id'].'_'.end(explode('_', $response['post_id']));	
		}
		else if(($sch->data['site'] == 'fbgroup' || $sch->data['site'] == 'fbevent') && ($sch->file_type == 'video' || $sch->file_type == 'album')){
			$url = 'https://graph.facebook.com/'.$sch->data['page_id'].'/feed?fields=object_id&access_token='.$sch->access_token;
			$ddd = curl_single($url);
			
			if(empty($sch->object_id))$obj = $response['id'];
			else $obj = $sch->object_id;
			
			$data = json_decode($ddd, true);
			foreach($data['data'] as $p){
				if($p['object_id'] == $obj){
					$sch->post_id = $sch->object_id = $p['id'];
					break;	
				}
			}	
		}
		
		if(empty($sch->post_id)){
			$sch->post_id = $response['id'];
			$sch->object_id = $response['id'];
		}
		
		/**
		 * workaround of facebook group photo no caption bug
		 */
		if($sch->data['site'] == 'fbgroup' && $sch->file_type == 'image' && !empty($sch->status)){
			$url = 'https://graph.facebook.com/'.$sch->post_id.'/?access_token='.$sch->access_token;
			$post = array('message' => $sch->status);		
			$ddd = curl_single($url, $post);		
		}
		
		$sch->insert_post_log();
		$response['post_link'] = get_link_from_post_id($sch->post_id, $site);
		output();
	}		
	else{
		$response['error'] = $lang['browse']['post_fail'].' <span><span class="sch_err" style="display:none">'.$sch->response.'</span><a onclick="$(this).parents(\'span:first\').find(\'span\').show()" href="javascript:void(0)">'.$lang['browse']['expand'].'</a></span>';
		$sch->check_errors();
		output();
	}
}
else if(!empty($_POST['update_posting_time'])){
	$sch_id = sql_real_escape_string($_POST['sch_id']);
	$datetime = sql_real_escape_string($_POST['datetime']);
	$type = sql_real_escape_string($_POST['type']);
	
	if($type == 'sch_group'){
		if(!$auth->is_schedule_group_owner($user_id, $sch_id)){
			$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
			output();	
		}	
	}
	else if($type == 'sch'){
		if(!$auth->is_schedule_owner($user_id, $sch_id)){
			$response['error'] = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['sch'];
			output();	
		}	
	}
	else{
		$response['error'] = $lang['js']['invalid_request'];
		output();	
	}
	
	$datetime = strtotime($datetime);
	if($datetime < time()){
		$response['error'] = $lang['ajax']['past_dates'];
		output();	
	}
	if($type == 'sch_group'){
		$time = reset_schedule_group($sch_id, 1, $user_data['time_zone'], $datetime, 0);
	}
	else if($type == 'sch'){
		sql_query("UPDATE schedules SET next_post = FROM_UNIXTIME('$datetime') WHERE schedule_id = '$sch_id'");	
		list($time) = sql_fetch_row(sql_query("SELECT next_post FROM schedules WHERE schedule_id = '$sch_id'"));
 	}
	
	$response['time1'] = get_formatted_time($time, 0, 1);
	$response['time2'] = get_formatted_time($time, 0, 2);
	
	output();
		
}

/**
 * Admin AJAX
 */
if(!empty($user_data['is_admin'])){
	if(!empty($is_demo)){
		$response['error'] = 'Admin actions are disabled in demo';
		output();	
	}
	/*if(!empty($_POST['update_storage'])){
		$storage = (int)$_POST['update_storage'];
		$storage *= 1024*1024;
		$uid = sql_real_escape_string($_POST['user_id']);
		
		sql_query("UPDATE users SET allowed_storage = '$storage' WHERE user_id = '$uid'");
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to update storage';
		}
		else $response['storage'] = formatSize($storage);
		output();
	}
	else if(!empty($_POST['update_post_per_day'])){
		$ppd = (int)$_POST['update_post_per_day'];
		$uid = sql_real_escape_string($_POST['user_id']);
		
		sql_query("UPDATE users SET post_per_day = '$ppd' WHERE user_id = '$uid'");
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to update settings';
		}
		output();
	}	
	else */
	if(!empty($_POST['remove_admin'])){
		$uid = sql_real_escape_string($_POST['remove_admin']);
		if($uid == $user_id){
			$response['error'] = 'You cannot remove yourself. Ask another admin to do that';
			output();	
		}
		
		if($user_data['is_admin'] != 1){
			$response['error'] = 'Admins can only be removed by super admins';
			output();	
		}
		
		list($alevel) = sql_fetch_row(sql_query("SELECT is_admin FROM users WHERE user_id = '$uid'"));
		if($alevel == 1){
			$response['error'] = 'Super admins cannot be removed';
			output();	
		}
		
		sql_query("UPDATE users SET is_admin = 0 WHERE user_id = '$uid'");
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to update settings';
		}
		output();
	}
	else if(!empty($_POST['add_admin'])){
		$uid = sql_real_escape_string($_POST['add_admin']);
		$level = sql_real_escape_string($_POST['level']);
		
		if($user_data['is_admin'] != 1){
			$response['error'] = 'Admins can only be added by super admins';
			output();	
		}
		
		$aa = $level == 1 ? 1 : 2;
		sql_query("UPDATE users SET is_admin = '$aa' WHERE user_id = '$uid'");
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to update settings';
		}
		output();
	}
	else if(!empty($_POST['adm_del_user'])){
		$uid = sql_real_escape_string($_POST['adm_del_user']);
		if($uid == $user_id){
			$response['error'] = 'You cannot delete yourself';
			output();	
		}
		
		list($alevel, $storage) = sql_fetch_row(sql_query("SELECT is_admin, storage FROM users WHERE user_id = '$uid'"));
		if($alevel == 2 && $user_data['is_admin'] != 1){
			$response['error'] = 'Admins can only be deleted by super admins';
			output();	
		}
		else if($alevel == 1){
			$response['error'] = 'Super admins cannot be deleted';
			output();	
		}
		
		sql_query("DELETE FROM creator_tools WHERE user_id = '$uid'");
		sql_query("DELETE FROM error_msg WHERE user_id = '$uid'");
		sql_query("DELETE FROM fb_accounts WHERE user_id = '$uid'");
		sql_query("DELETE FROM fb_groups WHERE user_id = '$uid'");
		sql_query("DELETE FROM fb_events WHERE user_id = '$uid'");
		sql_query("DELETE FROM fb_pages WHERE user_id = '$uid'");
		sql_query("DELETE FROM files WHERE user_id = '$uid'");
		sql_query("DELETE FROM file_meta WHERE user_id = '$uid'");
		sql_query("DELETE FROM folders WHERE user_id = '$uid'");
		sql_query("DELETE FROM post_counter WHERE user_id = '$uid'");
		sql_query("DELETE FROM post_log WHERE user_id = '$uid'");
		sql_query("DELETE FROM rss_feeds WHERE user_id = '$uid'");
		sql_query("DELETE FROM schedules WHERE user_id = '$uid'");
		sql_query("DELETE FROM schedule_groups WHERE user_id = '$uid'");
		sql_query("DELETE FROM token_expiry WHERE user_id = '$uid'");
		sql_query("DELETE FROM tw_accounts WHERE user_id = '$uid'");
		sql_query("DELETE FROM yt_accounts WHERE user_id = '$uid'");
		sql_query("DELETE FROM video_editor_queue WHERE user_id = '$uid'");
		sql_query("DELETE FROM user_categories WHERE user_id = '$uid'");
		sql_query("DELETE FROM users WHERE user_id = '$uid'");
		
		rrmdir(__STORAGE__.'/'.$storage);
		
		output();
	}
	else if(!empty($_POST['ban_user'])){
		$uid = sql_real_escape_string($_POST['ban_user']);
		if($uid == $user_id){
			$response['error'] = 'You cannot ban yourself';
			output();	
		}
		
		list($alevel) = sql_fetch_row(sql_query("SELECT is_admin FROM users WHERE user_id = '$uid'"));
		if($alevel == 2 && $user_data['is_admin'] != 1){
			$response['error'] = 'Admins can only be banned by super admins';
			output();	
		}
		else if($alevel == 1){
			$response['error'] = 'Super admins cannot be banned';
			output();	
		}
		
		sql_query("UPDATE users SET account_status = 2 WHERE user_id = '$uid'");
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to update settings';
		}
		output();
	}	
	else if(!empty($_POST['unban_user'])){
		$uid = sql_real_escape_string($_POST['unban_user']);
		
		sql_query("UPDATE users SET account_status = 1 WHERE user_id = '$uid'");
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to update settings';
		}
		output();
	}
	else if(!empty($_POST['change_posting'])){
		$type = sql_real_escape_string($_POST['change_posting']); 
		$uid = sql_real_escape_string($_POST['user_id']);
		
		$inv = 'enable';
		$tt = preg_split('/_/', $type, 2);
		if($tt[0] == 'enable'){
			$v = 0;
			$inv = 'disable';
		}
		else $v = 2;
		
		if($tt[1] == 'fb_posting')$t = 'fb_posting';
		else if($tt[1] == 'tw_posting')$t = 'tw_posting';
		else $t = 'yt_posting';
		
		sql_query("UPDATE users SET $t = '$v' WHERE user_id = '$uid'");
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to update settings';
		}
		else{
			$msg = ucwords($tt[0]).'d <button class="btn btn-'.($inv == 'enable' ? 'danger' : 'info').' btn-xs change_posting" id="'.$inv.'_'.$tt[1].'">'.ucwords($inv).'</button>';
			$response['msg'] = $msg;
		}
		output();
	}	
	else if(!empty($_POST['adm_folder_delete'])){
		$folder_id = sql_real_escape_string($_POST['adm_folder_delete']); 
		list($uid, $storage) = sql_fetch_row(sql_query("SELECT users.user_id, users.storage FROM users LEFT JOIN folders ON folders.user_id = users.user_id AND folders.folder_id = '$folder_id' WHERE folders.folder_id IS NOT NULL"));
		
		if(empty($storage)){
			$response['error'] = 'Failed to fetch user data';
			output();	
		}
		
		$q = sql_query("SELECT file_type, filename FROM files WHERE folder_id = '$folder_id'");
		while($res = sql_fetch_assoc($q)){
			$fname = $res['filename'];
			@unlink(dirname(__FILE__).'/storage/'.$storage.'/'.$fname);
			if($res['file_type'] == 'video')@unlink(dirname(__FILE__).'/storage/'.$storage.'/'.$fname.'.png');	
		}
		sql_query("DELETE FROM files WHERE folder_id = '$folder_id'");
		clear_schedules('folder', $folder_id);			 
		sql_query("DELETE FROM folders WHERE folder_id = '$folder_id'");
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to delete folder';
		}
		$auth->get_user_used_space($uid);
		output();
	}
	else if(!empty($_POST['adm_file_delete'])){
		$file_id = sql_real_escape_string($_POST['adm_file_delete']);
		$file = file_details($file_id);
		list($uid, $storage) = sql_fetch_row(sql_query("SELECT user_id, storage FROM users WHERE user_id = '".$file['user_id']."'"));
		
		$f = $file['filename'];
		$folder_id = $file['folder_id'];
		
		delete_file($f, $storage, $folder_id, $file_id, $file['file_type']);	
		
		$auth->get_user_used_space($uid);	
		output();
	}
	else if(!empty($_POST['adm_profile_ban'])){
		$id = sql_real_escape_string($_POST['adm_profile_ban']);	
		$site = sql_real_escape_string($_POST['site']);
		$uid = sql_real_escape_string($_POST['uid']);
		
		list($table, $col) = get_site_params($site);
		sql_query("UPDATE $table SET account_status = 2 WHERE $col = '$id' AND user_id = '$uid'");
		
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to ban profile';
		}
		else{
			sql_query("UPDATE schedules SET is_active = 2 WHERE (social_id = '$id' OR page_id = '$id') AND site = '$site' AND user_id = '$uid'");
			if($site == 'fbprofile')sql_query("UPDATE users SET fb_noti = '' WHERE fb_noti = '$id' AND user_id = '$uid'");	
		}
		output();
	}
	else if(!empty($_POST['adm_profile_unban'])){
		$id = sql_real_escape_string($_POST['adm_profile_unban']);	
		$site = sql_real_escape_string($_POST['site']);
		$uid = sql_real_escape_string($_POST['uid']);
		
		list($table, $col) = get_site_params($site);
		sql_query("UPDATE $table SET account_status = 1 WHERE $col = '$id' AND user_id = '$uid'");	
		
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to unban profile';
		}
		else{
			sql_query("UPDATE schedules SET is_active = 0 WHERE (social_id = '$id' OR page_id = '$id') AND site = '$site' AND user_id = '$uid'");	
		}
		output();
	}
	else if(!empty($_POST['adm_profile_delete'])){
		$id = sql_real_escape_string($_POST['adm_profile_delete']);	
		$site = sql_real_escape_string($_POST['site']);
		$uid = sql_real_escape_string($_POST['uid']);
		
		delete_profile($uid, $id, $site);
		output();
	}
	else if(!empty($_POST['adm_sch_ban'])){
		$id = sql_real_escape_string($_POST['adm_sch_ban']);	
		sql_query("UPDATE schedules SET is_active = 2 WHERE schedule_id = '$id'");	
		
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to ban schedule';
		}
		output();
	}
	else if(!empty($_POST['adm_sch_unban'])){
		$id = sql_real_escape_string($_POST['adm_sch_unban']);	
		sql_query("UPDATE schedules SET is_active = 0 WHERE schedule_id = '$id'");	
		
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to unban schedule';
		}
		output();
	}
	else if(!empty($_POST['adm_sch_delete'])){
		$id = sql_real_escape_string($_POST['adm_sch_delete']);	
		delete_schedule($id);
		output();
	}
	else if(!empty($_POST['adm_vq_delete'])){
		$qid = sql_real_escape_string($_POST['adm_vq_delete']);	
		
		list($lock, $done ,$file, $dfile, $storage) = sql_fetch_row(sql_query("SELECT video_editor_queue.is_locked, video_editor_queue.is_done, video_editor_queue.video_file, video_editor_queue.download_file, users.storage FROM video_editor_queue LEFT JOIN users ON users.user_id = video_editor_queue.user_id WHERE video_editor_queue.queue_id = '$qid'"));
		if(!empty($lock) && empty($done)){
			$response['error'] = 'This queue is currently being processed and cannot be deleted';
			output();
		}
		@unlink($file);
		if(!empty($dfile)){
			@unlink(__STORAGE__.'/'.$storage.'/'.$dfile);
		}
		
		sql_query("DELETE FROM video_editor_queue WHERE queue_id = '$qid'");
		
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to delete queue';
		}
		output();
		
	}
	else if(!empty($_POST['adm_add_user'])){
		$email = sql_real_escape_string(trim(strtolower($_POST['email'])));
		$password = sql_real_escape_string($_POST['password']);
		$is_admin = sql_real_escape_string((int)$_POST['adminship']);
		$membership = sql_real_escape_string((int)$_POST['membership']);
		$expiry = sql_real_escape_string((int)$_POST['mem_expires_in']);
		
		if(empty($membership))$membership = 1;
		
		if(strlen($password) < 6){
			$response['error'] = 'Password must be at least 6 characters long';
			output();	
		}
		
		if(!preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $email)){
			$response['error'] = 'Invalid Email';
			output();	
		}
		
		if(sql_num_rows(sql_query("SELECT NULL FROM users WHERE email = '$email'"))){
			$response['error'] = 'This email is already registered with another account';
			output();
		}
		
		sql_query("INSERT INTO users (email, password, is_admin, account_status, fb_posting, tw_posting, yt_posting, plan_id, membership_expiry_time) VALUES('$email', SHA1('$password'), '$is_admin', 1, 1, 1, 1, '$membership', DATE_ADD(NOW(), INTERVAL $expiry DAY))");
		
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Failed to create new user';
		}	
		else{
			$id = sql_insert_id();
			$storage = $id.'_'.rand().rand().rand();
			sql_query("UPDATE users SET storage = '$storage' WHERE user_id = '$id'");	
			send_email($email, 'user_add', array('pwd' => $password));
		}
		output();	
	}
	else if(!empty($_POST['adm_update_user'])){
		$uid = sql_real_escape_string($_POST['adm_update_user']);
		$email = sql_real_escape_string(trim(strtolower($_POST['email'])));
		$password = sql_real_escape_string($_POST['password']);
		
		if(!empty($password)){
			if(strlen($password) < 6){
				$response['error'] = 'Password must be at least 6 characters long';
				output();	
			}
			sql_query("UPDATE users SET password = SHA1('$password') WHERE user_id = '$uid'");
			if(sql_affected_rows() <= 0){
				$response['error'] = 'Failed to update password';
			}
		}
		
		if(!empty($email)){
			if(!preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $email)){
				$response['error'] = 'Invalid Email';
				output();	
			}
			if(sql_num_rows(sql_query("SELECT NULL FROM users WHERE email = '$email' AND user_id != '$uid'"))){
				$response['error'] = 'This email is already registered with another account';
				output();
			}
			sql_query("UPDATE users SET email = '$email' WHERE user_id = '$uid'");
			if(sql_affected_rows() <= 0){
				$response['error'] = 'Failed to update email';
			}
		}
		
		list($u_email) = sql_fetch_row(sql_query("SELECT email FROM users WHERE user_id = '$uid'"));
		send_email($u_email, 'user_update', array('pwd' => empty($password) ? 'Previous password' : $password));
		output();	
	}
	else if(!empty($_POST['save_plan'])){
		$c = get_plan_columns();
		$cols = array();
		foreach($c as $cc)$cols[] = $cc['Field'];
		unset($_POST['save_plan']);
		foreach($_POST as $k => $v)if(!in_array($k, $cols)){
			$response['error'] = 'Invalid column '.$k;
			output();	
		}
		
		foreach($_POST as $k => $v)if($v == '' && $k != 'plan_id'){
			$response['error'] = 'Empty value not accepted. Each field required. To disable use zero instead of blank field. Empty field: '.$k;
			output();	
		}
		
		$cols = array();
		$vals = array();
		if(empty($_POST['plan_id']))$sql = 'INSERT INTO membership_plans ';
		else $sql = 'UPDATE membership_plans SET ';
		foreach($_POST as $k => $v){
			if($k == 'plan_id')continue;			
			if(empty($_POST['plan_id'])){
				$cols[] = '`'.sql_real_escape_string($k).'`';
				$vals[] = "'".sql_real_escape_string($v)."'";	
			}
			else $cols[] = "`$k` = '$v'";	
		}
		
		if(empty($_POST['plan_id'])){
			$sql .= '('.implode(',', $cols).') VALUES('.implode(',', $vals).')';
		}
		else $sql .= implode(',', $cols)." WHERE plan_id = ".sql_real_escape_string($_POST['plan_id']);
		
		sql_query($sql);
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Database failure or nothing edited';
		}
		if(empty($_POST['plan_id'])){
			$plan_id = sql_insert_id();
			sql_query("UPDATE membership_plans SET display_on_site = 1 WHERE plan_id = '$plan_id'");	
		}	
		output();
	}
	else if(!empty($_POST['view_customize_plan'])){
		$plan_id = sql_real_escape_string($_POST['view_customize_plan']);	
		list($response['display_on_site'], $response['plan_features'], $response['plan_subtitle'], $response['is_preferred']) = 
			sql_fetch_row(sql_query("SELECT display_on_site, plan_features, plan_subtitle, is_preferred FROM membership_plans WHERE plan_id = '$plan_id'"));
			
		if(!empty($response['plan_features']))$response['plan_features'] = implode(json_decode($response['plan_features'], true), "\n");
		output();
	}
	else if(!empty($_POST['customize_plan'])){
		$plan_id = sql_real_escape_string($_POST['customize_plan']);
		$plan_subtitle = sql_real_escape_string($_POST['plan_subtitle']);	
		$is_preferred = empty($_POST['is_preferred']) ? 0 : 1;
		$display_on_site = empty($_POST['display_on_site']) ? 0 : 1;
		$plan_features = preg_split('/$\R?^/m', $_POST['plan_features']);
		$plan_features = sql_real_escape_string(json_encode($plan_features));
		
		sql_query("UPDATE membership_plans SET display_on_site = '$display_on_site', plan_features = '$plan_features', plan_subtitle = '$plan_subtitle', is_preferred = '$is_preferred' WHERE plan_id = '$plan_id'");
		if($is_preferred){
			sql_query("UPDATE membership_plans SET is_preferred = 0 WHERE plan_id != '$plan_id'");	
		}
		output();
	}
	else if(!empty($_POST['delete_plan_id'])){
		$plan_id = sql_real_escape_string($_POST['delete_plan_id']);
		if($plan_id == 1){
			$response['error'] = 'This plan cannot be deleted';
			output();	
		}
		sql_query("UPDATE users SET plan_id = '1' WHERE plan_id = '$plan_id'");
		sql_query("DELETE FROM membership_plans WHERE plan_id = '$plan_id'");
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Database failure';
		}	
		output();
	}
	else if(!empty($_POST['update_user_plan'])){
		$plan_id = sql_real_escape_string($_POST['update_user_plan']);
		$uid = sql_real_escape_string($_POST['user_id']);
		$expiry = sql_real_escape_string((int)$_POST['expiry']);
		
		sql_query("UPDATE users SET plan_id = '$plan_id', membership_expiry_time = DATE_ADD(NOW(), INTERVAL $expiry DAY) WHERE user_id = '$uid'");
		if(sql_affected_rows() <= 0){
			$response['error'] = 'Could not update plan';
		}	
		sql_query("UPDATE schedules SET rate_limited = 0 WHERE user_id = '$uid' AND rate_limited = 1");
		output();
	}
	else if(!empty($_POST['lang_del'])){
		$lang_name = $_POST['lang_del'];
		if(preg_match('/[^a-z0-9\_]/i', $lang_name)){
			$response['error'] = 'Invalid language file';
			output();	
		}
		
		$f = __ROOT__.'/lang/'.$lang_name.'.php';
		if(file_exists($f)){
			if(!@unlink($f)){
				$response['error'] = 'Failed to delete language file';
				output();
			}	
		}
		else{
			$response['error'] = 'Invalid language file';
			output();	
		}	
		output();
	}
	else if(!empty($_POST['lang_default'])){
		$lang_name = $_POST['lang_default'];
		if(preg_match('/[^a-z0-9\_]/i', $lang_name)){
			$response['error'] = 'Invalid language file';
			output();	
		}
		
		$f = __ROOT__.'/lang/'.$lang_name.'.php';
		if(file_exists($f)){
			if(!copy($f, __ROOT__.'/lang/default.php')){
				$response['error'] = 'Failed to copy language file';
				output();
			}
		}
		else{
			$response['error'] = 'Invalid language file';
			output();	
		}	
		output();
	}
	else if(!empty($_POST['add_cron'])){
		$cron = get_cron_task_list();
		$lines = array();
		
		foreach($cron as $i => $c){
			$lines[] = 'php '.$cron[$i].' >/dev/null 2>&1';	
		}
		
		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
			$response['error'] = 'Cannot add cron task on windows server. Please use task scheduler to configure the tasks manually';
			$response['cron'] = $cron;
			output();
		} 
		
		$path = get_path_to_crontab();
		if(empty($path)){
			$response['error'] = 'Failed to locate cron tab. Please setup manually';
			$response['cron'] = $cron;
			$response['lines'] = $lines;
			output();
		}
		
		$output = shell_exec($path.' -l');
		if(preg_match('/###SOCIALNINJA_CRONS_START_'.SESSION_NAME.'###(.*)###SOCIALNINJA_CRONS_END_'.SESSION_NAME.'###/s', $output)){
			$response['error'] = 'Cron task already added';
			output();
		}
		
		$output .= "###SOCIALNINJA_CRONS_START_".SESSION_NAME."###".PHP_EOL;
		foreach($lines as $l){
			$output = $output.'* * * * * '.$l.PHP_EOL;
		}
		$output .= "###SOCIALNINJA_CRONS_END_".SESSION_NAME."###";
		
		$ff = dirname(__FILE__).'/tmp/crontab_new.txt';
		file_put_contents($ff, $output);
		
		if(file_exists($ff) && filesize($ff) > 0){
			exec($path.' '.$ff, $o, $c);
			
			if($c){
				$response['error'] = 'Failed to add cron tasks';
				$response['cron'] = $cron;
				$response['lines'] = $lines;
			}
		}
		else{
			$response['error'] = 'Failed to add cron tasks';
			$response['cron'] = $cron;
			$response['lines'] = $lines;	
		}
		@unlink($ff);
		output();
	}
	
	else if(!empty($_POST['remove_cron'])){
		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
			$response['error'] = 'Cannot remove cron task on windows server. Please use task scheduler to configure the tasks manually';
			output();
		} 
		
		$path = get_path_to_crontab();
		if(empty($path)){
			$response['error'] = 'Failed to locate cron tab. Please remove manually';
			output();
		}
		
		$output = shell_exec($path.' -l');
		if(!preg_match('/###SOCIALNINJA_CRONS_START_'.SESSION_NAME.'###(.*)###SOCIALNINJA_CRONS_END_'.SESSION_NAME.'###/s', $output)){
			$response['error'] = 'Cron task not added';
			output();
		}
		
		
		$output = preg_replace('/###SOCIALNINJA_CRONS_START_'.SESSION_NAME.'###(.*)###SOCIALNINJA_CRONS_END_'.SESSION_NAME.'###/s', '', $output);
		
		$ff = dirname(__FILE__).'/tmp/crontab_new.txt';
		@unlink($ff);
		file_put_contents($ff, $output);
		
		if(file_exists($ff)){
			exec($path.' '.$ff, $o, $c);
			if($c){
				$response['error'] = 'Failed to remove cron tasks';
			}
		}
		else{
			$response['error'] = 'Failed to remove cron tasks';
		}
		@unlink($ff);
		output();
	}
}

$response['error'] = 'Data missing or invalid request';
output();
?>