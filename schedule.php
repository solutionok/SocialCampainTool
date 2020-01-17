<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$login_required = true;
include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['sch'];

if(empty($_GET['gid'])){
	redirect('index.php');	
}

$schedule_group_id = sql_real_escape_string($_GET['gid']);
$is_owner = $auth->is_schedule_group_owner($user_id, $schedule_group_id);

if(!$is_owner && empty($user_data['is_admin'])){
	display_error('You do not have permission to view this schedule!');
	exit();	
}

/**
 * Search parameter [site]
 */
$site = '';
if(!empty($_GET['site']))$site = sql_real_escape_string($_GET['site']);

$from = 1;
$rows = 25;

if(!empty($_GET['from']))$from = (int)$_GET['from'];
if($from < 1)$from = 1;

$schedules = array();
$schedule_group = get_schedules_group($schedule_group_id);
$total_schedules = count_schedules_schedules_from_group($schedule_group_id, $site);
if($total_schedules){
	$schedules = get_schedules_schedules_from_group($schedule_group_id, $site, $from, $rows);	
}

include(__ROOT__.'/templates/header.php');
include(__ROOT__.'/templates/schedule.php');
include(__ROOT__.'/templates/footer.php');
?>