<?php

require_once dirname(__FILE__).'/Google/Client.php';
require_once dirname(__FILE__).'/Google/Service/YouTube.php';

class Youtube
{
	/**
 	 * curl related variables
 	 */
	public $url;
	public $referer;
	public $cookie;
	public $curl_info;
	public $doPost;
	public $postData;
	public $response;
	public $last_url;
	public $userAgent;
	public $proxy;
	public $proxyAuth;
	
	public $yt_video_id;
	
	public $error;
	public $client;
	public $youtube;
	
	public $raw_token;
	public $yt_id;
	public $yt_token;
	public $yt_refresh_token;
	
	public $yt_account;
	
	public $log_file;
	
	
	public function __construct($token = '')
	{
		$this->log_file = dirname(__FILE__).'/log.txt';
		@unlink($this->log_file);
		
		$this->loadClass();
			
		if(!empty($token)){
			$this->loadToken($token);	
		}
	}
	
	public function getLoginUri()
	{
		return $this->client->createAuthUrl();
	}
	
	public function getUser()
	{
		$this->error = '';
		$this->url = 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$this->yt_token;
		$this->doPost = 0;
		$this->get_source();
		$data = json_decode($this->response, true);
		
		if(empty($data['id'])){
			if(!empty($data['error']['message']))$this->error = $data['error']['message'];
			else $this->error = 'Failed to retrieve user data!';
			$this->log($this->error);
			return false;	
		}
		
		$this->url = 'https://www.googleapis.com/youtube/v3/channels?part='.urlencode('id,statistics,contentDetails').'&mine=true&access_token='.$this->yt_token;
		$this->doPost = 0;
		$this->get_source();
		$ch = json_decode($this->response, true);

		if(empty($ch['items'][0])){
			$this->error = 'Failed to retrieve channel data!';
			$this->log($this->error);
			return false;	
		}
		
		$ch = array('channel' => $ch['items'][0]);
				
		$data = array_merge($data, $ch);
		$this->log('User data successfully received!');	

		$this->yt_account = $data;
		return $data;
	}
	
	public function getFeed($user_id)
	{
		$this->error = '';
		$this->url = 'https://www.googleapis.com/youtube/v3/playlistItems?maxResults=50&part=id,snippet&playlistId='.str_replace('UC', 'UU', $user_id);
		$this->doPost = 4;
		$this->get_source();
		$data = json_decode($this->response, true);
		//$this->log($this->response);
		if(!empty($data['items'])){
			$this->log('Feed data successfully returned!');
			return $data['items'];	
		}
		else $this->error = 'Failed to retrieve feed data!';
		$this->log($this->error);
		return false;
	}
	
	public function getInsights($id)
	{
		$this->error = '';
		$this->url = 'https://gdata.youtube.com/feeds/api/users/default/uploads/'.$id.'?alt=json&v=2.1';
		$this->doPost = 4;
		$this->get_source();
		$data = json_decode($this->response, true);
		//$this->log($this->response);
		if(!empty($data['entry'])){
			$this->log('Feed data successfully returned!');
			return $data['entry'];	
		}
		else $this->error = 'Failed to retrieve feed data!';
		$this->log($this->error);
		return false;
	}
	
	function deleteVideo($id)
	{
		$this->error = '';
		try{
			$this->youtube->videos->delete($id);
		}catch(Exception $e){
			$this->error = $e;
			$this->log($e);
		}
		$this->log('Successfully deleted video!');
	}
	
	public function loadClass()
	{
		global $settings;
		$client = new Google_Client();
		$client->setClientId($settings['yt_client_id']);
		$client->setClientSecret($settings['yt_client_secret']);
		$client->setRedirectUri(rtrim($settings['site_url'], '/').'/dologin.php');
		$client->setDeveloperKey($settings['yt_dev_token']);
		$client->setScopes(array('https://www.googleapis.com/auth/youtube','https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/youtube.upload'));
		$client->setAccessType('offline');
		$client->setState('offline');
		$client->setApprovalPrompt('force');
		$this->client = $client;
		$this->youtube = new Google_Service_YouTube($client);
	}
	
	public function loadToken($token)
	{
		$this->raw_token = '';
		$this->yt_token = '';
		$this->yt_refresh_token = '';
		$this->yt_id = '';
		
		$token = stripslashes($token);
		//$this->log($token);
		$tokens = json_decode($token, true);
		if(($tokens['created'] + $tokens['expires_in']) <= time()){
			//$this->log(($tokens['created'] + $tokens['expires_in']).'|'.time());
			$this->yt_refresh_token = stripslashes($tokens['refresh_token']);
			$this->yt_id = $tokens['yt_id'];
			$this->refreshToken();	
			return false;
		}
		
		$this->raw_token = $token;
		$this->client->setAccessToken($token);
		$this->yt_token = stripslashes($tokens['access_token']);
		$this->yt_refresh_token = stripslashes($tokens['refresh_token']);
		$this->yt_id = @$tokens['yt_id'];
		
		if(!empty($this->yt_id)){
			sql_query("UPDATE yt_accounts SET access_token = '".sql_real_escape_string($token)."' WHERE yt_id = '".sql_real_escape_string($this->yt_id)."'");
		}
		
		//$this->log('Final access token : '.$this->yt_token.' | Refresh token : '.$this->yt_refresh_token.' | ID : '.$this->yt_id);	
		
	}
	
	public function refreshToken($try = 0)
	{
		$this->error = '';
		$this->log('Refreshing tokens...');
		if(empty($this->yt_refresh_token)){
			$this->log('Empty refresh token');
			return false;	
		}
		try{
			$this->client->refreshToken($this->yt_refresh_token);
		}catch(Exception $e){
			$this->error = $e;
			$this->log('Failed to renew access token '.$e);
			return false;	
		}
		
        $token = $this->client->getAccessToken();
		$token = json_decode($token, true);
		$token['refresh_token'] = $this->yt_refresh_token;
		$token['yt_id'] = $this->yt_id;
		$token = json_encode($token);
		$this->loadToken($token);
	}
	
	public function uploadVideo($file, $title, $desc, $category, $privacy = 'public', $tags = '')
	{
		$this->log('Uploading video...');
		$this->yt_video_id = '';
		if(empty($this->yt_token)){
			$this->error = 'Empty token';
			$this->log($this->error);
			return false;
		}
		
		$youtube = $this->youtube;
		$snippet = new Google_Service_YouTube_VideoSnippet();
    	
		if(strlen($title) >= 100)$title = substr($title, 0, 99);
		if(strlen($desc) >= 1000)$title = substr($desc, 0, 999);
		
		$snippet->setTitle($title);
    	$snippet->setDescription($desc);
		
		$ttt = $tt = preg_replace('/[^a-z0-9\s]/i', '', $title);
		$tt = explode(' ', $tt);
		
		$cc = explode('|', $category);
		
		$tt[] = $cc[0];
		
		$stop = array('i','a','about','an','and','are','as','at','be','by','com','de','en','for','from','how','in','is','it','la','of','on','or','that','the','this','to','was','what','when','where','who','will','with','und','the','www', 'has', 'have', 'been', 'there');
		
		foreach($tt as $i => $a)if(strlen($a) < 3 || strlen($a) > 99 || in_array($a, $stop))unset($tt[$i]);
		$tt = array_slice($tt, 0, 7);
		
		$tags = trim($tags);
		$this->log('title: '.$title.' | tags: '.implode(',', $tt).' | category: '.$category.' | desc: '.$desc.' | passed tags: '.$tags);
		
		if(!empty($tags)){
			$this->log('Setting tags '.$tags);
			$snippet->setTags(explode(',', $tags));
		}
		else{
			$this->log('Setting tags '.implode(',', $tt));
			if(!empty($tt))$snippet->setTags($tt);
		}
		
    	// Numeric video category. See
    	// https://developers.google.com/youtube/v3/docs/videoCategories/list 
		try{
    		$snippet->setCategoryId(end($cc));
		}catch(Exception $e){
			$this->error = $e;
			$this->log($this->error);
			return false;	
		}
    	// Set the video's status to "public". Valid statuses are "public",
    	// "private" and "unlisted".
    	$status = new Google_Service_YouTube_VideoStatus();
    	try{
			$status->privacyStatus = $privacy;
		}catch(Exception $e){
			$this->error = $e;
			$this->log($this->error);
			return false;	
		}
	    $video = new Google_Service_YouTube_Video();
    	$video->setSnippet($snippet);
    	$video->setStatus($status);
		
		$size = filesize($file);

		// Specify the size of each chunk of data, in bytes. Set a higher value for
		// reliable connection as fewer chunks lead to faster uploads. Set a lower
		// value for better recovery on less reliable connections.
		$chunkSizeBytes = 10 * 1024 * 1024;

    	// Setting the defer flag to true tells the client to return a request which can be called
    	// with ->execute(); instead of making the API call immediately.
    	$this->client->setDefer(true);

		try{
    		// Create a request for the API's videos.insert method to create and upload the video.
    		$insertRequest = $youtube->videos->insert("status, snippet", $video);
				
		}catch(Exception $e){
			$this->error = $e;
			$this->log($this->error);
			return false;	
		}

		//if($size > $chunkSizeBytes)$chunkSizeBytes = $size/2;
		
		// Create a MediaFileUpload object for resumable uploads.
		$media = new Google_Http_MediaFileUpload(
			$this->client,
			$insertRequest,
			'video/*',
			null,
			true,
			$chunkSizeBytes
		);
		
		$media->setFileSize($size);

		$this->log('Sending video...');
		// Read the media file and upload it chunk by chunk.
		$status = false;
		$handle = fopen($file, "rb");
		while (!$status && !feof($handle)) {
		  $chunk = fread($handle, $chunkSizeBytes);
		  try{
			  $status = $media->nextChunk($chunk);
		  }catch(Exception $e){
				$this->error = $e;
				$this->log($this->error);
				return false;	
			}
		}
	
		fclose($handle);
		
		// If you want to make other calls after the file upload, set setDefer back to false
		$this->client->setDefer(false);
		$this->yt_video_id = $status['id'];	
		$this->log('Successfully uploaded '.$this->yt_video_id);
	} 
	
	public function get_source()
	{
		$this->log("Requesting ".$this->url.' '.($this->doPost ? 'Request type: POST' : 'Request type: GET'));
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_HEADER, 0);	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	
		curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
		if($this->doPost == 1){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->postData));
		}
		else if($this->doPost == 2){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postData);
		}
		else if($this->doPost == 3){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		}
		else if($this->doPost == 4){
			$this->log('Setting access headers');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->yt_token));
		}
		if(!empty($this->proxy)){
			$this->log('Using proxy '.$this->proxy);
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
			if(!empty($this->proxyAuth)){
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyAuth);	
			}
		}
		$data = curl_exec($ch);
		$data = preg_replace('/\s{2,}/',' ',$data);
		$this->response = $data;
		$info = curl_getinfo($ch);
		$this->curl_info = $info;
		$this->last_url = $info['url'];
		curl_close($ch);
		$this->log('Last request url : '.$this->last_url.'. HTTP response: '.$this->curl_info['http_code']);
		if($this->curl_info['http_code'] == 403){
			/*$block_path = dirname(dirname(__FILE__))."/yt_blocked_proxy.txt";
			$this->log('Proxy blocked');
			file_put_contents($block_path, $this->proxy.':'.$this->proxyAuth , FILE_APPEND);*/	
		}
	}
	
	public function log($str)
	{
		$fp = fopen($this->log_file, "a");
		fwrite($fp, date('[d-M-Y H:i:s]')." $str\r\n");
		fclose($fp);
		
		//echo $str."<br/>";
		//@flush();
		//@ob_flush();
		
	}
}


?>