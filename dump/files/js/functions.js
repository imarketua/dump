function getUrl(block, action)
	{	
		if(action == "" || action == undefined) action = $("#search-form").attr('action');
		var start_url = action;
		var url = "";
		
		$(block).find('input, select').each( function ()
						{
							var value = $(this).val();
							if($(this).attr('type') == "checkbox") 
							if($(this).is(':checked')) 
							value = 'on';
							else value = '';
							
							if(value != "" && $(this).attr('name') != undefined)
							url += $(this).attr('name')+"="+value+"&";
						});
							if(url != "") {
							url = "?"+url;
							url = url.substring(0, url.length - 1);
							}	
		var u = start_url+url;
		if(u.indexOf('?top_all') >= 3){ 
			var t = u.replace('?top_all?','?top_all&');
			return t;
		}else{
	                if($("#search-form").attr('action').indexOf('?top_all') >= 3){
	                        t = u.replace('?','?top_all&');
				return t;
	                }
			return u;
		}
	}
function getCities(t)
	{
		if(t.data('category') == undefined) category = "";
		else category = t.data('category');
		
		$.ajax({
			type: "GET",
			url: 'http://'+window.location.hostname+'/ajax/info.php',
			scriptCharset: 'utf-8',
			data: {'q' : t.val(), 'cat_href':category},
			success: function(response)
			{
				//alert(t.val());
				if($(".find-menu").length == 0) {
				$("<div class='find-menu'></div>").appendTo(t.parent());
				}
				t.parent().find('.find-menu').html(decodeURIComponent(response));
			}
		});
	}
function getPlaceholder(el, i)
{	
	if(el.data('placeholder') != undefined) {
		if(el.attr('id') == undefined) el.attr("id", "plc"+i);
		id = el.attr('id');
		
		if(!el.parent().hasClass('parent-placeholder'))
		el.wrap('<div class="relative inline parent-placeholder" />')
		.parent().
		prepend('<label class="placeholder" for="'+id+'">'+el.data("placeholder")+'</label>');
		if(el.val() != "") el.prev().css('display', 'none');
		else el.prev().css('display', 'block')
		$('.placeholder').on('click', function()
		{
			$(this).next().focus();
		}).next().on('focus', function()
		{
			$(this).prev().css('display', 'none');
		}).on('blur', function()
		{
			if($(this).val() == "")
			$(this).prev().css('display', 'block');
		});
	}
}
function cJSON(res)
{
	try
	{
	   var json = $.parseJSON(res);
	   return json;
	}
	catch(e)
	{
		  return false;
	}
}
function newWindow(obj)
{
	loading.start();
	var width = "auto", height = "auto";
	if($('.window').length > 0) {
		obj.width = $('.window').width();
		obj.height = $('.window').height();
	}
	if(obj != undefined){
		if(obj.width != undefined)
			width = obj.width;
		if(obj.height != undefined)
			height = obj.height;
		if(obj.action != undefined)
		$.ajax({
			type: "GET",
			url: 'ajax/'+obj.action,
			scriptCharset: 'utf-8',
			data: obj.data,
			success: function(response)
			{
				show({width: width, height: height, html:response});
				loading.end();
			}
		});
		else if(obj.html != undefined) {
			show({width: width, height: height, html:obj.html});
			loading.end();
		}
	}
	return false;
}
function close_wnd()
{
	$('.window').animate({ opacity : 0 }, 150,
	function() {
		$('.dark').animate({ opacity : 0 }, 250, function()
			{
				$(this).remove();
			});
		$(this).remove();
	});
	return false;
}
function show(obj)
{
	var width, height;
	if(obj != undefined){
		if(obj.width != undefined)
			width = obj.width;
		if(obj.height != undefined)
			height = obj.height;
	}
	if($('.window').length==0)
	{
		var left = $(document).width();
		var top = $(window).height();
		
		
		if($('.dark').length==0)
			$('<div class="dark"></div>').appendTo('body').css('opacity', 0).animate({'opacity': 0.5});
			
		left = (left-width) / 2;
		$('<div class="window"><a class="block close" onClick="return close_wnd();" href="javascript:void(0);"></a><div class="window-content p20px"></div></div>')
			.appendTo('body')
			.css({
				'left'   : left+'px',
				'top'    : '110px',
				'width'  : width,
				'height' : height
				});
	}
	if(obj.html != undefined)
	{
		$('.window').find('.window-content').html(obj.html);
	}
}
function getInfoWnd(obj)
{
	var width = obj.width;
	var height = obj.height;
	if(obj.type == "static")
	height = height + 30;
	
	if(obj.href != undefined)
	var href = obj.href;
	else
	var href = "javascript:void(0)";
	
	var left = $(document).width();
	var top = $(window).height();
		
	var header = obj.header;
	var desc = obj.desc;
						
	left=(left-width)/2;
	top=(top-height)/2-50;
			
	var html = "";
		
	if($('.info-wnd-all').length == 0) {
		html += "<div class='info-wnd-all' style=\"top:"+top+"px; left:"+left+"px; width:"+width+"px; height:"+height+"px;\">";
		html += "<div class='info-wnd-dark'></div>";
		html += "<div class='info-wnd-content'>";
		html += "<strong><u>"+header+"</u></strong><br /><br />";
		html += desc;
		if(obj.type == "static") {
			html += "<div class='info-wnd-ok-place padding-left-10px'>";
			html += "<a href='"+href+"'>";
			html += "<div class='info-wnd-ok' align='center'>ОК</div>";
			html += "</a>";
			html += "</div>";
		}
		html += "</div>";
		html += "</div>";
		$(html).appendTo('body').css('opacity', 0).animate({opacity: 1});
				
		var el = $('.info-wnd-all');
		if(obj.type != "static")
			setTimeout(function() {
				el.animate({'top' : '0px', 'opacity':0},120, function() {$(this).remove();});
			}, 2000);	
		else
		$(document).on('click', function(){
			if(obj.href != undefined)
				location.href = href;
			el.animate({'top' : '0px', 'opacity':0},120, function() {$(this).remove();})
		});
	}
}
var loading = {
	width: 120,
	height: 35,
	start : function()
	{
		var left = window.screen.width;
		var top = window.screen.height;
		
		left = (left - this.width) / 2;
		top  = (top - this.height) / 2-250;
		$('.load').show().css({
			top: top,
			left: left
		});
	},
	end: function()
	{
		if($('.load').length > 0) {
			$('.load').hide();
		}
	},
	go : function()
	{
		var html = "";
		var desc = "<img src='img/loading.gif' width='15' height='15'/><div style='position: relative; top: -3px; left: 0px; margin-left: 10px;' class='inline'>Загрузка</div>";
		if($('.load').length == 0) {
			html += "<div class='load' style=\"width:"+this.width+"px; height:"+this.height+"px;\">";
			html += "<div class='info-wnd-dark'></div>";
			html += "<div class='info-wnd-content'>";
			html += desc;
			html += "</div>";
			html += "</div>";
			$(html).appendTo('body');
			$('.load').hide();
		}
	}
}
function initialize(address) {
	var map;
	var service;
	var infowindow;
	var geocoder;
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(-34.397, 150.644);
    var mapOptions = {
      zoom: 15,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("map"), mapOptions);
	    geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        map.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
            map: map,
            position: results[0].geometry.location,
			draggable: true
        });
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
}
