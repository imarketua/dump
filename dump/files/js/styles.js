var styles = {
	'.button': { 'target' : '.submit', 'mouseover' : 'b-hover', 'mousedown' : 'b-active' },
	'#s-button': { 'target' : '.search-but', 'mouseover' : 's-hover', 'mousedown' : 's-active' },
	'.post' : {'mouseover' : 'post-hover', 'mousedown' : 'post-active' }
}
var def = {
	'mouseover'  : 'mouseout',
	'mouseenter' : 'mouseleave',
	'mousedown'  : 'mouseup',
	'focus'      : 'blur'
}
var func = {
	'add_class' : function(el, cl) {
		el.addClass(cl);
	},
	'remove_class' : function(el, cl) {
		el.removeClass(cl);
	}
}

for(var i in styles)
for (var s in styles[i]) if(s != 'target') {
	addEvent(s, i, styles[i]['target']); 
	if(def[s] != undefined) 
	addEvent(def[s], i, styles[i]['target'], 'remove_class', styles[i][s]);
}

function addEvent(s, i, t, f, style)
{
	style = style || styles[i][s];
	f = f || 'add_class';

	var el = $(document);
	var id = "";
	
	if(styles[i].target != undefined) id = styles[i].target; 
	else id = i;
	
	if(s == 'mouseup')
	i = 'html, '+i;
	
	el.on(s, i, function(e) { 
		element = $(e.target).closest(i);
		if(t != undefined) element = element.find(id);
		func[f](element, style);
	});
}
