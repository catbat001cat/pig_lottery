<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="user-scalable=no, width=320">
    <title>代理赚钱</title>
    <link rel="stylesheet" href="/qqonline_ex/themes/simplebootx_pig_fake/Public/cssdb/index.css">
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.min.js" type="application/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.cookie.js" type="application/javascript"></script>
    <style>
        #hidebg
        {
            position: absolute;
            left: 0px;
            top: 0px;
            background-color: black;
            width: 100%; /*宽度设置为100%，这样才能使隐藏背景层覆盖原页面*/
            height: 120%;
            filter: alpha(opacity=80); /*设置透明度为60%*/
            opacity: 0.8; /*非IE浏览器下设置透明度为60%*/
            display: none; /* http://www.jb51.net */
            z-index: 2;
        }
        #qrtext
        {
            filter: alpha(opacity=60); /*设置透明度为60%*/
            opacity: 0.6; /*非IE浏览器下设置透明度为60%*/
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
        body
        {
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .yjlb
        {
            border-radius: 3px;
            margin: 10px;
        }
        .yjlb div
        {
            overflow: hidden;
        }
        .yjlb div p
        {
            background: #fff;
            margin: 1% 1% 0 0;
            display: inline-block;
            width: 46%;
            float: left;
            font-size: 12px;
            height: 34px;
            line-height: 34px;
            padding-left: 3%;
            color: #666;
        }
        .yjlb div p.y1
        {
            width: 100%;
        }
        .yjlb div p span
        {
            color: #f25f55;
            font-size: 14px;
            margin-left: 5px;
        }
    </style>
</head>
<body align="center">
    <div class="all">
        <div class="content" style="width: 94%;">
            <div style="width: 100%; margin-top: 10px;">
                <a href="<?php echo U('index/daili1');?>">
                    <div style="background: #337AB7; color: white; width: 93px; height: 40px; float: left;
                        padding-top: 6px; border-radius: 4px; font-size: 16px; line-height: 34px;">
                        代理</div>
                </a><a href="<?php echo U('index/daili2');?>">
                    <div style="background: white; color: #A8A8A8; width: 91px; height: 38px; float: left;
                        padding-top: 6px; margin-left: 11px; border-radius: 4px; border: #EEEEEE 1px solid;
                        font-size: 16px; line-height: 32px;">
                        佣金</div>
                </a><a href="<?php echo U('index/daili3');?>">
                    <div style="background: white; color: #A8A8A8; width: 91px; height: 38px; float: left;
                        padding-top: 6px; margin-left: 10px; border-radius: 4px; border: #EEEEEE 1px solid;
                        font-size: 16px; line-height: 32px;">
                        下线</div>
                </a>
            </div>
            <div id="jiaoyimingxi">
                <!--
                <div style="width:100%; border:1px solid #F0F0F0; padding-bottom:12px;box-sizing: border-box;margin-top:10px;background:white;border-radius:4px; float:left">
                    <div style="margin-top:10px; margin-left:10px;"><span style="float:left; font-size:16px;">比特币</span><span style="float:right; margin-right:10px; color:#EB1B2B; margin-top:1px;">盈利 18.00 元</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-top:13px; margin-left:10px; color:#A8A8A8;"><span style="float:left;">交易单号：</span><span style="float:right; margin-right:10px;">718902</span>
                    </div>
                    <div class="clear"></div>
                    <div style=" margin-left:10px; color:#A8A8A8;"><span style="float:left;">手续费：</span><span style="float:right; margin-right:10px;">20.00 元</span>
                    </div>
                    <div class="clear"></div>
                    <div style=" margin-left:10px; color:#A8A8A8;"><span style="float:left;">周期：</span><span style="float:right; margin-right:10px;">30 秒 90% 收益</span>
                    </div>
                    <div class="clear"></div>
                    <div style=" margin-left:10px; color:#A8A8A8;"><span style="float:left;">方向：</span><span style="float:right; margin-right:10px;">看涨</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-left:10px; color:#A8A8A8;"><span style="float:left;">下单：</span><span style="float:right; margin-right:10px;">10.16 - 03:22:34 - 4044.39</span>
                    </div>
                    <div class="clear"></div>
                    <div style=" margin-left:10px; color:#A8A8A8;"><span style="float:left;">到期：</span><span style="float:right; margin-right:10px;">10.17 - 03:22:34 - 4044.39</span>
                    </div>
                </div>
                <div class="clear"></div>
             -->
            </div>
            <!--
            <div style="width:100%; border:1px solid #F0F0F0; padding-bottom:12px;box-sizing: border-box;margin-top:10px;background:white;border-radius:4px; float:left">
                <div style="margin-top:13px; margin-left:10px; color:#A8A8A8;"><span style="float:left;">交易单号：</span><span style="float:right; margin-right:10px;">718902</span>
                </div>
                <div class="clear"></div>
                <div style=" margin-left:10px; color:#A8A8A8;"><span style="float:left;">手续费：</span><span style="float:right; margin-right:10px;">20.00 元</span>
                </div>
                <div class="clear"></div>
            </div>
            -->
            <div style="width: 100%; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 10px;
                background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
                border-radius: 4px;">
                <div style="height: 25px;" id="jiaoyimingxi">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 25px;">
                        代理商</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 25px;
                        color: #A8A8A8" id="username">
                    </div>
                </div>
                <div class="clear">
                </div>
                <hr style="width: 279px; float: left; margin-top: 9px; margin-left: 10px; border: 0px;
                    height: 1px; background: #EEEEEE" />
                <div class="clear">
                </div>
                
                <div class="yjlb">
                    <div>
                        <p class="y1">
                            一级佣金 <span id="tg1tc"><?php echo ($ratio1); ?>%</span></p>
                        <p>
                            二级佣金 <span id="tg2tc"><?php echo ($ratio2); ?>%</span></p>
                        <p>
                            三级佣金 <span id="tg3tc"><?php echo ($ratio3); ?>%</span></p>
                        <p>
                            四级佣金 <span id="tg4tc"><?php echo ($ratio4); ?>%</span></p>
                        <p>
                            五级佣金 <span id="tg5tc"><?php echo ($ratio5); ?>%</span></p>
                    </div>
                </div>
                <div class="adltx" id="dailiyongjintixian_div">
                    <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/icon.png" height="16">
                    <a href="<?php echo U('index/dailiyongjintixian');?>" style="border-bottom: 1px solid #eee; border-top: 1px solid #eee;">
                        代理佣金微信提现</a>
                </div>
                <!--
                <div class="adltx" id="dbankyongjintx_div">
                    <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/icon.png" height="16">
                    <a href="bankyongjintx.html">代理佣金银行卡提现</a>
                </div>
                 -->
                <style>
                    .adltx
                    {
                        text-align: left;
                    }
                    .adltx a
                    {
                        display: block;
                        padding: 10px;
                        font-size: 16px;
                        color: #000;
                        line-height: 20px;
                    }
                    .adltx img
                    {
                        float: right;
                        margin: 14px 10px 0 0;
                    }
                </style>
            </div>
            <div class="clear">
            </div>
            <div style="width: 240px; color: white; background: #ED5758; margin: 0 auto; border-radius: 4px;
                margin-top: 40px; line-height: 23px; height: 32px; padding-top: 10px; font-size: 16px;"
                id="yaoqing">
                立即邀请</div>
            <div style="font-size: 12px; color: #007AFF; margin-top: 10px;">
                请长按弹出图片保存至相册</div>
            <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; margin-top: 20px;
                color: #A8A8A8;">
                如何推广？</div>
            <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #A8A8A8;
                margin-top: 10px;">
                1、点击"立即邀请", 将弹出的专属二维码保存至手机;</div>
            <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #A8A8A8;">
                2、找到微信好友将专属二维码进行分享;</div>
            <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #A8A8A8;">
                3、好友扫描二维码关注本公众号;</div>
            <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #A8A8A8;">
                4、好友首次登录后即成为您的下线;</div>
            <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #A8A8A8;">
                5、佣金 24 小时随时提现;</div>
            <div style="width: 300px; text-align: left; margin: 0 auto; font-size: 12px; color: #A8A8A8;">
                6、商务合作QQ：320358986;</div>
        </div>
    </div>
    <div style="width: 100%; top: 150px; position: absolute; z-index: 999;">
        <div style="background: #303031; border-radius: 20px; margin: 0 auto; padding: 11px 22px 11px 22px;
            color: white; display: none;" id="modal3">
            操作成功
        </div>
    </div>
    <div style="width: 100%; text-align: center; position: absolute; top: 10px; display: none;
        z-index: 100;" id="qrcode">
        <div id="bgimg">
        </div>
        <div style="color: white; margin-top: 5px;" id="qrtext">
            长按图片保存, 发送给好友注册!</div>
    </div>
    <div id="hidebg">
    </div>
    <!--footer-->
    <div style="border-top: #F0F0F0 1px solid; height: 45px; width: 100%; line-height: 44px;
        background: white; position: fixed; bottom: 0px; text-align: center; font-size: 16px;"
        onclick="location.href='index.php?g=Pig&m=index&a=index'">
        返回首页
    </div>
    <script>
        $("#d3").click(function () {
            location.href = "<?php echo U('index/newjiaoyimingxi');?>";
        });
        //获取地址栏传参
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
        //邀请
        $("#yaoqing").click(function () {
        	
        	 $.ajax({
         		url: 'index.php?g=Agent&m=channelads&a=gen',
         		type: "get",
         		dataType: "json",
         		data: {
         			id:1
         		},
         		success: function (data) {
         			if (data.code == 0)
         			{
                         var html = '<img src="' + data.img + '"  width="85%" />';
                         $("#foot").hide();
                         $("#qrcode").show();
                         $("#hidebg").show();
                         $("#bgimg").html(html);
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