<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

$login_required = true;
include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['dash'];

include(__ROOT__.'/templates/header.php');
include(__ROOT__.'/templates/dashboard.php');
include(__ROOT__.'/templates/footer.php');
?>