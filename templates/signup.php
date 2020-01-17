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
    	<h3><?php echo $lang['title']['signup']?></h3>
        <?php if(!empty($error)){?>
        <div class="alert alert-danger"><?php echo $error;?></div>
        <?php }else{
        		if($success == 1)echo '<div class="alert alert-success">'.$lang['signup'][11].'</div>';
        		else if($success == 2)echo '<div class="alert alert-success">'.$lang['signup'][12].'</div>';
			}
			if(!$success || !empty($error)){
        ?>
        <form action="" method="post" id="login-form">
        	<div class="form-group email-address">
              <label class="control-label"><?php echo $lang['login'][1]?></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                <input class="form-control" name="email" type="text" value="<?php echo @purify_text($_POST['email'])?>">
              </div>
            </div>
            
            <div class="form-group password">
              <label class="control-label"><?php echo $lang['login'][2]?></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input class="form-control" name="password" type="password">
              </div>
            </div>
            
            <div class="form-group">
              <label class="control-label"><?php echo $lang['reset'][1]?> <a href="javascript:void(0)" onclick="$('#captcha_img').attr('src', 'plugins/captcha/captcha.php?'+Math.random());$('#captcha').val('');"><?php echo $lang['reset'][2]?></a></label>
              <img src="plugins/captcha/captcha.php?<?php echo time()?>" id="captcha_img"/>
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-exclamation-sign"></i></span>
                <input class="form-control" name="captcha" id="captcha" type="text" value=""/>
              </div>
            </div>
            
            <button class="btn btn-info btn-block"><?php echo $lang['common'][50]?></button>
        </form>
        <?php }?>
        <br/>
    </div>
</div>