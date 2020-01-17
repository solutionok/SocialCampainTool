<?php
/**
 * @package Social Ninja
 * @version 1.5
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row" style="margin-top:20px">
	<div class="col-lg-4">
		<a class="btn btn-sm btn-success" onclick="$('#fb_app_settings').toggle()"><?php echo $lang['dashboard']['apps'][0]?></a>
        <br/><br/>
        <form id="fb_app_settings" class="apps_form">
        	<div class="form-group">
            	<label>App Id</label>
                <input type="text" class="form-control" name="fb_app_id" value="<?php if(!empty($settings['fb_app_id']))echo $settings['fb_app_id']?>">
           
            	<label>App Secret</label>
                <input type="text" class="form-control" name="fb_app_secret">
            </div>
        </form>
        <button class="btn btn-sm btn-warning fb_app_save"><?php echo $lang['common'][20]?></button>
	</div>
    
    <div class="col-lg-4">
		<a class="btn btn-sm btn-info" onclick="$('#tw_app_settings').toggle()"><?php echo $lang['dashboard']['apps'][1]?></a>
        <br/><br/>
        <form id="tw_app_settings" class="apps_form">
        	<div class="form-group">
            	<label>Consumer Key</label>
                <input type="text" class="form-control" name="tw_app_id" value="<?php if(!empty($settings['tw_app_id']))echo $settings['tw_app_id']?>">
           
            	<label>Consumer Secret</label>
                <input type="text" class="form-control" name="tw_app_secret">
            </div>
        </form>
        <button class="btn btn-sm btn-warning tw_app_save"><?php echo $lang['common'][20]?></button>
	</div>
    
    <div class="col-lg-4">
		<a class="btn btn-sm btn-danger" onclick="$('#yt_app_settings').toggle()"><?php echo $lang['dashboard']['apps'][2]?></a>
        <br/><br/>
        <form id="yt_app_settings" class="apps_form">
        	<div class="form-group">
            	<label>Client Id</label>
                <input type="text" class="form-control" name="yt_client_id" value="<?php if(!empty($settings['yt_client_id']))echo $settings['yt_client_id']?>">
           
            	<label>Client Secret</label>
                <input type="text" class="form-control" name="yt_client_secret">
                
                <label>API Key</label>
                <input type="text" class="form-control" name="yt_dev_token">
            </div>
        </form>
        <button class="btn btn-sm btn-warning yt_app_save"><?php echo $lang['common'][20]?></button>
	</div>
</div>