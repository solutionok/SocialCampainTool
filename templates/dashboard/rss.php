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
        <h3><?php echo $lang['dashboard'][30]?>
            <div class="pull-right">
                <div class="row pull-up">
                    <div class="col-lg-6">
                        <form class="search">
                            <input class="form-control submit-enter" name="q" placeholder="<?php echo $lang['dashboard'][31]?>"
                                value="<?php if(@$_GET['show'] == 'rss')echo @purify_text($_GET['q'])?>"/>
                            <input type="hidden" name="show" value="rss" />
                        </form>
                    </div>
                    <div class="col-lg-6">
                        <button class="btn btn-primary" onclick="$('.add-rss-modal').modal()"><?php echo $lang['dashboard'][32]?></button>
                    </div>
                </div>
            </div>
        </h3>
        <?php
        $rss_count = $auth->count_user_rss($user_id);
        if(!$rss_count)echo '<div class="alert alert-warning">'.$lang['dashboard'][33].'</div>';
        else{
            $from = 1;
            $rows = 40;
            $name = '';
            
            if(!empty($_GET['show']) && $_GET['show'] == 'rss'){
                if(!empty($_GET['from'])){
                    $from = (int)$_GET['from'];	
                    if($from < 1)$from = 1;
                }
                if(!empty($_GET['q'])){
                    $name = sql_real_escape_string($_GET['q']);
                }
                if(!empty($name))$rss_count = $auth->count_user_rss($user_id, $name);
            }
            
            if(empty($rss_count))echo '<div class="alert alert-warning">'.$lang['dashboard'][33].'</div>';
            else{
                echo '<h4>
						'.$rss_count.' '.$lang['dashboard'][34].'
					  </h4>
					  	<button class="btn btn-sm btn-default sel_all" rel="rss">'.$lang['js']['select_all'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-primary inv_sel" rel="rss">'.$lang['js']['inv_selected'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-danger del_selected" rel="rss">'.$lang['js']['del_selected'].'</button><br/><br/>
						<hr/>';
                $rsss = $auth->get_user_rss($user_id, $from, $rows, $name);
                $i = 0;
                foreach($rsss as $rss){
                    if(!$i)echo '<div class="row">';
                    echo '<div class="col-lg-2 rss rss-'.$rss['rss_feed_id'].'" rel="'.$rss['rss_feed_id'].'">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h5>
										<input type="checkbox" class="rss-checkbox">&nbsp;&nbsp;
                                        <a href="'.$rss['rss_url'].'" target="_blank">
                                            '.$rss['feed_name'].'
                                        </a>
                                    </h5>
                                    &nbsp;
                                    <i class="glyphicon glyphicon-edit pointer rss-edit" title="'.$lang['dashboard'][35].'"></i>&nbsp;
                                    <i class="glyphicon glyphicon-trash pointer rss-delete" title="'.$lang['dashboard'][36].'"></i>&nbsp;
                                </div>
                            </div>
                          </div>';
                    $i++;
                    if($i >= 6){
                        echo '</div><br/>';
                        $i = 0;	
                    }	
                }	
                if($i)echo '</div>';
                echo '<hr />';
				$g = $_GET;
				unset($g['show']);
                echo pagination($rss_count, $rows, $from, http_build_query($g), makeuri('dashboard.php?show=rss'));
            }
        }
        ?>
    </div>
</div>