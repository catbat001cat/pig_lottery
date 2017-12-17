<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="user-scalable=no, width=320">
    <title>代理赚钱</title>
    <link rel="stylesheet" href="/qqonline_ex/themes/simplebootx_pig_fake/Public/cssdb/index.css" />
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.min.js" type="application/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.cookie.js" type="application/javascript"></script>
    <style>
        .adltx
        {
            text-align: left;
        }
        .adltx span
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
            margin: 0px 10px 0 0;
        }
    </style>
</head>
<body align="center">
    <div class="all">
        <div class="content" style="width: 94%;">
            <div style="width: 100%; margin-top: 10px;">
                <a href="<?php echo U('index/daili1');?>">
                    <div style="background: white; color: #A8A8A8; width: 91px; height: 38px; float: left;
                        padding-top: 6px; border-radius: 4px; font-size: 16px; line-height: 32px; border: #EEEEEE 1px solid;">
                        代理</div>
                </a><a href="<?php echo U('index/daili2');?>">
                    <div style="background: #ED5758; color: white; width: 93px; height: 40px; float: left;
                        padding-top: 6px; margin-left: 11px; border-radius: 4px; font-size: 16px; line-height: 34px;">
                        佣金</div>
                </a><a href="<?php echo U('index/daili3');?>">
                    <div style="background: white; color: #A8A8A8; width: 91px; height: 38px; float: left;
                        padding-top: 6px; margin-left: 10px; border-radius: 4px; border: #EEEEEE 1px solid;
                        font-size: 16px; line-height: 32px;">
                        下线</div>
                </a>
            </div>
            <div class="clear">
            </div>
            <div class="adltx" id="dailiyongjintixian_div">
                <table style="width:99%;">
                    <tr>
                        <td>
                            <span style="border-bottom: 1px solid #eee; border-top: 1px solid #eee;">
                                代理佣金微信提现</span>
                        </td>
                        <td>
                            <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/imgdb/icon.png" height="16">
                        </td>
                    </tr>
                </table>
            </div>
            <!--****************************************1级-->
            <div style="width: 100%; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 10px;
                background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
                border-radius: 4px;">
                <div style="height: 25px;" id="jiaoyimingxi">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 25px;">
                        (1级)累计注册</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 25px;
                        color: #A8A8A8">
                        <font id="leiji"><?php echo ($level1_childusers); ?></font> 人</div>
                </div>
                <div class="clear">
                </div>

                <div style="height: 25px;" id="jiaoyimingxi">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 25px;">
                        (1级)累计佣金</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 25px;
                        color: #ED5758">
                        <font id="leijiyongjin"><?php echo ($total1); ?></font> 元</div>
                </div>
                <div class="clear">
                </div>
            </div>

            <div style="clear: both">
            </div>
            <!--****************************************1级-->
            <!--****************************************2级-->
            <div style="width: 100%; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 10px;
                background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
                border-radius: 4px;">
                <div style="height: 25px;" id="erjireg_div">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 25px;">
                        (2级)累计注册</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 25px;
                        color: #A8A8A8">
                        <font id="erjireg"><?php echo ($level2_childusers); ?></font> 人</div>
                </div>
                <div class="clear">
                </div>

                <div style="height: 25px;" id="erjileijiyongjin_div">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 8px;">
                        (2级)累计佣金</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 8px;
                        color: #ED5758">
                        <font id="erjileijiyongjin"><?php echo ($total2); ?></font> 元</div>
                </div>
                <div class="clear">
                </div>
            </div>

            <!--****************************************2级-->
            <!--****************************************3级-->
            <div style="width: 100%; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 10px;
                background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
                border-radius: 4px;">
                <div style="height: 25px;" id="tg3reg_div">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 25px;">
                        (3级)累计注册</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 25px;
                        color: #A8A8A8">
                        <font id="tg3reg"><?php echo ($level3_childusers); ?></font> 人</div>
                </div>
                <div class="clear">
                </div>

                <div style="height: 25px;" id="tg3yongjin_div">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 8px;">
                        (3级)累计佣金</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 8px;
                        color: #ED5758">
                        <font id="tg3yongjin"><?php echo ($total3); ?></font> 元</div>
                </div>
                <div class="clear">
                </div>
            </div>

            <!--****************************************3级-->
            <!--****************************************4级-->
            <div style="width: 100%; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 10px;
                background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
                border-radius: 4px;">
                <div style="height: 25px;" id="tg4reg_div">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 25px;">
                        (4级)累计注册</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 25px;
                        color: #A8A8A8">
                        <font id="tg4reg"><?php echo ($level4_childusers); ?></font> 人</div>
                </div>
                <div class="clear">
                </div>

                <div style="height: 25px;" id="tg4yongjin_div">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 8px;">
                        (4级)累计佣金</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 8px;
                        color: #ED5758">
                        <font id="tg4yongjin"><?php echo ($total4); ?></font> 元</div>
                </div>
                <div class="clear">
                </div>
            </div>

            <!--****************************************4级-->
            <!--****************************************5级-->
            <div style="width: 100%; border: 1px solid #EEEEEE; margin: 0 auto; margin-top: 10px;
                background: white; float: left; box-sizing: border-box; padding-bottom: 10px;
                border-radius: 4px;">
                <div style="height: 25px;" id="tg5reg_div">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 25px;">
                        (5级)累计注册</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 25px;
                        color: #A8A8A8">
                        <font id="tg5reg"><?php echo ($level5_childusers); ?></font> 人</div>
                </div>
                <div class="clear">
                </div>

                <div style="height: 25px;" id="tg5yongjin_div">
                    <div style="margin-top: 10px; float: left; margin-left: 10px; font-size: 16px; color: black;
                        line-height: 8px;">
                        (5级)累计佣金</div>
                    <div style="float: right; margin-top: 10px; margin-right: 20px; line-height: 8px;
                        color: #ED5758">
                        <font id="tg5yongjin"><?php echo ($total5); ?></font> 元</div>
                </div>
                <div class="clear">
                </div>
            </div>

            <!--****************************************5级-->
            <div style="margin-left: 10px; margin-top: 10px; color: #A8A8A8; text-align: left;
                font-size: 12px;">
                佣金实时结算至现金余额账户, 可实时提现;</div>
        </div>
        <div style="width: 100%; top: 150px; position: absolute; z-index: 999;">
            <div style="background: #303031; border-radius: 20px; margin: 0 auto; padding: 11px 22px 11px 22px;
                color: white; display: none;" id="modal3">
                操作成功
            </div>
        </div>
        <!--footer-->
        <div style="border-top: #F0F0F0 1px solid; height: 45px; width: 100%; line-height: 44px;
            background: white; position: fixed; bottom: 0px; text-align: center; font-size: 16px;"
            onclick="location.href='index.php?g=Pig&m=index&a=index'">
            返回首页
        </div>
    </div>
    <script>
        $("#dailiyongjintixian_div").click(function () {
        	location.href = "<?php echo U('index/dailiyongjintixian');?>";
        });

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
    </script>
</body>
</html>