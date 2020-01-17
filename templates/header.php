<?php
/**
 * @package Social Ninja
 * @version 1.5
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();

/**
 * Unset pending app changes on page navigation
 */
unset($_SESSION['pending_fb_app']);
unset($_SESSION['pending_tw_app']);
unset($_SESSION['pending_yt_app']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <base href="<?php echo __BASEURI__ == '' ? '/' : __BASEURI__.'/'?>">
    <title><?php if(!empty($title))echo $title.' | '; echo $settings['site_name']?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $settings['meta_description']?>">
    <meta name="keywords" content="<?php echo $settings['meta_keywords']?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="<?php echo site_url()?>/images/logo.png">
    <link rel="stylesheet" href="<?php echo site_url()?>/css/themes/<?php echo empty($user_data['theme']) ? $settings['site_theme'] : $user_data['theme']?>/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="<?php echo site_url()?>/css/themes/assets/css/bootswatch.min.css">
    <link rel="stylesheet" href="<?php echo site_url()?>/css/custom.css?v=1.7">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="<?php echo site_url()?>/css/themes/bower_components/html5shiv/dist/html5shiv.js"></script>
      <script src="<?php echo site_url()?>/css/themes/bower_components/respond/dest/respond.min.js"></script>
    <![endif]-->
    <?php if(empty($is_demo)){?>
	<script>0<parent.frames.length&&top.location.replace(document.location.toString());</script>
    <?php }?>
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script>
		var ajax_url = '<?php echo makeuri('ajax.php')?>';
		var dash_url = '<?php echo makeuri('dashboard.php')?>';
		var upload_url = '<?php echo makeuri('upload.php')?>';
    	var login_url = '<?php echo makeuri('dologin.php')?>';
    	try{
			var lang = $.parseJSON('<?php echo json_encode($lang['js'])?>');
		}catch(e){
			alert('Failed to parse language file. Javascript will not work correctly. Error: '+e)
		}
    </script>
    <script src="<?php echo site_url()?>/js/bootstrap.min.js"></script>
  </head>
  <body>
    <div class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <a href="<?php echo makeuri('index.php')?>" class="navbar-brand"><?php echo $settings['site_name']?></a>
          <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <div class="navbar-collapse collapse" id="navbar-main">
          <ul class="nav navbar-nav">
            <li class="dropdown">
              <a class="dropdown-toggle" data-toggle="dropdown" href="#" id="themes"><?php echo $lang['header'][0]?> <span class="caret"></span></a>
              <ul class="dropdown-menu" aria-labelledby="themes">
                <li><a href="<?php echo makeuri('dashboard.php?show=summary')?>"><?php echo $lang['header']['dashboard'][0]?></a></li>
                <li class="divider"></li>
                <li><a href="<?php echo makeuri('dashboard.php?show=accounts')?>"><?php echo $lang['header']['dashboard'][1]?></a></li>
                <li><a href="<?php echo makeuri('dashboard.php?show=folders')?>"><?php echo $lang['header']['dashboard'][2]?></a></li>
                <li class="divider"></li>
                <?php if(!empty($settings['fb_enabled'])){?>
                <li><a href="<?php echo makeuri('dashboard.php?show=fanpages')?>"><?php echo $lang['header']['dashboard'][3]?></a></li>
                <li><a href="<?php echo makeuri('dashboard.php?show=groups')?>"><?php echo $lang['header']['dashboard'][4]?></a></li>
                <li><a href="<?php echo makeuri('dashboard.php?show=events')?>"><?php echo $lang['header']['dashboard'][5]?></a></li>
                <li class="divider"></li>
                <?php }?>
                <li><a href="<?php echo makeuri('dashboard.php?show=categories')?>"><?php echo $lang['header']['dashboard']['cats']?></a></li>
                <li><a href="<?php echo makeuri('dashboard.php?show=rss')?>"><?php echo $lang['header']['dashboard'][7]?></a></li>
                <li><a href="<?php echo makeuri('dashboard.php?show=schedules')?>"><?php echo $lang['header']['dashboard'][8]?></a></li>
                <li class="divider"></li>
                <li><a href="<?php echo makeuri('dashboard.php?show=cleanup')?>"><?php echo $lang['header']['dashboard'][9]?></a></li>
                <li><a href="<?php echo makeuri('dashboard.php?show=logs')?>"><?php echo $lang['header']['dashboard'][11]?></a></li>
                <li><a href="<?php echo makeuri('dashboard.php?show=settings')?>"><?php echo $lang['header']['dashboard'][10]?></a></li>
              </ul>
            </li>
            <?php if($settings['media_plugin_enabled'] || $settings['downloader_plugin_enabled']){?>
            <li class="dropdown">
            	<a class="dropdown-toggle" data-toggle="dropdown" href="#" id="themes"><?php echo $lang['header'][1]?> <span class="caret"></span></a>
              	<ul class="dropdown-menu" aria-labelledby="themes">
                	<?php if($settings['media_plugin_enabled']){?>
                	<li><a href="<?php echo makeuri('plugins/media/index.php')?>"><?php echo $lang['header']['cc'][0]?></a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo makeuri('plugins/media/tools.php')?>"><?php echo $lang['header']['cc'][4]?></a></li>
                    <li><a href="<?php echo makeuri('plugins/media/editor.php')?>"><?php echo $lang['header']['cc'][1]?></a></li>
                    <li><a href="<?php echo makeuri('plugins/media/meme.php')?>"><?php echo $lang['header']['cc'][2]?></a></li>
                    <li><a href="<?php echo makeuri('plugins/media/htoimage.php')?>"><?php echo $lang['header']['cc'][3]?></a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo makeuri('plugins/media/videos.php')?>"><?php echo $lang['header']['cc'][5]?></a></li>
                    <?php }?>
                    <?php if($settings['downloader_plugin_enabled']){?>
                    <li class="divider"></li>
                    <li><a href="<?php echo makeuri('plugins/downloader/index.php')?>"><?php echo $lang['header']['cc'][6]?></a></li>
                    <?php }?>
              	</ul>
            </li>
            <?php }?>
            <?php if(!empty($user_data['is_admin'])){?>
            <li class="dropdown">
            	<a class="dropdown-toggle" data-toggle="dropdown" href="#" id="themes"><?php echo $lang['header'][2]?> <span class="caret"></span></a>
              	<ul class="dropdown-menu" aria-labelledby="themes">
                	<li><a href="<?php echo makeuri('admin.php')?>"><?php echo $lang['header']['admin'][0]?></a></li>
                    <li><a href="<?php echo makeuri('admin.php?module=users')?>"><?php echo $lang['header']['admin'][1]?></a></li>
                    <li><a href="<?php echo makeuri('admin.php?module=plan')?>"><?php echo $lang['header']['admin'][2]?></a></li>
                    <li><a href="<?php echo makeuri('admin.php?module=accounts')?>"><?php echo $lang['header']['admin'][3]?></a></li>
                    <li><a href="<?php echo makeuri('admin.php?module=pages')?>"><?php echo $lang['header']['dashboard'][3]?></a></li>
                    <li><a href="<?php echo makeuri('admin.php?module=folders')?>"><?php echo $lang['header']['admin'][4]?></a></li>
                    <li><a href="<?php echo makeuri('admin.php?module=files')?>"><?php echo $lang['header']['admin'][5]?></a></li>
                    <li><a href="<?php echo makeuri('admin.php?module=schedules')?>"><?php echo $lang['header']['admin'][6]?></a></li>
                    <li><a href="<?php echo makeuri('admin.php?module=videos')?>"><?php echo $lang['header']['admin'][7]?></a></li>
                    <?php if(file_exists(__ROOT__.'/templates/admin/payments.php')){?>
                    <li><a href="<?php echo makeuri('admin.php?module=payments')?>"><?php echo $lang['header']['payment']?></a></li>
                    <?php }?>
                    <li><a href="<?php echo makeuri('admin.php?module=lang')?>"><?php echo $lang['header']['admin'][8]?></a></li>
              	</ul>
            </li>
            <?php }?>
            <?php
            if(file_exists(dirname(__FILE__).'/pricing.php')){
			?>
			<li>
              <a href="<?php echo makeuri('pricing.php')?>"><?php echo $lang['title']['pricing']?></a>
            </li>
            <?php
			}if(!empty($is_demo)){
            ?>
            <li>
              <a href="http://codecanyon.net/item/social-ninja-facebook-twitter-youtube-campaigner/13345502?ref=inspireddev">Buy now</a>
            </li>
            <?php }?>
            <li>
              <?php
			  if(file_exists(__ROOT__.'/help.php')){?>
              	<a href="<?php echo makeuri('help.php')?>"><?php echo $lang['header'][3]?></a>	
              <?php	}else if(file_exists(__ROOT__.'/docs/')){?>
              	<a href="<?php echo makeuri('docs/')?>"><?php echo $lang['header'][3]?></a>
              <?php }?>
            </li>
          </ul>

          <ul class="nav navbar-nav navbar-right">
          	<li class="clock">
            	<a href="javascript:void(0)" class="clock-display"><?php echo date('d-M-Y h:i:s A')?></a>
            </li>
            <?php
			if(empty($is_logged_in)){?>
            <?php if(!empty($settings['enable_signup'])){?>
            <li><a href="<?php echo makeuri('signup.php')?>"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;<?php echo $lang['header'][12]?></a></li>
            <?php }?>
            <li><a href="<?php echo makeuri('login.php')?>"><i class="glyphicon glyphicon-log-in"></i>&nbsp;&nbsp;<?php echo $lang['header'][4]?></a></li>
            <?php }else{?>
            <li class="dropdown">
              <a class="dropdown-toggle" data-toggle="dropdown" href="#" id="themes"><?php echo $lang['header'][5]?> <?php echo strtok($user_data['email'], '@')?>! 
              	<span class="caret"></span></a>
                  <ul class="dropdown-menu" aria-labelledby="themes">
				  	<li><a href="<?php echo makeuri('dashboard.php')?>"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp;<?php echo $lang['header'][0]?></a></li>
                    <li><a href="<?php echo makeuri('logout.php')?>"><i class="glyphicon glyphicon-off"></i>&nbsp;&nbsp;<?php echo $lang['header'][6]?></a></li>
                  </ul>
            </li>
            <?php }?>
          </ul>

        </div>
      </div>
    </div>
    
    <!--start of main html container-->
    <div class="container body">
    
    	<?php
		/**
		 * end here if maintenance mode is enabled and user is non admin
		 */
		if(!empty($settings['enable_maintenance_mode']) && empty($user_data['is_admin']) && !$is_login_page){
			if(!empty($settings['maintenance_message']))$msg = $settings['maintenance_message'];
			else $msg = $lang['js']['maintenance'];
			echo '<div class="alert alert-danger">'.$msg.'</div><br/>';
			include(__ROOT__.'/templates/footer.php');
			exit;
		}
		/**
		 * show warning to to admin on non home page
		 */
		else if(!empty($settings['enable_maintenance_mode']) && !empty($user_data['is_admin']) && !$is_index_page){
			if(!empty($settings['maintenance_message']))$msg = $settings['maintenance_message'];
			else $msg = $lang['js']['maintenance'];
			echo '<div class="alert alert-danger">'.$msg.'</div><br/>';
		}
		/**
		 * show token expiry warning to to users on non home page
		 */
		else if(!empty($user_id) && !$is_index_page){
			if($settings['fb_enabled']){
				$r = sql_num_rows(sql_query("SELECT NULL FROM token_expiry WHERE user_id = '$user_id' AND site LIKE 'fb%' LIMIT 1"));
				if($r){
					echo '<div class="alert alert-info">
							'.$lang['header'][8].' '.$lang['header'][7].' <a href="'.makeuri('dologin.php?login_type=facebook').'">'.$lang['common'][25].'</a>.&nbsp;&nbsp;'.$lang['header'][9].' <a href="'.makeuri('merge.php').'">'.$lang['common'][25].'</a>.
						  </div><br/>';	
				}
			}
			if($settings['tw_enabled']){
				$r = sql_num_rows(sql_query("SELECT NULL FROM token_expiry WHERE user_id = '$user_id' AND site LIKE 'twitter' LIMIT 1"));
				if($r){
					echo '<div class="alert alert-info">
							'.$lang['header'][10].' '.$lang['header'][7].' <a href="'.makeuri('dologin.php?login_type=twitter').'">'.$lang['common'][25].'</a>
						  </div><br/>';	
				}
			}
			if($settings['yt_enabled']){
				$r = sql_num_rows(sql_query("SELECT NULL FROM token_expiry WHERE user_id = '$user_id' AND site LIKE 'youtube' LIMIT 1"));
				if($r){
					echo '<div class="alert alert-info">
							'.$lang['header'][11].'  '.$lang['header'][7].' <a href="'.makeuri('dologin.php?login_type=youtube').'">'.$lang['common'][25].'</a>
						  </div><br/>';	
				}
			}
		}
		?>