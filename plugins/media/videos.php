<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$login_required = true;

include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['qv'];

include(__ROOTDIR__.'/templates/header.php');
include(__CURDIR__.'/templates/videos.php');
include(__ROOTDIR__.'/templates/footer.php');
?>