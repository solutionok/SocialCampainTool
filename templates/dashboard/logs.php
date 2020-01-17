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
        <h3>
            <?php echo $lang['logs']['plog']?>
        </h3>
        <h4><?php echo $lang['logs']['last100']?></h4>
        <?php
        $logs = $auth->get_user_posts($user_id);
        if(!$logs)echo '<div class="alert alert-warning">'.$lang['plog'][2].'</div>';
        else{           
			echo '<table class="table">
			<tr>
				<th>'.$lang['plog']['table_header'][0].'</th>
				<th>'.$lang['plog']['table_header'][1].'</th>
				<th>'.$lang['plog']['table_header'][2].'</th>
				<th>'.$lang['plog']['table_header'][3].'</th>
				<th width="200px">'.$lang['plog']['table_header'][4].'</th>
				<th>'.$lang['plog']['table_header'][5].'</th>
				<th width="250px">'.$lang['plog']['table_header'][6].'</th>
			</tr>';
			
			foreach($logs as $post_log){
				
				if(empty($schedule_id)){
					$site = $post_log['site'];
					
					list($table, $col, $uname, $name) = get_site_params($site);
					$page_data = sql_fetch_assoc(sql_query("SELECT * FROM $table WHERE $col = '".$post_log['page_id']."'"));
					$plink = get_link_from_id($page_data[$uname], $site);
				}
				
				$posted_at = preg_replace('/\s/', '<br/>', get_formatted_time($post_log['posted_at']), 1);
				$link = get_link_from_post_id($post_log['post_id'], $site);
				$delete_at =  preg_replace('/\s/', '<br/>', get_formatted_time($post_log['delete_at']), 1);
				
				$ins = '';
				if(!empty($post_log['insights'])){
					$ii = json_decode(stripslashes($post_log['insights']), true);
					foreach($ii as $k => $v)$ins .= "$k : <b>$v</b><br/>";	
				}
				
				$hid = $delete_at.'&nbsp;&nbsp;
					   '.($delete_at != 'N/A' ? '<span class="label label-success pointer cancel_post_deletion">'.$lang['common'][37].'</span>' : '');
				
				if($post_log['hid_status'] == 1)$hid = '<span class="label label-danger">'.$lang['common'][38].'</span>';
				else if($post_log['hid_status'] == 2)$hid = '<span class="label label-info">'.$lang['common'][39].'</span>';	   
				
				echo '<tr rel="'.$post_log['post_log_id'].'" rel-deleted="'.($post_log['hid_status'] == 1 ? 1 : 0).'">
						<td>
							<a href="'.$plink.'" target="_blank">'.$page_data[$name].'</a><br/>
							<span class="label label-info">'.$post_log['site'].'</span>
						</td>
						<td><a href="'.$link.'" target="_blank">'.$post_log['post_id'].'</a></td>
						<td>'.$posted_at.'</td>
						<td>
							'.$hid.'
						</td>
						<td>
							<small>
								'.$lang['common'][42].': '.get_formatted_time($post_log['next_bump']).'<br/>
								'.$lang['common'][43].': '.get_formatted_time($post_log['last_bump']).'<br/>
								'.$lang['common'][44].': '.$post_log['last_bump_message'].'
							</small>
						</td>
						<td>
							'.$ins.'
							<small>Next refresh '.get_formatted_time($post_log['next_insight']).'</small>
						</td>
						<td>
							<a class="btn btn-sm btn-info" href="'.$link.'" target="_blank"><i class="glyphicon glyphicon-globe"></i>&nbsp;&nbsp;'.$lang['common'][40].'</a>&nbsp;&nbsp;
							<button class="btn btn-sm btn-danger post_log_del"><i class="glyphicon glyphicon-trash"></i>&nbsp;&nbsp;'.$lang['common'][0].'</button><br/><br/>
							'.($post_log['next_bump'] != '0000-00-00 00:00:00' ? '<button class="btn btn-sm btn-primary stop_bumping">'.$lang['common'][41].'</button>' : '').'
						</td>
					  </tr>';									
			}
			echo '</table>';
        }
        ?>
    </div>
</div>
<br/><br/>
<div class="row">
    <div class="col-lg-12">
        <h3>
            <?php echo $lang['logs']['elog']?>
            <?php if($logs){?>
            <div class="pull-right">
            	<button class="btn btn-sm btn-danger clear_logs"><?php echo $lang['ajax']['clear_logs']?></button>
            </div>
            <?php }?>
        </h3>
        <h4>
			<?php echo $lang['logs']['last100']?>
        </h4>
        <?php
        $logs = $auth->get_user_errors($user_id);
        if(!$logs)echo '<div class="alert alert-warning">'.$lang['logs']['noerr'].'</div>';
        else{           
			echo '<table class="table">
			<tr>
				<th>'.$lang['logs']['table_header'][0].'</th>
				<th>'.$lang['logs']['table_header'][1].'</th>
				<th>'.$lang['logs']['table_header'][2].'</th>
				<th>'.$lang['logs']['table_header'][3].'</th>
				<th>'.$lang['logs']['table_header'][4].'</th>
				<th>'.$lang['logs']['table_header'][5].'</th>
			</tr>';
			
			foreach($logs as $post_log){
				
				if(empty($schedule_id)){
					$site = $post_log['site'];
					
					list($table, $col, $uname, $name) = get_site_params($site);
					$page_data = sql_fetch_assoc(sql_query("SELECT * FROM $table WHERE $col = '".$post_log['page_id']."'"));
					$plink = get_link_from_id($page_data[$uname], $site);
				}
				
				$time = preg_replace('/\s/', '<br/>', get_formatted_time($post_log['added_at']), 1);
				
				echo '<tr>
						<td>
							<a href="'.$plink.'" target="_blank">'.$page_data[$name].'</a><br/>
							<span class="label label-info">'.$post_log['site'].'</span>
						</td>
						<td>'.$post_log['post_id'].'</td>
						<td>'.$post_log['file_id'].'</td>
						<td>'.$time.'</td>
						<td>'.$post_log['message'].'</td>
					  </tr>';									
			}
			echo '</table>';
        }
        ?>
    </div>
</div>
<input type="hidden" class="remove_from_site" />