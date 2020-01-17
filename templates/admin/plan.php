<?php
/**
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
$pmodule = dirname(__FILE__).'/pmodule.php';
$pmodule_ok = file_exists($pmodule);
?>

<div class="row">
	<div class="col-lg-12 text-center">
    	<h3>Add new plan</h3><br/>
        <button class="btn btn-danger" onclick="$('.plan-modal').find('.modal-title').html('Add new plan');$('#plan_name').val('');$('#plan_id').val('');$('.plan-modal').modal()">Add new plan</button>&nbsp;&nbsp;
        <button class="btn btn-info" onclick="$('.plan-edit-modal').modal()">Edit existing plan</button><br/><br/>
        <small>
        * To enable a feature put value of 1 in the textbox beside it and to disable put 0 in the textbox beside it 
        </small>
    </div>
</div>
<br/><br/>
<div class="row">
	<div class="col-lg-10">
    	<h3>Added Plans</h3>
        <table class="table">
        <tr><th>ID</th><th>Name</th><th>Actions</th></tr>
        <?php 
		$q = sql_query("SELECT * FROM membership_plans ORDER BY plan_price ASC");
		if(!sql_num_rows($q))echo '<tr><td colspan="10"><div class="alert alert-info">No plan found on database</div></td></tr>';
		else{
			while($res = sql_fetch_assoc($q)){
				echo '<tr>
						<td>#'.$res['plan_id'].'</td>
						<td>'.$res['plan_name'].'</td>
						<td>
							<button class="btn btn-info" onclick="$(\'#choose_plan\').val('.$res['plan_id'].');$(\'.plan_edit\').click()">View or edit</button>&nbsp;&nbsp;
							<button class="btn btn-danger" onclick="$(\'#choose_plan\').val('.$res['plan_id'].');$(\'.plan_delete\').click()">Delete</button>&nbsp;&nbsp;
							'.($pmodule_ok ? '<button class="btn btn-primary plan_customize" rel="'.$res['plan_id'].'">Customize</button>' : '').'
						</td>
					</tr>';	
			}	
		}
		?>
        </table>
    </div>
</div>

<div class="modal plan-edit-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Select a plan to edit</h4>
      </div>
      <div class="modal-body">
	  	<?php
		$p = get_membership_plans();
		if(empty($p)){
			echo '<div class="alert alert-info">No plan added yet!</div>';
		}
		else{
			echo '<select id="choose_plan" class="form-control"><option value="">SELECT ONE</option>';
			echo $p;
			echo '</select>';
			echo '<br/><br/><button class="btn btn-info plan_edit">Edit</button>&nbsp;&nbsp;<button class="btn btn-primary plan_delete">Delete</button>';
		}
		?>
      </div>
    </div>
  </div>
</div>

<div class="modal plan-modal">
  <div class="modal-dialog" style="width:96%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Add new plan</h4>
      </div>
      <div class="modal-body">
      	<?php 
		$cols = get_plan_columns();
		?>
        <div class="row">
            <form id="plan-form">
            <?php 
            $i = 0;
			foreach($cols as $col){
				if(in_array($col['Field'], array('plan_id', 'display_on_site', 'plan_features', 'plan_subtitle', 'is_preferred'))){
					continue;
				}
				else{
					$val = 1;
					switch($col['Field']){
						case "plan_name":
							$val = '';
						break;
						case "plan_price":
							$val = 0;
						break;
						case "plan_price_currency_code":
							$val = 'USD';
						break;
						case "folder_limit":
							$val = 50;
						break;
						case "schedule_limit":
							$val = 100;
						break;
						case "schedule_group_limit":
							$val = 50;
						break;
						case "social_profile_limit_per_site":
							$val = 4;
						break;
						case "page_group_event_limit":
							$val = 100;
						break;	
						case "rss_feed_limit":
							$val = 50;
						break;
						case "allowed_storage":
							$val = 300*1024*1024;
						break;
						case "post_per_day":
							$val = 300;
						break;
						case "facebook_post_per_day":
							$val = 100;
						break;
						case "twitter_post_per_day":
							$val = 100;
						break;
						case "youtube_post_per_day":
							$val = 100;
						break;
					}
					
					$hh = '<input class="form-control" name="'.$col['Field'].'"  id="'.$col['Field'].'" value="'.$val.'"/>';
					if(preg_match('/use_|enable_|enabled/', $col['Field']))
						$hh = '<select class="form-control" name="'.$col['Field'].'"  id="'.$col['Field'].'">
							   	<option value="1" '.($val ? 'selected="selected"' : '').'>Enabled</option>
								<option value="0" '.(!$val ? 'selected="selected"' : '').'>Disable</option>
							   </select>';
					
					if(!$i)echo '<div class="row">';
					echo '<div class="col-lg-2">
							<label>'.$col['Field'].'</label>
							'.$hh.'
						  </div>';
					$i++;
					if($i >= 6){
						echo '</div>';
						$i = 0;
					}
				}
            }
			if($i)echo '</div>';
            ?>
            <br/>
            <input type="hidden" name="save_plan" id="save_plan" value="1"/>
            <input type="hidden" name="plan_id" id="plan_id"/>
            </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary plan-save">Save</button>
      </div>
    </div>
  </div>
</div>

<?php if($pmodule_ok)include($pmodule)?>