<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$login_required = true;
include(dirname(__FILE__).'/loader.php');

if(empty($_GET['fid'])){
	redirect('index.php');	
}

$title = $lang['title']['browse_folder'];
$folder_id = sql_real_escape_string($_GET['fid']);
$folder_exists = folder_exists($folder_id);
$is_owner = $auth->is_folder_owner($user_id, $folder_id);

if(!$folder_exists){
	display_error('This folder does not exist!');
	exit();	
}
if(!$is_owner && empty($user_data['is_admin'])){
	display_error('You do not have permission to view this folder!');
	exit();	
}

$from = 1;
$rows = 40;

if(!empty($_GET['from']))$from = (int)$_GET['from'];
if($from < 1)$from = 1;

$name = '';
if(!empty($_GET['q']))$name = sql_real_escape_string($_GET['q']);

$type = '';
if(!empty($_GET['type']))$type = sql_real_escape_string($_GET['type']);

$folder = folder_details($folder_id);
$total_files = get_folder_file_count($folder_id, $name, $type);
if($total_files)$files = get_folder_files($folder_id, $from, $rows, $name, $type);

include(__ROOT__.'/templates/header.php');
include(__ROOT__.'/templates/browse.php');
include(__ROOT__.'/templates/footer.php');
?>