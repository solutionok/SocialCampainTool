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
    	<div class="alert alert-success loader">
        	<?php echo $lang['sync'][0]?>
        </div>
        <div class="text-center loader-gif loader">
        	<img src="images/loader.gif">
        </div><br/><br/>
        <table class="table fail-tab"></table>
    </div>
</div>
<script>
/**
 * Function to sync facebook groups
 */
var ajax_url = '<?php echo makeuri('ajax.php')?>';
var dash_url = '<?php echo makeuri('dashboard.php')?>';
var page_synced = 0;
var group_synced = 0;
var event_synced = 0;
function sync_account(fb_id)
{
	$.post(ajax_url, {
		sync_pages: 1,
		fb_id: fb_id
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			if(group_synced == 1 && event_synced == 1)$('.loader').hide();
			notify('error', data.error);
			if(data.fail.length > 0){
				$('.loader').hide();
				$('.fail-tab').append('<tr><td colspan="10"><div class="alert alert-info"><?php echo $lang['sync'][1]?> <a href="dashboard.php?show=summary">dashboard</a> >></div></td></tr>');
				for(i = 0; i < data.fail.length; i++){
					d = data.fail[i];
					$('.fail-tab').append('<tr class="pages sync_fb" rel="'+d.page_id+'" rel-o="'+fb_id+'"><td><a href="//facebook.com/'+d.page_id+'" target="_blank">'+d.page_name+'</a></td><td style="width:80px"><button class="btn btn-sm btn-info pp_del"><?php echo $lang['common'][0]?></button></td></tr>');		
				}
			}
		}
		else{
			page_synced = 1;
			if(group_synced == 1 && event_synced == 1){
				notify('success', '<?php echo $lang['sync'][4]?>');
				window.location.href = dash_url;
			}
		}
	});
	
	$.post(ajax_url, {
		sync_groups: 1,
		fb_id: fb_id
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			if(page_synced == 1 && event_synced == 1)$('.loader').hide();
			notify('error', data.error);
			if(data.fail.length > 0){
				$('.loader').hide();
				$('.fail-tab').append('<tr><td colspan="10"><div class="alert alert-info"><?php echo $lang['sync'][2]?> <a href="dashboard.php?show=summary">dashboard</a> >></div></td></tr>');
				for(i = 0; i < data.fail.length; i++){
					d = data.fail[i];
					$('.fail-tab').append('<tr class="groups sync_fb" rel="'+d.group_id+'" rel-o="'+fb_id+'"><td><a href="//facebook.com/'+d.group_id+'" target="_blank">'+d.group_name+'</a></td><td style="width:80px"><button class="btn btn-sm btn-info pp_del"><?php echo $lang['common'][0]?></button></td></tr>');		
				}
			}
		}
		else{
			group_synced = 1;
			if(page_synced == 1 && event_synced == 1){
				notify('success', '<?php echo $lang['sync'][4]?>');
				window.location.href = dash_url;
			}
		}
	});
	
	$.post(ajax_url, {
		sync_events: 1,
		fb_id: fb_id
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			if(page_synced == 1 && group_synced == 1)$('.loader').hide();
			notify('error', data.error);
			if(data.fail.length > 0){
				$('.loader').hide();
				$('.fail-tab').append('<tr><td colspan="10"><div class="alert alert-info"><?php echo $lang['sync'][3]?> <a href="dashboard.php?show=summary">dashboard</a> >></div></td></tr>');
				for(i = 0; i < data.fail.length; i++){
					d = data.fail[i];
					$('.fail-tab').append('<tr class="events sync_fb" rel="'+d.event_id+'" rel-o="'+fb_id+'"><td><a href="//facebook.com/'+d.event_id+'" target="_blank">'+d.event_name+'</a></td><td style="width:80px"><button class="btn btn-sm btn-info pp_del"><?php echo $lang['common'][0]?></button></td></tr>');		
				}
			}
		}
		else{
			event_synced = 1;
			if(page_synced == 1 && group_synced == 1){
				notify('success', '<?php echo $lang['sync'][4]?>');
				window.location.href = dash_url;
			}
		}
	});
}
sync_account('<?php echo $fb_id?>');
</script>