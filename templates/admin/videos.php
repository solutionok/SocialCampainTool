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
    	<h3 class="text-center">Queued videos for editing</h3><br/>
        <form>
        	<input type="hidden" name="module" value="videos">
            <div class="row">
            	<div class="col-lg-4"></div>
            	<div class="col-lg-3">
            		<input type="text" name="uid" value="<?php echo @htmlentities($_GET['uid'])?>" placeholder="Search by owner id" class="form-control">
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

if(!$settings['video_editor_enabled'] || !$settings['media_plugin_enabled']){
	echo '<div class="alert alert-warning">Video editor plugin is disabled</div>';
}
else echo '<div class="alert alert-info">To disable video editor plugin set <b>video_editor_enabled</b> or <b>media_plugin_enabled</b> variables to zero in configuration</div>';

$from = 1;
$rows = 100;
$name = '';
$uid = '';

if(!empty($_GET['from'])){
	$from = (int)$_GET['from'];	
	if($from < 1)$from = 1;
}
if(!empty($_GET['uid']))$uid = sql_real_escape_string($_GET['uid']);

$total = $admin->count_queued_video($uid);

if(!$total){
	echo '<div class="alert alert-info">No queued video found!</div>';
}
else{
	$videos = $admin->list_queued_video($from, $rows, $uid);
	echo '<h4>Total '.$total.' videos created</h4>';
	echo '<table class="table">';
	echo '<tr>
			<th>Owned By</th>
			<th>Video</th>
			<th>Task</th>
			<th>Segments</th>
			<th>Download</th>
			<th>Status</th>
			<th>Added at</th>
			<th width="150px">Action</th>
		</tr>';
		
	foreach($videos as $video){
		
		$vurl = $video['video_file'] ? '<a href="'.site_url().'/storage/'.$video['storage'].'/'.basename($video['video_file']).'">'.basename($video['video_file']).'</a>' : 'N/A';
		$durl = $video['download_file'] ? '<a href="'.site_url().'/storage/'.$video['storage'].'/'.basename($video['download_file']).'">'.basename($video['download_file']).'</a>' : 'N/A';
		
		echo '<tr rel="'.$video['queue_id'].'">
				<td>User#'.$video['user_id'].'<br/>'.$video['email'].'</td>
				<td>'.$vurl.'</td>
				<td><div style="max-width:200px; overflow:auto">'.$video['tasks'].'</div></td>
				<td><div style="max-width:200px; overflow:auto">'.$video['chunks'].'</div></td>
				<td>'.$durl.'</td>
				<td>'.($video['is_done'] == 0 ? '<span class="label label-info">PENDING</span>' : ($video['is_done'] == 1 ? '<span class="label label-info">DONE</span>' : '<span class="label label-danger">FAILED</span>')).'<br/><br/>'.($video['is_done'] == 0 ? ($video['is_locked'] == 1 ? '<span class="label label-success">PROCESSING</span>' : '') : '').'</td>
				<td>'.get_formatted_time($video['added_at']).'</td>
				<td>
					<button class="btn btn-primary btn-sm adm_vq_delete">Delete</button>
				</td>
			</tr>';
	}
	echo '</table>';
	echo pagination($total, $rows, $from, http_build_query($_GET), makeuri('admin.php?module=videos'));	
}
?>


