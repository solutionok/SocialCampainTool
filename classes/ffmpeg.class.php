<?php
/**
 * @package Social Ninja
 * @version 1.3
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
class ffmpeg
{
	public $FFMPEG;
	public $ot_dir;
	public $input;
	public $log_file;
	public $ffmpeg_log;
	public $error;
	public $duration;
	
	public $ffmpeg_exists;
	public $imagick_exists;
	
	public function __construct($video = '')
	{
		global $settings;
		$this->FFMPEG = $settings['ffmpeg'];
		
		if(!empty($video)){
			$this->input = $video;
			$this->ot_dir = dirname(dirname(__FILE__)).'/tmp/'.time().rand();
			$this->log_file = $this->ot_dir.'/log.txt';
			$this->ffmpeg_log = $this->ot_dir.'/ffmpeg_log.txt';
		
			if(!is_dir(dirname($this->ot_dir)))mkdir(dirname($this->ot_dir));
			if(!is_dir($this->ot_dir))mkdir($this->ot_dir);
		}
		else{
			$this->ot_dir = dirname(dirname(__FILE__)).'/tmp/';
			$this->log_file = $this->ot_dir.'/log_'.rand().time().'.txt';
			$this->ffmpeg_log = $this->ot_dir.'/ffmpeg_log_'.rand().time().'.txt';	
			
			if(!is_dir($this->ot_dir))mkdir($this->ot_dir);
		}
	}
	
	public function check_video()
	{
		if(!file_exists($this->input)){
			$this->error = 'FILE_MISSING';
			return false;
		}	
		$d = $this->analyze_video($this->input);
		if(empty($d['duration'])){
			$this->error = 'EMPTY_DURATION';
			return false;
		}
		$this->duration = $d;
		return true;
	}
	
	/**
	 * Function to create screenshots with ffmpeg
	 * @params array $times an array of times
	 * This function saves screenshots in $ot_dir
	 */
	public function create_screenshot($times)
	{
		$this->log('Creating screenshots...');
		foreach($times as $time){
			$ot = $this->ot_dir.'/screen_'.$this->pretty_time($time).'.png';
			$cmd = $this->FFMPEG.' -ss '.$time.' -i '.$this->input.' -vframes 1 -threads 1 '.$ot.' 2> '.$this->ffmpeg_log;
			$this->log('Screenshot at '.$time.' : '.$cmd);
			exec($cmd, $o, $c);
			if(!$c){
				$this->log('Screenshot successfully created at '.$time);	
			}
			else{
				$this->log('Failed to create screenshot at '.$time);
				@unlink($ot);
			}
		}
	}
	
	/**
	 * Function to create chunks from video with ffmpeg
	 * @params array $times an array of times | $times is a multidimensional array with each array element presenting another array with 0 => start_time 1=> end time
	 * This function saves video in $ot_dir
	 */
	public function create_chunks($times)
	{
		$this->log('Creating chunks...');
		foreach($times as $time){
			$s = $time[0];
			$t = $time[1] - $time[0];
			$ot = $this->ot_dir.'/chunk_'.$this->pretty_time($s).'_'.$this->pretty_time($time[1]).'.mp4';
			if($t < 5){
				$this->log('Chunk size less than 5 second');
				continue;
			}
			$cmd = $this->FFMPEG.' -ss '.$s.' -t '.$t.' -i '.$this->input.' -vcodec libx264 -acodec libmp3lame -threads 1 '.$ot.' 2> '.$this->ffmpeg_log;
			$this->log('Chunk at '.$s.' to '.$time[1].' : '.$cmd);
			exec($cmd, $o, $c);
			if(!$c){
				$this->log('Chunk successfully created at '.$s);	
			}
			else{
				$this->log('Failed to create chunk at '.$s);
				@unlink($ot);
			}
		}
	}
	
	/**
	 * Function to join chunks to single video with ffmpeg
	 * @params array $files an array of files -> each array item must indicate full system path
	 * This function saves the joined video in output dir
	 */
	public function join_chunks($files)
	{
		$this->log('Joining chunks...');
		
		$j = 0;
		$w = array();
		$h = array();
		$filter_complex = array();
		$pieces = array();
		$cmd = $this->FFMPEG;
		foreach($files as $i => $file){
			if(!file_exists($file)){
				$this->log("$file does not exists");
				unset($files[$i]);
				continue;
			}
			list($w1, $h1) = $this->get_video_size($file);
			if(!$w1 || !$h1){
				$this->log("$file has invalid size $w1 $h1");
				unset($files[$i]);
				continue;	
			}
			$filter_complex[] = "[$j:v]scale=WWWxHHH,setsar=1[v$j];[$j:a]anull[a$j]";
			$pieces[] = "[v$j][a$j]";
			$cmd .= ' -i '.$file.' ';
			$w[] = $w1;
			$h[] = $h1;
			$j++;
		}
		
		if(empty($files)){
			$this->log('No file found to join');
			return false;
		}
		
		$ot = $this->ot_dir.'/joined_'.time().'.mp4';
		$cmd .= ' -filter_complex "'.implode(';', $filter_complex).';'.implode('', $pieces).'concat=n='.$j.':v=1:a=1 [v] [a]" -map "[v]" -map "[a]" -vcodec libx264 -acodec libmp3lame -b 1500k -threads 1 '.$ot.' 2> '.$this->ffmpeg_log;
		
		$cmd = str_replace(array('WWW', 'HHH'), array(min($w), min($h)), $cmd);
		
		$this->log('Joining with command '.$cmd);
		exec($cmd, $o, $c);
		if(!$c){
			$this->log('Videos joined successfully');
			return true;	
		}
		$this->log('Failed to join videos');
		@unlink($ot);
		return false;
	}
	
	/**
	 * Function to join timed segments to single video with ffmpeg
	 * @params array $times an array of times | $times is a multidimensional array with each array element presenting another array with 0 => start_time 1=> end tim
	 * This function saves the joined video in output dir
	 */
	public function join_segments($times)
	{
		$this->log('Joining segments...');
		
		$j = 0;
		$filter_complex = array();
		$pieces = array();
		$cmd = $this->FFMPEG." -i ".$this->input;
		
		foreach($times as $time){
			$filter_complex[] = "[0:v]trim=start=".$time[0].":end=".$time[1].",setpts=PTS-STARTPTS[v$j];[0:a]atrim=start=".$time[0].":end=".$time[1].",asetpts=PTS-STARTPTS[a$j]";
			$pieces[] = "[v$j][a$j]";
			$j++;	
		}
		
		$ot = $this->ot_dir.'/joins_'.time().'.mp4';
		$cmd .= ' -filter_complex "'.implode(';', $filter_complex).';'.implode('', $pieces).'concat=n='.$j.':v=1:a=1 [v] [a]" -map "[v]" -map "[a]" -vcodec libx264 -acodec libmp3lame -b 1500k -threads 1 '.$ot.' 2> '.$this->ffmpeg_log;
				
		$this->log('Joining with command '.$cmd);
		exec($cmd, $o, $c);
		if(!$c){
			$this->log('Videos joined successfully');
			return true;	
		}
		$this->log('Failed to join videos');
		@unlink($ot);
		return false;
	}
	
	/**
	 * Function to create screenshot tiles from video with ffmpeg
	 * @param int $row row number
	 * @param int $column column number
	 * This function saves screenshots in $ot_dir
	 */
	public function create_tiles($row, $column)
	{
		$this->log('Creating tiles...');
		$data = $this->analyze_video($this->input);
		if(empty($data))return false;
		
		/**
		 * My selected width and height
		 */
		$sw = 320;
		$sh = 240;
		
		/**
		 * Now adjust tile size according to video aspect ratio
		 */
		$w = $data['width'];
		$h = $data['height'];
		
		if($w && $h){
			$r = $sw/$w;
			$sh = (int)($r*$h);
		}
		
		$frame = $data['duration']*($data['fps'] + (rand(-20,20)/10));
		$total = $row*$column;
		$diff = (int)($frame/$total);
		if($diff < 1)$diff = 1;
		$ot = $this->ot_dir.'/tiles_'.$row.'x'.$column.'.png';
		$cmd = $this->FFMPEG.' -ss 1 -i '.$this->input.' -frames 1 -threads 1 -vf "select=not(mod(n\,'.$diff.')),scale='.$sw.':'.$sh.',tile='.$row.'x'.$column.'" '.$ot.' 2> '.$this->ffmpeg_log;
		$this->log('Tiles : '.$cmd);
		exec($cmd, $o, $c);
		if(!$c){
			$this->log('Tiles successfully created');	
			
			list($w, $h) = getimagesize($ot);
			
			/*
			 * Create header
			 */
			$ot_title = $ot.'_title.png';
			
			$im = imagecreatetruecolor($w, 150);
			$white = imagecolorallocate($im, 255, 255, 255);
			$black = imagecolorallocate($im, 0, 0, 0);
			imagefilledrectangle($im, 0, 0, $w, 150, $white);;
			$font = dirname(dirname(__FILE__)).'/fonts/tahoma.ttf';
			imagettftext($im, 14, 0, 5, 25, $black, $font, "Filename: ".basename($this->input));
			imagettftext($im, 14, 0, 5, 50, $black, $font, "Filesize: ".formatSize(filesize($this->input)));
			imagettftext($im, 14, 0, 5, 75, $black, $font, "Duration: ".pretty_time($data['duration']));
			imagettftext($im, 14, 0, 5, 100, $black, $font, $data['audio']);
			imagettftext($im, 14, 0, 5, 125, $black, $font, $data['video'].' , '.$data['fps'].' fps');
			
			imagepng($im, $ot_title);
			
			if(file_exists($ot_title) && filesize($ot_title) > 100){
				$ot_f = $ot.'_final.png';
				$this->merge_images($ot_title, $ot, $ot_f);	
				if(file_exists($ot_f) && filesize($ot_f) > 100){
					rename($ot_f, $ot);
					@unlink($ot_title);
				}
			}
		}
		else{
			$this->log('Failed to create tiles');
			@unlink($ot);
		}
	}
	
	/**
	 * Function to join two png images
	 */
	public function merge_images($filename_x, $filename_y, $filename_result){		
		list($width_x, $height_x) = getimagesize($filename_x);
		list($width_y, $height_y) = getimagesize($filename_y);
				
		$image = imagecreatetruecolor($width_x, $height_x + $height_y);
		$image_x = imagecreatefrompng($filename_x);
		$image_y = imagecreatefrompng($filename_y);
		
		imagecopy($image, $image_x, 0, 0, 0, 0, $width_x, $height_x);
		imagecopy($image, $image_y, 0, $height_x, 0, 0, $width_y, $height_y);
		
		imagepng($image, $filename_result);
		imagedestroy($image);
		imagedestroy($image_x);
		imagedestroy($image_y);	
	}
	
	/**
	 * Function to create slideshow from images with ffmpeg
	 * @param array $files -> array of image system paths
	 * @param int $duration duration of each slide
	 * @param string $effect effect type
	 * This function saves created video in $ot_dir
	 */
	public function create_slideshow($files, $duration, $effect)
	{
		$this->log('Creating slideshow...');
		
		$j = 0;
		$w = array();
		$h = array();
		$filter_complex = array();
		$pieces = array();
		
		$imdir = $this->ot_dir.'/images/';
		if(!is_dir($imdir))mkdir($imdir);
		
		foreach($files as $i => $file){
			if(!file_exists($file)){
				$this->log("$file does not exists");
				unset($files[$i]);
				continue;
			}
			
			list($w1, $h1) = getimagesize($file);
			if(!$w1 || !$h1){
				$this->log("$file has invalid size $w1 $h1");
				unset($files[$i]);
				continue;	
			}
			
			$w[] = $w1;
			$h[] = $h1;
		}
		
		if(empty($files)){
			$this->log('No file found to process');
			return false;
		}
		
		foreach($files as $i => $file){
			$ot = $imdir.basename($file).'.png';			
			$this->log('Resizing image');
			
			$av_w = round(min($w)/2)*2;
			$av_h = round(min($h)/2)*2;
			
			$img = imagecreatefromfile($file);
			$img_r = thumbnail_box($img, $av_w, $av_h);
			imagepng($img_r, $ot);
			
			if(@filesize($ot) < 100){
				$this->log('Failed to resize image');
				unset($files[$i]);
			}
			else{
				$this->log('Image resized successfully');	
				$files[$i] = $ot; 
			}
		}
		
		if(empty($files)){
			$this->log('No file found to process');
			return false;
		}
		
		$ot = $this->ot_dir.'/slideshow_'.time().'.mp4';
		$cmd = $this->FFMPEG;
		
		if($effect == 'blackfade'){
			foreach($files as $file){
				$cmd .= ' -loop 1 -i '.$file.' ';
				$filter_complex[] = "[$j:v]trim=duration=$duration,".($j ? 'fade=t=in:st=0:d=0.5,' : '')."fade=t=out:st=".($duration-0.5).":d=0.5[v$j]";
				$pieces[] = "[v$j]";
				$j++;
			}
			
			$cmd .= ' -filter_complex "'.implode(';', $filter_complex).';'.implode('', $pieces).'concat=n='.$j.':v=1:a=0:,format=yuv420p [v]" -map "[v]" -vcodec libx264 -b 1500k '.$ot.' 2> '.$this->ffmpeg_log;
		}
		else{
			foreach($files as $file){
				$cmd .= ' -loop 1 -t '.($duration-1).' -i '.$file.' ';
				
				if($effect == 'crossfade')$exp = "blend=all_expr='A*(if(gte(T,1),1,T))+B*(1-(if(gte(T,1),1,T)))'";
				else if($effect == 'uncoverleft')$exp = "blend=all_expr='if(gte(T*SW*600+X,W),A,B)'";
				else if($effect == 'uncoverdown')$exp = "blend=all_expr='if(gte(Y-T*SH*600,0),B,A)'";
				else if($effect == 'ltr')$exp = "blend=all_expr='if(gte(X,W*T/1),B,A)'";
				else if($effect == 'uncoverupleft')$exp = "blend=all_expr='if(gte(T*SH*600+Y,H)*gte((T*600*SW+X)*W/H,W),A,B)'";
				
				$filter_complex[] = "[".($j+1).":v]trim=duration=1[vv$j];[$j:v]trim=duration=1[vvv$j];[vv$j][vvv$j]".$exp."[v".($j+1)."]";
				
				$pieces[] = '['.$j.':v][v'.($j+1).']';
				$j++;
			}
			
			array_pop($filter_complex);
			array_pop($pieces);
			
			$cmd .= ' -filter_complex "'.implode(';', $filter_complex).';'.implode('', $pieces).'['.($j-1).':v]concat=n='.($j*2 - 1).':v=1:a=0,format=yuv420p[v]" -map "[v]" -threads 1 '.$ot.' 2> '.$this->ffmpeg_log;
		}
		
		$this->log('Creating slideshow with command '.$cmd);
		exec($cmd, $o, $c);
		
		//clean image dir
		
		if(!$c && filesize($ot)){
			$this->log('Slideshow created successfully');
			return $ot;	
		}
		$this->log('Failed to create slideshow');
		@unlink($ot);
		return false;
		
	}
	
	/**
	 * Function to get video duration
	 * @params string $video path to video
	 * @return int $time in seconds
	 */
	public function get_video_duration($video){
		$d = 0;
		$log = $this->ffmpeg_log;
		$cmd = $this->FFMPEG.' -i '.$video.' 2> '.$log;
		exec($cmd,$o,$c);
		if(!$c || $c){
			$str = file_get_contents($log);
			if(preg_match("/Duration: ([0-9\:\.]+),/siU", $str, $m));
			if(!empty($m[1])){
				$d = preg_split("/\:/siU", $m[1]);
				$d = ((float)$d[0])*3600+((float)$d[1])*60+((float)$d[2]);
			}	
		}
		unlink($log);
		return $d;
	}
	
	/**
	 * Function to get video size wxh
	 * @params string $video path to video
	 * @return array size (w,h) in pixel
	 */
	public function get_video_size($video){
		$d = array(0, 0);
		$log = $this->ffmpeg_log;
		$cmd = $this->FFMPEG.' -i '.$video.' 2> '.$log;
		exec($cmd,$o,$c);
		if(!$c || $c){
			$str = file_get_contents($log);
			if(preg_match("/\s([0-9]+)x([0-9]+)([\s|,])/siU", $str, $m));
			if(!empty($m[1]) && !empty($m[2])){
				$d = array((int)$m[1], (int)$m[2]);
			}	
		}
		unlink($log);
		return $d;
	}
	
	public function analyze_video($video)
	{
		$data = array();
		$log = $this->ffmpeg_log;
		$cmd = $this->FFMPEG.' -i '.$video.' 2> '.$log;
		exec($cmd,$o,$c);
		if(!$c || $c){
			$str = file_get_contents($log);
			if(preg_match("/\s([0-9]+)x([0-9]+)([\s|,])/siU", $str, $m));
			if(!empty($m[1]) && !empty($m[2])){
				$data['width'] = (int)$m[1];
				$data['height'] = (int)$m[2];
			}
			
			if(preg_match("/Duration: ([0-9\:\.]+),/siU", $str, $m));
			if(!empty($m[1])){
				$d = preg_split("/\:/siU", $m[1]);
				$data['duration'] = ((float)$d[0])*3600+((float)$d[1])*60+((float)$d[2]);
			}	
						
			if(preg_match("/Stream \#0\:.*Video\:(.*)/mi", $str, $m));
			if(!empty($m[1])){
				$s = trim($m[1]);
				$ss = $data['width'].'x'.$data['height'];
				$s = explode($ss, $s);
				$s = $s[0].' '.$ss;
				$data['video'] = 'Video: '.preg_replace('/\s{2,}/', '', $s);
			}
			
			if(preg_match("/Stream \#0\:.*Audio\:(.*)/mi", $str, $m));
			if(!empty($m[1])){
				$data['audio'] = 'Audio: '.preg_replace('/\s{2,}/', '', trim($m[1]));
			}
			
			if(preg_match("/, ([0-9\.]+) fps,/mi", $str, $m));
			if(!empty($m[1])){
				$data['fps'] = (float)trim($m[1]);;
			}
		}
		unlink($log);
		return $data;
	}
	
	/**
	 * Function to create pretty time
	 *
	 * @param int $t time
	 * @return string $time in hh_mm_ss
	 */
	public function pretty_time($t)
	{
		return str_replace(':', '_', pretty_time($t));
	}
	
	/**
	 * Function to clean log files
	 *
	 */
	public function clean_logs()
	{
		@unlink($this->log_file);
		@unlink($this->ffmpeg_log);
		if(is_dir($this->ot_dir.'/images'))rrmdir($this->ot_dir.'/images');
	}
	
	/**
	 * Function to log debug strings
	 *
	 * @param string $str the string to log
	 * @return n/a
	 */
	public function log($str, $clear = 0)
	{
		$log_file = $this->log_file;
		clearstatcache();
		if(@filesize($log_file) > 2*1024*1024){
			$clear = 1;	
		}
		if($clear)$fp = fopen($log_file, "w");
		else $fp = fopen($log_file, "a");
		
		fwrite($fp, date('[d-M-Y H:i:s]')." $str\r\n");
		fclose($fp);
		
		if(!empty($_SERVER['HTTP_USER_AGENT']))echo $str."<br/>";
		else echo $str."\n";
		@flush();
		@ob_flush();
		
	}
}

?>