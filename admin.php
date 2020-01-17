<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
$login_required = true;
$admin_required = true;

$title = 'Admin Panel';
include(dirname(__FILE__).'/loader.php');

$admin = new admin();
include(__ROOT__.'/templates/header.php');

if(empty($_GET['module'])){
	include(__ROOT__.'/templates/admin.php');
}
else{
	if($_GET['module'] == 'users')include(__ROOT__.'/templates/admin/users.php');	
	else if($_GET['module'] == 'folders')include(__ROOT__.'/templates/admin/folders.php');
	else if($_GET['module'] == 'files')include(__ROOT__.'/templates/admin/files.php');	
	else if($_GET['module'] == 'accounts')include(__ROOT__.'/templates/admin/accounts.php');
	else if($_GET['module'] == 'pages')include(__ROOT__.'/templates/admin/pages.php');
	else if($_GET['module'] == 'schedules')include(__ROOT__.'/templates/admin/schedules.php');
	else if($_GET['module'] == 'videos')include(__ROOT__.'/templates/admin/videos.php');
	else if($_GET['module'] == 'plan')include(__ROOT__.'/templates/admin/plan.php');
	else if($_GET['module'] == 'lang')include(__ROOT__.'/templates/admin/lang.php');
	else if($_GET['module'] == 'payments')include(__ROOT__.'/templates/admin/payments.php');
	else include(__ROOT__.'/templates/admin.php');	
}
include(__ROOT__.'/templates/admin/adminjs.php');
include(__ROOT__.'/templates/footer.php');

?>