<?php
/**
 * @package Social Ninja
 * @version 1.4
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$logout_required = true;
include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['signup'];

if(empty($settings['enable_signup'])){
	redirect(makeuri('index.php', 1));		
}

$error = '';

/**
 * $success is set to 1 when signup is success
 * $success is set to 2 when signup verification is success
 */
$success = 0;

if(!empty($_GET['v'])){
	$res = $auth->signup_verify();	
	if($res === true)$success = 2;
	else $error = $res;		
}
else if(!empty($_POST['email']) && !empty($_POST['password'])){
	$res = $auth->signup();	
	if($res === true)$success = 1;
	else $error = $res;	
}


include(__ROOT__.'/templates/header.php');
include(__ROOT__.'/templates/signup.php');
include(__ROOT__.'/templates/footer.php');
?>