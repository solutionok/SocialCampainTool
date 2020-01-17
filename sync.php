<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
$login_required = true;
include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['sync'];

if(empty($_GET['fb_id']) || empty($settings['fb_enabled'])){
	redirect('dashboard.php');	
}

$fb_id = sql_real_escape_string($_GET['fb_id']);
$ok = $auth->is_id_owner($user_id, $fb_id, 'facebook');

if(!$ok){
	redirect('dashboard.php');	
}

include(__ROOT__.'/templates/header.php');
include(__ROOT__.'/templates/sync.php');
include(__ROOT__.'/templates/footer.php');
?>