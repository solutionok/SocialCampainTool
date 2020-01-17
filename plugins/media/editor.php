<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$login_required = true;

include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['meditor'];

include(__ROOTDIR__.'/templates/header.php');
include(__CURDIR__.'/templates/editor.php');
include(__CURDIR__.'/templates/footer.php');
include(__ROOTDIR__.'/templates/footer.php');
?>