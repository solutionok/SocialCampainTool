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
    	<h3 class="text-center">All files</h3>
        <form>
        	<input type="hidden" name="module" value="files">
            <div class="row">
            	<div class="col-lg-4">
            		<input type="text" name="uid" value="<?php echo @htmlentities($_GET['uid'])?>" placeholder="Search by owner id" class="form-control">
            	</div>
                <div class="col-lg-3">
            		<input type="text" name="folder_id" value="<?php echo @htmlentities($_GET['folder_id'])?>" placeholder="Search by folder id" class="form-control">
            	</div>
                 <div class="col-lg-4">
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
$folder_id = '';
$uid = '';
$name = '';

if(!empty($_GET['from'])){
	$from = (int)$_GET['from'];	
	if($from < 1)$from = 1;
}
if(!empty($_GET['uid']))$uid = sql_real_escape_string($_GET['uid']);
if(!empty($_GET['folder_id']))$folder_id = sql_real_escape_string($_GET['folder_id']);
if(!empty($_GET['name']))$name = sql_real_escape_string($_GET['name']);

$total = $admin->count_files($uid, $folder_id, $name);

if(!$total){
	echo '<div class="alert alert-info">No file found!</div>';
}
else{
	$files = $admin->list_files($from, $rows, $uid, $folder_id, $name);
	echo '<h4>Total '.$total.' files created</h4>';
	echo '<table class="table">';
	echo '<tr>
			<th>Folder Name</th>
			<th>Owned By</th>
			<th>File Name</th>
			<th>Caption</th>
			<th>Description</th>
			<th>Category</th>
			<th>Type</th>
			<th width="150px">Action</th>
		</tr>';
	foreach($files as $file){
		$path = site_url().'/storage/'.$file['storage'].'/'.$file['filename'];
		echo '<tr rel="'.$file['file_id'].'">
				<td>'.$file['folder_name'].'<br/>By User#'.$file['user_id'].'</td>
				<td>'.$file['email'].'<br/>'.get_formatted_time($file['added_at']).'</td>
				<td>
					<div style="width:200px;overflow:hidden">
						'.$file['filename'].'<br/>'.$file['original_name'].'<br/>
						'.($file['file_type'] == 'video' ? '<img src="'.$path.'.png" style="max-width:200px">' : ($file['file_type'] == 'image' ? '<img src="'.$path.'" style="max-width:200px">' : '')).'
					</div>
				</td>
				<td><div style="width:200px;overflow:hidden">'.$file['caption'].'</div></td>
				<td><div style="width:200px;overflow:hidden">'.$file['description'].'</div></td>
				<td>'.$file['category'].'<br/>'.$file['tags'].'</td>
				<td>'.$file['file_type'].'</td>
				<td>
					<a class="btn btn-info btn-sm" href="'.$path.'" target="_blank">View</a>&nbsp;&nbsp;
					<button class="btn btn-primary btn-sm adm_file_delete">Delete</button>
				</td>
			</tr>';
	}
	echo '</table>';
	echo pagination($total, $rows, $from, http_build_query($_GET), makeuri('admin.php?module=files'));	
}
?>


