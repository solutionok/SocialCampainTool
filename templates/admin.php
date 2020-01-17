<?php
/**
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();

if(!empty($_POST['site_config_save'])){
	if(empty($is_demo)){
		unset($_POST['site_config_save']);
		$kk = array();
		foreach($_POST as $k => $v){
			$k = sql_real_escape_string($k);
			$v = sql_real_escape_string($v);
			$kk[] = "`$k` = '$v'";
		}
		sql_query("UPDATE global_config SET ".implode(',', $kk));
		echo '<div class="alert alert-success">Configurations successfully saved</div>';
	}
	else echo '<div class="alert alert-danger">Admin actions are disabled in demo</div>';
}
else if(!empty($_POST['upload_logo'])){
	if(empty($is_demo)){
		if($_FILES['logo']['error'] == 0){
			if(!in_array($_FILES['logo']['type'], array('image/png'))){
				echo '<div class="alert alert-danger">Only png image allowed</div>';
			}
			else{
				$l = __ROOT__.'/tmp/logo.png';
				$r = move_uploaded_file($_FILES['logo']['tmp_name'], $l);	
				if(!$r)echo '<div class="alert alert-danger">Failed to move uploaded file</div>';
				else{
					list($w, $h) = getimagesize($l);
					if($w != 80 || $h != 80){
						echo '<div class="alert alert-danger">Upload 80x80 sized pic</div>';
						unlink($l);
					}
					else{
						$ln = __ROOT__.'/images/logo.png';
						unlink($ln);
						$r = @rename($l, $ln);
						if(!$r)echo '<div class="alert alert-danger">Failed to copy file</div>';
						else echo '<div class="alert alert-success">Logo successfully saved. Make a reload of this page to view new logo.</div>';	
						@unlink($l);
					}	
				}
			}	
		}
		else echo '<div class="alert alert-danger">File upload failed</div>';
	}
	else echo '<div class="alert alert-danger">Admin actions are disabled in demo</div>';
}
?>
<div class="row">
	<div class="col-lg-12 text-center">
    	<h3>Welcome to Admin Panel</h3><br/>
        <a class="btn btn-info" href="<?php echo makeuri('admin.php?module=users');?>"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;Manage Users</a>&nbsp;&nbsp;
        <a class="btn btn-danger" href="<?php echo makeuri('admin.php?module=schedules');?>"><i class="glyphicon glyphicon-time"></i>&nbsp;&nbsp;Manage Schedules</a>&nbsp;&nbsp;
        <a class="btn btn-success" href="<?php echo makeuri('admin.php?module=accounts');?>"><i class="glyphicon glyphicon-list"></i>&nbsp;&nbsp;Manage Accounts</a>&nbsp;&nbsp;
        <a class="btn btn-warning" href="<?php echo makeuri('admin.php?module=folders');?>"><i class="glyphicon glyphicon-folder-open"></i>&nbsp;&nbsp;Manage Folders</a>&nbsp;&nbsp;
        <a class="btn btn-default" href="<?php echo makeuri('admin.php?module=files');?>"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp;Manage Files</a>
        <button class="btn btn-info" onclick="$('.add-user-modal').modal()"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;Add New User</button><br/><br/>
        <a class="btn btn-warning" href="<?php echo makeuri('admin.php?module=plan');?>"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;Membership Plans</a>&nbsp;&nbsp;
        <button class="btn btn-danger add_cron"><i class="glyphicon glyphicon-time"></i>&nbsp;&nbsp;Add Cron Tasks</button>&nbsp;&nbsp;
        <button class="btn btn-success remove_cron"><i class="glyphicon glyphicon-time"></i>&nbsp;&nbsp;Remove Cron Tasks</button>
    </div>
</div>
<br/>
<div class="row">
	<div class="col-lg-12">
    	<h3 class="text-center">Site Configurations</h3>
        <form method="post">
    	<table class="table">
    	<?php
			$q = sql_query("SELECT * FROM global_config LIMIT 1");
			$res = sql_fetch_assoc($q);
			foreach($res as $k => $v){
				if($k == 'site_theme')echo '<tr><td>'.$k.'</td><td><select name="'.$k.'" class="form-control">'.list_themes(1).'</select></td></tr>';
				else if(preg_match('/(disable|enable|seo)/', $k, $m)){
						echo  '<tr><td>'.$k.'</td><td>
								<select class="form-control" name="'.$k.'">
							   	<option value="1" '.($v ? 'selected="selected"' : '').'>'.($m[1] == 'disable' ? 'Yes' : 'Enabled').'</option>
								<option value="0" '.(!$v ? 'selected="selected"' : '').'>'.($m[1] == 'disable' ? 'No' : 'Disable').'</option>
							   </select>
							   </td></tr>';
				}
				else echo '<tr><td>'.$k.'</td><td><input class="form-control" name="'.$k.'" value="'.purify_text($v).'"/></td></tr>';	
			}
		?>
        <tr><td></td><td><button class="btn btn-info">Save Configurations</button></td></tr>
        </table>
        <input type="hidden" name="site_config_save" value="1">
        </form>
        <br/>
        <small>* If you want users use their own apps, leave the app id and secret fields empty.</small><br/>
        <small>* To disable video and audio services, put ffmpeg field blank.</small>
    </div>
</div><br/><br/>
<hr/>
<div class="row">
	<div class="col-lg-12">
    	<h3 class="text-center">Customize site</h3>
        <form enctype="multipart/form-data" method="post">
        	<label>Change logo (80x80 png)</label>
        	<input type="file" name="logo" />
            <input type="hidden" name="upload_logo" value="1" /><br/><br/>
            <button class="btn btn-success">Upload</button>
        </form><br/><br/>
        <small>* To replace help file, create your own <b>help.php</b> file in social ninja directory</small><br/>
        <small>* If you want to use same theme as the main site in help file, use <b>sample-help.php</b> file included inside social ninja</small>
    </div>
</div>
<hr/>
<br/>
<div class="row">
	<div class="col-lg-12">
    	<h3 class="text-center">Cron Task Status</h3>
        <hr/>
        <div class="row">
        	<div class="col-lg-9">
            Post Scheduler Cron
            </div>
            <div class="col-lg-3" style="color:green; font-size:18px">
				<?php
                $l = dirname(dirname(__FILE__)).'/logs/lock-pcron.dat';
                $l = get_lock_status($l);
                if($l == -1)echo 'Never executed';
                else if($l == 0)echo 'Running';
                else echo $l;
                ?>
            </div>
        </div>
        <div class="row">
        	<div class="col-lg-9">
            Insights Cron
            </div>
            <div class="col-lg-3" style="color:green; font-size:18px">
				<?php
                $l = dirname(dirname(__FILE__)).'/logs/lock-scron.dat';
                $l = get_lock_status($l);
                if($l == -1)echo 'Never executed';
                else if($l == 0)echo 'Running';
                else echo $l;
                ?>
            </div>
        </div>
        <div class="row">
        	<div class="col-lg-9">
            Post Hide/Delete Cron
            </div>
            <div class="col-lg-3" style="color:green; font-size:18px">
				<?php
                $l = dirname(dirname(__FILE__)).'/logs/lock-hcron.dat';
                $l = get_lock_status($l);
                if($l == -1)echo 'Never executed';
                else if($l == 0)echo 'Running';
                else echo $l;
                ?>
            </div>
        </div>
        <div class="row">
        	<div class="col-lg-9">
            Bumping, Video Editing, Misc Cron
            </div>
            <div class="col-lg-3" style="color:green; font-size:18px">
				<?php
                $l = dirname(dirname(__FILE__)).'/logs/lock-msccron.dat';
                $l = get_lock_status($l);
                if($l == -1)echo 'Never executed';
                else if($l == 0)echo 'Running';
                else echo $l;
                ?>
            </div>
        </div>
        <br/>
        <small>* If a cron task is not running for a long time, check cron tab and server error logs.</small><br/>
    </div>
</div>


<div class="modal cron-notice">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Cron Setup</h4>
      </div>
      <div class="modal-body">  
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script>$('select[name="site_theme"]').val('<?php echo $res['site_theme']?>')</script>