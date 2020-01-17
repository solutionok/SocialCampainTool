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
    	<h3 class="text-center">Social Accounts</h3>
        <form>
        	<input type="hidden" name="module" value="accounts">
            <div class="row">
            	<div class="col-lg-2">
            		<input type="text" name="id" value="<?php echo @htmlentities($_GET['id'])?>" placeholder="Search by social id" class="form-control">
            	</div>
                <div class="col-lg-2">
            		<input type="text" name="uid" value="<?php echo @htmlentities($_GET['uid'])?>" placeholder="Search by user id" class="form-control">
            	</div>
                <div class="col-lg-2">
            		<input type="text" name="name" value="<?php echo @htmlentities($_GET['name'])?>" placeholder="Search by name" class="form-control">
            	</div>
                 <div class="col-lg-2">
            		<select name="banned" class="form-control">
                    	<option value="0">View all</option>
                        <option value="1">View banned profiles</option>
                    </select>
            	</div>
                <div class="col-lg-3">
            		<select name="site" class="form-control">
                    	<option value="fbprofile">Facebook</option>
                        <option value="twitter">Twitter</option>
                        <option value="youtube">Youtube</option>
                    </select>
            	</div>
                <div class="col-lg-1">
	                <button class="btn btn-info">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>
<br/>
<?php
$from = 1;
$rows = 100;
$id = '';
$uid = '';
$name = '';
$site = 'fbprofile';
$banned = 0;

if(!empty($_GET['from'])){
	$from = (int)$_GET['from'];	
	if($from < 1)$from = 1;
}
if(!empty($_GET['banned'])){
	$banned = 1;
}
if(!empty($_GET['id']))$id = sql_real_escape_string($_GET['id']);
if(!empty($_GET['uid']))$uid = sql_real_escape_string($_GET['uid']);
if(!empty($_GET['name']))$name = sql_real_escape_string($_GET['name']);
if(!empty($_GET['site']))$site = sql_real_escape_string($_GET['site']);

$total = $admin->count_accounts($site, $id, $uid, $name, $banned);

if(!$total){
	echo '<div class="alert alert-info">No account found!</div>';
}
else{
	list($table, $col, $idcol, $uname) = get_site_params($site);
	$accs = $admin->list_accounts($from, $rows, $site, $id, $uid, $name, $banned);
	echo '<h4>Total '.$total.' '.($site == 'fbprofile' ? 'facebook' : $site).' account found</h4>';
	echo '<table class="table">';
	echo '<tr>
			<th>Picture</th>
			<th>Name</th>
			<th>Username</th>
			<th>Last Refresh</th>
			<th width="200px">Action</th>
		</tr>';
		
	foreach($accs as $acc){
		$pic = get_medium_pp($acc['profile_pic'], $site);
		$url = get_profile_url($acc[$col], @$acc[$uname], $site);
		echo '<tr rel="'.$acc[$col].'" rel-site="'.$site.'" rel-uid="'.$acc['user_id'].'" rel-s="'.$acc[$col].'|'.$acc['user_id'].'">
				<td><img src="'.$pic.'" style="max-width:150px !important"/></td>
				<td>'.$acc['first_name'].' '.$acc['last_name'].'</td>
				<td>'.$acc[$uname].'</td>
				<td>'.get_formatted_time($acc['access_token_at']).'</td>
				<td>
					<a class="btn btn-info btn-sm" href="'.$url.'" target="_blank">View Profile</a><br/><br/>
					'.($acc['account_status'] == 1 ? '<button class="btn btn-primary btn-sm adm_profile_ban">Ban</button>' : '<button class="btn btn-primary btn-sm adm_profile_unban">Unban</button>').'&nbsp;&nbsp;
					<button class="btn btn-danger btn-sm adm_profile_delete">Delete</button>
				</td>
			</tr>';
	}
	echo '</table>';
	echo pagination($total, $rows, $from, http_build_query($_GET), makeuri('admin.php?module=accounts'));	
}
?>

<?php if(!empty($_GET['site'])){?>
<script>$('select[name="site"]').val('<?php echo htmlentities($_GET['site'])?>')</script>
<?php }?>
<?php if(!empty($_GET['banned'])){?>
<script>$('select[name="banned"]').val('<?php echo htmlentities($_GET['banned'])?>')</script>
<?php }?>