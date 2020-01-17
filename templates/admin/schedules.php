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
    	<h3 class="text-center">Schedules</h3>
        <form>
        	<input type="hidden" name="module" value="schedules">
            <div class="row">
            	<div class="col-lg-4">
            		<input type="text" name="name" value="<?php echo @htmlentities($_GET['name'])?>" placeholder="Search by name" class="form-control">
            	</div>
                <div class="col-lg-3">
            		<input type="text" name="uid" value="<?php echo @htmlentities($_GET['uid'])?>" placeholder="Search by user id" class="form-control">
            	</div>
                <div class="col-lg-4">
            		<select name="site" class="form-control">
                    	<option value="">All</option>
                    	<option value="fbprofile">Facebook</option>
                        <option value="fbpage">Facebook Page</option>
                        <option value="fbgroup">Facebook Group</option>
                        <option value="fbevent">Facebook Event</option>
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
$uid = '';
$site = '';
$name = '';

if(!empty($_GET['from'])){
	$from = (int)$_GET['from'];	
	if($from < 1)$from = 1;
}
if(!empty($_GET['uid']))$uid = sql_real_escape_string($_GET['uid']);
if(!empty($_GET['name']))$name = sql_real_escape_string($_GET['name']);
if(!empty($_GET['site']))$site = sql_real_escape_string($_GET['site']);

$total = $admin->count_schedules($site, $uid, $name);

if(!$total){
	echo '<div class="alert alert-info">No schedule found!</div>';
}
else{
	list($table, $col, $idcol, $uname) = get_site_params($site);
	$schs = $admin->list_schedules($from, $rows, $site, $uid, $name);
	
	echo '<h4>Total '.$total.' '.$site.' schedule found</h4>';
	echo '<table class="table">';
	echo '<tr>
			<th>UserID</th>
			<th>Name</th>
			<th>Attached IDs</th>
			<th>Frequency</th>
			<th>Next Post</th>
			<th>Last Post</th>
			<th>Status</th>
			<th width="200px">Action</th>
		</tr>';
		
	foreach($schs as $sch){
		$slink = get_profile_url($sch['social_id'], $sch['social_id'], $site);
		$plink = get_profile_url($sch['page_id'], $sch['page_id'], $site);
		$last_post = sql_fetch_assoc(sql_query("SELECT posted_at, post_id FROM post_log WHERE schedule_id = '".$sch['schedule_id']."' ORDER BY posted_at DESC LIMIT 1"));
		if(!empty($last_post)){
			$last_post_at = get_formatted_time($last_post['posted_at']);
			$link = get_link_from_post_id($last_post['post_id'], $site);
			
			$last_post_link = '<a href="'.$link.'" target="_blank">'.$last_post['post_id'].'</a>';
		}
		else{
			$last_post_at = 'N/A';	
			$last_post_link = '';
		}
		
		echo '<tr rel="'.$sch['schedule_id'].'">
				<td>'.$sch['user_id'].'</td>
				<td>'.$sch['schedule_group_name'].'<br/>Site: '.$sch['site'].'</td>
				<td>
					Owner: <a href="'.$slink.'" target="_blank">'.$sch['social_id'].'</a><br/>
					Page: <a href="'.$plink.'" target="_blank">'.$sch['page_id'].'</a>
				</td>
				<td>
					Post Each '.$sch['post_freq'].' '.$sch['post_freq_type'].'<br/>
					Delete Each '.$sch['post_delete_freq'].' '.$sch['post_delete_freq_type'].'
				</td>
				<td>
					'.get_formatted_time($sch['next_post']).'<br/>
					Last lock: '.get_formatted_time($sch['locked_at']).'
				</td>
				<td>
					'.$last_post_at.'<br/>
					'.$last_post_link.'
				</td>
				<td>
					Done: '.($sch['is_done'] == 1 ? 'Yes' : 'No').'<br/>
					Bumping: '.($sch['comment_bumping_freq'] ? 'Yes' : 'No').'<br/>
					Watermark: '.($sch['watermark'] ? 'Yes' : 'No').'<br/>
					Suspended: '.($sch['is_active'] <= 1 ? 'No' : 'Yes').'
				</td>
				<td>
					<a class="btn btn-info btn-sm" href="'.makeuri('post_log.php?gid='.$sch['schedule_group_id']).'" target="_blank">Post Log</a><br/><br/>
					'.($sch['is_active'] <= 1 ? '<button class="btn btn-primary btn-sm adm_sch_ban">Ban</button>' : '<button class="btn btn-primary btn-sm adm_sch_unban">Unban</button>').'&nbsp;&nbsp;
					<button class="btn btn-danger btn-sm adm_sch_delete">Delete</button>
				</td>
			</tr>';
	}
	echo '</table>';
	echo pagination($total, $rows, $from, http_build_query($_GET), makeuri('admin.php?module=schedules'));	
}
?>

<?php if(!empty($_GET['site'])){?>
<script>$('select[name="site"]').val('<?php echo htmlentities($_GET['site'])?>')</script>
<?php }?>