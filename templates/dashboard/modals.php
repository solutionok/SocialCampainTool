<?php
/**
 * @package Social Ninja
 * @version 1.1
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<!--modals-->
<?php 
$user_fb_ids = $auth->get_user_fb_ids($user_id);
$user_pages = $auth->get_user_pages_list($user_id, 1);
?>

<div class="modal create-folder-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][0]?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
            <input class="form-control" id="folderName" placeholder="<?php echo $lang['dashboard'][81]?>" type="text">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary create-folder-btn"><?php echo $lang['dashboard'][9]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal fb-login-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['title']['login']?></h4>
      </div>
      <div class="modal-body">
      	<div class="text-center">
       		<a href="<?php echo makeuri('dologin.php?login_type=facebook')?>"><img src="<?php echo site_url()?>/images/fbweblogin.png" width="270"/></a>
        </div>
        <br/>
        <h3 class="text-center">OR</h3>
        <br/>
        <form action="<?php echo makeuri('dologin.php?login_type=facebook')?>" method="post">
        	<label>Access Token/URL</label>
            <input type="text" name="access_token" class="form-control" />
            <br/>
            <button class="btn btn-info"><?php echo $lang['common'][45]?></button>
        </form>
        <br/><br/>
        <label>Token generator</label><br/>
        <a class="btn btn-success graph-token-gen" href="javascript:void(0)">Graph API Explorer</a>
        <a class="btn btn-danger htc-token-gen" href="javascript:void(0)">HTC Token</a>
        <a class="btn btn-info nok-token-gen" href="javascript:void(0)">Nokia Token</a>
        <a class="btn btn-primary iph-token-gen" href="javascript:void(0)">iPhoto Token</a><br/><br/>
        <a class="btn btn-info insta-token-gen" href="javascript:void(0)">Insta Token</a>
        <a class="btn btn-primary spot-token-gen" href="javascript:void(0)">Spotify Token</a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal m-limits-modal">
  <div class="modal-dialog" style="width:75%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][1]?></h4>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
      </div>
    </div>
  </div>
</div>


<div class="modal update-folder-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][2]?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
            <input class="form-control" id="newFolderName" placeholder="Enter new folder name" type="text">
          </div>
        </div>
      </div>
      <div class="modal-footer">
      	<input type="hidden" name="updateFolderId" value="">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary update-folder-btn"><?php echo $lang['modals'][2]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal add-rss-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][3]?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label><?php echo $lang['modals'][4]?></label>
          <div>
            <input class="form-control" id="rssName" placeholder="<?php echo $lang['modals'][7]?>" type="text">
          </div>
        </div>
        <div class="form-group">
          <label><?php echo $lang['modals'][5]?></label>
          <div>
            <input class="form-control" id="rssURL" placeholder="<?php echo $lang['modals'][8]?>" type="text">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary add-rss-btn"><?php echo $lang['modals'][3]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal update-rss-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][6]?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label><?php echo $lang['modals'][4]?></label>
          <div>
            <input class="form-control" id="newrssName" placeholder="<?php echo $lang['modals'][7]?>" type="text">
          </div>
        </div>
        <div class="form-group">
          <label><?php echo $lang['modals'][5]?></label>
          <div>
            <input class="form-control" id="newrssURL" placeholder="<?php echo $lang['modals'][8]?>" type="text">
          </div>
        </div>
      </div>
      <div class="modal-footer">
      	<input type="hidden" name="updateRSSId" value="">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary update-rss-btn"><?php echo $lang['modals'][6]?></button>
      </div>
    </div>
  </div>
</div>


<div class="modal feed-selector-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][9]?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label><?php echo $lang['modals'][10]?></label>
            <select name="feed_selector" id="feed_selector" class="form-control">
                <?php echo $user_pages;?>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary feed_selector_view"><?php echo $lang['modals']['buttons'][0]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal fb-import-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][11]?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
        	<div class="row">
				<div class="col-lg-6">
                    <label><?php echo $lang['modals'][12]?></label>
                    <select id="import_fb_id" class="form-control"><?php echo $user_fb_ids;?></select><br/>
                </div>
                <div class="col-lg-6">
                    <label><?php echo $lang['modals'][13]?></label>
                    <select id="import_type" class="form-control">
                    	<option value="HTML">HTML</option>
                        <option value="ID">ID</option>
                    </select><br/>
                </div>
            </div>
            <label><?php echo $lang['modals'][14]?></label>
            <textarea id="fb_group_event_ids" class="form-control" style="height:200px" placeholder="<?php echo $lang['modals'][15]?>"></textarea><br/>
            <?php echo $lang['modals'][16]?> <a href="javascript:void(0)" onclick="$('.import-html-help').modal()"><?php echo $lang['common'][25]?></a>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary fb_import_btn"><?php echo $lang['modals']['buttons'][1]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal import-html-help">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][17]?></h4>
      </div>
      <div class="modal-body">
        <ul>
        	<li><?php echo $lang['modals'][18]?></li>
            <li><?php echo $lang['modals'][19]?> <a href="https://mbasic.facebook.com/groups/?seemore&refid=27" target="_blank">https://mbasic.facebook.com/groups/?seemore&amp;refid=27</a></li>
            <li><?php echo $lang['modals'][20]?></li>
            <li><?php echo $lang['modals'][21]?></li>
            <li><?php echo $lang['modals'][22]?></li>
            <li><?php echo $lang['modals'][23]?> <a href="https://mbasic.facebook.com/groups/" target="_blank">https://mbasic.facebook.com/groups/</a></li>
            <li><?php echo $lang['modals'][24]?></li>
            <li><?php echo $lang['modals'][25]?></li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal update-fb-noti">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][26]?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
            <select id="fb_noti_id" class="form-control">
            	<?php echo $user_fb_ids;?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal" onclick="$('#fb_noti').attr('checked', false)"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary update-fb-noti-btn"><?php echo $lang['common'][45]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal adv-settings-group">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][27]?></h4>
      </div>
      <div class="modal-body">
      	<form id="adv_group_settings_form">
      	<div class="row">
        	<div class="col-lg-6">
                <div class="form-group">
                    <label><?php echo $lang['modals'][28]?></label>
                    <div class="row">
                    	<div class="col-lg-6">
                            <select class="form-control" id="stats_name">
                                <option value=""><?php echo $lang['modals'][29]?></option>
                                <option value="fb:Likes" class="fb_stats">Facebook Likes</option>
                                <option value="fb:Comments" class="fb_stats">Facebook Comments</option>
                                <option value="fbpage:Views" class="fb_stats">Facebook Views [Pages only]</option>
                                <option value="fbpage:Negative_Feedback" class="fb_stats">Facebook Negative Feedback [Pages only]</option>
                                
                                <option value="twitter:Favorites" class="tw_stats">Twitter Favorites</option>
                                <option value="twitter:Retweet" class="tw_stats">Twitter Retweet</option>
                                
                                <option value="youtube:Views" class="yt_stats">Youtube Views</option>
                                <option value="youtube:Likes" class="yt_stats">Youtube Likes</option>
                                <option value="youtube:Dislikes" class="yt_stats">Youtube Dislikes</option>
                                <option value="youtube:Favorites" class="yt_stats">Youtube Favorites</option>
                            
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
                        <option value="168-172">After 1 week</option>
                    </select>
                    <label><?php echo $lang['modals'][37]?></label>
                    <select name="bump_type" class="form-control">
                    	<option value="onetime"><?php echo $lang['modals'][38]?></option>
                        <option value="repeat"><?php echo $lang['modals'][39]?></option>
                    </select>
                </div>
        	</div>
        </div>
        <input type="hidden" name="adv_settings_sch_group_id" id="adv_settings_sch_group_id" value="" />
        </form>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-sm btn-danger schedule_group_stats_remove"><?php echo $lang['modals'][40]?></button>
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary adv_group_settings_save"><?php echo $lang['common'][20]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal post_now_modal">
  <div class="modal-dialog" style="width:75%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['browse']['post_sel_file']?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
          	<div class="row">
                <input type="hidden" name="p_file_id" id="p_file_id" value="" />
                <input type="hidden" name="stop_post" id="stop_post" value="" />
                <div class="col-lg-4">
                    <label><?php echo $lang['dashboard'][43]?></label><br/>
                    <select name="social_ids" id="social_ids2" class="select2">
                        <?php echo $user_pages;?>
                    </select><br/>
                    
                    <div class="row">
                    	<div class="col-lg-6">
                            <label><?php echo $lang['dashboard'][45]?></label>
                            <select name="watermark" id="watermark" class="form-control medium-input">
                                <option value=""><?php echo $lang['common'][13]?></option>
                                <?php echo $auth->get_user_watermarks($user_id, 1, 0)?>
                            </select>
                          </div>
                          <div class="col-lg-6">
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
                    </div>
                    
                    <br/>
                    
                    <div class="row">
                    	<div class="col-lg-6">
                        	<label><?php echo $lang['dashboard'][53]?></label>
                            <select name="post_delete_freq" id="post_delete_freq" class="form-control medium-input"></select>
                            <label>&nbsp;</label>
                            <select name="post_delete_freq_type" id="post_delete_freq_type" class="form-control medium-input">
                                <option value="minutes"><?php echo $lang['dashboard']['times'][0]?></option>
                                <option value="hours"><?php echo $lang['dashboard']['times'][1]?></option>
                                <option value="days"><?php echo $lang['dashboard']['times'][2]?></option>
                                <option value="weeks"><?php echo $lang['dashboard']['times'][3]?></option>
                                <option value="months"><?php echo $lang['dashboard']['times'][4]?></option>
                                <option value="years"><?php echo $lang['dashboard']['times'][5]?></option>
                            </select>
                        </div>
                    	<div class="col-lg-6">
                        	<label><?php echo $lang['dashboard'][54]?></label>
                            <select name="post_delete_action" id="post_delete_action" class="form-control medium-input">
                                <option value=""><?php echo $lang['common'][13]?></option>
                                <option value="DELETE"><?php echo $lang['common'][14]?></option>
                                <option value="HIDE"><?php echo $lang['common'][15]?> [<?php echo $lang['common'][16]?>]</option>
                            </select>
                            
                            <label><?php echo $lang['js']['delay']?></label>
                            <input type="text" name="post_delay" id="post_delay" class="form-control medium-input"/>
                        </div>
                    </div>
                    <br/>
                    <div class="row">
                    	<div class="col-lg-12">
                        	<small><?php echo $lang['js']['delay_help']?> <b>5,10</b> or <b>5</b></small>
                        </div>
                    </div>
                    
                </div>
                <div class="col-lg-4">
                     <h4>
                        <?php echo $lang['dashboard'][56]?> 
                        <span style="font-size:13px">
                            <i class="glyphicon glyphicon-edit sch_bulk pointer" title="<?php echo $lang['js']['bulk_sel']?>"></i>
                            &nbsp;&nbsp;
                            <i class="glyphicon glyphicon-trash sch_all_clear pointer" title="<?php echo $lang['js']['clear_all_sel']?>"></i>
                        </span>
                    </h4>
                    <div class="schedule-selected-pages schedule-selected-pages2">
                        <table class="table">
                        </table>
                    </div>
                </div>
                <div class="col-lg-4">
                     <h4>
                        <?php echo $lang['browse']['post_results']?> &nbsp;&nbsp; 
                        <button class="btn btn-info btn-xs" onclick="$('#stop_post').val(1)"><?php echo $lang['js']['stop']?></button>
                    </h4>
                    <div class="post-now-log">
                        <div class="posting_now">
                            
                        </div>
                        <br/>
                        <table class="table posted_now">
                        </table>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary post_now_submit"><?php echo $lang['common'][45]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal sel_autocomplete_modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close sel_autocomplete_modal_close" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['js']['bulk_sel']?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
            <select style="width:100%" id="sel_autocomplete" class="select2">
            	<option value=""><?php echo $lang['js']['select_one']?></option>
                <option value="fbprofile"><?php echo $lang['js']['fbprofile']?></option>
                <option value="fbpage"><?php echo $lang['js']['fbpage']?></option>
                <option value="fbgroup"><?php echo $lang['js']['fbgroup']?></option>
                <option value="fbevent"><?php echo $lang['js']['fbevent']?></option>
                <option value="twitter"><?php echo $lang['js']['twitter']?></option>
                <option value="youtube"><?php echo $lang['js']['youtube']?></option>
                <?php echo $auth->get_user_categories($user_id, 1, 500, '', 1)?>
            </select><br/><br/>
            <select style="display:none" id="sel_fb_profile" class="form-control">
                <?php echo $user_fb_ids?>
            </select><br/>
            <select style="display:none" id="sel_group_privacy" class="form-control">
                <option value=""><?php echo $lang['js']['select_one']?></option>
                <option value="OPEN">OPEN</option>
                <option value="CLOSED">CLOSED</option>
                <option value="SECRET">SECRET</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default sel_autocomplete_modal_close"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary sel_autocomplete_process"><?php echo $lang['common'][45]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal sch-help">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['modals'][17]?></h4>
      </div>
      <div class="modal-body">
        <ul>
        	<li><?php echo $lang['modals'][18]?></li>
            <li><?php echo $lang['modals'][19]?> <a href="https://mbasic.facebook.com/groups/?seemore&refid=27" target="_blank">https://mbasic.facebook.com/groups/?seemore&amp;refid=27</a></li>
            <li><?php echo $lang['modals'][20]?></li>
            <li><?php echo $lang['modals'][21]?></li>
            <li><?php echo $lang['modals'][22]?></li>
            <li><?php echo $lang['modals'][23]?> <a href="https://mbasic.facebook.com/groups/" target="_blank">https://mbasic.facebook.com/groups/</a></li>
            <li><?php echo $lang['modals'][24]?></li>
            <li><?php echo $lang['modals'][25]?></li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
      </div>
    </div>
  </div>
</div>

