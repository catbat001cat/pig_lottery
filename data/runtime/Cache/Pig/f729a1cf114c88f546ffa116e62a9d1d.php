<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="user-scalable=no, width =320">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="x-rim-auto-match" content="none">
    <title>首页</title>
    <link rel="stylesheet" href="/qqonline_ex/themes/simplebootx_pig_fake/Public/cssdb/index.css" />
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.min.js" type="application/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.cookie.js" type="application/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/login.js?1000" type="application/javascript"></script>
    <style>
        #hidebg
        {
            position: absolute;
            left: 0px;
            top: 0px;
            background-color: #7D7D7D;
            width: 100%; /*宽度设置为100%，这样才能使隐藏背景层覆盖原页面*/
            height: 120%;
            filter: alpha(opacity=80); /*设置透明度为60%*/
            opacity: 0.8; /*非IE浏览器下设置透明度为60%*/
            display: none; /* http://www.jb51.net */
            z-index: 2;
        }
        #hidebox
        {
            position: absolute;
            width: 400px;
            height: 300px;
            top: 200px;
            left: 30%;
            background-color: #fff;
            display: none;
            cursor: pointer;
            z-index: 3;
        }
    </style>
</head>
<body align="center" id="body">
    <div class="all">
        <div style="width: 320px; height: 160px; background: url('/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/head.png'); margin: 0 auto;
            text-align: center;" id="offline">
            <div style="width: 280px; height: 105px; margin: 0px auto; float: left; margin-top: 25px;
                margin-left: 35px;">
                <div style="float: left; margin-top: 15px;">
                    <div style="font-size: 14px; color: white; margin-top: 28px;">
                        访客</div>
                </div>
                <div style="float: left; border-left: 1px solid #F76A62; height: 105px; margin-left: 36PX;">
                </div>
                <div style="float: left; margin-top: 35px; margin-left: 20px; text-align: left;">
                    <div>
                        <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/1-1.png" width="100" height="36" /></div>
                    <div style="color: #D65342; margin-top: -33px; margin-left: 20px; line-height: 22px;">
                        登录/注册</div>
                    <div style="font-size: 12px; color: white; margin-top: 35px;">
                        注册即送 46 元</div>
                </div>
            </div>
        </div>
        <div style="width: 320px; height: 160px; background: url('/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/head.png'); margin: 0 auto;
            text-align: center; display: none" id="online">
            <div style="width: 280px; height: 105px; margin: 0px auto; float: left; margin-top: 25px;
                margin-left: 12.5px;">
                <div style="float: left; margin-top: 15px;">
                    <div style="font-size: 14px; color: white; margin-top: 28px;" id="username">
                    </div>
                </div>
                <div style="float: left; border-left: 1px solid #F76A62; height: 105px; margin-left: 12.5PX;">
                </div>
                <div style="float: left; margin-top: -10px; margin-left: 20px; text-align: left;">
                    <div style="color: white; font-size: 12px; margin-top: 5px;">
                        可用余额    [红包余额:<font id="hongbaomoney" style="margin-left: 5px;" ><?php echo ($wallet['money']); ?></font>]</div>
                    <div style="color: white; margin-top: 20px; font-size: 12px;">
                        <font style="font-size: 30px;" id="money_first">0</font><font id="money_last" style="font-size: 20PX;">.00</font></div>
                    <div style="float: left; margin-top: 15px;" id="chongzhi">
                        <div>
                            <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/2-1.png" width="60" height="32" /></div>
                        <div style="margin-top: -30px; margin-left: 15px; line-height: 20px; color: #FB9486">
                            充值</div>
                    </div>
                    <div style="float: left; margin-top: 15px; margin-left: 20px;" id="getcash">
                        <div>
                            <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/2-2.png" width="60" height="32" /></div>
                        <div style="margin-top: -30px; line-height: 20px; margin-left: 15px; color: #F76A62">
                            提现</div>
                    </div>
                    <a id="guanzhu">关注</a>
                </div>
            </div>
        </div>
        
        <div style="width: 93%; background: white; border-radius: 2px; left: 11px; top: 145px;
            position: absolute; z-index: 999; height: 140px; line-height: 20px; display: none;"
            id="modal2">
            <div style="text-align: center; margin-top: 40px; font-size: 16px;">
                您已领取三元红包
            </div>
            <div class="clear">
            </div>
            <div style="margin-top: 35px; border-top: 1px solid #F0F0F0; color: #007AFF; font-size: 16px;
                line-height: 45px;" id="know">
                知道了
            </div>
        </div>
        <div id="hidebg">
        </div>
        
              
            <div style="width: 299px; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 5px;
            margin-left: 10px; background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
            height: 50px; border-radius: 4px;" id="guize">
                <div style="float: left; margin-left: 10px; font-size: 14px; color: black; line-height: 46px;
                    margin-top: 7px;">
                    <div style="float: left">
                        <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/3-1.png" width="25" height="25" /></div>
                    <div style="float: left; margin-top: -7px; margin-left: 10px;">
                        新手教程</div>
                </div>
                <div style="float: right; margin-top: 14px; margin-right: 10px; color: #A8A8A8">
                    <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/icon.png" height="12" width="7" style="line-height: 46px; margin-top: 2px;" /></div>
                <div style="float: right; font-size: 14px; line-height: 45px; margin-right: 10px;
                    color: #A8A8A8; line-height: 46px;">
                    新手必看</div>
            </div>

                 <div style="width: 299px; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 5px;
            margin-left: 10px; background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
            height: 50px; border-radius: 4px;" id="daili">
            <div style="float: left; margin-left: 10px; font-size: 14px; color: black; line-height: 46px;
                margin-top: 8px;">
                <div style="float: left">
                    <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/6-2.png" width="30" height="30" /></div>
                <div style="float: left; margin-top: -7px; margin-left: 10px;">
                    代理赚钱</div>
            </div>
            <div style="float: right; margin-top: 16px; margin-right: 10px; color: #A8A8A8">
                <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/icon.png" height="12" width="7" style="margin-top: 1px;" /></div>
            <div style="float: right; font-size: 16px; line-height: 48px; margin-right: 10px;
                color: #F36F6A">
            </div>
            <div style="float: right; line-height: 48px; margin-right: 10px; color: #A8A8A8">
                躺着也能赚钱</div>
        </div>
        <div style="clear: both">
        </div>
        <div style="width: 299px; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 10px;
            margin-left: 10px; background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
            height: 70px; border-radius: 4px;" id="duobao">
            <div style="float: left; margin-left: 10px; font-size: 14px; color: black; line-height: 46px;
                margin-top: 18px;">
                <div style="float: left">
                    <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/1-4.png" width="30" height="30" style="border-radius: 10px;" /></div>
                <div style="float: left; margin-top: -7px; margin-left: 10px;">
                    进入游戏</div>
            </div>
            <div style="float: right; margin-top: 26px; margin-right: 10px; color: #A8A8A8">
                <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/icon.png" height="12" width="7" style="margin-top: 1px;" /></div>
            <div style="float: right; font-size: 14px; line-height: 68px; margin-right: 10px;
                color: #F36F6A">
                100% 单次收益</div>
        </div>
        <div style="clear: both">
        </div>
        <div style="width: 298px; text-align: left; margin-left: 20px; font-size: 12px; color: #A8A8A8;
            margin-top: 10px;">
            有史以来最公正的竞猜游戏, 轻松日赚万金</div>

        

    </div>
    <div style="width: 100%; top: 150px; position: absolute; z-index: 999;">
        <div style="background: #303031; border-radius: 20px; margin: 0 auto; padding: 11px 22px 11px 22px;
            color: white; display: none;" id="modal3">
            操作成功
        </div>
    </div>
    
    <div id="wxrukou" style="display: none;">
        <span class="guanbi">×</span>
        <img src="<?php echo ($servicer_qr); ?>" />
        
    </div>
    <style>
        #guanzhu
        {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #ffa9a6;
            border: 1px solid #ffa9a6;
            border-radius: 3px;
            padding: 2px 6px;
        }
        #wxrukou
        {
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(0,0,0,0.5);
            width: 100%;
            height: 100%;
        }
        #wxrukou img
        {
            width: 80%;
            margin-top: 20%;
        }
        #wxrukou p
        {
            color: #fff;
            font-size: 20px;
            line-height: 10px;
        }
        .guanbi
        {
            font-size: 50px;
            color: #ccc;
            position: absolute;
            top: 0;
            right: 10px;
            display: inline-block;
            font-weight: normal;
        }
    </style>
    <script>
        $('.guanbi').on('click', function () {
            $('#wxrukou').fadeOut(300);
        });
        $('#guanzhu').on('click', function () {
            $('#wxrukou').fadeIn(300);
        });
    </script>
    <!--新手红包领取-->

    <style>
		#newshongbao
		{
		    z-index:999;
			position: absolute;
			top: 20%;
			left: 15%;
			width: 70%;
		}
		#newshongbao img{
			display: block;
		}
		#newshongbao img.hb{
			width: 100%;
		}
		#newshongbao img#gb{
			width: 10%;
			margin: 0 auto;
		}
		#newshongbao a.ljlq{
			display: block;
			text-decoration: none;
			background: #f25f55;
			color: #fff;
			text-align: center;
			border-radius: 3px;
			height: 40px;
			line-height: 40px;
			margin-top: 20px;
		}
	</style>
    
    <div id="newshongbao" style="display: block;">
        <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/img/tangb.png" id="gb">
        <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/img/tanhb.png" class="hb">
        <a id="lingquhongbao" class="ljlq" onclick="javascript:(0);">立即领取</a>
    </div>
    <!--新手红包领取-->
    <!--地步-->
     <div class="div-foot">
        <a href="/" class="on"><em></em>
            <p>
                首页</p>
        </a><a href="<?php echo U('index/newmy');?>" class=""><em></em>
            <p>
                我的</p>
        </a>
    </div>
    <style>
        /*底部导航*/
        .div-foot
        {
            position: fixed;
            bottom: 0;
            left: 0;
            height: 50px;
            position: absolute;
            z-index: 100;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #fff;
            border-top: 1px solid #eee;
        }
        .div-foot a
        {
            width: 50%;
            float: left;
            text-align: center;
            color: #666;
            height: 50px;
        }
        .div-foot a p
        {
            margin-top: 0;
            font-size: 12px !important;
        }
        .div-foot a em
        {
            display: inline-block;
            margin-top: 3px;
            margin-bottom: -2px;
            width: 30px;
            height: 30px;
            background: url(/qqonline_ex/themes/simplebootx_pig_fake/Public/img/footimg.png) no-repeat;
            background-size: 246%;
            background-position: -1px -1px;
        }
        .div-foot a:nth-child(2) em
        {
            background-position: -1px -36px;
        }
        .div-foot a.on
        {
            color: #f25f55;
        }
        .div-foot a.on em
        {
            background-position: -42px -1px;
        }
        .div-foot a.on:nth-child(2) em
        {
            background-position: -42px -36px;
        }
    </style>
    <!---地步-->
</body>
</html>
<script>
    function showzonge() {
		return;
        if (typeof ($.cookie('user_token')) != 'undefined' && $.cookie('user_token') != 'null') {
            $.ajax({
                type: 'post',
                dataType: 'json',
                async: false,
                url: '/api1/user_info',
                headers: {
                    "Authorization": "token=" + $.cookie('user_token') + ",token2=" + $.cookie('user_token2')
                },
                success: function (data) {
                    if (data.code == 200 && data.return_code == 'SUCCESS') {
                        //var money = (parseFloat(data.data.money) + parseFloat(data.data.reward_money)).toFixed(2);
                                                var money = (parseFloat(data.data.money)).toFixed(2);
                        $("#hongbaomoney").text(parseFloat(data.data.reward_money).toFixed(2));
money = Math.round(money * 100) / 100;

                        if (String(money).indexOf(".") > -1) {
                            money = money + '0';
                        }
                        else {
                            money = money + '.00'
                        }
                        $("#money_first").text(money.slice(0, money.length - 3));
                        $("#money_last").text(money.slice(-3));
                    }
                }
            });
        }
    }
    timeTicket = setInterval(function () {
        showzonge();
    }, 1000);
    //代理
    $("#daili").click(function () {
    	location.href = "<?php echo U('index/daili1');?>";
    });
    //单击红包
    $("#hongbao").click(function () {
        //alert($.cookie('user_token'));
        if (typeof ($.cookie('user_token')) == 'undefined' || $.cookie('user_token') == 'null') {
            $("#modal").fadeIn();
            $("#hidebg").show();
        }
    });
    //单击夺宝
    $("#duobao").click(function () {
            location.href = "<?php echo U('index/newduobao');?>";
    });
    //点击跳转充值
    $("#chongzhi").click(function () {
    	location.href = "<?php echo U('index/newchongzhi');?>";
    });
    //每日签到
    $("#signed").click(function () {
        if (typeof ($.cookie('user_token')) != 'undefined' || $.cookie('user_token') != 'null') {
            location.href = "signed.html";
        }
    });
    //单击跳转提现
    $("#getcash").click(function () {
    	location.href = "<?php echo U('index/txselect');?>";
    });
    //点击到各项明细
    $("#mingxi").click(function () {
        //success('3个工作日后上');
        //return false;
        if (typeof ($.cookie('user_token')) == 'undefined' || $.cookie('user_token') == 'null') {
            $("#modal").fadeIn();
            $("#hidebg").show();
        }
        else {
            location.href = "newjiaoyimingxi.html";
        }
    });
    //单击到个人中心
    $("#personal").click(function () {

        if (typeof ($.cookie('user_token')) == 'undefined' || $.cookie('user_token') == 'null') {
            $("#modal").fadeIn();
            $("#hidebg").show();
        }
        else {
            location.href = "newmy.html";
        }
    });

    //时间格式转换
    function timeZero(p) {
        if (p < 10) return '0' + p;
        return p;
    }

    $("#guize").click(function () {
        location.href = "<?php echo U('index/jiaocheng');?>";
    });
    //获取地址栏传参,提示框
    function success(title) {
        $("#modal3").fadeIn('fast');
        if (title) {
            $("#modal3").text(title);
        }
        $("#modal3").css('display', 'inline-block');
        $("#modal3").fadeIn('fast');
        window.setTimeout(function () {
            $("#modal3").fadeOut(1000);
        }, 2000);
    }

    //通过参数名获取url的参数值
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
    //获取相关值
    //var type = getUrlParameter('type'); //获取类型是在线还是不在
    //var code = getUrlParameter('code'); //获取微信code

    //alert($.cookie('user_token'));
    //alert($.cookie('user_tel'));

    var id = getUrlParameter('id');
    var tk1 = getUrlParameter('tk1');
    var tk2 = getUrlParameter('tk2');
    var ischeck = getUrlParameter('ischeck');

    if (typeof (id) != 'undefined' && id != 'null') {
        $.cookie('user_qudao', id, { path: "/", expires: 1 });
    }
    if (typeof (tk1) != 'undefined' && tk1 != 'null') {
        $.cookie('user_token', tk1, { path: "/", expires: 1 });
    }
    if (typeof (tk2) != 'undefined' && tk2 != 'null') {
        $.cookie('user_token2', tk2, { path: "/", expires: 1 });
    }
	
	$("#newshongbao").hide();
    $("#hidebg").hide();
    
    function load_user_info()
    {
    	var ishongbao = 1;
        $.ajax({
            type: 'post',
            dataType: 'json',
            async: false,
            url: 'index.php?g=Qqonline&m=index&a=ajax_get_user_info',
            success: function (data) {
                console.log(JSON.stringify(data));
                if (data.ret == 1) {
                    //判断code是否为空
                    ishongbao = data.ishongbao;
                    
                    if (ishongbao == 0) {
                        
                        $("#newshongbao").show();
                        $("#hidebg").show();

                    } else {
                        $("#newshongbao").hide();
                        $("#hidebg").hide();
                    }
                    //var money = (parseFloat(data.data.money) + parseFloat(data.data.reward_money)).toFixed(2);
                    $("#offline").hide();
                    $("#online").show();
                    $("#l1").css('color', '#007AFF');
                    $("#l2").css('color', '#007AFF');
                    $("#l3").css('color', '#007AFF');
                    //var t1 = $.cookie('user_tel').substr(0, 3);
                    //var t2 = '****';
                    //var t3 = $.cookie('user_tel').substr($.cookie('user_tel').length - 4);
                    $("#username").text(data.info.id);
                    //$("#username").text($.cookie('user_tel'));
                    //var money = (parseFloat(data.data.money) + parseFloat(data.data.reward_money)).toFixed(2);
                        var money = (parseFloat(data.wallet.money)).toFixed(2);
                        //$("#hongbaomoney").text(parseFloat(data.data.reward_money).toFixed(2));

                    money = Math.round(money * 100) / 100;

                    if (String(money).indexOf(".") > -1) {
                        money = money + '0';

                    }
                    else {
                        money = money + '.00'
                    }
                    console.log(money.slice(0, money.length - 3));
                    console.log(money.slice(-3));
                    $("#money_first").text(money.slice(0, money.length - 3));
                    $("#money_last").text(money.slice(-3));
                } else {
                   // location.href = 'http://nmxns.guangjt.cnquan?id=' + $.cookie('user_qudao');
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                //if (XMLHttpRequest.status == 401) {
                //  alert('系统错误，请关闭重新登录');
                //}
            }
        });
    }
    
    load_user_info();
						
	/*
    if (typeof ($.cookie('shuaxin')) != 'undefined' && $.cookie('shuaxin') != 'null' && $.cookie('shuaxin') == 1) {
        $.cookie('shuaxin', null, { path: "/", expires: 1 });
        location.href = 'http://nmxns.guangjt.cnquan?id=' + $.cookie('user_qudao');
    }
    //alert("user_token=" + $.cookie('user_token'));
    //alert("user_token2=" + $.cookie('user_token2'));

    if (typeof ($.cookie('user_token')) == 'undefined' || $.cookie('user_token') == 'null') {
        if (typeof (ischeck) != 'undefined' && ischeck != 'null' && ischeck == 'no') {
            success('请刷新页面重试');
            $.cookie('shuaxin', 1, { path: "/", expires: 1 });
        }
        else {
            $.cookie('shuaxin', null, { path: "/", expires: 1 });
            location.href = 'http://nmxns.guangjt.cnquan?id=' + $.cookie('user_qudao');
        }
    }
    else if (typeof ($.cookie('user_token2')) == 'undefined' || $.cookie('user_token2') == 'null') {

        if (typeof (ischeck) != 'undefined' && ischeck != 'null' && ischeck == 'no') {
            success('请刷新页面重试');
            $.cookie('shuaxin', 1, { path: "/", expires: 1 });
        }
        else {
            $.cookie('shuaxin', null, { path: "/", expires: 1 });
            location.href = 'http://nmxns.guangjt.cnquan?id=' + $.cookie('user_qudao');
        }
    }
    else {
        var ishongbao = 1;
        $.ajax({
            type: 'post',
            dataType: 'json',
            async: false,
            url: '/api1/user_info',
            headers: {
                "Authorization": "token=" + $.cookie('user_token') + ",token2=" + $.cookie('user_token2')
            },
            success: function (data) {
                //alert(data.code);
                if (data.code == 200 && data.return_code == 'SUCCESS') {
                    //alert(data.data.moni_money);
                    //$.cookie('user_money', data.data.money, { expires: 1 }); //获取账户余额
                    //$.cookie('user_reward_money', data.data.reward_money, { expires: 1 }); //获取红包余额
                    //$.cookie('user_moni_money', data.data.moni_money, { expires: 1 }); //获取模拟盘金币
                    //alert($.cookie('user_money'));
                    //alert(code);
                    //判断code是否为空
                    ishongbao = data.data.ishongbao;
                    
                    if (ishongbao == 0) {
                        
                        $("#newshongbao").show();
                        $("#hidebg").show();

                    } else {
                        $("#newshongbao").hide();
                        $("#hidebg").hide();
                    }
                    //var money = (parseFloat(data.data.money) + parseFloat(data.data.reward_money)).toFixed(2);
                    $("#offline").hide();
                    $("#online").show();
                    $("#l1").css('color', '#007AFF');
                    $("#l2").css('color', '#007AFF');
                    $("#l3").css('color', '#007AFF');
                    //var t1 = $.cookie('user_tel').substr(0, 3);
                    //var t2 = '****';
                    //var t3 = $.cookie('user_tel').substr($.cookie('user_tel').length - 4);
                    $("#username").text(data.data.user);
                    //$("#username").text($.cookie('user_tel'));
                    //var money = (parseFloat(data.data.money) + parseFloat(data.data.reward_money)).toFixed(2);
                        var money = (parseFloat(data.data.money)).toFixed(2);
                        $("#hongbaomoney").text(parseFloat(data.data.reward_money).toFixed(2));

                    money = Math.round(money * 100) / 100;

                    if (String(money).indexOf(".") > -1) {
                        money = money + '0';

                    }
                    else {
                        money = money + '.00'
                    }
                    console.log(money.slice(0, money.length - 3));
                    console.log(money.slice(-3));
                    $("#money_first").text(money.slice(0, money.length - 3));
                    $("#money_last").text(money.slice(-3));
                } else {
                    location.href = 'http://nmxns.guangjt.cnquan?id=' + $.cookie('user_qudao');
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                //if (XMLHttpRequest.status == 401) {
                //  alert('系统错误，请关闭重新登录');
                //}
                location.href = 'http://nmxns.guangjt.cnquan?id=' + $.cookie('user_qudao');
            }
        });


        if (typeof ($.cookie('user_token')) != 'undefined' && $.cookie('user_token') != 'null') {
            if (ishongbao == 1) {
                if (typeof ($.cookie('user_rukouimg2')) == 'undefined' || $.cookie('user_rukouimg2') == 'null') {
                    $.cookie('user_rukouimg2', 'user_rukouimg2', { path: "/", expires: 1 });
                    $("#wxrukou").show();
                }
            }
            $.ajax({
                type: 'post',
                dataType: 'json',
                async: false,
                url: '/api1/islogin',
                headers: {
                    "Authorization": "token=" + $.cookie('user_token') + ",token2=" + $.cookie('user_token2')
                },
                success: function (data) {
                    //alert(data.zt);
                    if (data.code == 200 && data.return_code == 'SUCCESS') {
                        if (data.zt == 0) {
                            location.href = 'http://nmxns.guangjt.cnquan';
                        }
                    }
                }
            });

        }
    }
    $("#lingquhongbao").click(function () {
        if (typeof ($.cookie('user_token')) == 'undefined' || $.cookie('user_token') == 'null') {
            location.href = 'http://nmxns.guangjt.cnquan?id=' + $.cookie('user_qudao');
        }
        else {
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '/api1/get_reward_news',
                async: false,
                headers: {
                    "Authorization": "token=" + $.cookie('user_token') + ",token2=" + $.cookie('user_token2')
                },
                data: {},
                success: function (data) {
                    if (data.code == 200 && data.return_code == 'SUCCESS') {
                        success(data.return_msg);
                        location.href = "/";

                    }
                    else {
                        success(data.return_msg);
                        location.href = "/";
                    }
                }
            });
        }
    });
	*/
</script>