<?php
/**
 * @package Social Ninja
 * @version 1.5
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<!--modals-->
<div class="modal import-edited-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['imodal'][0]?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
          	<label><?php echo $lang['imodal'][1]?></label>
            <input class="form-control import-caption" />
          </div>
        </div>
        <div class="form-group">
          <div>
          	<label><?php echo $lang['imodal'][2]?></label>
            <input class="form-control import-name" />
          </div>
        </div>
        <div class="form-group">
          <div>
          	<label><?php echo $lang['imodal'][3]?></label>
            <select class="form-control import-folder">
            	<option value="">Select a folder</option>
            	<?php echo $auth->get_user_folders($user_id, 1, 5000, '', 1);?>
            </select>
          </div>
        </div>
        <small><?php echo $lang['imodal'][4]?></small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary import-edited-btn"><?php echo $lang['common'][20]?></button>
      </div>
    </div>
  </div>
</div>
