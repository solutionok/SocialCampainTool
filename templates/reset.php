<?php
/**
 * @package Social Ninja
 * @version 1.4
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
	<div class="col-lg-4 col-md-offset-4">
    	<?php if($step == 1){
		/**
		 * Reset request
		 * Show email input and captcha
		 */	
		?>
        <h3><?php echo $lang['reset'][0]?></h3>
        <?php if(!empty($error)){?>
        <div class="alert alert-danger"><?php echo $error;?></div>
        <?php }
		/**
		 * If verification email is not sent show reset form
		 */
		if(!$success){?>
        <form action="" method="post" id="reset-form">
        	<div class="form-group email-address">
              <label class="control-label"><?php echo $lang['login'][1]?></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                <input class="form-control" name="email" type="text" value="<?php echo @purify_text($_POST['email'])?>">
              </div>
            </div>
            
            <div class="form-group">
              <label class="control-label"><?php echo $lang['reset'][1]?> <a href="javascript:void(0)" onclick="$('#captcha_img').attr('src', 'plugins/captcha/captcha.php?'+Math.random());$('#captcha').val('');"><?php echo $lang['reset'][2]?></a></label>
              <img src="plugins/captcha/captcha.php?<?php echo time()?>" id="captcha_img"/>
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input class="form-control" name="captcha" id="captcha" type="text" value=""/>
              </div>
            </div>
            <button class="btn btn-sm btn-info btn-block login-btn"><?php echo $lang['common'][45]?></button>
        </form>       
        <?php }
		/**
		 * Show success message if email is sent
		 */
		else{?>	
		<div class="alert alert-success">
        	<?php echo $lang['reset'][3]?>
        </div>
		<?php
        }}else if($step == 2){
			if(!$success){
		?>
        <h3><?php echo $lang['reset'][0]?></h3>
        <?php if(!empty($error)){?>
        <div class="alert alert-danger"><?php echo $error;?></div>
        <?php }?>
        <form action="" method="post" id="reset-form">
        	<div class="form-group">
              <label class="control-label"><?php echo $lang['dashboard'][68]?></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input class="form-control" name="password" type="password">
              </div>
            </div>
            
            <div class="form-group">
              <label class="control-label"><?php echo $lang['dashboard'][69]?></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input class="form-control" name="password2" type="password">
              </div>
            </div>
            
            <button class="btn btn-sm btn-info btn-block login-btn"><?php echo $lang['common'][20]?></button>
        </form>
        <?php }else{?>
		<div class="alert alert-success">
        	<?php echo $lang['reset'][4]?>
        </div>		
		<?php }}?>
    </div>
</div>