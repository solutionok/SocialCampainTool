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
    	<h3 class="text-center">All folders</h3>
        <form>
        	<input type="hidden" name="module" value="folders">
            <div class="row">
            	<div class="col-lg-6">
            		<input type="text" name="uid" value="<?php echo @htmlentities($_GET['uid'])?>" placeholder="Search by owner id" class="form-control">
            	</div>
                <div class="col-lg-5">
            		<input type="text" name="name" value="<?php echo @htmlentities($_GET['name'])?>" placeholder="Search by folder name" class="form-control">
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
$name = '';
$uid = '';

if(!empty($_GET['from'])){
	$from = (int)$_GET['from'];	
	if($from < 1)$from = 1;
}
if(!empty($_GET['uid']))$uid = sql_real_escape_string($_GET['uid']);
if(!empty($_GET['name']))$name = sql_real_escape_string($_GET['name']);

$total = $admin->count_folders($uid, $name);

if(!$total){
	echo '<div class="alert alert-info">No folder found!</div>';
}
else{
	$folders = $admin->list_folders($from, $rows, $uid, $name);
	echo '<h4>Total '.$total.' folders created</h4>';
	echo '<table class="table">';
	echo '<tr>
			<th>Folder Name</th>
			<th>Owned By</th>
			<th>File Count</th>
			<th>Last Update</th>
			<th width="150px">Action</th>
		</tr>';
		
	foreach($folders as $folder){
		echo '<tr rel="'.$folder['folder_id'].'">
				<td>'.$folder['folder_name'].'</td>
				<td>'.$folder['email'].'<br/>User#'.$folder['user_id'].'</td>
				<td>'.$folder['file_count'].'</td>
				<td>'.get_formatted_time($folder['updated_at']).'</td>
				<td>
					<a class="btn btn-info btn-sm" href="'.makeuri('admin.php?module=files&folder_id='.$folder['folder_id']).'" target="_blank">View</a>&nbsp;&nbsp;
					<button class="btn btn-primary btn-sm adm_folder_delete">Delete</button>
				</td>
			</tr>';
	}
	echo '</table>';
	echo pagination($total, $rows, $from, http_build_query($_GET), makeuri('admin.php?module=folders'));	
}
?>


