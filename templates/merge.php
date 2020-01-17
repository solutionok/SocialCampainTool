<?php
/**
 * @package Social Ninja
 * @version 1.5
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();

$data = $auth->get_user_fb_ids($user_id);
?>
<div class="row">
	<div class="col-lg-12 text-center">
    	<h2><?php echo $lang['merge'][0]?></h2>
        <p style="text-align:justify">
        	<?php echo $lang['merge'][1]?><br/><br/>
            <b><?php echo $lang['merge'][3]?>:</b> <?php echo $lang['merge'][2]?>
        </p>
        
        <br/><br/>
        <div class="alert alert-info"><?php echo $lang['merge'][4]?></div>
		<select class="form-control merge_keep">
        	<?php echo $data?>
        </select>        
        
        <br/><br/>
        <div class="alert alert-success"><?php echo $lang['merge'][5]?></div>
        <select class="form-control merge_merge">
        	<?php echo $data?>
        </select> 
        
        <br/><br/>
        
        <button class="btn btn-sm btn-lg btn-primary fb_acc_merge"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;<?php echo $lang['merge'][6]?></button>
        
    </div>
</div>