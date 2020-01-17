<?php
/**
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
class auth
{
	const FB_API_VERSION = '';
	const LOGIN_REQUIRED = 1;
	const ACC_SUSPENDED = 2;
	const V_REQUIRED = 3;
	
	public $error;
	
	public $fb_sdk;
	public $tw_sdk;
	public $yt_sdk;
	
	public $fb_id;
	public $tw_id;
	public $yt_id;
	
	public $uid;
	public $me;
	
	public $user_data;
	public $extra_data;
	public $new_account;
	public $access_token;
	
	public function __construct()
	{
	}
	
	/**
	 * Function to check login
	 * @return userdata array of current logged in user on success | numeric error code or false on failure
	 */
	public function check_login()
	{
		if(empty($_SESSION[SESSION_NAME])){
			return false;
		}
		
		$uid = sql_real_escape_string($_SESSION[SESSION_NAME]);
		$user_data = $this->get_user_data($uid);
		
		if($user_data === self::LOGIN_REQUIRED){
			unset($_SESSION[SESSION_NAME]);
			return self::LOGIN_REQUIRED;	
		}
		else if($user_data === self::ACC_SUSPENDED){
			unset($_SESSION[SESSION_NAME]);
			return self::ACC_SUSPENDED;	
		}
		else if($user_data === self::V_REQUIRED){
			unset($_SESSION[SESSION_NAME]);
			return self::V_REQUIRED;	
		}
		
		return $user_data;
	}
	
	/**
	 * Function to get user data from mysql
	 * @param int $uid is the user id to fetch data | if no user id is passed current logged in users data is returned
	 * @return false on failure | array of user data on success | numeric code on special cases like account suspension or login check etc
	 */
	public function get_user_data($uid = '')
	{
		if(empty($uid))$uid = sql_real_escape_string($_SESSION[SESSION_NAME]);
		if(empty($uid))return false;
		
		$q = sql_query("SELECT * FROM users LEFT JOIN membership_plans ON membership_plans.plan_id = users.plan_id WHERE users.user_id = '$uid' LIMIT 1");
		$udata = sql_fetch_assoc($q);
		
		if($udata['account_status'] == 2)return self::ACC_SUSPENDED;
		else if($udata['account_status'] > 2)return self::V_REQUIRED;
		else if($udata['login_required'] && empty($_SESSION['pwd_no_logout']))return self::LOGIN_REQUIRED;
		
		$user_data = array();
		$user_data = $udata;
		
		return $user_data;
	}
	
	/**
	 * Function to get users post count for today
	 */
	public function get_users_posted_today($uid, $site = '')
	{
		list($p) = sql_fetch_row(sql_query("SELECT post_count FROM post_counter WHERE user_id = '$uid' AND today = CURDATE() AND site = '$site'"));
		return (int)$p;
	}
	
	/**
	 * Function to login
	 * collects user_id and password from http post
	 */
	public function login()
	{
		$email = sql_real_escape_string($_POST['email']);
		$password = sql_real_escape_string($_POST['password']);
		
		$q = sql_query("SELECT user_id, account_status FROM users WHERE email = '$email' AND password = SHA1('$password')");
		if(sql_num_rows($q)){
			list($uid, $account_status) = sql_fetch_row($q);
			if($account_status == 2)return self::ACC_SUSPENDED;
			else if($account_status > 2)return self::V_REQUIRED;
			$this->set_login($uid);
			return true;
		}
		else return false;
	}
	
	/**
	 * Function to signup
	 * collects user_id and password from http post
	 */
	public function signup()
	{
		global $lang;
		$captcha = @$_POST['captcha'];
		if($captcha != $_SESSION['captcha'] || empty($_SESSION['captcha'])){
			$_SESSION['captcha'] = '';
			return $lang['signup'][4];		
		}
		
		$_SESSION['captcha'] = '';
		$email = sql_real_escape_string($_POST['email']);
		$password = sql_real_escape_string($_POST['password']);
		
		if(!preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $email)){
			return $lang['ajax']['inv_email'];	
		}
		
		if(strlen($password) < 6){
			return $lang['ajax']['pwd_six_char'];	
		}
		
		list($q) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM users WHERE email = '$email'"));
		if(!empty($q)){
			return $lang['ajax']['email_taken'];
		}
		
		$code = base64_encode(sha1(rand().rand().rand().rand().rand()).'|signup|'.time());
		
		$r = send_email($email, 'signup', array('code' => $code));
		
		if($r){
			sql_query("INSERT INTO users (email, password, is_admin, fb_posting, tw_posting, yt_posting, plan_id, v_code, account_status) VALUES('$email', SHA1('$password'), 0, 1, 1, 1, 1, '$code', 3)");
			if(sql_insert_id() > 0){
				$id = sql_insert_id();
				$storage = $id.'_'.rand().rand().rand();
				sql_query("UPDATE users SET storage = '$storage' WHERE user_id = '$id'");
				if(sql_affected_rows() <= 0){
					return $lang['signup'][8];	
				}
				return true;
			}
			return $lang['signup'][8];
		}
		else{
			return $lang['signup'][9];
		}
	}
	
	/**
	 * Function to verify signup
	 * collects code from http get
	 */
	public function signup_verify()
	{
		global $lang;
		$code = sql_real_escape_string($_GET['v']);
		
		list($res) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM users WHERE v_code = '$code' AND account_status = 3"));
		if($res == 1){
			sql_query("UPDATE users SET account_status = 1, v_code = '' WHERE v_code = '$code' AND account_status = 3 LIMIT 1");
			if(sql_affected_rows() > 0){
				return true;	
			}
			return $lang['signup'][8];
		}
		else return $lang['signup'][10];
	}
	
	/**
	 * Function to set login
	 * @param int $uid user_id of authenticated user
	 */
	public function set_login($uid)
	{
		if(empty($uid))$uid = $this->uid;
		$_SESSION[SESSION_NAME] = $uid;
		setcookie(SESSION_NAME, $uid, 0, '/');
		$ip = sql_real_escape_string($_SERVER['REMOTE_ADDR']);
		sql_query("UPDATE users SET last_login_ip = '$ip', last_login_time = NOW(), login_required = 0 WHERE user_id = '$uid'");		
		return true;
	}
	
	/**
	 * Function to login with facebook
	 * @param string $token facebook access token
	 */
	public function facebook_login($token)
	{
		global $settings, $lang;
		
		$facebook = new Facebook(array('appId' => $settings['fb_app_id'], 'secret' => $settings['fb_app_secret']));
		$facebook->setAccessToken($token);
		
		/**
		 * Check if social id is available from access token
		 */
		$fb_id = $facebook->getUser();
		if(empty($fb_id)){
			$this->error = $lang['auth']['fail_uid_fb'];
			$this->fb_sdk_session_cleanup();
			return false;
		}
		
		$this->fb_sdk = $facebook;
		$this->fb_id = $fb_id;
		
		/**
		 * Check permissions
		 */
		$permissions = $facebook->api('/me/permissions');
		
		$scopes = preg_split('/\,/', $settings['fb_scope']);
		$allowed = array();
		/**
		 * workaround for version 1.0
		 */
		$v = 'up'; 
		if(empty($permissions['data'][0]['permission'])){
			foreach(@$permissions['data'][0] as $p => $v){
				if($v == 1)$allowed[] = $p;
			}
			/**
			 * low version
			 */
			$v = 'low';
		}
		/**
		 * for version >= 1.0
		 */
		else{
			foreach(@$permissions['data'] as $p){
				if(@$p['status'] == 'granted')$allowed[] = $p['permission'];
			}
		}
		
		
		foreach($scopes as $scope){
			if(!in_array($scope, $allowed))
			{	
				if($scope == 'user_groups' || $scope == 'user_managed_groups')continue;
				else if($scope == 'publish_pages' && $v == 'low')continue;
				else if($scope == 'user_posts' && $v == 'low')continue;
				else{
					//$this->error = $lang['auth']['fb_allow_all_perms'].' : <b>'.$scope.'</b>';
					//$this->fb_sdk_session_cleanup();
					//return false;
				}
			}
		}
		
		/**
		 * Check if social id is already added in database
		 */
		$q = sql_query("SELECT user_id, account_status FROM fb_accounts WHERE fb_id = '$fb_id'");
		$user_data = sql_fetch_assoc($q);
		$this->new_account = 1;
		
		/**
		 * If added we do not need new account
		 */
		if(!empty($user_data)){
			$this->new_account = 0;
			if($user_data['account_status'] != 1 || !$this->verify_uid($user_data['user_id'])){
				$this->error = $lang['auth']['fb_suspended'];
				$this->fb_sdk_session_cleanup();
				return false;
			}
			/**
			 * If logged in check whether database user id matches with logged in user
			 */			
			if(!empty($_SESSION[SESSION_NAME])){
				if($user_data['user_id'] != $_SESSION[SESSION_NAME]){
					$this->error = $lang['auth']['fb_added_already'];
					$this->fb_sdk_session_cleanup();
					return false;	
				}
				$this->uid = $_SESSION[SESSION_NAME];
			}
			/**
			 * If user is not logged in, log him in
			 */
			else $this->uid = $user_data['user_id'];
		}
		/**
		 * if this social id is not added in database
		 */
		else{
			/**
			 * Add new social account but user must be logged in
			 */
			if(!empty($_SESSION[SESSION_NAME])){
				
				/**
				 * check profile limits
				 */
				global $user_data; 
				if($this->count_user_social_profiles($user_data['user_id'], 'fbprofile') >= $user_data['social_profile_limit_per_site']){
					$this->error = $lang['dologin']['max_allow'].$user_data['social_profile_limit_per_site'].' '.$lang['dologin']['profiles'];
					$this->fb_sdk_session_cleanup();
					return false;	
				} 
				
				$this->uid = $_SESSION[SESSION_NAME];
			}
			else{
				$this->error = $lang['auth']['must_login'];
				$this->fb_sdk_session_cleanup();
				return false;	
			}	
		}
		return $this->add_user_data('facebook');
	}
	
	/**
	 * Function to login with twitter
	 * @param string $token twitter access token
	 */
	public function twitter_login($token)
	{
		global $settings, $lang;
		$this->access_token = $token;
		$token = explode(':::', $token);
		$twitter = new TwitterOAuth($settings['tw_app_id'], $settings['tw_app_secret'], $token[0], $token[1]);
		$tw_account = $twitter->get('account/verify_credentials');
		
		if(empty($tw_account->id)){
			$this->error = $lang['auth']['fail_uid_tw'];
			$this->tw_sdk_session_cleanup();
			return false;		
		}
		
		$tw_id = $tw_account->id;
		
		$this->tw_sdk = $tw_account;
		$this->tw_id = $tw_id;
		
		$q = sql_query("SELECT * FROM tw_accounts WHERE tw_id = '$tw_id'");
		$user_data = sql_fetch_assoc($q);
		$this->new_account = 1;
		
		/**
		 * If added we do not need new account
		 */
		if(!empty($user_data)){
			$this->new_account = 0;
			if($user_data['account_status'] != 1 || !$this->verify_uid($user_data['user_id'])){
				$this->error = $lang['auth']['tw_suspended'];
				$this->tw_sdk_session_cleanup();
				return false;
			}
			/**
			 * If logged in check whether database user id matches with logged in user
			 */			
			if(!empty($_SESSION[SESSION_NAME])){
				if($user_data['user_id'] != $_SESSION[SESSION_NAME]){
					$this->error = $lang['auth']['tw_added_already'];
					$this->tw_sdk_session_cleanup();
					return false;	
				}
				$this->uid = $_SESSION[SESSION_NAME];
			}
			/**
			 * If user is not logged in, log him in
			 */
			else $this->uid = $user_data['user_id'];
		}
		/**
		 * if this social id is not added in database
		 */
		else{
			/**
			 * Add new social account but user must be logged in
			 */
			if(!empty($_SESSION[SESSION_NAME])){
				/**
				 * check profile limits
				 */
				global $user_data;  
				if($this->count_user_social_profiles($user_data['user_id'], 'twitter') >= $user_data['social_profile_limit_per_site']){
					$this->error = $lang['dologin']['max_allow'].$user_data['social_profile_limit_per_site'].' '.$lang['dologin']['profiles'];
					return false;	
				} 
				
				$this->uid = $_SESSION[SESSION_NAME];
			}
			else{
				$this->error = $lang['auth']['must_login'];
				$this->tw_sdk_session_cleanup();
				return false;	
			}	
		}
		
		$this->add_user_data('twitter');
	}
	
	/**
	 * Function to login with youtube
	 * @param string $token twitter access token
	 */
	public function youtube_login($token)
	{
		global $lang;
		$yt_class = new Youtube($token);
		$yt_class->getUser();
		
		if(!empty($yt_class->error) || empty($yt_class->yt_account)){
			$this->error = $lang['auth']['fail_yt_auth'].'<br/>'.$lang['auth']['tech_reason'].': '.$yt_class->response;
			return false;
		}
		
		$yt_id = $yt_class->yt_account['id'];
		$this->yt_sdk = $yt_class;
		$this->yt_id = $yt_id;
		
		$q = sql_query("SELECT * FROM yt_accounts WHERE yt_id = '$yt_id'");
		$user_data = sql_fetch_assoc($q);
		$this->new_account = 1;
		
		/**
		 * If added we do not need new account
		 */
		if(!empty($user_data)){
			$this->new_account = 0;
			if($user_data['account_status'] != 1 || !$this->verify_uid($user_data['user_id'])){
				$this->error = $lang['auth']['yt_suspended'];
				return false;
			}
			/**
			 * If logged in check whether database user id matches with logged in user
			 */			
			if(!empty($_SESSION[SESSION_NAME])){
				if($user_data['user_id'] != $_SESSION[SESSION_NAME]){
					$this->error = $lang['auth']['yt_added_already'];
					return false;	
				}
				$this->uid = $_SESSION[SESSION_NAME];
			}
			/**
			 * If user is not logged in, log him in
			 */
			else $this->uid = $user_data['user_id'];
		}
		/**
		 * if this social id is not added in database
		 */
		else{
			/**
			 * Add new social account but user must be logged in
			 */
			if(!empty($_SESSION[SESSION_NAME])){
				/**
				 * check profile limits
				 */
				global $user_data; 
				if($this->count_user_social_profiles($user_data['user_id'], 'youtube') >= $user_data['social_profile_limit_per_site']){
					$this->error = $lang['dologin']['max_allow'].$user_data['social_profile_limit_per_site'].' '.$lang['dologin']['profiles'];
					return false;	
				} 
				
				$this->uid = $_SESSION[SESSION_NAME];
			}
			else{
				$this->error = $lang['auth']['must_login'];
				return false;	
			}	
		}
		
		$this->add_user_data('youtube');
	}
	
	/**
	 * Add user data based on type
	 * @param string $type facebook|twitter|youtube
	 */
	public function add_user_data($type)
	{
		$ip = sql_real_escape_string($_SERVER['REMOTE_ADDR']);
		
		if($type == 'facebook'){
			$facebook = $this->fb_sdk;
			$fb_id = $this->fb_id;
		
			$me = $facebook->api('/me?fields=first_name,last_name,email,gender');
			$me['access_token'] = $facebook->getAccessToken();
			$this->serialize_fb_data($me);
		}
		else if($type == 'twitter')$this->serialize_tw_data();
		else if($type == 'youtube')$this->serialize_yt_data();
		
		/**
		 * Get site database params
		 */
		list($table, $col) = get_site_params($type);
		
		/**
		 * Add social data into database
		 */
		$ok = $this->add_social_data($table, $col, $this->new_account);
		if(!$ok){
			$this->error = 'Database failure';
			return false;	
		}
		
		if(empty($this->uid) || !empty($this->error)){
			$this->error = 'Database failure';
			return false;
		}
		
		$social_id = (string)$this->$col;					
		
		if($type == 'facebook'){
			if(!empty($_SESSION['pending_fb_app'])){
				/*sql_query("INSERT INTO token_expiry (user_id, social_id, page_id, site, mail_sent, expired_at) SELECT '$this->uid', fb_id, fb_id, 'fbprofile', 1, NOW() FROM fb_accounts WHERE user_id = '$this->uid'");*/
				$app = $_SESSION['pending_fb_app'];
				$app = sql_real_escape_string($app);	
				sql_query("UPDATE users SET fb_app_config = '$app' WHERE user_id = '$this->uid'");
				unset($_SESSION['pending_fb_app']);
			}
			$this->reschedule_token_refresh( $this->uid, $social_id, 'fb' );
			sql_query("DELETE FROM token_expiry WHERE user_id = '$this->uid' AND social_id = '$social_id' AND site LIKE 'fb%'");
		}
		else if($type == 'twitter'){
			if(!empty($_SESSION['pending_tw_app'])){
				sql_query("INSERT INTO token_expiry (user_id, social_id, page_id, site, mail_sent, expired_at) SELECT '$this->uid', tw_id, tw_id, 'twitter', 1, NOW() FROM tw_accounts WHERE user_id = '$this->uid'");
				$app = $_SESSION['pending_tw_app'];
				$app = sql_real_escape_string($app);	
				sql_query("UPDATE users SET tw_app_config = '$app' WHERE user_id = '$this->uid'");
				unset($_SESSION['pending_tw_app']);
			}
			$this->reschedule_token_refresh( $this->uid, $social_id, 'twitter' );
			sql_query("DELETE FROM token_expiry WHERE user_id = '$this->uid' AND social_id = '$social_id' AND site LIKE 'twitter'");
		}
		else if($type == 'youtube'){
			if(!empty($_SESSION['pending_yt_app'])){
				sql_query("INSERT INTO token_expiry (user_id, social_id, page_id, site, mail_sent, expired_at) SELECT '$this->uid', yt_id, yt_id, 'youtube', 1, NOW() FROM yt_accounts WHERE user_id = '$this->uid'");
				$app = $_SESSION['pending_yt_app'];
				$app = sql_real_escape_string($app);	
				sql_query("UPDATE users SET yt_app_config = '$app' WHERE user_id = '$this->uid'");
				unset($_SESSION['pending_yt_app']);
			}
			$this->reschedule_token_refresh( $this->uid, $social_id, 'youtube' );
			sql_query("DELETE FROM token_expiry WHERE user_id = '$this->uid' AND social_id = '$social_id' AND site LIKE 'youtube'");
		}
		
		$this->set_login();
		return true;
	}
	
	public function reschedule_token_refresh( $uid, $social_id, $site )
	{
		$ok = sql_num_rows( sql_query( "SELECT NULL FROM token_expiry WHERE user_id = '$this->uid' AND social_id = '$social_id' AND site LIKE '".( $site == 'fb' ? 'fb%' : $site)."'" ) );
		
		if( $ok ) {
			$q = sql_query("SELECT * FROM schedules LEFT JOIN schedule_groups ON schedule_groups.schedule_group_id = schedules.schedule_group_id LEFT JOIN users ON users.user_id = '$this->uid' WHERE schedules.user_id = '$this->uid' AND schedules.social_id = '$social_id' AND schedules.site LIKE '".( $site == 'fb' ? 'fb%' : $site)."'");	
			
			$o = 0;
			while( $res = sql_fetch_assoc( $q ) ) {
				set_next_schedule_time( $res, 0, time() + $res['schedule_interval'] * $o + 60 );
				$o++;		
			}
		}
	}
	
	/**
	 * Serialize facebook account data to insert into mysql
	 * @param array|object $me facebook|twitter|youtube my account array|object
	 */
	public function serialize_fb_data($me)
	{
		$this->user_data = array();
		
		$this->user_data['first_name'] = sql_real_escape_string($me['first_name']);
		$this->user_data['last_name'] = sql_real_escape_string($me['last_name']);
		$this->user_data['email'] = sql_real_escape_string($me['email']);
		$this->user_data['sex'] = sql_real_escape_string(ucwords($me['gender']));
		$this->user_data['access_token'] = sql_real_escape_string($me['access_token']);
		$this->user_data['profile_pic'] = 'https://graph.facebook.com/'.$this->fb_id.'/picture?type=';
	}
	
	/**
	 * Serialize twitter account data to insert into mysql
	 */
	public function serialize_tw_data()
	{
		$me = $this->tw_sdk;
		$names = explode(' ', $me->name);
		if(empty($names[0]))$names = array($me->screen_name, '');
		
		$this->user_data['first_name'] = sql_real_escape_string($names[0]);
		$this->user_data['last_name'] = sql_real_escape_string(end($names));
		
		$this->user_data['email'] = '';
		$this->user_data['sex'] = '';
		$this->user_data['profile_pic'] = sql_real_escape_string($me->profile_image_url_https);
		
		$this->user_data['access_token'] = sql_real_escape_string($this->access_token);
		
		$this->extra_data = array();
		$this->extra_data['tw_username'] = sql_real_escape_string($me->screen_name);
		$this->extra_data['followers'] = sql_real_escape_string($me->followers_count);
		$this->extra_data['friends'] = sql_real_escape_string($me->friends_count);
		$this->extra_data['status'] = sql_real_escape_string($me->statuses_count);		
	}
	
	/**
	 * Serialize youtube account data to insert into mysql
	 */
	public function serialize_yt_data()
	{
		$me = $this->yt_sdk->yt_account;
		
		if(empty($me['name'])){
			$mm = explode('@', $me['email']);
			$first_name = $mm[0];
			$last_name = $first_name;	
		}
		else{
			$mm = explode(' ', $me['name']);
			$first_name = $mm[0];
			//if(empty($mm[1]))$last_name = $first_name;
			//else 
			$last_name = @$mm[1];		
		}
		
		/**
		 * Default no photo
		 */
		if(empty($me['picture'])){
			$me['picture'] = 'https://lh3.googleusercontent.com/-XdUIqdMkCWA/AAAAAAAAAAI/AAAAAAAAAAA/4252rscbv5M/s88-c-k-no/photo.jpg';	
		}
		else{
			/**
			 * Change photo size to medium
			 */
			if(!preg_match('/-c-k-no/i', $me['picture'])){
				$me['picture'] = str_replace('photo.jpg', 's88-c-k-no/photo.jpg', $me['picture']);	
			}	
		}
		
		$this->user_data['first_name'] = sql_real_escape_string($first_name);
		$this->user_data['last_name'] = sql_real_escape_string($last_name);
		
		$this->user_data['email'] = sql_real_escape_string($me['email']);;
		$this->user_data['sex'] = sql_real_escape_string(ucwords($me['gender']));
		$this->user_data['profile_pic'] = sql_real_escape_string($me['picture']);
		
		$token = json_decode($this->yt_sdk->raw_token, true);
		$token['yt_id'] = $this->yt_id;
		$this->user_data['access_token'] = sql_real_escape_string(json_encode($token));
		
		$this->extra_data = array();
		$this->extra_data['yt_username'] = sql_real_escape_string($me['channel']['id']);
		$this->extra_data['followers'] = sql_real_escape_string($me['channel']['statistics']['subscriberCount']);
		$this->extra_data['comments'] = sql_real_escape_string($me['channel']['statistics']['commentCount']);
		$this->extra_data['views'] = sql_real_escape_string($me['channel']['statistics']['viewCount']);
		$this->extra_data['videos'] = sql_real_escape_string($me['channel']['statistics']['videoCount']);
	}
	
	/**
	 * Add social data into database
	 * @param string $table social user database table fb_accounts|tw_accounts|yt_accounts
	 * @param string $col social user id column fb_id|tw_id|yt_id
	 * @param int $new whether it is a new user or old user
	 */
	public function add_social_data($table, $col ,$new = 0)
	{
		$social_id = (string)$this->$col;
		$ok = 0;
		if($new){
			sql_query("INSERT IGNORE INTO `$table` (`user_id`, `$col`, `first_name`, `last_name`, `email`, `profile_pic`, `sex`, `access_token`, `access_token_at`, `account_status`) VALUES('$this->uid', '$social_id', '{$this->user_data['first_name']}', '{$this->user_data['last_name']}', '{$this->user_data['email']}', '{$this->user_data['profile_pic']}', '{$this->user_data['sex']}', '{$this->user_data['access_token']}', NOW(), 1)");	
		}
		else{
			sql_query(
				"UPDATE `$table` SET 
					first_name = '{$this->user_data['first_name']}',
					last_name = '{$this->user_data['last_name']}',
					email = '{$this->user_data['email']}',
					profile_pic = '{$this->user_data['profile_pic']}',
					sex = '{$this->user_data['sex']}',
					access_token = '{$this->user_data['access_token']}',
					access_token_at = NOW()
				WHERE $col = '$social_id'
			");	
		}
		
		$ok = sql_affected_rows() > 0 ? 1 : 0;
		/**
		 * Add site specific extra social data
		 */
		if($ok){
			foreach($this->extra_data as $k => $v){
				sql_query("UPDATE $table SET `$k` = '$v' WHERE $col = '$social_id'");	
			}	
		}
		return $ok;
	}
	
	/**
	 * Function to save user fan pages into database
	 * @param $access_token is the access token
	 * @return array list of not updated facebook pages
	 */
	public function save_fan_pages($access_token, $fb_id, $user_id)
	{
		global $user_data;
		sql_query("UPDATE fb_pages SET last_update = '0000-00-00 00:00:00' WHERE fb_id = '$fb_id' AND user_id = '$user_id' AND manual_entry = 0");
		
		do{
			if(empty($next))$url = 'https://graph.facebook.com/'.self::FB_API_VERSION.'/me/accounts?limit=100&access_token='.$access_token.'&fields=name,likes,access_token,category';
			else $url = $next;
			
			$response = curl_single($url);
			$data = json_decode($response, true);
				
			if(empty($data['data'])){
				break;	
			}
			
			$j = 0;
			foreach($data['data'] as $i => $page){
				$name = sql_real_escape_string($page['name']);
				$likes = sql_real_escape_string($page['likes']);
				$category = sql_real_escape_string($page['category']);
				$access_token = sql_real_escape_string($page['access_token']);
				$page_id = sql_real_escape_string($page['id']);
				
				sql_query("INSERT INTO fb_pages (user_id, fb_id, page_id, page_name, likes, category, access_token, last_update, account_status)
							VALUES
							('$user_id',
							'$fb_id',
							'$page_id',
							'$name',
							'$likes',
							'$category',
							'$access_token',
							NOW(),
							1) 
							ON DUPLICATE KEY UPDATE
							page_name = '$name',
							likes = '$likes',
							category = '$category',
							access_token = '$access_token',
							last_update = NOW()");
				
				$j++;
				if($j >= $user_data['page_group_event_limit'])break;
			}
			
			if($j >= $user_data['page_group_event_limit'])break;
			$next = @$data['paging']['next'];
			if(empty($next))break;

		}while(1);
		
		$fail = array();
		$q = sql_query("SELECT * FROM fb_pages WHERE last_update = '0000-00-00 00:00:00' AND fb_id = '$fb_id' AND user_id = '$user_id'");
		while($res = sql_fetch_assoc($q))$fail[] = $res;
		return $fail;
	}
	
	/**
	 * Function to save user fan groups into database
	 * @param $access_token is the access token
	 * @return array list of not updated facebook pages
	 */
	public function save_fan_groups($access_token, $fb_id, $user_id)
	{
		global $user_data;
		$token = sql_real_escape_string($access_token);
		sql_query("UPDATE fb_groups SET last_update = '0000-00-00 00:00:00' WHERE fb_id = '$fb_id' AND user_id = '$user_id' AND manual_entry = 0");
		
		do{
			if(empty($next))$url = 'https://graph.facebook.com/'.self::FB_API_VERSION.'/me/groups?limit=100&access_token='.$access_token;
			else $url = $next;
			
			$response = curl_single($url);
			$data = json_decode($response, true);
				
			if(empty($data['data'])){
				break;	
			}
			
			$j = 0;
			foreach($data['data'] as $i => $group){
				$name = sql_real_escape_string($group['name']);
				$group_id = sql_real_escape_string($group['id']);
				$privacy = sql_real_escape_string($group['privacy']);
							
				sql_query("INSERT INTO fb_groups (user_id, fb_id, group_id, group_name, privacy, access_token ,last_update, account_status)
							VALUES
							('$user_id',
							'$fb_id',
							'$group_id',
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
							
				$j++;
				if($j >= $user_data['page_group_event_limit'])break;
			}
			
			if($j >= $user_data['page_group_event_limit'])break;
			$next = @$data['paging']['next'];
			if(empty($next))break;

		}while(1);
		
		$fail = array();
		$q = sql_query("SELECT * FROM fb_groups WHERE last_update = '0000-00-00 00:00:00' AND fb_id = '$fb_id' AND user_id = '$user_id'");
		while($res = sql_fetch_assoc($q))$fail[] = $res;
		return $fail;
	}
	
	/**
	 * Function to save user events into database
	 * @param $access_token is the access token
	 * @return array list of not updated facebook events
	 */
	public function save_events($access_token, $fb_id, $user_id)
	{
		global $user_data;
		$token = sql_real_escape_string($access_token);
		sql_query("UPDATE fb_events SET last_update = '0000-00-00 00:00:00' WHERE fb_id = '$fb_id' AND user_id = '$user_id' AND manual_entry = 0");
		
		do{
			if(empty($next))$url = 'https://graph.facebook.com/'.self::FB_API_VERSION.'/me/events?limit=100&access_token='.$access_token;
			else $url = $next;
			
			$response = curl_single($url);
			$data = json_decode($response, true);
				
			if(empty($data['data'])){
				break;	
			}
			
			$j = 0;
			foreach($data['data'] as $i => $event){
				$name = sql_real_escape_string($event['name']);
				$event_id = sql_real_escape_string($event['id']);
				$start_time = sql_real_escape_string(@$event['start_time']);
							
				sql_query("INSERT INTO fb_events (user_id, fb_id, event_id, event_name, start_time, access_token ,last_update, account_status)
							VALUES
							('$user_id',
							'$fb_id',
							'$event_id',
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
				
				$j++;			
				if($j >= $user_data['page_group_event_limit'])break;
			}
			
			if($j >= $user_data['page_group_event_limit'])break;
			$next = @$data['paging']['next'];
			if(empty($next))break;

		}while(1);
		
		$fail = array();
		$q = sql_query("SELECT * FROM fb_events WHERE last_update = '0000-00-00 00:00:00' AND fb_id = '$fb_id' AND user_id = '$user_id'");
		while($res = sql_fetch_assoc($q))$fail[] = $res;
		return $fail;
	}
	
	/**
	 * Function to check if uid is banned or not before login
	 * @param int $uid uid to check
	 */
	public function verify_uid($uid)
	{
		$q = sql_query("SELECT account_status FROM users WHERE user_id = '$uid'");
		list($status) = sql_fetch_row($q);
		return $status == 1 ? true : false;
	}
	
	/**
	 * Function to cleanup php sdk session
	 */
	public function fb_sdk_session_cleanup()
	{
		foreach($_SESSION as $k => $v)if(preg_match('/_access_token|_user_id/i', $k))unset($_SESSION[$k]);
	}
	
	/**
	 * Function to cleanup php sdk session
	 */
	public function tw_sdk_session_cleanup()
	{
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);
	}
	
	/**
	 * Function to count user folders
	 * @param int $uid user id
	 * @return int $total count
	 */
	public function count_user_folders($uid, $name = '')
	{
		$search = $name ? " (folder_name LIKE '%$name%' OR folder_id = '$name') " : ' 1 ';
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM folders WHERE user_id = '$uid' AND $search"));
		return $count;
	}
	
	/**
	 * Function to count user categories
	 * @param int $uid user id
	 * @return int $total count
	 */
	public function count_user_categories($uid, $name = '')
	{
		$search = $name ? " category_name LIKE '%$name%' " : ' 1 ';
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM user_categories WHERE user_id = '$uid' AND $search"));
		return $count;
	}
	
	/**
	 * Function to count user rss feeds
	 * @param int $uid user id
	 * @return int $total count
	 */
	public function count_user_rss($uid, $name = '')
	{
		$search = $name ? " feed_name LIKE '%$name%' " : ' 1 ';
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM rss_feeds WHERE user_id = '$uid' AND $search"));
		return $count;
	}
	
	/**
	 * Function to count user pages
	 * @param int $uid user id
	 * @return int $total count
	 */
	public function count_user_pages($uid, $name = '')
	{
		$search = $name ? " page_name LIKE '%$name%' " : ' 1 ';
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM fb_pages WHERE user_id = '$uid' AND $search"));
		return $count;
	}
	
	/**
	 * Function to count user groups
	 * @param int $uid user id
	 * @return int $total count
	 */
	public function count_user_groups($uid, $name = '')
	{
		$search = $name ? " group_name LIKE '%$name%' " : ' 1 ';
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM fb_groups WHERE user_id = '$uid' AND $search"));
		return $count;
	}
	
	/**
	 * Function to count user events
	 * @param int $uid user id
	 * @return int $total count
	 */
	public function count_user_events($uid, $name = '')
	{
		$search = $name ? " event_name LIKE '%$name%' " : ' 1 ';
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM fb_events WHERE user_id = '$uid' AND $search"));
		return $count;
	}
	
	/**
	 * Function to count user schedule groups
	 * @param int $uid user id
	 * @return int $total count
	 */
	public function count_user_schedule_groups($uid, $name = '')
	{
		$search = $name ? " schedule_group_name LIKE '%$name%' " : ' 1 ';
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM schedule_groups WHERE user_id = '$uid' AND $search"));
		return $count;
	}
	
	/**
	 * Function to count user schedules
	 * @param int $uid user id
	 * @return int $total count
	 */
	public function count_user_schedules($uid)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM schedule_groups WHERE user_id = '$uid'"));
		return $count;
	}
	
	/**
	 * Function to count user queued videos for edit
	 * @param int $uid user id
	 * @return int $total count
	 */
	public function count_user_queued_videos($uid)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM video_editor_queue WHERE user_id = '$uid'"));
		return $count;
	}
	
	/**
	 * Function to count user queued pending videos for edit
	 * @param int $uid user id
	 * @return int $total count
	 */
	public function count_user_pending_videos($uid)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM video_editor_queue WHERE user_id = '$uid' AND is_done = 0"));
		return $count;
	}
	
	/**
	 * Function to count user social profile for a given site
	 * @param int $uid user id
	 * @param string $site site name
	 * @return int $total count
	 */
	public function count_user_social_profiles($uid, $site)
	{
		list($table) = get_site_params($site);
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM $table WHERE user_id = '$uid'"));
		return $count;
	}
	
	/**
	 * Function to fetch user post log
	 * @return $logs array
	 */
	public function get_user_posts($user_id)
	{
		$posts = array();
		$q = sql_query("SELECT *
						FROM post_log 
					  WHERE user_id = '$user_id' AND is_hidden = 0
					  ORDER BY posted_at DESC
					  LIMIT 0,50");
				  
		while($res = sql_fetch_assoc($q)){
			$posts[] = $res;	
		}
		
		return $posts;
	}
	
	/**
	 * Function to fetch user error log
	 * @return $errs array
	 */
	public function get_user_errors($user_id)
	{
		$errs = array();
		$q = sql_query("SELECT *
						FROM error_msg 
					  WHERE user_id = '$user_id'
					  ORDER BY added_at DESC
					  LIMIT 0,50");
				  
		while($res = sql_fetch_assoc($q)){
			$errs[] = $res;	
		}
		
		return $errs;
	}
	
	/**
	 * Function to fetch user facebook ids
	 * @return $string html options elements
	 */
	public function get_user_fb_ids($user_id)
	{
		global $lang;
		$q = sql_query("SELECT 
							fb_accounts.fb_id AS id, 
							CONCAT(fb_accounts.first_name, ' ', fb_accounts.last_name) AS name, 
							'fbprofile' AS site, 
							fb_accounts.access_token_at,
							token_expiry.social_id AS token_expired 
						  FROM fb_accounts 
						  LEFT JOIN token_expiry ON 
							token_expiry.social_id = fb_accounts.fb_id AND 
							token_expiry.site = 'fbprofile' 
						  WHERE 
						  fb_accounts.user_id = '$user_id'");
				  
		$data = '<option value="">'.$lang['common'][13].'</option>';
		while($res = sql_fetch_assoc($q)){
			$data .= '<option value="'.$res['id'].'">
						'.$res['name'].' [LAST UPDATE: '.get_formatted_time($res['access_token_at']).'] '.(empty($res['token_expired']) ? '' : 'EXPIRED').'
					  </option>';	
		}
		
		return $data;
	}
	
	/**
	 * Function to fetch user folders
	 * @param int $uid user id
	 * @param int $from from index
	 * @param int $rows how many records?
	 * @param string $name search by name
	 * @params bool $html whether to return html for select
	 * @return array $results
	 */
	public function get_user_folders($uid, $from = 1, $rows = 100, $name = '', $html = 0)
	{
		$from--;
		$search = $name ? " (folder_name LIKE '%$name%' OR folder_id = '$name') " : ' 1 ';
		$q = sql_query("SELECT * FROM folders WHERE user_id = '$uid' AND $search LIMIT $from, $rows");
		$folders = array();
		$data = '';
		
		while($res = sql_fetch_assoc($q)){
			if($html){
				$f = $res['folder_id'];
				$n = $res['folder_name'];
				if($html != 1){
					$f = 'FOLDER:'.$res['folder_id'];
					$n = 'FOLDER: '.$res['folder_name'].' ['.$res['file_count'].' files]';	
				}
				$data .= '<option value="'.$f.'">'.$n.'</option>';
			}
			else $folders[] = $res;
		}
		if($html == 1){
			$data .= '<option value="WATERMARK">IMPORT TO WATERMARK</option>';
			$data .= '<option value="FRAME">IMPORT TO VIEW FINDERS</option>';
		}
		
		if($html)return $data;
		return $folders;
	}
	
	/**
	 * Function to fetch user categories
	 * @param int $uid user id
	 * @param int $from from index
	 * @param int $rows how many records?
	 * @param string $name search by name
	 * @params bool $html whether to return html for select
	 * @return array $results
	 */
	public function get_user_categories($uid, $from = 1, $rows = 100, $name = '', $html = 0)
	{
		$from--;
		$search = $name ? " category_name LIKE '%$name%' " : ' 1 ';
		$q = sql_query("SELECT * FROM user_categories WHERE user_id = '$uid' AND $search ORDER BY category_id DESC LIMIT $from, $rows");
		$folders = array();
		$data = '';
		
		while($res = sql_fetch_assoc($q)){
			if($html){
				$f = $res['category_id'];
				$n = $res['category_name'];
				$data .= '<option value="'.$f.'">'.$n.'</option>';
			}
			else $folders[] = $res;
		}
		if($html)return $data;
		return $folders;
	}
	
	/**
	 * Function to fetch user rss
	 * @param int $uid user id
	 * @param int $from from index
	 * @param int $rows how many records?
	 * @param string $name search by name
	 * @params bool $html whether to return html for select
	 * @return array $results
	 */
	public function get_user_rss($uid, $from = 1, $rows = 100, $name = '', $html = 0)
	{
		$from--;
		$search = $name ? " feed_name LIKE '%$name%' " : ' 1 ';
		$q = sql_query("SELECT * FROM rss_feeds WHERE user_id = '$uid' AND $search LIMIT $from, $rows");
		$rss = array();
		$data = '';
		while($res = sql_fetch_assoc($q)){
			if($html){
				$f = $res['rss_feed_id'];
				$n = $res['feed_name'];
				if($html != 1){
					$f = 'RSS:'.$res['rss_feed_id'];
					$n = 'RSS: '.$res['feed_name'];	
				}
				$data .= '<option value="'.$f.'">'.$n.'</option>';
			}
			else $rss[] = $res;
		}
		if($html)return $data;	
		return $rss;
	}
	
	/**
	 * Function to fetch user watermarks
	 * @param int $uid user id
	 * @params bool $html whether to return html for select
	 * @params bool $full_path whether to add full path to image or just filename
	 * @return array $results
	 */
	public function get_user_watermarks($uid, $html = 0, $full_path = 1)
	{
		global $user_data;
		$q = sql_query("SELECT * FROM creator_tools WHERE user_id = '$uid' AND tool_type = 'watermark' LIMIT 100");
		$tools = array();
		$data = '';
		while($res = sql_fetch_assoc($q)){
			if($html){
				$p = site_url().'/storage/'.$user_data['storage'].'/'.$res['filename'];
				if(!$full_path)$p = $res['filename'];
				$data .= '<option value="'.$p.'">'.$res['original_name'].'</option>';
			}
			else $tools[] = $res;
		}
		if($html)return $data;
		return $tools;
	}
	
	/**
	 * Function to fetch user frames
	 * @param int $uid user id
	 * @params bool $html whether to return html for select
	 * @return array $results
	 */
	public function get_user_frames($uid, $html = 0)
	{
		global $user_data;
		$q = sql_query("SELECT * FROM creator_tools WHERE user_id = '$uid' AND tool_type = 'frame' LIMIT 100");
		$tools = array();
		$data = '';
		while($res = sql_fetch_assoc($q)){
			if($html)$data .= '<option value="'.site_url().'/storage/'.$user_data['storage'].'/'.$res['filename'].'">'.$res['original_name'].'</option>';	
			else $tools[] = $res;
		}
		if($html){			
			$vf = __ROOT__.'/images/vf';
			$vff = scandir($vf);
			foreach($vff as $vfff)if($vfff != '.' && $vfff != '..')$data .= '<option value="'.site_url().'/images/vf/'.$vfff.'">DEFAULT: '.$vfff.'</option>';
			return $data;	
		}
		return $tools;
	}
	
	/**
	 * Function to fetch user pages
	 * @param int $uid user id
	 * @param int $from from index
	 * @param int $rows how many records?
	 * @param string $name search by name
	 * @return array $results
	 */
	public function get_user_pages($uid, $from = 1, $rows = 100, $name = '')
	{
		$from--;
		$search = $name ? " fb_pages.page_name LIKE '%$name%' " : ' 1 ';
		$q = sql_query("SELECT fb_pages.*, fb_accounts.*, fb_pages.account_status AS account_status
							FROM fb_pages 
							LEFT JOIN fb_accounts ON fb_accounts.user_id = '$uid' AND fb_accounts.fb_id = fb_pages.fb_id
						  WHERE fb_pages.user_id = '$uid' AND $search LIMIT $from, $rows");
		$pages = array();
		while($res = sql_fetch_assoc($q))$pages[] = $res;
		return $pages;
	}
	
	/**
	 * Function to fetch user groups
	 * @param int $uid user id
	 * @param int $from from index
	 * @param int $rows how many records?
	 * @param string $name search by name
	 * @return array $results
	 */
	public function get_user_groups($uid, $from = 1, $rows = 100, $name = '')
	{
		$from--;
		$search = $name ? " fb_groups.group_name LIKE '%$name%' " : ' 1 ';
		$q = sql_query("SELECT fb_groups.*, fb_accounts.*, fb_groups.account_status AS account_status
							FROM fb_groups 
							LEFT JOIN fb_accounts ON fb_accounts.user_id = '$uid' AND fb_accounts.fb_id = fb_groups.fb_id
						  WHERE fb_groups.user_id = '$uid' AND $search LIMIT $from, $rows");
		$groups = array();
		while($res = sql_fetch_assoc($q))$groups[] = $res;
		return $groups;
	}
	
	/**
	 * Function to fetch user events
	 * @param int $uid user id
	 * @param int $from from index
	 * @param int $rows how many records?
	 * @param string $name search by name
	 * @return array $results
	 */
	public function get_user_events($uid, $from = 1, $rows = 100, $name = '')
	{
		$from--;
		$search = $name ? " fb_events.event_name LIKE '%$name%' " : ' 1 ';
		$q = sql_query("SELECT fb_events.*, fb_accounts.*, fb_events.account_status AS account_status 
							FROM fb_events 
							LEFT JOIN fb_accounts ON fb_accounts.user_id = '$uid' AND fb_accounts.fb_id = fb_events.fb_id
						  WHERE fb_events.user_id = '$uid' AND $search LIMIT $from, $rows");
		$events = array();
		while($res = sql_fetch_assoc($q))$events[] = $res;
		return $events;
	}
	
	/**
	 * Function to fetch user schedule groups
	 * @param int $uid user id
	 * @param int $from from index
	 * @param int $rows how many records?
	 * @param string $name search by name
	 * @return array $results
	 */
	public function get_user_schedule_groups($uid, $from = 1, $rows = 100, $name = '')
	{
		$from--;
		$search = $name ? " schedule_groups.schedule_group_name LIKE '%$name%' " : ' 1 ';
		$q = sql_query("SELECT *,
							UNIX_TIMESTAMP(post_start_from) AS post_start_from,
							UNIX_TIMESTAMP(post_end_at) AS post_end_at
							FROM schedule_groups
							WHERE user_id = '$uid' AND $search
							ORDER BY schedule_group_id DESC
						  LIMIT $from, $rows");
		$schedules = array();
		while($res = sql_fetch_assoc($q)){
			if($res['post_start_from'])$res['post_start_from'] = date('Y-m-d', $res['post_start_from']);
			if($res['post_end_at'])$res['post_end_at'] = date('Y-m-d', $res['post_end_at']);
			$res['has_errors'] = 0;
			list($e) = sql_fetch_row(sql_query("SELECT notes FROM schedules WHERE schedule_group_id = '".$res['schedule_group_id']."' AND notes != '' LIMIT 1"));
			if(!empty($e))$res['has_errors'] = $e; 
			$schedules[] = $res;
		}
		return $schedules;
	}
	
	/**
	 * Function to fetch user queued videos for edit
	 * @param int $uid user id
	 * @param int $from from index
	 * @param int $rows how many records?
	 * @return array $results
	 */
	public function get_user_queued_videos($uid, $from = 1, $rows = 100)
	{
		$from--;
		$q = sql_query("SELECT *
						FROM video_editor_queue
						WHERE user_id = '$uid'
					  LIMIT $from, $rows");
		$ques = array();
		while($res = sql_fetch_assoc($q))$ques[] = $res;
		return $ques;
	}
	
	/**
	 * Function to fetch file meta information from database
	 * @param int $uid user id
	 * @param int $file_id file_id id
	 * @return array file meta on success | null on failure
	 */
	public function get_user_file_meta($uid, $file_id)
	{
		return sql_fetch_assoc(sql_query("SELECT * FROM file_meta WHERE user_id = '$uid' AND file_id = '$file_id' LIMIT 1"));
	}
	
	/**
	 * Function to fetch link meta information from database
	 * @param int $uid user id
	 * @param int $file_id file_id id
	 * @return array file meta on success | null on failure
	 */
	public function get_user_link_meta($uid, $file_id)
	{
		return sql_fetch_assoc(sql_query("SELECT * FROM link_meta WHERE user_id = '$uid' AND file_id = '$file_id' LIMIT 1"));
	}
	
	/**
	 * Function to check if a user owns a social id account
	 * @param int $uid user id
	 * @param int $sid page id
	 * @param string $site site name
	 * @return array(social id (owner), access token, username) on success or false on failure
	 */
	public function is_id_owner($uid, $sid, $site, $ownerid = '')
	{
		list($table, $col, $uname_col, $b, $sid_col) = get_site_params($site);
		$q = sql_query("SELECT $sid_col, $uname_col ,access_token FROM $table WHERE user_id = '$uid' AND $col = '$sid' AND account_status = 1 ".($ownerid == '' ? '' : " AND $sid_col = '$ownerid' ")." LIMIT 1");
		if(!sql_num_rows($q))return false;
		list($social_id, $uname, $access_token) = sql_fetch_row($q);
		return array($social_id, $access_token, $uname);
	}
	
	/**
	 * Function to check if a user owns a folder
	 * @param int $uid user id
	 * @param int $folder_id folder id
	 * @return int $total count
	 */
	public function is_folder_owner($uid, $folder_id)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM folders WHERE user_id = '$uid' AND folder_id = '$folder_id' LIMIT 1"));
		return $count;
	}
	
	/**
	 * Function to check if a user owns a category
	 * @param int $uid user id
	 * @param int $cat_id cat id
	 * @return int $total count
	 */
	public function is_category_owner($uid, $cat_id)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM user_categories WHERE user_id = '$uid' AND category_id = '$cat_id' LIMIT 1"));
		return $count;
	}
	
	/**
	 * Function to check if a user owns a rss feed
	 * @param int $uid user id
	 * @param int $rss_id rss id
	 * @return int $total count
	 */
	public function is_rss_owner($uid, $rss_id)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM rss_feeds WHERE user_id = '$uid' AND rss_feed_id = '$rss_id' LIMIT 1"));
		return $count;
	}
	
	/**
	 * Function to check if a user owns a schedule group
	 * @param int $uid user id
	 * @param int $sch_id schedule id
	 * @return int $total count
	 */
	public function is_schedule_group_owner($uid, $sch_id)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM schedule_groups WHERE user_id = '$uid' AND schedule_group_id = '$sch_id' LIMIT 1"));
		return $count;
	}
	
	/**
	 * Function to check if a user owns a schedule
	 * @param int $uid user id
	 * @param int $sch_id schedule id
	 * @return int $total count
	 */
	public function is_schedule_owner($uid, $sch_id)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM schedules WHERE user_id = '$uid' AND schedule_id = '$sch_id' LIMIT 1"));
		return $count;
	}
	
	/**
	 * Function to check if a user owns a file
	 * @param int $uid user id
	 * @param int $file_id file_id id
	 * @return int $total count
	 */
	public function is_file_owner($uid, $file_id)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM files WHERE user_id = '$uid' AND file_id = '$file_id' LIMIT 1"));
		return $count;
	}
	
	/**
	 * Function to check if a user owns a file
	 * @param int $uid user id
	 * @param string $file_name file_name
	 * @return int $total count
	 */
	public function is_file_owner_byname($uid, $file_name)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM files WHERE user_id = '$uid' AND filename = '$file_name' LIMIT 1"));
		return $count;
	}
	
	/**
	 * Function to check if a user owns a tool i.e. watermark or frame
	 * @param int $uid user id
	 * @param string $file_name file_name
	 * @return int $total count
	 */
	public function is_tool_owner_byname($uid, $file_name)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM creator_tools WHERE user_id = '$uid' AND filename = '$file_name' LIMIT 1"));
		return $count;
	}
	
	
	/**
	 * Function to check if a user owns a post log
	 * @param int $uid user id
	 * @param int $post_log_id post log id
	 * @return int $total count
	 */
	public function is_post_log_owner($uid, $post_log_id)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM post_log WHERE user_id = '$uid' AND post_log_id = '$post_log_id' LIMIT 1"));
		return $count;
	}
	
	/**
	 * Function to check if a user owns a video queue
	 * @param int $uid user id
	 * @param int $vq_id video queue id
	 * @return int $total count
	 */
	public function is_video_queue_owner($uid, $vq_id)
	{
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM video_editor_queue WHERE user_id = '$uid' AND queue_id = '$vq_id' LIMIT 1"));
		return $count;
	}
	
	/**
	 * Function to get disk space used by user
	 * @param int $uid user id
	 * @param bool $pretty format it to human readable? 
	 * @return string $used_space
	 */
	public function get_user_used_space($uid, $pretty = 0)
	{
		$data = sql_fetch_assoc(sql_query("SELECT storage FROM users WHERE user_id = '$uid'"));
		$size = dirSize(__ROOT__.'/storage/'.$data['storage']);
		sql_query("UPDATE users SET used_storage = '$size' WHERE user_id = '$uid'");
		if(!$pretty)return $size;
		return formatSize($size);
	}
	
	/**
	 * Function to get list of schedulable user profiles
	 * @param int $uid user id
	 * @param bool $html format it to html? 
	 * @return array|string list of pages
	 */
	public function get_user_pages_list($uid, $html = 0)
	{
		global $lang;
		$data = '<option value="">'.$lang['common'][13].'</option>';
		$accounts = array();
		$q = sql_query("SELECT fb_id AS id, CONCAT(first_name, ' ', last_name) AS name, 'fbprofile' AS site FROM fb_accounts WHERE user_id = '$uid'");
		$t = sql_num_rows($q);
		if($t && $html)$data .= '<optgroup label="Facebook accounts">';
		while($res = sql_fetch_assoc($q)){
			$accounts[] = $res;
			if($html)$data .= '<option value="'.$res['id'].'" rel="'.$res['site'].'" rel-owner="'.$res['id'].'">'.$res['name'].' ['.$res['site'].']</option>';	
		}
		if($t && $html)$data .= '</optgroup>';
		
		$q = sql_query("SELECT CONCAT(fb_accounts.first_name,' ', fb_accounts.last_name) AS owner_name, fb_pages.page_id AS id, fb_pages.page_name AS name, 'fbpage' AS site, fb_pages.fb_id FROM fb_pages LEFT JOIN fb_accounts ON fb_accounts.fb_id = fb_pages.fb_id WHERE fb_pages.user_id = '$uid' AND fb_accounts.fb_id IS NOT NULL");
		$t = sql_num_rows($q);
		if($t && $html)$data .= '<optgroup label="Facebook pages">';
		while($res = sql_fetch_assoc($q)){
			$accounts[] = $res;
			if($html)$data .= '<option value="'.$res['id'].'" rel="'.$res['site'].'" rel-owner="'.$res['fb_id'].'">'.$res['name'].' ['.$res['site'].' by '.$res['owner_name'].']</option>';	
		}
		if($t && $html)$data .= '</optgroup>';
		
		$q = sql_query("SELECT CONCAT(fb_accounts.first_name,' ', fb_accounts.last_name) AS owner_name, fb_groups.group_id AS id, fb_groups.group_name AS name, 'fbgroup' AS site, fb_groups.fb_id, fb_groups.privacy FROM fb_groups LEFT JOIN fb_accounts ON fb_accounts.fb_id = fb_groups.fb_id WHERE fb_groups.user_id = '$uid' AND fb_accounts.fb_id IS NOT NULL");
		$t = sql_num_rows($q);
		if($t && $html)$data .= '<optgroup label="Facebook groups">';
		while($res = sql_fetch_assoc($q)){
			if($html)$data .= '<option value="'.$res['id'].'" rel="'.$res['site'].'" rel-owner="'.$res['fb_id'].'" rel-privacy="'.$res['privacy'].'">'.$res['name'].' ['.$res['site'].' by '.$res['owner_name'].']</option>';	
			$accounts[] = $res;
		}
		if($t && $html)$data .= '</optgroup>';
		
		$q = sql_query("SELECT CONCAT(fb_accounts.first_name,' ', fb_accounts.last_name) AS owner_name, fb_events.event_id AS id, fb_events.event_name AS name, 'fbevent' AS site, fb_events.fb_id FROM fb_events LEFT JOIN fb_accounts ON fb_accounts.fb_id = fb_events.fb_id WHERE fb_events.user_id = '$uid' AND fb_accounts.fb_id IS NOT NULL");
		$t = sql_num_rows($q);
		if($t && $html)$data .= '<optgroup label="Facebook events">';
		while($res = sql_fetch_assoc($q)){
			if($html)$data .= '<option value="'.$res['id'].'" rel="'.$res['site'].'" rel-owner="'.$res['fb_id'].'">'.$res['name'].' ['.$res['site'].' by '.$res['owner_name'].']</option>';	
			$accounts[] = $res;
		}
		if($t && $html)$data .= '</optgroup>';
		
		$q = sql_query("SELECT tw_id AS id, tw_username AS name, 'twitter' AS site FROM tw_accounts WHERE user_id = '$uid'");
		$t = sql_num_rows($q);
		if($t && $html)$data .= '<optgroup label="Twitter accounts">';
		while($res = sql_fetch_assoc($q)){
			if($html)$data .= '<option value="'.$res['id'].'" rel="'.$res['site'].'" rel-owner="'.$res['id'].'">'.$res['name'].' ['.$res['site'].']</option>';	
			$accounts[] = $res;
		}
		if($t && $html)$data .= '</optgroup>';
		
		$q = sql_query("SELECT yt_id AS id, CONCAT(first_name, ' ', last_name) AS name, 'youtube' AS site FROM yt_accounts WHERE user_id = '$uid'");
		$t = sql_num_rows($q);
		if($t && $html)$data .= '<optgroup label="Youtube accounts">';
		while($res = sql_fetch_assoc($q)){
			$accounts[] = $res;
			if($html)$data .= '<option value="'.$res['id'].'" rel="'.$res['site'].'" rel-owner="'.$res['id'].'">'.$res['name'].' ['.$res['site'].']</option>';	
		}
		if($t && $html)$data .= '</optgroup>';
		
		if($html)return $data;	
		return $accounts;
	}
	
	/**
	 * Function to import file to folders
	 * @param int $user_id user id
	 * @param string $file path of the file
	 * @param int $foder_id folder id
	 * @param string $org_name original file name
	 * @param string $caption file caption 
	 * @return bool true|false
	 */
	public function import_file_to_folder($user_id, $file, $folder_id, $org_name, $caption)
	{
		$this->error = '';
		global $allowed_image_ext, $allowed_video_ext, $user_data, $settings, $lang;
		
		$file = basename($file);
		
		if($folder_id != 'WATERMARK' && $folder_id != 'FRAME'){
			if(!$this->is_folder_owner($user_id, $folder_id)){
				$this->error = $lang['ajax']['edit_not_allowed'].' : '.$lang['ajax']['folder'];
				return false;	
			}
		}
		if(preg_match('/[^0-9a-z\.\_]/i', $file)){
			$this->error = $lang['ajax']['inv_file_name'];
			return false;	
		}
		
		/**
		 * verify storage
		 */
		$file_path = __ROOT__.'/plugins/media/tmp/'.$file;
		$size = @filesize($file_path);
		
		if(empty($size)){
			$file_path = __ROOT__.'/tmp/'.$file;
			$size = @filesize($file_path);
			if(empty($size)){
				$this->error = $lang['ajax']['inv_file'];
				return false;
			}
		}
		
		$max_space = $user_data['allowed_storage'];
		$used_space = $this->get_user_used_space($user_id);
		
		if($used_space + $size >= $max_space){
			$this->error = $lang['ajax']['not_enough_space'].' : '.formatSize($max_space);
			return false;	
		}
	
		
		$ext = strtolower(substr($file,strrpos($file,'.'),strlen($file)));
	
		if(preg_match('/[^a-zA-Z0-9\.]/', $ext)){
			$this->error = $lang['ajax']['inv_file'];
			return false;
		}
		
		$sizeLimit = UPLOAD_MAX_SIZE;
		if(in_array(trim($ext, '.'), $allowed_image_ext)){
			$sizeLimit = IMAGE_UPLOAD_MAX_SIZE; //5MB
			$file_type = 'image';
		}
		else if(in_array(trim($ext, '.'), $allowed_video_ext))$file_type = 'video';
		else{
			$this->error = $lang['ajax']['inv_file'];
			return false;
		}
		
		if($size > $sizeLimit){
			$this->error = $lang['ajax']['f_too_large'].' '.formatSize($sizeLimit);
			return false;	
		}
		
		/**
		 * set a random name
		 */
		$jj = 0;
		$name = rand(1,99999).'_'.rand(1,99999).'_'.rand(1,99999).$ext;
		
		$exists = sql_num_rows(sql_query("SELECT NULL FROM files WHERE filename = '".sql_real_escape_string($name)."' AND user_id = '$user_id'"));
		while($exists){
			$name = rand(1,99999).'_'.rand(1,99999).'_'.rand(1,99999).$ext;
			$exists = sql_num_rows(sql_query("SELECT NULL FROM files WHERE filename = '".sql_real_escape_string($name)."' AND user_id = '$user_id'"));
			usleep(100);
			if($jj++ > 100){
				$this->error = $lang['ajax']['un_error_fname'];
				return false;	
			}
		}
		
		if($folder_id == 'WATERMARK' || $folder_id == 'FRAME'){
			if($folder_id == 'WATERMARK'){
				$t = sql_num_rows(sql_query("SELECT NULL FROM creator_tools WHERE tool_type = 'watermark' AND user_id = '$user_id'"));
			}
			else{
				$t = sql_num_rows(sql_query("SELECT NULL FROM creator_tools WHERE tool_type = 'frame' AND user_id = '$user_id'"));
			}
			if($t >= 100){
				$this->error = $lang['ajax']['max_wm_frame_added'];
				return false;
			}	
			if(empty($org_name))$org_name = $folder_id.'-'.date('d-M-y-H:i');	
		}
		
		$file_link = __STORAGE__.'/'.$user_data['storage'].'/'.$name;
		if(!is_dir(dirname($file_link)))mkdir(dirname($file_link));
		
		if(!copy($file_path, $file_link)){
			$err = '';
			if(function_exists('error_get_last')){
				$errors = error_get_last();
				$err = $errors['message']; 
			}
			$this->error = $lang['ajax']['un_err_copy_file'].' : '.$err;
			return false;
		}
		
		if($folder_id == 'WATERMARK' || $folder_id == 'FRAME'){
			sql_query("INSERT INTO creator_tools (user_id, filename, tool_type, original_name) 
								VALUES ('$user_id', 
										'".sql_real_escape_string($name)."', 
										'".($folder_id == 'WATERMARK' ? 'watermark' : 'frame')."'	,
										'".sql_real_escape_string($org_name)."'									
								)
					");	
			return true;		
		}
		
		list($pos) = sql_fetch_row(sql_query("SELECT MAX(position)+1 FROM files WHERE folder_id = '$folder_id'"));
		if(empty($pos))$pos = 1;
		
		sql_query("INSERT INTO files (user_id, folder_id, filename, original_name, caption, file_type, added_at, position) 
							VALUES ('$user_id', 
									'$folder_id', 
									'".sql_real_escape_string($name)."', 
									'$org_name', 
									'$caption', 
									'$file_type', 
									NOW(), 
									'$pos'
									)
				");
		
		if(sql_affected_rows() <= 0){
			$this->error = $lang['ajax']['un_err'];
			return false;	
		}
		
		$file_id = sql_insert_id();
		sql_query("UPDATE folders SET file_count = file_count + 1 WHERE folder_id = '$folder_id'");
		
		$sfile_link = $user_data['storage'].'/'.$name;		
		if($file_type == 'image'){
			sql_query("UPDATE folders SET thumb = '".sql_real_escape_string($sfile_link)."' WHERE folder_id = '$folder_id'");
		}
		/**
		 * create thumb using ffmpeg for video
		 */
		else if(!empty($settings['ffmpeg'])){		
			$in_path = $file_link;
			$out_path = $in_path.'.png';
			
			$ff = new ffmpeg('');
			$data = $ff->analyze_video($in_path);
			$duration = (float)$data['duration'];
			
			if($duration){
				$thumb = '';
				$cmd = $settings['ffmpeg'].' -ss '.rand(1, (int)$duration).' -i '.$in_path.' -y -f image2 -vcodec mjpeg -vframes 1 '.$out_path;
				exec($cmd, $o, $c);		
				if(!$c && file_exists($out_path)){
					$sthumb_link = $user_data['storage'].'/'.basename($in_path).'.png';
					sql_query("UPDATE folders SET thumb = '".sql_real_escape_string($sthumb_link)."' WHERE folder_id = '$folder_id'");
				}
				sql_query("UPDATE files SET duration = '$duration' WHERE file_id = '$file_id'");
			}
		}
		return true;		
	}
}

?>