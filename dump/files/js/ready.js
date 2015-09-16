$(document).ready(function()
{
	$('#top_all').click(function(){
//alert('work');
		var st = 'search/?top_all';
		var qu = $('.q').val();
		if(qu.length > 0) st = st + '&q='+qu;
		location.href = st;		
	});
	$('#search-form').on('submit', function()
	{
		location.href = getUrl("#search-form, .filters");
		return false;
	}).find('input[type=checkbox]').on('change', function()
	{
		location.href = getUrl("#search-form, .filters");
	});
	$('#search-form').find('#search-category').on('change', function()
	{
		action = $(this).find('option:selected').data('href');
		location.href = getUrl("#search-form, .filters", action);
	});
	$('#sort').on('change', function()
	{
		location.href = getUrl("#search-form, .filters");
	});
	$('input[data-placeholder], textarea[data-placeholder]').each(function(i)
	{
		getPlaceholder($(this), i);
	});
	$('#mailmessage').on('submit', function()
	{
		var $this = $(this);
		if(!validate($this)) return false;
		$.ajax({
			type: "GET",
			url: 'http://'+window.location.hostname+'/ajax/info.php',
			scriptCharset: 'utf-8',
			data: $this.serialize()+"&op=mailmessage&board_id="+$this.data('board-id'),
			success: function(response)
			{
				if(response == "ok") {
					$this.find('input, textarea').each(function(i)
					{
						$(this).val("");
						getPlaceholder($(this), i);
					});
					getInfoWnd({
						width: 400,
						height: 100,
						header: 'Сообщение успешно отправлено',
						desc: 'Сообщение успешно отправлено автору.<br />Ожидайте ответа на e-mail.'
					});
				}else alert(response);
			}
		});
		return false;
	});
	$("#showAddress").on('click', function()
	{
		var address = $('#board-address').html();
		newWindow({
			html: "<div id='map'></div>",
			width: 750,
			height: 500
		});
		initialize(address);
	});
	loading.go();
});
$(document).on('focus', '.find-region-input', function(e)
{
	$(e.target).addClass("brbottom-none");
	getCities($(e.target));
}).on('blur', '.find-region-input', function(e)
{
	if($(e.target).parent().find('.find-menu a:hover').length == 0) {
	$(e.target).parent().find('.find-menu').remove();
	$(e.target).removeClass("brbottom-none");
	}
}).on('keyup', '.find-region-input', function(e)
{
	getCities($(e.target));
});
$(document).on('click', '.dark', function(e)
{
	close_wnd();
});
$(document).on('click', '.choose-region-from-list', function(e)
{
	newWindow({
		'width': 740,
		'action' : 'info.php', 
		data: {
			'op' : 'regions',
			'cat_href' : $(e.target).closest('.choose-region-from-list').data('category')
		}
	});
});
$(document).on('click', '.window a', function(e){
	var href = $(e.target).closest('a').attr('href');
	if(href.charAt(0) == "#") {
		var cat = $(e.target).closest('a').data('category'); 
		newWindow({action:href.substring(1, href.length), data: {'cat_href' : cat}});
		return false;
	}else if($(e.target).hasClass('ajax-region'))
	{
		close_wnd();
		loading.start();
		location.href = getUrl("#search-form, .filters", href);
		return false;
	}
});
$(document).on('click', '.photo-gallery', function(e)
{
	var main = $('.photo').find('#main-photo');
	var reg = /\.([^\.]+)$/;
	var match = reg.exec($(this).attr('href'));
	$('#fullp').attr('href', $(this).attr('href').replace(match[1],'full' + match[0]));
	var el = $(e.target);
	$('.photo-gallery img').each(function()
	{
		if($(this).hasClass('op1'))	$(this).removeClass('op1');
	});
	el.closest('img').addClass('op1');
	main.width(el.data('width')).height(el.data('height')).attr('src', el.closest('img').attr('src'));
	return false;
});
$(document).on('click', '.phone-content a', function(e)
{
	loading.start();
	var el = $(e.target).parent();
	$.ajax({
		type: 'GET',
		url: 'ajax/info.php',
		scriptCharset: 'utf-8',
		data: {
			'op': 'phone',
			'board_id': el.data('id')
		},
		success: function(response)
		{
			loading.end();
			el.html(response);
		}
	});
});
