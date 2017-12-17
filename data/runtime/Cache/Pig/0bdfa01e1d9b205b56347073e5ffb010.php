<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content=" user-scalable=no, width=320">
    <title>常见问题</title>
    <link rel="stylesheet" href="/qqonline_ex/themes/simplebootx_pig_fake/Public/cssdb/index.css" />
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.min.js" type="application/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.cookie.js" type="application/javascript"></script>
</head>
<body align="center">
    <div class="all">
        <div style="width: 100%; border-top: 1px solid #EEEEEE; border-bottom: 1px solid #EEEEEE;
            margin: 0 auto; margin-top: 10px; background: white; float: left; box-sizing: border-box;
            padding-bottom: 11px;">
            <a href="<?php echo U('index/jiaoyiguize');?>" />
            <div style="height: 40px; color: black">
                <div style="margin-top: 12px; float: left; margin-left: 20px; font-size: 16px; line-height: 22px;">
                    夺宝规则</div>
                <div style="float: right; margin-top: 15px; margin-right: 20px; color: #A8A8A8">
                    <img src="imgdb/icon.png" height="12" width="7" /></div>
                <hr style="width: 300px; float: left; margin-top: 10px; margin-left: 20px; border: 0px;
                    height: 1px; background: #EEEEEE" />
            </div>
            </a> <a href="<?php echo U('index/chongzhixiangguan');?>" />
            <div style="height: 40px; color: black">
                <div style="margin-top: 4px; float: left; margin-left: 20px; font-size: 16px; line-height: 22px;">
                    充值相关</div>
                <div style="float: right; margin-top: 7px; margin-right: 20px; color: #A8A8A8">
                    <img src="imgdb/icon.png" height="12" width="7" /></div>
                <hr style="width: 300px; float: left; margin-top: 10px; margin-left: 20px; border: 0px;
                    height: 1px; background: #EEEEEE" />
            </div>
            </a> <a href="<?php echo U('index/tixianxiangguan');?>" />
            <div style="height: 40px; color: black">
                <div style="margin-top: 4px; float: left; margin-left: 20px; font-size: 16px; line-height: 22px;">
                    提现相关</div>
                <div style="float: right; margin-top: 7px; margin-right: 20px; color: #A8A8A8">
                    <img src="imgdb/icon.png" height="12" width="7" /></div>
                <hr style="width: 300px; float: left; margin-top: 10px; margin-left: 20px; border: 0px;
                    height: 1px; background: #EEEEEE" />
            </div>
            </a> <a href="hongbaoxiangguan.html" />
         
            </a>
        </div>
        <div class="clear">
        </div>
      
        <div class="clear">
        </div>
        <div style="width: 280px; text-align: left; margin: 0 auto; font-size: 12px; margin-top: 10px;
            color: #A8A8A8;">
            <h3 style="color:Black;">如您有任何疑问，请联系客服<br />客服QQ：XXXXXXXX</h3></div>
        
    </div>
    <script>
        $("#d3").click(function () {
        	location.href = "<?php echo U('index/newjiaoyimingxi');?>";
        });
        $("#lianxikefu").click(function () {
            if (typeof ($.cookie('user_token')) == 'undefined' || $.cookie('user_token') == 'null') {
                location.href = "/?type=offline"; //不在线
            }
            else {
                location.href = 'cs';
            }
        });
    </script>
    <div style="border-top: #F0F0F0 1px solid; height: 45px; width: 100%; line-height: 44px;
        background: white; position: fixed; bottom: 0px; text-align: center; font-size: 16px;"
        onclick="location.href='/'">
        返回首页
    </div>
</body>
</html>