<?php
/**
 * Video download class
 *
 * @package Social Ninja
 * @version 1.3
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
class download
{
	const SUPPORTED_HOSTS = 'youtube\.com|youtu\.be|facebook\.com|fb\.com|vimeo\.com|dailymotion\.com';
	const YT_HOSTS = 'youtube\.com|youtu\.be';
	const VM_HOSTS = 'vimeo\.com';
	const DM_HOSTS = 'dailymotion\.com';
	const FB_HOSTS = 'facebook\.com|fb\.com';
	
	public $url;
	public $error;
	public $video_id;
	
	public $hash;
	public $is_video;
	public $video_info;
	public $links;
	
	public function __construct($url)
	{
		global $lang;
		if(!empty($url)){
			$this->url = $url;
			$this->error = '';
			$this->is_video = 0;
			$url = strtolower($url);
			list($size, $mime) = $this->getSize($url);
			if($mime == 'image/jpeg' || $mime == 'image/png'){
				if($size > 5*1024*1024){
					$this->error = $lang['dwclass']['img_too_large'];
					return false;	
				}
				$this->get_image($mime);	
			}
			else{
				$url_data = parse_url($url);
				if(!preg_match('/'.self::SUPPORTED_HOSTS.'/i', $url_data['host'])){
					$this->error = $lang['dwclass']['not_supported'];
					return false;
				}
				if(preg_match('/'.self::FB_HOSTS.'/i', $url_data['host'])){
					$this->is_video = 1;
					$this->get_fb_links();
				}
				if(preg_match('/'.self::YT_HOSTS.'/i', $url_data['host'])){
					$this->is_video = 1;
					$this->get_yt_links();
				}
				if(preg_match('/'.self::DM_HOSTS.'/i', $url_data['host'])){
					$this->is_video = 1;
					$this->get_dm_links();
				}
				if(preg_match('/'.self::VM_HOSTS.'/i', $url_data['host'])){
					$this->is_video = 1;
					$this->get_vm_links();
				}
			}
		}
	}
	
	public function get_image($mime)
	{
		if($mime == 'image/jpeg')$ext = '.jpg';
		else $ext = '.png';
		
		$localFile = dirname(dirname(__FILE__)).'/tmp/'.rand().'_'.rand().'_'.rand().'.'.$ext;
		$this->downloadFile($this->url, $localFile);
		if(empty($this->error)){
			$this->links = $localFile;
			return true;	
		}
		return false;
	}
	
	public function get_yt_links()
	{
		global $lang;
		$yt_id = $this->get_yt_id();
		if(!$yt_id){
			$this->error = $lang['dwclass']['yt_extract_fail'];
			return false;	
		}
		$this->hash = sha1($this->video_id.'|yt');
		$this->video_id = $yt_id;
		$url = 'https://www.youtube.com/watch?v='.$yt_id;
		$source = curl_single($url);
		
		if(preg_match("/ytplayer.config = (.*);ytplayer\.load/siU", $source, $m)){
			$data = json_decode($m[1], true);
			
			/**
			 * Get video info
			 */
			$this->video_info = array();
			$this->video_info['title'] = @$data['args']['title'];;
			$this->video_info['thumb'] = @$data['args']['thumbnail_url'];
			$this->video_info['duration'] = @round($data['args']['length_seconds']/60, 2).' minutes';
			$this->video_info['views'] = @number_format($data['args']['view_count']);
			$this->video_info['rating'] = @round($data['args']['avg_rating'], 2);
			
			/**
			 * Get download links
			 */
			$url_encoded_fmt_stream_map = @$data['args']['url_encoded_fmt_stream_map'];
			
			if(!empty($url_encoded_fmt_stream_map)){
				$formats = explode(',',$url_encoded_fmt_stream_map);
				if(empty($formats)){
					$this->error = $lang['dwclass']['yt_emp_format'];
					return false;		
				}
				else{
					$avail_formats = array();
					$i = 0;
					
					foreach($formats as $format) {
						$ipbits = $ip = $itag = $quality = $s = $signature = $sig = '';
						$expire = time(); 
					
						parse_str($format);
						
						if(!empty($s) && empty($signature))$signature = $this->generate_signature($s);
						else if(!empty($sig) && empty($signature))$signature = $this->generate_signature($sig);
						
						$avail_formats[$i]['itag'] = $itag;
						$avail_formats[$i]['quality'] = $quality;
						$type = explode(';',$type);
						$avail_formats[$i]['type'] = $type[0];
						$avail_formats[$i]['url'] = urldecode($url) . '&signature=' . $signature;
						parse_str(urldecode($url));
						$avail_formats[$i]['expires'] = date("G:i:s T", $expire);
						$avail_formats[$i]['ipbits'] = $ipbits;
						$avail_formats[$i]['ip'] = $ip;
						list($bsize) = $this->getSize($avail_formats[$i]['url']);
						$avail_formats[$i]['size'] = formatSize($bsize);
						
						if($type[0] == 'video/mp4')$avail_formats[$i]['ext'] = 'mp4';
						else if($type[0] == 'video/3gpp')$avail_formats[$i]['ext'] = '3gp';
						else if($type[0] == 'video/x-flv')$avail_formats[$i]['ext'] = 'flv';
						else if($type[0] == 'video/webm')$avail_formats[$i]['ext'] = 'webm';
						
						$hash = sha1($avail_formats[$i]['url'].'|yt');
						$avail_formats[$i]['hash'] = $hash;
						$avail_formats[$i]['t'] = time();
						$avail_formats[$i]['bsize'] = $bsize;
						
						$i++;
					}
					$this->links = $avail_formats;					
				}
			}
			else{
				$this->error = $lang['dwclass']['yt_emp_fmt'];
				return false;
			}
		}
		else{
			$this->error = $lang['dwclass']['yt_emp_conf'];
			return false;
		}
	}
	
	public function generate_signature($s)
	{
		$s = str_split($s);
        $this->hK($s);
		$this->Cd($s, 67);
		$this->Cd($s, 1);
		$this->hK($s);
		$this->A1($s, 1);
		$this->Cd($s, 17);
		return implode("", $s);
	}
	
	public function Cd(&$a, $b)
	{
		$c = $a[0];
		$a[0] = $a[$b % count($a)];
		$a[$b] = $c;
	}
	public function A1(&$a, $b)
	{
		$a = array_slice($a, $b, 1000);
	}
	public function hK(&$a)
	{
		$a = array_reverse($a);
	}
	
	public function get_dm_links()
	{
		global $lang;
		$url = $this->url;
		$source = curl_single($url);
		$this->video_info = array();
		$this->links = array();
		
		if(preg_match('/(\{"context":.*)buildPlayer\(config\);/siU', $source, $m)){
			
			$m[1] = trim($m[1]);
			$m[1] = trim($m[1],';');
			$data = json_decode($m[1], true);
		
			if(!empty($data['metadata'])){
				$this->video_id = $data['metadata']['id'];
				$this->hash = sha1($this->video_id.'|dm');
				$this->video_info['title'] = $data['metadata']['title'];
				$this->video_info['thumb'] = $data['metadata']['poster_url'];
				$this->video_info['duration'] = round($data['metadata']['duration']/60, 2).' minutes';
				$this->video_info['views'] = '';
				
				if(preg_match('/"video_views":([0-9]+),/siU', $source, $m))$this->video_info['views'] = $m[1];
				
				$ext = 'mp4';
				if(!empty($data['metadata']['qualities']))
				foreach($data['metadata']['qualities'] as $q => $video){
					$video = end($video);
					if($video['type'] != 'video/mp4')continue;
					$hash = sha1($video['url'].'|dm');
					list($bsize) = $this->getSize($video['url']);
					$size = formatSize($bsize);
					$this->links[] = 
						array('quality' => $q.'p', 'type' => 'video/mp4', 'url' => $video['url'], 'hash' => $hash, 'ext' => $ext, 'size' => $size, 't' => time(), 'bsize' => $bsize);
				}
				else $this->error = $lang['dwclass']['dm_emp_q'];
			}
			else $this->error = $lang['dwclass']['dm_emp_meta'];
		}
		else $this->error = $lang['dwclass']['dm_emp_context'];
		
	}
	
	public function get_vm_links()
	{
		global $lang;
		$url = $this->url;
		$source = curl_single($url);
		
		$this->video_info = array();
		$this->links = array();	
		
		if(preg_match('/UserPlays","value":(.*)\}/siU', $source, $m)){
			$this->video_info['views'] = number_format(trim(strip_tags($m[1])));	
		}
		if(preg_match('/UserLikes","value":(.*)\}/siU', $source, $m)){
			$this->video_info['likes'] = number_format(trim(strip_tags($m[1])));	
		}
		
		if(preg_match("/\"config_url\":\"(.*)\"/siU", $source, $m)){
						
			$url = html_entity_decode(stripslashes($m[1]));
			
			$source = curl_single($url);
			$data = json_decode($source, true);
			
			if(!empty($data['video'])){
				
				$this->video_id = $data['video']['id'];
				$this->hash = sha1($this->video_id.'|vm');
				$this->video_info['title'] = $data['video']['title'];
				$this->video_info['thumb'] = str_replace('_640.', '_300.', $data['video']['thumbs']['640']);
				$this->video_info['duration'] = round($data['video']['duration']/60, 2).' minutes';
				$ext = 'mp4';
				
				foreach(@$data['request']['files']['progressive'] as $video){
					if(empty($video['url']))continue;
					$hash = sha1($video['url'].'|vm');
					$type = $video['quality'];
					list($bsize) = $this->getSize($video['url']);
					$size = formatSize($bsize);
					$this->links[] = 
						array('quality' => $type, 'type' => 'video/mp4', 'url' => $video['url'], 'hash' => $hash, 'ext' => $ext, 'size' => $size, 't' => time(), 'bsize' => $bsize);
				}
			}
			else $this->error = $lang['dwclass']['vm_emp_data'];
		}
		else $this->error = $lang['dwclass']['vm_emp_conf'];
	}
	
	public function get_fb_links()
	{
		global $lang;
		$fb_id = $this->get_fb_id();
		if(!$fb_id){
			$this->error = $lang['dwclass']['fb_extract_fail'];
			return false;	
		}
		$this->video_id = $fb_id;
		$this->hash = sha1($this->video_id.'|fb');
		$source = curl_single($this->url);
		$data = json_decode($source, true);
		
		$this->video_info = array();
		$this->links = array();
		
		$url = 'https://graph.facebook.com/'.$this->video_id.'?fields='.urlencode('name,length,source,picture,likes.limit(0).summary(true)');
		$source = curl_single($url);
		$data = json_decode($source, true);	
		
		$this->video_info['title'] = empty($data['name']) ? $this->video_id : $data['name'];
		if(empty($this->video_info['thumb']))$this->video_info['thumb'] = $data['picture'];
		$this->video_info['duration'] = round($data['length']/60, 2).' minutes';
		$this->video_info['views'] = $views;
		$this->video_info['likes'] = number_format($data['likes']['summary']['total_count']);
		$this->video_info['title'] = $data['id'];
		
		$ext = 'mp4';					
		$hash = sha1($data['source'].'|fb');
		
		list($bsize) = $this->getSize($data['source']);
		$size = formatSize($bsize);
		
		$this->links[] = array('quality' => 'default', 'type' => 'video/mp4', 'url' => $data['source'], 'hash' => $hash, 'ext' => $ext, 'size' => $size, 't' => time(), 'bsize' => $bsize);
					
	}
	
	public function get_yt_id()
	{
		$url = $this->url;
		if(preg_match("/v=(.*)\&/siU", $url, $m))return $m[1];
		else if(preg_match("/v=(.*)$/siU", $url, $m))return $m[1];
		else if(preg_match("/youtu\.be\/(.*)$/siU", $url, $m))return $m[1];
		else return false;
	}
	
	public function get_fb_id()
	{
		$url = $this->url;
		if(preg_match("/(facebook\.com|fb\.com)\/(.*)\/videos\/(.*)\/([0-9]+)\//siU", $url, $m))return $m[4];
		else if(preg_match("/(facebook\.com|fb\.com)\/(.*)\/videos\/([0-9]+)\//siU", $url, $m))return $m[3];
		else if(preg_match("/(facebook\.com|fb\.com)\/video\.php\?v\=([0-9]+)/siU", $url, $m))return $m[2];
		else return false;
	}
	
	public function getSize($url)
	{
		$ch = curl_init($url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_HEADER, true); 
		curl_setopt($ch, CURLOPT_NOBODY, true); 
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (U; Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0');
		$data = curl_exec($ch); 
		$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD); 
		$mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);		 
		curl_close($ch);
		return array($size, $mime); 
	}
	
	public function downloadFile($url, $localFile)
	{
		global $lang;
		$this->error = '';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER,0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);		
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);		
		curl_setopt($ch, CURLOPT_AUTOREFERER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1800);
		curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1; rv:15.0) Gecko/20100101 Firefox/15.0.1");	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
		$fp = fopen($localFile, 'w');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_exec($ch);
		
		$d = curl_getinfo($ch);
		if($d['http_code'] != 200)$this->error = $lang['dwclass']['f_dwn_fail'].' HTTP CODE: '.$d['http_code'];
	}
}
