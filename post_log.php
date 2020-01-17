<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$login_required = true;
include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['plog'];

if(empty($_GET['sid']) && empty($_GET['gid'])){
	redirect('index.php');	
}


$from = 1;
$rows = 100;
if(!empty($_GET['from'])){
	$from = (int)$_GET['from'];	
	if($from < 1)$from = 1;
}


if(!empty($_GET['sid'])){
	$schedule_id = sql_real_escape_string($_GET['sid']);
	$is_owner = $auth->is_schedule_owner($user_id, $schedule_id);
	
	if(!$is_owner && empty($user_data['is_admin'])){
		display_error('You do not have permission to view this schedule!');
		exit();	
	}
	
	$total = count_post_log($schedule_id);
	list($gid) = sql_fetch_row(sql_query("SELECT schedule_group_id FROM schedules WHERE schedule_id = '$schedule_id'"));
		
	$post_logs = get_post_logs($schedule_id, $from, $rows);
}
else if(!empty($_GET['gid'])){
	$schedule_group_id = sql_real_escape_string($_GET['gid']);
	$is_owner = $auth->is_schedule_group_owner($user_id, $schedule_group_id);
	
	if(!$is_owner && empty($user_data['is_admin'])){
		display_error('You do not have permission to view this schedule group!');
		exit();	
	}
	
	$total = count_post_log($schedule_group_id, 1);
	$gid = $schedule_group_id;
		
	$post_logs = get_post_logs($schedule_group_id, $from, $rows, 1);
}

include(__ROOT__.'/templates/header.php');
include(__ROOT__.'/templates/post_log.php');
include(__ROOT__.'/templates/footer.php');
?>