<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$logout_required = true;
include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['login'];

$error = '';
if(!empty($_POST['email']) && !empty($_POST['password'])){
	$res = $auth->login();	
	if($res === false)$error = $lang['login'][4];
	else if(is_numeric($res)){
		if($res == 2)$error = $lang['login'][5];
		else $error = $lang['login'][6];	
	}
	else if($res === true){
		if(!empty($_POST['r'])){
			header("location:".makeuri(base64_decode($_POST['r']), 1));
			exit();
		}
		else redirect(makeuri('dashboard.php', 1));	
	}
}


include(__ROOT__.'/templates/header.php');
include(__ROOT__.'/templates/login.php');
include(__ROOT__.'/templates/footer.php');
?>