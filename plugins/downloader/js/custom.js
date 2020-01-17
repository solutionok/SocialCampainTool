/**
 * @package Social Ninja
 * @version 1.3
 * @author InspiredDev
 * @copyright 2015
 */
 
var d_progress_timer = '';
$(document).on('click', '.sdownload', function(){
	var url = $('.d_url').val();
	if(url == '')return notify('error', lang.type_an_url);
	
	notify('wait', lang.fetching_f_info+'...');
	
	$.post(ajax_url, {
		'fetch_url': url,	
	}, function(response){
		$('.d_info, .d_links').html('');
		var data = $.parseJSON(response);
		if(data.error != '')return notify('error', data.error);
		else{
			notify('success', lang.finfo_got);
			if(data.is_video == 1){
				var info = data.video.info;
				var links = data.video.links;
				var hash = data.video.hash;
				
				var html = '';
				html += '<h3>'+info.title+'</h3>';
				html += '<img src="'+info.thumb+'" style="max-width:300px"/>';
				html += '<br/><br/><span>';
				if(info.desc != null)html += '<blockquote style="max-width:400px">'+info.desc+'</blockquote> ';
				if(info.duration != null)html += lang.duration+': <b>'+info.duration+'</b> ';
				if(info.views != null)html += lang.views+': <b>'+info.views+'</b> ';
				if(info.rating != null)html += lang.rating+': <b>'+info.rating+'</b> ';
				if(info.likes != null)html += lang.likes+': <b>'+info.likes+'</b> ';
				html += '</span>';
				
				$('.import-caption').val(info.title);
				$('.import-name').val(info.title);
				$('.d_info').append(html);
				
				html = '<h3>'+lang.dlinks+'</h3><table class="table" rel="'+hash+'">';
				html += '<tr><th>#</th><th>'+lang.type+'</th><th>'+lang.quality+'</th><th>'+lang.link_+'</th><th>'+lang.size+'</th><th>'+lang.dwn+'</th></tr>';
				for(i = 0; i < links.length; i++){
					html += '<tr rel="'+links[i].hash+'"><td>'+(i+1)+'</td><td>'+links[i].type+'</td><td>'+links[i].quality+'</td><td><a href="'+links[i].url+'" target="_blank">Link</a></td><td>'+links[i].size+'</td><td><button class="btn btn-xs btn-primary vdownload">'+lang.dwn+'</button></td></tr>';
						
				}
				html += '</table>';
				$('.d_links').append(html);	
			}	
			else{
				$('.d_info, .d_links').html('');
				$('.d_info').html('<h3>'+lang.img_info+'</h3><img src="'+data.image.thumb+'" style="max-width:500px"/><br/><br/>'+lang.name+': <b>'+data.image.name+'</b><br/>'+lang.size+': <b>'+data.image.size+'</b><br/><br/><div class="img_d_btn"><button class="btn btn-primary idownload">'+lang.dwn+'</button></div>');	
				$('.import-caption').val(data.image.name);
				$('.import-name').val(data.image.name);
			}
			$('.d_data').show();
		}
	});
});

$(document).on('click', '.vdownload', function(){
	var meta = $(this).parents('table:first').attr('rel');
	var file = $(this).parents('tr:first').attr('rel');
	
	$('#d_meta').val(meta);
	$('#d_file').val(file);
	
	$('.import-edited-modal').modal();
});

$(document).on('click', '.idownload', function(){
	var file = $('.d_info').find('img').attr('src');
	
	$('#d_meta').val('image');
	$('#d_file').val(file);
	
	$('.import-edited-modal').modal();
});

$(document).on('click', '.import-edited-btn', function(){
	var meta = $('#d_meta').val();
	var file = $('#d_file').val();
	
	var caption = $('.import-caption').val();
	var name = $('.import-name').val();
	var folder = $('.import-folder').val();
	
	if(folder == '')return notify('error', lang.sel_a_folder_f);
	if(folder == 'WATERMARK' || folder == 'FRAME')return notify('error', lang.sel_folder_cannot);
	$('.import-edited-modal').modal('hide');
	
	if(meta == 'image'){
		notify('wait', lang.please_wait+'...');
		
		$.post(ajax_url, {
			'd_caption': caption,
			'd_name': name,	
			'd_folder': folder,	
			'download_image': file,
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != ''){
				$('.img_d_btn').html('<div class="alert alert-danger">'+data.error+'</div>');
				return notify('error', data.error);
			}
			else{
				notify('success', lang.d_complete);
				$('.img_d_btn').html('<div class="alert alert-success">'+lang.f_imported+'</div>');
			}
		});	
	}
	else{
		$('.d_links').html('<h3>'+lang.dloading+'... <div class="pull-right d_progress">0%</div></h3><div class="progress progress-striped active"><div class="progress-bar" style="width: 0%"></div></div>');
		
		d_progress_timer = setTimeout(function(){download_progress(file, meta)}, 2000);
		
		$.post(ajax_url, {
			'd_caption': caption,
			'd_name': name,	
			'd_folder': folder,	
			'download_video': file,
			'download_video_meta': meta
		}, function(response){
			var data = $.parseJSON(response);
			if(data.error != '' && $('.d_links').find('.alert-danger').length <= 0){
				$('.d_links').html('<h3>'+lang.d_failed+'</h3><div class="alert alert-danger">'+data.error+'</div>');
				clearTimeout(d_progress_timer);
				return notify('error', data.error);
			}
			else{
				if($('.d_links').find('.alert-success').length <= 0){
					notify('success', lang.d_complete);
					$('.d_links').html('<h3>'+lang.d_complete+'</h3><div class="alert alert-success">'+lang.f_imported+'</div>');
					clearTimeout(d_progress_timer);
				}
			}
		});
	}
});

function download_progress(dfile, dmeta)
{
	$.post(ajax_url, {
		'download_file_progress': dfile,
		'download_file_meta_progress': dmeta
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != ''){
			clearTimeout(d_progress_timer);
			return notify('error', data.error);
		}
		else{
			if(data.isDone == 1 && $('.d_links').find('.alert-success').length <= 0){
				notify('success', lang.d_complete);
				$('.d_links').html('<h3>'+lang.d_complete+'</h3><div class="alert alert-success">'+lang.f_imported+'</div>');
				clearTimeout(d_progress_timer);	
			}
			else if(data.isDone == 2 && $('.d_links').find('.alert-danger').length <= 0){
				$('.d_links').html('<h3>'+lang.d_failed+'</h3><div class="alert alert-danger">'+data.errorMsg+'</div>');
				clearTimeout(d_progress_timer);
				notify('error', data.errorMsg);	
			}
			else{
				done = data.doneBytes;
				size = data.sizeBytes;
				p = (done/size)*100;
				p = parseInt(p);
				$('.d_progress').html(p+'%');
				$('.progress-bar').css('width', p+'%');
				d_progress_timer = setTimeout(function(){download_progress(dfile, dmeta)}, 2000);
			}
		}
	});
}