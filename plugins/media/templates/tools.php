<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<h4><?php echo $lang['editor']['tools']['title']?></h4>
<hr/>
<div class="row">
    <div class="col-lg-6">
        <div id="dropzone" class="dropzone-tools">
            <form action="<?php echo makeuri('upload.php')?>" class="dropzone" enctype="multipart/form-data">
                <div class="dz-message">
                    <?php echo $lang['editor']['tools']['ddwm']?><br />
                </div>
                <input type="hidden" name="wm" value="1">
            </form>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div id="dropzone" class="dropzone-tools">
            <form action="<?php echo makeuri('upload.php')?>" class="dropzone" enctype="multipart/form-data">
                <div class="dz-message">
                    <?php echo $lang['editor']['tools']['ddvf']?><br />
                </div>
                <input type="hidden" name="frame" value="1">
            </form>
        </div>
    </div>  
</div>     

<div class="row">
    <div class="col-lg-12">
    	<hr/>
        <h4><?php echo $lang['editor']['tools']['create_slide']?></h4>
        <div class="row">
        	<div class="col-lg-3">
            	<label><?php echo $lang['editor']['tools']['sel_f']?></label>
                <select name="folder_id" id="folder_id" class="form-control">
                    <option value=""><?php echo $lang['common'][13]?></option>
                    <?php echo $auth->get_user_folders($user_id, 1, 5000, '', 2)?>
                    <?php echo $auth->get_user_rss($user_id, 1, 5000, '', 2)?>
                </select>
            </div>
            <div class="col-lg-3">
            	<label><?php echo $lang['editor']['tools']['slide_dur']?></label>
                <select class="form-control" name="slide_duration" id="slide_duration">
                    <option value="3">3 second</option>
                    <option value="4">4 second</option>
                    <option value="5">5 second</option>
                    <option value="6">6 second</option>
                    <option value="7">7 second</option>
                    <option value="8">8 second</option>
                    <option value="9">9 second</option>
                    <option value="10">10 second</option>
                </select>
            </div>
            <div class="col-lg-3">
				<label><?php echo $lang['editor']['tools']['slide_type']?></label>
                <select class="form-control" name="slide_type" id="slide_type">
                    <?php echo get_available_slideshow_type()?>
                </select>            
            </div>
            <div class="col-lg-3">
				<button class="btn btn-sm btn-info create_slideshow" style="margin-top:24px"><?php echo $lang['js']['create']?></button>            
            </div>
        </div>
    </div>
</div>        

<div class="row">
    <div class="col-lg-12">
    	<hr/>
        <h4><?php echo $lang['editor']['tools']['create_wm']?></h4>
        <?php echo $lang['editor']['tools']['try_it']?> <a href="<?php echo makeuri('plugins/media/editor.php?create_wm=1')?>"><?php echo $lang['common'][25]?></a>
    </div>
</div>        
<hr/>

<div class="row">
	<div class="col-lg-12">
    	<h3><?php echo $lang['editor']['tools']['yourwm']?></h3>
        <?php
		$wms = $auth->get_user_watermarks($user_id);
		if(empty($wms))echo '<div class="alert alert-danger">'.$lang['editor']['tools']['nowm'].'</div>';
		else{
			$j = 0;
			foreach($wms as $wm){
				if(!$j)echo '<div class="row">';
				echo '<div class="col-lg-2" id="tool-'.$wm['tool_id'].'" rel="'.$wm['tool_id'].'">
						<div style="overflow:hidden">
							<img src="'.site_url().'/storage/'.$user_data['storage'].'/'.$wm['filename'].'" width="100px" class="wmtrans"/><br/><br/>
							<button class="btn btn-sm btn-warning delete_tool">'.$lang['js']['delete'].'</button>
						</div>
					 </div>';
				$j++;
				if($j >= 6){
					echo '</div><br/>';
					$j = 0;	
				}	
			}
			if($j)echo '</div><br/>';	
		}
		?>
    </div>
</div>

<div class="row">
	<div class="col-lg-12">
    	<h3><?php echo $lang['editor']['tools']['yourview']?></h3>
        <?php
		$frames = $auth->get_user_frames($user_id);
		if(empty($frames))echo '<div class="alert alert-danger">'.$lang['editor']['tools']['noframe'].'</div>';
		else{
			$j = 0;
			foreach($frames as $frame){
				if(!$j)echo '<div class="row">';
				echo '<div class="col-lg-2" id="tool-'.$frame['tool_id'].'" rel="'.$frame['tool_id'].'">
						<div style="overflow:hidden">
							<img src="'.site_url().'/storage/'.$user_data['storage'].'/'.$frame['filename'].'" width="100px"/><br/><br/>
							<button class="btn btn-sm btn-warning delete_tool">'.$lang['js']['delete'].'</button>
						</div>
					 </div>';
				$j++;	 
				if($j >= 6){
					echo '</div><br/>';
					$j = 0;	
				}	
			}
			if($j)echo '</div><br/>';	
		}
		?>
    </div>
</div>

<script src="<?php echo site_url()?>/js/dropzone.js"></script>
<script>Dropzone.autoDiscover = false;</script>