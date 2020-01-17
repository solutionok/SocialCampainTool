<?php
/**
 * @package Social Ninja
 * @version 1.2
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="modal lang-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['common'][47]?></h4>
      </div>
      <div class="modal-body">
      	<label><?php echo $lang['js']['select_lang']?></label>
        <select id="select_lang" class="form-control">
        	<option value=""><?php echo $lang['common'][13]?></option>
            <?php
			$lfiles = list_lang_files();
			foreach($lfiles as $lfile)echo '<option value="'.$lfile.'">'.$lfile.'</option>';
			?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-success save_lang"><?php echo $lang['common'][20]?></button>
      </div>
    </div>
  </div>
</div>

  <footer>
  	<hr/>
    <div class="row">
      <div class="col-lg-12">

        <ul class="list-unstyled">
          <li class="pull-right"><a href="javascript:void(0)" onclick='$("html, body").animate({ scrollTop: 0 }, "slow");'><?php echo $lang['index'][36]?></a></li>
          <li class="pull-right"><a href="javascript:void(0)" onclick='$(".lang-modal").modal()'><?php echo $lang['common'][47]?></a></li>
          <?php if(!empty($settings['admin_email'])){?>
          <li class="pull-right"><a href="<?php echo secure_email($settings['admin_email'])?>"><?php echo $lang['footer']['contactus']?></a></li>
          <?php }?>
          <li>&copy; <?php echo $settings['site_name'].' '.date('Y')?></li>
        </ul>
      </div>
    </div>

  </footer>
</div>
<!--/container-->
<script>var dtime = new Date(<?php echo (time() + date('Z')) * 1000;?>);</script>
<script src="<?php echo site_url()?>/js/jquery.ui.min.js"></script>
<script src="<?php echo site_url()?>/js/core.js?v=2.8"></script>
<script src="<?php echo site_url()?>/css/themes/assets/js/bootswatch.js"></script>
</body>
</html>
<?php sql_close();?>