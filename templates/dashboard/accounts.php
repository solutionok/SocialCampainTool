<?php
/**
 * @package Social Ninja
 * @version 1.5
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<h3><?php echo $lang['js']['fbprofile']?></h3>
<hr />
    <div class="row">
        <div class="col-lg-12">
            <?php echo print_social_ids('fbprofile')?>
        </div>    
    </div>

<h3><?php echo $lang['js']['twitter']?></h3>
<hr />
    <div class="row">
        <div class="col-lg-12">
            <?php echo print_social_ids('twitter')?>
        </div>    
    </div>
    
<h3><?php echo $lang['js']['youtube']?></h3>
<hr />
<div class="row">
    <div class="col-lg-12">
        <?php echo print_social_ids('youtube')?>
    </div>    
</div>