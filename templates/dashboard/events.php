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
            <?php echo $lang['dashboard'][20]?>
            <div class="pull-right">
                <div class="row pull-up">
                    <div class="col-lg-12">
                        <form class="search">
                            <input class="form-control submit-enter" name="q" placeholder="<?php echo $lang['dashboard'][24]?>" 
                                value="<?php if(@$_GET['show'] == 'events')echo @purify_text($_GET['q'])?>"/>
                            <input type="hidden" name="show" value="events" />
                        </form>
                    </div>
                </div>
            </div>
        </h3>
        <?php
        $event_count = $auth->count_user_events($user_id);
        if(!$event_count)echo '<div class="alert alert-warning">'.$lang['dashboard'][16].'</div>';
        else{
            $from = 1;
            $rows = 40;
            $name = '';
            
            if(!empty($_GET['show']) && $_GET['show'] == 'events'){
                if(!empty($_GET['from'])){
                    $from = (int)$_GET['from'];	
                    if($from < 1)$from = 1;
                }
                if(!empty($_GET['q'])){
                    $name = sql_real_escape_string($_GET['q']);
                }
                if(!empty($name))$event_count = $auth->count_user_events($user_id, $name);
            }
            
            if(empty($event_count))echo '<div class="alert alert-warning">'.$lang['dashboard'][16].'</div>';
            else{
                echo '<h4>
						'.$event_count.' '.$lang['dashboard'][21].'
					  </h4>
					  <button class="btn btn-sm btn-default sel_all" rel="events">'.$lang['js']['select_all'].'</button>&nbsp;&nbsp;
					<button class="btn btn-sm btn-primary inv_sel" rel="events">'.$lang['js']['inv_selected'].'</button>&nbsp;&nbsp;
					<button class="btn btn-sm btn-danger del_selected" rel="events">'.$lang['js']['del_selected'].'</button>
					<hr/>';
                $events = $auth->get_user_events($user_id, $from, $rows, $name);
                $i = 0;
                foreach($events as $event){
					$name = $event['event_name'];
					if($event['account_status'] != 1)$name = '<strike>'.$name.'</strike>';
                    if(!$i)echo '<div class="row">';
                    echo '<div class="col-lg-3 events event-'.$event['event_id'].'" rel="'.$event['event_id'].'" rel-o="'.$event['fb_id'].'">
                            <div class="row">
                                <div class="col-lg-4" style="min-height:85px">
                                    <img src="https://graph.facebook.com/'.$event['event_id'].'/picture?type=normal&access_token='.$event['access_token'].'" width="80px"/>
                                </div>
                                <div class="col-lg-8">
                                    <h5>
										<input type="checkbox" class="event-checkbox">&nbsp;&nbsp;
                                        <a href="//facebook.com/'.$event['event_id'].'" target="_blank">
                                            '.$name.'
                                        </a>
                                    </h5>
                                    <span class="label label-info">'.$event['start_time'].'</span>
                                </div>
                            </div>
                            Owned by <a href="//facebook.com/'.$event['event_id'].'" target="_blank">'.$event['first_name'].' '.$event['last_name'].'</a>
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
                echo pagination($event_count, $rows, $from, http_build_query($g), makeuri('dashboard.php?show=events'));
            }
        }
        ?>
    </div>
</div>