function loadPicList(){
	jQuery.ajax({
        type:"GET",
        url:"/Home/ArtList/GetTopPic/",
        dataType:"json",
		success:function(data){
		if(data.success){
			if(data.PicData!==""){
				var _html='<div id="ad-carousel" class="carousel slide" data-ride="carousel">';
				var _html1='<ol class="carousel-indicators">';
				var _html2='<div class="carousel-inner">';
				var Piclen=data.PicData.length;
				for(var h=0; h < Piclen; h++){
					var ph=h+1;
					if(h==0){
						_html1+='<li data-target="#ad-carousel" data-slide-to="'+h+'" class="active"></li>';
						_html2+='<div class="item active">';
					}else{
						_html1+='<li data-target="#ad-carousel" data-slide-to="'+h+'"></li>';
						_html2+='<div class="item">';
					}
						_html2+='<img src="/Uploads/Pic/'+data.PicData[h].picture+'" alt="'+ph+' slide" class="img-responsive">';
						_html2+='<div class="container">';
						_html2+='<div class="carousel-caption">';
						_html2+='<h1>'+data.PicData[h].title+'</h1>';
						_html2+='<p>'+data.PicData[h].moreinfo+'</p>';
						_html2+='</div>';
						_html2+='</div>';
						_html2+='</div>';
				}
				_html1+='</ol>';
				_html2+='</div>';
				_html+=_html1;
				_html+=_html2;
				_html+='<a class="left carousel-control" href="#ad-carousel" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>';
				_html+='<a class="right carousel-control" href="#ad-carousel" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>';
				_html+='</div>';
				jQuery('#PicList').append(_html);
			}
		}else{jQuery('#PicList').append('<p>哎呀，网络有点小问题哦</p>')};
	}
	});
}
		
function loadArtList(queryParameter) {
    jQuery.ajax({
        type:"GET",
        url:"/Home/ArtList/AList/"+queryParameter,
        dataType:"json",
        beforeSend:function(){
            jQuery('#loading').css("text-align","center");
            jQuery('#loading').slideDown(); //缓冲图标缓慢展示
        },
        success:function(data){
            if (data.success) {
                jQuery('#loading').slideUp('fast');  //缓冲图标向上隐藏
                        //jQuery('#loading').remove();
                        //1.0 组装首页博文列表
                var len = data.CoreData.length;
                for (var i = 0; i < len; i++) {
					var strArticle = ' ';
					if(i==0){
						strArticle = '<article class="excerpt excerpt-first">';
					}else if(i==len-1){
						strArticle = '<article class="excerpt excerpt-end">';
					}else{
						strArticle = '<article class="excerpt">';
					}
                 	//博文标题
                    strArticle += '<header>';
                    strArticle += '<a class="cat" href="#">' + data.CoreData[i].cateName + '<i></i></a>';
                    strArticle += '<h2><a class="gotoArchive" href=\"/Home/ArtList/ArtInfo/id/' + data.CoreData[i].id + '\" title=\"' + data.CoreData[i].title + '\">' + data.CoreData[i].title + '</a></h2>';
                    strArticle += '</header>';
                    //-标题下的tips小信息
                    strArticle += '<p class="meta">';
                    strArticle += '<span class="posttime">' + data.CoreData[i].pubtime + '</span>';
                    strArticle += '<span class="viewnum">浏览(<a href="javascript:;">' + data.CoreData[i].liulan_num + '</a>)</span>';
                    strArticle += '<span class="commentnum">评论(<a href="/Home/ArtList/ArtInfo/id/'+data.CoreData[i].id+'#comments-title">' + data.CoreData[i].pinlunNum + '</a>)</span>';
                    strArticle += '<span class="praisebtn">  <img width="15" height="15" class="praisenum" src="/Public/images/ico_praise.png" />(<a data-id=\"'+data.CoreData[i].Id+'\" praise-flag="0"  href="/">' + data.CoreData[i].zhan_num + '</a>)</span>';
                    strArticle += '</p>';
                    //正文:默认显示90个汉字或180个英文(后台已处理)
                    strArticle += '<p class="note">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + data.CoreData[i].moreinfo + '....</p>';
                    //关键词：<!--最多显示3个-->
                    strArticle += '<footer class="entry-footer">';
                    strArticle += '<span itemprop="keywords" class="tags-links">';//<!--itemprop属性就是方便机器识别的特定的标记-->
                    //最开始把for里面的变量也写为了i所以浏览器停止运行了；使用前先判断一下keywords是否为空
                    var keywords = data.CoreData[i].keywords;
					var keywordslen=keywords.length;
					if (keywords && keywordslen != 0) {
                        for (var j = 0; j < keywordslen; j++) {
                        strArticle += '<a rel="tag" href="/">' + keywords[j] + '</a>';
                        }
                    }
                    strArticle += '</span>';
                    strArticle += '<a class="more-link gotoArchive" rel="nofollow" href=\"/Home/ArtList/ArtInfo/id/' + data.CoreData[i].id + '\">继续阅读 »</a>';
                    strArticle += '</footer>';
                    strArticle += '</article>';
                    //附加
                    jQuery("#article-list").append(strArticle);
                }
                //2.1 组装分页插件
                jQuery('.pagination ul').html(data.PageNavStr);
				jQuery('.pagination ul a').click(function () {
					var strHref = jQuery(this).attr("href");
					var queryStr = strHref.substr(strHref.indexOf('p'));
					jQuery("#article-list article").remove();
					jQuery(".pagination ul li").remove(); //将li元素全清空
					$('html,body').animate({ scrollTop: jQuery('#article-list').offset().top-$('#zyn-header').outerHeight()-10}, 500); //65是"n条评论的高度"
					loadArtList(queryStr);
					return false;  //不执行href
				});	
                //2.2 为分页按钮注册点击事件
				jQuery('.author-avatar img').each(function () {
                    var url = jQuery(this).attr('data-url');
                    jQuery(this).attr('src', url);
                });
			}else{alert("发生了一些未知错误");}
		}
	});
}