<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="user-scalable=no, width=320">
    <title>提现</title>
    <link href="/qqonline_ex/themes/simplebootx_pig_fake/Public/css/common.css" rel="stylesheet" type="text/css" />
    <link href="/qqonline_ex/themes/simplebootx_pig_fake/Public/css/app.css" rel="stylesheet" type="text/css" />
    <link href="/qqonline_ex/themes/simplebootx_pig_fake/Public/css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/qqonline_ex/themes/simplebootx_pig_fake/Public/cssdb/index.css" />
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.min.js" type="application/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.cookie.js" type="application/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.shCircleLoader.js" type="text/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/js/modal.js"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/js/alert.js"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/js/md5.js"></script>
    <style>
        #shclDefault
        {
            width: 50px;
            height: 50px;
            margin-left: 125px;
            margin-top: -50px;
            position: absolute;
            z-index: 10;
        }
    </style>
</head>
<body align="center">
    <div class="all">
        <div style="width: 300px; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 10px;
            background: white; float: left; margin-left: 10px; padding-bottom: 10px; box-sizing: border-box;
            border-radius: 4px;">
            <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px;">
                到帐账户 <font style="color: #6678A8; margin-left: 15px; font-size: 16px;">本微信号零钱包</font></div>
            <div class="clear">
            </div>
            <div style="margin-top: 15px; margin-left: 10px; font-size: 16px;">
                <div style="float: left;">
                    提现金额
                </div>
                <div style="float: left; margin-top: -5px; line-height: 27px; font-size: 12px;">
                    <font style="margin-left: 20px; float: left; margin-top: 3px;">￥</font><input type="tel"
                        style="background: white; border: 0px; margin-left: 3px; font-size: 24px; width: 120px;
                        padding-left: 0px; line-height: 32px; float: left" placeholder="请输入金额" id="getcash" /><br />
                    <span id="issxf"></span>
                </div>
            </div>
            <div class="clear">
            </div>
            <div style="margin-top: 30px; float: left; margin-left: 10px; color: #9B9B9B; font-size: 12px;">
                佣金余额 <font style="margin-left: 32px;" id="money">￥<?php echo ($wallet['money2']); ?> </font>元（今日可提 <font style="color: #007AFF;"
                    id="getcash_time">5</font> 次）</div>
        </div>
        <div class="clear">
        </div>
        <div id="shclDefault">
        </div>
        <div style="width: 300px; color: white; background: #1DBBA4; margin: 0 auto; border-radius: 4px;
            margin-top: 20px; line-height: 23px; height: 32px; padding-top: 10px; font-size: 16px;"
            id="pay">
            确认提现</div>
        <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; margin-top: 10px;
            color: #286EE0;">
            新用户首次提现必须实盘夺宝 2 次;</div>
        <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #286EE0;">
            红包不能提现,只能用于抵扣下单;</div>
        <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #A8A8A8;">
            用户每天可以不限时间提现 <?php echo ($max_times); ?> 次（ 秒到账 ）;</div>
        <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #A8A8A8;">
            每次提现必须是 1 元的整数倍;</div>
        <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #A8A8A8;">
            每天提现最多 2 万元;</div>
        <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #A8A8A8;">
            客服QQ:XXXXX;</div>
    </div>
    <div style="width: 100%; top: 150px; position: absolute; z-index: 999;">
        <div style="background: #303031; border-radius: 20px; margin: 0 auto; padding: 11px 22px 11px 22px;
            color: white; display: none;" id="modal2">
            操作成功
        </div>
    </div>
    <!--footer-->
    <div style="border-top: #F0F0F0 1px solid; height: 45px; width: 100%; line-height: 44px;
        background: white; position: fixed; bottom: 0px; text-align: center; font-size: 16px;"
        onclick="location.href='index.php?g=Pig&m=index&a=index'">
        返回首页
    </div>
    <script>
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
        //提现
        var abc = 0;
        $("#pay").click(function () {
            var token = $.cookie('user_token');
            var token2 = $.cookie('user_token2');
            var gross = $("#getcash").val();
            if (gross == '' || gross < <?php echo ($base_price); ?>) {
                success('提现金额不能小于<?php echo ($base_price); ?>元');
                return false;
            }
            
        	var ticket = Date.parse(new Date());
        	var sign = hex_md5('apply_drawcash_with_money' + 1 + gross + ticket);
            
        	$.ajax({
        		url: 'index.php?g=Qqonline&m=index&a=apply_drawcash_with_money',
        		type: "get",
        		dataType: "json",
        		data: {
        			money:gross,
        			moneyType:1,
        			ticket:ticket,
        			sign:sign
        		},
        		success: function (data) {
        			if (data.ret == 1)
        			{
        				location.href = data.gourl;
        			}
        			else
        			{
        				success(data.msg);
        			}
        		}
        	});
        });
    </script>
</body>
</html>