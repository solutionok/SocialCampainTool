<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
class xml_feed
{
	public $url;
	public $error;
	public $posts;
	
	public function __construct($url)
	{
		$this->url = $url;
		$parts = @parse_url($url);
		
		if($parts['scheme'] != 'https' && $parts['scheme'] != 'http'){
			$this->error = 'INVALID_FEED_URL';
			return false;
		}
		
		return $this->parse();
	}
	
	public function parse()
	{
		$this->posts = array();
		$feed = @simplexml_load_file($this->url);
	
        if(empty($feed)){
			$this->error = 'FAILED_TO_LOAD_FEED';
			return false;
		}
		
		if(!empty($feed->channel->item))$c = $feed->channel->item;
		else if(!empty($feed->channel))$c = $feed->channel;
		else if(!empty($feed->entry->item))$c = $feed->entry->item;
		else if(!empty($feed->entry))$c = $feed->entry;
		else{
			$this->error = 'FAILED_TO_LOAD_FEED';
			return false;
		}
		
		foreach ($c as $item)
        {	
			$post = array();
            if(!empty($item->link->attributes()->href))$post['link']  = (string) $item->link->attributes()->href;
			else $post['link']  = (string) $item->link;
			if(!empty($item->image->attributes()->src))$post['image']  = (string) $item->image->attributes()->src;
            $post['title'] = (string) $item->title;
            $post['desc']  = (string) $item->description;
            $post['summary'] = $this->summarizeText($item->description);

			if( empty($post['link']) && empty($post['title']) && empty($post['desc']) )continue;

            $this->posts[] = $post;
        }
		return true;
	}
	
	public function summarizeText($summary) 
	{
        $summary = strip_tags($summary);
        $max_len = 100;
        if(strlen($summary) > $max_len){
            $summary = substr($summary, 0, $max_len) . '...';
		}
        return $summary;
    }
	
	
}