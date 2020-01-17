<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

define('__ROOTDIR__', dirname(dirname(dirname(__FILE__))));
define('__CURDIR__', dirname(__FILE__));
include(__ROOTDIR__.'/loader.php');

if(!$settings['downloader_plugin_enabled']){
	display_error($lang['common'][49]);
	exit;
}
?>