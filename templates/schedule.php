<?php
/**
 * @package Social Ninja
 * @version 1.5
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
	<div class="col-lg-12">
    	<h3><?php echo $schedule_group['schedule_group_name']?></h3>
        <div class="pull-right" style="margin-top:-45px">
            <form>
                <div class="row">
                    <div class="col-lg-8">
                        <select name="site" class="form-control">
                            <option value=""><?php echo $lang['sch'][0]?></option>
                            <option value="fbprofile">Facebook profile</option>
                            <option value="fbpage">Faceboook page</option>
                            <option value="fbgroup">Facebook group</option>
                            <option value="twitter">Twitter</option>
                            <option value="youtube">Youtube</option>
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <button class="btn btn-info"><?php echo $lang['common'][36]?></button>
                    </div>
                </div>
                <input type="hidden" name="gid" value="<?php echo purify_text($_GET['gid'])?>" />
            </form>
        </div>
    </div>
</div>
<br/>
<div class="row">
	<div class="col-lg-12">
    <?php if(!$total_schedules){?>
        <div class="alert alert-danger no-file-error"><?php echo $lang['sch'][1]?></div>
    <?php }else{?>
    <?php if(empty($schedules)){?>
        <div class="alert alert-danger no-file-error"><?php echo $lang['sch'][1]?></div>
    <?php }else{?>
        
        <?php
		echo '<h4>
				'.$total_schedules.' '.$lang['sch'][2].'
			</h4>
			<button class="btn btn-sm btn-default sel_all" rel="schedules">'.$lang['js']['select_all'].'</button>&nbsp;&nbsp;
			<button class="btn btn-sm btn-primary inv_sel" rel="schedules">'.$lang['js']['inv_selected'].'</button>&nbsp;&nbsp;
			<button class="btn btn-sm btn-danger del_selected" rel="schedules">'.$lang['js']['del_selected'].'</button>&nbsp;&nbsp;
			<button class="btn btn-sm btn-success resume_selected" rel="schedules">'.$lang['js']['resume'].'</button>&nbsp;&nbsp;
			<button class="btn btn-sm btn-info stop_selected" rel="schedules">'.$lang['js']['stop'].'</button><br/><br/>';
			
		echo '<table class="table">';
		echo '<tr>
		      	<th>'.$lang['sch']['table'][0].'</th>
				<th>'.$lang['sch']['table'][1].'</th>
				<th>'.$lang['sch']['table'][2].'</th>
				<th>'.$lang['sch']['table'][3].'</th>
				<th>'.$lang['sch']['table'][4].'</th>
				<th width="200px">'.$lang['sch']['table'][5].'</th>
			  </tr>';
		foreach($schedules as $schedule){
			$site = $schedule['site'];
			list($table, $col, $uname, $name) = get_site_params($site);
			$page_data = sql_fetch_assoc(sql_query("SELECT * FROM $table WHERE $col = '".$schedule['page_id']."'"));
			$last_post = sql_fetch_assoc(sql_query("SELECT posted_at, post_id FROM post_log WHERE schedule_id = '".$schedule['schedule_id']."' ORDER BY posted_at DESC LIMIT 1"));
			if(!empty($last_post)){
				$last_post_at = get_formatted_time($last_post['posted_at']);
				$link = get_link_from_post_id($last_post['post_id'], $site);
				$last_post_link = '<a href="'.$link.'" target="_blank">'.$last_post['post_id'].'</a>';
				if( $last_post['post_id'] == - 1 ) $last_post_link = '';
			}
			else{
				$last_post_at = 'N/A';	
				$last_post_link = '';
			}
			
			$status = '';
			if($schedule['is_active'] == 1){
				$status = '<span class="label label-success">'.$lang['js']['active'].'</span>';
				$status_button = '<button class="btn btn-sm btn-success stop_schedule">'.$lang['js']['stop'].'</button>';
			}
			else if($schedule['is_active'] == 2){
				$status = '<span class="label label-danger">'.$lang['js']['suspended'].'</span>';
				$status_button = '';
			}
			else if($schedule['is_active'] == 0){
				$status = '<span class="label label-danger">'.$lang['js']['stopped'].'</span>';
				$status_button = '<button class="btn btn-sm btn-success resume_schedule">'.$lang['js']['resume'].'</button>';
			}
			else if($schedule['is_active'] == 3){
				$status = '<span class="label label-info">'.$lang['js']['waiting'].'</span>';
				$status_button = '<button class="btn btn-sm btn-success resume_schedule">'.$lang['js']['resume'].'</button>';
			}
			
        	echo '<tr rel="'.$schedule['schedule_id'].'" rel-site="'.$schedule['site'].'">
					<td>
						<input type="checkbox" class="schedule-checkbox">&nbsp;&nbsp;
						<a href="'.get_link_from_id($page_data[$uname], $site).'" target="_blank">'.$page_data[$name].'</a><br/>
						<span class="label label-info">'.$site.'</span>
					</td>
					<td>
						'.(
							!$schedule['is_done'] ? 
								(
									$schedule['is_locked'] ? 
									'<span class="label label-info">'.$lang['js']['processing'].'</span>' 
									: 
									'<span class="label label-info">'.$lang['js']['pending'].'</span>'
								)
								: 
								'<span class="label label-warning">'.$lang['js']['done'].'</span>'
							).
							'<br/><br/>
						'.$status.'
					</td>
					<td>
						<span class="time">'.($n = get_formatted_time($schedule['next_post'])).'</span>&nbsp;&nbsp;
						'.( $n == 'N/A' ? '' : '<i class="glyphicon glyphicon-edit pointer edit_sch_time"></i>' ).'
						<input type="text" class="sch_time form-control edit_sch_submit medium-input2" rel="sch" 
							value="'.get_formatted_time($schedule['next_post'], 0, 2).'" style="display:none"/>
					</td>
					<td>'.$last_post_at.'<br/>'.$last_post_link.'</td>
					<td>'.($schedule['rate_limited'] ? '<span style="color:red; font-weight:bold">Rate limited till<br/>'.get_formatted_time($schedule['rate_limited_at'], 3600).'</span><br/>' : 'N/A<br/>').($schedule['notes'] ? 'NOTE: '.$schedule['notes'] : '').'</td>
					<td>
						<button class="btn btn-sm btn-info del_schedule">'.$lang['js']['delete'].'</button>&nbsp;&nbsp;
						'.$status_button.'<br/><br/>
						<button class="btn btn-sm btn-primary show_adv_settings">'.$lang['js']['options'].'</button>
						&nbsp;&nbsp;<a href="post_log.php?sid='.$schedule['schedule_id'].'" class="btn btn-sm btn-danger">'.$lang['js']['view_posts'].'</a>
					</td>
				  </tr>';
		}
		echo '</table>';
        ?>
    <?php }
		echo '<hr />';
		$g = $_GET;
		echo pagination($total_schedules, $rows, $from, http_build_query($g), makeuri('schedule.php'));
	}?>
	</div>
</div>

<div class="modal adv-settings">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][27]?></h4>
      </div>
      <div class="modal-body">
      	<form id="adv_settings_form">
      	<div class="row">
        	<div class="col-lg-6">
                <div class="form-group">
                    <label><?php echo $lang['modals'][28]?></label>
                    <div class="row">
                    	<div class="col-lg-6">
                            <select class="form-control" id="stats_name">
                                <option value=""><?php echo $lang['modals'][29]?></option>
                                <option value="Likes" class="fb_stats">Facebook Likes</option>
                                <option value="Comments" class="fb_stats">Facebook Comments</option>
                                <option value="Views" class="fb_stats">Facebook Views [Pages only]</option>
                                <option value="Negative_Feedback" class="fb_stats">Facebook Negative Feedback [Pages only]</option>
                                
                                <option value="Favorites" class="tw_stats">Twitter Favorites</option>
                                <option value="Retweet" class="tw_stats">Twitter Retweet</option>
                                
                                <option value="Views" class="yt_stats">Youtube Views</option>
                                <option value="Likes" class="yt_stats">Youtube Likes</option>
                                <option value="Dislikes" class="yt_stats">Youtube Dislikes</option>
                                <option value="Favorites" class="yt_stats">Youtube Favorites</option>
                            
                            </select>
                    	</div>
                        <div class="col-lg-6">
                            <select class="form-control" id="stats_operator">
                                <option value=""><?php echo $lang['modals'][30]?></option>
                                <option value="below">IS BELOW OR EQUAL</option>
                                <option value="above">IS ABOVE OR EQUAL</option>
                            </select>
                    	</div>
                    </div>
                    <div class="row" style="margin-top:10px">     
                        <div class="col-lg-6">
                   			<input type="text" class="form-control" id="stats_amount" placeholder="Type number"/>
                    	</div>
                        <div class="col-lg-6">
                            <select class="form-control" id="stats_time">
                                <option value=""><?php echo $lang['modals'][31]?></option>
                                <option value="3">IN 3 HOURS</option>
                                <option value="6">IN 6 HOURS</option>
                                <option value="9">IN 9 HOURS</option>
                                <option value="12">IN 12 HOURS</option>
                                <option value="18">IN 18 HOURS</option>
                                <option value="24">IN 24 HOURS</option>
                                <option value="48">IN 48 HOURS</option>
                                <option value="72">IN 72 HOURS</option>
                                <option value="96">IN 96 HOURS</option>
                            </select>
                    	</div>
                    </div>
                    <div class="row" style="margin-top:10px"> 
                    	<div class="col-lg-3">
                    		<button class="btn btn-sm btn-info stats_ctrl_add"><?php echo $lang['common'][46]?></button>
                    	</div>
                    </div>
                    <div class="row" style="margin-top:10px"> 
                    	<div class="col-lg-12 stats_ctrl" style="height:150px; overflow:auto">
                    		
                    	</div>
                    </div>
                </div>
        	</div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label><?php echo $lang['modals'][32]?></label>
                    <textarea class="form-control" name="comments" style="height:150px" placeholder="<?php echo $lang['modals'][33]?>"></textarea>
                    <label><?php echo $lang['modals'][34]?></label>
                    <select name="comment_delay" class="form-control">
                    	<option value=""><?php echo $lang['common'][13]?></option>
                        <option value="disable"><?php echo $lang['modals'][35]?></option>
                    	<option value="1-4"><?php echo $lang['modals'][36]?> 1 to 4 hours</option>
                        <option value="4-8"><?php echo $lang['modals'][36]?> 4 to 8 hours</option>
                        <option value="8-12"><?php echo $lang['modals'][36]?> 8 to 12 hours</option>
                        <option value="12-18"><?php echo $lang['modals'][36]?> 12 to 18 hours</option>
                        <option value="18-24"><?php echo $lang['modals'][36]?> 18 to 24 hours</option>
                        <option value="24-48"><?php echo $lang['modals'][36]?> 24 to 48 hours</option>
                        <option value="48-72"><?php echo $lang['modals'][36]?> 48 to 72 hours</option>
                    </select>
                    <label><?php echo $lang['modals'][37]?></label>
                    <select name="bump_type" class="form-control">
                    	<option value="onetime"><?php echo $lang['modals'][38]?></option>
                        <option value="repeat"><?php echo $lang['modals'][39]?></option>
                    </select>
                </div>
        	</div>
        </div>
        <input type="hidden" name="adv_settings_sch_id" id="adv_settings_sch_id" value="" />
        </form>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-sm btn-danger schedule_stats_remove"><?php echo $lang['modals'][40]?></button>
        <button type="button" class="btn btn-sm btn-warning schedule_reset"><?php echo $lang['modals'][41]?></button>
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary adv_settings_save"><?php echo $lang['common'][20]?></button>
      </div>
    </div>
  </div>
</div>

<?php if(!empty($_GET['site'])){?>
<script>$('select[name="site"]').val('<?php echo purify_text($_GET['site'])?>')</script>
<?php }?>
