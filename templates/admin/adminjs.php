<?php
/**
 * @package Social Ninja
 * @version 1.1
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<script>
$(document).on('click', ".up_storage", function(){
	var user_id = $(this).parents('tr:first').attr('rel');
	$('#c_user').val(user_id);
	$('.up-storage-modal').modal();		
});
$(document).on('click', ".up_post_per_day", function(){
	var user_id = $(this).parents('tr:first').attr('rel');
	$('#c_user').val(user_id);
	$('.up-post-modal').modal();		
});
$(document).on('click', ".up_membership", function(){
	var user_id = $(this).parents('tr:first').attr('rel');
	$('#c_user').val(user_id);
	$('.up-membership-modal').modal();		
});
$(document).on('click', ".add_admin", function(){
	var user_id = $(this).parents('tr:first').attr('rel');
	$('#c_user').val(user_id);
	$('.add-admin-modal').modal();		
});
$(document).on('click', ".add_cron", function(){
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'add_cron': 1,
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			notify('error', data.error);
			var notice = 'Add following scripts - <br/><br/>' + data.cron.join('<br/>') + ( data.lines != null ? '<br/><br/>Commands-<br/><br/>'+data.lines.join('<br/>') : '' );
			$('.cron-notice').find('.modal-body').html(notice);
			$('.cron-notice').modal();
		}
		else{
			notify('success', 'Cron task added successfully');
		}
	});
});
$(document).on('click', ".remove_cron", function(){
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'remove_cron': 1,
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			notify('success', 'Cron task removed successfully');
		}
	});
});
$(document).on('click', ".up-membership-btn", function(){
	var user_id = $('#c_user').val();
	var plan = $('#update_user_plan').val();
	var expiry = $('#update_user_plan_exp').val();
	var plan_name = $('#update_user_plan option:selected').text();
	if(plan == '')return notify('error', 'Please enter a valid plan');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'update_user_plan': plan,
		'expiry': expiry,
		'user_id': user_id
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			$('.up-membership-modal').modal('hide');	
			notify('success', 'Plan successfully updated');
			$('tr[rel="'+user_id+'"]').find('.user_membership').html(plan_name);
			
		}
	});
});
$(document).on('click', ".up-storage-btn", function(){
	var user_id = $('#c_user').val();
	var storage = $('#storage').val();
	if(storage == '')return notify('error', 'Please enter a valid storage amount');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'update_storage': storage,
		'user_id': user_id
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			$('.up-storage-modal').modal('hide');	
			notify('success', 'Storage successfully updated');
			$('tr[rel="'+user_id+'"]').find('.user_storage').html(data.storage);
			
		}
	});
});
$(document).on('click', ".up-post-btn", function(){
	var user_id = $('#c_user').val();
	var ppd = $('#post_per_day').val();
	if(ppd == '')return notify('error', 'Please enter a valid amount');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'update_post_per_day': ppd,
		'user_id': user_id
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			$('.up-post-modal').modal('hide');	
			notify('success', 'Successfully updated');
			$('tr[rel="'+user_id+'"]').find('.ppd').html(ppd);
			
		}
	});
});
$(document).on('click', ".remove_admin", function(){
	var user_id = $(this).parents('tr:first').attr('rel');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'remove_admin': user_id
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+user_id+'"]');
			notify('success', 'Successfully removed admin');
			elem.find('.remove_admin').removeClass('remove_admin').addClass('add_admin').html('Promote');
			elem.find('.at').html('No');
			elem.find('td:first').find('.label').remove();
		}
	});
});
$(document).on('click', ".add-user-btn", function(){
	notify('wait', 'Requesting...');
	$.post(ajax_url, $('#add-user-form').serialize(), 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			$('.add-user-modal').modal('hide');
			$("input[name='email'], input[name='password'] ,select[name='adminship']").val("");
			$('.add-u-note').html('');
			notify('success', 'New user added successfully');
		}
	});
});
$(document).on('click', ".update-user-btn", function(){
	notify('wait', 'Requesting...');
	$.post(ajax_url, $('#update-user-form').serialize(), 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			$('.update-user-modal').modal('hide');
			$("input[name='email'], input[name='password'] ,select[name='adminship']").val("");
			$('.add-u-note').html('');
			notify('success', 'User updated successfully');
		}
	});
});
$(document).on('click', ".add-admin-btn", function(){
	var user_id = $('#c_user').val();
	var level = $('#alevel').val();
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'add_admin': user_id,
		'level': level
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+user_id+'"]');
			notify('success', 'Successfully added admin');
			elem.find('.add_admin').removeClass('add_admin').addClass('remove_admin').html('Remove');
			$('.add-admin-modal').modal('hide');
			elem.find('.at').html('Yes');
		}
	});
});
$(document).on('click', ".adm_del_user", function(){
	if(!confirm_action('Are you sure to delete this user?', $(this)))return false;
	var user_id = $(this).parents('tr:first').attr('rel');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'adm_del_user': user_id
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			notify('success', 'Successfully deleted user');
			var elem = $('tr[rel="'+user_id+'"]');
			elem.html('<td colspan="10"><div class="alert alert-info">Successfully deleted user</div></td>');
		}
	});
});
$(document).on('click', ".ban_user", function(){
	var user_id = $(this).parents('tr:first').attr('rel');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'ban_user': user_id
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+user_id+'"]');
			notify('success', 'Successfully banned user');
			elem.find('.ban_user').removeClass('ban_user').addClass('unban_user').html('Unban');
			elem.find('.bt').html('Suspended');
		}
	});
});
$(document).on('click', ".unban_user", function(){
	var user_id = $(this).parents('tr:first').attr('rel');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'unban_user': user_id
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+user_id+'"]');
			notify('success', 'Successfully unbanned user');
			elem.find('.unban_user').removeClass('unban_user').addClass('ban_user').html('Ban');
			elem.find('.bt').html('Active');
		}
	});
});
$(document).on('click', ".change_posting", function(){
	var user_id = $(this).parents('tr:first').attr('rel');
	var type = $(this).attr('id');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'change_posting': type,
		'user_id': user_id
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+user_id+'"]');
			notify('success', 'Successfully changed settings');
			elem.find('.'+type.split('_')[1]+'p').html(data.msg);
		}
	});
});
$(document).on('click', ".adm_vq_delete", function(){
	if(!confirm_action('Are you sure to delete this queue?', $(this)))return false;
	
	var qid = $(this).parents('tr:first').attr('rel');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'adm_vq_delete': qid,
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+qid+'"]');
			notify('success', 'Successfully deleted');
			elem.html('<td colspan="10"><div class="alert alert-success">Queue deleted</div></td>');
		}
	});
});
$(document).on('click', ".adm_folder_delete", function(){
	if(!confirm_action('Are you sure to delete this folder?', $(this)))return false;
	
	var folder_id = $(this).parents('tr:first').attr('rel');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'adm_folder_delete': folder_id,
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+folder_id+'"]');
			notify('success', 'Successfully deleted');
			elem.html('<td colspan="10"><div class="alert alert-success">Folder deleted</div></td>');
		}
	});
});
$(document).on('click', ".adm_file_delete", function(){
	if(!confirm_action('Are you sure to delete this file?', $(this)))return false;
	
	var file_id = $(this).parents('tr:first').attr('rel');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'adm_file_delete': file_id,
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+file_id+'"]');
			notify('success', 'Successfully deleted');
			elem.html('<td colspan="10"><div class="alert alert-success">File deleted</div></td>');
		}
	});
});
$(document).on('click', ".adm_profile_ban", function(){
	var id = $(this).parents('tr:first').attr('rel');
	var site = $(this).parents('tr:first').attr('rel-site');
	var uid = $(this).parents('tr:first').attr('rel-uid');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'adm_profile_ban': id,
		'site': site,
		'uid': uid
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel-s="'+id+'|'+uid+'"]');
			notify('success', 'Profile banned');
			elem.find('.adm_profile_ban').removeClass('adm_profile_ban').addClass('adm_profile_unban').html('Unban');
		}
	});
});
$(document).on('click', ".adm_profile_unban", function(){
	var id = $(this).parents('tr:first').attr('rel');
	var site = $(this).parents('tr:first').attr('rel-site');
	var uid = $(this).parents('tr:first').attr('rel-uid');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'adm_profile_unban': id,
		'site': site,
		'uid': uid
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel-s="'+id+'|'+uid+'"]');
			notify('success', 'Profile unbanned');
			elem.find('.adm_profile_unban').removeClass('adm_profile_unban').addClass('adm_profile_ban').html('Ban');
		}
	});
});
$(document).on('click', ".adm_profile_delete", function(){
	if(!confirm_action('Are you sure to delete this profile?', $(this)))return false;
	
	var id = $(this).parents('tr:first').attr('rel');
	var site = $(this).parents('tr:first').attr('rel-site');
	var uid = $(this).parents('tr:first').attr('rel-uid');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'adm_profile_delete': id,
		'site': site,
		'uid': uid
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel-s="'+id+'|'+uid+'"]');
			notify('success', 'Profile deleted');
			elem.html('<td colspan="10"><div class="alert alert-success">Profile deleted</div></td>');
		}
	});
});
$(document).on('click', ".adm_sch_ban", function(){
	var sch_id = $(this).parents('tr:first').attr('rel');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'adm_sch_ban': sch_id
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+sch_id+'"]');
			notify('success', 'Successfully banned schedule');
			elem.find('.adm_sch_ban').removeClass('adm_sch_ban').addClass('adm_sch_unban').html('Unban');
		}
	});
});
$(document).on('click', ".adm_sch_unban", function(){
	var sch_id = $(this).parents('tr:first').attr('rel');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'adm_sch_unban': sch_id
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+sch_id+'"]');
			notify('success', 'Successfully unbanned schedule');
			elem.find('.adm_sch_unban').removeClass('adm_sch_unban').addClass('adm_sch_ban').html('Ban');
		}
	});
});
$(document).on('click', ".adm_sch_delete", function(){
	if(!confirm_action('Are you sure to delete this schedule?', $(this)))return false;
	
	var sch_id = $(this).parents('tr:first').attr('rel');
	 
	notify('wait', 'Requesting...');
	$.post(ajax_url, {
		'adm_sch_delete': sch_id,
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			var elem = $('tr[rel="'+sch_id+'"]');
			notify('success', 'Successfully deleted');
			elem.html('<td colspan="10"><div class="alert alert-success">Schedule deleted</div></td>');
		}
	});
});
$(document).on('click', ".add-u-rand-pwd", function(){
	var pwd = randStr(10);
	$('#add-user-form').find('input[type="password"]').val(pwd);
	$('#update-user-form').find('input[type="password"]').val(pwd);
	$('.add-u-note').html('<h4 style="color:green">Password: '+pwd+'</h4>');
});
$(document).on('click', ".plan-save", function(){
	notify('wait', 'Requesting...');
	$.post(ajax_url, 
		$('#plan-form').serialize()
	, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			notify('success', 'Successfully added new plan. Please wait...');
			window.location = window.location.href;
		}
	});
});
$(document).on('click', ".plan_edit", function(){
	var val = $('#choose_plan').val();
	if(val == '')return notify('error', 'Please select a plan first');
	val = $.parseJSON(atob($('#choose_plan option:selected').attr('rel')));
	$.each(val, function(e, v){
		$('#'+e).val(v);
	});
	$('.plan-modal').find('.modal-title').html('Update plan');
	$('.plan-modal').modal();
});
$(document).on('click', ".plan_delete", function(){
	if(!confirm_action('If you delete a plan, all users with this plan will be downgraded to Basic Plan. Do you want to proceed?', $(this)))return false;
	
	var val = $('#choose_plan').val();
	if(val == '')return notify('error', 'Please select a plan first');
	
	notify('wait', 'Requesting...');
	$.post(ajax_url, { 
		'delete_plan_id': val
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			notify('success', 'Successfully deleted plan. Please wait...');
			window.location = window.location.href;
		}
	});
});
$(document).on('click', ".lang_del", function(){
	var val = $(this).parents('tr:first').attr('rel');
	if(val == 'en')return notify('error', 'English language cannot be deleted');
	
	notify('wait', 'Requesting...');
	$.post(ajax_url, { 
		'lang_del': val
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			notify('success', 'Successfully deleted lang. Please wait...');
			window.location = window.location.href;
		}
	});
});
$(document).on('click', ".lang_default", function(){
	var val = $(this).parents('tr:first').attr('rel');
	
	notify('wait', 'Requesting...');
	$.post(ajax_url, { 
		'lang_default': val
	}, 
	function(response){
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			notify('success', 'Successfully made default lang. Please wait...');
			window.location = window.location.href;
		}
	});
});

$(document).on('click', ".up_user", function(){
	var elem = $(this).parents('tr:first');
	var uid = elem.attr('rel');
	$('input[name="adm_update_user"]').val(uid);
	$('.update-user-modal').modal();
});

function randStr(length)
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i = 0; i < length; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}
</script>

<div class="modal add-user-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Add new user</h4>
      </div>
      <div class="modal-body">
         <form id="add-user-form">
            <div class="row">
                <div class="col-lg-6">    	
                    <label>Email Address</label>
                    <input type="text" name="email" class="form-control"/>
                    
                    <label>Password <a href="javascript:void(0)" class="add-u-rand-pwd">Random</a></label>
                    <input type="password" name="password" class="form-control"/>
                              
                </div>
                
                <div class="col-lg-6">
                     
                    <label>Adminship</label>
                    <select name="adminship" class="form-control">
                        <option value="">None</option>
                        <option value="1">Super Admin</option>
                        <option value="2">Normal Admin</option>
                    </select>
                    
                    <label>Membership</label>
                    <select name="membership" class="form-control">
                        <option value="">Default</option>
                        <?php echo get_membership_plans()?>
                    </select>
                    <br/>
                    <div class="add-u-note"></div>
                </div>
            </div>
             <div class="row">
                <div class="col-lg-6">    	
                    <label>Membership expires in days (no expiry for basic plan)</label>
                    <input type="text" name="mem_expires_in" class="form-control" value="30"/>            
                </div>
            </div>
            <input type="hidden" name="adm_add_user" value="1" />
         </form> 
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary add-user-btn">Add User</button>
      </div>
    </div>
  </div>
</div>

<div class="modal update-user-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Update user</h4>
      </div>
      <div class="modal-body">
         <form id="update-user-form">
            <div class="row">
                <div class="col-lg-12">    	
                    <label>New email Address</label>
                    <input type="text" name="email" class="form-control"/>
                    
                    <label>New password <a href="javascript:void(0)" class="add-u-rand-pwd">Random</a></label>
                    <input type="password" name="password" class="form-control"/>        
                	<br/>
                    <div class="add-u-note"></div>
                	<br/>
                    * keep a field blank if not changing that field
                </div>
            </div>
            <input type="hidden" name="adm_update_user" value="" />
         </form> 
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary update-user-btn">Update User</button>
      </div>
    </div>
  </div>
</div>
