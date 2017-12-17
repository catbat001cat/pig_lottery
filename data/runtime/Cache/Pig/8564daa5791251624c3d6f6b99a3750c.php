<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="user-scalable=no, width=320">
    <title>明细</title>
    <link rel="stylesheet" href="/qqonline_ex/themes/simplebootx_pig_fake/Public/cssdb/index.css">
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.min.js" type="application/javascript"></script>
    <script src="/qqonline_ex/themes/simplebootx_pig_fake/Public/jsdb/jquery.cookie.js" type="application/javascript"></script>
</head>
<body align="center">
    <div class="all">
        <div class="content" style="width: 94%;">
            <div style="width: 100%; margin-top: 10px;">
                <a href="<?php echo U('index/newjiaoyimingxi');?>">
                    <div style="background: white; color: #A8A8A8; width: 141px; height: 38px; float: left;
                        padding-top: 6px; border-radius: 4px; font-size: 16px; border: #EEEEEE 1px solid;
                        line-height: 32px;">
                        夺宝记录</div>
                </a><a href="<?php echo U('index/newzijinmingxi');?>">
                    <div style="background: #ED5758; color: white; width: 141px; height: 40px; float: left;
                        padding-top: 6px; margin-left: 11px; border-radius: 4px; font-size: 16px; line-height: 34px;">
                        夺宝资金</div>
                </a>
            </div>
            <div class="clear">
            </div>
            <div id="zijinmingxi">
                <!--
                <div style="width:100%; border:1px solid #F0F0F0; padding-bottom:10px;box-sizing: border-box;margin-top:10px;background:white;border-radius:4px; float:left">
                    <div style="margin-top:10px; margin-left:10px;"><span style="float:left; font-size:16px;">充值</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-top:10px; margin-left:10px; color:#A8A8A8;"><span style="float:left;">余额 50.00 元</span><span style="float:right; margin-right:10px; color:#DE2F3B">+ 50.00 元</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-top:5px; margin-left:10px; color:#A8A8A8;"><span style="float:left; font-size:12px;">时间： 2016.08.01-12:31-03.22.44</span>
                    </div>
                </div>
                <div class="clear"></div>
                <div style="width:100%; border:1px solid #F0F0F0; padding-bottom:10px;margin-top:10px;box-sizing: border-box;background:white;border-radius:4px; float:left">
                    <div style="margin-top:10px; margin-left:10px;"><span style="float:left; font-size:16px;">充值</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-top:10px; margin-left:10px; color:#A8A8A8;"><span style="float:left;">余额 50.00 元</span><span style="float:right; margin-right:10px; color:#DE2F3B">+ 50.00 元</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-top:5px; margin-left:10px; color:#A8A8A8;"><span style="float:left; font-size:12px;">时间： 2016.08.01-12:31-03.22.44</span>
                    </div>
                </div>
                <div class="clear"></div>
                <div style="width:100%; border:1px solid #F0F0F0; padding-bottom:10px;margin-top:10px;box-sizing: border-box;background:white;border-radius:4px; float:left">
                    <div style="margin-top:10px; margin-left:10px;"><span style="float:left; font-size:16px;">交易</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-top:10px; margin-left:10px; color:#A8A8A8;"><span style="float:left;">余额 50.00 元</span><span style="float:right; margin-right:10px; color:#1DBBA4">- 50.00 元</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-top:5px; margin-left:10px; color:#A8A8A8;"><span style="float:left; font-size:12px;">时间： 2016.08.01-12:31-03.22.44</span>
                    </div>
                </div>
                <div class=”clear“></div>
                <div style="width:100%; border:1px solid #F0F0F0; padding-bottom:10px;margin-top:10px;box-sizing: border-box;background:white;border-radius:4px; float:left">
                    <div style="margin-top:10px; margin-left:10px;"><span style="float:left; font-size:16px;">手续费</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-top:10px; margin-left:10px; color:#A8A8A8;"><span style="float:left;">余额 50.00 元</span><span style="float:right; margin-right:10px; color:#1DBBA4">- 50.00 元</span>
                    </div>
                    <div class="clear"></div>
                    <div style="margin-top:5px; margin-left:10px; color:#A8A8A8;"><span style="float:left; font-size:12px;">时间： 2016.08.01-12:31-03.22.44</span>
                    </div>
                </div>
                <div class="clear"></div>
             -->
            </div>
            <div style="text-align: center; margin-top: 10px; font-size: 12px; color: #A8A8A8;">
                只显示最近 10 次资金明细</div>
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
        var type = ['reward', 'order', 'handle_fee', 'bit_shipan_back', 'bitzs_shipan_back', 'ltc_shipan_back', 'bit_shipan_ping_back', 'bitzs_shipan_ping_back', 'ltc_shipan_ping_back', 'bit_shipan_ying_back', 'bitzs_shipan_ying_back', 'ltc_shipan_ying_back', 'chongzhi', 'tixian', 'yongjin', 'wsdb_shipan_back', 'wsdb_shipan_ping_back', 'wsdb_shipan_ying_back'];
        var yue = parseFloat($.cookie('user_money')) + parseFloat($.cookie('user_reward_money'));
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: '/api1/transaction_list',
            headers: {
                "Authorization": "token=" + $.cookie('user_token') + ",token2=" + $.cookie('user_token2')
            },
            data: { 'start': 0, 'limit': 30, 'type': type, 'is_moni': 0 },
            success: function (data) {
                if (data.code == 200 && data.return_msg != 'null') {
                    for (var i in data.data) {
                        if (i < 10) {
                            var type = '';
                            if (data.data[i].type == 'reward') {
                                type = '红包奖励';
                                //gross =  '+ '+data.data[i].reward_gross+' 元';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].reward_gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'order') {
                                type = '下单';
                                gross = '<span style="float:right; margin-right:10px; color:#1DBBA4">- ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'handle_fee') {
                                type = '手续费';
                                gross = '<span style="float:right; margin-right:10px; color:#1DBBA4">- ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'yongjin') {
                                type = '1级佣金';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            } else if (data.data[i].type == 'ejyongjin') {
                                type = '2级佣金';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            } else if (data.data[i].type == 'sanjiyongjin') {
                                type = '三级佣金';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            } else if (data.data[i].type == 'sijiyongjin') {
                                type = '四级佣金';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'dali3ji') {
                                type = '3级佣金';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'dali4ji') {
                                type = '4级佣金';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'dali5ji') {
                                type = '5级佣金';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }

                            else if (data.data[i].type == 'qiandaohongbao') {
                                type = '签到红包';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'bit_shipan_back') {
                                type = '交割失败';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'bitzs_shipan_back') {
                                type = '交割失败';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'ltc_shipan_back') {
                                type = '交割失败';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'wsdb_shipan_back') {
                                type = '交割失败';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'bit_shipan_ping_back') {
                                type = '平';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'bitzs_shipan_ping_back') {
                                type = '平';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'ltc_shipan_ping_back') {
                                type = '平';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'wsdb_shipan_ping_back') {
                                type = '平';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'bit_shipan_ying_back') {
                                type = '盈利';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'wsdb_shipan_ying_back') {
                                type = '盈利';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'bitzs_shipan_ying_back') {
                                type = '盈利';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'ltc_shipan_ying_back') {
                                type = '盈利';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'chongzhi') {
                                type = '充值';
                                gross = '<span style="float:right; margin-right:10px; color:#DE2F3B">+ ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'tixian') {
                                type = '提现';
                                gross = '<span style="float:right; margin-right:10px; color:#1DBBA4">- ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            else if (data.data[i].type == 'tgtixian') {
                                type = '推广提现';
                                gross = '<span style="float:right; margin-right:10px; color:#1DBBA4">- ' + data.data[i].gross + ' 元</span>';
                                create_time = data.data[i].create_time;
                            }
                            if (type != '' && gross != '' && create_time != '') {
                                html += '<div style="width:100%; border:1px solid #F0F0F0; padding-bottom:10px;box-sizing: border-box;margin-top:10px;background:white;border-radius:4px; float:left; font-size:12px;">' +
									'<div style="margin-top:10px; margin-left:10px;"><span style="float:left; font-size:16px;">' + type + '</span>' +
									'</div>' +
									'<div class="clear"></div>' +
									'<div style="margin-top:10px; margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left;">账户余额 ' + data.data[i].remain_money + ' 元</span>' +

										gross +
									'</div>' +
									'<div class="clear"></div>' +
									'<div style="margin-top:3px; margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left;">红包余额 ' + data.data[i].remain_reward_money + ' 元</span>' +
									'</div>' +
//									'<div class="clear"></div>' +
//                                    '<div style="margin-top:3px; margin-left:10px; color:#A8A8A8;">' +
//										'<span style="float:left;">佣金余额 ' + data.data[i].tg_yongjin + ' 元</span>' +
//									'</div>' +
									'<div class="clear"></div>' +
									'<div style="margin-top:5px; margin-left:10px; color:#A8A8A8;">' +
										'<span style="float:left; font-size:10px;">' + create_time + '</span>' +
									'</div>' +
            				  '</div>' +
							  '<div class="clear"></div>';
                            }
                        }
                    }
                    $("#zijinmingxi").html(html);
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