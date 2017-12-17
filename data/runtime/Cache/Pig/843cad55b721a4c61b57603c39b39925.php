<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="user-scalable=no, width=320">
    <title>交易明细</title>
    <link rel="stylesheet" href="/qqonline_ex/themes/simplebootx_pig_fake/Public/cssdb/index.css">
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.min.js" type="application/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.cookie.js" type="application/javascript"></script>
</head>
<body align="center">
    <div class="all">
        <div class="content" style="width: 94%;">
            <div style="width: 100%; margin-top: 10px;">
                <a href="<?php echo U('index/newjiaoyimingxi');?>">
                    <div style="background: #337AB7; color: white; width: 141px; height: 40px; float: left;
                        padding-top: 6px; border-radius: 4px; font-size: 16px; line-height: 34px;">
                        夺宝记录</div>
                </a><a href="<?php echo U('index/newzijinmingxi');?>">
                    <div style="background: white; color: #A8A8A8; width: 141px; height: 38px; float: left;
                        padding-top: 6px; margin-left: 11px; border-radius: 4px; border: #EEEEEE 1px solid;
                        font-size: 16px; line-height: 32px;">
                        夺宝资金</div>
                </a>
            </div>
            <div class="clear">
            </div>
            <div id="jiaoyimingxi">
                <!--
                <div style="width:100%; border:1px solid #F0F0F0; padding-bottom:12px;box-sizing: border-box;margin-top:10px;background:white;border-radius:4px; float:left">
                    <div style="margin-top:10px; margin-left:10px;"><span style="float:left; font-size:16px;">比特币</span><span style="float:right; margin-right:10px; color:#EB1B2B; margin-top:1px;">盈利 18.00 元</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-top:13px; margin-left:10px; color:#A8A8A8;"><span style="float:left;">平台单号：</span><span style="float:right; margin-right:10px;">718902</span>
                    </div>
                    <div class="clear"></div>
                    <div style=" margin-left:10px; color:#A8A8A8;"><span style="float:left;">手续费：</span><span style="float:right; margin-right:10px;">20.00 元</span>
                    </div>
                    <div class="clear"></div>
                    <div style=" margin-left:10px; color:#A8A8A8;"><span style="float:left;">周期：</span><span style="float:right; margin-right:10px;">30 秒 90% 夺宝盈利</span>
                    </div>
                    <div class="clear"></div>
                    <div style=" margin-left:10px; color:#A8A8A8;"><span style="float:left;">判断方向：</span><span style="float:right; margin-right:10px;">看涨</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-left:10px; color:#A8A8A8;"><span style="float:left;">下单：</span><span style="float:right; margin-right:10px;">10.16 - 03:22:34 - 4044.39</span>
                    </div>
                    <div class="clear"></div>
                    <div style=" margin-left:10px; color:#A8A8A8;"><span style="float:left;">平台时间：</span><span style="float:right; margin-right:10px;">10.17 - 03:22:34 - 4044.39</span>
                    </div>
                </div>
                <div class="clear"></div>
             -->
            </div>
            <!--
            <div style="width:100%; border:1px solid #F0F0F0; padding-bottom:12px;box-sizing: border-box;margin-top:10px;background:white;border-radius:4px; float:left">
                <div style="margin-top:13px; margin-left:10px; color:#A8A8A8;"><span style="float:left;">平台单号：</span><span style="float:right; margin-right:10px;">718902</span>
                </div>
                <div class="clear"></div>
                <div style=" margin-left:10px; color:#A8A8A8;"><span style="float:left;">手续费：</span><span style="float:right; margin-right:10px;">20.00 元</span>
                </div>
                <div class="clear"></div>
            </div>
            -->
            <div style="text-align: center; margin-top: 10px; font-size: 12px; color: #A8A8A8;">
                只显示最近 10 次交易结果</div>
        </div>
    </div>
    <!--footer-->
    <div style="border-top: #F0F0F0 1px solid; height: 45px; width: 100%; line-height: 44px;
        background: white; position: fixed; bottom: 0px; text-align: center; font-size: 16px;"
        onclick="location.href='/'">
        返回首页
    </div>
    <script>
		return;
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
//        var id = getUrlParameter('id');
//        if (typeof (id) != 'undefined') {
//            $.cookie('user_qudao', id, { expires: 1 });
//        }
        if (typeof ($.cookie('user_token')) == 'undefined' || $.cookie('user_token') == 'null') {
            location.href = "/";
        }
        var html = '';
        var gross = '';
        var create_time = '';
        var yue = parseFloat($.cookie('user_money')) + parseFloat($.cookie('user_reward_money'));
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/api1/order_list',
            headers: {
                "Authorization": "token=" + $.cookie('user_token') + ",token2=" + $.cookie('user_token2')
            },
            data: { 'type': 'all', 'is_moni': 0, 'start': 0, 'limit': 30, 'status': [2, 3], 'is_read': 'all' },
            success: function (data) {
                if (data.code == 200 && data.return_msg != 'null') {
                    for (var i in data.data) {
                        if (i < 10) {
                            if (data.data[i].type == 'bit') {
                                var type = '当天现货';
                                if (data.data[i].trend == 'up') {
                                    var trend = '看涨';
                                }
                                else {
                                    var trend = '看跌';
                                }
                                var xiadandaoqi = '<div style="margin-left:10px; color:#A8A8A8;">' +
													'<span style="float:left;">下单：</span>' +
													'<span style="float:right; margin-right:10px;">' + data.data[i].place_time_format + ' - ' + data.data[i].place_value + '</span>' +
												'</div>' +
												'<div class="clear"></div>';
                            }
                            else if (data.data[i].type == 'bitzs') {
                                var type = '结果';
                                if (data.data[i].trend == 'up') {
                                    var trend = '看涨';
                                }
                                else if (data.data[i].trend == 'down') {
                                    var trend = '猜小';
                                } else if (data.data[i].trend == 'dan') {
                                    var trend = '猜单';
                                } else if (data.data[i].trend == 'suang') {
                                    var trend = '猜双';
                                }
                                var xiadandaoqi = '';
                            }
                            else if (data.data[i].type == 'wsdb') {
                                var type = '结果';
                                if (data.data[i].trend == 'up') {
                                    var trend = '猜大';
                                }
                                else if (data.data[i].trend == 'down') {
                                    var trend = '猜小';
                                } else if (data.data[i].trend == 'dan') {
                                    var trend = '猜单';
                                } else if (data.data[i].trend == 'suang') {
                                    var trend = '猜双';
                                }
                                var xiadandaoqi = '';
                            }
                            else {
                                var type = '莱特币';
                            }
                            if (data.data[i].status == 3) {
                                var money = '<span style="float:right; margin-right:10px; color:#A8A8A8; margin-top:1px;font-size:14px;">交割失败</span>'
                                var handlefee = '退回';
                                var title = '盈利';
                                var trans = '<span style="float:right; margin-right:10px;">+ ' + data.data[i].trans_result_money + ' 元</span>';
                            }
                            else if (data.data[i].result == 1) {
                                if (data.data[i].order_time_out == 90) {
                                    var mon = data.data[i].trans_result_money;
                                    if (String(mon).indexOf(".") > -1) {
                                        mon += '0';
                                    }
                                    else {
                                        mon += '.00'
                                    }
                                }
                                else {
                                    var mon = data.data[i].trans_result_money;
                                }
                                var money = '<span style="float:right; margin-right:10px; color:#EB1B2B; margin-top:1px;font-size:14px;">盈利 ' + mon + ' 元</span>';
                                var title = '盈利';
                                var trans = '<span style="float:right; margin-right:10px;">+ ' + data.data[i].trans_result_money + ' 元</span>';
                            }
                            else if (data.data[i].result == 2) {
                                if (data.data[i].order_time_out == 90) {
                                    var mon = data.data[i].trans_result_money;
                                    if (String(mon).indexOf(".") > -1) {
                                        mon += '0';
                                    }
                                    else {
                                        mon += '.00'
                                    }
                                }
                                else {
                                    var mon = data.data[i].trans_result_money;
                                }
                                var money = '<span style="float:right; margin-right:10px; color:#1DBBA4; margin-top:1px;font-size:14px;">亏损 ' + mon + ' 元</span>';
                                var title = '亏损';
                                var trans = '<span style="float:right; margin-right:10px;">- ' + data.data[i].trans_result_money + ' 元</span>';
                            }
                            else if (data.data[i].result == 3) {
                                var money = '<span style="float:right; margin-right:10px;  margin-top:1px;color:#007AFF;font-size:14px;">平手</span>';
                                var title = '亏损';
                                var trans = '<span style="float:right; margin-right:10px;">+ ' + data.data[i].trans_result_money + ' 元</span>';
                            }
                            if (data.data[i].order_time_out == 10 && data.data[i].type != 'bitzs') {
                                var persent = '70%';
                            }
                            else if (data.data[i].order_time_out == 15 && data.data[i].type != 'bitzs') {
                                var persent = '70%';
                            }
                            else if (data.data[i].order_time_out == 20) {
                                var persent = '80%';
                            }
                            else if (data.data[i].order_time_out == 30) {
                                var persent = '80%';
                            }
                            else if (data.data[i].order_time_out == 60) {
                                var persent = '90%';
                            }
                            else if (data.data[i].order_time_out == 90) {
                                var persent = '95%';
                            }
                            else {
                                var persent = '100%';
                            }
                            var handlefee = data.data[i].handle_fee;
                            html += '<div style="width:100%; border:1px solid #F0F0F0; padding-bottom:12px;box-sizing: border-box;margin-top:10px;background:white;border-radius:4px; float:left;font-size:12px;">' +
									'<div style="margin-top:10px; margin-left:10px;">' +
										'<span style="float:left; font-size:16px;">' + type + '</span>' +
										money +
									'</div>' +
									'<div class="clear"></div>' +
									'<div style="margin-top:13px; margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left;">平台单号：</span>' +
										'<span style="float:right; margin-right:10px;">' + data.data[i].id + '</span>' +
									'</div>' +
									'<div class="clear"></div>' +
									'<div style=" margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left;">下单：</span>' +
										'<span style="float:right; margin-right:10px;">' + data.data[i].trans_fee + ' 元</span>' +
									'</div>' +
									'<div class="clear"></div>' +
									'<div style=" margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left;">判断方向：</span>' +
										'<span style="float:right; margin-right:10px;">' + trend + '</span>' +
									'</div>' +
									'<div class="clear"></div>' +
									'<div style=" margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left;">微信单号：</span>' +
										'<span style="float:right; margin-right:10px;">' + data.data[i].wx_trans_id + '</span>' +
									'</div>' +
									'<div class="clear"></div>' +
									'<div style=" margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left;">' + title + '：</span>' +
										trans +
									'</div>' +
									'<div class="clear"></div>' +
									xiadandaoqi +
									'<div style=" margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left;">平台时间：</span>' +
										'<span style="float:right; margin-right:10px;">' + data.data[i].expect_result_time + '</span>' +
									'</div>' +
               				'</div>' +
                			'<div class="clear"></div>';
                            html += '<div style="width:100%; border:1px solid #F0F0F0; padding-bottom:12px;box-sizing: border-box;margin-top:10px;background:white;border-radius:4px; float:left;font-size:12px;">' +
							'<div style="margin-top:13px; margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left;">平台单号：</span>' +
										'<span style="float:right; margin-right:10px;">' + data.data[i].id + '</span>' +
							'</div>' +
							'<div class="clear"></div>' +
									'<div style=" margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left;">手续费：</span>' +
										'<span style="float:right; margin-right:10px;">- ' + handlefee + ' 元</span>' +
									'</div>' +
							'</div>' +
							'<div class="clear"></div>';
                        }
                    }
                    $("#jiaoyimingxi").html(html);
                }

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (XMLHttpRequest.status == 401) {
                    location.href = '/';
                }
            }
        });
    </script>
</body>
</html>