<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$logout_required = true;
include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['resetpw'];

$step = 1;
$success = 0;
$error = '';

/**
 * Email entered
 */
if(!empty($_POST['email']) && !empty($_POST['captcha'])){
	$step = 1;
	$captcha = strtolower(trim($_POST['captcha']));
	
	if($captcha != $_SESSION['captcha'] || empty($_SESSION['captcha'])){
		$_SESSION['captcha'] = '';
		$error = $lang['signup'][4];		
	}
	else{
		$_SESSION['captcha'] = '';
		$email = sql_real_escape_string(strtolower(trim($_POST['email'])));
		$q = sql_query("SELECT user_id, account_status,v_code FROM users WHERE email LIKE '$email'");
		if(!sql_num_rows($q)){
			$error = $lang['signup'][5];
		}
		else{
			list($uid, $status, $vcode) = sql_fetch_row($q);
			if($status != 1)$error = $lang['signup'][6];
			else{
				$r = 1;
				if($vcode)$r = auth_init_pwd_reset_request($uid, $vcode);
				if($r){
					$code = gen_pwd_reset_token($uid);
					$r = send_email($email, 'pwd_reset', array('code' => $code));
					if(!$r)$error = 'Failed to send email';
					else{
						sql_query("UPDATE users SET v_code = '$code' WHERE user_id = '$uid'");
						$success = 1;
					}
				}
				else{
					$error = $lang['signup'][7];	
				}
			}	
		}	
	}
}

/**
 * link clicked and new password submitted
 */

else if(!empty($_GET['c']) && !empty($_POST['password']) && !empty($_POST['password2'])){
	$c = sql_real_escape_string($_GET['c']);
	$q = sql_query("SELECT user_id, account_status,v_code FROM users WHERE v_code = '$c'");
	if(sql_num_rows($q)){
		list($uid, $status, $vcode) = sql_fetch_row($q);
		if($status != 1)$error = 'Your account has been suspended';
		else{
			$r = auth_init_pwd_reset_request($uid, $vcode, 1);
			if($r){
				$step = 2;
				
				$password = sql_real_escape_string($_POST['password']);
				$password2 = sql_real_escape_string($_POST['password2']);
				
				if(strlen($password) < 6)$error = 'Password must be at least 6 characters long';
				else if($password != $password2)$error = 'Passwords do not match';	
				else{
					sql_query("UPDATE users SET v_code = '', password = SHA1('$password'), login_required = 1 WHERE user_id = '$uid'");
					if(!sql_affected_rows())$error = 'Database failure';
					else $success = 1;	
				}
			}
		}	
	}	
}

/**
 * link clicked
 */
else if(!empty($_GET['c'])){
	$c = sql_real_escape_string($_GET['c']);
	$q = sql_query("SELECT user_id, account_status,v_code FROM users WHERE v_code = '$c'");
	if(sql_num_rows($q)){
		list($uid, $status, $vcode) = sql_fetch_row($q);
		if($status != 1)$error = 'Your account has been suspended';
		else{
			$r = auth_init_pwd_reset_request($uid, $vcode, 1);
			if($r){
				$step = 2;	
			}
		}
	}	
}


include(__ROOT__.'/templates/header.php');
include(__ROOT__.'/templates/reset.php');
include(__ROOT__.'/templates/footer.php');
?>