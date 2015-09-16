function validate(form)
	{	
		text = {
		v_required:    "Заполните поле!",
		v_max_60:      "Максимум - <strong>60</strong> симв.",
		v_max_30:      "Максимум - <strong>30</strong> симв.",
		v_email:       "Введите <strong>правильный</strong><br />e-mail!",
		v_min_5:       "Минимум - <strong>5</strong> симв.",
		v_min_15:      "Минимум - <strong>15</strong> симв.",
		v_max_1500:    "Максимум - <strong>1500</strong> симв.",
		v_num_only:    "Допустимы только цифры!",
		v_password:    "Пароли не совпадают",
		v_phone:       "Допустимые символы: <br /><strong>все цифры, знак + и запятая</strong>"
		}
		
		errors = 0;
		first = 0;
		
		form.find('input[data-validate], textarea[data-validate], select[data-validate]').each( function()
			{	
				e = $(this);
				var c = check(e);
				var el = "";
				
				if(e.get(0).tagName == "SELECT") el = e.parent();
				else el = e;

				if(c != "ok") {
					if(errors == 0) first = el.offset().top-20;
					errors++;
					el.parent().find('.additem-errors').remove();
					el.after("<div class='additem-errors'>"+text[c]+"</div>");
				}else{
					//el.next().remove();
				}
			});
			
		if(errors > 0)
		{
			$('html, body').animate({'scrollTop' :first+"px"});
			return false;
		}else
		return true;
	}
	
function check(f)
{
		var inf = f.data('validate').split(',');
		for(var i in inf)
		{
			inf[i] = $.trim(inf[i]);
		}
		if(has(inf, 'v_required')) {
			if(f.val() == "" || f.val() == null)
				return 'v_required';
		}
		if(has(inf, 'v_min_5')) {
			if(f.val().length < 5)
				return 'v_min_5';
		}
		if(has(inf, 'v_max_60')) {
			if(f.val().length > 60)
				return 'v_max_60';
		}
		if(has(inf, 'v_max_30')) {
			if(f.val().length > 30)
				return 'v_max_30';
		}
		if(has(inf, 'v_min_15')) {
			if(f.val().length < 15)
				return 'v_min_15';
		}
		if(has(inf, 'v_max_1500')) {
			if(f.val().length > 1500)
				return 'v_max_1500';
		}
		if(has(inf, 'v_num_only')) {
			if (f.val().match(/[^0-9]/g)) 
				return 'v_num_only';
		}
		if(has(inf, 'v_email')) {
		var valid = /^[a-z0-9._-]+@[a-z0-9-]+\.([a-z]{1,6}\.)?[a-z]{2,6}$/i;
			if (!f.val().match(valid)) 
				return 'v_email';
		}
		if(has(inf, 'v_password')) {
			var count = 0;
			var pass = new Array();
			$('.v_password').each( function ()
				{
					count++;
					pass[count] = $(this).val();
				});
			for(i = 1; i < count; i++)
				if(pass[i+1] != undefined) 
					if(pass[i] != pass[i+1])
						return 'v_password';
					
		}
		if(has(inf, 'v_phone'))
		{
			var valid = /[^\d,+ ]/g;
			if (f.val().match(valid))
				return 'v_phone';
		}
		
		return "ok";
}
function has(inf, val)
{
	var a = $.inArray(val, inf);
	if(a == -1) return false;
	else if (a > -1) return true;
}




