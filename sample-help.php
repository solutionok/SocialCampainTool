<?php
/**
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */

include(dirname(__FILE__).'/loader.php');
$title = $lang['title']['help'];
include(__ROOT__.'/templates/header.php');
?>

<div class="row">
	<div class="col-lg-12">
    	<h3 class="text-center"><?php echo $lang['title']['help'];?></h3>
        
        <!--YOUR CONTENT GOES HERE-->
    </div>
</div>

<?php
include(__ROOT__.'/templates/footer.php');
?>