<?php
/**
 * @package Social Ninja
 * @version 1.2
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<?php include(__ROOTDIR__.'/templates/import_modal.php')?>
<input type="hidden" id="import-edited-type" value="">
<input type="hidden" id="ftrans" value="0">
<input type="hidden" id="edited-img" value="0">
<input type="hidden" id="edited-vid" value="0">
<input type="hidden" id="up_files" value="">
<input type="hidden" id="imw" value="">
<input type="hidden" id="imh" value="">
<div class="tmpdiv"></div>
<script>var upload_url_tmp = 'upload.php';</script>
<script src="<?php echo site_url()?>/js/jquery.ui.min.js"></script>
<script src="js/colorpicker.js"></script>
<script src="js/cropper.min.js"></script>
<script src="js/jquery.vintage.min.js"></script>
<script src="js/vintage.presets.js"></script>
<script src="<?php echo site_url()?>/js/dropzone.js"></script>
<script src="js/slider.js"></script>
<script src="js/watermark.js"></script>
<script src="js/easeljs.js"></script>
<script src="js/rasterizeHTML.js"></script>
<script src="js/custom.js?v=1.3"></script>
<script>Dropzone.autoDiscover = false;</script>