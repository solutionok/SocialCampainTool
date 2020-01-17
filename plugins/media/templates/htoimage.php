<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
	<div class="col-lg-6">
    	<h4>
        	<?php echo $lang['editor']['hto']['preview']?>
        	<div class="pull-right">
            	<button class="btn btn-sm btn-info cresize">
                	<i class="glyphicon glyphicon-retweet"></i>&nbsp;&nbsp;<?php echo $lang['editor']['hto']['resize']?>
                </button>
                <button class="btn btn-sm btn-success import-edited-img-int">
                	<i class="glyphicon glyphicon-download-alt"></i>&nbsp;&nbsp;<?php echo $lang['editor']['meme']['imp_my']?>
                </button>
            </div>
        </h4>
        <hr>
        <div class="editor-before-img" style="display:none"></div>
    	<canvas width="500" height="500" id="mycanvas"></canvas>
    </div>
    <div class="col-lg-6">
    	<div class="alert alert-warning"><?php echo $lang['editor']['hto']['nolarge']?></div>
    	<textarea class="form-control" id="text"  style="height:300px" maxlength="50000"></textarea><br/><br/>
    	<button class="btn btn-sm btn-info html-apply"><?php echo $lang['editor']['meme']['apply']?></button>
    </div>
</div>
<button class="import-edited-img" style="display:none"></button>