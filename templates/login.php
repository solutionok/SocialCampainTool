<?php
/**
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
	<div class="col-lg-4 col-md-offset-4">
    	<h3><?php echo $lang['login'][0]?></h3>
        <?php if(!empty($error)){?>
        <div class="alert alert-danger"><?php echo $error;?></div>
        <?php }else if(!empty($is_demo)){?>
        <div class="alert alert-info">
			Login as admin: <b>admin@example.com</b><br/>
			Login as user: <b>test@example.com</b><br/>
			Password: <b>123456</b>
		</div>
        <?php }?>
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
            
            <button class="btn btn-info btn-block login-btn"><?php echo $lang['header'][4]?></button>
            <input type="hidden" name="r" value="<?php echo @purify_text($_GET['r'])?>" />
        </form>
        <br/>
        <div>
        	<a href="<?php echo makeuri('reset.php')?>" class="btn btn-primary btn-block"><?php echo $lang['login'][3]?></a>
        </div>       
    </div>
</div>