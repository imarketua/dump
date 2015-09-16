$(document).ready(function()
{
	setInfo();
});
function setInfo()
	{	
	
	
		text = {
		title:        "",
		name:         "Введите свое полное имя.<br /> Например: <strong>Павел</strong>",
		lastname:     "Введите свою фамилию.",
		email:        "Введите <strong>правильную</strong> электронную<br> почту.",
		password:     "Введите пароль.<br>Минимум - <strong>6</strong> символов.",
		password2:    "Повторите этот же пароль.",
		password3:    "Повторите этот пароль заного"
		}
		
		$('.img').each(function(i)
                {
                        $(this).mouseover( function() {
                                var position = 'right';
                                if($(this).data('pos') != undefined) position = $(this).data('pos');

                                e = $(this);
                                txt = e.data('info');
                                div({
                                id: i,
                                text: txt,
                                position: position,
                                type: 'information',
                                element: e
                                });
                        }).mouseout(function() {
                                div({
                                id: i,
                                op: 'destroy'
                                });
                        });
                })

		$('input[data-info], textarea[data-info]').each(function(i)
		{
			$(this).focus( function() {
				var position = 'right';
				if($(this).data('pos') != undefined) position = $(this).data('pos');
				
				e = $(this);
				txt = e.data('info');
				div({
				id: i,
				text: txt,
				position: position,
				type: 'information',
				element: e
				});			
			}).focusout(function() {
				div({
				id: i,
				op: 'destroy'
				});
			});
		})
	}
function div (param)
{
	if(param.op != 'destroy')
	{
		if($('#wnd_'+param.id).length == 0)
		{
			var top = param.element.offset().top;
			var left = param.element.offset().left + (e.width() + 18);
			var curs = "<div></div>";
			if(param.position == "bottom")
			{
				top = param.element.offset().top+param.element.height()+22;
				left = param.element.offset().left - 16;
				curs = "";
			}
			var a = $('<div id="wnd_'+param.id+'" class="info">'+curs+param.text+'</div>').appendTo('body');
				a.css
					({
						'position' : 'absolute',
						'top'      : top+"px",
						'left'     : left+15+"px"
					});
		}
	}
	else
	{
		if($('#wnd_'+param.id).length > 0)	$('#wnd_'+param.id).remove();
	}
}
function check()
	{
		return false;
	}




