<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="format-detection" content="telephone=no">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"
        name="viewport" />
    <meta name="keywords" content="签到">
    <meta name="description" content="签到">
    <meta name="author" content="签到">
    <title>签到</title>
    <link href="/qqonline_ex/themes/simplebootx_pig_fake/Public/css/style.css" rel="stylesheet" type="textcss">
    <link href="/qqonline_ex/themes/simplebootx_pig_fake/Public/css/common.css" rel="stylesheet" type="textcss" />
    <link href="/qqonline_ex/themes/simplebootx_pig_fake/Public/css/app.css" rel="stylesheet" type="textcss" />
    <link href="/qqonline_ex/themes/simplebootx_pig_fake/Public/css/lebao.css" rel="stylesheet" type="textcss" />
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.min.js"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.cookie.js"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/js/login.js"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/js/clipboard.js"></script>
    <style>
        .bjbai{
            background: #ffeed6;
        }
        .qdan{
            margin-top: 10px;
            text-align: center;
        }
        #btnsigned{
            background: #f25f55;
            padding: 14px 80px;
            margin: 0 auto;
            border-radius: 100px;
            border: 1px solid #e8463b;
            color: #fff;
            font-size: 20px;
        }
        .jryqd{
            background: #ff827a;
            padding: 14px 80px;
            margin: 0 auto;
            border-radius: 100px;
            border: 1px solid #ff827a;
            color: #fff;
            font-size: 20px;
        }
        .qdwz{
            margin-top: 10px;
            text-align: center;
            color: #e8463b;
        }
        .cu{
            font-weight: bold;
        }
    </style>
</head>
<body class="bjbai">
    <div>
        <img src="/qqonline_ex/themes/simplebootx_pig_fake/Public/img/qiandaobg.jpg" alt="" width="100%">
    </div>
    <div class=" container-fluid margin-top-30">
        <div class="qdan">
                <button type="button" id="btnsigned" name="btnsigned">
                    签到<span class="span1 vw4 hong">￥1.00</span></button>
        </div>
        <div class="col-xs-2">
        </div>
    </div>
    <div class="qdwz">
            <span>您还未签到哦，赶紧签到吧</span>
    </div>
    <div style="margin:20px; font-size: 16px; text-align: center;">
        签到总获得<span style="font-weight: bold;"> 0.00 </span>元
    </div>
    <div style="margin:20px; font-size: 12px;">
        签到说明：每天签到可获得1元交易红包<span style=" color: #999">（交易使用，不能直接提现）</span>
        
    </div>
    <script src="js/modal.js"></script>
            <script src="js/alert.js"></script>              
    <script type="text/javascript">
		return;
        $(function () {
            $('#btnsigned').on('click', function () {
                $('#btnsigned').text('签到中……');
                $.ajax({
                    type: 'post',
                    dataType: "json",
                    url: '/api1/user_signed',
                    async: false,
                    headers: {
                        "Authorization": "token=" + $.cookie('user_token') + ",tel=" + $.cookie('user_token2')
                    },
                    success: function (data) {
                        if (data.code == 1) {
                            alertMsg(data.messages, '/index/signed');
                        } else {
                            if (data.code == -2) {
                                alertMsg(data.messages, '');
                            } else {
                                alertMsg(data.messages, '');
                            }
                        }
                    },
                    error: function () {
                    }
                })

            })

        })
    </script>
                <!--footer-->
    <div style="border-top: #F0F0F0 1px solid; height: 45px; width: 100%; line-height: 44px;
        background: white; position: fixed; bottom: 0px; text-align: center; font-size: 16px;"
        onclick="location.href='/'">
        返回首页
    </div>
</body>
</html>