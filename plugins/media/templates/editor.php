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
    	<h3 class="text-center"><?php echo $lang['editor']['editor']['title']?></h3>
        <hr/>
        
        <div class="row">
			<div class="col-lg-12">
                <div id="dropzone" class="dropzone-editor">
                    <form action="<?php echo makeuri('upload.php')?>" class="dropzone" enctype="multipart/form-data">
                        <div class="dz-message">
                            <?php echo $lang['editor']['editor']['ddimgvid']?><br />
                        </div>
                    </form>
                </div>
            </div>
        </div>    
    </div>
</div>

<div class="row editor" id="editor">
    <div class="col-lg-6 editor-before">
        <h5><?php echo $lang['editor']['editor']['org']?></h5>
        <div class="editor-before-img">
        </div>
        <div>
            <br/>
            <button class="btn btn-sm btn-sm btn-success apply-opt show-resize"><i class="glyphicon glyphicon-resize-small"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['resize']?></button>
            <button class="btn btn-sm btn-sm btn-info apply-opt show-crop"><i class="glyphicon glyphicon-retweet"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['crop']?></button>
            <button class="btn btn-sm btn-sm btn-primary apply-opt add-effect"><i class="glyphicon glyphicon-eye-open"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['addeff']?></button>
            <button class="btn btn-sm btn-sm btn-warning apply-opt add-wm"><i class="glyphicon glyphicon-eye-open"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['addwmt']?></button>
            <button class="btn btn-sm btn-sm btn-danger apply-opt add-wm-img" style="margin-top:5px"><i class="glyphicon glyphicon-eye-open"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['addwmi']?></button>
            <button class="btn btn-sm btn-sm btn-success apply-opt import-edited-img" style="margin-top:5px"><i class="glyphicon glyphicon-download-alt"></i>&nbsp;&nbsp;<?php echo $lang['editor']['meme']['imp_my']?></button>
            
            <button class="btn btn-sm btn-sm btn-success save-resize"><i class="glyphicon glyphicon-ok"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['save']?></button>
            <button class="btn btn-sm btn-sm btn-danger cancel-resize"><i class="glyphicon glyphicon-remove"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['cancel']?></button>
            
            <button class="btn btn-sm btn-sm btn-success save-crop"><i class="glyphicon glyphicon-ok"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['save']?></button>
            <button class="btn btn-sm btn-sm btn-danger cancel-crop"><i class="glyphicon glyphicon-remove"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['cancel']?></button>
            
            <button class="btn btn-sm btn-sm btn-success save-effect"><i class="glyphicon glyphicon-ok"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['save']?></button>
            <button class="btn btn-sm btn-sm btn-danger cancel-effect"><i class="glyphicon glyphicon-remove"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['cancel']?></button>
            
            <button class="btn btn-sm btn-sm btn-success save-wm"><i class="glyphicon glyphicon-ok"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['save']?></button>
            <button class="btn btn-sm btn-sm btn-danger cancel-wm"><i class="glyphicon glyphicon-remove"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['cancel']?></button>
            
            <button class="btn btn-sm btn-sm btn-success save-wm-img"><i class="glyphicon glyphicon-ok"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['save']?></button>
            <button class="btn btn-sm btn-sm btn-danger cancel-wm-img"><i class="glyphicon glyphicon-remove"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['cancel']?></button>
        </div>
    </div>
    
    <div class="col-lg-6 col-md-offset-1 editor-after">
        <div class="div-1">
            <div class="div-11">
                <h5>
                    <?php echo $lang['editor']['meme']['edited_img']?>
                    <div class="pull-right pointer editorfullscreen-go">
                        <i class="glyphicon glyphicon-fullscreen"></i>&nbsp;&nbsp;<?php echo $lang['editor']['editor']['ff']?> 
                    </div>
                </h5>
                <div class="row">
                    <div class="col-lg-12 cropped-after-img">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 editor-after-img">
                    </div>
                    <div class="col-lg-12 editor-after-img-hidden">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 img-options">
                    </div>
                </div>
            </div>
        
            <div class="row effect-custom div-12">
            	<!--control for wm image-->
                <div class="col-lg-12 wm-img-controls">
                    <div class="row">
                        <div class="col-lg-4">
                        	<?php echo $lang['editor']['editor']['image']?>: 
                        	<select class="form-control wm-img-img">
                                <option value=""><?php echo $lang['common'][13]?></option>
                                <?php echo $auth->get_user_watermarks($user_id, 1)?>
                            </select>
                        </div>
                        <div class="col-lg-4"><?php echo $lang['editor']['editor']['position'];?>: <select class="wm-img-pos form-control medium-input"><option value="lowerRight">Bottom Right</option><option value="lowerLeft">Bottom Left</option><option value="upperRight">Top Right</option><option value="upperLeft">Top Left</option><option value="center">Center</option></select></div>
                        <div class="col-lg-2"><?php echo $lang['editor']['meme']['opacity'];?>: <input class="form-control small-input wm-img-opacity" value="0.5"></div>
                    </div>
                    <br/>			
                    <button class="btn btn-sm btn-sm btn-info wm-img-apply"><?php echo $lang['editor']['meme']['apply'];?></button>
                 </div> 
                <!--/control for wm image-->
                
                <!--control for wm text-->    
                <div class="col-lg-12 wm-controls">
                    <div class="row">
                        <div class="col-lg-4"><?php echo $lang['editor']['meme']['text'];?>: <input class="form-control medium-input wm-text" value=""></div>
                        <div class="col-lg-4"><?php echo $lang['editor']['editor']['position'];?>: <select class="wm-pos form-control medium-input"><option value="lowerRight">Bottom Right</option><option value="lowerLeft">Bottom Left</option><option value="upperRight">Top Right</option><option value="upperLeft">Top Left</option><option value="center">Center</option></select></div>
                        <div class="col-lg-3">
                        	<?php echo $lang['editor']['meme']['color']?>: <br/><input class="form-control small-input wm-color" value="#ffffff">
                        </div>
                    </div><br/>
                    <div class="row">
                        <div class="col-lg-2"><?php echo $lang['editor']['meme']['opacity'];?>: <input class="form-control small-input wm-opacity" value="0.5"></div>
                        <div class="col-lg-2"><?php echo $lang['editor']['meme']['fsize'];?>: <input class="form-control small-input wm-font-size" value="36"></div>
                        <div class="col-lg-4"><?php echo $lang['editor']['meme']['font'];?>: <select class="wm-font form-control medium-input"></select></div>
                        <div class="col-lg-2"><?php echo $lang['editor']['meme']['rotate'];?>: <input class="form-control small-input wm-rotate" value="0"></div>
                    </div>
                    
                    <div class="row meme-input">
                    	<br/><br/>
                        <div class="col-lg-4"><?php echo $lang['editor']['meme']['text'];?>: <input class="form-control medium-input wm-text-2" value=""></div>
                        <div class="col-lg-4"><?php echo $lang['editor']['editor']['position'];?>: <select class="wm-pos-2 form-control medium-input"><option value="lowerRight">Bottom Right</option><option value="lowerLeft">Bottom Left</option><option value="upperRight">Top Right</option><option value="upperLeft">Top Left</option><option value="center">Center</option></select></div>
                        <div class="col-lg-3">
                        	<?php echo $lang['editor']['meme']['color']?>: <br/><input class="form-control small-input wm-color-2" value="#ffffff">
                        </div>
                    </div><br/>
                    <div class="row meme-input">
                        <div class="col-lg-2"><?php echo $lang['editor']['meme']['opacity'];?>: <input class="form-control small-input wm-opacity-2" value="0.5"></div>
                        <div class="col-lg-2"><?php echo $lang['editor']['meme']['fsize'];?>: <input class="form-control small-input wm-font-size-2" value="36"></div>
                        <div class="col-lg-4"><?php echo $lang['editor']['meme']['font'];?>: <select class="wm-font-2 form-control medium-input"></select></div>
                        <div class="col-lg-2"><?php echo $lang['editor']['meme']['rotate'];?>: <input class="form-control small-input wm-rotate-2" value="0"></div>
                    </div>
                    
                    <br/>			
                    <button class="btn btn-sm btn-sm btn-info wm-apply"><?php echo $lang['editor']['meme']['apply'];?></button>
                    <button class="btn btn-sm btn-sm btn-warning wm-meme"><?php echo $lang['editor']['editor']['add_ano']?></button>
                </div>
                <!--/control for wm text-->
                
          		<!--control for effects-->
                <div class="col-lg-12 effect-controls">
                    <?php echo $lang['editor']['editor']['brightness']?><div class="pull-right slider-val brightness-val">0</div>
                    <div class="slider-1 slider" rel="brightness"></div><br/>
                    
                    <?php echo $lang['editor']['editor']['contrast']?><div class="pull-right slider-val contrast-val">0</div>
                    <div class="slider-1 slider" rel="contrast"></div><br/>
                    
                    <?php echo $lang['editor']['editor']['vignette']?><div class="pull-right slider-val vignette-val">0</div>
                    <div class="slider-2 slider" rel="vignette"></div><br/>
                    
                   <?php echo $lang['editor']['editor']['lc']?><div class="pull-right slider-val lighten-val">0</div>
                    <div class="slider-2 slider" rel="lighten"></div><br/>
                    
                    <?php echo $lang['editor']['editor']['desaturate']?><div class="pull-right slider-val desaturate-val">0</div>
                    <div class="slider-2 slider" rel="desaturate"></div><br/>
                    
                    <?php echo $lang['editor']['editor']['noise']?><div class="pull-right slider-val noise-val">0</div>
                    <div class="slider-3 slider" rel="noise"></div><br/>
                    
                    <?php echo $lang['editor']['editor']['ts']?><div class="pull-right slider-val tint-val">0</div>
                    <div class="slider-2 slider" rel="tint"></div><br/>
                    
                    <?php echo $lang['editor']['editor']['tc']?><br/>
                    <input class="form-control input-small tint-color" /><br/>
                    <br/><br/>
                    <?php echo $lang['editor']['editor']['vf']?> <a href="<?php echo makeuri('plugins/media/tools.php')?>" target="_blank"><?php echo $lang['editor']['editor']['add_new_vf']?></a>
                    <select class="form-control view_finder">
                    	<option value=""><?php echo $lang['common'][13]?></option>
                        <?php echo $auth->get_user_frames($user_id, 1)?>
                    </select><br/>
                    
                    Speia
                    <div class="onoffswitch">
                        <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox slider-check" id="sepia">
                        <label class="onoffswitch-label" for="sepia"></label>
                    </div>
                </div>
                <!--/control for effects-->
            </div>
        </div>
    </div>
</div>

<div class="row v-editor" id="v-editor">
	<div class="col-lg-6">
    	<div id="vplayer" class="center-text"></div><br/>
        <div class="row">
        	<div class="col-lg-6">
            	<label><?php echo $lang['editor']['editor']['pname']?></label>
                <input class="form-control p_name" value="Project <?php echo date('d-M-Y H:i:s')?>"/>
            </div>
            <div class="col-lg-2">
                <label><?php echo $lang['editor']['editor']['ptime']?></label>
                <input class="form-control jplay_time" style="width:90px"/>
            </div>
            <div class="col-lg-3">
                <label><?php echo $lang['editor']['editor']['jto']?></label>
                <input class="form-control jplay_jump" style="width:100px" placeholder="<?php echo $lang['editor']['editor']['penter']?>"/>
            </div>
         </div><br/>
        <button class="btn btn-sm btn-sm btn-info" onclick="jp_instance.play()">
        	<i class="glyphicon glyphicon-play"></i>
        </button>&nbsp;&nbsp;
        <button class="btn btn-sm btn-sm btn-danger" onclick="jp_instance.pause()">
        	<i class="glyphicon glyphicon-pause"></i>
        </button>&nbsp;&nbsp;
        <button class="btn btn-sm btn-success" onclick="jp_instance.stop()">
        	<i class="glyphicon glyphicon-stop"></i>
        </button>&nbsp;&nbsp;
        <button class="btn btn-sm btn-warning jp_fiveb" title="<?php echo $lang['editor']['editor']['jfb']?>">
        	<i class="glyphicon glyphicon-fast-backward"></i>
        </button>&nbsp;&nbsp;
        <button class="btn btn-sm btn-info jp_fivef" title="<?php echo $lang['editor']['editor']['jff']?>">
        	<i class="glyphicon glyphicon-fast-forward"></i>
        </button>&nbsp;&nbsp;
        <button class="btn btn-sm btn-success jp_oneb" title="<?php echo $lang['editor']['editor']['jsb']?>">
        	<i class="glyphicon glyphicon-step-backward"></i>
        </button>&nbsp;&nbsp;
        <button class="btn btn-sm btn-danger jp_onef" title="<?php echo $lang['editor']['editor']['jsf']?>">
        	<i class="glyphicon glyphicon-step-forward"></i>
        </button>&nbsp;&nbsp;<br/><br/>
        <button class="btn btn-sm btn-success take_screenshot"><?php echo $lang['editor']['editor']['tshot']?></button>
        <button class="btn btn-sm btn-danger create_tile"><?php echo $lang['editor']['editor']['ctile']?></button>
        <button class="btn btn-sm btn-info start_segment"><?php echo $lang['editor']['editor']['sseg']?></button>
        <button class="btn btn-sm btn-warning end_segment"><?php echo $lang['editor']['editor']['eseg']?></button>
    </div>
    <div class="col-lg-6">
    	<h4><?php echo $lang['editor']['editor']['pending_op']?></h4>
        <hr />
        <table class="table v-pending">
        </table>
        <button class="btn btn-sm btn-primary pending_submit" style="display:none"><?php echo $lang['editor']['editor']['stask']?></button><br/><br/>
        <h4><?php echo $lang['editor']['editor']['segments']?></h4>
        <hr />
        <table class="table v-segments">
        </table>
    </div>
</div>

<div class="modal tile-size-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['editor']['editor']['tsize']?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
            <input class="form-control" id="tsize" placeholder="<?php echo $lang['editor']['editor']['tsize_format']?>" type="text" value="4x4">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary tsize-btn"><?php echo $lang['common'][20]?></button>
      </div>
    </div>
  </div>
</div>
<script src="jw/jwplayer.js"></script>
<script type="text/javascript">jwplayer.key="jFaJ+nEsCSJSMeoX6qyR8X/9qHYPWroRAP1nVg==";var jp_instance = '';</script>

<?php 
if(!empty($_GET['create_wm']) || !empty($_GET['file_id'])){
	if(!empty($_GET['create_wm'])){
		$wm = __ROOT__.'/images/500_transparent.png';
		$f = 'tmp/'.time().rand(111111, 999999).'.png';
		copy($wm, __CURDIR__.'/'.$f);	
	}
	else if(!empty($_GET['file_id'])){
		$file_id = sql_real_escape_string($_GET['file_id']);
		
		if($auth->is_file_owner($user_id, $file_id)){
			list($file_type, $path, $org_name) = sql_fetch_row(sql_query("SELECT file_type, filename, original_name FROM files WHERE file_id = '$file_id'"));
			if($file_type == 'image'){
				$f = site_url().'/storage/'.$user_data['storage'].'/'.$path;
			}
			else $v = site_url().'/storage/'.$user_data['storage'].'/'.$path;
		}
	}
	
	if(!empty($f)){
		if($settings['image_editor_enabled']){	
?>
			<script>
            var trans500 = '<?php echo $f?>';
            $('.editor-before-img').html('<img src="'+trans500+'" style="max-width:420px"/>');
            $('.editor-after-img').html('<img src="'+trans500+'" style="max-width:420px"/>');
            <?php if(!empty($wm)){?>$('.editor-before, .editor-after').addClass('wmtrans');<?php }?>
            $('.editor').show();
            $('#up_files').val($('#up_files').val()+ ',' +trans500);
            window.location.href = window.location.href.replace(location.hash,"") + '#editor';
            </script>
<?php   }
		else{ 
			echo '<div class="alert alert-danger">Image editor is disabled</div>';
		}
    }
	else if(!empty($v)){
		if($settings['video_editor_enabled']){
?>
			<script>
			jp_instance = jwplayer("vplayer").setup({
				file: '<?php echo $v?>',
				provider: "http",
				primary: 'html5'
			});
			jp_instance.on('time', function(t){
				if(t != null){
					var date = new Date(null);
					date.setSeconds(t.position);
					var p = date.toISOString().substr(11, 8);
					$('.jplay_time').val(p);		
				}
			});
			$('.v-editor').show();
			$('.v-pending, .v-segments').html('');
			$('.pending_submit').hide();
			$('.p_name').val('<?php echo $org_name?>')
			window.location.href = window.location.href.replace(location.hash,"") + '#v-editor';
			</script>
<?php	}
		else{ 
			echo '<div class="alert alert-danger">'.$lang['editor']['editor']['v_disabled'].'</div>';
		}
	}
}
?>