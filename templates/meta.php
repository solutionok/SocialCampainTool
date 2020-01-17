<?php
/**
 * @package Social Ninja
 * @version 1.6
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="modal file-meta-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['meta'][0]?></h4>
      </div>
      <form class="form" id="update_file_meta_form">
      <input type="hidden" name="file_meta_id" id="file_meta_id"/>
      <input type="hidden" name="save_file_meta" id="save_file_meta" value="1"/>
      <div class="modal-body">
        <div class="form-group">
          <div>
            <label><?php echo $lang['meta'][1]?></label>
            <textarea class="form-control" name="file_meta_desc" id="file_meta_desc" placeholder="<?php echo $lang['browse'][17]?>"></textarea>
          </div>
        </div>
        <div class="form-group">
          <div>
            <label><?php echo $lang['meta'][2]?></label>
            <input class="form-control" type="text" name="file_meta_tags" id="file_meta_tags" placeholder="<?php echo $lang['meta']['sep_comma']?>"/>
          </div>
        </div>
        <div class="form-group">
          <div>
            <label><?php echo $lang['meta'][3]?></label>
            <select class="form-control" name="file_meta_category" id="file_meta_category"><option value=""><?php echo $lang['common'][13]?></option><?php echo get_yt_cats()?></select>
          </div>
        </div>
        <div class="form-group">
          <div>
            <label><?php echo $lang['meta'][4]?></label>
            <select class="form-control" name="file_meta_privacy" id="file_meta_privacy"><option value=""><?php echo $lang['common'][13]?></option><?php echo get_yt_privacy()?></select>
          </div>
        </div>
      </div>
      </form>
      <div class="modal-footer">
        <a class="btn btn-sm btn-success"><?php echo $lang['browse'][10]?></a>
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary update-file-meta-btn"><?php echo $lang['common'][20]?></button>
      </div>
    </div>
  </div>
</div>