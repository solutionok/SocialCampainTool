<?php
/**
 * @package Social Ninja
 * @version 1.5
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();

$used_storage = $user_data['used_storage'];
$allowed_storage = $user_data['allowed_storage'];
$post_today = $auth->get_users_posted_today($user_id);
$post_limit = $user_data['post_per_day'];

$post_today_fb = $auth->get_users_posted_today($user_id, 'facebook');
$post_limit_fb = $user_data['facebook_post_per_day'];

$post_today_tw = $auth->get_users_posted_today($user_id, 'twitter');
$post_limit_tw = $user_data['twitter_post_per_day'];

$post_today_yt = $auth->get_users_posted_today($user_id, 'youtube');
$post_limit_yt = $user_data['youtube_post_per_day'];
?>
<div class="row">
	<div class="col-lg-12">
    	<h1 class="text-center"><?php echo $lang['header'][0]?></h1>
    </div>
</div>
<div class="row">
	<div class="col-lg-12">
    	<ul class="nav nav-tabs" id="tabs">
            <li class="active"><a aria-expanded="false" href="#summary" data-toggle="tab"><?php echo $lang['header']['dashboard'][0]?></a></li>
            <li class=""><a aria-expanded="true" href="#accounts" data-toggle="tab"><?php echo $lang['header']['dashboard'][1]?></a></li>
            <li class=""><a aria-expanded="true" href="#folders" data-toggle="tab"><?php echo $lang['header']['dashboard'][2]?></a></li>
            <?php if(!empty($settings['fb_enabled'])){?>
            <li class=""><a aria-expanded="true" href="#fanpages" data-toggle="tab"><?php echo $lang['header']['dashboard'][3]?></a></li>
            <li class=""><a aria-expanded="true" href="#groups" data-toggle="tab"><?php echo $lang['header']['dashboard'][4]?></a></li>
            <li class=""><a aria-expanded="true" href="#events" data-toggle="tab"><?php echo $lang['header']['dashboard'][5]?></a></li>
            <?php }?>
            <li class=""><a aria-expanded="true" href="#categories" data-toggle="tab"><?php echo $lang['header']['dashboard']['cats']?></a></li>
            <li class=""><a aria-expanded="true" href="#rss" data-toggle="tab"><?php echo $lang['header']['dashboard'][7]?></a></li>
            <li class=""><a aria-expanded="true" href="#schedules" data-toggle="tab"><?php echo $lang['header']['dashboard'][8]?></a></li>
            <li class=""><a aria-expanded="true" href="#cleanup" data-toggle="tab"><?php echo $lang['header']['dashboard'][9]?></a></li>
            <li class=""><a aria-expanded="true" href="#logs" data-toggle="tab"><?php echo $lang['header']['dashboard'][11]?></a></li>
            <li class=""><a aria-expanded="true" href="#settings" data-toggle="tab"><?php echo $lang['header']['dashboard'][10]?></a></li>
         </ul>
        
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="summary">
            	<div class="row">
                	<h3 class="text-center"><?php echo $lang['header'][5]?> <?php echo strtok($user_data['email'], '@')?></h3>
                	<div class="col-lg-6 col-md-offset-3">
                        <table class="table">
                        	<tr>
                            	<th><?php echo $lang['dashboard'][0]?></th>
                                <td><?php echo $user_data['time_zone'] == '' ? 'UTC' : $user_data['time_zone']?></td>
                            </tr>
                            <tr>
                            	<th><?php echo $lang['dashboard'][1]?></th>
                                <td>
									<?php 
									echo '<span style="color:'.($allowed_storage - $used_storage < 1024*1024 ? 'red' : '').'">'.formatSize($used_storage).'</span>';
									?>
                                </td>
                            </tr>
                            <tr>
                            	<th><?php echo $lang['dashboard'][2]?></th>
                                <td><?php echo formatSize($allowed_storage)?></td>
                            </tr>
                            <tr>
                            	<th><?php echo $lang['dashboard'][5]?></th>
                                <td><?php echo get_formatted_time($user_data['last_login_time'])?><br/><?php echo $lang['common'][23]?> <?php echo $user_data['last_login_ip']?></td>
                            </tr>
                            <tr>
                            	<th><?php echo $lang['dashboard'][6]?></th>
                                <td>
									<?php echo $user_data['plan_name'].' '.$lang['dashboard']['till'].' '.($user_data['plan_id'] == 1 ? 'N/A' : '<br/>'.get_formatted_time($user_data['membership_expiry_time']))?>
                                    <br/><button class="btn btn-xs btn-info btn-check-limits" rel="<?php echo base64_encode(json_encode(get_plan_details($user_data['plan_id'])))?>"><?php echo $lang['dashboard'][7]?></button></td>
                            </tr>
                            <tr>
                            	<th><?php echo $lang['dashboard'][3]?></th>
                                <td>
									<?php 
									echo $lang['pricing']['total'].' : <span style="color:'.($post_limit - $post_today < 5 ? 'red' : '').'">'.$post_today.'</span>'
									?> <?php echo $lang['common'][21]?><br/>
                                    <?php 
									echo $lang['dashboard'][75].' : <span style="color:'.($post_limit_fb - $post_today_fb < 5 ? 'red' : '').'">'.$post_today_fb.'</span>'
									?> <?php echo $lang['common'][21]?><br/>
                                    <?php 
									echo $lang['dashboard'][76].' : <span style="color:'.($post_limit_tw - $post_today_tw < 5 ? 'red' : '').'">'.$post_today_tw.'</span>'
									?> <?php echo $lang['common'][21]?><br/>
                                    <?php 
									echo $lang['dashboard'][77].' : <span style="color:'.($post_limit_yt - $post_today_yt < 5 ? 'red' : '').'">'.$post_today_yt.'</span>'
									?> <?php echo $lang['common'][21]?>
                                </td>
                            </tr>
                            <tr>
                            	<th><?php echo $lang['dashboard'][4]?></th>
                                <td>
									<?php echo $lang['pricing']['total'].' : '.$post_limit?> <?php echo $lang['common'][21]?>/<?php echo $lang['common'][22]?><br/>
                                    <?php echo $lang['dashboard'][75].' : '.$post_limit_fb?> <?php echo $lang['common'][21]?>/<?php echo $lang['common'][22]?><br/>
                                    <?php echo $lang['dashboard'][76].' : '.$post_limit_tw?> <?php echo $lang['common'][21]?>/<?php echo $lang['common'][22]?><br/>
                                    <?php echo $lang['dashboard'][77].' : '.$post_limit_yt?> <?php echo $lang['common'][21]?>/<?php echo $lang['common'][22]?>
                                </td>
                            </tr>
                        </table>
                   	</div>
                </div>
            </div>
            <div class="tab-pane fade" id="accounts">
            	
            </div>
            <div class="tab-pane fade" id="folders">
            	
            </div>
            <?php if(!empty($settings['fb_enabled'])){?>
            <div class="tab-pane fade" id="fanpages">
            	
            </div>
			<?php }if(!empty($settings['fb_enabled'])){?>
            <div class="tab-pane fade" id="groups">
            	
            </div>
            <?php }?>
            <?php if(!empty($settings['fb_enabled'])){?>
            <div class="tab-pane fade" id="events">
            	
            </div>
            <?php }?>
            
            <div class="tab-pane fade" id="categories">
          
            </div>
            
            <div class="tab-pane fade" id="schedules">
            	
            </div>
            <div class="tab-pane fade" id="cleanup">
            	<div class="pull-right" style="margin-bottom:10px">
                    <button class="btn btn-success" onclick="$('.feed-selector-modal').modal()"><?php echo $lang['dashboard'][43]?></button>&nbsp;&nbsp;
                    <button class="btn btn-danger feed-dh-selected" rel="delete"><?php echo $lang['dashboard'][60]?></button>&nbsp;&nbsp;
                    <!--<button class="btn btn-sm btn-warning feed-dh-selected" rel="hide">Hide selected posts</button>&nbsp;&nbsp;-->
                </div>
            	<div class="row">
					<div class="col-lg-12">
                        <table class="table feed-viewer">
                    		<tr><td><div class="alert alert-warning"><?php echo $lang['dashboard'][43]?></div></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="logs">
            	
            </div>
            
            <div class="tab-pane fade" id="settings">
            	<div class="row">
                    <div class="col-lg-4">
                    	<div class="form-group">
                          <label class="control-label"><?php echo $lang['dashboard'][61]?></label>
                            <select id="time_zone">
                                <option value=""><?php echo $lang['common'][13]?></option>
                                <?php 
                                    $timezones = get_time_zones();
                                    foreach($timezones as $tz => $locale)
                                        echo '<option value="'.$tz.'" '.($user_data['time_zone'] == $tz ? 'selected':'').'>'.$locale.'</option>';
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                    </div>
            		<div class="col-lg-4">
                    	<div class="clock"><h3 class="clock-display"><?php echo date('d-M-Y h:i:s A')?></h3></div>
                    </div>
                </div>
            	<br/>
                
                <hr/>
                <label><?php echo $lang['dashboard'][65]?></label><br/>
                <a href="javascript:void(0)" onclick="$('.fb-login-modal').modal()"><img width="200px" src="images/fblogin.png" alt="Login with facebook"/></a>&nbsp;&nbsp;
   				<a href="<?php echo makeuri('dologin.php?login_type=youtube')?>"><img width="200px" src="images/ytlogin.png" alt="Login with youtube"/></a>&nbsp;&nbsp;
    			<a href="<?php echo makeuri('dologin.php?login_type=twitter')?>"><img width="200px" src="images/twlogin.png" alt="Login with twitter"/></a>&nbsp;&nbsp;	
                <br/><br/>
                <hr/>
                <br/><br/>
                <div class="row">
                	<div class="col-lg-4">
                		<label><?php echo $lang['dashboard'][62]?></label><br/>
                		<select class="theme_changer form-control medium-input"><?php echo list_themes(1)?></select>
						<br/><br/>
						<?php if($settings['fb_enabled']){?>
                        <label><?php echo $lang['dashboard'][63]?></label><br/>
                        <button class="btn btn-sm btn-info" onclick="$('.fb-import-modal').modal()"><i class="glyphicon glyphicon-import"></i>&nbsp;&nbsp;Open importer</button>
                        <?php }?>
                		
                	</div>
                    <div class="col-lg-4">    
						<?php if($settings['fb_enabled']){?>
                        <label><?php echo $lang['dashboard'][64]?></label><br/>
                        <a class="btn btn-sm btn-warning" href="<?php echo makeuri('merge.php')?>"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;Open merger</a>
                        <?php }?>
                    </div>
                    <div class="col-lg-4">
                    	<?php if(!empty($settings['yt_enabled'])){?>
                    	<label><?php echo $lang['dashboard'][27]?></label><br/>
                		<button class="btn btn-sm btn-info comm_file_meta_editor">
                        <i class="glyphicon glyphicon-cog"></i>&nbsp;&nbsp;<?php echo $lang['dashboard'][28]?>
                        </button><br/><br/>
                		<small>* <?php echo $lang['dashboard'][29]?></small><br/><br/>
                        <?php }?>
                    </div>
                </div>
                <br/><br/>
               	<hr/>
                <?php include(dirname(__FILE__).'/apps.php');?>               
               
                <br/>
                <hr/>
                
                <div class="row">
                    <div class="col-lg-4">
                    	<h4><?php echo $lang['dashboard'][66]?></h4>
                        <form id="pwd_change">
                            <div class="form-group">
                                <label><?php echo $lang['dashboard'][67]?></label>
                                <input type="password" class="form-control" name="old_password">
                           
                                <label><?php echo $lang['dashboard'][68]?></label>
                                <input type="password" class="form-control" name="new_password">
                                
                                <label><?php echo $lang['dashboard'][69]?></label>
                                <input type="password" class="form-control" name="new_password2">
                            </div>
                            <input type="hidden" name="pwd_change" value="1" />
                        </form>
                        <button class="btn btn-sm btn-warning pwd_save"><?php echo $lang['common'][24]?></button>
                    </div>
                    <div class="col-lg-4">
                    	<h4><?php echo $lang['dashboard'][70]?></h4>
                        <?php
						$g = empty($_SESSION['new_email_code']) ? 0 : 1
						?>
                        <div class="email_change_div" style=" <?php if($g)echo 'display:none;'?>">
                            <form id="email_change">
                                <div class="form-group">
                                    <label><?php echo $lang['dashboard'][71]?></label>
                                    <input type="text" class="form-control" name="new_email">
                                    
                                    <label><?php echo $lang['dashboard'][69]?></label>
                                    <input type="password" class="form-control" name="password">
                                </div>
                                <input type="hidden" name="email_change" value="1" />
                            </form>
                            <button class="btn btn-sm btn-warning email_save"><?php echo $lang['common'][24]?></button>
                        </div>
                        <div class="email_verify_div" style=" <?php if(!$g)echo 'display:none;'?>">
                            <form id="email_verify">
                                <div class="form-group">
                                    <label><?php echo $lang['dashboard'][72]?></label>
                                    <input type="text" class="form-control" id="new_email_code">
                                </div>
                            </form>
                            <button class="btn btn-sm btn-warning email_code_verify"><?php echo $lang['dashboard'][73]?></button>
                        </div>
                    </div>
                    <div class="col-lg-4">
                    	<div class="row">
							<div class="col-lg-6">
                                <h4><?php echo $lang['dashboard'][74]?></h4>
                                
                                <label><?php echo $lang['dashboard'][75]?> <?php if($user_data['fb_posting'] == 2)echo '<span style="color:red">[SUSPENDED]</span>';?></label>
                                <div class="onoffswitch">
                                    <input type="checkbox" name="fb_posting" class="onoffswitch-checkbox slider-check toggle_posting" id="fb_posting" 
                                        <?php if($user_data['fb_posting'] == 1)echo 'checked';?>>
                                    <label class="onoffswitch-label" for="fb_posting"></label>
                                </div>
                                
                                <label><?php echo $lang['dashboard'][76]?> <?php if($user_data['tw_posting'] == 2)echo '<span style="color:red">[SUSPENDED]</span>';?></label>
                                <div class="onoffswitch">
                                    <input type="checkbox" name="tw_posting" class="onoffswitch-checkbox slider-check toggle_posting" id="tw_posting"
                                        <?php if($user_data['tw_posting'] == 1)echo 'checked';?>>
                                    <label class="onoffswitch-label" for="tw_posting"></label>
                                </div>
                                
                                <label><?php echo $lang['dashboard'][77]?> <?php if($user_data['yt_posting'] == 2)echo '<span style="color:red">[SUSPENDED]</span>';?></label>
                                <div class="onoffswitch">
                                    <input type="checkbox" name="yt_posting" class="onoffswitch-checkbox slider-check toggle_posting" id="yt_posting"
                                        <?php if($user_data['yt_posting'] == 1)echo 'checked';?>>
                                    <label class="onoffswitch-label" for="yt_posting"></label>
                                </div>
                        	</div>
                            <div class="col-lg-6">
                            	<h4><?php echo $lang['dashboard'][78]?></h4>
                                
                                <label><?php echo $lang['dashboard'][79]?></label>
                                <div class="onoffswitch">
                                    <input type="checkbox" name="email_noti" class="onoffswitch-checkbox slider-check toggle_noti" id="email_noti" 
                                        <?php if($user_data['email_noti'] == 1)echo 'checked';?>>
                                    <label class="onoffswitch-label" for="email_noti"></label>
                                </div>
                                
                                <label><?php echo $lang['dashboard'][80]?></label>
                                <div class="onoffswitch">
                                    <input type="checkbox" name="fb_noti" class="onoffswitch-checkbox slider-check toggle_noti" id="fb_noti"
                                        <?php if($user_data['fb_noti'] != 0)echo 'checked';?>>
                                    <label class="onoffswitch-label" for="fb_noti"></label>
                                </div>
                                
                            </div>
                        </div>
                    </div>
    			</div>
                
            </div>
        
        	<div class="tab-pane fade" id="rss">
        		
        	</div>
 			
            <!--/end of tab panes-->	       
        </div>
    </div>
</div>


<script src="<?php echo site_url()?>/js/select2.min.js?v=1.4"></script>

<?php include(dirname(__FILE__).'/dashboard/modals.php');?>
<?php include(dirname(__FILE__).'/meta.php');?>

<script>
	$('#time_zone, #sel_autocomplete, #feed_selector').select2();
	var minutes_selectors = [<?php echo implode(',', get_valid_schedule_intervals('minutes'))?>];
	var hours_selectors = [<?php echo implode(',', get_valid_schedule_intervals('hours'))?>];
	var days_selectors = [<?php echo implode(',', get_valid_schedule_intervals('days'))?>];
	var weeks_selectors = [<?php echo implode(',', get_valid_schedule_intervals('weeks'))?>];
	var months_selectors = [<?php echo implode(',', get_valid_schedule_intervals('months'))?>];
	var years_selectors = [<?php echo implode(',', get_valid_schedule_intervals('years'))?>];
	var hour_ranges = [<?php echo implode(',', get_valid_schedule_intervals('hour_ranges'))?>];
	var $_GET = {};
	var get_params = '<?php echo http_build_query($_GET)?>';
	$_GET['show'] = '';
	<?php if(!empty($_GET['show'])){?>
	$_GET['show'] = '<?php echo @purify_text($_GET['show'])?>';
	<?php }?>
	<?php if(!empty($settings['seo_url'])){?>
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var u = $(e.target).attr('href').replace('#', '');
		try{window.history.replaceState( {} , document.title, '<?php echo makeuri('dashboard.php')?>'.replace(/\/\s*$/, "")+'/'+u+'/');}catch(e){}
	});
	<?php }else{?>
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var u = $(e.target).attr('href').replace('#', '');
		try{window.history.replaceState( {} , document.title, '<?php echo makeuri('dashboard.php')?>?show='+u);}catch(e){}
	});
	<?php }?>
	<?php if(!empty($user_data['theme'])){?>
	$('.theme_changer').val('<?php echo $user_data['theme']?>');
	<?php }?>
</script>