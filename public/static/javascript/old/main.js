$(function(){
	$('.panel-toggle').data('show',true);
	$('#zztj').css('display','none'); 
	$('.panel-toggle').click(function(){
		var t=$(this).parents('div.panel').children('ul');
		if($(this).data('show')){
			$(this).removeClass('glyphicon glyphicon-upload').addClass('glyphicon glyphicon-download');
			$(this).parents('div.panel').addClass('toggle');
			t.slideUp("normal");
			$(this).data('show',false);
		}else{
			$(this).removeClass('glyphicon glyphicon-download').addClass('glyphicon glyphicon-upload');
			$(this).parents('div.panel').removeClass('toggle');
			t.slideDown("normal");
			$(this).data('show',true);
		}
	});
	
	$('.panel-remove').click(function(){
		$(this).parents('.panel').toggle(300);
	});
});
function changeArt(t){
	if(t==1){
		if($('#zztj').is(":hidden")){
			return false;
		}else{
			$('#zztj').toggle();
			$('#zxwz').toggle(300);
		}
	}else{
		if($('#zztj').is(":hidden")){
			$('#zxwz').toggle();
			$('#zztj').toggle(300);
		}else{
			return false;
		}
	}
}
$(function(){
		var url = "http://sapi.sina.cn/ls/allasync?ver=3";
		$.ajax({
			url: url,
			type: "get",
			timeout: 60000,
			dataType: "jsonp",
			success: function (record, type) {
			var d = eval(record);
			var img = d.retData.weather.icon_v3; //图标 icon icon_v3
			var city = d.retData.weather.city;
			var up = d.retData.weather.up;
			var down = d.retData.weather.down;
			var e = jQuery("#weather");
			if (img){
				e.empty();
				e.append("<div id='wicon' class='pull-right' style='margin-right:5px'><img src='" + img + "' width=20 height=20 />&nbsp;" + city + "&nbsp; " + down + "&nbsp;~&nbsp;" + up + "℃</div>");
			}
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
			}
		});
});