<?php
/**
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['home'];

include(__ROOT__.'/templates/header.php');
include(__ROOT__.'/templates/homeslider.php');
include(__ROOT__.'/templates/index.php');
if(file_exists(__ROOT__.'/templates/pricing.php'))
	include(__ROOT__.'/templates/pricing.php');
include(__ROOT__.'/templates/footer.php');
?>