<?php
/**
 * General functions used in this project
 *
 * @package Social Ninja
 * @version 1.6
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

/**
 * Function to connect to mysql
 */
function sql_conn()
{			
	global $mysqli;	
	if($mysqli)@mysqli_close($mysqli);
	
	$mysqli = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	if(!$mysqli)return false;
	
	$db = @mysqli_select_db($mysqli, DB_NAME);
	if(!$db)return false;
	
	$charset = @mysqli_set_charset($mysqli, 'utf8mb4');
	if(!$charset)$charset = @mysqli_set_charset($mysqli, 'utf8');
	if(!$charset)return false;
	
	return $mysqli;
}

/**
 * Mysql functions
 */
function sql_connect($db_host, $db_user, $db_pwd)
{
	return @mysqli_connect($db_host, $db_user, $db_pwd);
}

function sql_connect_error(){
	return mysqli_connect_error();	
}

function sql_select_db($db_name)
{
	global $mysqli;
	return @mysqli_select_db($mysqli, $db_name);
}

function sql_num_rows($resource)
{
	global $mysqli;
	return mysqli_num_rows($resource);
}

function sql_query($query)
{
	global $mysqli;
	return mysqli_query($mysqli, $query);
}

function sql_fetch_assoc($resource)
{
	global $mysqli;
	return mysqli_fetch_assoc($resource);
}

function sql_fetch_row($resource)
{
	global $mysqli;
	return mysqli_fetch_row($resource);
}

function sql_affected_rows()
{
	global $mysqli;
	return mysqli_affected_rows($mysqli);
}

function sql_error()
{
	global $mysqli;
	return mysqli_error($mysqli);
}

function sql_escape_string($string)
{
	global $mysqli;
	return mysqli_escape_string($mysqli, $string);
}

function sql_real_escape_string($string)
{
	global $mysqli;
	return mysqli_real_escape_string($mysqli, $string);
}

function sql_insert_id()
{
	global $mysqli;
	return mysqli_insert_id($mysqli);
} 

function sql_close()
{
	global $mysqli;
	return mysqli_close($mysqli);
}

function sql_data_seek($result, $offset)
{
	return mysqli_data_seek($result, $offset);	
}

/**
 * Function to output ajax json response
 * $response is an array holding request response
 */
function output()
{
	global $response;
	echo json_encode($response);
	@sql_close();
	exit;
}

/**
 * Function to load site settings
 */
function load_settings()
{
	$settings = array();
	$settings = sql_fetch_assoc(sql_query("SELECT * FROM global_config"));
	return $settings;
}

/**
 * Function to load app settings
 * @param string $user_id load users settings
 */
function load_app_settings($user_id)
{
	global $settings;
	if(empty($settings['fb_scope'])){
		$scope = 'email,manage_pages,publish_actions,publish_pages,read_insights,user_managed_groups,user_posts,user_events,user_photos,user_videos,user_groups';
		$settings['fb_scope'] = $scope;
		sql_query("UPDATE global_config SET fb_scope = '$scope'");
	}
	
	list($fb_app, $tw_app, $yt_app) = sql_fetch_row(sql_query("SELECT fb_app_config, tw_app_config, yt_app_config FROM users WHERE user_id = '$user_id'"));
	if(!empty($fb_app)){
		$fb_app = json_decode($fb_app, true);
		$settings['fb_app_id'] = $fb_app['fb_app_id'];
		$settings['fb_app_secret'] = $fb_app['fb_app_secret'];
		$settings['fb_app_token'] = $fb_app['fb_app_token'];	
	}	
	if(!empty($tw_app)){
		$tw_app = json_decode($tw_app, true);
		$settings['tw_app_id'] = $tw_app['tw_app_id'];
		$settings['tw_app_secret'] = $tw_app['tw_app_secret'];
	}
	if(!empty($yt_app)){
		$yt_app = json_decode($yt_app, true);
		$settings['yt_client_id'] = $yt_app['yt_client_id'];
		$settings['yt_client_secret'] = $yt_app['yt_client_secret'];
		$settings['yt_dev_token'] = $yt_app['yt_dev_token'];	
	}
	
	/**
	 * Check if any app save is pending
	 */
	if(!empty($_SESSION['pending_fb_app'])){
		$fb_app = json_decode($_SESSION['pending_fb_app'], true);
		$settings['fb_app_id'] = $fb_app['fb_app_id'];
		$settings['fb_app_secret'] = $fb_app['fb_app_secret'];
		$settings['fb_app_token'] = $fb_app['fb_app_token'];	
	}	
	if(!empty($_SESSION['pending_tw_app'])){
		$tw_app = json_decode($_SESSION['pending_tw_app'], true);
		$settings['tw_app_id'] = $tw_app['tw_app_id'];
		$settings['tw_app_secret'] = $tw_app['tw_app_secret'];
	}
	if(!empty($_SESSION['pending_yt_app'])){
		$yt_app = json_decode($_SESSION['pending_yt_app'], true);
		$settings['yt_client_id'] = $yt_app['yt_client_id'];
		$settings['yt_client_secret'] = $yt_app['yt_client_secret'];
		$settings['yt_dev_token'] = $yt_app['yt_dev_token'];	
	}	
}

/**
 * Function to autoload classes
 * @param string $class class name
 */
function spl_autoloader($class)
{
	if(file_exists(dirname(__FILE__)."/classes/{$class}.class.php")){
		include(dirname(__FILE__)."/classes/{$class}.class.php");
	}
}

/**
 * Function to generate pagination html
 * @param int $count is the total record count
 * @param int $rows is the number of rows per page
 * @param int $from is the start index of pagination
 * @param string $qu is the query string i.e $_GET variables
 * @param string $url is the url for pagination. Default url is PHP_SELF
 * @return string pagination html 
 */
function pagination($count, $rows, $from, $qu, $url = '')
{
	$html = '';
	if(empty($url))$url = $_SERVER['PHP_SELF'];
	if(!preg_match('/\?/', $url)){
		$url .= '?';
		$url = rtrim($url, '&');
	}
	else $url .= '&';
	//$from++;
	$qu = preg_replace("/from=(\d+)/","",$qu);
	$qu = preg_replace("/^\&/","",$qu);
	$qu = preg_replace('/\&{2,}/', '&', $qu);
	$html .=  '<div class="text-center"><ul class="pagination pagination-sm">';
	if(($from-$rows) >= 0)$html .=  '<li class="prev"><a href="'.$url.'from='.($from-$rows).($qu ? '&'.$qu : '').'" class="pagina">← Previous</a></li>';
	$index = (int)($count/$rows);
	if($count%$rows)$index++;
	for($i = 1 ; $i <= $index ; $i++){
		$current_page = (int)($from/$rows)+1;
		if($index > 20 && $current_page > 10 && $i == 3){
			$html .=  "<li><span class='dots'>...</span></li>";$i=$current_page-5;
		}
		if($index > 20 && ($index - $current_page) > 10 && $i == ($current_page + 5)){
			$html .=  "<li><span class='dots'>...</span></li>";$i=$index-2;
		}
		if($i == (int)($from/$rows) +1 )$html .=  '<li class="active"><span>'.$i.'</span></li>';
		else $html .=  '<li><a href="'.$url.'from='.(($i-1)*$rows+1).($qu ? '&'.$qu : '').'" class="pagina">'.$i.'</a></li>';
	}
	if(($from + $rows) <= $count)$html .=  '<li class="next"><a href="'.$url.'from='.($from+$rows).($qu ? '&'.$qu : '').'" class="pagina">Next → </a></li>';
	$html .=  '</ul></div>';
	return $html;
}

/**
 * Function to generate seo friendly url
 * @param string $url is the input url
 * @return string seo friendly url 
 */
function makeuri($url, $real = 0)
{
	global $settings;
	if($settings['seo_url']){
		if(preg_match('/\.js|\.css|\.png|\.jpg|\.gif/i', $url))$url = $url;
		else if(preg_match('/dashboard\.php\?show=(.*)/i', $url, $m))$url = 'dashboard/'.$m[1].'/';
		else if(preg_match('/login\.php\?type=(.*)/i', $url, $m))$url = 'login/'.$m[1].'/';
		else if(preg_match('/log\.php\?site=(.*)/i', $url, $m))$url = 'log/'.$m[1].'/';
		else $url = str_replace('.php', '/', $url);		
	}
	if(!$real)return site_url().'/'.$url;
	else return $url;	
}

/**
 * Function to redirect to seo friendly url
 * @param string $url is the input url 
 */
function redirect($url)
{
	header('location: '.site_url().'/'.$url);
	exit();
}

/**
 * Function to display error message in case of fatal errors
 * @param string $message is the error message to show
 * @param bool $full_page indicates whether to include full page with header and footer
 */
function display_error($message, $full_page = true)
{
	global $settings, $user_data, $is_logged_in, $title, $lang, $is_index_page, $is_login_page;
	if($full_page)include(__ROOT__.'/templates/header.php');
	include(__ROOT__.'/templates/error.php');
	if($full_page)include(__ROOT__.'/templates/footer.php');
}

/**
 * Function to get site parameters
 * @param string $site is the site name
 * @return array $table->usertable $col->usercol $uname->username $name->namecol $social_id->owner social id column
 */
function get_site_params($site)
{
	if($site == 'facebook' || $site == 'fbprofile'){
		$table = 'fb_accounts';
		$col = 'fb_id';
		$uname = 'fb_id';
		$name = 'first_name';
		$social_id = 'fb_id';
	}
	else if($site == 'fbpage'){
		$table = 'fb_pages';
		$col = 'page_id';
		$uname = 'page_id';
		$name = 'page_name';
		$social_id = 'fb_id';
	}
	else if($site == 'fbgroup'){
		$table = 'fb_groups';
		$col = 'group_id';
		$uname = 'group_id';
		$name = 'group_name';
		$social_id = 'fb_id';
	}
	else if($site == 'fbevent'){
		$table = 'fb_events';
		$col = 'event_id';
		$uname = 'event_id';
		$name = 'event_name';
		$social_id = 'fb_id';
	}
	else if($site == 'twitter'){
		$table = 'tw_accounts';
		$col = 'tw_id';
		$uname = 'tw_username';
		$name = 'tw_username';
		$social_id = 'tw_id';
	}
	else if($site == 'youtube'){
		$table = 'yt_accounts';
		$col = 'yt_id';
		$uname = 'yt_username';
		$name = 'first_name';
		$social_id = 'yt_id';
	}
	
	return @array($table, $col, $uname, $name, $social_id);
}

/**
 * Function to get link from id
 * @param string $id
 * @param string $site
 * @return string $url
 */
function get_link_from_id($id, $site)
{
	if(preg_match('/^fb/', $site)){
		return 'https://www.facebook.com/'.$id;	
	}
	else if($site == 'twitter'){
		return 'https://www.twitter.com/'.$id;	
	}
	else if($site == 'youtube'){
		return 'https://www.youtube.com/channel/'.$id;	
	}
}

/**
 * Function to print social ids
 * @param array $user_data is the user_data array
 * @return string $html to print
 */
function print_social_ids($site)
{
	global $user_id, $lang;
	list($table, $col, $uname) = get_site_params($site);
	$html = '';
	
	$q = sql_query("SELECT $table.*, token_expiry.user_id AS token_expired FROM $table 
					  LEFT JOIN token_expiry ON token_expiry.user_id = '$user_id' AND token_expiry.social_id = $table.$col AND token_expiry.site = '$site'
					  WHERE $table.user_id = '$user_id'");
					  
	if(!sql_num_rows($q)){
		$html .= '<div class="alert alert-info">'.$lang['common'][1].'</div>';
	}else{
		$i = 0;
		while($account = sql_fetch_assoc($q)){
			
			$name = $account['first_name'].' '.$account['last_name'];
			if($account['account_status'] != 1)$name = '<strike>'.$name.'</strike>';
			
			$pic = get_medium_pp($account['profile_pic'], $site);
			$url = get_profile_url($account[$col], @$account[$uname], $site);
			
			if(!$i)$html .= '<div class="row">';
			$html .= '<div class="col-lg-4 social_id" rel="'.$account[$col].'" rel-site="'.$site.'">
					<div class="row">
						<div class="col-lg-5">
							<img src="'.$pic.'" width="100px"/>
						</div>
						<div class="col-lg-7">
							<h4>
								<a href="'.$url.'" target="_blank">
									'.$name.'
								</a><br/><br/>
								<button class="btn btn-sm btn-info del_social_id">
									'.$lang['common'][0].'
								</button>
								&nbsp;&nbsp;
								<a class="btn btn-sm btn-success" href="'.makeuri( 'sync.php?'.$col.'='.$account[$col] ).'">
									'.ucwords( $lang['index'][41] ).'
								</a>
								'.(empty($account['token_expired']) ? '' : '<br/><span style="color:red; font-weight:bold">'.$lang['common'][2].'</span>').'
							</h4>
						</div>
					</div>
				  </div>';
			$i++;
			if($i >= 3){
				$html .= '</div>';
				$i = 0;
			}
		}
		if($i)$html .= '</div>';	
	}
	return $html;
}

/**
 * Function to get medium sized pp from database saved url
 * @param string $url is the url of pp saved in database
 * @param string $site is the site
 * @return string $url is the medium sized pp
 */
function get_medium_pp($url, $site)
{
	if($site == 'facebook' || $site == 'fbprofile')return $url .= 'normal';
	else if($site == 'twitter')return str_replace('_normal', '_200x200', $url);
	else if($site == 'youtube')return $url;
	return $url .= 'normal';
}

/**
 * Function to get profile url from user id or username
 * @param string $id is the id
 * @param string $username is the username
 * @param string $site is the site
 * @return string $url
 */
function get_profile_url($id, $username ,$site)
{
	if($site == 'twitter')return 'https://twitter.com/'.$username;
	else if($site == 'youtube')return 'https://youtube.com/channel/'.$username;
	else return 'https://www.facebook.com/'.$id;
}

/**
 * Function to check if a folder exists
 * @param int $folder_id folder id
 * @return int $result
 */
function folder_exists($folder_id)
{
	return sql_num_rows(sql_query("SELECT NULL FROM folders WHERE folder_id = '$folder_id' LIMIT 1"));
}

/**
 * Function to get folder details
 * @param int $folder_id folder id
 * @return array $result
 */
function folder_details($folder_id)
{
	return sql_fetch_assoc(sql_query("SELECT * FROM folders WHERE folder_id = '$folder_id' LIMIT 1"));
}

/**
 * Function to get file details
 * @param int $file_id folder id
 * @return array $result
 */
function file_details($file_id)
{
	return sql_fetch_assoc(sql_query("SELECT * FROM files WHERE file_id = '$file_id' LIMIT 1"));
}

/**
 * Function to get tool details
 * @param int $tool_id tool_id
 * @return array $result
 */
function tool_details($tool_id)
{
	return sql_fetch_assoc(sql_query("SELECT * FROM creator_tools WHERE tool_id = '$tool_id' LIMIT 1"));
}

/**
 * Function to get files count
 * @param int $folder_id folder id
 * @return int $result
 */
function get_folder_file_count($folder_id, $name = '', $type = '')
{
	$search = ' 1 ';
	if(!empty($name))$search .=  " AND ( original_name LIKE '%$name%' OR file_id = '$name' ) ";
	if(!empty($type))$search .=  " AND file_type = '$type' ";
	return sql_num_rows(sql_query("SELECT NULL FROM files WHERE folder_id = '$folder_id' AND $search"));
}

/**
 * Function to get files from
 * @param int $folder_id folder id
 * @return array $result
 */
function get_folder_files($folder_id, $from = 1, $rows = 100, $name = '', $type = '')
{
	$files = array();
	$from--;
	
	$search = ' 1 ';
	if(!empty($name))$search .=  " AND ( original_name LIKE '%$name%' OR file_id = '$name' ) ";
	if(!empty($type))$search .=  " AND file_type = '$type' ";
	
	$q = sql_query("SELECT * FROM files WHERE folder_id = '$folder_id' AND $search ORDER BY position ASC LIMIT $from, $rows");
	while($res = sql_fetch_assoc($q))$files[] = $res;
	return $files;
}

/**
 * Function to purify text from script injection
 * @param string $text
 * @return string $result
 */
function purify_text($text) 
{
	return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Function to delete file from server
 * @param string $file
 * @param string $storage storage path to file
 * @return bool $result
 */
function delete_file($file, $storage, $folder_id, $file_id, $file_type) 
{
	if($file_type != 'text'){
		$path = __ROOT__.'/storage/'.$storage.'/'.$file;
		$f = sql_real_escape_string($file);
		
		$new_file = '';
		list($fid) = sql_fetch_row(sql_query("SELECT folder_id FROM folders WHERE thumb LIKE '%$f%'"));
		if(!empty($fid)){
			list($new_file, $file_type) = sql_fetch_row(sql_query("SELECT filename, file_type FROM files WHERE folder_id = '$fid' AND filename != '$f'"));
			
			if(empty($new_file))$new_file = '';
			else $new_file = $storage.'/'.$new_file;
			if($file_type == 'video')$new_file .= '.png';
			$new_file = sql_real_escape_string($new_file);			
		}
		
		sql_query("UPDATE folders SET thumb = '$new_file' WHERE folder_id = '$fid'");
		
		@unlink($path);
		@unlink($path.'.png');
		/**
		 * Delete all watermarked tmp files
		 */
		$l = glob(dirname(__FILE__).'/tmp/'.$file.'*');
		foreach($l as $ll)@unlink($ll);
	}
	
	sql_query("UPDATE folders SET file_count = file_count - 1 WHERE folder_id = '$folder_id' AND file_count >= 1");
	sql_query("DELETE FROM files WHERE file_id = '$file_id'");
	sql_query("DELETE FROM file_meta WHERE file_id = '$file_id'");
}

/**
 * Function to get time zone lists
 * @return array $result
 */
function get_time_zones()
{
	return
	$timezones = array(
		'Pacific/Midway'       => "(GMT-11:00) Midway Island",
		'US/Samoa'             => "(GMT-11:00) Samoa",
		'US/Hawaii'            => "(GMT-10:00) Hawaii",
		'US/Alaska'            => "(GMT-09:00) Alaska",
		'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
		'America/Tijuana'      => "(GMT-08:00) Tijuana",
		'US/Arizona'           => "(GMT-07:00) Arizona",
		'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
		'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
		'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
		'America/Mexico_City'  => "(GMT-06:00) Mexico City",
		'America/Monterrey'    => "(GMT-06:00) Monterrey",
		'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
		'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
		'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
		'America/Bogota'       => "(GMT-05:00) Bogota",
		'America/Lima'         => "(GMT-05:00) Lima",
		'America/Caracas'      => "(GMT-04:30) Caracas",
		'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
		'America/La_Paz'       => "(GMT-04:00) La Paz",
		'America/Santiago'     => "(GMT-04:00) Santiago",
		'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
		'America/Sao_Paulo'	   => "(GMT-03:00) Brasilia",
		'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
		'Greenland'            => "(GMT-03:00) Greenland",
		'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
		'Atlantic/Azores'      => "(GMT-01:00) Azores",
		'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
		'Africa/Casablanca'    => "(GMT) Casablanca",
		'Europe/Dublin'        => "(GMT) Dublin",
		'Europe/Lisbon'        => "(GMT) Lisbon",
		'Europe/London'        => "(GMT) London",
		'Africa/Monrovia'      => "(GMT) Monrovia",
		'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
		'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
		'Europe/Berlin'        => "(GMT+01:00) Berlin",
		'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
		'Europe/Brussels'      => "(GMT+01:00) Brussels",
		'Europe/Budapest'      => "(GMT+01:00) Budapest",
		'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
		'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
		'Europe/Madrid'        => "(GMT+01:00) Madrid",
		'Europe/Paris'         => "(GMT+01:00) Paris",
		'Europe/Prague'        => "(GMT+01:00) Prague",
		'Europe/Rome'          => "(GMT+01:00) Rome",
		'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
		'Europe/Skopje'        => "(GMT+01:00) Skopje",
		'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
		'Europe/Vienna'        => "(GMT+01:00) Vienna",
		'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
		'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
		'Europe/Athens'        => "(GMT+02:00) Athens",
		'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
		'Africa/Cairo'         => "(GMT+02:00) Cairo",
		'Africa/Harare'        => "(GMT+02:00) Harare",
		'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
		'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
		'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
		'Europe/Kiev'          => "(GMT+02:00) Kyiv",
		'Europe/Minsk'         => "(GMT+02:00) Minsk",
		'Europe/Riga'          => "(GMT+02:00) Riga",
		'Europe/Sofia'         => "(GMT+02:00) Sofia",
		'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
		'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
		'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
		'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
		'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
		'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
		'Asia/Tehran'          => "(GMT+03:30) Tehran",
		'Europe/Moscow'        => "(GMT+04:00) Moscow",
		'Asia/Baku'            => "(GMT+04:00) Baku",
		'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
		'Asia/Muscat'          => "(GMT+04:00) Muscat",
		'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
		'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
		'Asia/Kabul'           => "(GMT+04:30) Kabul",
		'Asia/Karachi'         => "(GMT+05:00) Karachi",
		'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
		'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
		'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
		'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
		'Asia/Almaty'          => "(GMT+06:00) Almaty",
		'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
		'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
		'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
		'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
		'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
		'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
		'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
		'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
		'Australia/Perth'      => "(GMT+08:00) Perth",
		'Asia/Singapore'       => "(GMT+08:00) Singapore",
		'Asia/Taipei'          => "(GMT+08:00) Taipei",
		'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
		'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
		'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
		'Asia/Seoul'           => "(GMT+09:00) Seoul",
		'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
		'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
		'Australia/Darwin'     => "(GMT+09:30) Darwin",
		'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
		'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
		'Pacific/Guam'         => "(GMT+10:00) Guam",
		'Australia/Hobart'     => "(GMT+10:00) Hobart",
		'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
		'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
		'Australia/Sydney'     => "(GMT+10:00) Sydney",
		'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
		'Asia/Magadan'         => "(GMT+12:00) Magadan",
		'Pacific/Auckland'     => "(GMT+12:00) Auckland",
		'Pacific/Fiji'         => "(GMT+12:00) Fiji",
	);
}

/**
 * Function to get disk space consumed by a directory
 * @param string $directory directory name
 * @return string $used_space
 * This function is optimized to run faster in linux and windows
 */
function dirSize($directory) {
    $size = 0;
	if(!is_dir($directory))return $size;
	/*
	 * For linux
	 */
	exec('/usr/bin/du -sb '.$directory, $o, $c);
	if($c == 0 && !empty($o[0])){
		$size = trim($o[0]);
		$size = trim(substr($size, 0, strpos($size, '/')));
		return $size;	
	}
	else{
		/**
		 * For windows
		 */
		 if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
			try{
				$obj = new COM ( 'scripting.filesystemobject');
				if(is_object($obj)){
					$ref = $obj->getfolder($directory);
					$size = $ref->size;
					$obj = null;
					return $size;
				}
			}catch(Exception $e){}
		 }
		 /**
		 * For others -> slow method
		 */
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
			$size+=$file->getSize();
		}
		return $size;
	}
} 

/**
 * Function to format disk space to human readable format
 * @param int $size size
 * @return string $formatted_size
 */
function formatSize($size)
{
	if($size < 1024) return $size.' bytes';
	else if($size >= 1024 && $size < 1024*1024) return round($size/1024, 2).' KB';
	else if($size >= 1024*1024 && $size < 1024*1024*1024) return round($size/1024/1024, 2).' MB';
	else return round($size/1024/1024/1024, 2).' GB';
}

/**
 * Function to format page likes/followers to human readable format
 * @param int $n like count
 * @param int $precision how many decimal places to return
 * @return string $formatted_number
 */
function formatLikes($n, $precision = 2) {
    if($n < 10000)$n_format = number_format($n);
	else if($n < 1000000)$n_format = number_format($n / 1000, $precision). ' K';
	else if($n < 1000000000)$n_format = number_format($n / 1000000, $precision) . ' M';
    else $n_format = number_format($n / 1000000000, $precision) . ' B';
    return $n_format;
}

/**
 * Function to perform curl request
 * use when no logging and debugging needed
 * @param string $url
 * @param array $post post data if any
 * @return string $response request response
 */
function curl_single($url, $post = array(), $timeout = 30, $headers = array())
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	@curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 0);
	if(!empty($post)){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	if(!empty($headers)){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:31.0) Gecko/20100101 Firefox/31.0');
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$data = curl_exec($ch);
	return $data;
}

/**
 * Function to perform multi curl request
 * use when no logging and debugging needed
 * @param array $url array of url
 * @param array $post post data if any (2D array)
 * @return array $response request response
 */
function curl_multi($url, $post = array(), $timeout = 30, $headers = array())
{
	$mh = curl_multi_init();
	$ch = array();
	$response = array();
	
	for($i = 0; $i < count($url); $i++):	
		$ch[$i] = curl_init($url[$i]);
		curl_setopt($ch[$i], CURLOPT_HEADER,0);	
		curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER,1);	
		curl_setopt($ch[$i], CURLOPT_BINARYTRANSFER,0);	
		curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION,1);	
		curl_setopt($ch[$i], CURLOPT_TIMEOUT, $timeout);	
		curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER,0);
		@curl_setopt($ch[$i], CURLOPT_SAFE_UPLOAD, 0);
		if(!empty($post[$i])){
			curl_setopt($ch[$i], CURLOPT_POST,1);
			curl_setopt($ch[$i], CURLOPT_POSTFIELDS, $post[$i]);
		}
		if(!empty($headers[$i])){
			curl_setopt($ch[$i], CURLOPT_HTTPHEADER, $headers[$i]);
		}
		curl_multi_add_handle($mh, $ch[$i]);
	endfor;
	
	if(empty($ch))return;
	
	$running=0;	
	do{
		curl_multi_exec($mh,$running);
		$info = curl_multi_info_read($mh);
		if($info['handle']) {
			$finalresult[] = curl_multi_getcontent($info['handle']);
			$returnedOrder[] = array_search($info['handle'], $ch, true);
			curl_multi_remove_handle($mh, $ch[end($returnedOrder)]);
			curl_close($ch[end($returnedOrder)]);
			$rr = end($finalresult);
			$index = end($returnedOrder);
			$response[$index] = $rr;
		}
		usleep(500);
	}while($running > 0);
	curl_multi_close($mh);
	
	return $response;
}

/**
 * Function to create pretty time
 * @param float $seconds second
 * @return string $time in pretty format hh:mm:ss
 */

function pretty_time($seconds){
  $t = round($seconds);
  $t = sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
  $t = preg_replace('/^00:/', '', $t);
  return $t;
}

/**
 * Function to get valid time intervals used for scheduling
 * @param string $type minutes|hours|weeks|days|months|years
 * @param int $validate whether to validate an input
 * @return array|boot valid intervals array|validate result
 */
function get_valid_schedule_intervals($type, $validate = 0)
{
	$valid = array();
	switch($type){
		case "minutes":
			for($i = 5; $i <= 59; $i += 1)$valid[] = $i;
		break;
		
		case "hours":
			for($i = 1; $i <= 23; $i ++)$valid[] = $i;
		break;
		
		case "weeks":
			for($i = 1; $i <= 52; $i += 1)$valid[] = $i;
		break;
		
		case "days":
			for($i = 1; $i <= 60; $i += 1)$valid[] = $i;
		break;
		
		case "months":
			for($i = 1; $i <= 36; $i += 1)$valid[] = $i;
		break;
		
		case "years":
			for($i = 1; $i <= 5; $i += 1)$valid[] = $i;
		break;
		
		case "hour_ranges":
			for($i = 0; $i <= 23; $i += 1)$valid[] = "'".sprintf('%02d', $i).":00'";
		break;
	}
	
	if(!empty($validate)){
		if(in_array($validate, $valid))return true;
		return false;	
	}
	return $valid;
}

function convert_post_freq($post_freq_type, $post_freq)
{
	switch($post_freq_type){
		case "minutes":
			$post_freq *= 60;
		break;
		
		case "hours":
			$post_freq *= 3600;
		break;
		
		case "days":
			$post_freq *= 3600*24;
		break;
		
		case "weeks":
			$post_freq *= 3600*24*7;
		break;
		
		case "months":
			$post_freq *= 3600*24*30;
		break;
		
		case "years":
			$post_freq *= 3600*24*365;
		break;	
	}
	return $post_freq;
}

/**
 * Function to get next posting time
 * @param string $post_freq posting interval in seconds
 * @param int $post_from what hour is to start posting from (0-23)
 * @param int $post_to what hour is to stop posting (0-23)
 * @param string $user_timezone timezone of the user
 * @return string UTC timestamp for next post
 */
function get_next_post_time($post_freq, $post_from = '', $post_to = '', $start_from = '', $user_timezone = '', $now = '')
{
	if(empty($user_timezone))date_default_timezone_set('UTC');
	else date_default_timezone_set($user_timezone);
	
	if(empty($now))$now = time();
	
	$left_before_next_day = mktime(0, 0, 0, date('n'), date('j') + 1) - time();
	$tz_offset = date('Z');
	
	if($start_from == '0000-00-00' || $start_from == '0000-00-00 00:00:00')$start_from = '';
	
	/**
	 * Check if we have to start posting from a future date and no post start end time given
	 */
	if(!empty($start_from) && $post_from == '' && $post_to == ''){
		
		if(!is_numeric($start_from))list($start_from) = sql_fetch_row(sql_query("SELECT DATE('$start_from')"));
		else list($start_from) = sql_fetch_row(sql_query("SELECT DATE(FROM_UNIXTIME('$start_from'))"));
		
		$start_from_t = strtotime($start_from);
		if($now < $start_from_t){
			$d = $start_from.' '.date('H:i:00', $now);
			return strtotime($d);	
		}
		else return strtotime(date('d-M-Y H:i:00', $now + $post_freq));
	}
	
	if(!empty($start_from)){
		
		if(!is_numeric($start_from))list($start_from) = sql_fetch_row(sql_query("SELECT UNIX_TIMESTAMP('$start_from')"));
		
		$next_post = strtotime(date('d-M-Y H:i:00', $start_from + $post_freq));
		$next_post_day = date('d-m-Y', $start_from + $post_freq + $left_before_next_day);
	}
	
	if(empty($next_post) || $next_post < $now){
		$next_post = strtotime(date('d-M-Y H:i:00', $now + $post_freq));
		$next_post_day = date('d-m-Y', $now + $post_freq + $left_before_next_day);	
	}
	
	$today = date('d-m-Y', $now);
	
	/**
	 * Check if we have to post from a specific period
	 */
	if(is_numeric($post_from) && is_numeric($post_to) && $post_from != $post_to){
		$post_to--;
		$hour_next = (int)date('H', $next_post);
		
		if($post_to <= 0)$post_to = 24;
		
		if($post_from < $post_to){
			if($hour_next >= $post_from && $hour_next < $post_to){
				return $next_post;
			}
			else{
				/**
				 * no time to post today
				 */
				if($hour_next >= $post_to){
					return strtotime($next_post_day.' '.timepadding($post_from).':'.timepadding(rand(0, 59)).':00');
				}
				else{
					$t = strtotime($today.' '.timepadding($post_from).':'.timepadding(rand(0, 59)).':00');
					if($t < $now)return strtotime($next_post_day.' '.timepadding($post_from).':'.timepadding(rand(0, 59)).':00');
					return $t;
				}
			}	
		}
		else if($post_from > $post_to){
			if(($hour_next >= $post_from && $hour_next <= 23) || ($hour_next <= $post_to && $hour_next >= 0)){
				return $next_post;
			}
			else{
				/**
				 * no time to post today
				 */
				if($hour_next >= $post_to){
					return strtotime($next_post_day.' '.timepadding($post_from).':'.timepadding(rand(0, 59)).':00');
				}
				else{
					$t = strtotime($today.' '.timepadding($post_from).':'.timepadding(rand(0, 59)).':00');
					if($t < $now)return strtotime($next_post_day.' '.timepadding($post_from).':'.timepadding(rand(0, 59)).':00');
					return $t;
				}
			}	
		}
	}
	
	return $next_post;
}

/**
 * Function to get next comment bumping
 * @param string $bump_freq posting interval in "from_hour-to_hour" format
 * @return string UTC timestamp for next post
 */
function next_comment_bump_time($bump_freq)
{
	$ff = explode('-', $bump_freq);
	
	$ff[0] = $ff[0]*60;
	$ff[1] = $ff[1]*60;
	
	$tt = rand($ff[0], $ff[1]);
	return time() + $tt*60;	
}

/**
 * Function to pad time with leading zeros
 * @param int $t time
 * @return string padded time
 */
function timepadding($t)
{
	return sprintf('%02d', $t);
}

/**
 * Function get profile feed
 * @param string $page_id id of the page
 * @param string $site site name | inernally used
 * @return array $feed = array('post_id', 'link', 'is_hidden', 'message', 'picture', 'created_time')
 */
function get_profile_feed($page_id, $site, $access_token, $social_id, $username)
{
	global $settings, $user_id;
	$d = array();
	if(empty($access_token))return $d;
	if(preg_match('/^fb/i', $site)){
				
		$url = 'https://graph.facebook.com/'.$page_id.'/feed?access_token='.$access_token.'&limit=100&include_hidden=true&fields=message,story,picture,created_time,is_hidden';		
		$data = curl_single($url);
		
		if(preg_match("/".INVALID_TOKEN_TERMS."/i", $data) && $social_id){
			sql_query("INSERT INTO token_expiry VALUES('$user_id', '$social_id','$page_id', '$site' ,0, NOW())");
			if(in_array($site, array('fbpage', 'fbgroup', 'fbevent'))){
				sql_query("INSERT INTO token_expiry VALUES('$user_id', '$social_id','$social_id', 'fbprofile' ,0, NOW())");	
			}
			return false;
		}
		
		$d = @json_decode($data, true);
		$d = @$d['data'];
		if(empty($d)){
			return false;	
		}
		foreach($d as $k => $v){
			if(empty($d[$k]['id'])){
				unset($d[$k]);
				continue;
			}
			$d[$k]['post_id'] = $d[$k]['id'];
			$d[$k]['link'] = 'https://www.facebook.com/'.$d[$k]['id'];
			$d[$k]['message'] = !empty($d[$k]['message']) ? $d[$k]['message'] : (!empty($d[$k]['story']) ? $d[$k]['story'] : 'No description available');	
			$d[$k]['message'] = purify_text($d[$k]['message']);
			if($d[$k]['is_hidden'] == true)$d[$k]['message'] = $d[$k]['message'].'&nbsp;&nbsp;<span class="label label-warning">HIDDEN</span>';
			if(!empty($d[$k]['picture']))$d[$k]['message'] .= '<br/><br/><img style="max-width:300px" src="'.$d[$k]['picture'].'"/>';
			$d[$k]['created_time'] = @date('d-M-y h:i:s A', strtotime($d[$k]['created_time']));
		}
	}
	else if($site == 'twitter'){
		
		require_once dirname(__FILE__)."/sdk/twitter/twitter.php";
		
		$token = explode(':::', $access_token);
		$tw = new TwitterOAuth($settings['tw_app_id'], $settings['tw_app_secret'], $token[0], $token[1]);
		$tw->decode_json = false;
		$data = $tw->get('statuses/user_timeline', array('count' => 100));
		
		if(preg_match("/".INVALID_TOKEN_TERMS."/i", $data) && $social_id){
			sql_query("INSERT INTO token_expiry VALUES('$user_id', '$social_id', '$page_id', '$site' ,0, NOW())");
			return false;
		}
		
		$d = json_decode($data, true);
		if(empty($d) || isset($d['error'])){
			return false;	
		}
		foreach($d as $k => $v){
			if(empty($d[$k]['id_str'])){
				unset($d[$k]);
				continue;
			}
			$p = @$d[$k]['entities']['media'][0]['media_url_https'];
			$d[$k]['post_id'] = $d[$k]['id_str'];
			$d[$k]['link'] = 'https://twitter.com/profile/statuses/'.$d[$k]['id_str'].'/';
			$d[$k]['picture'] = $p;
			$d[$k]['message'] = purify_text(@$d[$k]['text']);
			$d[$k]['created_time'] = @date('d-M-y h:i:s A', strtotime($d[$k]['created_at']));
			if(!empty($d[$k]['picture']))$d[$k]['message'] .= '<br/><img style="max-width:300px" src="'.$d[$k]['picture'].'"/>';
			unset($d[$k]['text']);
			unset($d[$k]['created_at']);	
		}
	}
	else if($site == 'youtube'){
		
		require_once dirname(__FILE__)."/sdk/youtube/youtube.php";
		
		$token = $access_token;
		$yt = new Youtube($token);
		$dd = $yt->getFeed($username);
		
		if(preg_match("/".INVALID_TOKEN_TERMS."/i", $yt->response) && $social_id){
			sql_query("INSERT INTO token_expiry VALUES('$user_id', '$social_id','$page_id', '$site' ,0, NOW())");
			return false;
		}
		
		if(!is_array($dd)){
			return false;
		}
		$d = array();
		foreach($dd as $k => $v){
			$s = $dd[$k]['snippet'];
			if(empty($s))continue;
			if(empty($s['resourceId']['videoId']))continue;
			$d[$k]['message'] = purify_text($s['title'])."<br/><br/>".purify_text($s['description']);
			$d[$k]['post_id'] = $s['resourceId']['videoId'];
			$d[$k]['picture'] = $s['thumbnails']['default']['url'];
			$d[$k]['link'] = 'https://youtu.be/'.$d[$k]['post_id'];
			$d[$k]['created_time'] = @date('d-M-y h:i:s A', strtotime($s['publishedAt']));
			if(!empty($d[$k]['picture']))$d[$k]['message'] .= '<br/><img style="max-width:300px" src="'.$d[$k]['picture'].'"/>';
		}
	}
	return $d;
}

/**
 * Function confirm if a facebook token is invalid
 * requires facebook app token
 * @param string $token access token
 * @return bool status of token
 */
function confirm_invalid_token($token)
{
	global $settings;
	if(empty($settings['fb_app_token']))return false;
	$url = 'https://graph.facebook.com/debug_token?input_token='.$token.'&access_token='.$settings['fb_app_token'];
	$data = curl_single($url);
	if(preg_match("/".INVALID_TOKEN_TERMS."/i", $data))return true;
	return false;
}

/**
 * Function list site themes
 * @param bool $html whether to send as html options element?
 * @return array $theme_names
 */
function list_themes($html = 0)
{
	$list = array();
	$data = '<option value="">NONE</option>';
	foreach(scandir(__ROOT__.'/css/themes') as $f){
		if($f == '..' || $f == '.' || $f == 'assets' || $f == 'bower_components' || $f == 'fonts')continue;
		$list[] = $f;
		$data .= '<option value="'.$f.'">'.$f.'</option>';
	}
	if($html)return $data;
	return $f;
}

/**
 * Function to log output from utility scripts
 * @param string $str line to write
 * @param bool $clear whether to clear the log file
 * @param string $log_file path to the log file optional
 * @return n/a
 */
function do_log($str, $clear = 0, $log_file = '')
{
	$master_log_file = dirname(__FILE__).'/logs/log.txt';
	if(!empty($log_file))$master_log_file = $log_file;
	if($clear)$fp = fopen($master_log_file, "w");
	else $fp = fopen($master_log_file, "a");
	fwrite($fp,date('[d-M-Y H:i:s]')." $str\r\n");
	fclose($fp);

	/*
	if(!empty($_SERVER['HTTP_USER_AGENT']))echo $str."<br/>";
	else echo $str."\n";
	@flush();
	@ob_flush();
	*/
}

/**
 * Function to get youtube categories
 * @param bool $verify whether the entry should be verified or not
 * @return string options list of categories | bool is valid or not
 */
function get_yt_cats($verify = 0)
{
	$data = '';
	$cats = array(	
				1 => 'Film & Animation', 
				2 => 'Autos & Vehicles', 
				10 => 'Music', 
				15 => 'Pets & Animals', 
				17 => 'Sports', 
				19 => 'Travel & Events', 
				20 => 'Gaming', 
				22 => 'People & Blogs', 
				23 => 'Comedy', 
				24 => 'Entertainment', 
				25 => 'News & Politics', 
				26 => 'Howto & Style', 
				27 => 'Education', 
				28 => 'Science & Technology', 
				29 => 'Nonprofits & Activism'
			);
	foreach($cats as $id => $cat)$data .= '<option value="'.$id.'">'.$cat.'</option>';
	if(!$verify)return $data;
	if(!empty($cats[$verify]))return $cats[$verify];
	return false;
}

/**
 * Function to get youtube privacy list
 * @param bool $verify whether the entry should be verified or not
 * @return string options list of privacies | bool is valid or not
 */
function get_yt_privacy($verify = 0)
{
	$data = '';
	$privacy = array('public', 'private', 'unlisted');
	foreach($privacy as $p)$data .= '<option value="'.$p.'">'.$p.'</option>';
	if(!$verify)return $data;
	if(in_array($verify, $privacy))return true;
	return false;
}

/**
 * Function to check if the script is already running or not
 * @param $set string ebay or sams
 * @return bool the run status
 */
function check_running($set)
{
	$running=0;
	if(file_exists(dirname(__FILE__)."/logs/lock-".$set.".dat")){
		$fp=fopen(dirname(__FILE__)."/logs/lock-".$set.".dat","r");
		if(!flock($fp,LOCK_EX | LOCK_NB))$running=1;
		fclose($fp);
	}
	return $running;
}

/**
 * Function to check if the file is already locked or not
 * @param $set string
 * @return bool the run status
 */
function check_running_file($file)
{
	$running=0;
	if(file_exists($file)){
		$fp=fopen($file,"r");
		if(!flock($fp,LOCK_EX | LOCK_NB))$running=1;
		fclose($fp);
	}
	return $running;
}

/**
 * Function to lock a process
 * @param $set string
 * @return $fp resource file pointer
 */
function lock_process($set)
{
	$fp = fopen(dirname(__FILE__)."/logs/lock-".$set.".dat","w");
	flock($fp, LOCK_EX);
	return $fp;
}

/**
 * Function to lock a file
 * @param $file string
 * @return $fp resource file pointer
 */
function lock_file($file)
{
	$fp = fopen($file,"w");
	flock($fp, LOCK_EX);
	return $fp;
}

/**
 * Function to unlock a process
 * @param $fp resource file pointer
 * @return n/a
 */
function unlock_process($fp)
{
	@flock($fp, LOCK_UN);
	@fclose($fp);
}

/**
 * Function to configure and verify fb app
 * @return string app_token on success and false on failure
 */
function configure_fb_app($app_id, $app_secret)
{
	global $settings;
	$url = 'https://graph.facebook.com/oauth/access_token?client_id='.$app_id.'&client_secret='.$app_secret.'&grant_type=client_credentials';
	$response = curl_single($url);
	
	if(preg_match('/access_token\=/i', $response)){
		$app_token = sql_real_escape_string(trim(str_replace('access_token=', '', $response)));
	}
	
	else {
		$aa = json_decode($response, true);
		$app_token = $aa['access_token'];	
	}
	
	if(empty($app_token))return false;
	
	$parse = parse_url($settings['site_url']);
	$domain = $parse['host'];

	$url = 'https://graph.facebook.com/'.$app_id.'/';
	$post = array('access_token' => $app_token, 'app_domains' => json_encode(array($domain)), 'website_url' => $settings['site_url'], 'canvas_url' => $settings['site_url'], 'secure_canvas_url' => str_replace('http://', 'https://', $settings['site_url']));
	
	$response = curl_single($url, $post);
	$data = json_decode($response, true);
	
	if(@$data['success'] == true)return $app_token;
	return false;
}

/**
 * Function to check and verify facebook app and add it to global_config database
 * @return string 'SUCCESS' on success and 'FAILED' on failure
 */
function save_fb_app($app_id, $app_secret, $app_token)
{
	
	sql_query("UPDATE global_config SET fb_app_id = '$app_id', fb_app_secret = '$app_secret', fb_app_token = '$app_token' LIMIT 1");	
	sql_query("INSERT INTO token_expiry (user_id, social_id, page_id, site, mail_sent, expired_at) SELECT user_id, fb_id, fb_id, 'fbprofile', 1, NOW() FROM fb_accounts");
	return 'SUCCESS';
}

/**
 * Function to check and verify twitter app and add it to global_config database
 * @return string 'SUCCESS' on success and 'FAILED' on failure
 */
function save_tw_app($app_id, $app_secret)
{
	sql_query("UPDATE global_config SET tw_app_id = '$app_id', tw_app_secret = '$app_secret' LIMIT 1");	
	sql_query("INSERT INTO token_expiry (user_id, social_id, page_id, site, mail_sent, expired_at) SELECT user_id, tw_id, tw_id, 'twitter', 1, NOW() FROM tw_accounts");
	return 'SUCCESS';
}

/**
 * Function to check and verify twitter app and add it to global_config database
 * @return string 'SUCCESS' on success and 'FAILED' on failure
 */
function save_yt_app($app_id, $app_secret, $dev_token)
{
	sql_query("UPDATE global_config SET yt_client_id = '$app_id', yt_client_secret = '$app_secret', yt_dev_token = '$dev_token' LIMIT 1");	
	sql_query("INSERT INTO token_expiry (user_id, social_id, page_id, site, mail_sent, expired_at) SELECT user_id, yt_id, yt_id, 'youtube', 1, NOW() FROM yt_accounts");
	return 'SUCCESS';
}

/**
 * Function to delete a profile from database
 * @param int $user_id is the owner user id
 * @param int $id is the social id
 * @param string $site site name
 * @return n/a
 */
function delete_profile($user_id, $id, $site)
{
	$s = $site;
	if($site == 'fbprofile')$s = 'fb';
	
	list($table, $col) = get_site_params($site);
	
	sql_query("DELETE FROM schedules WHERE user_id = '$user_id' AND (social_id = '$id' OR page_id = '$id') AND site LIKE '$s%'");
	
	if($site == 'fbprofile'){
		sql_query("UPDATE users SET fb_noti = '' WHERE fb_noti = '$id' AND user_id = '$user_id'");	
	}
	if($site == 'fbprofile' || $site == 'fbpage'){
		sql_query("DELETE FROM fb_pages WHERE user_id = '$user_id' AND fb_pages.fb_id = '$id'");			 
	}
	if($site == 'fbprofile' || $site == 'fbgroup'){				 
		sql_query("DELETE FROM fb_groups WHERE user_id = '$user_id' AND fb_groups.fb_id = '$id'");
	}
	if($site == 'fbprofile' || $site == 'fbevent'){
		sql_query("DELETE FROM fb_events WHERE user_id = '$user_id' AND fb_events.fb_id = '$id'");	
	}
	
	sql_query("DELETE FROM token_expiry WHERE user_id = '$user_id' AND social_id = '$id' AND site like '$s%'");
	sql_query("DELETE FROM $table WHERE user_id = '$user_id' AND $col = '$id'");
	
	/**
	 * Clear empty schedule groups
	 */
	sql_query("DELETE schedule_groups FROM schedule_groups 
				 LEFT JOIN schedules ON schedules.schedule_group_id = schedule_groups.schedule_group_id 
				 WHERE schedules.schedule_group_id IS NULL");
	
	/**
	 * Clear error logs
	 */			 
	sql_query("DELETE FROM error_msg WHERE user_id = '$user_id' AND (social_id = '$id' OR page_id = '$id') AND site LIKE '$s%'");			 
				 
	clear_empty_post_logs();		
}

/**
 * Function to clear empty post logs
 */
function clear_empty_post_logs()
{
	/**
	 * when a facebook profile is deleted
	 */
	sql_query("DELETE post_log FROM post_log LEFT JOIN fb_accounts ON fb_accounts.fb_id = post_log.social_id WHERE SUBSTRING(post_log.site, 1, 2) = 'fb' AND fb_accounts.fb_id IS NULL");
	
	/**
	 * when a twitter profile is deleted
	 */
	sql_query("DELETE post_log FROM post_log LEFT JOIN tw_accounts ON tw_accounts.tw_id = post_log.social_id WHERE post_log.site = 'twitter' AND tw_accounts.tw_id IS NULL");
	
	/**
	 * when a youtube profile is deleted
	 */
	sql_query("DELETE post_log FROM post_log LEFT JOIN yt_accounts ON yt_accounts.yt_id = post_log.social_id WHERE post_log.site = 'youtube' AND yt_accounts.yt_id IS NULL");
	
	/**
	 * when a facebook page is deleted
	 */
	sql_query("DELETE post_log FROM post_log LEFT JOIN fb_pages ON fb_pages.page_id = post_log.page_id WHERE post_log.site = 'fbpage' AND fb_pages.page_id IS NULL");
	
	/**
	 * when a facebook group is deleted
	 */
	sql_query("DELETE post_log FROM post_log LEFT JOIN fb_groups ON fb_groups.group_id = post_log.page_id WHERE post_log.site = 'fbgroup' AND fb_groups.group_id IS NULL");
	
	/**
	 * when a facebook event is deleted
	 */
	sql_query("DELETE post_log FROM post_log LEFT JOIN fb_events ON fb_events.event_id = post_log.page_id WHERE post_log.site = 'fbevent' AND fb_events.event_id IS NULL");
}

/**
 * Function to clear schedule and logs
 * @param string $type type of clearance
 * @param string $param any parameters needed
 * @return n/a
 */
function clear_schedules($type, $param)
{
	if($type == 'folder'){
		sql_query("DELETE schedules FROM schedule_groups 
					 LEFT JOIN schedules ON schedules.schedule_group_id = schedule_groups.schedule_group_id
					 WHERE schedule_groups.folder_id = 'FOLDER:$param'");
		sql_query("DELETE FROM schedule_groups WHERE folder_id = 'FOLDER:$param'");	
		sql_query("DELETE FROM post_log WHERE folder_id = 'FOLDER:$param'");		
	}
	else if($type == 'rss'){
		sql_query("DELETE schedules FROM schedule_groups 
					 LEFT JOIN schedules ON schedules.schedule_group_id = schedule_groups.schedule_group_id
					 WHERE schedule_groups.folder_id = 'RSS:$param'");
		sql_query("DELETE FROM schedule_groups WHERE folder_id = 'RSS:$param'");	
		sql_query("DELETE FROM post_log WHERE folder_id = 'RSS:$param'");	
	}
	else if($type == 'schedule_group'){
		sql_query("DELETE FROM post_log WHERE schedule_group_id = '$param'");	
	}
	else if($type == 'schedule'){
		sql_query("DELETE FROM post_log WHERE schedule_id = '$param'");	
	}
}

/**
 * Function to get schedules from schedule group id
 * @param string $group_id schedule group id
 * @param string $site any parameters needed
 * @return array groups list of schedules
 */
function get_schedules_schedules_from_group($group_id, $site, $from = 1, $rows = 25)
{
	$from--;
	$search = ' 1 ';
	if(!empty($site))$search = " site = '$site' ";
	$q = sql_query("SELECT * FROM schedules 
					  WHERE 
					  schedule_group_id = '$group_id' AND 
					  $search
					  ORDER BY schedule_id DESC
					  LIMIT $from, $rows");
	$groups = array();
	while($res = sql_fetch_assoc($q))$groups[] = $res;
	return $groups;
}

/**
 * Function to count schedules from schedule group id
 * @param string $group_id schedule group id
 * @param string $site any parameters needed
 * @return array groups list of schedules
 */
function count_schedules_schedules_from_group($group_id, $site)
{
	$search = ' 1 ';
	if(!empty($site))$search = " site = '$site' ";
	list($total) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM schedules 
					  WHERE 
					  schedule_group_id = '$group_id' AND 
					  $search"));
	return $total;
}

/**
 * Function to get schedule group details
 * @param string $group_id schedule group id
 * @return array schedule group details
 */
function get_schedules_group($group_id)
{
	return sql_fetch_assoc(sql_query("SELECT * FROM schedule_groups WHERE schedule_group_id = '$group_id'"));
}

/**
 * Function to format mysql datetime to user readable time
 * @param string $time mysql time
 * @param int $add seconds to add or subtract
 * @return string $time formatted time
 */
function get_formatted_time($time, $add = 0, $format = 1)
{		
	if($time == '0000-00-00 00:00:00')return 'N/A';
	list($time) = sql_fetch_row(sql_query("SELECT UNIX_TIMESTAMP('$time')"));
	if($format == 1)return date('d-M-y h:i:s A', $time + $add);
	else return date('Y-m-d h:i:s A', $time + $add);
}

/**
 * Function to delete schedule 
 * @param int $sch_id schedule id
 * @return n/a
 */
function delete_schedule($sch_id)
{
	list($gid) = sql_fetch_row(sql_query("SELECT schedule_group_id FROM schedules WHERE schedule_id = '$sch_id'"));
	
	sql_query("UPDATE schedule_groups SET total_schedules = total_schedules - 1 WHERE total_schedules >= 0 AND schedule_group_id = '$gid'");
	sql_query("DELETE FROM post_log WHERE schedule_id = '$sch_id'");
	sql_query("DELETE FROM schedules WHERE schedule_id = '$sch_id'");
}

/**
 * Function to count post log
 * @param int $sid schedule id
 * @param bool $group whether the sch_id stands for schedule group id or not
 * @return int $total count
 */
function count_post_log($sch_id, $group = 0)
{
	$col = $group ? 'schedule_group_id' : 'schedule_id';
	return sql_num_rows(sql_query("SELECT NULL FROM post_log WHERE $col = '$sch_id' AND is_hidden = 0"));
}

/**
 * Function to fetch post logs from a schedule
 * @param int $sch_id schedule id
 * @param int $from from index
 * @param int $rows how many records?
 * @param bool $group whether the sch_id stands for schedule group id or not
 * @return array $results
 */
function get_post_logs($sch_id, $from = 1, $rows = 100, $group = 0)
{
	$col = $group ? 'schedule_group_id' : 'schedule_id';
	$from--;
	$q = sql_query("SELECT *
						FROM post_log 
					  WHERE $col = '$sch_id' AND is_hidden = 0
					  ORDER BY posted_at DESC
					  LIMIT $from, $rows");
	$logs = array();
	while($res = sql_fetch_assoc($q))$logs[] = $res;
	return $logs;
}

/**
 * Function to get post link from post_id
 * @param string $post_id post id
 * @param string $site
 * @return string $link
 */
function get_link_from_post_id($post_id, $site)
{
	$link = '';
	if($site == 'youtube'){
		$link = 'https://youtu.be/'.$post_id;
	}
	else if($site == 'twitter'){
		$link = 'https://twitter.com/user/statuses/'.$post_id.'/';
	}
	else $link = 'https://www.facebook.com/'.$post_id;
	
	return $link;
}

/**
 * Function to check error in response from social media
 * @param string $response response array (json decoded)
 * @return bool true on success | false on failure
 */
function check_errors_in_response($response, $error, $user_id, $social_id, $site, $page_id, $post_id, $file_id, $log_file, $access_token)
{
	do_log('Checking errors...', 0, $log_file);
	if(empty($response))return false;
	
	if(empty($error)){
		if(!empty($response['error']) && is_string($response['error']))$error = $response['error'];
		else{
			/**
			 * parse errors array
			 */
			if(!empty($response['errors']))$response['error'] = end($response['errors']); 
			
			if(!empty($response['error']['message']))$error = $response['error']['message']; 
			else if(!empty($response['error']['message']['message']))$error = $response['error']['message']['message'];
		}
	}
	
	$err = 0;
	if(!empty($error)){
		$err = 1;
		/**
		 * Clear old error messages
		 */
		sql_query("DELETE FROM error_msg WHERE user_id = '$user_id' LIMIT 100,99999");
		sql_query("INSERT INTO error_msg (user_id, social_id, page_id, post_id, file_id, message, added_at, site) VALUES
										   ('$user_id', 
											'$social_id', 
											'$page_id', 
											'$post_id', 
											'$file_id',
											'".sql_real_escape_string($error)."', 
											NOW(), 
											'$site')"
					);
		do_log('Action failed: '.$error, 0, $log_file);
	}
	
	/**
	 * Problems with access token?
	 */
	$rr = json_encode($response);
	
	if(preg_match("/".INVALID_TOKEN_TERMS."/i", $rr)){
		$ok = 1;
		if($site == 'fbprofile' || $site == 'fbpage' || $site == 'fbgroup' || $site == 'fbevent'){
			//if(!confirm_invalid_token($access_token))$ok = 0;	
			$ok = 1;
		}
		if($ok){
			do_log("Error with access token on page $page_id ".$rr, 0, $log_file);
			$mail_sent = sql_num_rows(sql_query("SELECT NULL FROM token_expiry WHERE user_id = '$user_id' AND mail_sent = 1")) ? 1 : 0;
			if(in_array($site, array('fbpage', 'fbgroup', 'fbevent'))){
				sql_query("INSERT INTO token_expiry VALUES('$user_id', '$social_id','$social_id', 'fbprofile' ,0, NOW())");	
			}
			sql_query("INSERT INTO token_expiry (user_id, social_id, page_id, mail_sent, expired_at, site) VALUES(
												  '$user_id', 
												  '$social_id', 
												  '$page_id', 
												  '$mail_sent', 
												  NOW(), 
												  '$site')"
						);
		}
		else do_log('Could not validate token failure '.$rr, 0, $log_file);
		return false;
	}
	
	/**
	 * Problems with blocks?
	 */
	else if(preg_match("/".BLOCKED_TERMS."/", $rr)){
		do_log("Social site block ".$page_id." ".$rr, 0, $log_file);
		sql_query("UPDATE schedules SET rate_limited = 1, rate_limited_at = NOW(), notes = 'Limited temporarily due to API Errors. Check error logs' WHERE user_id = '$user_id' AND social_id = '$social_id' AND page_id = '$page_id' AND site = '$site'");
		return false;
	}
	else if($err)return 'ERROR';
	return true;
}

/**
 * Function to verify rss feed url
 * @param string $url
 * @return bool true on success | string error on failure
 */
function verify_rss_feed($url)
{
	$feed = new xml_feed($url);
	if(!empty($feed->error)){
		return $feed->error;	
	}
	if(empty($feed->posts)){
		return 'Failed to load any post from RSS feed. Please double check the URL';	
	}	
	return true;
}

/**
 * Function to verify stats name against site
 * @param string $name stats name
 * @param string $site
 * @return bool true on success | bool false on failure
 */
function validate_stats_name($name, $site)
{
	$fb_arr = array('Likes', 'Comments');
	$fb_page_arr = array('Likes', 'Comments', 'Views', 'Negative_Feedback');
	$tw_arr = array('Favorites', 'Retweet');
	$yt_arr = array('Views', 'Likes', 'Dislikes', 'Favorites');
	
	if($site == 'twitter')$arr = $tw_arr;
	else if($site == 'youtube')$arr = $yt_arr;
	else if($site == 'fbpage')$arr = $fb_page_arr;
	else $arr = $fb_arr;
	
	if(in_array($name, $arr))return true;
	return false;
}

/**
 * Function to reset schedule groups status
 */
function reset_schedule_group_status()
{
	/**
	 * Mark all schedule groups as not done when at least one schedule in that group is not done yet
	 */
	sql_query("UPDATE schedule_groups LEFT JOIN schedules ON schedules.schedule_group_id = schedule_groups.schedule_group_id AND schedules.is_done = 0 SET schedule_groups.is_done = 0 WHERE schedules.schedule_id IS NOT NULL");
	
	/**
	 * Mark all schedule groups as done when all schedules in that group are done
	 */
	sql_query("UPDATE schedule_groups LEFT JOIN schedules ON schedules.schedule_group_id = schedule_groups.schedule_group_id AND schedules.is_done = 0 SET schedule_groups.is_done = 1 WHERE schedules.schedule_id IS NULL");
}

/**
 * Function to get mime type from extension
 */
function get_mime_from_ext($ext)
{
	$mime_types_map = array(
				"jpg" => "image/jpeg",
				"png" => "image/png",
				"jpeg" => "image/jpeg",
				"gif" => "image/gif",
				"avi" => "video/x-msvideo",
				"wmv" => "video/x-ms-wmv",
				"flv" => "video/x-flv",
				"mp4" => "video/mp4",
				"mpeg" => "video/mpeg",
				"m4v" => "video/x-m4v",
				"mpg" => "video/mpeg",
				"mkv" => "video/x-matroska",
				"3gp" => "video/3gp",
				"zip" => "application/zip",
	);
	
	if(!empty($mime_types_map[$ext]))return $mime_types_map[$ext];
	return 'application/octet-stream';
}


/**
 * Function to create image from image path
 * @param string $image_path
 * @return resource $image
 */
function imagecreatefromfile($image_path){
	list($width, $height, $image_type) = getimagesize($image_path);
	switch ($image_type){
		case IMAGETYPE_GIF: return imagecreatefromgif($image_path); break;
		case IMAGETYPE_JPEG: return imagecreatefromjpeg($image_path); break;
		case IMAGETYPE_PNG: return imagecreatefrompng($image_path); break;
		default: return ''; break;
	}
}


/**
 * Function to resize image with padding
 * @param string $img image resource
 * @param int $box_w box width
 * @param int $box_h box height
 * @return resource $image
 */

function thumbnail_box($img, $box_w, $box_h) {
    $new = imagecreatetruecolor($box_w, $box_h);
    if(empty($new))return false;
	
    $fill = imagecolorallocate($new, 0, 0, 0);
    imagefill($new, 0, 0, $fill);

    /**
	 * compute resize ratio
	 */
    $hratio = $box_h / imagesy($img);
    $wratio = $box_w / imagesx($img);
    $ratio = min($hratio, $wratio);

    /**
	 * if the source is smaller than the thumbnail size, don't resize -- add a margin instead (that is, dont magnify images)
	 */
    if($ratio > 1.0)$ratio = 1.0;

    /**
	 * compute sizes
	 */
    $sy = floor(imagesy($img) * $ratio);
    $sx = floor(imagesx($img) * $ratio);

	/**
     * compute margins
     * Using these margins centers the image in the thumbnail.
     * If you always want the image to the top left, 
     * set both of these to 0
	 */
    $m_y = floor(($box_h - $sy) / 2);
    $m_x = floor(($box_w - $sx) / 2);

	/**
     * Copy the image data, and resample
     * If you want a fast and ugly thumbnail,
     * replace imagecopyresampled with imagecopyresized
	 */
    if(!imagecopyresampled($new, $img,
        $m_x, $m_y, //dest x, y (margins)
        0, 0, //src x, y (0,0 means top left)
        $sx, $sy,//dest w, h (resample to this size (computed above)
        imagesx($img), imagesy($img)) //src w, h (the full size of the original)
    ) {
        //copy failed
        imagedestroy($new);
        return null;
    }
    //copy successful
    return $new;
}

/**
 * Function to get available slideshow types
 * @param string $verify
 */
function get_available_slideshow_type($verify = '')
{
	$types = array('blackfade' => 'Fade in&#x2F;out', 'crossfade' => 'Cross fade', 'uncoverleft' => 'Uncover from left', 'uncoverdown' => 'Uncover from down', 'ltr' => 'Uncover from left to right', 'uncoverupleft' => 'Uncover from up and left');
	
	if($verify == -1)return $types;
	if($verify)return array_key_exists($verify, $types);
	
	$options = '<option value="">RANDOM</option>';
	foreach($types as $type => $name)$options .= '<option value="'.$type.'">'.$name.'</option>';
	return $options;
}

/**
 * Converts a given size with units to bytes.
 * @param string $str
 */
function toBytes($str){
	$val = preg_replace('/\s/', '', trim($str));
	$last = strtolower($str[strlen($str)-1]);
	switch($last) {
		case 'g': $val *= 1024;
		case 'm': $val *= 1024;
		case 'k': $val *= 1024;
	}
	return $val;
}

/**
 * Function to clear tmp directory used by a schedule group
 * @param int $gid schedule group id
 * @return n/a
 */
function clear_tmp_sgid($gid)
{
	$d = dirname(__FILE__).'/tmp/';
	$files = scandir($d);
	foreach($files as $file){
		$p = $gid.'_slideshow\.mp4|'.$gid.'_wm_\.jpg|'.$gid.'_wm_\.mp4';
		if(preg_match('/'.$p.'/', $file))unlink($d.'/'.$file);	
	}
}

/**
 * Function to recursively delete a folder
 * @param string $dir
 * @return n/a
 */
function rrmdir($dir) 
{ 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   } 
}

/**
 * Function to shuffle associative array
 * @param array $list
 * @return array $random shuffled array
 */
function shuffle_assoc($list) 
{ 
  if (!is_array($list)) return $list; 

  $keys = array_keys($list); 
  shuffle($keys); 
  $random = array(); 
  foreach ($keys as $key) { 
    $random[$key] = $list[$key]; 
  }
  return $random; 
}

/**
 * Function to create zip file
 * @param string $folder to zip
 * @param string $zip zip file path to save
 */
function create_zip($folder, $zip_path)
{
	$zip = new ZipArchive();
	$zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($folder),
		RecursiveIteratorIterator::LEAVES_ONLY
	);

	$j = 0;
	foreach($files as $name => $file){
		if (!$file->isDir()){
			$filePath = $file->getRealPath();
			$relativePath = substr($filePath, strlen($folder) + 1);	
			if(!empty($filePath) && !empty($relativePath)){
				$zip->addFile($filePath, ltrim($relativePath, '/'));
				$j++;
			}
		}
	}
	$zip->close();
	
	/**
	 * Delete zip if no file added
	 */
	if(!$j)@unlink($zip_path);
}

/**
 * Function to delete fb events groups or pages
 */
function delete_fb_pages($site, $user_id, $fb_id, $page_id)
{
	list($table, $col) = get_site_params($site);
	
	sql_query("DELETE FROM schedules WHERE user_id = '$user_id' AND page_id = '$page_id' AND social_id = '$fb_id' AND site LIKE '$site'");
	sql_query("DELETE FROM token_expiry WHERE user_id = '$user_id' AND social_id = '$fb_id' AND page_id = '$page_id' AND site LIKE '$site'");
	sql_query("DELETE FROM $table WHERE $col = '$page_id' AND fb_id = '$fb_id' AND user_id = '$user_id'");
	
	/**
	 * Clear empty schedule groups
	 */
	sql_query("DELETE schedule_groups FROM schedule_groups 
				 LEFT JOIN schedules ON schedules.schedule_group_id = schedule_groups.schedule_group_id 
				 WHERE schedules.schedule_group_id IS NULL");
	
	/**
	 * Clear error logs
	 */			 
	sql_query("DELETE FROM error_msg WHERE user_id = '$user_id' AND social_id = '$fb_id' AND page_id = '$page_id' AND site LIKE '$site'");			 
				 
	clear_empty_post_logs();
}

/**
 * Function to send email
 * @param string $to
 * @param string $type type of email to be sent -> sch_done|token_expiry etc..
 * @param array $params parameters to include in email like user_id, schedule name, expired pages etc...
 */
function send_email($to, $type, $params)
{
	global $settings;
	$headers = "Content-type: text/html\r\n";
	$subject = '';
	$body = '';
	
	if($type == 'sch_done'){
		$subject = 'You schedule on '.strip_tags($params['page_name']).' has been completed';
		$body = '<html>
				  <body>
					Dear user,
					<p>
					  Your schedule on '.$params['page_name'].' has been completed. Please visit the <a href="'.site_url().'/dashboard.php">dashboard</a> to view and manage your schedules.
					</p>
					Best Regards,<br/>
					'.$settings['site_name'].'
				  </body>
				</html>';	
	}
	else if($type == 'pwd_reset'){
		$subject = 'Confirm password reset request';
		$body = '<html>
				  <body>
					Dear user,
					<p>
					  Your have requested to reset your password on '.$settings['site_name'].'. Please visit the following link to reset password-<br/>
					  <a href="'.site_url().'/reset.php?c='.$params['code'].'">'.site_url().'/reset.php?c='.$params['code'].'</a><br/><br/>
					  If you haven\'t requested to reset password, please ignore this email.
					</p>
					Best Regards,<br/>
					'.$settings['site_name'].'
				  </body>
				</html>';	
	}
	else if($type == 'user_add'){
		$subject = 'Your account has been created on '.$settings['site_name'];
		$body = '<html>
				  <body>
					Dear user,
					<p>
					  Your account has been created on '.$settings['site_name'].'<br/>
					  Please use the following login information to get access to member dashboard-<br/><br/>
					  Email : '.$to.'<br/>
					  Password: '.$params['pwd'].'<br/>
					  Login URL: <a href="'.site_url().'/login.php">'.site_url().'/login.php</a><br/><br/>
					  Please change your '.$settings['site_name'].' account password immediately after login.
					</p>
					Best Regards,<br/>
					'.$settings['site_name'].'
				  </body>
				</html>';	
	}
	else if($type == 'user_update'){
		$subject = 'Your account information has been updated on '.$settings['site_name'];
		$body = '<html>
				  <body>
					Dear user,
					<p>
					  Your account has been updated on '.$settings['site_name'].'<br/>
					  Please use the following login information to get access to member dashboard-<br/><br/>
					  Email : '.$to.'<br/>
					  Password: '.$params['pwd'].'<br/>
					  Login URL: <a href="'.site_url().'/login.php">'.site_url().'/login.php</a><br/><br/>
					  Please change your '.$settings['site_name'].' account password immediately after login.
					</p>
					Best Regards,<br/>
					'.$settings['site_name'].'
				  </body>
				</html>';	
	}
	else if($type == 'email_change'){
		$subject = 'Email verification code';
		$body = '<html>
				  <body>
					Dear user,
					<p>
					  You have requested to add this email to your '.$settings['site_name'].' account<br/>
					  Please use the following verification code to verify this email-<br/><br/>
					  Code : '.$params['code'].'<br/><br/>
					  If you didn\'t request to add this email address, please ignore this email.
					</p>
					Best Regards,<br/>
					'.$settings['site_name'].'
				  </body>
				</html>';	
	}
	else if($type == 'signup'){
		$link = rtrim(site_url(), '/').'/signup.php?v='.$params['code'];
		$subject = 'Registration confirmation';
		$body = '<html>
				  <body>
					Dear user,
					<p>
					  Thank you for creating an account in '.$settings['site_name'].'<br/>
					  Please use the following link to verify your email address-<br/><br/>
					  Link : <a href="'.$link.'">'.$link.'</a><br/><br/>
					</p>
					Best Regards,<br/>
					'.$settings['site_name'].'
				  </body>
				</html>';	
	}
	
	try{
		//mail($to, $subject, $body, $headers);
		
		$mail = new phpmailer(true); 
		$mail->FromName = $settings['site_name'];
		$mail->IsHTML(true);
		$mail->CharSet="UTF-8";
		$mail->AddAddress($to,'');
		$mail->AddReplyTo('noreply@localhost.com', 'No Reply');
		$mail->Subject = $subject;
    	$mail->Body = $body;
		$mail->send();
		
	}catch(Exception $e){return false;}
	
	return true;
}

/**
 * Function to send fb_noti when a schedule is complete
 * @param string $fb_id type of email to be sent -> sch_done|token_expiry etc..
 * @param array $params parameters to include in email like user_id, schedule name, expired pages etc...
 * @param string $access_token users access token
 * @param string $app_token app_token of the app
 */
function fb_noti($fb_id, $params, $access_token, $app_token)
{
	$url = 'https://graph.facebook.com/'.$fb_id.'/notifications?access_token='.$access_token.'&template='.urlencode("Your schedule for ".$params['page_name']." has been completed").'&href='.urlencode('dashboard.php?show=schedules');
	$post = array('access_token' => $app_token);
	$d = curl_single($url, $post);
	$d = json_decode($d, true);
	if(!empty($d['success']))return true;
	return false;
}

/**
 * Function to decode password reset token and check if too many pwd request or requested too early 
 */
function auth_init_pwd_reset_request($uid, $code, $long_live = 0)
{
	$c = base64_decode($code);
	$c = explode('|', $c);
	if(count($c) < 3)return false;
	
	if($c[1] != $uid)return false;
	if(time() - $c[2] < 15*60 && !$long_live)return false;
	if(time() - $c[2] > 3600*24 && $long_live)return false;
	return true;
}

/**
 * Function to create password reset token
 */
function gen_pwd_reset_token($uid)
{
	return base64_encode(sha1(rand().rand().rand().rand().rand()).'|'.$uid.'|'.time());
}

/**
 * Get list of plan columns from plan table
 */
function get_plan_columns()
{
	$cols = array();
	$q = sql_query("SHOW COLUMNS FROM membership_plans");
	while($res = sql_fetch_assoc($q))$cols[] = $res;
	return $cols;
}

/**
 * Function to get membership plan html to use in select input
 */
function get_membership_plans()
{
	$q = sql_query("SELECT * FROM membership_plans");
	$plans = '';
	while($res = sql_fetch_assoc($q)){
		$plans .= '<option rel="'.base64_encode(json_encode($res)).'" value="'.$res['plan_id'].'">'.$res['plan_name'].'</option>';
	}
	return $plans;
}

/**
 * Function to get membership plan html to use in select input
 */
function get_plan_details($plan_id)
{
	return sql_fetch_assoc(sql_query("SELECT * FROM membership_plans WHERE plan_id = '$plan_id'"));
}

/**
 * Function to get elapsed time
 */
function time_elapsed_string($ptime)
{
    $etime = time() - $ptime;
    if ($etime < 1){
        return '0 seconds';
    }

    $a = array( 365 * 24 * 60 * 60  =>  'year',
                 30 * 24 * 60 * 60  =>  'month',
                      24 * 60 * 60  =>  'day',
                           60 * 60  =>  'hour',
                                60  =>  'minute',
                                 1  =>  'second'
                );
    $a_plural = array( 'year'   => 'years',
                       'month'  => 'months',
                       'day'    => 'days',
                       'hour'   => 'hours',
                       'minute' => 'minutes',
                       'second' => 'seconds'
                );

    foreach ($a as $secs => $str){
        $d = $etime / $secs;
        if ($d >= 1){
            $r = round($d);
            return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
        }
    }
}



/**
 * Function to get path to cron tab
 */
function get_path_to_crontab()
{
	$path = '/usr/bin/crontab';
	exec($path.' -l', $o, $c);
	if($c == 0 || $c == 1)return $path;
	
	$path = '/bin/crontab';
	exec($path.' -l', $o, $c);
	if($c == 0 || $c == 1)return $path;
	
	return false;
}

/**
 * Function to prepare cron tasks list
 */
function get_cron_task_list()
{
	$d = dirname(__FILE__);
	$d = rtrim($d, '/');
	$d = rtrim($d, '\\');
	$d = str_replace('\\', '/', $d);
	
	$cron = array();
	$cron[0] = $d.'/cron/poster.php';
	$cron[1] = $d.'/cron/hid.php';
	$cron[2] = $d.'/cron/misc.php';
	$cron[3] = $d.'/cron/stats.php';
	
	return $cron;
}

/**
 * Function to get cron lock status
 */
function get_lock_status($file)
{
	if(!file_exists($file))return -1;
	$r = check_running_file($file);
	if($r)return 0;
	
	clearstatcache();
	$t = filemtime($file);
	return time_elapsed_string($t);
}

/**
 * Function to list language files
 */
function list_lang_files($verify = 0)
{
	$files = array();
	$lfiles = scandir(__ROOT__.'/lang/');
	foreach($lfiles as $lfile){
		if($lfile == '..' || $lfile == '.' || $lfile == 'default.php' || $lfile == 'index.php' || $lfile == '.htaccess')continue;
		$lf = str_replace('.php', '', $lfile);
		$aa = file_get_contents(__ROOT__.'/lang/'.$lfile);
		if(!preg_match('/Language File/', $aa))continue;
		if($verify && $lf == $verify)return true;
		$files[] = $lf;
	}
	if($verify)return false;
	return $files;
}

/**
 * Function to extract links from caption
 */
function extract_caption_links($caption)
{
	$link = '';
	if(preg_match('/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', $caption, $m)){
		$link = $m[0];
		$caption = str_replace($link, '', $caption);	
	}
	return array($caption, $link);
}

/**
 * Function to get site url
 */
function site_url()
{
	global $settings;
	return rtrim($settings['site_url'], '/');
}

/**
 * Function to reset schedule group
 * @param int $gid schedule group id
 * @param int $keep_logs whether to keep post logs
 * @return n/a
 */
function reset_schedule_group($gid, $keep_logs = 0, $time_zone = '', $next_post = '', $reset = 1)
{
	if(!$keep_logs)sql_query("DELETE FROM post_log WHERE schedule_group_id = '$gid'");
	$next_post_mysql = '';
	
	list($post_freq_sec, $post_only_from, $post_only_to, $post_start_from, $comment_bumps) = sql_fetch_row(sql_query("SELECT schedule_groups.schedule_interval, schedule_groups.post_only_from, schedule_groups.post_only_to, schedule_groups.post_start_from, schedule_groups.comment_bumps FROM schedule_groups WHERE schedule_group_id = '$gid'"));
	
	if(empty($next_post))$next_post = get_next_post_time($post_freq_sec, $post_only_from, $post_only_to, $post_start_from, $time_zone);
	
	$comment_bumps = sql_real_escape_string($comment_bumps);
	$q = sql_query("SELECT schedule_id FROM schedules WHERE schedule_group_id = '$gid'");
	$add_minute = 1; 
	
	$extra = "";
	if($reset)$extra = " is_done = 0, ";
	
	while($res = sql_fetch_assoc($q)){
		$sid = $res['schedule_id'];
		sql_query("UPDATE schedules SET $extra next_post = DATE_ADD(FROM_UNIXTIME('$next_post'), INTERVAL 0 MINUTE), notes = '', comment_bumps = '$comment_bumps' WHERE schedule_id = '$sid'");
		if(!$add_minute || $add_minute == 1){
			sql_query("UPDATE schedule_groups SET $extra next_post = DATE_ADD(FROM_UNIXTIME('$next_post'), INTERVAL 0 MINUTE) WHERE schedule_group_id = '$gid'");
			list($next_post_mysql) = sql_fetch_row(sql_query("SELECT next_post FROM schedule_groups WHERE schedule_group_id = '$gid'"));
		}
		$add_minute += (int)($post_freq_sec/60)+1;
		$next_post = get_next_post_time($post_freq_sec, $post_only_from, $post_only_to, $post_start_from_og, $time_zone, $next_post + 60 );
	}
	
	if($reset)reset_schedule_group_status();
	return $next_post_mysql;
}

/**
 * Function to secure email address
 */
function secure_email($email)
{
	$link = 'mailto:' . $email;
	$obfuscatedLink = "";
	for ($i=0; $i <strlen($link); $i++){
		 $obfuscatedLink .= "&#" . ord($link[$i]) . ";";
	}
	return $obfuscatedLink;
}

/**
 * data must contain all from users, schedules and schedule_groups table
 */
function set_next_schedule_time( $data, $elapsed = 0, $jump_to = 0 )
{
	$interval = $data['schedule_interval'];
	if($data['sync_post']){		
		if( $elapsed ) {
			$elapsed = ceil($elapsed/60);
			sql_query("UPDATE schedules SET next_post = DATE_ADD(next_post, INTERVAL $elapsed MINUTE) WHERE 
						schedule_group_id = '".$data['schedule_group_id']."' AND next_post != '0000-00-00 00:00:00'");
		}
		list($max_post_next) = sql_fetch_row(sql_query("SELECT UNIX_TIMESTAMP(MAX(next_post)) FROM schedules WHERE schedule_group_id = '".$data['schedule_group_id']."'"));
		
		$add = $max_post_next + 60;
	}
	else{
		$interval += 60;
		$add = time() - 60;
	}
		
	$next_post = get_next_post_time($interval, $data['post_only_from'], $data['post_only_to'], $data['post_start_from'], $data['time_zone'], $add + $jump_to );
	
	sql_query("UPDATE schedules SET is_locked = 0, next_post = FROM_UNIXTIME('$next_post') 
				WHERE schedule_id = '".$data['schedule_id']."' AND next_post != '0000-00-00 00:00:00'");
	
	list($min_post_next) = sql_fetch_row(sql_query("SELECT MIN(next_post) FROM schedules WHERE schedule_group_id = '".$data['schedule_group_id']."' AND is_done = 0 AND is_active = 1 AND rate_limited = 0 AND next_post != '0000-00-00 00:00:00'"));
	
	if(empty($min_post_next))$min_post_next = '0000-00-00 00:00:00';			
	
	sql_query("UPDATE schedule_groups SET next_post = '$min_post_next' 
				WHERE schedule_group_id = '".$data['schedule_group_id']."' AND is_done != 1");
}

?>