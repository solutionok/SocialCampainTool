<?php
/**
 * @package Social Ninja
 * @version 1.5
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
    <div class="col-lg-12">
        <h3><?php echo $lang['dashboard'][8]?>
            <div class="pull-right">
                <div class="row pull-up">
                    <div class="col-lg-6">
                        <form class="search">
                            <input class="form-control submit-enter" name="q" placeholder="<?php echo $lang['dashboard'][25]?>"
                                value="<?php if(@$_GET['show'] == 'folders')echo @purify_text($_GET['q'])?>"/>
                            <input type="hidden" name="show" value="folders" />
                        </form>
                    </div>
                    <div class="col-lg-6">
                        <button class="btn btn-primary" onclick="$('.create-folder-modal').modal()"><?php echo $lang['dashboard'][9]?></button>
                    </div>
                </div>
            </div>
        </h3>
        <?php
        $folder_count = $auth->count_user_folders($user_id);
        if(!$folder_count)echo '<div class="alert alert-warning">'.$lang['dashboard'][10].'</div>';
        else{
            $from = 1;
            $rows = 24;
            $name = '';
            
            if(!empty($_GET['show']) && $_GET['show'] == 'folders'){
                if(!empty($_GET['from'])){
                    $from = (int)$_GET['from'];	
                    if($from < 1)$from = 1;
                }
                if(!empty($_GET['q'])){
                    $name = sql_real_escape_string($_GET['q']);
                }
                if(!empty($name))$folder_count = $auth->count_user_folders($user_id, $name);
            }
            
            if(empty($folder_count))echo '<div class="alert alert-warning">'.$lang['dashboard'][11].'</div>';
            else{
                echo '<h4>
						'.$folder_count.' '.$lang['dashboard'][12].'
					  </h4>
					  	<button class="btn btn-sm btn-default sel_all" rel="folders">'.$lang['js']['select_all'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-primary inv_sel" rel="folders">'.$lang['js']['inv_selected'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-danger del_selected" rel="folders">'.$lang['js']['del_selected'].'</button><br/><br/>
						<hr/>';
                $folders = $auth->get_user_folders($user_id, $from, $rows, $name);
                $i = 0;
                foreach($folders as $folder){
                    if(!$i)echo '<div class="row">';
                    echo '<div class="col-lg-3 folders folder-'.$folder['folder_id'].'" rel="'.$folder['folder_id'].'">
                            <div class="row">
                                <div class="col-lg-4 folder-thumb-preview" 
                                    style="background:url(\''.($folder['thumb'] ? 'storage/'.$folder['thumb'] : 'images/folder.png').'\')">
                                </div>
                                <div class="col-lg-8">
                                    <h5>
										<input type="checkbox" class="folder-checkbox">&nbsp;&nbsp;
                                        <a href="'.makeuri('browse.php?fid='.$folder['folder_id']).'">
                                            '.$folder['folder_name'].'
                                        </a>
                                    </h5>
                                    <span class="label label-info">'.$folder['file_count'].' files</span>
                                    &nbsp;
                                    <i class="glyphicon glyphicon-edit pointer folder-edit" title="'.$lang['dashboard'][13].'"></i>&nbsp;
                                    <i class="glyphicon glyphicon-trash pointer folder-delete" title="'.$lang['dashboard'][14].'"></i>&nbsp;
                                </div>
                            </div>
                          </div>';
                    $i++;
                    if($i >= 4){
                        echo '</div><br/>';
                        $i = 0;	
                    }	
                }	
                if($i)echo '</div>';
                echo '<hr />';
				$g = $_GET;
				unset($g['show']);
                echo pagination($folder_count, $rows, $from, http_build_query($g), makeuri('dashboard.php?show=folders'));
            }
        }
        ?>
    </div>
</div>