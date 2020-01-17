/**
 * @package Social Ninja
 * @version 1.2
 * @author InspiredDev
 * @copyright 2015
 */
 
var fonts = loadCanvasFonts();
$('.wm-font, .wm-font-2').html(fonts);

/**
 * Dropzone file uploader in editor
 */
if($('.dropzone-editor').length > 0){
	create_editor_uploader();
}
 

$(document).on('click', '.html-apply', function(){
	var h = $('#text').val();
	var canvas = document.getElementById("mycanvas");
	var context = canvas.getContext('2d');
	context.clearRect(0, 0, canvas.width, canvas.height);
	rasterizeHTML.drawHTML(h, canvas);
	$('#import-edited-type').val('image');
	$('#edited-img').val(1);
	$('.import-edited-img-int, .cresize').show();
});

$(document).on('click', '.cresize', function(){
	var newsize = prompt(lang.enter_new_size);
	if(newsize == '' || newsize == null)return;
	var n = newsize.split('x');
	n[0] = parseInt(n[0]);
	n[1] = parseInt(n[1]);
	var canvas = document.getElementById("mycanvas");	
	canvas.width = n[0];
	canvas.height = n[1];
	$('.html-apply').click();
})

$(document).on('click', '.import-edited-img-int', function(){
	var canvas = document.getElementById("mycanvas");
	canvas.toBlob(function(img){
		saveBlobImage(img, 1);
	});	
});

$(document).on('click', '.add-wm', function(){
	$('.save-wm, .cancel-wm, .wm-controls, .div-12').show();
	$('.apply-opt').hide();
	
	var file = $('.editor-before-img > img').attr('src');
	$('.editor-after-img').html('<img src="'+file+'" style="max-width:420px"/>');
});

$(document).on('click', '.add-wm-img', function(){
	$('.save-wm-img, .cancel-wm-img, .wm-img-controls, .div-12').show();
	$('.apply-opt').hide();
	
	var file = $('.editor-before-img > img').attr('src');
	$('.editor-after-img').html('<img src="'+file+'" style="max-width:420px"/>');
});

$(document).on('click', '.import-edited-img', function(){
	if($('#edited-img').val() == 0)return notify('error', lang.not_edited);
	$('#import-edited-type').val('image');
	$('.import-edited-modal').modal();
});

$(document).on('click', '.import-edited-btn', function(){
	if($('#edited-img').val() == 0)return notify('error', lang.not_edited);
	var folder = $('.import-folder').val();
	if(folder == '')return notify('error', lang.sel_folder);
	
	if($('#import-edited-type').val() == 'image')src = $('.editor-before-img > img').attr('src');
	else if($('#import-edited-type').val() == 'video')src = $('.editor-before-img > img').attr('src');
	else return notify('error', 'Invalid request');
	
	notify('wait', lang.saving_file+'...');
	
	$.post(ajax_url, {
		'importEdited': src,
		'caption': $('.import-caption').val(),
		'name': $('.import-name').val(),
		'folder': folder	
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != '')return notify('error', data.error);
		else{
			notify('success', lang.file_imported);
			$('.import-edited-modal').modal('hide');	
		}
	});
});

/**
 * Watermark X pos
 */
var x = function(boat, metrics, context) {
  return 0;
};

/**
 * Watermark Y pos
 */
var y = function(boat, metrics, context) {
  return 0;
};

/**
 * Watermark rotate
 */
var rotate = function(target) {
  var angle = parseInt($('.wm-rotate').val());
  var context = target.getContext('2d');
  var text = $('.wm-text').val();
  var metrics = context.measureText(text);
  var xa = Math.abs(angle) > 90 ? ( metrics.width + parseFloat(angle/10) ) : ( - metrics.width - parseFloat(angle/10) );
  var ya = Math.abs(angle) < 90 ? ( metrics.width + parseFloat((angle%90)*2) ) : ( - metrics.width - parseFloat((angle%90)*2) );;
  var x = (target.width / 2) + xa;
  var y = (target.height / 2) - ya;

  context.translate(x, y);
  context.globalAlpha = parseFloat($('.wm-opacity').val());
  context.fillStyle = $('.wm-color').val();
  context.font = parseInt($('.wm-font-size').val())+'px '+$('.wm-font').val();
  context.rotate(angle * Math.PI / 180);
  context.fillText(text, 0, 0);
  return target;
};

$(document).on('click', '.wm-apply', function(){
	var text2 = '';
	var text = $('.wm-text').val();
	var pos = $('.wm-pos').val();
	var color = $('.wm-color').val();
	var font = $('.wm-font').val();
	var opacity = parseFloat($('.wm-opacity').val());
	var fsize = parseInt($('.wm-font-size').val());
	var angle = parseInt($('.wm-rotate').val());
	
	if(isNaN(opacity) || isNaN(fsize) || isNaN(angle) || text == ''){
		return notify('error', lang.inv_input);	
	}
	if(fsize > 200 || fsize < 1){
		return notify('error', lang.font_size_must);	
	}
	if(angle < -180 || angle > 180){
		return notify('error', lang.angle_must);	
	}
	if(opacity > 1 || opacity < 0){
		return notify('error', lang.opa_must);	
	}
	if(angle != 0 && pos != 'center'){
		if(!confirm_action(lang.ang_center, $(this)))return false;
	}
	
	
	if($('.wm-text-2') != ''){
		var text2 = $('.wm-text-2').val();
		var pos2 = $('.wm-pos-2').val();
		var color2 = $('.wm-color-2').val();
		var font2 = $('.wm-font-2').val();
		var opacity2 = parseFloat($('.wm-opacity-2').val());
		var fsize2 = parseInt($('.wm-font-size-2').val());
		var angle2 = parseInt($('.wm-rotate-2').val());
		
		if(isNaN(opacity2) || isNaN(fsize2) || isNaN(angle2)){
			return notify('error', lang.inv_input);	
		}
		if(fsize2 > 200 || fsize2 < 1){
			return notify('error', lang.font_size_must);	
		}
		if(angle2 < -180 || angle2 > 180){
			return notify('error', lang.angle_must);	
		}
		if(opacity2 > 1 || opacity2 < 0){
			return notify('error', lang.opa_must);	
		}
		if(angle2 != 0 && pos2 != 'center'){
			if(!confirm_action(lang.ang_center, $(this)))return false;
		}		
	}
	
	
	var src = $('.editor-before-img > img').prop('src');
	if(angle != 0){
		watermark([src])
		  .image(rotate)
		  .then(function (img) {
			var src = $(img).prop('src');
			$('.editor-after-img > img').attr('src', src);
			if(text2 != ''){
				watermark_text2();	
			}
		  });	
	}
	else{
		switch(pos){
			case "lowerRight":
				watermark([src])
				  .image(watermark.text.lowerRight(text, fsize + 'px ' + font , color, opacity))
				  .then(function (img) {
					var src = $(img).prop('src');
					$('.editor-after-img > img').attr('src', src);
					if(text2 != ''){
						watermark_text2();	
					}
				  });
			 break;	
			 case "lowerLeft":
				watermark([src])
				  .image(watermark.text.lowerLeft(text, fsize + 'px ' + font , color, opacity))
				  .then(function (img) {
					var src = $(img).prop('src');
					$('.editor-after-img > img').attr('src', src);
					if(text2 != ''){
						watermark_text2();	
					}
				  });
			 break;	
			 case "upperRight":
				watermark([src])
				  .image(watermark.text.upperRight(text, fsize + 'px ' + font , color, opacity, 48))
				  .then(function (img) {
					var src = $(img).prop('src');
					$('.editor-after-img > img').attr('src', src);
					if(text2 != ''){
						watermark_text2();	
					}
				  });
			 break;	
			 case "upperLeft":
				watermark([src])
				  .image(watermark.text.upperLeft(text, fsize + 'px ' + font , color, opacity, 48))
				  .then(function (img) {
					var src = $(img).prop('src');
					$('.editor-after-img > img').attr('src', src);
					if(text2 != ''){
						watermark_text2();	
					}
				  });
			 break;	
			case "center":
				watermark([src])
				  .image(watermark.text.center(text, fsize + 'px ' + font , color, opacity))
				  .then(function (img) {
					var src = $(img).prop('src');
					$('.editor-after-img > img').attr('src', src);
					if(text2 != ''){
						watermark_text2();	
					}
				  });
			 break;	
		}
	}
});

$(document).on('click', '.wm-img-apply', function(){
	var img = $('.wm-img-img').val();
	var pos = $('.wm-img-pos').val();
	var opacity = parseFloat($('.wm-img-opacity').val());
	
	if(isNaN(opacity) || img == ''){
		return notify('error', lang.inv_input);	
	}
	if(opacity > 1 || opacity < 0){
		return notify('error', lang.opa_must);	
	}
	
	var src = $('.editor-before-img > img').prop('src');
	
	switch(pos){
		case "lowerRight":
			watermark([src, img])
			  .image(watermark.image.lowerRight(opacity))
			  .then(function (img) {
				var src = $(img).prop('src');
				$('.editor-after-img > img').attr('src', src);
			  });
		 break;	
		 case "lowerLeft":
			watermark([src, img])
			  .image(watermark.image.lowerLeft(opacity))
			  .then(function (img) {
				var src = $(img).prop('src');
				$('.editor-after-img > img').attr('src', src);
			  });
		 break;	
		 case "upperRight":
			watermark([src, img])
			  .image(watermark.image.upperRight(opacity, 48))
			  .then(function (img) {
				var src = $(img).prop('src');
				$('.editor-after-img > img').attr('src', src);
			  });
		 break;	
		 case "upperLeft":
			watermark([src, img])
			  .image(watermark.image.upperLeft(opacity, 48))
			  .then(function (img) {
				var src = $(img).prop('src');
				$('.editor-after-img > img').attr('src', src);
			  });
		 break;	
		case "center":
			watermark([src, img])
			  .image(watermark.image.center(opacity))
			  .then(function (img) {
				var src = $(img).prop('src');
				$('.editor-after-img > img').attr('src', src);
			  });
		 break;	
	}
});

$(document).on('click', '.save-wm', function(){
	$('.save-effect').click();
});

$(document).on('click', '.save-wm-img', function(){
	$('.save-effect').click();
});

$(document).on('click', '.wm-meme', function(){
	$('.meme-input').toggle(); 
	if($('.meme-input').is(':hidden') == true)$('.wm-text-2').val('');
	else{
		$('.wm-pos').val('upperLeft');
		$('.wm-pos-2').val('lowerLeft');	
	}
});


$(document).on('click', '.cancel-wm', function(){
	$('.save-wm, .cancel-wm, .wm-controls, .div-12').hide();
	$('.apply-opt').show();
	$('.editor-after-img, .img-options, .editor-after-img-hidden').html('');
});

$(document).on('click', '.cancel-wm-img', function(){
	$('.save-wm-img, .cancel-wm-img, .wm-img-controls, .div-12').hide();
	$('.apply-opt').show();
	$('.editor-after-img, .img-options, .editor-after-img-hidden').html('');
});

$(document).on('click', '.show-crop', function(){
	$('.editor-before-img > img').cropper({
	  autoCrop: false,
	  minCropBoxWidth: 30,
	  minCropBoxHeight: 30,
	  minContainerWidth: 30,
	  minContainerHeight: 30,
	  strict: false,
	  guides: false,
	  preview: '.cropped-after-img',
	  crop: function(data){
		  $('.crop-width').val(data.width);
		  $('.crop-height').val(data.height);
		  $('.crop-x').val(data.x);
		  $('.crop-y').val(data.y);
		  $('.crop-angle').val(data.rotate);
	  }
	});
	var html = '<div class="row">'+
					'<div class="col-lg-2">Width: <input class="form-control small-input crop-width"></div>'+
					'<div class="col-lg-2">Height: <input class="form-control small-input crop-height"></div>'+
					'<div class="col-lg-2">X: <input class="form-control small-input crop-x"></div>'+
					'<div class="col-lg-2">Y: <input class="form-control small-input crop-y"></div>'+
					'<div class="col-lg-2">Rotate: <input class="form-control small-input crop-angle"></div>'+
				'</div><br/>'+			
				'<button class="btn btn-sm btn-info crop-box-resize">'+lang.set_size+'</button>';
				
	$('.img-options').html(html);
	$('.apply-opt, .editor-after-img, .editorfullscreen-go').hide();
	$('.cancel-crop, .save-crop, .cropped-after-img, .img-options').show();
});

$(document).on('click', '.show-resize', function(){
	var src = $('.editor-before-img > img').attr('src');
	$('.editor-after-img').html('<canvas id="mycanvas"></canvas><canvas id="hcanvas" class="editor-after-canvas" style="display:none"></canvas>');
	
	var canvas = document.getElementById("mycanvas");
	var hcanvas = document.getElementById("hcanvas");
	
	var img = new Image();
	img.src = src;
	
	img.onload = function(){
		var w = img.naturalWidth;
		var h = img.naturalHeight;
		var r = getRatio(w);
		 
		hcanvas.width = w;
    	hcanvas.height = h;
		
    	ctx = hcanvas.getContext('2d');
    	ctx.drawImage(this, 0, 0, w, h);
		
		canvas.width = parseInt(w*r);
    	canvas.height = parseInt(h*r);
		
    	ctx = canvas.getContext('2d');
    	ctx.drawImage(this, 0, 0, parseInt(w*r), parseInt(h*r));
	};
	
	var html = '<div class="row">'+
					'<div class="col-lg-2">Width: <input class="form-control small-input resize-width"></div>'+
					'<div class="col-lg-2">Height: <input class="form-control small-input resize-height"></div>'+
				'</div><br/>'+			
				'<button class="btn btn-sm btn-info apply-resize">'+lang.set_size+'</button>';
				
	$('.img-options').html(html);
	$('.apply-opt, .editorfullscreen-go').hide();
	$('.cancel-resize, .save-resize, .img-options').show();
});

$(document).on('click', '.apply-resize', function(){
	var w = parseInt($('.resize-width').val());
	var h = parseInt($('.resize-height').val());
	
	if(isNaN(w) || isNaN(h)){
		return notify('error', lang.inv_input);	
	}
	if(w < 10 || h < 10){
		return notify('error', lang.wh_10px);	
	}

	var src = $('.editor-before-img > img').attr('src');
	
	var canvas = document.getElementById("mycanvas");
	var hcanvas = document.getElementById("hcanvas");
	
	var img = new Image();
	img.src = src;
	
	img.onload = function(){
		var r = getRatio(w);
		
		hcanvas.width = w;
    	hcanvas.height = h;
		
    	ctx = hcanvas.getContext('2d');
    	ctx.drawImage(this, 0, 0, w, h);
		
		canvas.width = parseInt(w*r);
    	canvas.height = parseInt(h*r);
		
    	ctx = canvas.getContext('2d');
    	ctx.drawImage(this, 0, 0, parseInt(w*r), parseInt(h*r));
	};
	
});

$(document).on('click', '.crop-box-resize', function(){
	var w = parseInt($('.crop-width').val());
	var h = parseInt($('.crop-height').val());
	var x = parseFloat($('.crop-x').val());
	var y = parseFloat($('.crop-y').val());
	var a = parseInt($('.crop-angle').val());
	
	if(isNaN(w) || isNaN(h) || isNaN(x) || isNaN(y) || isNaN(a)){
		return notify('error', lang.inv_input);	
	}
	if(w < 10 || h < 10){
		return notify('error', lang.wh_10px);	
	}
	/*if(x < 0 || y < 0){
		return notify('error', 'X and Y must be greater than 0');	
	}*/
	if(a < -180 || a > 180){
		return notify('error', lang.angle_must);	
	}
	
	data = {"width" : w, "height": h, "x": x, "y": y, "rotate": a};
	$('.editor-before-img > img').cropper('setData', data);
});

$(document).on('click', '.save-resize', function(){
	var canvas = document.getElementById("hcanvas");
	canvas.toBlob(function(img) {
		saveBlobImage(img);
	});
});

$(document).on('click', '.save-crop', function(){
	$('.editor-before-img > img').cropper('getCroppedCanvas').toBlob(function(img){
		saveBlobImage(img);
	});
});

$(document).on('click', '.save-effect', function(){
	var src = $('.editor-after-img > img').prop('src');
	
	if(src.match(/data:image\/jpeg/gi))var base64 = src.split("data:image/jpeg;base64,")[1];
	else if(src.match(/data:image\/png/gi))var base64 = src.split("data:image/png;base64,")[1];
	else return notify('error', 'Invalid image');
	
	var img = b64toBlob(base64, 'image/png');
	saveBlobImage(img);
});

$(document).on('click', '.cancel-crop', function(){
	$('.editor-before-img > img').cropper('destroy');
	$('.cancel-crop, .save-crop, .cropped-after-img').hide();
	$('.apply-opt, .editor-after-img, .editorfullscreen-go').show();
	$('.img-options').html('');
});

$(document).on('click', '.cancel-resize', function(){
	$('.editor-after-img').html('');
	$('.cancel-resize, .save-resize, .cropped-after-img').hide();
	$('.apply-opt, .editor-after-img, .editorfullscreen-go').show();
	$('.img-options').html('');
});

$(document).on('click', '.add-effect', function(){
	var btns = ['default', 'primary', 'info', 'warning', 'danger', 'success'];
	
	$('.save-effect, .cancel-effect, .effect-controls, .div-12').show();
	$('.apply-opt, .effect-custom').hide();
	
	var file = $('.editor-before-img > img').attr('src');
	$('.editor-after-img').html('<img src="'+file+'" style="max-width:420px"/>');
	var html = '';
	$.each(vintagePresets, function(index, val){
		arr = shuffle(btns);
		html += '<button class="btn btn-sm btn-'+arr[0]+' apply-effect" rel="'+index+'">'+index+'</button>&nbsp;';
	});
	html += '<button class="btn btn-sm btn-info apply-effect" rel="custom" onclick="$(\'.effect-custom\').show();$(\'.editorfullscreen-go\').click()">custom</button>&nbsp;';
	$('.img-options').html(html);
});

$(document).on('click', '.cancel-effect', function(){
	$('.editor-after-img, .img-options, .editor-after-img-hidden').html('');
	$('.apply-opt').show();
	$('.save-effect, .cancel-effect, .effect-custom, .effect-controls, .div-12').hide();
});

$(document).on('click', '.apply-effect', function(){
	var file = $('.editor-before-img > img').attr('src');
	$('.editor-after-img-hidden').html('<img src="'+file+'" style="max-width:420px"/>');
	
	var filter = $(this).attr('rel');
	var options = {
        onError: function() {
           notify('error', 'Failed to apply effect');
        },
		onStop: function() {
			$('.editor-after-img').html('<img src="'+$('.editor-after-img-hidden > img').attr('src')+'" style="max-width:420px"/>');	
		}
    };
    var effect = vintagePresets[filter];
    var vimg = $('.editor-after-img-hidden > img').vintage(options, effect);
});


$(document).on('click', '.editorfullscreen-go', function(){
	$('#ftrans').val(1);
	var sepia = $('#sepia').is(':checked');
	var vf = $('.view_finder').val();
	
	if($('.tmpdiv').hasClass('fullscreen')){
		$('.div-11, .div-12').removeClass('col-lg-6');
		$('.div-12').addClass('row');
		var html = $('.tmpdiv').html();
		$('.tmpdiv').html('').removeClass('fullscreen').hide();
		$('.editor-after').html(html);	
		prepare_sliders();	
	}
	else{
		$('.div-12').removeClass('row');
		$('.div-11, .div-12').addClass('col-lg-6');
		var html = $('.editor-after').html();
		$('.editor-after').html('');
		$('.tmpdiv').html('<div>' + html + '</div>').addClass('fullscreen').show();
		window.location.href = window.location.href.replace(location.hash, "") + "#";
		prepare_sliders();
	}
	setTimeout(function(){
		if(sepia == true)$('#sepia').prop('checked', true);
		$('.view_finder').val(vf);
		$('#ftrans').val(0);
	}, 1000);
});

$(document).on('click', '#sepia', function(){
	apply_custom_filter(1);
});

$(document).on('change', '.tint-color', function(){
	apply_custom_filter(1);
});

$(document).on('change', '.view_finder', function(){
	if($('.view_finder').val() != '')
		apply_custom_filter(1);
});


function colorpicker(){
	var val1 = '#ffffff';
	var val2 = '#ffffff';
	var val3 = '#ffffff';
	
	$('.sp-replacer').remove();
	
	$(".wm-color").spectrum({
		showInput: true,
		preferredFormat: "hex",
		color: val1
	});
	
	$(".wm-color-2").spectrum({
		showInput: true,
		preferredFormat: "hex",
		color: val2
	});
	
	$(".tint-color").spectrum({
		showInput: true,
		preferredFormat: "rgb",
		color: val3
	});
}

prepare_sliders();

function prepare_sliders(){
	
	colorpicker();
	
	$(".slider-1").slider({
		min: -255, 
		max: 255, 
		value: 1, 
		step: 1,
		change: function( event, ui ) {
			apply_custom_filter($(this), ui);	
		} 	
	});
	
	$(".slider-2").slider({
		min: 0, 
		max: 1, 
		value: 0, 
		step: 0.01,
		change: function( event, ui ) {
			apply_custom_filter($(this), ui);	
		} 	
	});
	
	$(".slider-3").slider({
		min: 0, 
		max: 50, 
		value: 0, 
		step: 1,
		change: function( event, ui ) {
			apply_custom_filter($(this), ui);	
		} 	
	});
	
	$('.slider-val').each(function(){
		var className = $(this).attr('class').split(' ')[2].split('-')[0];
		$(".slider[rel='"+className+"']").slider('value', parseFloat($(this).html()));
	});	
}

function watermark_text2()
{
	var text = $('.wm-text-2').val();
	var pos = $('.wm-pos-2').val();
	var color = $('.wm-color-2').val();
	var font = $('.wm-font-2').val();
	var opacity = parseFloat($('.wm-opacity-2').val());
	var fsize = parseInt($('.wm-font-size-2').val());
	var angle = parseInt($('.wm-rotate-2').val());
	
	var src = $('.editor-after-img > img').prop('src');
	if(angle != 0){
		watermark([src])
		  .image(rotate)
		  .then(function (img) {
			var src = $(img).prop('src');
			$('.editor-after-img > img').attr('src', src);
		  });	
	}
	else{
		switch(pos){
			case "lowerRight":
				watermark([src])
				  .image(watermark.text.lowerRight(text, fsize + 'px ' + font , color, opacity))
				  .then(function (img) {
					var src = $(img).prop('src');
					$('.editor-after-img > img').attr('src', src);
				  });
			 break;	
			 case "lowerLeft":
				watermark([src])
				  .image(watermark.text.lowerLeft(text, fsize + 'px ' + font , color, opacity))
				  .then(function (img) {
					var src = $(img).prop('src');
					$('.editor-after-img > img').attr('src', src);
				  });
			 break;	
			 case "upperRight":
				watermark([src])
				  .image(watermark.text.upperRight(text, fsize + 'px ' + font , color, opacity, 48))
				  .then(function (img) {
					var src = $(img).prop('src');
					$('.editor-after-img > img').attr('src', src);
				  });
			 break;	
			 case "upperLeft":
				watermark([src])
				  .image(watermark.text.upperLeft(text, fsize + 'px ' + font , color, opacity, 48))
				  .then(function (img) {
					var src = $(img).prop('src');
					$('.editor-after-img > img').attr('src', src);
				  });
			 break;	
			case "center":
				watermark([src])
				  .image(watermark.text.center(text, fsize + 'px ' + font , color, opacity))
				  .then(function (img) {
					var src = $(img).prop('src');
					$('.editor-after-img > img').attr('src', src);
				  });
			 break;	
		}
	}
}

function apply_custom_filter(elem, ui)
{
	if($('#ftrans').val() == 1)return;
	if(elem != 1){
		if(elem.is(':hidden') == true)return;
		var id = elem.attr('rel');
		$('.'+id+'-val').html(ui.value);
	}
	
	var file = $('.editor-before-img > img').attr('src');
	if(file == '')return;
	
	$('.editor-after-img-hidden').html('<img src="'+file+'" style="max-width:420px"/>');;
	
	var filter = $(this).attr('rel');
	var options = {
        onError: function() {
           notify('error', 'Failed to apply effect');
        },
		onStop: function() {
			$('.editor-after-img').html('<img src="'+$('.editor-after-img-hidden > img').attr('src')+'" style="max-width:420px"/>');	
		}
    };
	
	var brightness = $('.slider[rel="brightness"]').slider("option", "value");
	var contrast = $('.slider[rel="contrast"]').slider("option", "value");
	var vignette = $('.slider[rel="vignette"]').slider("option", "value");
	var lighten = $('.slider[rel="lighten"]').slider("option", "value");
	var desaturate = $('.slider[rel="desaturate"]').slider("option", "value");
	var noise = $('.slider[rel="noise"]').slider("option", "value");
	var sepia = $('#sepia').is(':checked') == true ? 1 : 0;
	var tint = $('.slider[rel="tint"]').slider("option", "value");
	var viewFinder = $('.view_finder').val();
	var tint_ok = 0;
	if(tint != 0){
		var tint_color = $('.tint-color').val();
		if(tint_color != ''){
			tint_color = tint_color.replace('rgb(', '');
			tint_color = tint_color.replace(')', '');
			tint_color = tint_color.split(',');
			tint_ok = 1;
		}
	}
	
    var effect = {
		brightness : brightness,
		contrast: contrast,
		vignette: vignette,
		lighten: lighten,
		noise: noise,
		sepia: sepia,
	};
	
	if(tint_ok == 1)effect['screen'] = {r: tint_color[0], g: tint_color[1], b: tint_color[2], a : tint}
	if(viewFinder != '')effect['viewFinder'] = viewFinder;
	
    $('.editor-after-img-hidden > img').vintage(options, effect).show();
}


function shuffle(array) {
  var currentIndex = array.length, temporaryValue, randomIndex ;

  // While there remain elements to shuffle...
  while (0 !== currentIndex) {

    // Pick a remaining element...
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex -= 1;

    // And swap it with the current element.
    temporaryValue = array[currentIndex];
    array[currentIndex] = array[randomIndex];
    array[randomIndex] = temporaryValue;
  }

  return array;
}

function saveBlobImage(img, dynamic)
{
	var vname = Date.now().toString() + (Math.random() * (100000 - 999999) + 100000).toString();
	vname = vname.replace(/[^0-9]/, '');
	vname += '.png';
	var formData = new FormData();
	formData.append('file', img);
	formData.append('plugin', 'media');
	if(dynamic == null)formData.append('path', $('.editor-before-img').find('img').attr('src'));
	else formData.append('path', vname);
	formData.append('saveCroppedImg', 1);
	
	notify('wait', lang.saving_img+'...');
	
	$.ajax('upload.php', {
		method: "POST",
		data: formData,
		processData: false,
		contentType: false,
		success: function (response) {
		  var data = $.parseJSON(response);
			if(data.error != '')return notify('error', data.error);
			else{	
				if(dynamic == null){
					notify('success', lang.img_saved);
					var file = 'tmp/'+data.uploadName;
					$('.editor-before-img').html('<img src="'+file+'?t='+Math.random()+'" style="max-width:420px"/>');
					$('.cancel-resize, .cancel-effect, .cancel-crop, .cancel-wm, .cancel-wm-img').click();
					$('#edited-img').val(1);
				}
				else{
					notify('success', lang.img_saved_imp);
					$('.editor-before-img').html('<img src="tmp/'+vname+'"/>');
					$('.import-edited-img').click();	
				}
			}
		},
		error: function () {
			notify('error', lang.img_save_fail);
		}
	  });
}

function b64toBlob(b64Data, contentType, sliceSize) {
    contentType = contentType || '';
    sliceSize = sliceSize || 512;

    var byteCharacters = atob(b64Data);
    var byteArrays = [];

    for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
        var slice = byteCharacters.slice(offset, offset + sliceSize);

        var byteNumbers = new Array(slice.length);
        for (var i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }

        var byteArray = new Uint8Array(byteNumbers);

        byteArrays.push(byteArray);
    }

    var blob = new Blob(byteArrays, {type: contentType});
    return blob;
}

function loadCanvasFonts()
{
	html = '<option value="Impact">Impact</option>';
	html += '<option value="Josefin Slab">Josefin Slab</option>';
	html += '<option value="Georgia">Georgia</option>';	
	html += '<option value="tahoma">Tahoma</option>';	
	html += '<option value="Times New Roman">Times New Roman</option>';	
	html += '<option value="Arial">Arial</option>';
	html += '<option value="Comic Sans MS">Comic Sans MS</option>';
	html += '<option value="Lucida Sans Unicode">Lucida Sans Unicode</option>';
	html += '<option value="Trebuchet MS">Trebuchet MS</option>';
	html += '<option value="Verdana">Verdana</option>';
	html += '<option value="Courier New">Courier New</option>';	
	html += '<option value="Lucida Console">Lucida Console</option>';	
	return html
}

function create_editor_uploader()
{
	var accept = '.jpg, .png, .jpeg, .flv, .mp4';
	var a = $('.dropzone-editor').attr('accept');
	if(a != null){
		if(a == 'image/*')accept = '.jpg, .png, .jpeg';
		else accept = '.flv, .mp4';	
	}	
		
	$(".dropzone").dropzone({
		url: upload_url_tmp ,
		maxFilesize: 250, //in MB
		maxFiles: 1,
		acceptedFiles: accept,
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
		  this.on("complete", function (file, response) {
			var mydropzone = this;
			mydropzone.removeAllFiles();
			if(file.xhr == null)return false;
			var data = $.parseJSON(file.xhr.response);
			if(data.error != ''){
				notify('error', data.error);	
			}
			else{
				var file = 'tmp/'+data.uploadName;
				
				if(data.file_type == 'image'){				
					$('.editor-before-img').html('<img src="'+file+'" style="max-width:420px"/>');
					$('.editor-after-img').html('<img src="'+file+'" style="max-width:420px"/>');
					
					if(typeof getImageSize == 'function' && $('.meme-editor').length > 0)getImageSize(file, 0);	
					
					$('.cancel-crop, .cancel-effect, .cancel-wm, .cancel-wm-img').click();
					$('.editor-before, .editor-after').removeClass('wmtrans');
					$('.editor').show();
					window.location.href = window.location.href.replace(location.hash,"") + '#editor';
				}
				else{
					jp_instance = jwplayer("vplayer").setup({
						file: file,
						provider: "http",
						primary: 'html5'
					});
					jp_instance.on('time', function(t){
						if(t != null){
							var date = new Date(null);
							date.setSeconds(t.position);
							var p = date.toISOString().substr(11, 8);
							$('.jplay_time').val(p);		
						}
					});
					$('.v-editor').show();
					$('.v-pending, .v-segments').html('');
					$('.pending_submit').hide();
					$('.p_name').val(data.orgName);
					window.location.href = window.location.href.replace(location.hash,"") + '#v-editor';
				}
				$('#up_files').val($('#up_files').val()+ ',' +data.uploadName);
			}
		});
	  }
	});
}

/*
 * Code for meme generation
 */
var canvas;
var stage;		
var update = true;
/**
 * Init 
 */
memeinit = function () {
	canvas = document.getElementById("mycanvas");
	stage = new createjs.Stage(canvas);
	createjs.Touch.enable(stage);
	stage.enableMouseOver(10);
}

/**
 * Load and display the uploaded picture on CreateJS Stage 
 */
displayPicture = function (imgPath, dragDrop, pos) {

	var image = new Image();	
	image.onload = function (event) {
		// Create a Bitmap from the loaded image
		var img = new createjs.Bitmap(event.target)
		// scale it
		var width = img.image.width;
		var height = img.image.height;
		
		var r = 1;
		if(dragDrop == 0)r = getRatio(width);
		else{
			img.x = 10;
			img.y = 10;
			if(pos != null){
				if(pos == 2)img.y = $('#imh').val() - 80;
			}
		}
		
		img.scaleX = img.scaleY = img.scale = r;
		/// Add to display list
		stage.addChild(img);
		//Enable Drag'n'Drop 
		if(dragDrop == 1){
			img.cursor = 'move';
			img.addEventListener("mousedown", function (evt) {
				// bump the target in front of its siblings:
				var o = evt.target;
				o.parent.addChild(o);
				o.offset = {x: o.x - evt.stageX, y: o.y - evt.stageY};
			});

			// the pressmove event is dispatched when the mouse moves after a mousedown on the target until the mouse is released.
			img.addEventListener("pressmove", function (evt) {
				var o = evt.target;
				o.x = evt.stageX + o.offset.x;
				o.y = evt.stageY + o.offset.y;
				// indicate that the stage should be updated on the next tick:
				update = true;
			});
		}
		// Render Stage
		stage.update();
	}
	// Load the image
	image.src = imgPath;
	createjs.Ticker.addEventListener("tick", tick);
}

function tick(event) {
	// this set makes it so the stage only re-renders when an event handler indicates a change has happened.
	if (update) {
		update = false; // only update once
		stage.update(event);
	}
}

getImageSize = function(src, dragDrop) {
	var img = new Image();
	img.src = src;
	img.addEventListener('load', function() {		
	  // once the image is loaded:
	  var context = canvas.getContext('2d');
	  if(!dragDrop){
		context.clearRect(0, 0, canvas.width, canvas.height);  
	  }
	  
	  var width = img.naturalWidth;
	  var height = img.naturalHeight;
	  
	  var r = getRatio(width);
	  
	  width = parseInt(width*r);
	  height = parseInt(height*r);
	  
	  $('#imw').val(width);
	  $('#imh').val(height);
	  	  
	  context.canvas.width = width;
	  context.canvas.height = height;
	  displayPicture(src, dragDrop);
	  
	}, false);
	img.src = src;	
}

getRatio = function(width)
{	
	if(width <= 500){
		return 1;
	}	
	else if(width > 500 && width <= 600){
		return 0.70;
	}
	else if(width > 600 && width <= 800){
		return 0.60;
	}
	else if(width > 800 && width <= 1000){
		return 0.50;
	}
	else if(width > 1000 && width <= 1300){
		return 0.40;
	}
	else if(width > 1300 && width <= 1500){
		return 0.30;
	}
	else return 0.20;	
}

writeText = function(pos){
	if(pos == 1){
		var text = $('.wm-text').val();
		var color = $('.wm-col').val();
		var font = $('.wm-font').val();
		var opacity = parseFloat($('.wm-opacity').val());
		var fsize = parseInt($('.wm-font-size').val());
		var angle = parseInt($('.wm-rotate').val());
	}
	else{
		var text = $('.wm-text-2').val();
		var color = $('.wm-col-2').val();
		var font = $('.wm-font-2').val();
		var opacity = parseFloat($('.wm-opacity-2').val());
		var fsize = parseInt($('.wm-font-size-2').val());	
		var angle = parseInt($('.wm-rotate-2').val());
	}
	
	if(isNaN(opacity) || isNaN(fsize) || isNaN(angle) || text == ''){
		return notify('error', lang.inv_input);	
	}
	if(fsize > 200 || fsize < 1){
		return notify('error', lang.font_size_must);	
	}
	if(opacity > 1 || opacity < 0){
		return notify('error', lang.opa_must);	
	}
	if(angle > 180 || angle < -180){
		return notify('error', lang.angle_must);	
	}
	
	if(pos == 1)var canvas = document.getElementById("hcanvas");
	else var canvas = document.getElementById("hcanvas2");
	
	color = color.replace('rgb', 'rgba');
	color = color.replace(')', ',' + opacity + ')');	
	if(color == '')color = 'rgba(255, 255, 255, 1)';
	
  	var context = canvas.getContext("2d");	
	context.clearRect(0, 0, canvas.width, canvas.height);
	context.fillStyle = color;
  	context.font = fsize+"px "+font;
	context.rotate(angle * Math.PI / 180);
	context.fillText(text, 48, 48);
	var src = canvas.toDataURL();
	displayPicture(src, 1, pos);	
}

$(document).on('click', '.wmm-apply', function(){
	var s = 0;
	if($('.wm-text').val() != ''){
		writeText(1);
		$('.wm-text').val('');
		s = 1;
	}
	if($('.wm-text-2').val() != ''){
		writeText(2);	
		$('.wm-text-2').val('');
		s = 1;
	}
	if(!s)return notify('error', lang.btm);
	else notify('success', lang.added_now_drag);
	$('#import-edited-type').val('image');
	$('#edited-img').val(1);
});

$(document).on('click', '.wmm-reset', function(){
	var file = $('.editor-after-img > img').attr('src');
	getImageSize(file, 0);	
});

/**
 * check if this page is meme.php | has meme uploader
 */
if($('.meme-editor').length > 0){ 
	memeinit();
	
	if(preload_file != null){
		if(preload_file != ''){
			getImageSize(preload_file, 0);
			$('.editor').show();
			window.location.href = window.location.href.replace(location.hash,"") + '#editor';	
		}
	}
}

/**
 * Jplayer
 */
$(document).on('click', '.jp_fivef', function(){
	jp_instance.seek(jp_instance.getPosition() + 5); 
	jp_pause_seek();
});

$(document).on('click', '.jp_fiveb', function(){
	jp_instance.seek(jp_instance.getPosition() - 5); 
	jp_pause_seek();
});
$(document).on('click', '.jp_onef', function(){
	jp_instance.seek(jp_instance.getPosition() + 1); 
	jp_pause_seek();
});

$(document).on('click', '.jp_oneb', function(){
	jp_instance.seek(jp_instance.getPosition() - 1); 
	jp_pause_seek();
});

$(document).on('keypress', '.jplay_jump' , function(e){
	var code = (e.keyCode ? e.keyCode : e.which);
	
	if(code == 13) {
		jp_instance.seek(parseFloat($(this).val()));
		jp_pause_seek();
		return false;
	}
});

$(document).on('click', '.take_screenshot', function(){
	var t = jp_instance.getPosition(); 
	var date = new Date(null);
	date.setSeconds(t);
	var p = date.toISOString().substr(11, 8);
	$('.v-pending').append('<tr rel="'+t+'" rel-type="screenshot"><td><i class="glyphicon glyphicon-camera"></i>&nbsp;&nbsp;Create a screenshot at '+p+'&nbsp;&nbsp;<button class="btn btn-sm btn-danger" onclick="$(this).parents(\'tr:first\').remove()"><i class="glyphicon glyphicon-trash"></i></button></td></tr>');
	$('.pending_submit').show();
});

$(document).on('click', '.create_tile', function(){
	$('.tile-size-modal').modal();
});

$(document).on('click', '.tsize-btn', function(){
	var tsize = $('#tsize').val();
	if(tsize == '' || !tsize.match(/([0-9+])x([0-9+])/gi))return notify('error', 'Invalid tile size');
	if($('.v-pending').find('tr[rel="'+tsize+'"]').length > 0)return notify('error', 'Screenshot tile of same size is already queued');
	
	$('.v-pending').append('<tr rel="'+tsize+'" rel-type="tile"><td><i class="glyphicon glyphicon-camera"></i>&nbsp;&nbsp;Create a screenshot tile sized '+tsize+' of full video&nbsp;&nbsp;<button class="btn btn-sm btn-danger" onclick="$(this).parents(\'tr:first\').remove()"><i class="glyphicon glyphicon-trash"></i></button></td></tr>');
	$('.pending_submit').show();
	$('.tile-size-modal').modal('hide');
});

$(document).on('click', '.start_segment', function(){
	var last = $('.v-segments').find('tr:first');
	if(last.length > 0 && last.attr('status') != 'ended'){
		return notify('error', lang.seg_started);
	}
	
	var t = jp_instance.getPosition(); 
	var date = new Date(null);
	date.setSeconds(t);
	var p = date.toISOString().substr(11, 8);
	
	var v = $('.v-segments').find('tr:first').attr('rel-sl');
	var total = parseInt(v == null ? 0 : v) + 1;
	
	$('.v-segments').prepend('<tr status="started" rel-start="'+t+'" rel-start-h="'+p+'" rel-sl="'+total+'"><td><input type="checkbox" class="segment_ch"/>&nbsp;&nbsp;<i class="glyphicon glyphicon-film"></i>&nbsp;&nbsp;Segment started at '+p+'&nbsp;&nbsp;<button class="btn btn-sm btn-danger" onclick="$(this).parents(\'tr:first\').remove()"><i class="glyphicon glyphicon-trash"></i></button></td></tr>');
});

$(document).on('click', '.end_segment', function(){
	var last = $('.v-segments').find('tr:first');
	if(last.length <= 0 || last.attr('status') != 'started'){
		return notify('error', lang.start_seg);
	}
	
	var t = jp_instance.getPosition(); 
	
	var date = new Date(null);
	date.setSeconds(t);
	var p = date.toISOString().substr(11, 8);
	
	last.html();
	var ss = last.attr('rel-start-h');
	var sst = last.attr('rel-start');
	
	if(parseInt(t) - parseInt(sst) < 5)return notify('error', 'A segment must be at least 5 seconds longer'); 
	
	last.attr('status', 'ended');
	last.attr('rel-end', t);
	last.attr('rel-end-h', p);
	
	var v = $('.v-segments').find('tr:first').attr('rel-sl');
	var total = parseInt(v == null ? 1 : v);
	
	last.html('<td><input type="checkbox" class="segment_ch"/>&nbsp;&nbsp;Segment#'+total+'&nbsp;&nbsp;<i class="glyphicon glyphicon-film"></i>&nbsp;&nbsp;Segment from '+ss+' from '+p+' &nbsp;&nbsp; <button class="btn btn-sm btn-danger" onclick="$(this).parents(\'tr:first\').remove()"><i class="glyphicon glyphicon-trash"></i></button></td>');
	
	create_v_segment_action_btns();
});

$(document).on('click', '.join_seg', function(){
	var elem = $('.v-segments').find('tr');
	if(elem.length <= 0)return notify('error', lang.no_seg_created);
	
	var segs = [];
	elem.each(function(){
		var e = $(this);
		if(e.find('input[type="checkbox"]').is(':checked') == true){
			j = e.attr('rel-sl');
			if(e.attr('rel-start') != null && e.attr('rel-end') != null)segs.push(j);
		}	
	});
	if(segs.length <= 0)return notify('error', lang.no_seg_sel);
	
	segs.sort();
	segs = segs.join(',');
	
	if($('.v-pending').find('tr[rel-type="join"]').length > 0 && $('.v-pending').find('tr[rel="'+segs+'"]').length > 0)return notify('error', 'This task is already queued');
	
	$('.v-pending').append('<tr rel="'+segs+'" rel-type="join"><td><i class="glyphicon glyphicon-camera"></i>&nbsp;&nbsp;Join segments '+segs+'&nbsp;&nbsp;<button class="btn btn-sm btn-danger" onclick="$(this).parents(\'tr:first\').remove()"><i class="glyphicon glyphicon-trash"></i></button></td></tr>');
	$('.pending_submit').show();
});

$(document).on('click', '.cut_seg', function(){
	var elem = $('.v-segments').find('tr');
	if(elem.length <= 0)return notify('error', lang.no_seg_created);
	
	var segs = [];
	elem.each(function(){
		var e = $(this);
		if(e.find('input[type="checkbox"]').is(':checked') == true){
			j = e.attr('rel-sl');
			if(e.attr('rel-start') != null && e.attr('rel-end') != null)segs.push(j);
		}
	});
	if(segs.length <= 0)return notify('error', lang.no_seg_sel);
	
	segs.sort();
	segs = segs.join(',');
	
	if($('.v-pending').find('tr[rel-type="cut"]').length > 0 && $('.v-pending').find('tr[rel="'+segs+'"]').length > 0)return notify('error', 'This task is already queued');
	
	$('.v-pending').append('<tr rel="'+segs+'" rel-type="cut"><td><i class="glyphicon glyphicon-camera"></i>&nbsp;&nbsp;Cut segments '+segs+'&nbsp;&nbsp;<button class="btn btn-sm btn-danger" onclick="$(this).parents(\'tr:first\').remove()"><i class="glyphicon glyphicon-trash"></i></button></td></tr>');
	$('.pending_submit').show();
});

$(document).on('click', '.pending_submit', function(){
	var segments = [];
	var tasks = [];
	var seg = $('.v-segments').find('tr');
	
	seg.each(function(){
		var e = $(this);
		j = e.attr('rel-sl');
		if(e.attr('rel-start') != null && e.attr('rel-end') != null)segments.push({'start': e.attr('rel-start'), 'end' : e.attr('rel-end'), 'index': j});
	});

	var ts = $('.v-pending').find('tr');
	
	ts.each(function(){
		var e = $(this);
		tasks.push({'rel' : e.attr('rel'), 'type' : e.attr('rel-type')});
	});
	
	if(tasks.length <= 0)return notify('error', lang.no_task);
	
	notify('wait', lang.requesting+'...');
	
	$.post(ajax_url, {
		'add_video_task': 1,
		'tasks': JSON.stringify(tasks),
		'segments': JSON.stringify(segments),
		'video': jp_instance.getPlaylistItem()['file'],
		'title': $('.p_name').val()
	}, function(response){
		var data = $.parseJSON(response);
		if(data.error != '')return notify('error', data.error);
		else{
			notify('success', lang.task_q);
			$('.v-pending, .v-segments').html('');
			$('.pending_submit').hide();
		}
	});
	
});

function create_v_segment_action_btns()
{
	if($('.v-segment-control').length <= 0)
	$('.v-segments').append('<tr class="v-segment-control"><td><button class="btn btn-sm btn-info join_seg">'+lang.jss+'</button>&nbsp;&nbsp;<button class="btn btn-sm btn-primary cut_seg">'+lang.css+'</button>&nbsp;&nbsp;<button class="btn btn-sm btn-success" onclick="$(\'.segment_ch\').prop(\'checked\', true)">'+lang.chkall+'</button>&nbsp;&nbsp;<button class="btn btn-sm btn-danger" onclick="$(\'.segment_ch\').prop(\'checked\', false)">'+lang.unchkall+'</button></td></tr>');
}

function jp_pause_seek()
{
	setTimeout(function(){jp_instance.pause();}, 100);
}

/* canvas-toBlob.js
 * A canvas.toBlob() implementation.
 * 2013-12-27
 * 
 * By Eli Grey, http://eligrey.com and Devin Samarin, https://github.com/eboyjr
 * License: MIT
 *   See https://github.com/eligrey/canvas-toBlob.js/blob/master/LICENSE.md
 */

/*global self */
/*jslint bitwise: true, regexp: true, confusion: true, es5: true, vars: true, white: true,
  plusplus: true */

/*! @source http://purl.eligrey.com/github/canvas-toBlob.js/blob/master/canvas-toBlob.js */

(function(view) {
"use strict";
var
	  Uint8Array = view.Uint8Array
	, HTMLCanvasElement = view.HTMLCanvasElement
	, canvas_proto = HTMLCanvasElement && HTMLCanvasElement.prototype
	, is_base64_regex = /\s*;\s*base64\s*(?:;|$)/i
	, to_data_url = "toDataURL"
	, base64_ranks
	, decode_base64 = function(base64) {
		var
			  len = base64.length
			, buffer = new Uint8Array(len / 4 * 3 | 0)
			, i = 0
			, outptr = 0
			, last = [0, 0]
			, state = 0
			, save = 0
			, rank
			, code
			, undef
		;
		while (len--) {
			code = base64.charCodeAt(i++);
			rank = base64_ranks[code-43];
			if (rank !== 255 && rank !== undef) {
				last[1] = last[0];
				last[0] = code;
				save = (save << 6) | rank;
				state++;
				if (state === 4) {
					buffer[outptr++] = save >>> 16;
					if (last[1] !== 61 /* padding character */) {
						buffer[outptr++] = save >>> 8;
					}
					if (last[0] !== 61 /* padding character */) {
						buffer[outptr++] = save;
					}
					state = 0;
				}
			}
		}
		// 2/3 chance there's going to be some null bytes at the end, but that
		// doesn't really matter with most image formats.
		// If it somehow matters for you, truncate the buffer up outptr.
		return buffer;
	}
;
if (Uint8Array) {
	base64_ranks = new Uint8Array([
		  62, -1, -1, -1, 63, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1
		, -1, -1,  0, -1, -1, -1,  0,  1,  2,  3,  4,  5,  6,  7,  8,  9
		, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
		, -1, -1, -1, -1, -1, -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35
		, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51
	]);
}
if (HTMLCanvasElement && !canvas_proto.toBlob) {
	canvas_proto.toBlob = function(callback, type /*, ...args*/) {
		  if (!type) {
			type = "image/png";
		} if (this.mozGetAsFile) {
			callback(this.mozGetAsFile("canvas", type));
			return;
		} if (this.msToBlob && /^\s*image\/png\s*(?:$|;)/i.test(type)) {
			callback(this.msToBlob());
			return;
		}

		var
			  args = Array.prototype.slice.call(arguments, 1)
			, dataURI = this[to_data_url].apply(this, args)
			, header_end = dataURI.indexOf(",")
			, data = dataURI.substring(header_end + 1)
			, is_base64 = is_base64_regex.test(dataURI.substring(0, header_end))
			, blob
		;
		if (Blob.fake) {
			// no reason to decode a data: URI that's just going to become a data URI again
			blob = new Blob
			if (is_base64) {
				blob.encoding = "base64";
			} else {
				blob.encoding = "URI";
			}
			blob.data = data;
			blob.size = data.length;
		} else if (Uint8Array) {
			if (is_base64) {
				blob = new Blob([decode_base64(data)], {type: type});
			} else {
				blob = new Blob([decodeURIComponent(data)], {type: type});
			}
		}
		callback(blob);
	};

	if (canvas_proto.toDataURLHD) {
		canvas_proto.toBlobHD = function() {
			to_data_url = "toDataURLHD";
			var blob = this.toBlob();
			to_data_url = "toDataURL";
			return blob;
		}
	} else {
		canvas_proto.toBlobHD = canvas_proto.toBlob;
	}
}
}(typeof self !== "undefined" && self || typeof window !== "undefined" && window || this.content || this));
