<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
	<div class="col-lg-12">
    	<h3 class="text-center"><?php echo $lang['editor']['videos']['qv']?></h3>
    	
        <?php
		$total = $auth->count_user_queued_videos($user_id);
		if(!$total)echo '<div class="alert alert-danger">'.$lang['editor']['videos']['novid'].'</div>';
		else{
			$from = 1;
			$rows = 100;
			if(!empty($_GET['from'])){
				$from = (int)$_GET['from'];	
				if($from < 1)$from = 1;
			}
			$videos = $auth->get_user_queued_videos($user_id, $from, $rows);
			
			if(!empty($videos)){
				echo '<table class="table">';
				echo '<tr><th>#</th><th>'.$lang['editor']['videos']['table'][0].'</th><th>'.$lang['editor']['videos']['table'][1].'</th><th>'.$lang['editor']['videos']['table'][2].'</th><th>'.$lang['editor']['videos']['table'][3].'</th><th>'.$lang['editor']['videos']['table'][4].'</th><th>'.$lang['editor']['videos']['table'][5].'</th></tr>';
				foreach($videos as $video){
					echo '<tr rel="'.$video['queue_id'].'">
							<td>'.$video['queue_id'].'</td>
							<td>'.$video['title'].'</td>
							<td>'.get_formatted_time($video['added_at']).'</td>
							<td>'.($video['is_done'] == 0 ? '<span class="label label-info">'.$lang['js']['pending'].'</span>' : ($video['is_done'] == 1 ? '<span class="label label-info">'.$lang['js']['done'].'</span>' : '<span class="label label-danger">'.$lang['js']['failed'].'</span>')).'<br/><br/>'.($video['is_done'] == 0 ? ($video['is_locked'] == 1 ? '<span class="label label-success">'.$lang['js']['processing'].'</span>' : '') : '').'</td>
							<td>'.($video['notes'] ? $video['notes'] : 'N/A').'</td>
							<td>'.($video['download_file'] ? '<a href="'.site_url().'/storage/'.$user_data['storage'].'/'.$video['download_file'].'">'.$video['download_file'].'</a>'  : 'N/A').'</td>
							<td><button class="btn btn-sm btn-primary vq_delete">'.$lang['js']['delete'].'</button></td>
						 </tr>';
				}
				echo '</table>';
				echo pagination($total, $rows, $from, http_build_query($_GET), makeuri('plugins/media/videos.php'));
			}
			else echo '<div class="alert alert-danger">'.$lang['editor']['videos']['fetch_fail'].'</div>';
		}
		?>
    
    </div>
</div>