<?php
/**
 * @package Social Ninja
 * @version 1.4
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$login_required = true;
include(dirname(__FILE__).'/loader.php');

/**
 * check requested login type and redirect if no type supplied
 */
if(empty($_GET['login_type'])){
	if(!empty($_SESSION['login_type']))$login_type = $_SESSION['login_type'];
	else redirect('index.php');
}
else{
	$login_type = $_GET['login_type'];
	$_SESSION['login_type'] = $login_type;
}

/**
 * login based on login type
 */
switch($login_type):
	case "facebook" :
		if((empty($settings['fb_app_id']) || empty($settings['fb_app_secret']) || empty($settings['fb_scope'])) && empty($_POST['access_token'])){
			display_error($lang['dologin']['nofbapp']);
			exit();	
		}
		if(empty($settings['fb_enabled'])){
			display_error($lang['dologin']['fbdis']);
			exit();	
		}
		if(empty($user_data['use_facebook'])){
			display_error($lang['dologin']['mem_fb_dis']);
			exit();	
		}
		
		require_once dirname(__FILE__)."/sdk/facebook/facebook.php";
		
		if(empty($_POST['access_token'])){
			$facebook = new Facebook(array('appId' => $settings['fb_app_id'], 'secret' => $settings['fb_app_secret']));		
			$user = $facebook->getUser();
		}
		else{
			if(preg_match('/access_token=([a-z0-9\-\_\:]+?)/siU', $_POST['access_token'], $m))$tt = trim($m[1]);
			else $tt = trim($_POST['access_token']);
			
			$facebook = new Facebook(array('appId' => '', 'secret' => ''));		
			$facebook->setAccessToken($tt);
			$user = $facebook->getUser();	
		}
		
		/**
		 * if user id found process login
		 */
		if(!empty($user)){
			$access_token = $facebook->getAccessToken();
			$auth->facebook_login($access_token);
			/**
			 * if error is not empty show it
			 */
			if(!empty($auth->error)){
				display_error($auth->error);
				exit();		
			}
			/**
			 * else redirect to dashboard
			 */
			else{
				$auth->fb_sdk_session_cleanup();
				redirect('sync.php?fb_id='.$auth->fb_id);
			}
			exit();
		}
		
		$scope = $settings['fb_scope'];
		if(!empty($_GET['error_message'])){
			if(preg_match('/user_groups/i', $_GET['error_message'])){
				$scope = str_replace('user_groups', '', $scope);
			}	
		}
		
		$login_config = array('canvas' => 1,'fbconnect' => 1, 'scope' => $scope);
		$login_url = $facebook->getLoginUrl($login_config);
		header("location:".$login_url);
		exit();
	break;
	
	case "twitter" :
		if(empty($settings['tw_app_id']) || empty($settings['tw_app_secret'])){
			display_error($lang['dologin']['notwapp']);
			exit();	
		}
		if(empty($settings['tw_enabled'])){
			display_error($lang['dologin']['twdis']);
			exit();	
		}
		if(empty($user_data['use_twitter'])){
			display_error($lang['dologin']['mem_tw_dis']);
			exit();	
		}
		
		require_once dirname(__FILE__)."/sdk/twitter/twitter.php";
		
		if(!empty($_GET['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])){		
			$twitter = new TwitterOAuth($settings['tw_app_id'], $settings['tw_app_secret'], $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
			$token_credentials = $twitter->getAccessToken($_GET['oauth_verifier']);
			$token = $token_credentials['oauth_token'].':::'.$token_credentials['oauth_token_secret'];
			$auth->twitter_login($token);
			$auth->tw_sdk_session_cleanup();
			
			if(!empty($auth->error)){
				display_error($auth->error);
				exit();		
			}
			else{
				redirect('dashboard.php');	
			}
			exit();
		}
		
		$twitter = new TwitterOAuth($settings['tw_app_id'], $settings['tw_app_secret']);
		$temporary_credentials = $twitter->getRequestToken("http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
		
		if(empty($temporary_credentials['oauth_token']) || empty($temporary_credentials['oauth_token_secret'])){
			display_error($lang['dologin']['fail_tw_oauth']);
			exit();		
		}
		
		$_SESSION['oauth_token'] = $temporary_credentials['oauth_token'];
		$_SESSION['oauth_token_secret'] = $temporary_credentials['oauth_token_secret'];
		
		$login_url = $twitter->getAuthorizeURL($temporary_credentials);
		
		header("location:".$login_url);
		exit();
		
	break;
	
	case "youtube" :
		if(empty($settings['yt_client_id']) || empty($settings['yt_client_secret']) || empty($settings['yt_dev_token'])){
			display_error($lang['dologin']['noytapp']);
			exit();	
		}
		if(empty($settings['yt_enabled'])){
			display_error($lang['dologin']['ytdis']);
			exit();	
		}
		if(empty($user_data['use_youtube'])){
			display_error($lang['dologin']['mem_yt_dis']);
			exit();	
		}
		
		require_once dirname(__FILE__)."/sdk/youtube/youtube.php";
		
		$yt = new Youtube();		
		if(!empty($_GET['code'])){		
			try{
				$yt->client->authenticate($_GET['code']);
			}catch(Exception $e){
				display_error($lang['dologin']['fail_yt_oauth'].'<br/>'.$lang['dologin']['fail_yt_tech'].': '.$e);
				exit();		
			}
			
			$token = $yt->client->getAccessToken();
			$auth->youtube_login($token);
		
			if(!empty($auth->error)){
				display_error($auth->error);
				exit();		
			}
			else{
				redirect('dashboard.php');	
			}
			exit();
		}
	
		$login_url = $yt->getLoginUri();		
		header("location:".$login_url);
		exit;
	break;
endswitch;
?>