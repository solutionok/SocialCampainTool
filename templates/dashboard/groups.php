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
        <h3>
            <?php echo $lang['dashboard'][18]?>
            <div class="pull-right">
                <div class="row pull-up">
                    <div class="col-lg-12">
                        <form class="search">
                            <input class="form-control submit-enter" name="q" placeholder="<?php echo $lang['dashboard'][23]?>"
                                value="<?php if(@$_GET['show'] == 'groups')echo @purify_text($_GET['q'])?>"/>
                            <input type="hidden" name="show" value="groups" />
                        </form>
                    </div>
                </div>
            </div>
        </h3>
        <?php
        $group_count = $auth->count_user_groups($user_id);
        if(!$group_count)echo '<div class="alert alert-warning">'.$lang['dashboard'][16].'</div>';
        else{
            $from = 1;
            $rows = 40;
            $name = '';
            
            if(!empty($_GET['show']) && $_GET['show'] == 'groups'){
                if(!empty($_GET['from'])){
                    $from = (int)$_GET['from'];	
                    if($from < 1)$from = 1;
                }
                
                if(!empty($_GET['q'])){
                    $name = sql_real_escape_string($_GET['q']);
                }
                if(!empty($name))$group_count = $auth->count_user_groups($user_id, $name);
            }
            
            if(empty($group_count))echo '<div class="alert alert-warning">'.$lang['dashboard'][16].'</div>';
            else{
                echo '<h4>
						'.$group_count.' '.$lang['dashboard'][19].'
					  </h4>
						<button class="btn btn-sm btn-default sel_all" rel="groups">'.$lang['js']['select_all'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-primary inv_sel" rel="groups">'.$lang['js']['inv_selected'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-danger del_selected" rel="groups">'.$lang['js']['del_selected'].'</button>
					  <hr/>';
                $groups = $auth->get_user_groups($user_id, $from, $rows, $name);
                $i = 0;
                foreach($groups as $group){
					$name = $group['group_name'];
					if($group['account_status'] != 1)$name = '<strike>'.$name.'</strike>';
                    if(!$i)echo '<div class="row">';
                    echo '<div class="col-lg-3 groups group-'.$group['group_id'].'" rel="'.$group['group_id'].'" rel-o="'.$group['fb_id'].'">
                            <div class="row">
                                <div class="col-lg-4" style="min-height:85px">
                                    <img src="https://graph.facebook.com/'.$group['group_id'].'/picture?type=normal&access_token='.$group['access_token'].'" width="80px"/>
                                </div>
                                <div class="col-lg-8">
                                    <h5>
                                        <input type="checkbox" class="group-checkbox">&nbsp;&nbsp;
										<a href="//facebook.com/'.$group['group_id'].'" target="_blank">
                                            '.$name.'
                                        </a>
                                    </h5>
                                    <span class="label label-info">'.$group['privacy'].' GROUP</span>
                                </div>
                            </div>
                            Owned by <a href="//facebook.com/'.$group['fb_id'].'" target="_blank">'.$group['first_name'].' '.$group['last_name'].'</a>
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
                echo pagination($group_count, $rows, $from, http_build_query($g), makeuri('dashboard.php?show=groups'));
            }
        }
        ?>
    </div>
</div>