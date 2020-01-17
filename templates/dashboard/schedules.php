<?php
/**
 * @package Social Ninja
 * @version 1.6
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
    <div class="col-lg-12">
        <h3>
            <?php echo $lang['dashboard'][37]?>
            <div class="pull-right">
                <div class="row pull-up">
                    <div class="col-lg-6">
                        <form class="search">
                            <input class="form-control submit-enter" name="q" placeholder="<?php echo $lang['dashboard'][38]?>"
                                value="<?php if(@$_GET['show'] == 'schedules')echo @purify_text($_GET['q'])?>"/>
                            <input type="hidden" name="show" value="schedules" />
                        </form>
                    </div>
                    <div class="col-lg-6">
                        <button class="btn btn-primary open-schedule-modal"><?php echo $lang['dashboard'][39]?></button>
                    </div>
                </div>
            </div>
        </h3>
        <?php
        $schedule_count = $auth->count_user_schedule_groups($user_id);
        if(!$schedule_count)echo '<div class="alert alert-warning">'.$lang['dashboard'][40].'</div>';
        else{
            $from = 1;
            $rows = 25;
            $name = '';
            
            if(!empty($_GET['show']) && $_GET['show'] == 'schedules'){
                if(!empty($_GET['from'])){
                    $from = (int)$_GET['from'];	
                    if($from < 1)$from = 1;
                }
                
                if(!empty($_GET['q'])){
                    $name = sql_real_escape_string($_GET['q']);
                }
                if(!empty($name))$schedule_count = $auth->count_user_schedule_groups($user_id, $name);
            }
            
            if(empty($schedule_count))echo '<div class="alert alert-warning">'.$lang['dashboard'][40].'</div>';
            else{
                echo '<h4>
						'.$schedule_count.' '.$lang['dashboard'][82].'
					 </h4>
						<button class="btn btn-sm btn-default sel_all" rel="schedule_groups">'.$lang['js']['select_all'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-primary inv_sel" rel="schedule_groups">'.$lang['js']['inv_selected'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-danger del_selected" rel="schedule_groups">'.$lang['js']['del_selected'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-success resume_selected" rel="schedule_groups">'.$lang['js']['resume'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-info stop_selected" rel="schedule_groups">'.$lang['js']['stop'].'</button><br/><br/>';
                $schedules = $auth->get_user_schedule_groups($user_id, $from, $rows, $name);
                
                echo '<table class="table schedule-table">
                        <tr>
                            <th>'.$lang['common'][3].'</th>
                            <th>'.$lang['common'][4].'</th>
                            <th>'.$lang['common'][5].'</th>
                            <th>'.$lang['common'][6].'</th>
                            <th>'.$lang['common'][7].'</th>
                            <th>'.$lang['common'][9].'</th>
                            <th width="250px">'.$lang['common'][8].'</th>
                        </tr>';
                        
                foreach($schedules as $schedule){
                    echo '<tr rel="'.$schedule['schedule_group_id'].'" id="sch-grp-'.$schedule['schedule_group_id'].'" data-json="'.base64_encode(json_encode($schedule)).'">
                            <td>
								<input type="checkbox" class="schedule_group-checkbox">&nbsp;&nbsp;'.$schedule['schedule_group_name'].'
							</td>
                            <td>
								'.($schedule['is_done'] == 1 ? 
									'<span class="label label-success">'.$lang['js']['done'].'</span>' : 
									'<span class="label label-info">'.$lang['js']['processing'].'</span>'
									).'
								'.(!empty($schedule['has_errors']) ? '<br/><br/><span class="label label-danger" title="'.strip_tags($schedule['has_errors']).'">'.(strtoupper($lang['js']['error'])).'</span>' : '').'
							</td>
                            <td>'.($schedule['is_active'] == 1 ? '<span class="label label-success">'.$lang['js']['active'].'</span>' : ( $schedule['is_active'] == 2 ? '<span class="label label-danger">'.$lang['js']['suspended'].'</span>' : '<span class="label label-info">'.$lang['js']['stopped'].'</span>')).'</td>
                            <td>
								<div style="max-width:100px">
									<span class="time">'.($n = get_formatted_time($schedule['next_post'])).'</span>&nbsp;&nbsp;
									'.( $n == 'N/A' ? '' : '<i class="glyphicon glyphicon-edit pointer edit_sch_time"></i>' ).' 
									<br/><br/>
									<input type="text" class="sch_time form-control edit_sch_submit medium-input2" rel="sch_group"
									 value="'.get_formatted_time($schedule['next_post'], 0, 2).'" style="display:none"/>
								</div>
							</td>
                            <td>
                                <div style="max-width:100px">'.get_formatted_time($schedule['last_post']).'</div>
                            </td>
                            <td>
								'.$schedule['total_schedules'].'
							</td>
                            <td>
                                <button class="btn btn-sm btn-info schedule-group-edit">
                                    <i class="glyphicon glyphicon-edit pointer"></i>&nbsp;&nbsp;'.$lang['common'][10].'
                                </button>&nbsp;&nbsp;
                                <a class="btn btn-sm btn-primary schedule-explore" href="'.makeuri('post_log.php?gid='.$schedule['schedule_group_id']).'">
                                    <i class="glyphicon glyphicon-globe pointer"></i>&nbsp;&nbsp;'.$lang['common'][11].'
                                </a>
                                <br/><br/>
                                <a class="btn btn-sm btn-danger schedule-explore" href="'.makeuri('schedule.php?gid='.$schedule['schedule_group_id']).'">
                                    <i class="glyphicon glyphicon-search pointer"></i>&nbsp;&nbsp;'.$lang['common'][12].'
                                </a>&nbsp;&nbsp;
                                <button class="btn btn-sm btn-warning schedule-group-delete">
                                    <i class="glyphicon glyphicon-trash pointer"></i>&nbsp;&nbsp;'.$lang['common'][0].'
                                </button>										
                            </td>
                         </tr>';									
                }
                echo '</table>';	
                $g = $_GET;
				unset($g['show']);
                echo pagination($schedule_count, $rows, $from, http_build_query($g), makeuri('dashboard.php?show=schedules'));
            }
        }
        ?>
    </div>
</div>

<div class="modal add-schedule-modal fade">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title"><?php echo $lang['dashboard'][41]?></h4>
          </div>
          <div class="modal-body" style="overflow:hidden !important">
            <form id="add-schedule-form">
            <input type="hidden" name="schedule_save" value="-1">
            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group">
                      <div class="row">
                        <div class="col-lg-6">
                            <label><?php echo $lang['dashboard'][42]?></label>
                            <input name="schedule_group_name" id="schedule_group_name" class="form-control"/>
                        </div>
                        <div class="col-lg-6">
                            <label><?php echo $lang['dashboard'][43]?></label><br/>
                            <select name="social_ids" id="social_ids" class="select2">
                                <?php echo $auth->get_user_pages_list($user_id, 1);?>
                            </select>
                          </div>
                      </div>
                      <div class="row">
                          <div class="col-lg-6">
                            <label><?php echo $lang['dashboard'][44]?></label><br/>
                            <select name="folder_id" id="folder_id" class="select2">
                                <option value=""><?php echo $lang['common'][13]?></option>
                                <?php echo $auth->get_user_folders($user_id, 1, 5000, '', 2)?>
                                <?php echo $auth->get_user_rss($user_id, 1, 5000, '', 2)?>
                            </select>
                          </div>
                          <div class="col-lg-2">
                            <label><?php echo $lang['dashboard'][45]?></label>
                            <select name="watermark" id="watermark" class="form-control medium-input">
                                <option value=""><?php echo $lang['common'][13]?></option>
                                <?php echo $auth->get_user_watermarks($user_id, 1, 0)?>
                            </select>
                          </div>
                          <div class="col-lg-2">
                            <label><?php echo $lang['dashboard'][46]?></label>
                            <select name="watermark_position" id="watermark_position" class="form-control medium-input">
                                <option value=""><?php echo $lang['common'][13]?></option>
                                <option value="TOPLEFT">TOP LEFT</option>
                                <option value="TOPRIGHT">TOP RIGHT</option>
                                <option value="BOTTOMLEFT">BOTTOM LEFT</option>
                                <option value="BOTTOMRIGHT">BOTTOM RIGHT</option>
                                <option value="CENTER">CENTER</option>
                            </select>
                          </div>
                          
                          <!--new added-->
                          <div class="col-lg-2">
                          	<label><?php echo $lang['dashboard']['schedule']['start_posting']?></label>
                          	<input type="text" name="post_start_from" id="post_start_from" class="form-control medium-input"/>
                          </div>
                          <!--/new added-->
                          
                      </div>
                    <div class="row">
                        <div class="col-lg-2">
                            <label><?php echo $lang['dashboard'][47]?></label>
                            <select name="post_freq" id="post_freq" class="form-control"></select><br/>
                            <select name="post_freq_type" id="post_freq_type" class="form-control">
                                <option value="minutes"><?php echo $lang['dashboard']['times'][0]?></option>
                                <option value="hours"><?php echo $lang['dashboard']['times'][1]?></option>
                                <option value="days"><?php echo $lang['dashboard']['times'][2]?></option>
                                <option value="weeks"><?php echo $lang['dashboard']['times'][3]?></option>
                                <option value="months"><?php echo $lang['dashboard']['times'][4]?></option>
                                <option value="years"><?php echo $lang['dashboard']['times'][5]?></option>
                            </select>
                            <br/>
                            <label><?php echo $lang['dashboard'][48]?></label>
                            <select name="post_sequence" id="post_sequence" class="form-control">
                                <option value="random"><?php echo $lang['common'][32]?></option>
                                <option value="ordered"><?php echo $lang['common'][33]?></option>
                                <option value="slideshow"><?php echo $lang['common'][34]?> [<?php echo $lang['ajax']['fbnyt']?>]</option>
                                <option value="album"><?php echo $lang['common'][35]?> [<?php echo $lang['ajax']['fbntw']?>]</option>
                                <option value="image|text|video">
                                    <?php echo $lang['common'][29]?>, <?php echo $lang['common'][30]?>, <?php echo $lang['common'][31]?>
                                </option>
                                <option value="image|video|text">
                                    <?php echo $lang['common'][29]?>, <?php echo $lang['common'][31]?>, <?php echo $lang['common'][30]?>
                                </option>
                                <option value="text|image|video">
                                    <?php echo $lang['common'][30]?>, <?php echo $lang['common'][29]?>, <?php echo $lang['common'][31]?>
                                </option>
                                <option value="text|video|image">
                                    <?php echo $lang['common'][30]?>, <?php echo $lang['common'][31]?>, <?php echo $lang['common'][29]?>
                                </option>
                                <option value="video|text|image">
                                    <?php echo $lang['common'][31]?>, <?php echo $lang['common'][30]?>, <?php echo $lang['common'][29]?>
                                </option>
                                <option value="video|image|text">
                                    <?php echo $lang['common'][31]?>, <?php echo $lang['common'][29]?>, <?php echo $lang['common'][30]?>
                                </option>
                            </select>
                            
                        </div>
                        <div class="col-lg-2">                        	
                            
                            <label><?php echo $lang['dashboard'][51]?></label>
                            <div class="onoffswitch">
                                <input type="checkbox" name="is_active" class="onoffswitch-checkbox slider-check" id="is_active" checked="checked"
                                onchange="if($(this).is(':checked')==true)$(this).val(1);else $(this).val(0)">
                                <label class="onoffswitch-label" for="is_active"></label>
                            </div>
                            
                            <label>
								<?php echo $lang['js']['sync_post']?>
                                <i class="glyphicon glyphicon-exclamation-sign" data-toggle="tooltip" data-placement="right" 
                            	title="<?php echo $lang['schedule']['sync_post_help']?>"></i>
                           	</label>
                            <div class="onoffswitch">
                                <input type="checkbox" name="sync_post" class="onoffswitch-checkbox slider-check" id="sync_post" 
                                onchange="if($(this).is(':checked')==true)$(this).val(1);else $(this).val(0)">
                                <label class="onoffswitch-label" for="sync_post"></label>
                            </div>
                            
                            <label>
								<?php echo $lang['dashboard']['schedule']['one_time_use']?>
                            	<i class="glyphicon glyphicon-exclamation-sign" data-toggle="tooltip" data-placement="right" title="<?php echo $lang['schedule']['one_time_help']?>"></i>
                            </label>
                            <div class="onoffswitch">
                                <input type="checkbox" name="onetime_post" class="onoffswitch-checkbox slider-check" id="onetime_post" 
                                onchange="if($(this).is(':checked')==true)$(this).val(1);else $(this).val(0)">
                                <label class="onoffswitch-label" for="onetime_post"></label>
                            </div>
                           
                        </div>
                        
                        <!--new added-->
                        <div class="col-lg-2">
                            
                            <label>
								<?php echo $lang['dashboard']['schedule']['repeat_posting']?>
                                <i class="glyphicon glyphicon-exclamation-sign" data-toggle="tooltip" data-placement="right" 
                                title="<?php echo $lang['schedule']['repeat_posting_help']?>"></i>
                            </label>
                            <div class="onoffswitch">
                                <input type="checkbox" name="do_repeat" class="onoffswitch-checkbox slider-check" id="do_repeat"
                                onchange="if($(this).is(':checked')==true)$(this).val(1);else $(this).val(0)">
                                <label class="onoffswitch-label" for="do_repeat"></label>
                            </div>
                            
                            <label>
								<?php echo $lang['dashboard']['schedule']['repeat_camp']?>
                            	<i class="glyphicon glyphicon-exclamation-sign" data-toggle="tooltip" data-placement="right" 
                                title="<?php echo $lang['schedule']['repeat_camp_help']?>"></i>
                            </label>
                            <div class="onoffswitch">
                                <input type="checkbox" name="repeat_campaign" class="onoffswitch-checkbox slider-check" id="repeat_campaign"
                                onchange="if($(this).is(':checked')==true)$(this).val(1);else $(this).val(0)">
                                <label class="onoffswitch-label" for="repeat_campaign"></label>
                            </div>
                            
                           
                            
                            <label>
								<?php echo $lang['dashboard'][50]?>
                                <i class="glyphicon glyphicon-exclamation-sign" data-toggle="tooltip" data-placement="right" 
                                title="<?php echo $lang['schedule']['auto_del_help']?>"></i>
                            </label>
                            <div class="onoffswitch">
                                <input type="checkbox" name="auto_delete_file" class="onoffswitch-checkbox slider-check" id="auto_delete_file" 
                                onchange="if($(this).is(':checked')==true)$(this).val(1);else $(this).val(0)">
                                <label class="onoffswitch-label" for="auto_delete_file"></label>
                            </div>
                           
                        </div>
                        <!--/new added-->
                        
                        <div class="col-lg-2">
                            <label><?php echo $lang['dashboard'][52]?></label>
                            <select name="post_only_from" id="post_only_from" class="form-control medium-input"></select>
                            
                            <label><?php echo $lang['dashboard'][53]?></label>
                            <select name="post_delete_freq" id="post_delete_freq" class="form-control medium-input"></select>
                            
                            <!--room available-->
                        </div>
                        
                        <div class="col-lg-2">
                            <label><?php echo $lang['common'][17]?></label>
                            <select name="post_only_to" id="post_only_to" class="form-control medium-input"></select>

                            <label>&nbsp;</label>
                            <select name="post_delete_freq_type" id="post_delete_freq_type" class="form-control medium-input">
                                <option value="minutes"><?php echo $lang['dashboard']['times'][0]?></option>
                                <option value="hours"><?php echo $lang['dashboard']['times'][1]?></option>
                                <option value="days"><?php echo $lang['dashboard']['times'][2]?></option>
                                <option value="weeks"><?php echo $lang['dashboard']['times'][3]?></option>
                                <option value="months"><?php echo $lang['dashboard']['times'][4]?></option>
                                <option value="years"><?php echo $lang['dashboard']['times'][5]?></option>
                            </select>
                           
                            <div class="sl_type_choose" style="display:none">
                                <label><?php echo $lang['dashboard'][58]?></label>
                                <select class="form-control medium-input" name="slide_type" id="slide_type">
                                    <?php echo get_available_slideshow_type()?>
                                </select>                                  
                            </div>
                           
                        </div>
                        
                        <!--new added-->
                        <div class="col-lg-2">                           
                            <label><?php echo $lang['dashboard']['schedule']['end_posting']?></label>
                            <input type="text" name="post_end_at" id="post_end_at" class="form-control medium-input"/>
                           
                            <label><?php echo $lang['dashboard'][54]?></label>
                            <select name="post_delete_action" id="post_delete_action" class="form-control medium-input">
                                <option value=""><?php echo $lang['common'][13]?></option>
                                <option value="DELETE"><?php echo $lang['common'][14]?></option>
                                <option value="HIDE"><?php echo $lang['common'][15]?> [<?php echo $lang['common'][16]?>]</option>
                            </select>
                          
                          	 <div class="sl_type_choose" style="display:none">                                
                                <label><?php echo $lang['dashboard'][57]?></label>
                                <select class="form-control medium-input" name="slide_duration" id="slide_duration">
                                    <option value="3">3 <?php echo $lang['common'][18]?></option>
                                    <option value="4">4 <?php echo $lang['common'][18]?></option>
                                    <option value="5">5 <?php echo $lang['common'][18]?></option>
                                    <option value="6">6 <?php echo $lang['common'][18]?></option>
                                    <option value="7">7 <?php echo $lang['common'][18]?></option>
                                    <option value="8">8 <?php echo $lang['common'][18]?></option>
                                    <option value="9">9 <?php echo $lang['common'][18]?></option>
                                    <option value="10">10 <?php echo $lang['common'][18]?></option>
                                </select>          
                            </div>
                            
                        </div>
                        <!--/new added-->  
                      </div>
                    </div>
                 </div>
                 <div class="col-lg-3">
                    <h4>
						<?php echo $lang['dashboard'][56]?> 
                        <span style="font-size:13px">
                        	<i class="glyphicon glyphicon-edit sch_bulk pointer" title="<?php echo $lang['js']['bulk_sel']?>"></i>
							&nbsp;&nbsp;
                        	<i class="glyphicon glyphicon-trash sch_all_clear pointer" title="<?php echo $lang['js']['clear_all_sel']?>"></i>
                            &nbsp;&nbsp;
                            <!--<i class="glyphicon glyphicon-exclamation-sign pointer" title="<?php echo $lang['title']['help']?>" onclick="$('.sch-help').modal()"></i>-->
                        </span>
                    </h4>
                    <div class="schedule-selected-pages">
                        <table class="table">
                        </table>
                    </div>
                    
                 </div>
              </div>
              </form>
          </div>
          <div class="modal-footer">
            <div class="row">
                <div class="col-lg-6">
                    <div class="alert alert-info add-sc-footer-warning" style="display:none; text-align:left; font-size:12px"></div>
                </div>
                <div class="col-lg-6">
                	<button type="button" class="btn btn-sm btn-info add-schedule-advanced-opts"><?php echo $lang['dashboard'][59]?></button>
                	<button type="button" class="btn btn-sm btn-warning schedule_group_reset"><?php echo $lang['modals'][41]?></button>
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
                    <button type="button" class="btn btn-sm btn-primary add-schedule-submit-btn"><?php echo $lang['common'][20]?></button>
                </div>
          </div>
        </div>
      </div>
    </div>
</div>

<script>
$("#social_ids, #folder_id").select2();
if($('.sch_time').length > 0){
	$('.sch_time').datetimepicker({
		controlType: 'select',
		dateFormat: 'yy-mm-dd',
		timeFormat: 'hh:mm:00 TT',
		oneLine: true,
		onClose: function(dateText, inst) {
			datetimepicker_submit(dateText, inst, $(this))	
		}
	});
}
</script>