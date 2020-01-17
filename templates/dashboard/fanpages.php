<?php
/**
 * @package Social Ninja
 * @version 1.1
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
    <div class="col-lg-12">
        <h3>
            <?php echo $lang['dashboard'][15]?>
            <div class="pull-right">
                <div class="row pull-up">
                    <div class="col-lg-12">
                        <form class="search">
                            <input class="form-control submit-enter" name="q" placeholder="<?php echo $lang['dashboard'][22]?>" 
                                value="<?php if(@$_GET['show'] == 'fanpages')echo @purify_text($_GET['q'])?>"/>
                            <input type="hidden" name="show" value="fanpages" />
                        </form>
                    </div>
                </div>
            </div>
        </h3>
        <?php
        $page_count = $auth->count_user_pages($user_id);
        if(!$page_count)echo '<div class="alert alert-warning">'.$lang['dashboard'][16].'</div>';
        else{
            $from = 1;
            $rows = 40;
            $name = '';
            
            if(!empty($_GET['show']) && $_GET['show'] == 'fanpages'){
                if(!empty($_GET['from'])){
                    $from = (int)$_GET['from'];	
                    if($from < 1)$from = 1;
                }
                if(!empty($_GET['q'])){
                    $name = sql_real_escape_string($_GET['q']);
                }
                if(!empty($name))$page_count = $auth->count_user_pages($user_id, $name);
            }
            
            if(empty($page_count))echo '<div class="alert alert-warning">'.$lang['dashboard'][16].'</div>';
            else{
                echo '<h4>
						'.$page_count.' '.$lang['dashboard'][17].'
					  </h4>
					  <button class="btn btn-sm btn-default sel_all" rel="fanpages">'.$lang['js']['select_all'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-primary inv_sel" rel="fanpages">'.$lang['js']['inv_selected'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-danger del_selected" rel="fanpages">'.$lang['js']['del_selected'].'</button>
						<hr/>';
                $pages = $auth->get_user_pages($user_id, $from, $rows, $name);
                $i = 0;
                foreach($pages as $page){
					$name = $page['page_name'];
					if($page['account_status'] != 1)$name = '<strike>'.$name.'</strike>';
                    if(!$i)echo '<div class="row">';
                    echo '<div class="col-lg-3 pages page-'.$page['page_id'].'" rel="'.$page['page_id'].'" rel-o="'.$page['fb_id'].'">
                            <div class="row">
                                <div class="col-lg-4" style="min-height:85px">
                                    <img src="https://graph.facebook.com/'.$page['page_id'].'/picture?type=normal&access_token='.$page['access_token'].'" width="80px"/>
                                </div>
                                <div class="col-lg-8">
                                    <h5>
										<input type="checkbox" class="fanpage-checkbox">&nbsp;&nbsp;
                                        <a href="//facebook.com/'.$page['page_id'].'" target="_blank">
                                            '.$name.'
                                        </a>
                                    </h5>
                                    <span class="label label-info">'.$page['likes'].' likes</span>
                                    <br/>
                                    <span class="label label-info">'.$page['category'].'</span>
                                    &nbsp;
                                </div>
                            </div>
                            Owned by <a href="//facebook.com/'.$page['fb_id'].'" target="_blank">'.$page['first_name'].' '.$page['last_name'].'</a>
                            &nbsp;&nbsp;<i class="glyphicon glyphicon-trash pp_del"></i>
                          </div>';
                    $i++;
                    if($i >= 4){
                        echo '</div><br/><br/>';
                        $i = 0;	
                    }	
                }	
                if($i)echo '</div>';
                echo '<hr />';
				$g = $_GET;
				unset($g['show']);
                echo pagination($page_count, $rows, $from, http_build_query($g), makeuri('dashboard.php?show=pages'));
            }
        }
        ?>
    </div>
</div>