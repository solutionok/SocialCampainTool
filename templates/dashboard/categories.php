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
        <h3><?php echo $lang['header']['dashboard']['cats']?>
            <div class="pull-right">
                <div class="row pull-up">
                    <div class="col-lg-6">
                        <form class="search">
                            <input class="form-control submit-enter" name="q" placeholder="<?php echo $lang['dashboard']['type_cat_name']?>"
                                value="<?php if(@$_GET['show'] == 'categories')echo @purify_text($_GET['q'])?>"/>
                            <input type="hidden" name="show" value="folders" />
                        </form>
                    </div>
                    <div class="col-lg-6">
                        <button class="btn btn-primary create_new_cat"><?php echo $lang['dashboard']['create_new_cat']?></button>
                    </div>
                </div>
            </div>
        </h3>
        <?php
        $cat_count = $auth->count_user_categories($user_id);
        if(!$cat_count)echo '<div class="alert alert-warning">'.$lang['dashboard']['no_cat'].'</div>';
        else{
            $from = 1;
            $rows = 25;
            $name = '';
            
            if(!empty($_GET['show']) && $_GET['show'] == 'categories'){
                if(!empty($_GET['from'])){
                    $from = (int)$_GET['from'];	
                    if($from < 1)$from = 1;
                }
                if(!empty($_GET['q'])){
                    $name = sql_real_escape_string($_GET['q']);
                }
                if(!empty($name))$cat_count = $auth->count_user_categories($user_id, $name);
            }
            
            if(empty($cat_count))echo '<div class="alert alert-warning">'.$lang['dashboard']['no_cat_found'].'</div>';
            else{
                echo '<h4>
						'.$cat_count.' '.$lang['dashboard']['cat_found'].'
					  </h4>
					  	<button class="btn btn-sm btn-default sel_all" rel="categories">'.$lang['js']['select_all'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-primary inv_sel" rel="categories">'.$lang['js']['inv_selected'].'</button>&nbsp;&nbsp;
						<button class="btn btn-sm btn-danger del_selected" rel="categories">'.$lang['js']['del_selected'].'</button><br/><br/>';
						
                $cats = $auth->get_user_categories($user_id, $from, $rows, $name);
                $i = 0;
                echo '<table class="table user_cat_tab">';
				echo '<tr>
						<th>'.$lang['dashboard']['cat_id'].'</th>
						<th>'.$lang['dashboard']['cat_name'].'</th>
						<th>'.$lang['dashboard']['p_count'].'</th>
						<th>'.$lang['common'][8].'</th>
					 </tr>';
					 
				foreach($cats as $cat){
					$p_count = count(json_decode($cat['selected_pages'], true));
                    echo '<tr rel="'.$cat['category_id'].'" id="cat_'.$cat['category_id'].'">
                          	<td><input type="checkbox" class="category-checkbox">&nbsp;&nbsp;#'.$cat['category_id'].'</td>
							<td>'.$cat['category_name'].'</td>
							<td>'.$p_count.'</td>
							<td width="200px"><button class="btn btn-sm btn-info edit_cat_open">'.$lang['js']['edit'].'</button>&nbsp;&nbsp;<button class="btn btn-sm btn-danger cat_delete">'.$lang['js']['delete'].'</button></td>
                          </tr>';
                   	
                }	
                
				echo '</table>';
                
				$g = $_GET;
				unset($g['show']);
                echo pagination($cat_count, $rows, $from, http_build_query($g), makeuri('dashboard.php?show=categories'));
            }
        }
        ?>
    </div>
</div>

<?php
$user_cats = $auth->get_user_categories($user_id, 1, 500, '', 1);
?>

<div class="modal add_edit_cats">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo $lang['js']['add_edit_cats']?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div>
          	<div class="row">
            	<form id="sel_autocomplete_save_as_form">
                	<input type="hidden" name="add_edit_cats" value="1" />
                    <div class="col-lg-6">
                        <label><?php echo $lang['dashboard'][43]?></label><br/>
                        <select name="social_ids" id="social_ids2" class="select2">
                            <?php echo $auth->get_user_pages_list($user_id, 1);?>
                        </select><br/>
                        
                        <label><?php echo $lang['js']['save_as']?></label>
                        <select style="width:100%" id="sel_autocomplete_save_as" name="save_cat" class="form-control">
                            <option value=""><?php echo $lang['js']['create_new_cat']?></option>
                            <?php echo $user_cats?>
                        </select>
                        <label><?php echo $lang['js']['cat_name']?></label>
                        <input type="text" class="form-control" id="new_cat_name" name="new_cat_name" value="" placeholder="<?php echo $lang['js']['cat_name']?>" />
                    </div>
                    <div class="col-lg-6">
                         <h4>
                            <?php echo $lang['dashboard'][56]?> 
                            <span style="font-size:13px">
                                <i class="glyphicon glyphicon-edit sch_bulk pointer" title="<?php echo $lang['js']['bulk_sel']?>"></i>
                                &nbsp;&nbsp;
                                <i class="glyphicon glyphicon-trash sch_all_clear pointer" title="<?php echo $lang['js']['clear_all_sel']?>"></i>
                            </span>
                        </h4>
                        <div class="schedule-selected-pages schedule-selected-pages2">
                            <table class="table">
                            </table>
                        </div>
                    </div>
            	</form>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo $lang['common'][19]?></button>
        <button type="button" class="btn btn-sm btn-primary save_add_edit_cats"><?php echo $lang['common'][45]?></button>
      </div>
    </div>
  </div>
</div>

<script>
$('#social_ids2').select2(); 
</script>
