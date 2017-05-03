function GenerateComments(queryData, anchorflag, anchorRootCmtId, anchorNewCmtId, anchorcmtId) {
    jQuery('textarea').each(function() {
        this.value = ''
    });
    $.ajax({
        type: "GET",
        url: "/Msg/Index/GetAllMsg/" + queryData,
        dataType: "json",
        beforeSend: function() {
            jQuery('.comments-wrap #comments .pagination').remove(); //去除分页
            jQuery('.comments-wrap #comments .commentlist').remove(); //直接将所有评论的大容器去掉
            jQuery('#loading-comments').slideDown(); //缓冲图标缓慢展示
        },
        dataType: "json",
        success: function(data) {
            if (data.success) {
                jQuery('#loading-comments').slideUp('fast'); //缓冲图标向上隐藏
                //评论内容淡入
                if (data.CoreData != "") {
                    jQuery('.comments-wrap #comments').append(data.CoreData); //after()/before()方法在被选元素后/前插入指定的内容。插入评论
                    var imgshow = $('#comments img');
                    //追加分页
                    if (data.PageNavStr != "") {
                        jQuery('.comments-wrap #comments').append(data.PageNavStr); //在<ol class=commentlist>后加入分页html
                        //$('.comments-wrap #comments .pagination').css("text-align", "right");
                        //为评论分页按钮 绑定 click事件
                        jQuery('.pagination ul a').click(function() {
                            var strHref = jQuery(this).attr("href");
                            var queryStr = strHref.substr(strHref.indexOf('p'));
                            jQuery('.comments-wrap #comments .pagination').remove(); //去除分页
                            jQuery('.comments-wrap #comments .commentlist').remove(); //直接将所有评论的大容器去掉
                            jQuery('#loading-comments').slideDown(); //缓冲图标缓慢展示
                            //$('html,body').animate({ scrollTop: jQuery('#article-list').offset().top-$('#zyn-header').outerHeight()-10}, 500); //65是"n条评论的高度"
                            jQuery('html,body').animate({
                                scrollTop: jQuery('#comments-title').offset().top - 80
                            }, 800); //65是"n条评论的高度"
                            GenerateComments('/' + queryStr);
                            return false; //不执行href
                        });
                    }
                }
                //加载完评论和分页后, 定位
                //ajax加载完页面数据后定位到相应评论锚点
                if (anchorflag == "1") {
                    if (anchorRootCmtId == "1") {
                        //定位到评论区
                        jQuery('html,body').animate({
                            scrollTop: $('#comment-' + anchorcmtId).offset().top - 110
                        }, 800);
                        jQuery('#comment-' + anchorcmtId).css('border', 'solid 2px #00e3e3');
                        jQuery('#second-comment-' + anchorNewCmtId).css('border', 'solid 2px #1abc9c');
                    } else if (anchorRootCmtId == "2") {
                        jQuery('html,body').animate({
                            scrollTop: $('#second-comment-' + anchorcmtId).offset().top - 110
                        }, 800);
                        jQuery('#second-comment-' + anchorcmtId).css('border', 'solid 2px #00e3e3');
                        jQuery('#second-comment-' + anchorNewCmtId).css('border', 'solid 2px #1abc9c');
                    } else {
                        return;
                    }
                }
            }
        }
    });
};

function checkName(uNa) {
    $.get('/Home/Public/chechUserName/uName/' + uNa, function(result) {
        if (result) {
            $html = '<label id="error1" for="comment" class="error">该用户名已被占用啦，换一个吧</label>';
            jQuery('#vname').after($html);
            $("#submit").removeAttr("disabled", true);
            return false;
        } else {
            $("#submit").removeAttr("disabled", true);
        }
    });
}

//type 是否为新留言？ sort 回复的留言是一级留言还是二级
function Comment(type, sort) {
    //alert(type);
    var VId = jQuery('#vid').val(); //父Id 确定是否为1级评论
    var VName = jQuery('#vname').val(); //昵称
    var VEmail = jQuery('#vemail').val(); //邮箱
    var CmtText = jQuery('#comment').val(); //评论主体

    if (VName == "") {
        $html = '<label id="error1" for="comment" class="error">昵称不能为空</label>';
        jQuery('#vname').after($html);
        return false;
    }
    if (VEmail == "") {
        $html = '<label id="error2" for="comment" class="error">邮箱不能为空</label>';
        jQuery('#vemail').after($html);
        return false;
    }
    if (CmtText == "") {
        $html = '<label id="error3" for="comment" class="error">什么都没写?</label>';
        jQuery('#comment').after($html);
        return false;
    }

    if (jQuery('#commentform .error').length > 0) {
        alert('请处理好输入格式');
        return false;
    }

    //评论后台交互，若评论成功将内容附加至对应部分
    jQuery.ajax({
        type: "POST",
        url: "/Msg/Index/addMsg",
        data: {
            'VId': VId,
            'VName': VName,
            'VEmail': VEmail,
            'CmtText': CmtText,
            'type': type
        },
        dataType: "json",
        beforeSend: function() {
            jQuery('#loading').slideDown(); //缓冲图标缓慢展示
        },
        dataType: "json",
        success: function(data) {
            if (data.success) {
                jQuery('#loading').slideUp();
                jQuery('#SessionName').val(VName);
                jQuery('#SessionEmail').val(VEmail);
                if (type == 0) {
                    jQuery('#respond').before(data.CommData);
                } else {
                    if (sort == 0) {
                        jQuery('#div-comment-' + VId).after(data.CommData);
                    } else {
                        jQuery('#sdiv-comment-' + VId).after(data.CommData);
                    }
                    jQuery('#closeCom').click(); //触发取消回复按钮
                }
                jQuery('#vname').val(VName); //将昵称与邮箱填入文本框并使其不可用
                jQuery('#vemail').val(VEmail);
                jQuery('#comment').val("");
                jQuery('#vname').attr("disabled", "true");
                jQuery('#vemail').attr("disabled", "true");
            } else {
                alert(data.msg);
                return false;
            }
        }
    });
}

function addComment(id, type, sort) {
    var uName = jQuery("#SessionName").val();
    var uEmail = jQuery("#SessionEmail").val();
    // var uName="{$Think.session.login.uname}";
    // var uEmail="{$Think.session.login.email}";
    jQuery("#respond").remove();
    var html_c = '';
    html_c += '<div id="respond" class="comment-respond">';
    html_c += '<h3 id="reply-title" class="comment-reply-title">';
    html_c += '<i class="fa fa-pencil"></i>欢迎留言';
    html_c += '<small><a rel="nofollow" id="closeCom" href="javascript:;">取消回复</a></small>';
    html_c += '</h3>';
    html_c += '<from novalidate="novalidate" action="#" method="post" id="commentform" class="comment-form">';
    html_c += '<input id="vid" name="fId" value=' + id + ' type="hidden">';
    html_c += '<div class="row">';
    html_c += '<div class="col-md-6">';
    html_c += '<div class="form-group">';
    html_c += '<div class="input-group">';
    html_c += '<div class="input-group-addon">';
    html_c += '<span class="glyphicon glyphicon-user"></span>';
    html_c += '</div>';
    if (uName !== "") {
        html_c += '<input type="text" size="20" id="vname" name="VName" class="form-control" placeholder="起个昵称吧" value="' + uName + '" disabled="true" required="">';
    } else {
        html_c += '<input type="text" size="20" id="vname" name="VName" class="form-control" maxlength="12" placeholder="起个昵称吧" value="" required="">';
    }
    html_c += '</div>';
    html_c += '</div>';
    html_c += '</div>';
    html_c += '<div class="col-md-6">';
    html_c += '<div class="form-group">';
    html_c += '<div class="input-group">';
    html_c += '<div class="input-group-addon">';
    html_c += '<span class="glyphicon glyphicon-envelope">';
    html_c += '</span>';
    html_c += '</div>';
    if (uEmail !== "") {
        html_c += '<input type="text" size="20" id="vemail" name="VEmail" class="form-control" placeholder="请输入您的邮箱" value="' + uEmail + '" disabled="true" required="">';
    } else {
        html_c += '<input type="text" size="20" id="vemail" name="VEmail" class="form-control" placeholder="请输入您的邮箱" value="" required="">';
    }
    html_c += '</div>';
    html_c += '</div>';
    html_c += '</div>';
    html_c += '</div>';
    html_c += '<textarea id="comment" class="form-control" placeholder="赶快发表你的见解吧！" name="CmtText" cols="30" rows="3" required=""></textarea>';
    html_c += '<div id="loading" style="display:none">';
    html_c += '<img src="/Public/images/ico_loading2.gif" data-bd-imgshare-binded="1">';
    html_c += '<span>正在提交，请稍后......</span>';
    html_c += '</div>';
    html_c += '<p class="form-submit">';
    html_c += '<input name="submit" class="hiddeninput" id="submit" value="发表评论" type="button" onclick="Comment(' + type + ',' + sort + ')">';
    html_c += '</p>';
    html_c += '</from>';
    html_c += '</div>';
    if (sort == 0) {
        jQuery('#div-comment-' + id).after(html_c);
    } else {
        jQuery('#sdiv-comment-' + id).after(html_c);
    }
    jQuery('#closeCom').one('click', function() {
        resetComm(html_c);
    });
    if (uName != "") {
        jQuery('#comment').focus();
    } else {
        jQuery('#vname').focus();
    }
}

function resetComm(html) {
    jQuery("#respond").remove();
    jQuery("#addComm").before(html);
    jQuery("#closeCom").remove();
    jQuery("#vid").val("");
    jQuery('#submit').attr('onclick', '');
    //setTimeout(function () {
    //$('#submit').attr('onclick', '').bind('click', function () { Comment(0); });
    //}, 1);
    //jQuery("#submit").unbind("click"); //移除所有事件 
    jQuery("#submit").bind('click', function() {
        Comment(0, 0)
    }); //为按钮绑定提交函数
}