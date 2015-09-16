$(window).ready(function(){function t(t){try{var a=$.parseJSON(t);return a}catch(e){return!1}}$(".submit-btn, .sh-submit").click(function(t){t.preventDefault();var a,e=$("#mailmessage, #add-item-form, #help-form");a=validate(e),console.log(a),1==a&&e.submit()}),$(".add-btn").click(function(){$("#image-upload").click()}),$(".burger-btn").click(function(){$(".drop-down-menu").toggleClass("opened")}),$(function(){var a=$(".add-btn"),o=$("#photos_id").val(),i=$("#photo-content");o&&new AjaxUpload(a,{action:"http://"+window.location.host+"/upload.php",name:"uploadfile",data:{id:o,op:"upload"},onSubmit:function(t,a){},onComplete:function(a,o){var n=t(o);if(n){fckngcrap=!0;var s=0,c=0;$(".img").length>0&&($(".img").each(function(){$(this).attr("data")>s&&(s=$(this).attr("data"))}),c=s+1);var d="";d+="<span class='photo-min'>",d+="<span class='img-min img' data='"+c+'\' onclick="make_default($(this))" data-info="Кликните для выбора главной фотографии объявления">',d+="<img src='"+n.path+"' width='"+n.width+"' height='"+n.height+"'>",d+="</span>",d+="<a href='javascript:void(0);' data-id=\""+n.id+'">Удалить</a>',d+="</span>",i.append(d),$(".img").off("mouseover"),$(".img").off("mouseout"),$(".img").each(function(t){$(this).mouseover(function(){var a="right";void 0!=$(this).data("pos")&&(a=$(this).data("pos")),e=$(this),txt=e.data("info"),div({id:t,text:txt,position:a,type:"information",element:e})}).mouseout(function(){div({id:t,op:"destroy"})})})}else alert(o)}})}),$(document).on("click",".photo-min a",function(t){var a=$(t.target).closest(".photo-min"),e=a.find("a").data("id");$.ajax({type:"POST",url:"http://"+window.location.host+"/upload.php",scriptCharset:"utf-8",data:{id:e,op:"delete"},success:function(t){"ok"==t?a.remove():alert(t)}})}),$("#choose-region").find("select").on("change",function(){var a=$(this).find("option:selected").data("id");$.ajax({type:"GET",url:"http://"+window.location.hostname+"/ajax/info.php",scriptCharset:"utf-8",data:"&reg_id="+encodeURIComponent(a),success:function(a){if(arr=t(a),arr){var e="<div class='form-group-custom' id='city'>";e+="<h4>Город *</h4>",e+="<select name='city' data-validate=\"v_required\" class=\"city\">",e+="<option value='' disabled selected>Выбрать...</option>";for(var o in arr)e+="<option value='"+o+"'>"+arr[o]+"</option>";e+="</select>",e+="</div>",$("#city").remove(),$("#choose-region").after(e)}else alert("")}})}),$("#choose-category").find("select").on("change",function(){var a="",e=$(this).find("option:selected").data("id");$("#subcategory").remove(),$("#choose-category").after(a),$.ajax({type:"GET",url:"http://"+window.location.hostname+"/ajax/info.php",scriptCharset:"utf-8",data:"&cat_id="+encodeURIComponent(e),success:function(a){if(arr=t(a),arr){var e="<div id='subcategory' class='form-group-custom'>";e+="<h4>Подкатегория *</h4>",e+="<select class='subcategory' name='id_category' data-validate=\"v_required\">",e+="<option value='' disabled selected>Выбрать...</option>";for(var o in arr)e+="<option value='"+o+"'>"+arr[o]+"</option>";e+="</select></span></div>",$("#subcategory").remove(),$("#choose-category").after(e)}else alert("")}})})});
// Owl Carousel
$('#item-gallery').owlCarousel({

  navigation: true,
  pagination: false,
  singleItem: true
});

$(document).on('click', '.item-author-phone a', function(e)
{
	var el = $(this).parent();
	$.ajax({
		type: 'GET',
		url: '/ajax/info.php',
		scriptCharset: 'utf-8',
		data: {
			'op': 'phone',
			'board_id': el.data('id')
		},
		success: function(response)
		{
			el.html(response);
		}
	});
});

jQuery(document).ready(function(){
  jQuery('#service_button a').click(function(){
    return false;
  });

  jQuery('.smsbutt').hover(function(){
    var smsprice = jQuery('.prs2').text();
    jQuery('.price-hover').text(smsprice+' гривен');
    jQuery('.tip-oplati').text('через смс');
    jQuery('.o-p').css('display', 'block');
  });
  jQuery('.intButt').hover(function(){
    var prs1 = jQuery('.prs1').text();
    jQuery('.price-hover').text(prs1+' рублей');
    jQuery('.tip-oplati').text('через Яндекс деньги');
    jQuery('.o-p').css('display', 'block');
  });
  jQuery('.prw24butt').hover(function(){
    var prs3 = jQuery('.prs3').text();
    jQuery('.price-hover').text(prs3+' гривен');
    jQuery('.tip-oplati').text('через Приват банк');
    jQuery('.o-p').css('display', 'block');
  });

  jQuery('.smsbutt').click(function(){
    jQuery('.toggle1').css('display', 'block');
    jQuery('#service_button').css('display', 'none');
    return false;
  });
  jQuery('.intButt').click(function(){
    jQuery('.intclick').click();
    return false;
  });
  jQuery('.prw24butt').click(function(){
    jQuery('.privatForm').click();
    return false;
  });
  

function addParameter(param, value) {
  var url = window.location.href;
   var a = document.createElement('a'), regex = /(?:\?|&amp;|&)+([^=]+)(?:=([^&]*))*/g;
   var match, str = []; a.href = url; param = encodeURIComponent(param);
   while (match = regex.exec(a.search))
       if (param != match[1]) str.push(match[1]+(match[2]?"="+match[2]:""));
   str.push(param+(value?"="+ encodeURIComponent(value):""));
   a.search = str.join("&");
   return a.href;
}


  $('#sort').on('change', function()
  {
    var option = $(this).find('option:selected').val();
    location.href = addParameter('sort', option);
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
          });
          $('#successful_send').modal('show');
        }else alert(response);
      }
    });
    return false;
  });
});
