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
	<div class="col-lg-12">
    	<h3 class="text-center">Facebook Pages, Groups & Events</h3>
        <form>
        	<input type="hidden" name="module" value="pages">
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
                    	<option value="fbpage">Facebook Page</option>
                        <option value="fbgroup">Facebook Group</option>
                        <option value="fbevent">Facebook Event</option>
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
$site = 'fbpage';
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
	echo '<h4>Total '.$total.' '.$site.' found</h4>';
	echo '<table class="table">';
	echo '<tr>
			<th>Picture</th>
			<th>Name</th>
			<th>Added By</th>
			<th>Action</th>
		</tr>';
		
	foreach($accs as $acc){
		$acc['profile_pic'] = 'https://graph.facebook.com/'.$acc[$col].'/picture?type=';
		$pic = get_medium_pp($acc['profile_pic'], $site);
		$url = get_profile_url($acc[$col], @$acc[$uname], $site);
		$owner_url = get_profile_url($acc['fb_id'], '', 'fbprofile');
		
		echo '<tr rel="'.$acc[$col].'" rel-site="'.$site.'" rel-uid="'.$acc['user_id'].'" rel-s="'.$acc[$col].'|'.$acc['user_id'].'">
				<td><img src="'.$pic.'" style="max-width:150px !important"/></td>
				<td>
					'.$acc[$uname].'<br/>
					ID: '.$acc[$col].'
				</td>
				<td>
					User #'.$acc['user_id'].'<br/>
					Facebook: <a href="'.$owner_url.'" target="_blank">'.$acc['fb_id'].'</a>
				</td>
				<td>
					<a class="btn btn-info btn-sm" href="'.$url.'" target="_blank">View Profile</a><br/><br/>
					'.($acc['account_status'] == 1 ? '<button class="btn btn-primary btn-sm adm_profile_ban">Ban</button>' : '<button class="btn btn-primary btn-sm adm_profile_unban">Unban</button>').'&nbsp;&nbsp;
					<button class="btn btn-danger btn-sm adm_profile_delete">Delete</button>
				</td>
			</tr>';
	}
	echo '</table>';
	echo pagination($total, $rows, $from, http_build_query($_GET), makeuri('admin.php?module=pages'));	
}
?>

<?php if(!empty($_GET['site'])){?>
<script>$('select[name="site"]').val('<?php echo htmlentities($_GET['site'])?>')</script>
<?php }?>
<?php if(!empty($_GET['banned'])){?>
<script>$('select[name="banned"]').val('<?php echo htmlentities($_GET['banned'])?>')</script>
<?php }?>