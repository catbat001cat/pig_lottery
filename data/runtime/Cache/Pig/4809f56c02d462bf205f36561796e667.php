<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="user-scalable=no, width=320">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="x-rim-auto-match" content="none">
    <title>我</title>
    <link rel="stylesheet" href="/qqonline_ex/themes/simplebootx_pig_fake/Public/cssdb/index.css" />
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.min.js" type="application/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.cookie.js" type="application/javascript"></script>
</head>
<body align="center">
    <div class="all" style="margin-bottom: 0px;">
        <div style="width: 299px; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 30px;
            margin-left: 10px; background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
            height: 50px; border-radius: 4px;" id="mingxi">
            <div style="float: left; margin-left: 10px; font-size: 14px; color: black; line-height: 46px;
                margin-top: 8px;">
                <div style="float: left">
                    <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/1-5.png" width="30" height="30" /></div>
                <div style="float: left; margin-top: -7px; margin-left: 10px;">
                    各项明细</div>
            </div>
            <div style="float: right; margin-top: 16px; margin-right: 10px; color: #A8A8A8">
                <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/icon.png" height="12" width="7" style="margin-top: 1px;" /></div>
            <div style="float: right; font-size: 16px; line-height: 48px; margin-right: 10px;
                color: #F36F6A">
            </div>
        </div>
         <div style="width: 299px; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 10px;
            margin-left: 10px; background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
            height: 50px; border-radius: 4px;" id="wenti">
            <div style="float: left; margin-left: 10px; font-size: 14px; color: black; line-height: 46px;
                margin-top: 8px;">
                <div style="float: left">
                    <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/6-1.png" width="30" height="30" /></div>
                <div style="float: left; margin-top: -7px; margin-left: 10px;">
                    常见问题</div>
            </div>
            <div style="float: right; margin-top: 16px; margin-right: 10px; color: #A8A8A8">
                <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/icon.png" height="12" width="7" style="margin-top: 1px;" /></div>
            <div style="float: right; font-size: 16px; line-height: 48px; margin-right: 10px;
                color: #F36F6A">
            </div>
        </div>
          <div style="width: 299px; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 20px;
            margin-left: 10px; background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
            height: 50px; border-radius: 4px;" id="signed">
            <div style="float: left; margin-left: 10px; font-size: 14px; color: black; line-height: 46px;
                margin-top: 8px;">
                <div style="float: left">
                    <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/signed.png" width="30" height="30" /></div>
                <div style="float: left; margin-top: -7px; margin-left: 10px;">
                    每日签到</div>
            </div>
            <div style="float: right; margin-top: 16px; margin-right: 10px; color: #A8A8A8">
                <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/icon.png" height="12" width="7" style="margin-top: 1px;" /></div>
            <div style="float: right; font-size: 16px; line-height: 48px; margin-right: 10px;
                color: #F36F6A">
            </div>
        </div>

        <div style="width: 299px; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 20px;
            margin-left: 10px; background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
            height: 50px; border-radius: 4px;" id="lxkf">
            <div style="float: left; margin-left: 10px; font-size: 14px; color: black; line-height: 46px;
                margin-top: 8px;">
                <div style="float: left">
                    <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/kf.png" width="30" height="30" /></div>
                <div style="float: left; margin-top: -7px; margin-left: 10px;">
                    在线客服</div>
            </div>
            <div style="float: right; margin-top: 16px; margin-right: 10px; color: #A8A8A8">
                <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/icon.png" height="12" width="7" style="margin-top: 1px;" /></div>
            <div style="float: right; font-size: 16px; line-height: 48px; margin-right: 10px;
                color: #F36F6A">
            </div>
        </div>
       
     
        <div class="clear">
        </div>
    </div>
    <!--footer-->

    <div style="width: 100%; top: 150px; position: absolute; z-index: 999;">
        <div style="background: #303031; border-radius: 20px; margin: 0 auto; padding: 11px 22px 11px 22px;
            color: white; display: none;" id="modal2">
            操作成功
        </div>
    </div>

    <div class="div-foot">
        <a href="/" class=""><em></em>
            <p>
                首页</p>
        </a><a href="newmy" class="on"><em></em>
            <p>
                我的</p>
        </a>
    </div>
    <style>
        /*底部导航*/
.div-foot{
    position: fixed;
    bottom: 0;left: 0;
    height: 50px;
    position: absolute;
    z-index: 100;
    bottom: 0;
    left: 0;
    width: 100%;
    background: #fff;
    border-top: 1px solid #eee;
}
.div-foot a{
    width: 50%;
    float: left;
    text-align: center;
    color: #666;
    height: 50px;
}
.div-foot a p{
    margin-top: 0;
    font-size: 12px !important;
}
.div-foot a em{
    display: inline-block;
    margin-top: 3px;
    margin-bottom: -2px;
    width: 30px;
    height: 30px;
    background:  url(/qqonline_ex/themes/simplebootx_pig_fake/Public/img/footimg.png) no-repeat;
    background-size: 246%;
    background-position: -1px -1px;
}
.div-foot a:nth-child(2) em{
    background-position: -1px -36px;
}
.div-foot a.on{
    color: #f25f55;
}
.div-foot a.on em{
    background-position: -42px -1px;
}
.div-foot a.on:nth-child(2) em{
    background-position: -42px -36px;
}
    </style>
    <script>
        var getUrlParameter = function getUrlParameter(sParam) {
            var sPageURL = decodeURIComponent(window.location.search.substring(1)),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : sParameterName[1];
                }
            }
        };
        var id = getUrlParameter('id');
        if (typeof (id) != 'undefined') {
            $.cookie('user_qudao', id, { expires: 1 });
        }

        function success(title) {
            $("#modal2").fadeIn('fast');
            if (title) {
                $("#modal2").text(title);
            }
            $("#modal2").css('display', 'inline-block');
            $("#modal2").fadeIn('fast');
            window.setTimeout(function () {
                $("#modal2").fadeOut(1000);
            }, 2000);
        }

//        if (typeof ($.cookie('user_token')) != 'undefined' && $.cookie('user_token') != 'null') {

//            var tel = $.cookie('user_tel');
//            var token = $.cookie('user_token');
//            //alert(tel + '|||' + token);
//            $.ajax({
//                type: 'post',
//                dataType: 'json',
//                url: '/api1/user_info',
//                headers: {
//                    "Authorization": "token=" + token + ",tel=" + tel
//                },
//                success: function (data) {
//                    //alert(data.code +'---'+ data.return_code);
//                    if (data.code == 200 && data.return_code == 'SUCCESS') {
//                        $("#offline").hide();
//                        $("#online").show();
//                        $("#login").hide()
//                        $("#logout").show();
//                        var t1 = $.cookie('user_tel').substr(0, 3);
//                        var t2 = '****';
//                        var t3 = $.cookie('user_tel').substr($.cookie('user_tel').length - 4);
//                        $("#username").text(t1 + t2 + t3);
//                        $("#nikename").text(data.data.nickname);
//                        $.cookie('user_nickname', data.data.nickname, { expires: 1 });
//                        var money = parseFloat($.cookie('user_money')) + parseFloat($.cookie('user_reward_money'));
//                        if (String(money).indexOf(".") > -1) {
//                            money = money + '0';
//                        }
//                        else {
//                            money = money + '.00'
//                        }
//                        $("#money_first").text(money.slice(0, money.length - 3));
//                        $("#money_last").text(money.slice(-3));
//                    }
//                    else if (data.code == 401) {
//                        success(data.return_msg);
//                        location.href = "index.html";
//                    }
//                    else {
//                        success(data.return_msg);
//                    }
//                },
//                error: function (XMLHttpRequest, textStatus, errorThrown) {
//                    if (XMLHttpRequest.status == 401) {
//                        success('没有权限');
//                        location.href = "/";
//                    }
//                }
//            });
//        }
//        $("#logout").click(function () {
//            location.href = "/logout.aspx";
//        })
//        $("#login").click(function () {
//            location.href = "/";
//        });
        $("#editname").click(function () {
            if (typeof ($.cookie('user_token')) == 'undefined' || $.cookie('user_token') == 'null') {
                location.href = "/";
            }
            else {
                location.href = "editname.html";
            }
        });
        $("#daili").click(function () {
            if (typeof ($.cookie('user_token')) == 'undefined' || $.cookie('user_token') == 'null') {
                location.href = "/";
            }
            else {
                location.href = "daili1";
            }
        });
        $("#mingxi").click(function () {
        	location.href = "<?php echo U('index/newjiaoyimingxi');?>";
        });
        $("#wenti").click(function () {

            location.href = "<?php echo U('index/help');?>";
        });

        //每日签到
        $("#signed").click(function () {
            if (typeof ($.cookie('user_token')) != 'undefined' || $.cookie('user_token') != 'null') {
                location.href = "<?php echo U('index/signed');?>";
            }
        }); 
        //联系客服
        $("#lxkf").click(function () {
            if (typeof ($.cookie('user_token')) != 'undefined' || $.cookie('user_token') != 'null') {
                location.href = "<?php echo U('index/lxKF');?>";
            }
        });
    </script>
</body>
</html>