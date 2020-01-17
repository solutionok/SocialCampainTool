<?php
/**
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
	<div class="col-lg-12">
    	<h1 class="text-center folder-header" rel="<?php echo $folder_id?>" data-offset="<?php echo $from?>">
			<?php echo $folder['folder_name']?><br/><br/>
            <div class="text-center">
        		<button class="btn btn-danger btn-clear-upload" onclick="$('#dropzone').toggle()"><i class="glyphicon glyphicon-upload"></i>&nbsp;&nbsp;<?php echo $lang['common'][26]?></button>&nbsp;
            	<button class="btn btn-info add-comm-cap" onclick="$('.add-comm-caption-modal').modal()">
                	<i class="glyphicon glyphicon-retweet"></i>&nbsp;&nbsp;<?php echo $lang['common'][27]?>
                </button>&nbsp;
            	<button class="btn btn-success" onclick="$('.add-text-post-modal').modal()">
                	<i class="glyphicon glyphicon-edit"></i>&nbsp;&nbsp;<?php echo $lang['common'][28]?>
                </button>&nbsp;
                <button class="btn btn-danger delete_all_file"><i class="glyphicon glyphicon-trash"></i>&nbsp;&nbsp;<?php echo $lang['browse'][0]?></button>
                <button class="btn btn-warning file_repos"><i class="glyphicon glyphicon-refresh"></i>&nbsp;&nbsp;<?php echo $lang['browse'][1]?></button>
                <button class="btn btn-danger repos_done" style="display:none"><i class="glyphicon glyphicon-ok"></i>&nbsp;&nbsp;<?php echo $lang['browse'][2]?></button>
            </div>
        </h1>
        <form>
        	<div class="row">
            	<div class="col-lg-8">
                	<input class="form-control submit-enter" name="q" placeholder="Search files" value="<?php echo @purify_text($_GET['q'])?>"/>&nbsp;
                </div>
                <div class="col-lg-3">
                	<select name="type" class="form-control">
                    	<option value=""><?php echo $lang['browse'][3]?></option>
                        <option value="image"><?php echo $lang['common'][29]?></option>
                        <option value="video"><?php echo $lang['common'][31]?></option>
                        <option value="text"><?php echo $lang['common'][30]?></option>
                    </select>
                </div>
                <div class="col-lg-1">
                	<button class="btn btn-info"><?php echo $lang['common'][36]?></button>
                </div>
            </div>
            <input type="hidden" name="fid" value="<?php echo purify_text($_GET['fid'])?>" />
        </form>
        <div id="dropzone" class="dropzone-folder" style="display:none">
        	<form action="<?php echo makeuri('upload.php')?>" class="dropzone" enctype="multipart/form-data">
            	<input type="hidden" name="folder_id" value="<?php echo $folder_id?>" />
                <input type="hidden" name="caption" value="" />
                <input type="hidden" name="use_name_as_cap" />
                <div class="dz-message">
                	<?php echo $lang['browse'][4]?><br />
                	<span class="note">(<?php echo $lang['browse'][5]?>)</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="row">
	<div class="col-lg-12 file-pane-master">
    <?php if(!$total_files){?>
        <div class="alert alert-danger no-file-error"><?php echo $lang['browse'][6]?></div>
    <?php }else{?>
    <?php if(empty($files)){?>
        <div class="alert alert-danger no-file-error"><?php echo $lang['browse'][6]?></div>
    <?php }else{?>
        
        <?php
		echo '<h4>
				'.$total_files.' '.$lang['browse'][7].' 
				<div class="pull-right">
					<button class="btn btn-sm btn-default sel_all" rel="files">'.$lang['js']['select_all'].'</button>&nbsp;&nbsp;
					<button class="btn btn-sm btn-primary inv_sel" rel="files">'.$lang['js']['inv_selected'].'</button>&nbsp;&nbsp;
					<button class="btn btn-sm btn-danger del_selected" rel="files">'.$lang['js']['del_selected'].'</button>
					<button class="btn btn-sm btn-success cap_selected" rel="files" onclick="$(\'.add-bulk-caption-modal\').modal()">'.$lang['js']['caption_sel'].'</button>
				</div>
			</h4><hr/>';
		$max_file_per_row = 4;
        $i = 0;
		$storage = 'storage/'.$user_data['storage'];
        foreach($files as $file){
			/**
			 * Make the thumb
			 */
			if($file['file_type'] == 'image')$link = $thumb = $storage.'/'.$file['filename'];
			else if($file['file_type'] == 'video'){
				$thumb = 'images/video.png';
				$link = $storage.'/'.$file['filename'];
				if(file_exists($storage.'/'.$file['filename'].'.png'))$thumb = $storage.'/'.$file['filename'].'.png';
			}
			else{
				$link = $thumb = 'images/text.png';
				$link = 'data:text/plain;charset=utf-8;base64,'.base64_encode($file['caption']);
			}
			
			/**
			 * check if has link
			 */
			list($c, $l) = extract_caption_links($file['caption']);
			
            if(!$i)echo '<div class="row file-pane-slave">';
            echo '<div class="col-lg-'.(12/$max_file_per_row).' file-holder file-'.$file['file_id'].'" rel="'.$file['file_id'].'" rel-type="'.$file['file_type'].'">
					<div class="file-holder-row-parent">
						<div class="row file-holder-row box effect7">						
							<div class="row">
								<div class="col-lg-12 file-orig-label" style="max-width:'.(10*100/$max_file_per_row).'px">
									<input type="checkbox" class="file-checkbox"/>&nbsp;&nbsp;
									'.($file['original_name'] ? $file['original_name'] : '<br/>').'
								</div>
							</div>
							<div class="row" style="margin-left:15px">
								<div class="col-lg-12">
									<div class="pull-right">
										<span class="label label-'.($file['file_type'] == 'image' ? 'success' : 'danger').'">'.$file['file_type'].'</span>&nbsp;&nbsp;
										<span class="post_now pointer">
											<i class="glyphicon glyphicon-share" title="'.$lang['js']['post_now'].'"></i>
										</span>&nbsp;&nbsp;
										<span class="'.($file['file_type'] == 'video' ? 'edit_meta' : ( $file['file_type'] == 'text' ? 'edit_link' : 'editor_open') ).' pointer">
											<i class="glyphicon glyphicon-cog" title="'.$lang['js']['edit'].'"></i>
										</span>&nbsp;&nbsp;
										<span class="dwnload_file pointer">
											<a href="'.$link.'" target="_blank" '.($file['file_type'] == 'text' ? 'download="status.txt"' : '').' class="dlink">
												<i class="glyphicon glyphicon-download-alt" title="'.$lang['browse'][8].'"></i>
											</a>
										</span>&nbsp;&nbsp;
										<span class="delete_file pointer">
											<i class="glyphicon glyphicon-trash" title="'.$lang['browse'][9].'"></i>
										</span>&nbsp;&nbsp;
									</div>		
								</div>
							</div>
						</div>
						<br/>
						<div class="row">
							<div class="col-lg-12 file-thumb-preview" style="background:url(\''.$thumb.'\')">
								<div class="bottom-right">
									'.($file['duration'] ? '<span class="label label-info">'.pretty_time($file['duration']).'</span>' : (empty($l) ? '' : '<span class="label label-info">Link</span>')).'
								</div>
							</div>
						</div>
						<br/>
						<div class="row">
							<div class="col-lg-12 file-caption-preview">
								<textarea class="form-control editor_text" placeholder="'.$lang['browse'][11].'">'.$file['caption'].'</textarea>
							</div>
						</div>
					</div>
                  </div>';
            $i++;
            if($i >= $max_file_per_row){
                echo '</div><br/><br/>';
                $i = 0;	
            }
        }
        if($i) echo '</div>';
        ?>
    <?php }}?>
	</div>
    <?php if(!empty($files)){
		echo pagination($total_files, $rows, $from, http_build_query($_GET), makeuri('browse.php?fid='.$folder_id));	
	}?>
</div>

<?php
$var_helper1 = '<table class="table">
            	<tr><th colspan="10">'.$lang['browse']['available_vars'].'</th></tr>
            	<tr>
                	<td>[SCHEDULE_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+S</td>
                    <td>[FIRST_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+F</td>
                    <td>[LAST_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+L</td>
					<td>[TAG_ME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+M</td>
                </tr>
                <tr>
                	<td>[FULL_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+U</td>
					<td>[TIME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+T</td>
					<td>[DATE]<br/>'.$lang['browse']['shortcut'].' : Ctrl+D</td>
					<td>[DATE_TIME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+E</td>
                </tr>
                <tr>
                    <td>[PAGE_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+P</td>
                    <td>[FILE_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+I</td>
					<td>[GREETINGS]<br/>'.$lang['browse']['shortcut'].' : Ctrl+G</td>
               </tr>
            </table>';

$var_helper2 = '<table class="table">
            	<tr><th colspan="10">'.$lang['browse']['available_vars'].'</th></tr>
            	<tr>
                	<td>[SCHEDULE_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+S</td>
                    <td>[FIRST_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+F</td>
                    <td>[LAST_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+L</td>
                </tr>
                <tr>
                	<td>[FULL_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+U</td>
					<td>[TIME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+T</td>
					<td>[DATE]<br/>'.$lang['browse']['shortcut'].' : Ctrl+D</td>
                </tr>
                <tr>
					<td>[DATE_TIME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+E</td>
                    <td>[PAGE_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+P</td>
                    <td>[FILE_NAME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+I</td>
               </tr>
			   <tr>
			   		<td>[TAG_ME]<br/>'.$lang['browse']['shortcut'].' : Ctrl+M</td>
					<td>[GREETINGS]<br/>'.$lang['browse']['shortcut'].' : Ctrl+G</td>
			   </tr>
            </table>';
?>

<!--modals-->
<div class="modal add-comm-caption-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['browse'][12]?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
            <textarea class="form-control" id="comm-caption" placeholder="<?php echo $lang['browse'][14]?>"></textarea><br/>
            <input type="checkbox" id="comm-caption-fname" />&nbsp;&nbsp;<label><?php echo $lang['browse'][13]?></label>
            <br/><br/>
            <?php echo $var_helper1?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary add-comm-caption-btn"><?php echo $lang['common'][20]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal add-bulk-caption-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['js']['add_caption_sel']?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
            <textarea class="form-control" id="bulk-caption" placeholder="<?php echo $lang['browse'][14]?>"></textarea>
            <br/><br/>
            <?php echo $var_helper1?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary add-bulk-caption-btn"><?php echo $lang['common'][20]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal add-text-post-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['browse'][15]?></h4>
      </div>
      <div class="modal-body">
      	<div class="row">
			<div class="col-lg-6">
                <div class="form-group">
                  <div>
                    <label><?php echo $lang['browse'][16]?></label>
                    <textarea class="form-control" id="add-text-post" placeholder="<?php echo $lang['browse'][17]?>"></textarea>
                  </div>
                </div>
                <div class="form-group">
                  <div>
                    <label><?php echo $lang['browse'][18]?><br/>(<?php echo $lang['browse'][19]?>)</label>
                    <input type="file" name="csv" />
                  </div>
                </div>
           </div>
           <div class="col-lg-6">
           		<?php echo $var_helper2?>
           </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary add-text-post-btn"><?php echo $lang['common'][20]?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal link-meta-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['link']['title']?></h4>
      </div>
      <form class="form" id="update_link_meta_form">
      <input type="hidden" name="link_meta_id" id="link_meta_id"/>
      <input type="hidden" name="save_link_meta" id="save_link_meta" value="1"/>
      <div class="modal-body">
      	<small><?php echo $lang['link']['when_work']?></small><br/><br/>
        <div class="form-group">
          <div>
            <label><?php echo $lang['link']['desc']?></label>
            <textarea class="form-control" name="link_meta_desc" id="link_meta_desc" placeholder="<?php echo $lang['link']['type_desc']?>"></textarea>
          </div>
        </div>
        <div class="form-group">
          <div>
            <label><?php echo $lang['link']['name']?></label>
            <input type="text" class="form-control" name="link_meta_title" id="link_meta_title" placeholder="<?php echo $lang['link']['type_title']?>" />
          </div>
        </div>
        <div class="form-group">
          <div>
            <label><?php echo $lang['link']['image']?></label>
            <input class="form-control" type="text" name="link_meta_image" id="link_meta_image" placeholder="<?php echo $lang['link']['type_image']?>"/>
          </div>
        </div>
        </form>
        <div class="modal-footer">
        	<button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        	<button type="button" class="btn btn-sm btn-primary update-link-meta-btn"><?php echo $lang['common'][20]?></button>
        </div>
      </div>
   </div>
  </div>
</div>

<?php include(dirname(__FILE__).'/meta.php');?>
<?php include(dirname(__FILE__).'/dashboard/modals.php');?>

<script src="<?php echo site_url()?>/js/select2.min.js"></script>
<script src="<?php echo site_url()?>/js/dropzone.js"></script>
<script src="<?php echo site_url()?>/js/jquery.ui.min.js"></script>
<script>Dropzone.autoDiscover = false;var editor_url = '<?php echo makeuri('plugins/media/editor.php')?>'</script>
<?php if(!empty($_GET['type'])){?>
<script>$('select[name="type"]').val('<?php echo purify_text($_GET['type'])?>')</script>
<?php }?>
<script>
$('#social_ids2, #sel_autocomplete').select2(); 
var minutes_selectors = [<?php echo implode(',', get_valid_schedule_intervals('minutes'))?>];
var hours_selectors = [<?php echo implode(',', get_valid_schedule_intervals('hours'))?>];
var days_selectors = [<?php echo implode(',', get_valid_schedule_intervals('days'))?>];
var weeks_selectors = [<?php echo implode(',', get_valid_schedule_intervals('weeks'))?>];
var months_selectors = [<?php echo implode(',', get_valid_schedule_intervals('months'))?>];
var years_selectors = [<?php echo implode(',', get_valid_schedule_intervals('years'))?>];
var hour_ranges = [<?php echo implode(',', get_valid_schedule_intervals('hour_ranges'))?>];
</script>