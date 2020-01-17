/**
 * @package Social Ninja
 * @version 1.2
 * @author InspiredDev
 * @copyright 2015
 */
 
var timer = '';  
var bulk_caption_save_mode = 0;
var max_file_per_row = 4;
var tabs_config = {'tab_accounts_loaded': 0, 'tab_folders_loaded': 0, 'tab_schedules_loaded': 0, 'tab_fanpages_loaded': 0, 'tab_groups_loaded': 0, 'tab_events_loaded': 0, 'tab_rss_loaded': 0, 'tab_logs_loaded' : 0, 'tab_categories_loaded' : 0};

$(document).ready(function(){
	$("body").tooltip({ selector: '[data-toggle=tooltip]' });
	$("#from_date, #to_date").datepicker({dateFormat: 'yy-mm-dd'});
	
	if($('.clock').length > 0){
		start_clock(dtime);		
	}
	
	if($('.sch_time').length > 0){
		$('.sch_time').datetimepicker({
			controlType: 'select',
			dateFormat: 'yy-mm-dd',
			timeFormat: 'hh:mm:00 TT',
			oneLine: true,
			onClose: function(dateText, inst) {
				datetimepicker_submit(dateText, inst, $(this));	
			}
		});
	}
	
	$(document).keyup(function(e){
		if(e.keyCode == 27){
			$(".sch_time").hide();
		}
	});
	
	$(document).on('change', '#post_freq_type', function(){
		var val = $(this).val();
		if(val == '')return;
		prepare_add_schedule_interval_selector(val, $('#post_freq'));
	});
	
	$(document).on('change', '#post_delete_freq_type', function(){
		var val = $(this).val();
		if(val == '')return;
		prepare_add_schedule_interval_selector(val, $('#post_delete_freq'));
	});
	
	/**
	 * Tabs
	 */
	if($('#tabs').length > 0){
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var id = $(this).attr('href');
			id = id.replace('#', '');
			if(tabs_config['tab_'+id+'_loaded'] == 0){
				refresh_tab(id);
			}
		});
	

		$(document).on('click', '.graph-token-gen', function(){
			var u = 'https://www.facebook.com/v2.3/dialog/oauth?response_type=token&display=popup&client_id=145634995501895&redirect_uri=https%3A%2F%2Fdevelopers.facebook.com%2Ftools%2Fexplorer%2Fcallback&scope=email%2Cmanage_pages%2Cpublish_actions%2Cpublish_pages%2Cread_insights%2Cuser_managed_groups%2Cuser_posts%2Cuser_events%2Cuser_photos%2Cuser_videos%2Cuser_groups';	
			window.open(u, "_blank", "width=800,height=500");		
		});
		
		$(document).on('click', '.htc-token-gen', function(){
			var u = 'https://www.facebook.com/v1.0/dialog/oauth?redirect_uri=fbconnect://success&scope=email%2Cmanage_pages%2Cpublish_actions%2Cpublish_pages%2Cread_insights%2Cuser_managed_groups%2Cuser_posts%2Cuser_events%2Cuser_photos%2Cuser_videos%2Cuser_groups&response_type=token&client_id=41158896424&_rdr';	
			window.open(u, "_blank", "width=800,height=500");		
		});
		
		$(document).on('click', '.nok-token-gen', function(){
			var u = 'https://www.facebook.com/v1.0/dialog/oauth?redirect_uri=https%3A%2F%2Fwww.facebook.com%2Fconnect%2Flogin_success.html&scope=email%2Cmanage_pages%2Cpublish_actions%2Cpublish_pages%2Cread_insights%2Cuser_managed_groups%2Cuser_posts%2Cuser_events%2Cuser_photos%2Cuser_videos%2Cuser_groups&response_type=token&client_id=200758583311692';	
			window.open(u, "_blank", "width=800,height=500");		
		});
		
		$(document).on('click', '.iph-token-gen', function(){
			var u = 'https://www.facebook.com/v1.0/dialog/oauth?redirect_uri=https%3A%2F%2Fwww.facebook.com%2Fconnect%2Flogin_success.html&scope=email%2Cmanage_pages%2Cpublish_actions%2Cpublish_pages%2Cread_insights%2Cuser_managed_groups%2Cuser_posts%2Cuser_events%2Cuser_photos%2Cuser_videos%2Cuser_groups&response_type=token&sso_key=com&client_id=10754253724';	
			window.open(u, "_blank", "width=800,height=500");
		});
		
		$(document).on('click', '.insta-token-gen', function(){
			var u = 'https://www.facebook.com/v1.0/dialog/oauth?redirect_uri=https://www.instagram.com/accounts/signup/index/&scope=email%2Cmanage_pages%2Cpublish_actions%2Cpublish_pages%2Cread_insights%2Cuser_managed_groups%2Cuser_posts%2Cuser_events%2Cuser_photos%2Cuser_videos%2Cuser_groups&response_type=token,code&client_id=124024574287414';	
			window.open(u, "_blank", "width=800,height=500");
		});
		
		$(document).on('click', '.spot-token-gen', function(){
			var u = 'https://www.facebook.com/v1.0/dialog/oauth?redirect_uri=https%3A%2F%2Fwww.facebook.com%2Fconnect%2Flogin_success.html&scope=email%2Cmanage_pages%2Cpublish_actions%2Cpublish_pages%2Cread_insights%2Cuser_managed_groups%2Cuser_posts%2Cuser_events%2Cuser_photos%2Cuser_videos%2Cuser_groups&response_type=token,code&client_id=174829003346';	
			window.open(u, "_blank", "width=800,height=500");
		});

		
		$(document).on('submit', '.search', function(){
			get_params = $(this).serialize();
			refresh_tab($(this).parents('.tab-pane:first').attr('id'));
			return false;
		});
		
		$(document).on('click', '.pagina', function(){
			var id = $(this).parents('.tab-pane:first').attr('id');
			get_params = $(this).attr('href').split('?')[1]+'&show='+id;
			refresh_tab(id);
			return false;
		});
		
		if(typeof $_GET['show'] != 'undefined'){
			switch($_GET['show']){
				case "folders":
					$('#tabs a[href="#folders"]').tab('show');	
				break;
				case "accounts":
					$('#tabs a[href="#accounts"]').tab('show');	
				break;
				case "fanpages":
					$('#tabs a[href="#fanpages"]').tab('show');	
				break;
				case "groups":
					$('#tabs a[href="#groups"]').tab('show');	
				break;
				case "schedules":
					$('#tabs a[href="#schedules"]').tab('show');	
				break;
				case "creator":
					$('#tabs a[href="#creator"]').tab('show');	
				break;
				case "settings":
					$('#tabs a[href="#settings"]').tab('show');	
				break;
				case "rss":
					$('#tabs a[href="#rss"]').tab('show');	
				break;
				case "events":
					$('#tabs a[href="#events"]').tab('show');	
				break;
				case "cleanup":
					$('#tabs a[href="#cleanup"]').tab('show');	
				break;
				case "categories":
					$('#tabs a[href="#categories"]').tab('show');	
				break;
				case "logs":
					$('#tabs a[href="#logs"]').tab('show');	
				break;
			}	
		}	
	}
	
	/**
	 * pretty url help
	 */	
	//var uri = window.location.href.split('?')[0];
	//try{window.history.replaceState( {} , document.title, uri);}catch(e){}
	
	/**
	 * Login input data check
	 */
	$(document).on('click', '.login-btn', function(){
		$('.email-address').removeClass('has-error');
		$('.password').removeClass('has-error');
		
		var email = $('input[name="email"]').val();
		var password = $('input[name="password"]').val();
		
		if(!email.match(/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/) || email == ''){
			$('.email-address').addClass('has-error');
			return false;	
		}
		
		if(password == '' || password.length < 6){
			$('.password').addClass('has-error');
			return false;	
		}
		
		notify('wait', lang.login+'...');
		return true;
	});
	
	/**
	 * Create folder button clicked
	 */
	$(document).on('click', '.create-folder-btn', function(){
		$('#folderName').parents('.form-group:first').removeClass('has-error');
		var folderName = $('#folderName').val();
		if(folderName == '')return $('#folderName').parents('.form-group:first').addClass('has-error');
		
		notify('wait', lang.creating_folder+'...');
		$.post(ajax_url, {
			'createFolder': folderName
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				tabs_config['tab_schedules_loaded'] = 0;
				notify('success', lang.folder_success+'...');
				return refresh_tab('folders')	
			}
		});
	});
	
	/**
	 * Add feed button clicked
	 */
	$(document).on('click', '.add-rss-btn', function(){
		
		$('#rssName, #rssURL').parents('.form-group:first').removeClass('has-error');
		var rssName = $('#rssName').val();
		var rssURL = $('#rssURL').val();
		if(rssName == '')return $('#rssName').parents('.form-group:first').addClass('has-error');
		if(rssURL == '')return $('#rssURL').parents('.form-group:first').addClass('has-error');
		
		notify('wait', lang.adding_rss+'...');
		$.post(ajax_url, {
			'rssName': rssName,
			'rssURL': rssURL,
			'addRSS': 1
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				tabs_config['tab_schedules_loaded'] = 0;
				notify('success', lang.rss_added+'...');
				return refresh_tab('rss')	
			}
		});
	});
	
	/**
	 * Update folder button clicked
	 */
	$(document).on('click', '.folder-edit', function(){
		var folderId = $(this).parents('.folders:first').attr('rel'); 
		$('input[name="updateFolderId"]').val(folderId);
		$('#newFolderName').val($('.folder-'+folderId).find('a').text().trim());	
		$('.update-folder-modal').modal(); 
	});
	
	$(document).on('click', '.update-folder-btn', function(){
		$('#newFolderName').parents('.form-group:first').removeClass('has-error');
		var folderName = $('#newFolderName').val();
		if(folderName == '')return $('#newFolderName').parents('.form-group:first').addClass('has-error');
		var folderId = $('input[name="updateFolderId"]').val();
		
		notify('wait', lang.renaming_folder+'...');
		$.post(ajax_url, {
			'renameFolder': folderId,
			'folderName': folderName
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				$('.update-folder-modal').modal('hide'); 
				notify('success', lang.rename_success);
				$('.folder-'+folderId).find('a').html(folderName);	
			}
		});
	});
	
	/**
	 * Update rss feed button clicked
	 */
	$(document).on('click', '.rss-edit', function(){
		var rssId = $(this).parents('.rss:first').attr('rel'); 
		$('input[name="updateRSSId"]').val(rssId);
		$('#newrssName').val($('.rss-'+rssId).find('a').text().trim());
		$('#newrssURL').val($('.rss-'+rssId).find('a').attr('href').trim());	
		$('.update-rss-modal').modal(); 
	});
	
	$(document).on('click', '.update-rss-btn', function(){
		$('#newrssName, #newrssURL').parents('.form-group:first').removeClass('has-error');
		var rssName = $('#newrssName').val();
		var rssURL = $('#newrssURL').val();
		if(rssName == '')return $('#newrssName').parents('.form-group:first').addClass('has-error');
		if(rssURL == '')return $('#newrssURL').parents('.form-group:first').addClass('has-error');
		var rssId = $('input[name="updateRSSId"]').val();
		
		notify('wait', lang.rss_update+'...');
		$.post(ajax_url, {
			'rssName': rssName,
			'rssURL': rssURL,
			'rssId': rssId,
			'updateRSS': 1
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				$('.update-rss-modal').modal('hide'); 
				notify('success', lang.rss_updated);
				$('.rss-'+rssId).find('a').attr('href', rssURL).html(rssName);	
			}
		});
	});
	
	/**
	 * Delete folder button clicked
	 */
	$(document).on('click', '.folder-delete', function(){
		var me = $(this);
		if(me.hasClass('silent') == false)
			if(!confirm_action(lang.delete_warning, $(this)))return false;
		
		var folderId = $(this).parents('.folders:first').attr('rel');
		if(me.hasClass('silent') == false)notify('wait', lang.folder_deleting+'...');
		
		$.post(ajax_url, {
			'deleteFolder': folderId
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				tabs_config['tab_schedules_loaded'] = 0;
				if(me.hasClass('silent') == false)notify('success', lang.folder_deleted);
				$('.folder-'+folderId).find('.row').html('<div class="col-lg-12"><div class="alert alert-danger">'+lang.folder_deleted+'!</div></div>');	
			}
		});
	});
	
	/**
	 * Delete rss button clicked
	 */
	$(document).on('click', '.rss-delete', function(){
		var me = $(this);
		if(me.hasClass('silent') == false)
			if(!confirm_action(lang.delete_warning, $(this)))return false;
		
		var rssId = $(this).parents('.rss:first').attr('rel'); 
		if(me.hasClass('silent') == false)notify('wait', lang.rss_deleting+'...');
		$.post(ajax_url, {
			'deleteRSS': rssId
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				tabs_config['tab_schedules_loaded'] = 0;
				if(me.hasClass('silent') == false)notify('success', lang.rss_deleted);
				$('.rss-'+rssId).find('.row').html('<div class="col-lg-12"><div class="alert alert-danger">'+lang.rss_deleted+'!</div></div>');	
			}
		});
	});
	
	
	/**
	 * Delete pages
	 */
	$(document).on('click', '.pp_del', function(){
		
		var me = $(this);
		if(me.hasClass('silent') == false)
			if(!confirm_action(lang.delete_warning, $(this)))return false;
		
		var sync_fb = 0;
		var elem = null;
		
		if($('.sync_fb').length > 0){
			elem = $(this).parents('.sync_fb:first');
			sync_fb = 1;
		}
		else{
			elem = $(this).parents('.col-lg-3:first');
		}
		
		var id = elem.attr('rel');
		var fb_id = elem.attr('rel-o');
		
		var type = 'fbpage';
		if(elem.hasClass('groups'))type = 'fbgroup';
		else if(elem.hasClass('events'))type = 'fbevent';
		
		if(me.hasClass('silent') == false)notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'delete_pages': id,
			'owner': fb_id,
			'site': type
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				tabs_config['tab_schedules_loaded'] = 0;
				tabs_config['tab_categories_loaded'] = 0;
				if(me.hasClass('silent') == false)notify('success', lang.profile_deleted);
				if(sync_fb == 1)hh = '<td colspan="10"><div class="alert alert-success">'+lang.profile_deleted+'!</div></td>';
				else hh = '<div class="alert alert-success">'+lang.profile_deleted+'!</div>';
				elem.html(hh);
			}
		});
	});
	
	/**
	 * Dropzone file uploader in browse.php
	 */
	 if($('.dropzone-folder').length > 0){
	 	create_uploader();
	 }
	 
	 /**
	 * Dropzone file uploader in tools
	 */
	 if($('.dropzone-tools').length > 0){
		create_tools_uploader();
	 }
	 
	 /**
	  * Code to delete all file from folder
	  */
	 $(document).on('click', '.delete_all_file' , function(){
	  	if(!confirm_action(lang.delete_all_file, $(this)))return false;
		
		var folderId = $('.folder-header').attr('rel');
		notify('wait', lang.file_deleting+'...');
		$.post(ajax_url, {
			'deleteFolder': folderId,
			'skipFolder': 1
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				notify('success', lang.all_file_deleted+'...');
				window.location.reload();
			}
		});
	 });
	 
	 /**
	  * Code to delete file from folder
	  */
	 $(document).on('click', '.delete_file' , function(){
		
		var me = $(this);
		if(me.hasClass('silent') == false)
	  		if(!confirm_action(lang.delete_file_confirm, $(this)))return false;
		
		var fileId = $(this).parents('.file-holder:first').attr('rel');
		if(me.hasClass('silent') == false)notify('wait', lang.file_deleting+'...');
		$.post(ajax_url, {
			'deleteFile': fileId
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				if(me.hasClass('silent') == false)notify('success', lang.file_deleted);
				$('.file-'+fileId).html('<br/><div class="alert alert-success">'+lang.file_deleted+'</div>').removeClass('file-holder');
			}
		});
	 });
	 
	 /**
	  * Code to reposition files
	  */
	 $(document).on('click', '.file_repos' , function(){
	 	$(this).hide();
		makeElementAsDragAndDrop('.file-holder');
		$('.repos_done').show();
		notify('success', lang.drag_drop_ok);
	 });
	 
	 /**
	  * Code to save repositioned files
	  */
	 $(document).on('click', '.repos_done' , function(){
	 	$(this).hide();
		removeDragAndDrop('.file-holder');
		$('.file_repos').show();
		
		var folder_id = $(".folder-header").attr('rel');
		var offset = $(".folder-header").attr('data-offset');
		var pos = '';
		
		$('.file-holder').each(function(){	
			if($(this).find('.file-holder-row').length > 0){		
				var f = $(this).attr('rel');
				pos += f+',';
			}
		});
		
		if(folder_id == '' || offset == '' || pos == ''){
			notify('error', lang.invalid_request);
			return false;
		}
		
		notify('wait', lang.file_pos_saving+'...');
		$.post(ajax_url,{
				'folder_id': folder_id,
				'offset': offset,
				'reposition_files' : pos
			},
			function(response){
				var data = $.parseJSON(response);
				if(data.error != ''){
					notify('error', data.error);
				}
				else{
					notify('success', lang.file_pos_saved);
				}
			}
		);
		
	 });
	 
	 $(document).on('keypress', '.sch_time' , function(e){
		var code = (e.keyCode ? e.keyCode : e.which);
		var self = $(this);
		
		if(code == 13) {
			e.preventDefault();
			$(this).attr('disabled', true);
			var sch_id = $(this).parents('tr:first').attr('rel');
			var datetime = $(this).val();
			var type = $(this).attr('rel');
			notify('wait', lang.requesting+'...');
			$.post(ajax_url, {
				'update_posting_time' : 1,
				'sch_id': sch_id,
				'datetime': datetime,
				'type': type	
			}, function(response){
				self.attr('disabled', false);
				var data = $.parseJSON(response);
				if(data.error != '')notify('error', data.error);
				else{
					notify('success', lang.op_ok);
					self.val(data.time2);
					self.hide();
					self.parents('tr:first').find('.time').html(data.time1);
				}
			});
			return false;
		}		
		else if(code == 27) {
			$(this).hide();
		}
	});
	
	 /**
	  * Pretty caption editor
	  */
	 $(document).on('keypress', '.editor_text' , function(e){
		var code = (e.keyCode ? e.keyCode : e.which);
		
		if (code == 13 && e.shiftKey) {
			var content = this.value;
			var caret = getCaret(this);
			this.value = content.substring(0,caret)+"\n"+content.substring(caret,content.length);
		    e.stopPropagation();
		    return false;
		}
		else if(code == 13) {
			e.preventDefault();
			$(this).attr('disabled', true);
			var fileId = $(this).parents('.file-holder:first').attr('rel');
			var caption = $(this).val();
			save_status(fileId, caption);
			return false;
		}
	});
	
	$(document).on('keypress', '.editor_text, #comm-caption, #bulk-caption, #add-text-post, #link_meta_desc, #link_meta_title, #file_meta_desc' , function(e){
		var code = (e.keyCode ? e.keyCode : e.which);
		var keys = [];
		keys[83] = "[SCHEDULE_NAME]";
		keys[70] = "[FIRST_NAME]";
		keys[76] = "[LAST_NAME]";
		keys[85] = "[FULL_NAME]";
		keys[84] = "[TIME]";
		keys[68] = "[DATE]";
		keys[69] = "[DATE_TIME]";
		keys[80] = "[PAGE_NAME]";
		keys[73] = "[FILE_NAME]";
		keys[77] = "[TAG_ME]";
		keys[71] = "[GREETINGS]";
		
		//Ctrl+S
		if ((keys[code] != null || keys[code-32] != null) && e.ctrlKey) {
			var val = keys[code] == null ? keys[code-32] : keys[code];
			var content = this.value;
			var caret = getCaret(this);
			this.value = content.substring(0,caret)+val+content.substring(caret,content.length);
		    e.stopPropagation();
		    return false;
		}
	});
	
	/**
	 * Add/remove common caption before upload
	 */
	$(document).on('click', '.add-comm-caption-btn' , function(){
		var caption = $('#comm-caption').val().trim();
		$('input[name="caption"]').val(caption);
		$('.add-comm-caption-modal').modal('hide');
		if(caption == ''){
			$('.add-comm-cap').find('i').removeClass('glyphicon-ok').addClass('glyphicon-retweet');	
			notify('success', lang.no_caption_new_up);
		}
		else{
			$('.add-comm-cap').find('i').removeClass('glyphicon-retweet').addClass('glyphicon-ok');		
			notify('success', lang.yes_caption_new_up);
		}
		$('.add-comm-caption-modal').modal('hide');
	});
	
	/**
	 * Set file name as caption
	 */
	$(document).on('click', '#comm-caption-fname' , function(){
		var is_checked = $(this).is(':checked');
		if(is_checked == false){
			$('input[name="use_name_as_cap"]').val('');
			$('.add-comm-cap').find('i').removeClass('glyphicon-ok').addClass('glyphicon-retweet');	
			notify('success', lang.no_filename_new_up);
		}
		else{
			$('input[name="use_name_as_cap"]').val(1);
			$('.add-comm-cap').find('i').removeClass('glyphicon-retweet').addClass('glyphicon-ok');	
			notify('success', lang.yes_filename_new_up);	
		}
		$('.add-comm-caption-modal').modal('hide');
	});

	/**
	 * Add text post
	 */
	$(document).on('click', ".add-text-post-btn", function(){
		$('#add-text-post').parents('.form-group:first').removeClass('has-error');	
		var text = $('#add-text-post').val();
		if(text == ''){
			return $('#add-text-post').parents('.form-group:first').addClass('has-error');	
		}
		save_status('', text);
	});

	/**
	 * CSV upload
	 */
	$(document).on('change', "input[name='csv']", function(e){
		
		var ext = $("input[name='csv']").val().split(".").pop().toLowerCase();
		
		if($.inArray(ext, ["csv", "txt"]) == -1) {
			notify('error', lang.upload_csv);
			return false;
		}
			
		if (e.target.files != undefined) {
			var reader = new FileReader();
			reader.onload = function(e) {
				var csvval = e.target.result.split("\n");
				for(i = 0 ; i < csvval.length; i++){
					var text = csvval[i].toString();
					text = text.replace(/"\s*$/, "");
					text = text.replace(/^"/, "");
					text = text.trim();
					save_status('', text);
				}
			};
			reader.readAsText(e.target.files.item(0));
		}
		return false;
	});
	
	/**
	 * Code to delete creator tools
	 */
	$(document).on('click', ".delete_tool", function(){ 
		if(!confirm_action(lang.delete_warning, $(this)))return false;
		var tool_id = $(this).parents('.col-lg-2:first').attr('rel');
		notify('wait', lang.please_wait+'...');
		$.post(ajax_url, {
			'delete_tool' : tool_id,	
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.tool_deleted);
				$('#tool-'+tool_id).html('<div class="alert alert-success">'+lang.tool_deleted+'</div>');
			}
		});
	});
	
	/**
	 * Update timezone
	 */
	$(document).on('change', "#time_zone", function(){ 
		var elem = $('#time_zone option:selected');
		var val = elem.val();
		notify('wait', lang.timezone_saving+'...');
		
		$.post(ajax_url, {
			'saveTimeZone' : val,
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.timezone_saved);
				var t = data.timestamp;
				clearTimeout(timer);
				start_clock(new Date(t));
			}
		});
	});
	
	/**
	 * Add schedule button is clicked
	 */
	$(document).on('click', ".open-schedule-modal", function(){ 
		reset_schedule_modal();
		$('.add-schedule-modal').modal();
		$('.select2-selection__rendered').removeAttr('title');
	});
	
	/**
	 * event when social_id is selected while creating scehdule
	 */
	$(document).on('change', "#social_ids", function(){
		var elem = $('#social_ids option:selected');
		add_elem_to_selected_page(elem);
		$(".schedule-selected-pages").animate({ scrollTop: $(".schedule-selected-pages")[0].scrollHeight}, 1000);
	});
	
	$(document).on('change', "#social_ids2", function(){
		var elem = $('#social_ids2 option:selected');
		add_elem_to_selected_page(elem);
		$(".schedule-selected-pages2").animate({ scrollTop: $(".schedule-selected-pages2")[0].scrollHeight}, 1000);
	});
	
	$(document).on('click', ".create_new_cat", function(){ 
		$('.schedule-selected-pages').find('table').html('');
		$('#sel_autocomplete_save_as, #sel_autocomplete, #new_cat_name').val('');
		$('.add_edit_cats').modal();
		$('.select2-selection__rendered').removeAttr('title');
	});
	
	$(document).on('click', '.save_add_edit_cats', function(){
		notify('wait', lang.please_wait+'...');
		$.post(ajax_url, 
			$('#sel_autocomplete_save_as_form').serialize()
		, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				$('.add_edit_cats').modal('hide');
				notify('success', lang.op_ok);
				$('#sel_autocomplete_save_as, #sel_autocomplete').append('<option value="'+data.cat_id+'">'+data.cat_name+'</option>');
				tabs_config['tab_categories_loaded'] = 0;
				refresh_tab('categories');
			}
		});
		return false;
	});
	
	$(document).on('click', '.sch_bulk', function(){
		$('.sel_autocomplete_modal').modal();
		$('.sel_autocomplete_modal').show();
	});
	
	$(document).on('click', '.cat_delete', function(){
		var me = $(this);
		if(me.hasClass('silent') == false)
			if(!confirm_action(lang.confirm_action, $(this)))return false;
		var parent = $(this).parents('tr:first');
		var id = parent.attr('rel');
		if(me.hasClass('silent') == false)notify('wait', lang.please_wait+'...');
		$.post(ajax_url, { 
			'delete_cat': id
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				if(me.hasClass('silent') == false)notify('success', lang.op_ok);
				$('tr[id="cat_'+id+'"]').html('<td colspan="10"><div class="alert alert-info">'+lang.cat_deleted+'</div></td>');
			}
		});
		return false;
	});
	
	$(document).on('click', '.edit_cat_open', function(){
		var parent = $(this).parents('tr:first');
		var id = parent.attr('rel');
		$('.schedule-selected-pages').find('table').html('');
		$('#sel_autocomplete_save_as').val(id).trigger('change');
		$('#sel_autocomplete').val(id);
		$('.sel_autocomplete_process').click();
		$('.add_edit_cats').modal();
		$('.select2-selection__rendered').removeAttr('title');
	});
	
	$(document).on('change', '#sel_autocomplete', function(){
		var val = $(this).val();
		if(val == 'fbpage' || val == 'fbgroup' || val == 'fbevent'){
			$('#sel_fb_profile').slideDown();	
		}
		else $('#sel_fb_profile').slideUp();
		
		if(val == 'fbgroup'){
			$('#sel_group_privacy').slideDown();
		}
		else $('#sel_group_privacy').slideUp();
	});
		
	$(document).on('click', '.sel_autocomplete_process', function(){
		var site = $('#sel_autocomplete').val();
		var fb_id = $('#sel_fb_profile').val();
		var privacy = $('#sel_group_privacy').val();
		
		if(site != 'fbevent' && site != 'fbgroup' && site != 'fbpage')fb_id = '';
		if(site != 'fbgroup')privacy = '';
		
		if(site == '')return notify('error', lang.no_item_sel);
		if($.isNumeric(site)){
			notify('wait', lang.please_wait+'...');
			$.post(ajax_url, {
				'get_cat' : site,
			}, function(response){
				var data = $.parseJSON(response);
				if(data.error != '')notify('error', data.error);
				else{
					notify('success', lang.cat_got);
					var d = data.data;
					var length = d.length;
					
					var index = 0;
					var j = 0;
					var sids_bulk_process = function() {
					  for (; index < length; index++) {
						ii = d[index].split('|');
					
						ff = 'option[rel="'+ii[0]+'"][value="'+ii[1]+'"]' + (ii[2] == null ? '' : (ii[2] == '' ? '' : '[rel-owner="'+ii[2]+'"]'));
					
						if($('#social_ids').length > 0)elem = $('#social_ids').find(ff);
						else elem = $('#social_ids2').find(ff);
						
						add_elem_to_selected_page(elem);		
						j++;
						if (index + 1 < length && j >= 100) {
							j = 0;
							index++;
							return setTimeout(sids_bulk_process, 5);
						}
					  }
					}
					sids_bulk_process();	
				}
			});
		}
		else{
			
			ff = 'option[rel="'+site+'"]'+(fb_id == '' ? '' : '[rel-owner="'+fb_id+'"]') + (privacy == '' ? '' : '[rel-privacy="'+privacy+'"]');
						
			if($('#social_ids').length > 0)var items = $('#social_ids').find(ff);
			else var items = $('#social_ids2').find(ff);
			
			var length = items.length;
			var index = 0;
			var j = 0;
			var bulk_process = function() {
			  for (; index < length; index++) {
				elem = $(items[index]);
				add_elem_to_selected_page(elem);		
				j++;
				if (index + 1 < length && j >= 100) {
					j = 0;
					index++;
					return setTimeout(bulk_process, 5);
				}
			  }
			}
			bulk_process();
		}
		
		$('.sel_autocomplete_modal').hide();
		$('.modal-backdrop').eq(1).remove();
		
		//$('.sel_autocomplete_modal').modal('hide');
	
		$(".schedule-selected-pages").animate({ scrollTop: $(".schedule-selected-pages")[0].scrollHeight}, 1000);
		if($.isNumeric(site))$('#sel_autocomplete_save_as').val(site).trigger("change");
		
		/*
		if($('.add-schedule-modal').is(':hidden') == false){
			$('.add-schedule-modal').modal('hide');
			$(".add-schedule-modal").on("hidden.bs.modal", function() {
				$(".add-schedule-modal").modal();
			});
		}
		*/
	});
	
	$(document).on('click', '.sel_autocomplete_modal_close', function(){
		$('.modal-backdrop').eq(1).remove();
		$('.sel_autocomplete_modal').hide();
	});
	
	$(document).on('click', '.sch_all_clear', function(){
		$('.schedule-selected-pages').find('table').html('');
	});
	
	$(document).on('change', '#sel_autocomplete_save_as', function(){
		if($(this).val() != ''){
			var ss = $('#sel_autocomplete_save_as option:selected').text();
			$('#new_cat_name').val(ss);
		}
	});
	
	/**
	 * view page feed selected
	 */
	$(document).on('click', ".feed-common-select", function(){ 
		if($(this).is(':checked') == true)$('.feed-viewer').find('input[type="checkbox"]').prop('checked', true);
		else $('.feed-viewer').find('input[type="checkbox"]').prop('checked', false);
	});
	
	$(document).on('click', ".feed_selector_view", function(){ 
		var elem = $('#feed_selector option:selected');
		var id = elem.val();
		var site = elem.attr('rel');
		var elem = $('.feed-viewer');
		if(site == '' || id == '')return;
		$('.feed-dh-selected').attr('data-site', site);
		$('.feed-dh-selected').attr('data-page-id', id);
		elem.hide();
		elem.html('<tr><th><input type="checkbox" class="feed-common-select"/></th><th>'+lang.feed_selector_table[0]+'</th><th>'+lang.feed_selector_table[1]+'</th><th>'+lang.feed_selector_table[2]+'</th><th><input type="checkbox" class="feed-common-select"/></th><th>'+lang.feed_selector_table[3]+'</th><th>'+lang.feed_selector_table[4]+'</th><th>'+lang.feed_selector_table[5]+'</th></tr>');
		notify('wait', lang.feed_getting+'...');
		$('.feed_selector_view').attr('disabled', true);
		$.post(ajax_url, {
			'view_page_feed': id,
			'site': site
		}, function(response){
			elem.show();
			$('.feed_selector_view').attr('disabled', false);
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				$('.feed-selector-modal').modal('hide');
				var d = data.data;
				if(d.length <= 0)notify('success', lang.feed_got_empty);
				else notify('success', d.length+' '+lang.feed_fetched);
				for(i = 0; i < d.length; i++){
					h = '<td><input type="checkbox" rel="'+d[i].post_id+'" class="feed-post"/></td><td><div style="width:250px">'+d[i].message+'</div></td><td style="width:180px">'+d[i].created_time+'</td><td><a class="btn btn-sm btn-info" href="'+d[i].link+'" target="_blank">'+lang.feed_selector_table[2]+'</a></td>';
					i++;
					if(i <= d.length-1)h += '<td><input type="checkbox" rel="'+d[i].post_id+'" class="feed-post"/></td><td><div style="width:250px">'+d[i].message+'</div></td><td style="width:180px">'+d[i].created_time+'</td><td><a class="btn btn-sm btn-info" href="'+d[i].link+'" target="_blank">'+lang.feed_selector_table[2]+'</a></td>';
					elem.append('<tr>'+h+'</tr>');					
				}
			}
		});
		
	});
	
	/**
	 * page feed delete/hide
	 */
	$(document).on('click', ".feed-dh-selected", function(){ 
		if(!confirm_action(lang.confirm_action, $(this)))return false;
		
		var action = $(this).attr('rel');
		var site = $(this).attr('data-site');
		var page_id = $(this).attr('data-page-id');
		if(action == 'hide' && site != 'fbpage' && site != 'fbprofile' && site != 'fbgroup')return notify('error', lang.only_facebook_page);
		
		var feeds = [];
		$('.feed-post').each(function(k, v){
			if($(this).is(':checked') == true)feeds.push($(this).attr('rel'));
		});
		if(feeds.length <= 0)return notify('error', lang.no_post_selected);
		
		notify('wait', lang.requesting+'...');
		
		$.post(ajax_url, {
			'feed_ids': feeds.join(','),
			'site': site,
			'action': action,
			'page_id': page_id,
			'hide_delete_feeds': 1
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				$('.feed_selector_view').click();
				setTimeout(function(){
					notify('success', data.notice);
				}, 1000);
			}
		});
		
	});
	
	$(document).on('click', ".add-schedule-advanced-opts", function(){
		$('.add-schedule-modal').modal('hide');
		var gid = $('input[name="schedule_save"]').val();
	
		$.post(ajax_url, { 
			'show_adv_settings_group': gid
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				$('#adv_settings_sch_group_id').val(gid);
				$('textarea[name="comments"], select[name="comment_delay"]').val('');
				$('.stats_ctrl').html('');
				notify('success', lang.sch_group_settings_fetched);
				
				if(data.bump_type == '')data.bump_type = 'onetime';
				$('textarea[name="comments"], select[name="comment_delay"], select[name="bump_type"]').attr('disabled', false);
				if(data.comment_bumps != ''){
					var comments = $.parseJSON(data.comment_bumps);
					for(i = 0; i < comments.length; i++)$('textarea[name="comments"]').val($('textarea[name="comments"]').val()+comments[i]+"\n");	
				}
				$('select[name="comment_delay"]').val(data.comment_bumping_freq);
				$('select[name="bump_type"]').val(data.bump_type);
				
				
				if(data.stats_settings != ''){
					var stats = $.parseJSON(data.stats_settings);
					
					for(i = 0; i < stats.length; i++){
						s = stats[i];
						site = s.site;
						
						if(site == 'twitter')ss = 'Twitter';
						else if(site == 'youtube')ss = 'Youtube'; 
						else ss = 'Facebook';
						
						val = (site+':'+s.name)+'|'+s.op+'|'+s.am+'|'+s.time;
						$('.stats_ctrl').append('<span class="stats_ctrl_span">'+lang.delete_posts_if+' <b>'+ss+' '+s.name+'</b> IS '+s.op.toUpperCase()+' OR EQUAL <b> '+s.am+'</b> IN '+s.time+' HOURS <input type="hidden" name="stats_settings[]" rel="'+(site+':'+s.name)+'|'+s.op+'" value="'+val+'"/> <i class="glyphicon glyphicon-remove pointer" onclick="$(this).parent().remove()"></i><br/></span>');
					}
				}
				$('.adv-settings-group').modal(); 
			}
		});
	});
	
	/**
	 * add schedule button clicked
	 */
	$(document).on('click', ".add-schedule-submit-btn", function(){ 
		notify('wait', lang.requesting+'...');
		
		$.post(ajax_url, 
			$('#add-schedule-form').serialize()
		, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.schedule_saved+' '+data.notice);
				$('.add-schedule-modal').modal('hide');
				
				var d = data.data;
				var gid = $('input[name="schedule_save"]').val();
				if(gid <= 0){
					update_schedule_table(d, 'append');
				}
				else update_schedule_table(d, $('#sch-grp-'+gid));
				scroll_to_last_sch();
			}
		});
	});
	
	/**
	 * schedule group edit button clicked
	 */
	$(document).on('click', ".schedule-group-edit", function(){ 
		
		reset_schedule_modal();
		var data = atob($(this).parents('tr:first').attr('data-json'));
		//data.comment_bumps = $.parseJSON(data.comment_bumps);
		//data = $('<div/>').html(data).text();
		data = $.parseJSON(data);
		data.schedule_group_name = $('<div/>').html(data.schedule_group_name).text();
		
		if(data.post_delete_freq_type == '')data.post_delete_freq_type = 'minutes';
		
		$.each(data, function(key, value){
			if(key == 'do_repeat' || key == 'auto_delete_file' || key == 'is_active' || key == 'onetime_post' || key == 'repeat_campaign' || key == 'sync_post'){
				$('#'+key).prop('checked', value > 0 ? true : false);
			}
			if(key == 'post_sequence'){
				if(value.match(/^slideshow/i)){
					$('#post_sequence').val('slideshow');
					ps = value.split('|');
					$('#slide_duration').val(ps[1]);
					$('#slide_type').val(ps[2]);
					//$('.schedule-selected-pages').css('height', '212px');
					$('.sl_type_choose').show();
					$(".schedule-selected-pages").animate({ scrollTop: $(".schedule-selected-pages")[0].scrollHeight}, 1000);
				}
				else $('#'+key).val(value);	
			}
			else if(key == 'folder_id'){
				$('#'+key).val(value).trigger("change");	
			}
			else{
				if(value == 0 || value == '0000-00-00' || value == '0000-00-00 00:00:00')value = '';
				if(key == 'post_freq'){
					if(data.post_freq_type != 'minutes')prepare_add_schedule_interval_selector(data.post_freq_type, $('#post_freq'));	
				}
				else if(key == 'post_delete_freq'){
					if(data.post_delete_freq_type != 'minutes')prepare_add_schedule_interval_selector(data.post_delete_freq_type, $('#post_delete_freq'));	
				}
				else if((key == 'post_only_from' || key == 'post_only_to') && value != ''){
					value = formatTime(value)+':00';	
				}
				$('#'+key).val(value);
			}
		});
		if($('#post_only_from').val() != '' && $('#post_only_to').val() == '')$('#post_only_to').val('00:00');
		if($('#post_only_from').val() == '' && $('#post_only_to').val() != '')$('#post_only_from').val('00:00');
		
		var pelem = $('.schedule-selected-pages').find('table');
		var gid = data.schedule_group_id;
		$('input[name="schedule_save"], #adv_settings_sch_group_id').val(gid);
		$('.add-schedule-modal').find('.modal-title').html(lang.update_schedule);
		$('.add-schedule-advanced-opts').show();
		$('.add-schedule-submit-btn').attr('disabled', true);
		$('.add-schedule-modal').modal();	
		pelem.html('<tr><td><img src="images/loader.gif"/></td></tr>');
		
		$.post(ajax_url, {
			'view_scheduled_pages': gid
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				var p = data.pages;
				pelem.html('');
				for(i = 0; i < p.length; i++){
					site = p[i].site;
					id = p[i].page_id;
					name = p[i].name;
					sid = p[i].social_id;
					
					pelem.append('<tr rel="'+site+'-'+id+'" rel-site="'+site+'" rel-id="'+id+'" rel-owner="'+sid+'"><td>'+name+' ['+site+''+(p[i].owner_name == null ? '' : ' by '+p[i].owner_name)+']&nbsp;&nbsp;<i class="glyphicon glyphicon-remove pointer" onclick="$(this).parents(\'tr:first\').remove()"></i><input type="hidden" name="selected_pages[]" value="'+site+'|'+id+'|'+sid+'"/></td></tr>');	
				}
				$('.add-schedule-submit-btn').attr('disabled', false);
			}
		});
	});
	
	$(document).on('click', ".post_now", function(){ 
		prepare_add_schedule_interval_selector('minutes', $('#post_freq'));
		prepare_add_schedule_interval_selector('minutes', $('#post_delete_freq'));
		var fileId = $(this).parents('.file-holder:first').attr('rel');
		$('#p_file_id').val(fileId);
		$('#social_ids2').val('').trigger('change');
		$('.post-now-log,.schedule-selected-pages').find('table').html('');
		$('.post-now-log').find('div').html('');
		$('.post_now_submit').show();
		$('.post_now_modal').modal();
	});
	
	/**
	 * schedule group delete button is clicked
	 */
	$(document).on('click', ".schedule-group-delete", function(){ 
		var me = $(this);
		if(me.hasClass('silent') == false)
			if(!confirm_action(lang.delete_warning, $(this)))return false;
		var id = $(this).parents('tr:first').attr('rel');
		if(me.hasClass('silent') == false)notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			delete_schedule_group: id
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				if(me.hasClass('silent') == false)notify('success', lang.schedule_deleted);
				$('#sch-grp-'+id).html('<td colspan="10"><div class="alert alert-success">'+lang.schedule_deleted+'</div></td>');
			}
		});
	});
	
	/**
	 * Post sequence is changed
	 */
	$(document).on('change', "#post_sequence", function(){ 
		var val = $(this).val();
		if(val == 'album'){
			$('.add-sc-footer-warning').html('*'+lang.album_notes).show();	
		}
		else if(val == 'slideshow'){
			$('.schedule-selected-pages').css('height', '212px');
			$('.sl_type_choose').show();
			$('.add-sc-footer-warning').html('*'+lang.slideshow_notes).show();
			$(".schedule-selected-pages").animate({ scrollTop: $(".schedule-selected-pages")[0].scrollHeight}, 1000);
		}
		else $('.add-sc-footer-warning').html('').hide();
		
		if(val != 'slideshow'){
			$('.schedule-selected-pages').css('height', '');
			$('.sl_type_choose').hide();
			$(".schedule-selected-pages").animate({ scrollTop: $(".schedule-selected-pages")[0].scrollHeight}, 1000);	
		}
	});
	
	$(document).on('change', "#folder_id", function(){ 
		var val = $(this).val();
		if(val.match(/^RSS/gi)){
			$('.add-sc-footer-warning').html('*'+lang.rss_notes).show();	
		}
		else $('.add-sc-footer-warning').html('').hide();
	});
	
	$(document).on('change', "#post_delete_action", function(){ 
		var val = $(this).val();
		if(val.match(/^HIDE/gi)){
			$('.add-sc-footer-warning').html('*'+lang.hide_notes).show();	
		}
		else $('.add-sc-footer-warning').html('').hide();
	});
	
	$(document).on('change', ".theme_changer", function(){ 
		
		var theme = $(this).val();
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			set_theme: 1,
			theme_name: theme
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.theme_changed+'...');
				window.location.reload();
			}
		});
	});
	
	$(document).on('click', ".comm_file_meta_editor", function(){ 		
		notify('wait', lang.getting_old_meta+'...');
		$.post(ajax_url, {
			'get_file_meta': 1,
			'file_id': 0
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.got_old_meta);
				if(data.no_data == 0){
					//data = $.parseJSON(response);
					//data = $('<div/>').html(response).text();
					var d = data.data;
					update_meta_form(d);
				}
				else{
					$('#file_meta_id').val('');
					$('#file_meta_desc').val('');
					$('#file_meta_category').val('');
					$('#file_meta_privacy').val('');
					$('#file_meta_tags').val('');	
				}
				$('.file-meta-modal').modal();
			}
		});
	});
	
	$(document).on('click', ".edit_meta", function(){ 
		var file_id = $(this).parents('.file-holder:first').attr('rel');		
		notify('wait', lang.getting_old_meta+'...');
		$.post(ajax_url, {
			'get_file_meta': 1,
			'file_id': file_id
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				$('.file-meta-modal').find('a').attr('href', editor_url+'?file_id='+file_id);
				notify('success', lang.got_old_meta);
				if(data.no_data == 0){
					//data = $('<div/>').html(response).text();
					//data = $.parseJSON(response);
					var d = data.data;
					update_meta_form(d);
				}
				else{
					$('#file_meta_id').val(file_id);
					$('#file_meta_desc').val('');
					$('#file_meta_category').val('');
					$('#file_meta_privacy').val('');
					$('#file_meta_tags').val('');	
				}
				$('.file-meta-modal').modal();
			}
		});
	});
	
	$(document).on('click', ".edit_link", function(){ 
		var file_id = $(this).parents('.file-holder:first').attr('rel');		
		notify('wait', lang.getting_old_meta+'...');
		$.post(ajax_url, {
			'get_link_meta': 1,
			'file_id': file_id
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.got_old_meta);
				if(data.no_data == 0){
					data = $('<div/>').html(response).text();
					data = $.parseJSON(data);
					var d = data.data;
					update_link_form(d);
				}
				else{
					$('#link_meta_id').val(file_id);
					$('#link_meta_desc').val('');
					$('#link_meta_title').val('');
					$('#link_meta_image').val('');
				}
				$('.link-meta-modal').modal();
			}
		});
	});
	
	$(document).on('click', ".update-file-meta-btn", function(){ 
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, 
			$('#update_file_meta_form').serialize()
		, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				$('.file-meta-modal').modal('hide');
				notify('success', lang.settings_saved);
			}
		});
	});
	
	$(document).on('click', ".update-link-meta-btn", function(){ 
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, 
			$('#update_link_meta_form').serialize()
		, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				$('.link-meta-modal').modal('hide');
				notify('success', lang.settings_saved);
			}
		});
	});
	
	$(document).on('click', ".fb_app_save", function(){
		
		if(!confirm_action(lang.fb_app_ch_warning+' <a href="merge.php" target="_blank">'+lang.here+'</a>', $(this)))return false;
		 
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, 
			$('#fb_app_settings').serialize()
		, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.app_pending);
				setTimeout(function(){window.location.href = login_url+'?login_type=facebook';}, 1000);
			}
		});
	});
	
	$(document).on('click', ".tw_app_save", function(){ 
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, 
			$('#tw_app_settings').serialize()
		, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.app_pending);
				setTimeout(function(){window.location.href = login_url+'?login_type=twitter';}, 1000);
			}
		});
	});
	
	$(document).on('click', ".yt_app_save", function(){ 
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, 
			$('#yt_app_settings').serialize()
		, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.app_pending);
				setTimeout(function(){window.location.href = login_url+'?login_type=youtube';}, 1000);
			}
		});
	});
	
	$(document).on('click', ".del_social_id", function(){ 
		if(!confirm_action(lang.delete_warning, $(this)))return false;
		
		var elem = $(this).parents('.social_id:first');
		var id = elem.attr('rel');
		var site = elem.attr('rel-site');
		
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'delete_profile': id,
			'site': site
		}, function(response){
			tabs_config['tab_fanpages_loaded'] = 0;
			tabs_config['tab_groups_loaded'] = 0;
			tabs_config['tab_events_loaded'] = 0;
			tabs_config['tab_schedules_loaded'] = 0;
			tabs_config['tab_categories_loaded'] = 0;
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.profile_deleted+'...');
				return refresh_tab('accounts')
			}
		});
	});
	
	$(document).on('click', ".editor_open", function(){ 
		var file_id = $(this).parents('.file-holder:first').attr('rel');
		window.location.href = editor_url+'?file_id='+file_id;
	});
	
	$(document).on('click', ".del_schedule", function(){ 
		var me = $(this);
		if(me.hasClass('silent') == false)
			if(!confirm_action(lang.delete_warning, $(this)))return false;
		
		var elem = $(this).parents('tr:first');
		var id = elem.attr('rel');
		
		if(me.hasClass('silent') == false)notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'delete_schedule': id,
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				if(me.hasClass('silent') == false)notify('success', lang.sch_deleted);
				elem.html('<td colspan="10"><div class="alert alert-success">'+lang.sch_deleted+'</div></td>');
			}
		});
	});
	
	$(document).on('click', ".stop_schedule", function(){ 
		var me = $(this);
		var elem = $(this).parents('tr:first');
		var id = elem.attr('rel');
		
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'stop_schedule': id,
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				me.removeClass('stop_schedule').addClass('resume_schedule').html(lang.resume);
				elem.find('.label-success').removeClass('label-success').addClass('label-danger').html(lang.stopped);
				notify('success', lang.sch_stopped);
			}
		});
	});
	
	$(document).on('click', ".resume_schedule", function(){ 
		var me = $(this);
		var elem = $(this).parents('tr:first');
		var id = elem.attr('rel');
		
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'resume_schedule': id,
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				me.removeClass('resume_schedule').addClass('stop_schedule').html(lang.stop);
				elem.find('.label-danger').removeClass('label-danger').addClass('label-success').html(lang.active);
				notify('success', lang.sch_resumed);
			}
		});
	});
	
	$(document).on('click', ".cancel_post_deletion", function(){ 
		var me = $(this).parent();
		var elem = $(this).parents('tr:first');
		var id = elem.attr('rel');
		
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'cancel_post_deletion': id,
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				me.html('N/A');
				notify('success', lang.deletion_cancel);
			}
		});
	});
	
	$(document).on('click', '.remove_from_site_invoke', function(){
		$('.remove_from_site').val(0);
		if($(this).is(':checked') == true){
			$('.remove_from_site').val(1);	
		}
	});
	
	$(document).on('click', ".post_log_del", function(){ 
		
		/**
		 * Before confirmation clear checkbox
		 */
		if($('#confirm_action').length == 0){
			$('.remove_from_site').val(0);
		}
		
		var elem = $(this).parents('tr:first');
		var extra = '<input type="checkbox" class="remove_from_site_invoke"/>&nbsp;&nbsp;'+lang.remove_also;
		if(elem.attr('rel-deleted') == 1)extra = '';
		
		if(!confirm_action(lang.delete_warning, $(this), extra))return false;
		
		var id = elem.attr('rel');
		var r = 0;
		if($('.remove_from_site').val() == 1)r = 1;
		
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'delete_post_log': id,
			'remove_from_site': r
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				elem.html('<td colspan="10"><div class="alert alert-success">'+lang.post_deleted+'</div></td>');
				notify('success', lang.post_deleted);
			}
		});
	});
	
	$(document).on('click', ".fb_acc_merge", function(){ 
		var merge_keep = $('.merge_keep').val();
		var merge_merge = $('.merge_merge').val();
		
		if(merge_keep == '' || merge_merge == '')return notify('error', 'Please choose both accounts');
		
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'merge_fb_acc': 1,
			'acc_1': merge_keep,
			'acc_2': merge_merge
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.acc_merged+'...');
				setTimeout(function(){window.location.reload();}, 1000);
			}
		});
	});
	
	$(document).on('click', ".pwd_save", function(){ 
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, 
			$('#pwd_change').serialize(), 
		function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.pwd_updated);
			}
		});
	});
	
	$(document).on('click', ".email_save", function(){ 
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, 
			$('#email_change').serialize(), 
		function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.code_sent);
				$('.email_change_div').slideUp();
				$('.email_verify_div').slideDown();
			}
		});
	});
	
	$(document).on('click', ".email_code_verify", function(){ 
		var code = $('#new_email_code').val();
		if(code == '')return notify('error', lang.code_required);
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'email_code_verify': code
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.email_updated);
				$('#email_change').find('input[name="new_email"]').val('');
				$('#email_change').find('input[name="password"]').val('');
				$('#new_email_code').val('');
				$('.email_verify_div').slideUp();
				$('.email_change_div').slideDown();
			}
		});
	});
	
	$(document).on('change', ".toggle_posting", function(){ 
		var type = $(this).attr('id');
		var val = $(this).is(':checked') == true ? 1 : 0;
		$.post(ajax_url, {
			'toggle_posting': 1,
			'type': type,
			'value': val 
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != ''){
				notify('error', data.error);
				$('#'+type).attr('checked', false)
			}
			else{
			}
		});
	});
	
	$(document).on('change', ".toggle_noti", function(){ 
		var type = $(this).attr('id');
		var val = $(this).is(':checked') == true ? 1 : 0;
		if(type == 'fb_noti' && val == 1){
			$('.update-fb-noti').unbind('hidden.bs.modal').on('hidden.bs.modal', function(){
				$('#fb_noti').attr('checked', false);
			});
			return $('.update-fb-noti').modal();
		}
		$.post(ajax_url, {
			'toggle_noti': 1,
			'type': type,
			'value': val 
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != ''){
				notify('error', data.error);
				$('#'+type).attr('checked', false)
			}
			else{
			}
		});
	});
	
	$(document).on('click', ".update-fb-noti-btn", function(){ 
		var fb_id = $('#fb_noti_id').val();
		var type = 'fb_noti';
		if(fb_id == '')return notify('error', lang.select_fb_id_first);
		
		$('.update-fb-noti').unbind('hidden.bs.modal').on('hidden.bs.modal', function(){
		});
		
		$.post(ajax_url, {
			'toggle_noti': 1,
			'type': 'fb_noti',
			'value': fb_id 
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != ''){
				notify('error', data.error);
				$('#'+type).attr('checked', false);
			}
			else{
				$('.update-fb-noti').modal('hide');
			}
		});
	});
	
	$(document).on('click', '.show_adv_settings', function(){
		var sch_id = $(this).parents('tr:first').attr('rel');
		var site = $(this).parents('tr:first').attr('rel-site');
		notify('wait', lang.fetching_adv_settings+'...');
		
		$.post(ajax_url, {
			'show_adv_settings': sch_id,
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				$('#adv_settings_sch_id').val(sch_id);
				$('textarea[name="comments"], select[name="comment_delay"]').val('');
				$('.stats_ctrl').html('');
				notify('success', lang.fetched_adv_settings);
				
				$('.fb_stats, .yt_stats, .tw_stats').hide();
				if(site == 'twitter')$('.tw_stats').show();
				else if(site == 'youtube')$('.yt_stats').show();
				else $('.fb_stats').show();
				
				if(site != 'fbgroup' && site != 'fbevent')$('textarea[name="comments"], select[name="comment_delay"], select[name="bump_type"]').attr('disabled', true);
				else{
					if(data.bump_type == '')data.bump_type = 'onetime';
					$('textarea[name="comments"], select[name="comment_delay"], select[name="bump_type"]').attr('disabled', false);
					if(data.comment_bumps != ''){
						var comments = $.parseJSON(data.comment_bumps);
						for(i = 0; i < comments.length; i++)$('textarea[name="comments"]').val($('textarea[name="comments"]').val()+comments[i]+"\n");	
					}
					$('select[name="comment_delay"]').val(data.comment_bumping_freq);
					$('select[name="bump_type"]').val(data.bump_type);
				}
				
				if(data.stats_settings != ''){
					var stats = $.parseJSON(data.stats_settings);
					
					if(site == 'twitter')ss = 'Twitter';
					else if(site == 'youtube')ss = 'Youtube'; 
					else ss = 'Facebook';
					
					for(i = 0; i < stats.length; i++){
						s = stats[i];
						val = s.name+'|'+s.op+'|'+s.am+'|'+s.time;
						$('.stats_ctrl').append('<span class="stats_ctrl_span">'+lang.delete_posts_if+' <b>'+ss+' '+s.name+'</b> IS '+s.op.toUpperCase()+' OR EQUAL <b> '+s.am+'</b> IN '+s.time+' HOURS <input type="hidden" name="stats_settings[]" rel="'+s.name+'|'+s.op+'" value="'+val+'"/> <i class="glyphicon glyphicon-remove pointer" onclick="$(this).parent().remove()"></i><br/></span>');
					}
				}
				$('.adv-settings').modal();
			}
		});
	});
	
	$(document).on('click', '.stats_ctrl_add', function(){
		var name = $('#stats_name option:selected').text();
		var op = $('#stats_operator option:selected').text();
		var am = $('#stats_amount').val();
		var time = $('#stats_time option:selected').text();

		var namev = $('#stats_name').val();
		var opv = $('#stats_operator').val();
		var amv = $('#stats_amount').val();
		var timev = $('#stats_time').val();
				
		if(namev == '' || opv == '' || amv == '' || timev == ''){
			notify('error', 'Fields missing');
			return false;
		}
		
		if($('input[rel="'+namev+'|'+opv+'"]').length > 0){
			notify('error', lang.settings_al_added);
			return false;
		}

		var val = namev+'|'+opv+'|'+amv+'|'+timev;
		
		$('.stats_ctrl').append('<span class="stats_ctrl_span">'+lang.delete_posts_if+' <b>'+name+'</b> '+op+' <b>'+am+'</b> '+time+' <input type="hidden" name="stats_settings[]" rel="'+namev+'|'+opv+'" value="'+val+'"/> <i class="glyphicon glyphicon-remove pointer" onclick="$(this).parent().remove()"></i><br/></span>');
		
		notify('warning', lang.settings_not_saved_yet);
		return false;
	});
	
	$(document).on('click', ".adv_settings_save", function(){ 
		notify('wait', 'Saving advance settings...');
		$.post(ajax_url, $('#adv_settings_form').serialize(), 
		function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', 'Settings saved');
				$('.adv-settings').modal('hide');
			}
		});
	});
	
	$(document).on('click', ".adv_group_settings_save", function(){ 
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, $('#adv_group_settings_form').serialize(), 
		function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', 'Settings saved');
				$('.adv-settings-group').modal('hide');
			}
		});
	});
	
	$(document).on('click', ".stop_bumping", function(){ 
		var elem = $(this);
		var log_id = elem.parents('tr:first').attr('rel');
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'stop_bumping': log_id
		}, 
		function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.bumping_stopped);
				elem.hide();
			}
		});
	});
	
	$(document).on('click', ".schedule_reset", function(){
		if(!confirm_action(lang.post_log_warning, $(this)))return false;
		var sch_id = $('#adv_settings_sch_id').val();
		var elem = $('tr[rel="'+sch_id+'"]'); 
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'schedule_reset': sch_id
		}, 
		function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.sch_reset_ok);
				refresh_tab('schedules');
			}
		});
	});
	
	$(document).on('click', ".schedule_group_stats_remove", function(){
		if(!confirm_action(lang.post_del_tr_confirm, $(this)))return false;
		var sch_id = $('#adv_settings_sch_group_id').val();
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'schedule_group_stats_remove': sch_id
		}, 
		function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.op_ok);
			}
		});
	});
	
	$(document).on('click', ".schedule_stats_remove", function(){
		if(!confirm_action(lang.trig_warning, $(this)))return false;
		var sch_id = $('#adv_settings_sch_id').val();
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'schedule_stats_remove': sch_id
		}, 
		function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.op_ok);
			}
		});
	});
	
	$(document).on('click', ".schedule_group_reset", function(){
		if(!confirm_action(lang.post_log_warning, $(this)))return false;
		var sch_id = $('#adv_settings_sch_group_id').val();
		var elem = $('tr[rel="'+sch_id+'"]'); 
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'schedule_group_reset': sch_id
		}, 
		function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				notify('success', lang.sch_reset_ok_all);
				refresh_tab('schedules');
			}
		});
	});
	
	$(document).on('click', ".fb_import_btn", function(){
		var ids = $('#fb_group_event_ids').val();
		var fb_id = $('#import_fb_id').val();
		if(ids == '' || fb_id == '')return notify('error', lang.insert_id_html);
		 
		var type = $('#import_type').val();
		if(type == 'HTML'){
			var html = ids + '</html>';
			var i = 0;
			/*ids = html.match(/<h3([\s\S]*)<h3/g);
			if(ids === null){
				ids = html.match(/<h3([\s\S]*)<\/html/g);
				if(ids === null){
					return notify('error', lang.no_group_id);
				}
			}
			ids = ids.join('');*/
			var m = html.match(/groups\/([0-9]+)/g);
			if(m === null)return notify('error', lang.no_group_id);
			if(m.length <= 0)return notify('error', lang.no_group_id);
			
			m = m.join("\n");
			m = m.replace(/groups\//g, '');
			if(m == '')return notify('error', lang.no_group_id);			
			ids = m;	
		}
		
		notify('wait', lang.requesting+'...');
		$.post(ajax_url, {
			'fb_group_event_ids': ids,
			'owner_fb_id': fb_id
		}, 
		function(response){
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				tabs_config['tab_groups_loaded'] = 0;
				tabs_config['tab_events_loaded'] = 0;
				tabs_config['tab_schedules_loaded'] = 0;
				notify('success', data.msg);
				$('.fb-import-modal').modal('hide');
			}
		});
	});
	
	$(document).on('click', '.vq_delete', function(){
		if(!confirm_action(lang.delete_warning, $(this)))return false;
		
		var qid = $(this).parents('tr:first').attr('rel');
		notify('wait', lang.requesting+'...');
		
		$.post(ajax_url, {
			'video_queue_delete': qid
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				notify('success', lang.task_deleted);
				$('tr[rel="'+qid+'"]').html('<td colspan="10"><div class="alert alert-success">'+lang.task_deleted+'</div></td>');
			}
		});
	});
	
	$(document).on('click', '.create_slideshow', function(){
		var fid = $('#folder_id').val();
		var dur = $('#slide_duration').val();
		var type = $('#slide_type').val();
		
		if(fid == '' || dur == '')return notify('error', lang.all_fields_req);
		
		notify('wait', lang.requesting+'...');
		
		$.post(ajax_url, {
			'queue_slideshow': 1,
			's_folder_id': fid,
			'slide_duration': dur,
			'slide_type': type
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				notify('success', lang.task_queued);
			}
		});
	});
	
	$(document).on('click', '.btn-check-limits', function(){
		var d = $.parseJSON(atob($(this).attr('rel')));
		var elem = $('.m-limits-modal').find('.modal-body');
		var i = 0;
		var html = '';
		$.each(d, function(k, v){
			if(k != 'plan_id' && k != 'display_on_site' && k != 'plan_features' && k != 'plan_subtitle' && k != 'is_preferred'){
				if(k == 'allowed_storage')v = parseInt(v/1024/1024)+ ' MB';
				if(k != 'plan_price'){
					if(v == 0)v = '<span style="color:red">'+lang.disabled+'</span>';
					else if(v == 1)v = lang.enabled;
				}
				if(!i)html += '<div class="row">';
				html += '<div class="col-lg-4">'+k+'</div><div class="col-lg-2">'+v+'</div>';
				i++;
				if(i >= 2){
					html += '</div>';
					i = 0;	
				}
			}
		});
		if(i)html += '</div>';
		elem.html(html);
		$('.m-limits-modal').modal();
	});
	
	$(document).on('click', '.save_lang', function(){
		var ll = $('#select_lang').val();
		if(ll.match(/[^a-z0-9_]/)){
			return notify('error', lang.inv_lang_name);	
		}
		
		var d = new Date();
   	 	d.setTime(d.getTime() + (365*24*60*60*1000));
    	var expires = "expires="+d.toUTCString();
		document.cookie="ninja_lang="+ll+";path=/;expires="+expires;
		window.location = window.location.href;
	});
	
	$(document).on('click', '.sel_all', function(){
		var type = $(this).attr('rel');
		var elem = null;
		
		if(type == 'files'){
			elem = $('.file-checkbox');	
		}
		else if(type == 'groups'){
			elem = $('.group-checkbox');
		}
		else if(type == 'events'){
			elem = $('.event-checkbox');
		}
		else if(type == 'fanpages'){
			elem = $('.fanpage-checkbox');
		}
		else if(type == 'folders'){
			elem = $('.folder-checkbox');
		}
		else if(type == 'rss'){
			elem = $('.rss-checkbox');
		}
		else if(type == 'schedule_groups'){
			elem = $('.schedule_group-checkbox');
		}
		else if(type == 'schedules'){
			elem = $('.schedule-checkbox');
		}
		else if(type == 'categories'){
			elem = $('.category-checkbox');
		}
		
		elem.prop('checked', true);	
	});
	
	$(document).on('click', '.inv_sel', function(){
		var type = $(this).attr('rel');
		var elem = null;
		
		if(type == 'files'){
			elem = $('.file-checkbox');
		}
		else if(type == 'groups'){
			elem = $('.group-checkbox');
		}
		else if(type == 'events'){
			elem = $('.event-checkbox');
		}
		else if(type == 'fanpages'){
			elem = $('.fanpage-checkbox');
		}
		else if(type == 'folders'){
			elem = $('.folder-checkbox');
		}
		else if(type == 'rss'){
			elem = $('.rss-checkbox');
		}
		else if(type == 'schedule_groups'){
			elem = $('.schedule_group-checkbox');
		}
		else if(type == 'schedules'){
			elem = $('.schedule-checkbox');
		}
		else if(type == 'categories'){
			elem = $('.category-checkbox');
		}
		
		elem.each(function(){
			if($(this).is(':checked') == true)$(this).prop('checked', false);
			else $(this).prop('checked', true);
		});
	});
	
	$(document).on('click', '.del_selected', function(){
		if(!confirm_action(lang.delete_warning, $(this)))return false;
		
		var type = $(this).attr('rel');
		
		var tt = type;
		var kk = null;
		
		if(type == 'groups')kk = $('.group-checkbox:checked');
		else if(type == 'events')kk = $('.event-checkbox:checked');
		else if(type == 'folders')kk = $('.folder-checkbox:checked');
		else if(type == 'rss')kk = $('.rss-checkbox:checked');
		else if(type == 'schedule_groups')kk = $('.schedule_group-checkbox:checked');
		else if(type == 'schedules')kk = $('.schedule-checkbox:checked');
		else if(type == 'fanpages'){
			tt = 'pages';
			kk = $('.fanpage-checkbox:checked');
		}
		else if(type == 'files')kk = $('.file-checkbox:checked');
		else if(type == 'categories')kk = $('.category-checkbox:checked');
		
		var length = kk.length;		
		if(length <= 0)return notify('error', lang.no_item_sel);			
		var index = 0;
		var j = 0;
		var sleep = 1000;
		
		notify('wait', lang.please_wait+'...');
			
		var bulk_delete = function() {
		  for (; index < length; index++) {
			td = $(kk[index]);
			if(type == 'files'){
				t = td.parents('.file-holder:first').find('.delete_file');
				j_max = 4;
			}
			else if(type == 'folders'){
				t = td.parents('.folders:first').find('.folder-delete');
				j_max = 1;
			}
			else if(type == 'rss'){
				t = td.parents('.rss:first').find('.rss-delete');
				j_max = 10;
			}
			else if(type == 'schedule_groups'){
				t = td.parents('tr:first').find('.schedule-group-delete');
				j_max = 5;
			}
			else if(type == 'schedules'){
				t = td.parents('tr:first').find('.del_schedule');
				j_max = 10;
			}
			else if(type == 'categories'){
				t = td.parents('tr:first').find('.cat_delete');
				j_max = 15;
			}
			else{
				t = td.parents('.'+tt+':first').find('.pp_del');
				j_max = 10;
			}
			
			t.addClass('silent');
			t.click();		
			j++;
			if (index + 1 < length && j >= j_max) {
				j = 0;
				index++;
				return setTimeout(bulk_delete, sleep);
			}
			if(index + 1 >= length)notify('success', lang.op_ok);
		  }
		}
		bulk_delete();	
	});
	
	$(document).on('click', '.resume_selected, .stop_selected', function(){
		var type = $(this).attr('rel');
		
		var op = 'resume';
		if($(this).hasClass('stop_selected'))op = 'stop';
		
		if(type == 'schedule_groups')var kk = $('.schedule_group-checkbox:checked');
		else var kk = $('.schedule-checkbox:checked');
		
		var length = kk.length;
		if(length <= 0)return notify('error', lang.no_item_sel);			
		var index = 0;
		var j = 0;
		var sleep = 1000;
		var ids = [];
			
		for (; index < length; index++) {
			tr = $(kk[index]).parents('tr:first');
			ids.push(tr.attr('rel'));
		}
		if(ids.length <= 0)return notify('error', lang.no_item_sel);
		
		notify('wait', lang.please_wait+'...');
		
		$.post(ajax_url, {
			'bulk_stop_resume': 1,
			'type': type,
			'ids': ids.join(','),
			'operation': op
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				notify('success', lang.op_ok);
				if(type == 'schedule_groups'){
					tabs_config['tab_schedules_loaded'] = 0;
					refresh_tab('schedules');
				}
				else window.location.reload();
			}
		});
			
	});
	
	
	$(document).on('click', '.add-bulk-caption-btn', function(){
		var cap = $('#bulk-caption').val();
		var kk = $('.file-checkbox:checked');
		var length = kk.length;
		if(cap == '')return notify('error', lang.caption_missing);		
		if(length <= 0)return notify('error', lang.no_item_sel);			
		var index = 0;
		var j = 0;
		var sleep = 1000;
		
		notify('wait', lang.please_wait+'...');
		bulk_caption_save_mode = 1;
			
		var bulk_caption = function() {
		  for (; index < length; index++) {
			td = $(kk[index]);
			t = td.parents('.file-holder:first');
			jj = t.find('textarea');
			jj.val(cap);
			jj.trigger($.Event('keypress', {which: 13}));
			j_max = 10;
			
			j++;
			if (index + 1 < length && j >= j_max) {
				j = 0;
				index++;
				return setTimeout(bulk_caption, sleep);
			}
			if(index + 1 >= length){
				bulk_caption_save_mode = 0;
				$('.add-bulk-caption-modal').modal('hide');
				notify('success', lang.op_ok);
			}
		  }
		}
		bulk_caption();	
	});
	
	$(document).on('click', '.clear_logs', function(){
		notify('wait', lang.requesting+'...');
		
		$.post(ajax_url, {
			'clear_logs': 1,
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{
				notify('success', lang.op_ok);
				tabs_config['tab_logs_loaded'] = 0;
				refresh_tab('logs');
			}
		});
	});
	
	$(document).on('click', '.edit_sch_time', function(){
		$(this).parents('td:first').find('.edit_sch_submit').show().focus();
	});
	
	$(document).on('click', '.post_now_submit', function(){
		var last_index = 0;
		if($('#post_now_submit_index').length > 0){
			last_index = parseInt($('#post_now_submit_index').val());
		}
		else{
			$('#stop_post').val(0);
			$('.posted_now').html('');
			$('body').append('<input type="hidden" id="post_now_submit_index" value="0"/>');	
		}
		
		var pages = $('input[name="selected_pages[]"]');
		if(pages.length <= 0)return notify('error', lang.no_item_sel);
		if(pages.length <= last_index){
			$('.posting_now').html('<b>'+lang.done+'</b>');
			$(this).show();
			return $('#post_now_submit_index').remove();
		}
		
		var file_id = $('#p_file_id').val();
		var watermark = $('#watermark').val();
		var watermark_position = $('#watermark_position').val();
		var post_delete_freq = $('#post_delete_freq').val();
		var post_delete_freq_type = $('#post_delete_freq_type').val();
		var post_delete_action = $('#post_delete_action').val();
	
		var self = $(this);
		self.hide();
		var s_pages = pages.slice(last_index, last_index + 1);
		
		$.each(s_pages, function(){
			post_delay = $('#post_delay').val().split(',');
			delay_min = 0;
			delay_max = 0;
			
			delay_min = parseInt(post_delay[0]);
			if(post_delay[1] != null)delay_max = parseInt(post_delay[1]);
			
			if(delay_min == 0 || isNaN(delay_min))delay_min = 10;
			if(delay_max < 0 || isNaN(delay_max))delay_max = 0;
			
			if($('#stop_post').val() == '1'){
				self.show();
				return $('.posting_now').html(lang.stopped);		
			}
			
			p = $(this).val();
			n = $(this).parents('tr:first').find('td').text();
			
			d = {
					'file_id': file_id, 
					'page_id': p, 
					'post_now': 1, 
					'watermark': watermark, 
					'watermark_position': watermark_position,
					'post_delete_freq': post_delete_freq,
					'post_delete_freq_type': post_delete_freq_type,
					'post_delete_action': post_delete_action,
					'page_name': n
				};
			
			var poster_func = function(d){
				var li = parseInt($('#post_now_submit_index').val());
				n = d.page_name;
				$('.posting_now').html(lang.processing+' : <b>'+n+'</b> <img src="images/loader.gif" width="20"/>');
			
				if($('#stop_post').val() == '1'){
					self.show();
					return $('.posting_now').html(lang.stopped);		
				}
				
				if(pages.length <= li + 1){
					d['delete_now'] = 1;
				}
				else d['delete_now'] = 0;
				
				var response = ayncAjaxMakePost(d);
				var data = $.parseJSON(response);
				
				if(data.error != ''){
					$('.posted_now').append('<tr><td colspan="10"><b>' + n + '</b> : '+data.error+'</td></tr>');
				}
				else{
					h = lang.posted_to + ' <b>' + n + '</b> : <a href="'+data.post_link+'" target="_blank">'+data.post_link+'</a>';
					$('.posted_now').append('<tr><td colspan="10">'+h+'</td></tr>');
				}
			
				$('#post_now_submit_index').val(li + 1);
				$('.post_now_submit').click();
			}
			
			var poster_timer = function(sec, d){
				if($('#stop_post').val() == '1'){
					self.show();
					$('#post_now_submit_index').remove();
					return $('.posting_now').html(lang.stopped);		
				}
				$('.posting_now').html(lang.waiting+' : <b>'+parseInt(sec)+'</b>&nbsp;&nbsp;&nbsp;<img src="images/loader.gif" width="20"/>');	
				sec--;
				if(sec > 0)setTimeout(function(){poster_timer(sec, d)}, 1000);
				else poster_func(d);
			}
			
			var process_func = function(d){
				var li = parseInt($('#post_now_submit_index').val());
				if(delay_min == 0 || !li){
					var sec = 1;
					poster_timer(sec, d);
				}
				else{
					if(delay_max == 0){
						var sec = delay_min;
						poster_timer(sec, d);
					}
					else{
						var sec = Math.floor(Math.random() * (delay_max - delay_min + 1)) + delay_min;
						poster_timer(sec, d);
					}
				}
			}
			
			process_func(d);
			return false;
			
		});
	});
	
});

function ayncAjaxMakePost(data) {
    return $.ajax({
        type: "POST",
        url: ajax_url,
		data: data,
        async: false
    }).responseText;
}

/**
 * add selected page/group to selected pages table
 */
function add_elem_to_selected_page(elem)
{
	var id = elem.val();
	if(id == '')return;
	var site = elem.attr('rel');
	var sid = elem.attr('rel-owner');
	var name = elem.html();
	var elem = $('.schedule-selected-pages').find('table');
	var i = elem.find('tr[rel="'+site+'-'+id+'-'+sid+'"]').length;
	if(i > 0)return notify('error', lang.pro_already_sel + " : "+name);
	elem.append('<tr rel="'+site+'-'+id+'-'+sid+'" rel-site="'+site+'" rel-id="'+id+'"><td>'+name+'&nbsp;&nbsp;<i class="glyphicon glyphicon-remove pointer" onclick="$(this).parents(\'tr:first\').remove()"></i><input type="hidden" name="selected_pages[]" value="'+site+'|'+id+'|'+sid+'"/></td></tr>');
}


function refresh_tab(id)
{
	$('.gritter-item-wrapper').remove();
	$('.modal').modal('hide');
	$('.modal-backdrop').hide();
	
	$('#'+id).html('<br/><div class="text-center"><h3>'+lang.loadin_please_wait+'...</h3><br/><img src="images/loader.gif"/></div>');				
	$.post(ajax_url, {
		'load_tab': id,
		'get_params': get_params
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			$('#'+id).html('<br/><div class="alert alert-danger">'+data.error+'</div>');
		}
		else{
			tabs_config['tab_'+id+'_loaded'] = 1;
			$('#'+id).html(data.html);	
		}
	});
}

function update_meta_form(d)
{
	$('#file_meta_id').val(d.file_id);
	$('#file_meta_desc').val($('<div/>').html(d.description).text());
	if(d.category != ''){
		$('#file_meta_category').val(d.category.split('|')[1]);
	}
	else $('#file_meta_category').val('');
	$('#file_meta_privacy').val(d.privacy);
	$('#file_meta_tags').val($('<div/>').html(d.tags).text());
}

function update_link_form(d)
{
	$('#link_meta_id').val(d.file_id);
	$('#link_meta_title').val(d.link_title);
	$('#link_meta_desc').val(d.link_desc);
	$('#link_meta_image').val(d.link_image);
}

function update_schedule_table(d, action)
{
	if($('.schedule-table').length <= 0){
		$('.modal-backdrop').hide();
		return refresh_tab('schedules')
	}
	var html = 
			'<td><input type="checkbox" class="schedule_group-checkbox">&nbsp;&nbsp;'+d.schedule_group_name+'</td>'+
			'<td>'+(d.is_done == 1 ? '<span class="label label-success">'+lang.done+'</span>' : '<span class="label label-info">'+lang.processing+'</span>')+'</td>'+
			'<td>'+(d.is_active == 1 ? '<span class="label label-success">'+lang.active+'</span>' : ( d.is_active == 2 ? '<span class="label label-danger">'+lang.suspended+'</span>' : '<span class="label label-info">'+lang.stopped+'</span>'))+'</td>'+
			'<td>'+
				'<div style="max-width:100px">'+
					'<span class="time">'+d.next_post+'</span>&nbsp;&nbsp;'+
					'<i class="glyphicon glyphicon-edit pointer edit_sch_time"></i>'+
					'<input type="text" class="sch_time form-control edit_sch_submit medium-input2" rel="sch_group"'+
					'value="'+d.next_post2+'" style="display:none"/>'+
				'</div>'+
			'<td>'+
				'<div style="max-width:100px">'+d.last_post+'</div>'+
			'</td>'+
			'<td>'+d.total_schedules+'</td>'+
			'<td>'+
				'<button class="btn btn-sm btn-info schedule-group-edit"><i class="glyphicon glyphicon-edit pointer"></i>&nbsp;&nbsp;'+lang.edit+'</button>&nbsp;&nbsp;&nbsp;'+
				'<a class="btn btn-sm btn-primary schedule-explore" href="'+d.log_url+'"><i class="glyphicon glyphicon-globe pointer"></i>&nbsp;&nbsp;'+lang.view_posts+'</a><br/><br/>'+
				'<a href="'+d.explore_url+'" class="btn btn-sm btn-danger schedule-explore"><i class="glyphicon glyphicon-search pointer"></i>&nbsp;&nbsp;'+lang.explore+'</a>&nbsp;&nbsp;&nbsp;'+
				'<button class="btn btn-sm btn-warning schedule-group-delete"><i class="glyphicon glyphicon-trash pointer"></i>&nbsp;&nbsp;'+lang.delete_+'</button>'+
			'</td>';
				 
	if(action == 'append')
		$('.schedule-table').append('<tr rel="'+d.schedule_group_id+'" id="sch-grp-'+d.schedule_group_id+'" data-json="'+btoa(JSON.stringify(d))+'">'+html+'</tr>');
	else{
		action.html(html);
		action.attr('data-json', btoa(JSON.stringify(d)));
	}
	//highlight
	$('#sch-grp-'+d.schedule_group_id).effect('highlight', {}, 5000);
	$('#sch-grp-'+d.schedule_group_id).find('.sch_time').datetimepicker({
		controlType: 'select',
		dateFormat: 'yy-mm-dd',
		timeFormat: 'hh:mm:00 TT',
		oneLine: true,
		onClose: function(dateText, inst) {
			datetimepicker_submit(dateText, inst, $(this));	
		}
	});
}

function reset_schedule_modal()
{
	prepare_add_schedule_interval_selector('minutes', $('#post_freq'));
	prepare_add_schedule_interval_selector('minutes', $('#post_delete_freq'));
	prepare_add_schedule_interval_selector('hour_ranges', $('#post_only_from, #post_only_to'));
	$("#post_start_from, #post_end_at").datepicker({dateFormat: 'yy-mm-dd', minDate: 0});
	$('.schedule-selected-pages').find('table').html('');
	$('input[name="selected_pages[]"]').remove();
	$('input[name="schedule_save"]').val('-1');
	$('.add-schedule-modal').find('.modal-title').html(lang.create_new_sch);
	$('#post_freq_type, #post_delete_freq_type').val('minutes');
	$('.add-schedule-submit-btn').attr('disabled', false);
	$('#schedule_group_name, #slide_type, #post_start_from, #post_end_at').val('');
	$('#do_repeat, #auto_delete_file, #onetime_post, #repeat_campaign, #sync_post').prop('checked', false);
	$('#is_active').prop('checked', true);
	$('#slide_duration').val('3');
	$('#post_sequence').val('random');
	$('.add-sc-footer-warning').html('').hide();
	$('.schedule-selected-pages').css('height', '');
	$('.sl_type_choose').hide();
	$('#social_ids, #folder_id').val('').trigger("change");
	$('.add-schedule-advanced-opts').hide();
}

function create_uploader()
{
	$(".dropzone").dropzone({
		url: upload_url ,
		maxFilesize: 1024, //in MB
		acceptedFiles: '.jpg, .png, .jpeg, .flv, .mp4, .mov, .wmv, .avi, .mpeg, .m4v, .mkv, .3gp, .mpg, .webm',
		init: function () {
		  /**
		   * Clear file list when upload button is toggled
		   */
		  var mydropzone = this;
		  $(".btn-clear-upload").on("click", function() {
			mydropzone.removeAllFiles();
		  });
	  
	  	  this.on("error", function(file,response) {
			notify('error', response);
		  });
	  
	  	  /**
		   * Code to run when uploade done
		   */
		  this.on("complete", function (file) {
			var mydropzone = this;
			if(file.xhr == null)return false;
			var data = $.parseJSON(file.xhr.response);
			if(data.error != ''){
				notify('error', data.error);	
			}
			else{
				if(data.file_type == 'video'){
					var img = new Image();
					img.src = 'storage/' + data.thumb;
					dropzone_resize_thumb(img, mydropzone, file);						
				}

				var html = get_file_template(data);
				append_file_html(html);
			}
		});
	  }
	});
}

function save_status(fileId, caption)
{
	var folderId = $('.folder-header').attr('rel');
	var type = (fileId == '' ? 'status' : 'caption');
	if(bulk_caption_save_mode == 0)notify('wait', lang.saving+' '+ type +'...');
	$.post(ajax_url, {
		'fileId' : fileId,
		'folderId': folderId,
		'caption': caption,
		'saveCaption': 1	
	}, function(response){
		$('.file-'+fileId).find('textarea').attr('disabled', false);
		var data = $.parseJSON(response);
		if(data.error != '')notify('error', data.error);
		else{
			$('#add-text-post').val('');
			if(bulk_caption_save_mode == 0)notify('success', type + ' '+lang.succ_saved);
			if(fileId == ''){
				var file = [];
				file.fileId = data.fileId;
				file.orgName = '<br/>';
				file.thumb = file.file_link = 'images/text.png';
				file.file_link = 'data:text/plain;charset=utf-8;base64,'+btoa(caption);
				file.org_caption = caption;
				file.file_type = 'text';
				var html = get_file_template(file);	
				append_file_html(html);
			}
			else{
				$('.file-'+fileId).find('.dlink').attr('href', 'data:text/plain;charset=utf-8;base64,'+btoa(caption));	
			}	
		}
	});
}

function notify(type, message)
{
	$('.gritter-item-wrapper').remove();
	
	time = 5000;
	image = '';
	sticky = false;
	class_name = '';
	
	if(type == 'wait'){
		title = lang.please_wait+'...';
		sticky = true;
	}
	else if(type == 'success'){
		title = lang.success+'!';
	}
	else if(type == 'error'){
		title = lang.error+'!';
	}
	else if(type == 'warning'){
		title = lang.warning+'!';
	}
	
	$.gritter.add({
		title: title,
		text: message,
		image: image,
		sticky: sticky,
		time: time,
		class_name : class_name
	});
}

function confirm_action(msg, elem, extra)
{
	if($('#confirm_action').length == 0){
		show_confirm_box(msg, elem, extra);
		return false;
	}
	
	var ok = false;
	if($('#__confirm__').val() == 1)ok = true;
	$('#confirm_action').modal('hide');
	$('#confirm_action').remove();
	if($('.modal-backdrop').length > 1)$('.modal-backdrop').eq(1).remove();
	else $('.modal-backdrop').eq(0).remove();
	return ok;
}

function show_confirm_box(msg, elem, extra){
	
	$('body').append('<div class="modal fade" id="confirm_action" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">'+lang.conf_required+'!</h4></div><div class="modal-body"><p>'+msg+'</p>'+(extra != null ? '<p>'+extra+'</p>' : '')+'</div><div class="modal-footer"><button type="button" class="btn btn-sm btn-success" data-dismiss="modal">'+lang.cancel+'</button><button type="button" class="btn btn-sm btn-danger" id="confirm">'+lang.ok+'</button></div></div></div><input type="hidden" id="__confirm__" value="0"/></div>');	

	$('#confirm_action').modal();	
	$('#confirm_action').find('.modal-footer #confirm').on('click', function(){
		$('#confirm_action').modal('hide');
		$('#__confirm__').val(1);
		elem.click();
	});
	$('#confirm_action').on('hidden.bs.modal', function () {
  		if($('#__confirm__').val() == 0){
			$('#confirm_action').remove();	
		}
	});
}

/**
 * Function to purify text to remove malicious codes
 */
function purify_text(str)
{
	str.replace('<', '&lt;');
	str.replace('>', '&gt;');
	str.replace('"', '&quot;');
	return str;
}

/**
 * Function to prepare file-pane template
 */
function get_file_template(file)
{
	var l = 0;
	var mm = file.org_caption.match(/(https?:\/\/[^\s]+)/g);
	if(mm != null)l = 1;
	var s = (10*100)/max_file_per_row;
	html = '';
	
	html += '<div class="col-lg-'+(12/max_file_per_row)+' file-holder file-'+file.fileId+'" rel="'+file.fileId+'" rel-type="'+file.file_type+'">'+
				'<div class="file-holder-row-parent">'+
					'<div class="row file-holder-row box effect7">'+
						'<div class="row">'+
							'<div class="col-lg-12 file-orig-label" style="max-width:'+s+'px"><input type="checkbox" class="file-checkbox"/>&nbsp;&nbsp;'+
							(file.orgName == '' ? '<br/>' : file.orgName)+'</div>'+
						'</div>'+
						'<div class="row" style="margin-left:15px">'+
							'<div class="col-lg-12">'+
									'<div class="pull-right">'+
										'<span class="label label-'+(file.file_type == 'image' ? 'success' : 'danger')+'">'+file.file_type+'</span>&nbsp;&nbsp;'+
										'<span class="post_now pointer">'+
											'<i class="glyphicon glyphicon-share" title="'+lang.post_now+'"></i>'+
										'</span>&nbsp;&nbsp;'+
										'<span class="'+(file.file_type == 'video' ? 'edit_meta' : ( file.file_type == 'text' ? 'edit_link' : 'editor_open'))+' pointer">'+
											'<i class="glyphicon glyphicon-cog" title="'+lang.edit+'"></i>'+
										'</span>&nbsp;&nbsp;'+
										'<span class="dwnload_file pointer">'+
											'<a href="'+file.file_link+'" target="_blank" '+(file.file_type == 'text' ? 'download="status.txt"' : '')+' class="dlink"><i class="glyphicon glyphicon-download-alt" title="'+lang.dw_file+'"></i></a>'+
										'</span>&nbsp;&nbsp;'+
										'<span class="delete_file pointer">'+
											'<i class="glyphicon glyphicon-trash" title="'+lang.del_file+'"></i>'+
										'</span>&nbsp;&nbsp;'+
									'</div>'+		
								'</div>'+
						'</div>'+
					'</div><br/>'+
					'<div class="row">'+
						'<div class="col-lg-12 file-thumb-preview" style="background:url(\''+file.thumb+'\')">'+
							'<div class="bottom-right">'+(file.duration != null ? '<span class="label label-info">'+file.duration+'</span>' : (l == 0 ? '' : '<span class="label label-info">Link</span>'))+'</div>'+
						'</div>'+
					'</div><br/>'+
					'<div class="row">'+
						'<div class="col-lg-12 file-caption-preview">'+
							'<textarea class="form-control editor_text" placeholder="'+lang.type_caption+'">'+file.org_caption+'</textarea>'+
						'</div>'+
					'</div>'+
				'</div>'+
			  '</div>';
	return html;
}

/**
 * Function to append file html to the end of file container
 */
function append_file_html(html)
{
	if($('.file-pane-slave').length > 0){
		var last_div = $('.file-pane-slave').last().find('.file-holder').length;
		if(last_div < max_file_per_row){
			$('.file-pane-slave').last().append(html);	
		}
		else{
			$('.file-pane-master').append('<div class="row file-pane-slave">'+html+'</div><br/><br/>');	
		}	
	}
	else{
		$('.no-file-error').slideUp();
		btns = '<h4>'+
				lang.options+
				'<div class="pull-right">'+
					'<button class="btn btn-sm btn-default sel_all" rel="files">'+lang.select_all+'</button>&nbsp;&nbsp;'+
					'<button class="btn btn-sm btn-primary inv_sel" rel="files">'+lang.inv_selected+'</button>&nbsp;&nbsp;'+
					'<button class="btn btn-sm btn-danger del_selected" rel="files">'+lang.del_selected+'</button>&nbsp;&nbsp;'+
					'<button class="btn btn-sm btn-success cap_selected" rel="files" onclick="$(\'.add-bulk-caption-modal\').modal()">'+lang.caption_sel+'</button>'+
				'</div>'+
			  '</h4><hr/>';
		$('.file-pane-master').html(btns+'<div class="row file-pane-slave">'+html+'</div><br/><br/>');
	}
}
 
 
/**
 * Function to resize dropzone video thumbnail
 */
function dropzone_resize_thumb( src, mydropzone , file) {
   var tmp = new Image(), canvas, context, cW, cH;
 
   type = 'image/png';
   quality = 1;
 
   cW = 120;
   cH = 120;
 
   tmp.src = src.src;
   tmp.onload = function() { 
	   canvas = document.createElement( 'canvas' );
	   canvas.width = cW;
	   canvas.height = cH;
	   context = canvas.getContext( '2d' );
	   context.drawImage( tmp, 0, 0, cW, cH );
	   mydropzone.emit("thumbnail", file, canvas.toDataURL( type, quality ));
   }
}

/**
 * Function make file-pane drag droppable
 */
function makeElementAsDragAndDrop(elem) {
	$(elem).draggable({
		revert: "invalid",
		cursor: "move",
		helper: "clone"
	});
	$(elem).droppable({
		drop: function(event, ui) {
			var $dragElem = $(ui.draggable).clone().replaceAll(this);
			$(this).replaceAll(ui.draggable);
			makeElementAsDragAndDrop(this);
			makeElementAsDragAndDrop($dragElem);
		}
	});
}

/**
 * Function remove drag droppable
 */
function removeDragAndDrop(elem){
	$(elem).draggable("destroy");
	$(elem).droppable("destroy");
	//$('.dragdrop').removeClass('dragdrop');	
}

/**
 * Function to submit form in iframe
 */
function submit_iframe(action)
{
	var target = 'iframe_submit';
	var iframe = $('iframe[name='+target+']'); 
	if(iframe.length == 0){
		$('body').append('<iframe name="iframe_submit" style="display: none"></iframe>');
	}

	iframe = $('iframe[name='+target+']');
	if(iframe.length > 0){
		iframe.unbind('load.ajaxsubmit').bind( 'load.ajaxsubmit', function(){
			var response = $(this).contents().text(); 
			var data = $.parseJSON(response);
			if(data.error != '')notify('error', data.error);
			else{
				/*
				if(action == 'textUpload'){
						
				}
				*/	
			} 
		});    
	}
	else{
		notify('error', lang.sorry_err);	
	}
}

function create_tools_uploader()
{
	$(".dropzone").dropzone({
		url: upload_url ,
		maxFilesize: 5, //in MB
		acceptedFiles: '.jpg, .png, .jpeg',
		init: function () {
		  /**
		   * Clear file list when upload button is toggled
		   */
		  var mydropzone = this;
		  $(".btn-clear-upload").on("click", function() {
			mydropzone.removeAllFiles();
		  });
	  
	  	  /**
		   * Code to run when uploade done
		   */
		  this.on("complete", function (file) {
			var mydropzone = this;
			var data = $.parseJSON(file.xhr.response);
			if(data.error != ''){
				notify('error', data.error);	
			}
			else{
				notify('success', lang.up_succ_refresh);
				//var html = get_file_template(data);
				//append_file_html(html);
			}
		});
	  }
	});
}

function prepare_add_schedule_interval_selector(type, elem)
{
	switch(type){
		case "minutes":
			data = minutes_selectors;
		break;
		
		case "hours":
			data = hours_selectors;
		break;
		
		case "days":
			data = days_selectors;
		break;
		
		case "weeks":
			data = weeks_selectors;
		break;
		
		case "months":
			data = months_selectors;
		break;
		
		case "years":
			data = years_selectors;
		break;
		
		case "hour_ranges":
			data = hour_ranges;
		break;			
	}
	
	elem.html('<option value="">'+lang.select_one+'</option>');
	for(i = 0; i < data.length; i++)elem.append('<option value="'+data[i]+'">'+data[i]+'</option>');
}

/**
 * Codes for clock display
 */
function start_clock(date)
{
	var mshort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var h = date.getUTCHours();
    var m = date.getUTCMinutes();
    var s = date.getUTCSeconds();
	var d = date.getUTCDate();
	var mon = mshort[date.getUTCMonth()];
	var y = date.getUTCFullYear();
	var ampm = h >= 12 ? 'PM' : 'AM';
  	h = h % 12;
  	h = h ? h : 12;
    h = formatTime(h);
    m = formatTime(m);
    s = formatTime(s);
	d = formatTime(d);
	
    $('.clock').find('.clock-display').html(d+'-'+mon+'-'+y+' '+h+":"+m+":"+s+" "+ampm);
	
	date.setSeconds(date.getSeconds() + 1);
    timer = setTimeout(function(){start_clock(date)}, 1000);
}

/**
 * Add leading zero
 */
function formatTime(i) {
    if (i < 10)i = "0" + i;
    return i;
}

function getCaret(el) 
{
    if (el.selectionStart) {
        return el.selectionStart;
    } else if (document.selection) {
        el.focus();
        var r = document.selection.createRange();
        if (r == null) {
            return 0;
        }
        var re = el.createTextRange(),
            rc = re.duplicate();
        re.moveToBookmark(r.getBookmark());
        rc.setEndPoint('EndToStart', re);
        return rc.text.length;
    }
    return 0;
}

function datetimepicker_submit(dateText, inst, elem)
{
	setTimeout(function(){
		if($('.sch_time:visible').length <= 0)return;
		if(inst.lastVal != elem.val())elem.trigger(jQuery.Event('keypress', {which: 13}))
	}, 200);
}

function scroll_to_last_sch() {
	var hq = $(".schedule-table").find('tr:last').offset().top
    jQuery("html, body").animate({ scrollTop: hq }, 500);
}

/**
 * gritter js code
 */
 (function(b){b.gritter={};b.gritter.options={position:"",class_name:"",fade_in_speed:"medium",fade_out_speed:1000,time:6000};b.gritter.add=function(f){try{return a.add(f||{})}catch(d){var c="Gritter Error: "+d;(typeof(console)!="undefined"&&console.error)?console.error(c,f):alert(c)}};b.gritter.remove=function(d,c){a.removeSpecific(d,c||{})};b.gritter.removeAll=function(c){a.stop(c||{})};var a={position:"",fade_in_speed:"",fade_out_speed:"",time:"",_custom_timer:0,_item_count:0,_is_setup:0,_tpl_close:'<a class="gritter-close" href="#" tabindex="1">Close Notification</a>',_tpl_title:'<span class="gritter-title">[[title]]</span>',_tpl_item:'<div id="gritter-item-[[number]]" class="gritter-item-wrapper [[item_class]]" style="display:none" role="alert"><div class="gritter-top"></div><div class="gritter-item">[[close]][[image]]<div class="[[class_name]]">[[title]]<p>[[text]]</p></div><div style="clear:both"></div></div><div class="gritter-bottom"></div></div>',_tpl_wrap:'<div id="gritter-notice-wrapper"></div>',add:function(g){if(typeof(g)=="string"){g={text:g}}if(g.text===null){throw'You must supply "text" parameter.'}if(!this._is_setup){this._runSetup()}var k=g.title,n=g.text,e=g.image||"",l=g.sticky||false,m=g.class_name||b.gritter.options.class_name,j=b.gritter.options.position,d=g.time||"";this._verifyWrapper();this._item_count++;var f=this._item_count,i=this._tpl_item;b(["before_open","after_open","before_close","after_close"]).each(function(p,q){a["_"+q+"_"+f]=(b.isFunction(g[q]))?g[q]:function(){}});this._custom_timer=0;if(d){this._custom_timer=d}var c=(e!="")?'<img src="'+e+'" class="gritter-image" />':"",h=(e!="")?"gritter-with-image":"gritter-without-image";if(k){k=this._str_replace("[[title]]",k,this._tpl_title)}else{k=""}i=this._str_replace(["[[title]]","[[text]]","[[close]]","[[image]]","[[number]]","[[class_name]]","[[item_class]]"],[k,n,this._tpl_close,c,this._item_count,h,m],i);if(this["_before_open_"+f]()===false){return false}b("#gritter-notice-wrapper").addClass(j).append(i);var o=b("#gritter-item-"+this._item_count);o.fadeIn(this.fade_in_speed,function(){a["_after_open_"+f](b(this))});if(!l){this._setFadeTimer(o,f)}b(o).bind("mouseenter mouseleave",function(p){if(p.type=="mouseenter"){if(!l){a._restoreItemIfFading(b(this),f)}}else{if(!l){a._setFadeTimer(b(this),f)}}a._hoverState(b(this),p.type)});b(o).find(".gritter-close").click(function(){a.removeSpecific(f,{},null,true);return false;});return f},_countRemoveWrapper:function(c,d,f){d.remove();this["_after_close_"+c](d,f);if(b(".gritter-item-wrapper").length==0){b("#gritter-notice-wrapper").remove()}},_fade:function(g,d,j,f){var j=j||{},i=(typeof(j.fade)!="undefined")?j.fade:true,c=j.speed||this.fade_out_speed,h=f;this["_before_close_"+d](g,h);if(f){g.unbind("mouseenter mouseleave")}if(i){g.animate({opacity:0},c,function(){g.animate({height:0},300,function(){a._countRemoveWrapper(d,g,h)})})}else{this._countRemoveWrapper(d,g)}},_hoverState:function(d,c){if(c=="mouseenter"){d.addClass("hover");d.find(".gritter-close").show()}else{d.removeClass("hover");d.find(".gritter-close").hide()}},removeSpecific:function(c,g,f,d){if(!f){var f=b("#gritter-item-"+c)}this._fade(f,c,g||{},d)},_restoreItemIfFading:function(d,c){clearTimeout(this["_int_id_"+c]);d.stop().css({opacity:"",height:""})},_runSetup:function(){for(opt in b.gritter.options){this[opt]=b.gritter.options[opt]}this._is_setup=1},_setFadeTimer:function(f,d){var c=(this._custom_timer)?this._custom_timer:this.time;this["_int_id_"+d]=setTimeout(function(){a._fade(f,d)},c)},stop:function(e){var c=(b.isFunction(e.before_close))?e.before_close:function(){};var f=(b.isFunction(e.after_close))?e.after_close:function(){};var d=b("#gritter-notice-wrapper");c(d);d.fadeOut(function(){b(this).remove();f()})},_str_replace:function(v,e,o,n){var k=0,h=0,t="",m="",g=0,q=0,l=[].concat(v),c=[].concat(e),u=o,d=c instanceof Array,p=u instanceof Array;u=[].concat(u);if(n){this.window[n]=0}for(k=0,g=u.length;k<g;k++){if(u[k]===""){continue}for(h=0,q=l.length;h<q;h++){t=u[k]+"";m=d?(c[h]!==undefined?c[h]:""):c[0];u[k]=(t).split(l[h]).join(m);if(n&&u[k]!==t){this.window[n]+=(t.length-u[k].length)/l[h].length}}}return p?u:u[0]},_verifyWrapper:function(){if(b("#gritter-notice-wrapper").length==0){b("body").append(this._tpl_wrap)}}}})(jQuery);


/**
 * timepicker
 */
!function(e){"function"==typeof define&&define.amd?define(["jquery","jquery-ui"],e):e(jQuery)}(function($){if($.ui.timepicker=$.ui.timepicker||{},!$.ui.timepicker.version){$.extend($.ui,{timepicker:{version:"1.6.1"}});var Timepicker=function(){this.regional=[],this.regional[""]={currentText:"Now",closeText:"Done",amNames:["AM","A"],pmNames:["PM","P"],timeFormat:"HH:mm",timeSuffix:"",timeOnlyTitle:"Choose Time",timeText:"Time",hourText:"Hour",minuteText:"Minute",secondText:"Second",millisecText:"Millisecond",microsecText:"Microsecond",timezoneText:"Time Zone",isRTL:!1},this._defaults={showButtonPanel:!0,timeOnly:!1,timeOnlyShowDate:!1,showHour:null,showMinute:null,showSecond:null,showMillisec:null,showMicrosec:null,showTimezone:null,showTime:!0,stepHour:1,stepMinute:1,stepSecond:1,stepMillisec:1,stepMicrosec:1,hour:0,minute:0,second:0,millisec:0,microsec:0,timezone:null,hourMin:0,minuteMin:0,secondMin:0,millisecMin:0,microsecMin:0,hourMax:23,minuteMax:59,secondMax:59,millisecMax:999,microsecMax:999,minDateTime:null,maxDateTime:null,maxTime:null,minTime:null,onSelect:null,hourGrid:0,minuteGrid:0,secondGrid:0,millisecGrid:0,microsecGrid:0,alwaysSetTime:!0,separator:" ",altFieldTimeOnly:!0,altTimeFormat:null,altSeparator:null,altTimeSuffix:null,altRedirectFocus:!0,pickerTimeFormat:null,pickerTimeSuffix:null,showTimepicker:!0,timezoneList:null,addSliderAccess:!1,sliderAccessArgs:null,controlType:"slider",oneLine:!1,defaultValue:null,parse:"strict",afterInject:null},$.extend(this._defaults,this.regional[""])};$.extend(Timepicker.prototype,{$input:null,$altInput:null,$timeObj:null,inst:null,hour_slider:null,minute_slider:null,second_slider:null,millisec_slider:null,microsec_slider:null,timezone_select:null,maxTime:null,minTime:null,hour:0,minute:0,second:0,millisec:0,microsec:0,timezone:null,hourMinOriginal:null,minuteMinOriginal:null,secondMinOriginal:null,millisecMinOriginal:null,microsecMinOriginal:null,hourMaxOriginal:null,minuteMaxOriginal:null,secondMaxOriginal:null,millisecMaxOriginal:null,microsecMaxOriginal:null,ampm:"",formattedDate:"",formattedTime:"",formattedDateTime:"",timezoneList:null,units:["hour","minute","second","millisec","microsec"],support:{},control:null,setDefaults:function(e){return extendRemove(this._defaults,e||{}),this},_newInst:function($input,opts){var tp_inst=new Timepicker,inlineSettings={},fns={},overrides,i;for(var attrName in this._defaults)if(this._defaults.hasOwnProperty(attrName)){var attrValue=$input.attr("time:"+attrName);if(attrValue)try{inlineSettings[attrName]=eval(attrValue)}catch(err){inlineSettings[attrName]=attrValue}}overrides={beforeShow:function(e,t){return $.isFunction(tp_inst._defaults.evnts.beforeShow)?tp_inst._defaults.evnts.beforeShow.call($input[0],e,t,tp_inst):void 0},onChangeMonthYear:function(e,t,i){$.isFunction(tp_inst._defaults.evnts.onChangeMonthYear)&&tp_inst._defaults.evnts.onChangeMonthYear.call($input[0],e,t,i,tp_inst)},onClose:function(e,t){tp_inst.timeDefined===!0&&""!==$input.val()&&tp_inst._updateDateTime(t),$.isFunction(tp_inst._defaults.evnts.onClose)&&tp_inst._defaults.evnts.onClose.call($input[0],e,t,tp_inst)}};for(i in overrides)overrides.hasOwnProperty(i)&&(fns[i]=opts[i]||this._defaults[i]||null);tp_inst._defaults=$.extend({},this._defaults,inlineSettings,opts,overrides,{evnts:fns,timepicker:tp_inst}),tp_inst.amNames=$.map(tp_inst._defaults.amNames,function(e){return e.toUpperCase()}),tp_inst.pmNames=$.map(tp_inst._defaults.pmNames,function(e){return e.toUpperCase()}),tp_inst.support=detectSupport(tp_inst._defaults.timeFormat+(tp_inst._defaults.pickerTimeFormat?tp_inst._defaults.pickerTimeFormat:"")+(tp_inst._defaults.altTimeFormat?tp_inst._defaults.altTimeFormat:"")),"string"==typeof tp_inst._defaults.controlType?("slider"===tp_inst._defaults.controlType&&"undefined"==typeof $.ui.slider&&(tp_inst._defaults.controlType="select"),tp_inst.control=tp_inst._controls[tp_inst._defaults.controlType]):tp_inst.control=tp_inst._defaults.controlType;var timezoneList=[-720,-660,-600,-570,-540,-480,-420,-360,-300,-270,-240,-210,-180,-120,-60,0,60,120,180,210,240,270,300,330,345,360,390,420,480,525,540,570,600,630,660,690,720,765,780,840];null!==tp_inst._defaults.timezoneList&&(timezoneList=tp_inst._defaults.timezoneList);var tzl=timezoneList.length,tzi=0,tzv=null;if(tzl>0&&"object"!=typeof timezoneList[0])for(;tzl>tzi;tzi++)tzv=timezoneList[tzi],timezoneList[tzi]={value:tzv,label:$.timepicker.timezoneOffsetString(tzv,tp_inst.support.iso8601)};return tp_inst._defaults.timezoneList=timezoneList,tp_inst.timezone=null!==tp_inst._defaults.timezone?$.timepicker.timezoneOffsetNumber(tp_inst._defaults.timezone):-1*(new Date).getTimezoneOffset(),tp_inst.hour=tp_inst._defaults.hour<tp_inst._defaults.hourMin?tp_inst._defaults.hourMin:tp_inst._defaults.hour>tp_inst._defaults.hourMax?tp_inst._defaults.hourMax:tp_inst._defaults.hour,tp_inst.minute=tp_inst._defaults.minute<tp_inst._defaults.minuteMin?tp_inst._defaults.minuteMin:tp_inst._defaults.minute>tp_inst._defaults.minuteMax?tp_inst._defaults.minuteMax:tp_inst._defaults.minute,tp_inst.second=tp_inst._defaults.second<tp_inst._defaults.secondMin?tp_inst._defaults.secondMin:tp_inst._defaults.second>tp_inst._defaults.secondMax?tp_inst._defaults.secondMax:tp_inst._defaults.second,tp_inst.millisec=tp_inst._defaults.millisec<tp_inst._defaults.millisecMin?tp_inst._defaults.millisecMin:tp_inst._defaults.millisec>tp_inst._defaults.millisecMax?tp_inst._defaults.millisecMax:tp_inst._defaults.millisec,tp_inst.microsec=tp_inst._defaults.microsec<tp_inst._defaults.microsecMin?tp_inst._defaults.microsecMin:tp_inst._defaults.microsec>tp_inst._defaults.microsecMax?tp_inst._defaults.microsecMax:tp_inst._defaults.microsec,tp_inst.ampm="",tp_inst.$input=$input,tp_inst._defaults.altField&&(tp_inst.$altInput=$(tp_inst._defaults.altField),tp_inst._defaults.altRedirectFocus===!0&&tp_inst.$altInput.css({cursor:"pointer"}).focus(function(){$input.trigger("focus")})),(0===tp_inst._defaults.minDate||0===tp_inst._defaults.minDateTime)&&(tp_inst._defaults.minDate=new Date),(0===tp_inst._defaults.maxDate||0===tp_inst._defaults.maxDateTime)&&(tp_inst._defaults.maxDate=new Date),void 0!==tp_inst._defaults.minDate&&tp_inst._defaults.minDate instanceof Date&&(tp_inst._defaults.minDateTime=new Date(tp_inst._defaults.minDate.getTime())),void 0!==tp_inst._defaults.minDateTime&&tp_inst._defaults.minDateTime instanceof Date&&(tp_inst._defaults.minDate=new Date(tp_inst._defaults.minDateTime.getTime())),void 0!==tp_inst._defaults.maxDate&&tp_inst._defaults.maxDate instanceof Date&&(tp_inst._defaults.maxDateTime=new Date(tp_inst._defaults.maxDate.getTime())),void 0!==tp_inst._defaults.maxDateTime&&tp_inst._defaults.maxDateTime instanceof Date&&(tp_inst._defaults.maxDate=new Date(tp_inst._defaults.maxDateTime.getTime())),tp_inst.$input.bind("focus",function(){tp_inst._onFocus()}),tp_inst},_addTimePicker:function(e){var t=$.trim(this.$altInput&&this._defaults.altFieldTimeOnly?this.$input.val()+" "+this.$altInput.val():this.$input.val());this.timeDefined=this._parseTime(t),this._limitMinMaxDateTime(e,!1),this._injectTimePicker(),this._afterInject()},_parseTime:function(e,t){if(this.inst||(this.inst=$.datepicker._getInst(this.$input[0])),t||!this._defaults.timeOnly){var i=$.datepicker._get(this.inst,"dateFormat");try{var s=parseDateTimeInternal(i,this._defaults.timeFormat,e,$.datepicker._getFormatConfig(this.inst),this._defaults);if(!s.timeObj)return!1;$.extend(this,s.timeObj)}catch(n){return $.timepicker.log("Error parsing the date/time string: "+n+"\ndate/time string = "+e+"\ntimeFormat = "+this._defaults.timeFormat+"\ndateFormat = "+i),!1}return!0}var a=$.datepicker.parseTime(this._defaults.timeFormat,e,this._defaults);return a?($.extend(this,a),!0):!1},_afterInject:function(){var e=this.inst.settings;$.isFunction(e.afterInject)&&e.afterInject.call(this)},_injectTimePicker:function(){var e=this.inst.dpDiv,t=this.inst.settings,i=this,s="",n="",a=null,r={},l={},o=null,u=0,c=0;if(0===e.find("div.ui-timepicker-div").length&&t.showTimepicker){var m=" ui_tpicker_unit_hide",d='<div class="ui-timepicker-div'+(t.isRTL?" ui-timepicker-rtl":"")+(t.oneLine&&"select"===t.controlType?" ui-timepicker-oneLine":"")+'"><dl><dt class="ui_tpicker_time_label'+(t.showTime?"":m)+'">'+t.timeText+'</dt><dd class="ui_tpicker_time '+(t.showTime?"":m)+'"><input class="ui_tpicker_time_input" '+(t.timeInput?"":"disabled")+"/></dd>";for(u=0,c=this.units.length;c>u;u++){if(s=this.units[u],n=s.substr(0,1).toUpperCase()+s.substr(1),a=null!==t["show"+n]?t["show"+n]:this.support[s],r[s]=parseInt(t[s+"Max"]-(t[s+"Max"]-t[s+"Min"])%t["step"+n],10),l[s]=0,d+='<dt class="ui_tpicker_'+s+"_label"+(a?"":m)+'">'+t[s+"Text"]+'</dt><dd class="ui_tpicker_'+s+(a?"":m)+'"><div class="ui_tpicker_'+s+"_slider"+(a?"":m)+'"></div>',a&&t[s+"Grid"]>0){if(d+='<div style="padding-left: 1px"><table class="ui-tpicker-grid-label"><tr>',"hour"===s)for(var h=t[s+"Min"];h<=r[s];h+=parseInt(t[s+"Grid"],10)){l[s]++;var p=$.datepicker.formatTime(this.support.ampm?"hht":"HH",{hour:h},t);d+='<td data-for="'+s+'">'+p+"</td>"}else for(var f=t[s+"Min"];f<=r[s];f+=parseInt(t[s+"Grid"],10))l[s]++,d+='<td data-for="'+s+'">'+(10>f?"0":"")+f+"</td>";d+="</tr></table></div>"}d+="</dd>"}var _=null!==t.showTimezone?t.showTimezone:this.support.timezone;d+='<dt class="ui_tpicker_timezone_label'+(_?"":m)+'">'+t.timezoneText+"</dt>",d+='<dd class="ui_tpicker_timezone'+(_?"":m)+'"></dd>',d+="</dl></div>";var g=$(d);for(t.timeOnly===!0&&(g.prepend('<div class="ui-widget-header ui-helper-clearfix ui-corner-all"><div class="ui-datepicker-title">'+t.timeOnlyTitle+"</div></div>"),e.find(".ui-datepicker-header, .ui-datepicker-calendar").hide()),u=0,c=i.units.length;c>u;u++)s=i.units[u],n=s.substr(0,1).toUpperCase()+s.substr(1),a=null!==t["show"+n]?t["show"+n]:this.support[s],i[s+"_slider"]=i.control.create(i,g.find(".ui_tpicker_"+s+"_slider"),s,i[s],t[s+"Min"],r[s],t["step"+n]),a&&t[s+"Grid"]>0&&(o=100*l[s]*t[s+"Grid"]/(r[s]-t[s+"Min"]),g.find(".ui_tpicker_"+s+" table").css({width:o+"%",marginLeft:t.isRTL?"0":o/(-2*l[s])+"%",marginRight:t.isRTL?o/(-2*l[s])+"%":"0",borderCollapse:"collapse"}).find("td").click(function(e){var t=$(this),n=t.html(),a=parseInt(n.replace(/[^0-9]/g),10),r=n.replace(/[^apm]/gi),l=t.data("for");"hour"===l&&(-1!==r.indexOf("p")&&12>a?a+=12:-1!==r.indexOf("a")&&12===a&&(a=0)),i.control.value(i,i[l+"_slider"],s,a),i._onTimeChange(),i._onSelectHandler()}).css({cursor:"pointer",width:100/l[s]+"%",textAlign:"center",overflow:"hidden"}));if(this.timezone_select=g.find(".ui_tpicker_timezone").append("<select></select>").find("select"),$.fn.append.apply(this.timezone_select,$.map(t.timezoneList,function(e,t){return $("<option />").val("object"==typeof e?e.value:e).text("object"==typeof e?e.label:e)})),"undefined"!=typeof this.timezone&&null!==this.timezone&&""!==this.timezone){var M=-1*new Date(this.inst.selectedYear,this.inst.selectedMonth,this.inst.selectedDay,12).getTimezoneOffset();M===this.timezone?selectLocalTimezone(i):this.timezone_select.val(this.timezone)}else"undefined"!=typeof this.hour&&null!==this.hour&&""!==this.hour?this.timezone_select.val(t.timezone):selectLocalTimezone(i);this.timezone_select.change(function(){i._onTimeChange(),i._onSelectHandler(),i._afterInject()});var v=e.find(".ui-datepicker-buttonpane");if(v.length?v.before(g):e.append(g),this.$timeObj=g.find(".ui_tpicker_time_input"),this.$timeObj.change(function(){var e=i.inst.settings.timeFormat,t=$.datepicker.parseTime(e,this.value),s=new Date;t?(s.setHours(t.hour),s.setMinutes(t.minute),s.setSeconds(t.second),$.datepicker._setTime(i.inst,s)):(this.value=i.formattedTime,this.blur())}),null!==this.inst){var k=this.timeDefined;this._onTimeChange(),this.timeDefined=k}if(this._defaults.addSliderAccess){var T=this._defaults.sliderAccessArgs,D=this._defaults.isRTL;T.isRTL=D,setTimeout(function(){if(0===g.find(".ui-slider-access").length){g.find(".ui-slider:visible").sliderAccess(T);var e=g.find(".ui-slider-access:eq(0)").outerWidth(!0);e&&g.find("table:visible").each(function(){var t=$(this),i=t.outerWidth(),s=t.css(D?"marginRight":"marginLeft").toString().replace("%",""),n=i-e,a=s*n/i+"%",r={width:n,marginRight:0,marginLeft:0};r[D?"marginRight":"marginLeft"]=a,t.css(r)})}},10)}i._limitMinMaxDateTime(this.inst,!0)}},_limitMinMaxDateTime:function(e,t){var i=this._defaults,s=new Date(e.selectedYear,e.selectedMonth,e.selectedDay);if(this._defaults.showTimepicker){if(null!==$.datepicker._get(e,"minDateTime")&&void 0!==$.datepicker._get(e,"minDateTime")&&s){var n=$.datepicker._get(e,"minDateTime"),a=new Date(n.getFullYear(),n.getMonth(),n.getDate(),0,0,0,0);(null===this.hourMinOriginal||null===this.minuteMinOriginal||null===this.secondMinOriginal||null===this.millisecMinOriginal||null===this.microsecMinOriginal)&&(this.hourMinOriginal=i.hourMin,this.minuteMinOriginal=i.minuteMin,this.secondMinOriginal=i.secondMin,this.millisecMinOriginal=i.millisecMin,this.microsecMinOriginal=i.microsecMin),e.settings.timeOnly||a.getTime()===s.getTime()?(this._defaults.hourMin=n.getHours(),this.hour<=this._defaults.hourMin?(this.hour=this._defaults.hourMin,this._defaults.minuteMin=n.getMinutes(),this.minute<=this._defaults.minuteMin?(this.minute=this._defaults.minuteMin,this._defaults.secondMin=n.getSeconds(),this.second<=this._defaults.secondMin?(this.second=this._defaults.secondMin,this._defaults.millisecMin=n.getMilliseconds(),this.millisec<=this._defaults.millisecMin?(this.millisec=this._defaults.millisecMin,this._defaults.microsecMin=n.getMicroseconds()):(this.microsec<this._defaults.microsecMin&&(this.microsec=this._defaults.microsecMin),this._defaults.microsecMin=this.microsecMinOriginal)):(this._defaults.millisecMin=this.millisecMinOriginal,this._defaults.microsecMin=this.microsecMinOriginal)):(this._defaults.secondMin=this.secondMinOriginal,this._defaults.millisecMin=this.millisecMinOriginal,this._defaults.microsecMin=this.microsecMinOriginal)):(this._defaults.minuteMin=this.minuteMinOriginal,this._defaults.secondMin=this.secondMinOriginal,this._defaults.millisecMin=this.millisecMinOriginal,this._defaults.microsecMin=this.microsecMinOriginal)):(this._defaults.hourMin=this.hourMinOriginal,this._defaults.minuteMin=this.minuteMinOriginal,this._defaults.secondMin=this.secondMinOriginal,this._defaults.millisecMin=this.millisecMinOriginal,this._defaults.microsecMin=this.microsecMinOriginal)}if(null!==$.datepicker._get(e,"maxDateTime")&&void 0!==$.datepicker._get(e,"maxDateTime")&&s){var r=$.datepicker._get(e,"maxDateTime"),l=new Date(r.getFullYear(),r.getMonth(),r.getDate(),0,0,0,0);(null===this.hourMaxOriginal||null===this.minuteMaxOriginal||null===this.secondMaxOriginal||null===this.millisecMaxOriginal)&&(this.hourMaxOriginal=i.hourMax,this.minuteMaxOriginal=i.minuteMax,this.secondMaxOriginal=i.secondMax,this.millisecMaxOriginal=i.millisecMax,this.microsecMaxOriginal=i.microsecMax),e.settings.timeOnly||l.getTime()===s.getTime()?(this._defaults.hourMax=r.getHours(),this.hour>=this._defaults.hourMax?(this.hour=this._defaults.hourMax,this._defaults.minuteMax=r.getMinutes(),this.minute>=this._defaults.minuteMax?(this.minute=this._defaults.minuteMax,this._defaults.secondMax=r.getSeconds(),this.second>=this._defaults.secondMax?(this.second=this._defaults.secondMax,this._defaults.millisecMax=r.getMilliseconds(),this.millisec>=this._defaults.millisecMax?(this.millisec=this._defaults.millisecMax,this._defaults.microsecMax=r.getMicroseconds()):(this.microsec>this._defaults.microsecMax&&(this.microsec=this._defaults.microsecMax),this._defaults.microsecMax=this.microsecMaxOriginal)):(this._defaults.millisecMax=this.millisecMaxOriginal,this._defaults.microsecMax=this.microsecMaxOriginal)):(this._defaults.secondMax=this.secondMaxOriginal,this._defaults.millisecMax=this.millisecMaxOriginal,this._defaults.microsecMax=this.microsecMaxOriginal)):(this._defaults.minuteMax=this.minuteMaxOriginal,this._defaults.secondMax=this.secondMaxOriginal,this._defaults.millisecMax=this.millisecMaxOriginal,this._defaults.microsecMax=this.microsecMaxOriginal)):(this._defaults.hourMax=this.hourMaxOriginal,this._defaults.minuteMax=this.minuteMaxOriginal,this._defaults.secondMax=this.secondMaxOriginal,this._defaults.millisecMax=this.millisecMaxOriginal,this._defaults.microsecMax=this.microsecMaxOriginal)}if(null!==e.settings.minTime){var o=new Date("01/01/1970 "+e.settings.minTime);this.hour<o.getHours()?(this.hour=this._defaults.hourMin=o.getHours(),this.minute=this._defaults.minuteMin=o.getMinutes()):this.hour===o.getHours()&&this.minute<o.getMinutes()?this.minute=this._defaults.minuteMin=o.getMinutes():this._defaults.hourMin<o.getHours()?(this._defaults.hourMin=o.getHours(),this._defaults.minuteMin=o.getMinutes()):this._defaults.hourMin===o.getHours()===this.hour&&this._defaults.minuteMin<o.getMinutes()?this._defaults.minuteMin=o.getMinutes():this._defaults.minuteMin=0}if(null!==e.settings.maxTime){var u=new Date("01/01/1970 "+e.settings.maxTime);this.hour>u.getHours()?(this.hour=this._defaults.hourMax=u.getHours(),this.minute=this._defaults.minuteMax=u.getMinutes()):this.hour===u.getHours()&&this.minute>u.getMinutes()?this.minute=this._defaults.minuteMax=u.getMinutes():this._defaults.hourMax>u.getHours()?(this._defaults.hourMax=u.getHours(),this._defaults.minuteMax=u.getMinutes()):this._defaults.hourMax===u.getHours()===this.hour&&this._defaults.minuteMax>u.getMinutes()?this._defaults.minuteMax=u.getMinutes():this._defaults.minuteMax=59}if(void 0!==t&&t===!0){var c=parseInt(this._defaults.hourMax-(this._defaults.hourMax-this._defaults.hourMin)%this._defaults.stepHour,10),m=parseInt(this._defaults.minuteMax-(this._defaults.minuteMax-this._defaults.minuteMin)%this._defaults.stepMinute,10),d=parseInt(this._defaults.secondMax-(this._defaults.secondMax-this._defaults.secondMin)%this._defaults.stepSecond,10),h=parseInt(this._defaults.millisecMax-(this._defaults.millisecMax-this._defaults.millisecMin)%this._defaults.stepMillisec,10),p=parseInt(this._defaults.microsecMax-(this._defaults.microsecMax-this._defaults.microsecMin)%this._defaults.stepMicrosec,10);this.hour_slider&&(this.control.options(this,this.hour_slider,"hour",{min:this._defaults.hourMin,max:c,step:this._defaults.stepHour}),this.control.value(this,this.hour_slider,"hour",this.hour-this.hour%this._defaults.stepHour)),this.minute_slider&&(this.control.options(this,this.minute_slider,"minute",{min:this._defaults.minuteMin,max:m,step:this._defaults.stepMinute}),this.control.value(this,this.minute_slider,"minute",this.minute-this.minute%this._defaults.stepMinute)),this.second_slider&&(this.control.options(this,this.second_slider,"second",{min:this._defaults.secondMin,max:d,step:this._defaults.stepSecond}),this.control.value(this,this.second_slider,"second",this.second-this.second%this._defaults.stepSecond)),this.millisec_slider&&(this.control.options(this,this.millisec_slider,"millisec",{min:this._defaults.millisecMin,max:h,step:this._defaults.stepMillisec}),this.control.value(this,this.millisec_slider,"millisec",this.millisec-this.millisec%this._defaults.stepMillisec)),this.microsec_slider&&(this.control.options(this,this.microsec_slider,"microsec",{min:this._defaults.microsecMin,max:p,step:this._defaults.stepMicrosec}),this.control.value(this,this.microsec_slider,"microsec",this.microsec-this.microsec%this._defaults.stepMicrosec))}}},_onTimeChange:function(){if(this._defaults.showTimepicker){var e=this.hour_slider?this.control.value(this,this.hour_slider,"hour"):!1,t=this.minute_slider?this.control.value(this,this.minute_slider,"minute"):!1,i=this.second_slider?this.control.value(this,this.second_slider,"second"):!1,s=this.millisec_slider?this.control.value(this,this.millisec_slider,"millisec"):!1,n=this.microsec_slider?this.control.value(this,this.microsec_slider,"microsec"):!1,a=this.timezone_select?this.timezone_select.val():!1,r=this._defaults,l=r.pickerTimeFormat||r.timeFormat,o=r.pickerTimeSuffix||r.timeSuffix;"object"==typeof e&&(e=!1),"object"==typeof t&&(t=!1),"object"==typeof i&&(i=!1),"object"==typeof s&&(s=!1),"object"==typeof n&&(n=!1),"object"==typeof a&&(a=!1),e!==!1&&(e=parseInt(e,10)),t!==!1&&(t=parseInt(t,10)),i!==!1&&(i=parseInt(i,10)),s!==!1&&(s=parseInt(s,10)),n!==!1&&(n=parseInt(n,10)),a!==!1&&(a=a.toString());var u=r[12>e?"amNames":"pmNames"][0],c=e!==parseInt(this.hour,10)||t!==parseInt(this.minute,10)||i!==parseInt(this.second,10)||s!==parseInt(this.millisec,10)||n!==parseInt(this.microsec,10)||this.ampm.length>0&&12>e!=(-1!==$.inArray(this.ampm.toUpperCase(),this.amNames))||null!==this.timezone&&a!==this.timezone.toString();if(c&&(e!==!1&&(this.hour=e),t!==!1&&(this.minute=t),i!==!1&&(this.second=i),s!==!1&&(this.millisec=s),n!==!1&&(this.microsec=n),a!==!1&&(this.timezone=a),this.inst||(this.inst=$.datepicker._getInst(this.$input[0])),this._limitMinMaxDateTime(this.inst,!0)),this.support.ampm&&(this.ampm=u),this.formattedTime=$.datepicker.formatTime(r.timeFormat,this,r),this.$timeObj){var m=this.$timeObj[0].selectionStart,d=this.$timeObj[0].selectionEnd;l===r.timeFormat?this.$timeObj.val(this.formattedTime+o):this.$timeObj.val($.datepicker.formatTime(l,this,r)+o),this.$timeObj[0].setSelectionRange(m,d)}this.timeDefined=!0,c&&this._updateDateTime()}},_onSelectHandler:function(){var e=this._defaults.onSelect||this.inst.settings.onSelect,t=this.$input?this.$input[0]:null;e&&t&&e.apply(t,[this.formattedDateTime,this])},_updateDateTime:function(e){e=this.inst||e;var t=e.currentYear>0?new Date(e.currentYear,e.currentMonth,e.currentDay):new Date(e.selectedYear,e.selectedMonth,e.selectedDay),i=$.datepicker._daylightSavingAdjust(t),s=$.datepicker._get(e,"dateFormat"),n=$.datepicker._getFormatConfig(e),a=null!==i&&this.timeDefined;this.formattedDate=$.datepicker.formatDate(s,null===i?new Date:i,n);var r=this.formattedDate;if(""===e.lastVal&&(e.currentYear=e.selectedYear,e.currentMonth=e.selectedMonth,e.currentDay=e.selectedDay),this._defaults.timeOnly===!0&&this._defaults.timeOnlyShowDate===!1?r=this.formattedTime:(this._defaults.timeOnly!==!0&&(this._defaults.alwaysSetTime||a)||this._defaults.timeOnly===!0&&this._defaults.timeOnlyShowDate===!0)&&(r+=this._defaults.separator+this.formattedTime+this._defaults.timeSuffix),this.formattedDateTime=r,this._defaults.showTimepicker)if(this.$altInput&&this._defaults.timeOnly===!1&&this._defaults.altFieldTimeOnly===!0)this.$altInput.val(this.formattedTime),this.$input.val(this.formattedDate);else if(this.$altInput){this.$input.val(r);var l="",o=null!==this._defaults.altSeparator?this._defaults.altSeparator:this._defaults.separator,u=null!==this._defaults.altTimeSuffix?this._defaults.altTimeSuffix:this._defaults.timeSuffix;this._defaults.timeOnly||(l=this._defaults.altFormat?$.datepicker.formatDate(this._defaults.altFormat,null===i?new Date:i,n):this.formattedDate,l&&(l+=o)),l+=null!==this._defaults.altTimeFormat?$.datepicker.formatTime(this._defaults.altTimeFormat,this,this._defaults)+u:this.formattedTime+u,this.$altInput.val(l)}else this.$input.val(r);else this.$input.val(this.formattedDate);this.$input.trigger("change")},_onFocus:function(){if(!this.$input.val()&&this._defaults.defaultValue){this.$input.val(this._defaults.defaultValue);var e=$.datepicker._getInst(this.$input.get(0)),t=$.datepicker._get(e,"timepicker");if(t&&t._defaults.timeOnly&&e.input.val()!==e.lastVal)try{$.datepicker._updateDatepicker(e)}catch(i){$.timepicker.log(i)}}},_controls:{slider:{create:function(e,t,i,s,n,a,r){var l=e._defaults.isRTL;return t.prop("slide",null).slider({orientation:"horizontal",value:l?-1*s:s,min:l?-1*a:n,max:l?-1*n:a,step:r,slide:function(t,s){e.control.value(e,$(this),i,l?-1*s.value:s.value),e._onTimeChange()},stop:function(t,i){e._onSelectHandler()}})},options:function(e,t,i,s,n){if(e._defaults.isRTL){if("string"==typeof s)return"min"===s||"max"===s?void 0!==n?t.slider(s,-1*n):Math.abs(t.slider(s)):t.slider(s);var a=s.min,r=s.max;return s.min=s.max=null,void 0!==a&&(s.max=-1*a),void 0!==r&&(s.min=-1*r),t.slider(s)}return"string"==typeof s&&void 0!==n?t.slider(s,n):t.slider(s)},value:function(e,t,i,s){return e._defaults.isRTL?void 0!==s?t.slider("value",-1*s):Math.abs(t.slider("value")):void 0!==s?t.slider("value",s):t.slider("value")}},select:{create:function(e,t,i,s,n,a,r){for(var l='<select class="ui-timepicker-select ui-state-default ui-corner-all" data-unit="'+i+'" data-min="'+n+'" data-max="'+a+'" data-step="'+r+'">',o=e._defaults.pickerTimeFormat||e._defaults.timeFormat,u=n;a>=u;u+=r)l+='<option value="'+u+'"'+(u===s?" selected":"")+">",l+="hour"===i?$.datepicker.formatTime($.trim(o.replace(/[^ht ]/gi,"")),{hour:u},e._defaults):"millisec"===i||"microsec"===i||u>=10?u:"0"+u.toString(),l+="</option>";return l+="</select>",t.children("select").remove(),$(l).appendTo(t).change(function(t){e._onTimeChange(),e._onSelectHandler(),e._afterInject()}),t},options:function(e,t,i,s,n){var a={},r=t.children("select");if("string"==typeof s){if(void 0===n)return r.data(s);a[s]=n}else a=s;return e.control.create(e,t,r.data("unit"),r.val(),a.min>=0?a.min:r.data("min"),a.max||r.data("max"),a.step||r.data("step"))},value:function(e,t,i,s){var n=t.children("select");return void 0!==s?n.val(s):n.val()}}}}),$.fn.extend({timepicker:function(e){e=e||{};var t=Array.prototype.slice.call(arguments);return"object"==typeof e&&(t[0]=$.extend(e,{timeOnly:!0})),$(this).each(function(){$.fn.datetimepicker.apply($(this),t)})},datetimepicker:function(e){e=e||{};var t=arguments;return"string"==typeof e?"getDate"===e||"option"===e&&2===t.length&&"string"==typeof t[1]?$.fn.datepicker.apply($(this[0]),t):this.each(function(){var e=$(this);e.datepicker.apply(e,t)}):this.each(function(){var t=$(this);t.datepicker($.timepicker._newInst(t,e)._defaults)})}}),$.datepicker.parseDateTime=function(e,t,i,s,n){var a=parseDateTimeInternal(e,t,i,s,n);if(a.timeObj){var r=a.timeObj;a.date.setHours(r.hour,r.minute,r.second,r.millisec),a.date.setMicroseconds(r.microsec)}return a.date},$.datepicker.parseTime=function(e,t,i){var s=extendRemove(extendRemove({},$.timepicker._defaults),i||{}),n=(-1!==e.replace(/\'.*?\'/g,"").indexOf("Z"),function(e,t,i){var s,n=function(e,t){var i=[];return e&&$.merge(i,e),t&&$.merge(i,t),i=$.map(i,function(e){return e.replace(/[.*+?|()\[\]{}\\]/g,"\\$&")}),"("+i.join("|")+")?"},a=function(e){var t=e.toLowerCase().match(/(h{1,2}|m{1,2}|s{1,2}|l{1}|c{1}|t{1,2}|z|'.*?')/g),i={h:-1,m:-1,s:-1,l:-1,c:-1,t:-1,z:-1};if(t)for(var s=0;s<t.length;s++)-1===i[t[s].toString().charAt(0)]&&(i[t[s].toString().charAt(0)]=s+1);return i},r="^"+e.toString().replace(/([hH]{1,2}|mm?|ss?|[tT]{1,2}|[zZ]|[lc]|'.*?')/g,function(e){var t=e.length;switch(e.charAt(0).toLowerCase()){case"h":return 1===t?"(\\d?\\d)":"(\\d{"+t+"})";case"m":return 1===t?"(\\d?\\d)":"(\\d{"+t+"})";case"s":return 1===t?"(\\d?\\d)":"(\\d{"+t+"})";case"l":return"(\\d?\\d?\\d)";case"c":return"(\\d?\\d?\\d)";case"z":return"(z|[-+]\\d\\d:?\\d\\d|\\S+)?";case"t":return n(i.amNames,i.pmNames);default:return"("+e.replace(/\'/g,"").replace(/(\.|\$|\^|\\|\/|\(|\)|\[|\]|\?|\+|\*)/g,function(e){return"\\"+e})+")?"}}).replace(/\s/g,"\\s?")+i.timeSuffix+"$",l=a(e),o="";s=t.match(new RegExp(r,"i"));var u={hour:0,minute:0,second:0,millisec:0,microsec:0};return s?(-1!==l.t&&(void 0===s[l.t]||0===s[l.t].length?(o="",u.ampm=""):(o=-1!==$.inArray(s[l.t].toUpperCase(),$.map(i.amNames,function(e,t){return e.toUpperCase()}))?"AM":"PM",u.ampm=i["AM"===o?"amNames":"pmNames"][0])),-1!==l.h&&("AM"===o&&"12"===s[l.h]?u.hour=0:"PM"===o&&"12"!==s[l.h]?u.hour=parseInt(s[l.h],10)+12:u.hour=Number(s[l.h])),-1!==l.m&&(u.minute=Number(s[l.m])),-1!==l.s&&(u.second=Number(s[l.s])),-1!==l.l&&(u.millisec=Number(s[l.l])),-1!==l.c&&(u.microsec=Number(s[l.c])),-1!==l.z&&void 0!==s[l.z]&&(u.timezone=$.timepicker.timezoneOffsetNumber(s[l.z])),u):!1}),a=function(e,t,i){try{var s=new Date("2012-01-01 "+t);if(isNaN(s.getTime())&&(s=new Date("2012-01-01T"+t),isNaN(s.getTime())&&(s=new Date("01/01/2012 "+t),isNaN(s.getTime()))))throw"Unable to parse time with native Date: "+t;return{hour:s.getHours(),minute:s.getMinutes(),second:s.getSeconds(),millisec:s.getMilliseconds(),microsec:s.getMicroseconds(),timezone:-1*s.getTimezoneOffset()}}catch(a){try{return n(e,t,i)}catch(r){$.timepicker.log("Unable to parse \ntimeString: "+t+"\ntimeFormat: "+e)}}return!1};return"function"==typeof s.parse?s.parse(e,t,s):"loose"===s.parse?a(e,t,s):n(e,t,s)},$.datepicker.formatTime=function(e,t,i){i=i||{},i=$.extend({},$.timepicker._defaults,i),t=$.extend({hour:0,minute:0,second:0,millisec:0,microsec:0,timezone:null},t);var s=e,n=i.amNames[0],a=parseInt(t.hour,10);return a>11&&(n=i.pmNames[0]),s=s.replace(/(?:HH?|hh?|mm?|ss?|[tT]{1,2}|[zZ]|[lc]|'.*?')/g,function(e){switch(e){case"HH":return("0"+a).slice(-2);case"H":return a;case"hh":return("0"+convert24to12(a)).slice(-2);case"h":return convert24to12(a);case"mm":return("0"+t.minute).slice(-2);case"m":return t.minute;case"ss":return("0"+t.second).slice(-2);case"s":return t.second;case"l":return("00"+t.millisec).slice(-3);case"c":return("00"+t.microsec).slice(-3);case"z":return $.timepicker.timezoneOffsetString(null===t.timezone?i.timezone:t.timezone,!1);case"Z":return $.timepicker.timezoneOffsetString(null===t.timezone?i.timezone:t.timezone,!0);case"T":return n.charAt(0).toUpperCase();case"TT":return n.toUpperCase();case"t":return n.charAt(0).toLowerCase();case"tt":return n.toLowerCase();default:return e.replace(/'/g,"")}})},$.datepicker._base_selectDate=$.datepicker._selectDate,$.datepicker._selectDate=function(e,t){var i,s=this._getInst($(e)[0]),n=this._get(s,"timepicker");n&&s.settings.showTimepicker?(n._limitMinMaxDateTime(s,!0),i=s.inline,s.inline=s.stay_open=!0,this._base_selectDate(e,t),s.inline=i,s.stay_open=!1,this._notifyChange(s),this._updateDatepicker(s)):this._base_selectDate(e,t)},$.datepicker._base_updateDatepicker=$.datepicker._updateDatepicker,$.datepicker._updateDatepicker=function(e){var t=e.input[0];if(!($.datepicker._curInst&&$.datepicker._curInst!==e&&$.datepicker._datepickerShowing&&$.datepicker._lastInput!==t||"boolean"==typeof e.stay_open&&e.stay_open!==!1)){this._base_updateDatepicker(e);var i=this._get(e,"timepicker");i&&i._addTimePicker(e)}},$.datepicker._base_doKeyPress=$.datepicker._doKeyPress,$.datepicker._doKeyPress=function(e){var t=$.datepicker._getInst(e.target),i=$.datepicker._get(t,"timepicker");if(i&&$.datepicker._get(t,"constrainInput")){var s=i.support.ampm,n=null!==i._defaults.showTimezone?i._defaults.showTimezone:i.support.timezone,a=$.datepicker._possibleChars($.datepicker._get(t,"dateFormat")),r=i._defaults.timeFormat.toString().replace(/[hms]/g,"").replace(/TT/g,s?"APM":"").replace(/Tt/g,s?"AaPpMm":"").replace(/tT/g,s?"AaPpMm":"").replace(/T/g,s?"AP":"").replace(/tt/g,s?"apm":"").replace(/t/g,s?"ap":"")+" "+i._defaults.separator+i._defaults.timeSuffix+(n?i._defaults.timezoneList.join(""):"")+i._defaults.amNames.join("")+i._defaults.pmNames.join("")+a,l=String.fromCharCode(void 0===e.charCode?e.keyCode:e.charCode);return e.ctrlKey||" ">l||!a||r.indexOf(l)>-1}return $.datepicker._base_doKeyPress(e)},$.datepicker._base_updateAlternate=$.datepicker._updateAlternate,$.datepicker._updateAlternate=function(e){var t=this._get(e,"timepicker");if(t){var i=t._defaults.altField;if(i){var s=(t._defaults.altFormat||t._defaults.dateFormat,this._getDate(e)),n=$.datepicker._getFormatConfig(e),a="",r=t._defaults.altSeparator?t._defaults.altSeparator:t._defaults.separator,l=t._defaults.altTimeSuffix?t._defaults.altTimeSuffix:t._defaults.timeSuffix,o=null!==t._defaults.altTimeFormat?t._defaults.altTimeFormat:t._defaults.timeFormat;a+=$.datepicker.formatTime(o,t,t._defaults)+l,t._defaults.timeOnly||t._defaults.altFieldTimeOnly||null===s||(a=t._defaults.altFormat?$.datepicker.formatDate(t._defaults.altFormat,s,n)+r+a:t.formattedDate+r+a),$(i).val(e.input.val()?a:"")}}else $.datepicker._base_updateAlternate(e)},$.datepicker._base_doKeyUp=$.datepicker._doKeyUp,$.datepicker._doKeyUp=function(e){var t=$.datepicker._getInst(e.target),i=$.datepicker._get(t,"timepicker");
if(i&&i._defaults.timeOnly&&t.input.val()!==t.lastVal)try{$.datepicker._updateDatepicker(t)}catch(s){$.timepicker.log(s)}return $.datepicker._base_doKeyUp(e)},$.datepicker._base_gotoToday=$.datepicker._gotoToday,$.datepicker._gotoToday=function(e){var t=this._getInst($(e)[0]);this._base_gotoToday(e);var i=this._get(t,"timepicker"),s=$.timepicker.timezoneOffsetNumber(i.timezone),n=new Date;n.setMinutes(n.getMinutes()+n.getTimezoneOffset()+s),this._setTime(t,n),this._setDate(t,n),i._onSelectHandler()},$.datepicker._disableTimepickerDatepicker=function(e){var t=this._getInst(e);if(t){var i=this._get(t,"timepicker");$(e).datepicker("getDate"),i&&(t.settings.showTimepicker=!1,i._defaults.showTimepicker=!1,i._updateDateTime(t))}},$.datepicker._enableTimepickerDatepicker=function(e){var t=this._getInst(e);if(t){var i=this._get(t,"timepicker");$(e).datepicker("getDate"),i&&(t.settings.showTimepicker=!0,i._defaults.showTimepicker=!0,i._addTimePicker(t),i._updateDateTime(t))}},$.datepicker._setTime=function(e,t){var i=this._get(e,"timepicker");if(i){var s=i._defaults;i.hour=t?t.getHours():s.hour,i.minute=t?t.getMinutes():s.minute,i.second=t?t.getSeconds():s.second,i.millisec=t?t.getMilliseconds():s.millisec,i.microsec=t?t.getMicroseconds():s.microsec,i._limitMinMaxDateTime(e,!0),i._onTimeChange(),i._updateDateTime(e)}},$.datepicker._setTimeDatepicker=function(e,t,i){var s=this._getInst(e);if(s){var n=this._get(s,"timepicker");if(n){this._setDateFromField(s);var a;t&&("string"==typeof t?(n._parseTime(t,i),a=new Date,a.setHours(n.hour,n.minute,n.second,n.millisec),a.setMicroseconds(n.microsec)):(a=new Date(t.getTime()),a.setMicroseconds(t.getMicroseconds())),"Invalid Date"===a.toString()&&(a=void 0),this._setTime(s,a))}}},$.datepicker._base_setDateDatepicker=$.datepicker._setDateDatepicker,$.datepicker._setDateDatepicker=function(e,t){var i=this._getInst(e),s=t;if(i){"string"==typeof t&&(s=new Date(t),s.getTime()||(this._base_setDateDatepicker.apply(this,arguments),s=$(e).datepicker("getDate")));var n,a=this._get(i,"timepicker");s instanceof Date?(n=new Date(s.getTime()),n.setMicroseconds(s.getMicroseconds())):n=s,a&&n&&(a.support.timezone||null!==a._defaults.timezone||(a.timezone=-1*n.getTimezoneOffset()),s=$.timepicker.timezoneAdjust(s,a.timezone),n=$.timepicker.timezoneAdjust(n,a.timezone)),this._updateDatepicker(i),this._base_setDateDatepicker.apply(this,arguments),this._setTimeDatepicker(e,n,!0)}},$.datepicker._base_getDateDatepicker=$.datepicker._getDateDatepicker,$.datepicker._getDateDatepicker=function(e,t){var i=this._getInst(e);if(i){var s=this._get(i,"timepicker");if(s){void 0===i.lastVal&&this._setDateFromField(i,t);var n=this._getDate(i),a=$.trim(s.$altInput&&s._defaults.altFieldTimeOnly?s.$input.val()+" "+s.$altInput.val():s.$input.val());return n&&s._parseTime(a,!i.settings.timeOnly)&&(n.setHours(s.hour,s.minute,s.second,s.millisec),n.setMicroseconds(s.microsec),null!=s.timezone&&(s.support.timezone||null!==s._defaults.timezone||(s.timezone=-1*n.getTimezoneOffset()),n=$.timepicker.timezoneAdjust(n,s.timezone))),n}return this._base_getDateDatepicker(e,t)}},$.datepicker._base_parseDate=$.datepicker.parseDate,$.datepicker.parseDate=function(e,t,i){var s;try{s=this._base_parseDate(e,t,i)}catch(n){if(!(n.indexOf(":")>=0))throw n;s=this._base_parseDate(e,t.substring(0,t.length-(n.length-n.indexOf(":")-2)),i),$.timepicker.log("Error parsing the date string: "+n+"\ndate string = "+t+"\ndate format = "+e)}return s},$.datepicker._base_formatDate=$.datepicker._formatDate,$.datepicker._formatDate=function(e,t,i,s){var n=this._get(e,"timepicker");return n?(n._updateDateTime(e),n.$input.val()):this._base_formatDate(e)},$.datepicker._base_optionDatepicker=$.datepicker._optionDatepicker,$.datepicker._optionDatepicker=function(e,t,i){var s,n=this._getInst(e);if(!n)return null;var a=this._get(n,"timepicker");if(a){var r,l,o,u,c=null,m=null,d=null,h=a._defaults.evnts,p={};if("string"==typeof t){if("minDate"===t||"minDateTime"===t)c=i;else if("maxDate"===t||"maxDateTime"===t)m=i;else if("onSelect"===t)d=i;else if(h.hasOwnProperty(t)){if("undefined"==typeof i)return h[t];p[t]=i,s={}}}else if("object"==typeof t){t.minDate?c=t.minDate:t.minDateTime?c=t.minDateTime:t.maxDate?m=t.maxDate:t.maxDateTime&&(m=t.maxDateTime);for(r in h)h.hasOwnProperty(r)&&t[r]&&(p[r]=t[r])}for(r in p)p.hasOwnProperty(r)&&(h[r]=p[r],s||(s=$.extend({},t)),delete s[r]);if(s&&isEmptyObject(s))return;if(c?(c=0===c?new Date:new Date(c),a._defaults.minDate=c,a._defaults.minDateTime=c):m?(m=0===m?new Date:new Date(m),a._defaults.maxDate=m,a._defaults.maxDateTime=m):d&&(a._defaults.onSelect=d),c||m)return u=$(e),o=u.datetimepicker("getDate"),l=this._base_optionDatepicker.call($.datepicker,e,s||t,i),u.datetimepicker("setDate",o),l}return void 0===i?this._base_optionDatepicker.call($.datepicker,e,t):this._base_optionDatepicker.call($.datepicker,e,s||t,i)};var isEmptyObject=function(e){var t;for(t in e)if(e.hasOwnProperty(t))return!1;return!0},extendRemove=function(e,t){$.extend(e,t);for(var i in t)(null===t[i]||void 0===t[i])&&(e[i]=t[i]);return e},detectSupport=function(e){var t=e.replace(/'.*?'/g,"").toLowerCase(),i=function(e,t){return-1!==e.indexOf(t)?!0:!1};return{hour:i(t,"h"),minute:i(t,"m"),second:i(t,"s"),millisec:i(t,"l"),microsec:i(t,"c"),timezone:i(t,"z"),ampm:i(t,"t")&&i(e,"h"),iso8601:i(e,"Z")}},convert24to12=function(e){return e%=12,0===e&&(e=12),String(e)},computeEffectiveSetting=function(e,t){return e&&e[t]?e[t]:$.timepicker._defaults[t]},splitDateTime=function(e,t){var i=computeEffectiveSetting(t,"separator"),s=computeEffectiveSetting(t,"timeFormat"),n=s.split(i),a=n.length,r=e.split(i),l=r.length;return l>1?{dateString:r.splice(0,l-a).join(i),timeString:r.splice(0,a).join(i)}:{dateString:e,timeString:""}},parseDateTimeInternal=function(e,t,i,s,n){var a,r,l;if(r=splitDateTime(i,n),a=$.datepicker._base_parseDate(e,r.dateString,s),""===r.timeString)return{date:a};if(l=$.datepicker.parseTime(t,r.timeString,n),!l)throw"Wrong time format";return{date:a,timeObj:l}},selectLocalTimezone=function(e,t){if(e&&e.timezone_select){var i=t||new Date;e.timezone_select.val(-i.getTimezoneOffset())}};$.timepicker=new Timepicker,$.timepicker.timezoneOffsetString=function(e,t){if(isNaN(e)||e>840||-720>e)return e;var i=e,s=i%60,n=(i-s)/60,a=t?":":"",r=(i>=0?"+":"-")+("0"+Math.abs(n)).slice(-2)+a+("0"+Math.abs(s)).slice(-2);return"+00:00"===r?"Z":r},$.timepicker.timezoneOffsetNumber=function(e){var t=e.toString().replace(":","");return"Z"===t.toUpperCase()?0:/^(\-|\+)\d{4}$/.test(t)?("-"===t.substr(0,1)?-1:1)*(60*parseInt(t.substr(1,2),10)+parseInt(t.substr(3,2),10)):e},$.timepicker.timezoneAdjust=function(e,t){var i=$.timepicker.timezoneOffsetNumber(t);return isNaN(i)||e.setMinutes(e.getMinutes()+-e.getTimezoneOffset()-i),e},$.timepicker.timeRange=function(e,t,i){return $.timepicker.handleRange("timepicker",e,t,i)},$.timepicker.datetimeRange=function(e,t,i){$.timepicker.handleRange("datetimepicker",e,t,i)},$.timepicker.dateRange=function(e,t,i){$.timepicker.handleRange("datepicker",e,t,i)},$.timepicker.handleRange=function(e,t,i,s){function n(n,a){var r=t[e]("getDate"),l=i[e]("getDate"),o=n[e]("getDate");if(null!==r){var u=new Date(r.getTime()),c=new Date(r.getTime());u.setMilliseconds(u.getMilliseconds()+s.minInterval),c.setMilliseconds(c.getMilliseconds()+s.maxInterval),s.minInterval>0&&u>l?i[e]("setDate",u):s.maxInterval>0&&l>c?i[e]("setDate",c):r>l&&a[e]("setDate",o)}}function a(t,i,n){if(t.val()){var a=t[e].call(t,"getDate");null!==a&&s.minInterval>0&&("minDate"===n&&a.setMilliseconds(a.getMilliseconds()+s.minInterval),"maxDate"===n&&a.setMilliseconds(a.getMilliseconds()-s.minInterval)),a.getTime&&i[e].call(i,"option",n,a)}}s=$.extend({},{minInterval:0,maxInterval:0,start:{},end:{}},s);var r=!1;return"timepicker"===e&&(r=!0,e="datetimepicker"),$.fn[e].call(t,$.extend({timeOnly:r,onClose:function(e,t){n($(this),i)},onSelect:function(e){a($(this),i,"minDate")}},s,s.start)),$.fn[e].call(i,$.extend({timeOnly:r,onClose:function(e,i){n($(this),t)},onSelect:function(e){a($(this),t,"maxDate")}},s,s.end)),n(t,i),a(t,i,"minDate"),a(i,t,"maxDate"),$([t.get(0),i.get(0)])},$.timepicker.log=function(){window.console&&window.console.log.apply(window.console,Array.prototype.slice.call(arguments))},$.timepicker._util={_extendRemove:extendRemove,_isEmptyObject:isEmptyObject,_convert24to12:convert24to12,_detectSupport:detectSupport,_selectLocalTimezone:selectLocalTimezone,_computeEffectiveSetting:computeEffectiveSetting,_splitDateTime:splitDateTime,_parseDateTimeInternal:parseDateTimeInternal},Date.prototype.getMicroseconds||(Date.prototype.microseconds=0,Date.prototype.getMicroseconds=function(){return this.microseconds},Date.prototype.setMicroseconds=function(e){return this.setMilliseconds(this.getMilliseconds()+Math.floor(e/1e3)),this.microseconds=e%1e3,this}),$.timepicker.version="1.6.1"}});