<?php
/**
 * @package Social Ninja
 * @version 1.1
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
	<div class="col-lg-12">
    	<h3 class="text-center">Users registered on this site</h3><br/>
        <form>
        	<input type="hidden" name="module" value="users">
            <div class="row">
            	<div class="col-lg-2">
            		<input type="text" name="uid" value="<?php echo @htmlentities($_GET['uid'])?>" placeholder="Search by id" class="form-control">
            	</div>
                <div class="col-lg-2">
            		<input type="text" name="email" value="<?php echo @htmlentities($_GET['email'])?>" placeholder="Search by email" class="form-control">
            	</div>
                <div class="col-lg-2">
            		<input type="checkbox" name="show_admins" <?php if(!empty($_GET['show_admins'])){?>checked="checked"<?php }?> >
                    &nbsp;&nbsp;Show admins
            	</div>
                 <div class="col-lg-2">
            		<select name="banned" class="form-control">
                    	<option value="0">View all</option>
                        <option value="1">View banned profiles</option>
                    </select>
            	</div>
                <div class="col-lg-1">
	                <button class="btn btn-info">Search</button>
                </div>
                <div class="col-lg-1">
                	<button class="btn btn-info" onclick="$('.add-user-modal').modal(); return false;"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;Add New User</button>
                </div>
            </div>
        </form>
    </div>
</div>
<br/>
<?php
$from = 1;
$rows = 100;
$email = '';
$uid = '';
$show_admin = 0;
$banned = 0;

if(!empty($_GET['from'])){
	$from = (int)$_GET['from'];	
	if($from < 1)$from = 1;
}
if(!empty($_GET['email'])){
	$email = sql_real_escape_string($_GET['email']);
}
if(!empty($_GET['uid'])){
	$uid = sql_real_escape_string($_GET['uid']);
}
if(!empty($_GET['show_admins'])){
	$show_admin = 1;
}
if(!empty($_GET['banned'])){
	$banned = 1;
}
							
$total_users = $admin->count_users($email, $uid, $show_admin, $banned);
if(empty($total_users))echo '<div class="alert alert-info">No user found!</div>';
else{
	echo "<h4>Total $total_users users registered</h4>";
	$users = $admin->list_users($from, $rows, $email, $uid, $show_admin, $banned);
	echo '<table class="table">
			<tr>
				<th>Email</th>
				<th>TimeZone</th>
				<th>Last Login</th>
				<th>Membership</th>
				<th>Limits</th>
				<th>Is Admin</th>
				<th>Account Status</th>
				<th>Limit Posting</th>
				<th>View</th>
			</tr>';
	foreach($users as $user){
		echo '<tr rel="'.$user['user_id'].'">
				<td>
					'.$user['email'].'<br/>
					'.($user['is_admin'] ? ($user['is_admin'] == 1 ? '<span class="label label-info">Super Admin</span><br/>' : '<span class="label label-info">Admin</span><br/>') : '').'<br/>
					<button class="btn btn-danger btn-xs adm_del_user">Delete</button>
				</td>
				<td>
					'.(empty($user['time_zone']) ? 'N/A' : $user['time_zone']).'<br/>
					Theme: '.(empty($user['theme']) ? 'N/A' : $user['theme']).'
				</td>
				<td>
					'.(empty($user['last_login_time']) ? 'N/A' : get_formatted_time($user['last_login_time']).'<br/>From '.$user['last_login_ip']).'
				</td>
				<td>
					<span class="user_membership">'.$user['plan_name'].'</span><br/>
					untill '.($user['plan_id'] == 1 ? 'N/A' : get_formatted_time($user['membership_expiry_time'])).'<br/>
					<button class="btn btn-success btn-xs up_membership">Update</button>
				</td>
				<td>
					'.formatSize($user['used_storage']).' used of <span class="user_storage">'.formatSize($user['allowed_storage']).'</span><br/>
					<span class="ppd">'.$user['post_per_day'].'</span> posts/day <br/>
				</td>
				<td>
					'.($user['is_admin'] ? '<span class="at">Yes</span> <br/><button class="btn btn-primary btn-xs remove_admin">Remove</button>' : '<span class="at">No</span> <br/><button class="btn btn-primary btn-xs add_admin">Promote</button>').'
				</td>
				<td>
					'.($user['account_status'] != 1 ? 
						($user['account_status'] == 3 ? '<span class="bt">Pending</span>' : '<span class="bt">Suspended</span>').
						'<br/><button class="btn btn-primary btn-xs unban_user">Unban</button>' 
						: 
						'<span class="bt">Active</span> <br/><button class="btn btn-primary btn-xs ban_user">Ban</button>').'<br/>
						<button class="btn btn-info btn-xs up_user">Update user</button>
				</td>
				<td>
					Facebook: <span class="fbp">'.($user['fb_posting'] <= 1 ? 'Enabled <button class="btn btn-info btn-xs change_posting" id="disable_fb_posting">Disable</button>' : 'Disabled <button class="btn btn-danger btn-xs change_posting" id="enable_fb_posting">Enable</button>').'</span><br/>
					Twitter: <span class="twp">'.($user['tw_posting'] <= 1 ? 'Enabled <button class="btn btn-info btn-xs change_posting" id="disable_tw_posting">Disable</button>' : 'Disabled <button class="btn btn-danger btn-xs change_posting" id="enable_tw_posting">Enable</button>').'</span><br/>
					Youtube: <span class="ytp">'.($user['yt_posting'] <= 1 ? 'Enabled <button class="btn btn-info btn-xs change_posting" id="disable_yt_posting">Disable</button>' : 'Disabled <button class="btn btn-danger btn-xs change_posting" id="enable_yt_posting">Enable</button>').'</span><br/>
				</td>
				<td>
					<a class="btn btn-primary btn-xs" href="'.makeuri('admin.php?module=folders&uid='.$user['user_id']).'" target="_blank">Folders</a>
					<a class="btn btn-success btn-xs" href="'.makeuri('admin.php?module=files&uid='.$user['user_id']).'" target="_blank">Files</a><br/>
					<a class="btn btn-danger btn-xs" href="'.makeuri('admin.php?module=schedules&uid='.$user['user_id']).'" target="_blank">Schedules</a>
					<a class="btn btn-default btn-xs" href="'.makeuri('admin.php?module=accounts&uid='.$user['user_id']).'" target="_blank">Accounts</a>
				</td>
			  </tr>';
	}
	echo '</table>';
	
	echo pagination($total_users, $rows, $from, http_build_query($_GET), makeuri('admin.php?module=users'));	
}
?>

<input type="hidden" id="c_user" />

<div class="modal up-membership-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Update user membership plan</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
            <?php
			$p = get_membership_plans();
			if(empty($p)){
				echo '<div class="alert alert-info">No plan added yet!</div>';
			}
			else{
				echo '<label>Select a plan</label>';
				echo '<select id="update_user_plan" class="form-control"><option value="">SELECT ONE</option>';
				echo $p;
				echo '</select><br/>';
				echo '<label>Membership expires in days (no expiry for basic plan)</label>';
				echo '<input type="text" id="update_user_plan_exp" value="30" class="form-control"/>';
			}
			?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary up-membership-btn">Save</button>
      </div>
    </div>
  </div>
</div>

<!--
<div class="modal up-storage-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Update user storage</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
          	<label>Enter allowed storage in MegaBytes</label>
            <input class="form-control" id="storage" placeholder="Enter storage in MB" type="text">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary up-storage-btn">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal up-post-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Update user post limit</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
          	<label>Enter allowed posts per day</label>
            <input class="form-control" id="post_per_day" placeholder="Enter allowed post per day" type="text">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary up-post-btn">Save</button>
      </div>
    </div>
  </div>
</div>
-->

<div class="modal add-admin-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Promote to Admin</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
          	<label>Choose admin type</label>
            <select class="form-control" id="alevel">
            	<option value="2">Normal Admin</option>
                <option value="1">Super Admin</option>
            </select>
            <br/>
            <small>*Super admins cannot be removed without database access</small>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary add-admin-btn">Save</button>
      </div>
    </div>
  </div>
</div>

<?php if(!empty($_GET['banned'])){?>
<script>$('select[name="banned"]').val('<?php echo htmlentities($_GET['banned'])?>')</script>
<?php }?>