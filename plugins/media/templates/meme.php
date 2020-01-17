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
	<div class="col-lg-12">
    	<h3 class="text-center"><?php echo $lang['editor']['meme']['title']?></h3>
        <hr/>
        
        <div class="row">
			<div class="col-lg-12">
                <div id="dropzone" class="dropzone-editor meme-editor" accept="image/*">
                    <form action="<?php echo makeuri('upload.php')?>" class="dropzone" enctype="multipart/form-data">
                    	<input type="hidden" name="meme" value="1">
                        <div class="dz-message">
                            <?php echo $lang['editor']['meme']['ddimg']?><br />
                        </div>
                    </form>
                </div>
            </div>
        </div>    
    </div>
</div>

<div class="row editor" id="editor">    
    <div class="col-lg-6">
        <h5>
            <?php echo $lang['editor']['meme']['edited_img']?>
        </h5>
        <div>
            <canvas id="mycanvas" class="editor-after-canvas"></canvas>
            <canvas id="hcanvas" class="editor-after-canvas" style="display:none" width="800px"></canvas>
            <canvas id="hcanvas2" class="editor-after-canvas" style="display:none" width="800px"></canvas>
            <div class="editor-after-img" style="display:none"></div>
            <div class="editor-before-img" style="display:none"></div>
        </div> 
        <br/>
        <button class="btn btn-success apply-opt import-edited-img-int" style="display:block !important"><i class="glyphicon glyphicon-download-alt"></i>&nbsp;&nbsp;<?php echo $lang['editor']['meme']['imp_my']?></button>		
    </div>
    
    <div class="col-lg-6">
    	<!--control for wm image-->
        <div class="row">                    
             <!--control for wm text-->    
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-lg-4"><?php echo $lang['editor']['meme']['text1']?>: <input class="form-control medium-input wm-text" value=""></div>
                    <div class="col-lg-2">
                        <?php echo $lang['editor']['meme']['color']?>: <br/><input class="form-control small-input tint-color wm-col" value="">
                    </div>
                    <div class="col-lg-2"><?php echo $lang['editor']['meme']['opacity']?>: <input class="form-control small-input wm-opacity" value="1"></div>
                    <div class="col-lg-2"><?php echo $lang['editor']['meme']['fsize']?>: <input class="form-control small-input wm-font-size" value="36"></div>
                </div><br/>
                <div class="row">
                    <div class="col-lg-4"><?php echo $lang['editor']['meme']['font']?>: <select class="wm-font form-control medium-input"></select></div>
                    <div class="col-lg-2"><?php echo $lang['editor']['meme']['rotate']?>: <input class="form-control small-input wm-rotate" value="0"></div>
                </div>
                
               
                <div class="row">
                    <br/><br/>
                    <div class="col-lg-4"><?php echo $lang['editor']['meme']['text2']?>: <input class="form-control medium-input wm-text-2" value=""></div>
                    <div class="col-lg-2">
                        <?php echo $lang['editor']['meme']['color']?>: <br/><input class="form-control small-input tint-color wm-col-2" value="">
                    </div>
                    <div class="col-lg-2"><?php echo $lang['editor']['meme']['opacity']?>: <input class="form-control small-input wm-opacity-2" value="1"></div>
                    <div class="col-lg-2"><?php echo $lang['editor']['meme']['fsize']?>: <input class="form-control small-input wm-font-size-2" value="36"></div>
                </div><br/>
                <div class="row">
                    <div class="col-lg-4"><?php echo $lang['editor']['meme']['font']?>: <select class="wm-font-2 form-control medium-input"></select></div>
                    <div class="col-lg-2"><?php echo $lang['editor']['meme']['rotate']?>: <input class="form-control small-input wm-rotate-2" value="0"></div>
                </div>
                
                <br/>			
                <button class="btn btn-info wmm-apply"><?php echo $lang['editor']['meme']['apply']?></button>
                <button class="btn btn-danger wmm-reset"><?php echo $lang['editor']['meme']['reset']?></button>
            </div>
            <!--/control for wm text-->
        </div>        
	</div>
</div>
<button class="import-edited-img" style="display:none"></button>

<?php if(!empty($_GET['file_id'])){
		$file_id = sql_real_escape_string($_GET['file_id']);
		
		if($auth->is_file_owner($user_id, $file_id)){
			list($path) = sql_fetch_row(sql_query("SELECT filename FROM files WHERE file_id = '$file_id'"));
			$f = site_url().'/storage/'.$user_data['storage'].'/'.$path;	
		}
	}
?>
<script>
var preload_file = '<?php echo empty($f) ? '' : $f?>';
</script>