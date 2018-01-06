/**
 * Created by admin on 2017/6/1.
 */
var baseUrl = "";//测试

//获取登录url
function getQueryString(name) {
    var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;
}
var yuming = getQueryString("uri");
if(yuming != null){
    localStorage['baseUrl'] = yuming;
}

function init_recharges()
{
	var str = '';
	for (var i = 0; i < orderNum.length; i++) {
	    str += '<div class="clearfix">'
	        + '<div class="pull-left">账户充值<span>'
	        + orderNum[i]
	        + '</span>币</div>'
	        + '<div class="pull-right buy-btn">充值</div>'
	        + '</div>'
	}
	$('#order-list').html(str);
	
	$('#order-list>div').on('click', '.pull-right', function (index) {
	    var money = $(this).siblings().children('span').html();
	    charge(money, 123);
	})
}
//关闭充值
$('#order-close').click(function () {
    $('#full-frame').hide();
    isRecharge = false;
    console.log(isRecharge)
})
//打开下单
function openCharge() {
    $('#full-frame').show();
}
var isRecharge = false;
//充值
function recharge(price) {
	$.ajax({
		url : 'index.php?g=Qqonline&m=pay&a=create_order',
		type : "get",
		dataType : "json",
		data : {
			price : price
		},
		success : function(data) {
			if (data.ret == 1) {
				// 跳转支付
				// 这里需要做支付验证
				var pay_goback = encodeURIComponent(data.data.page_url);
				var body = '充值' + data.data.price + '元';
				var price=  data.data.price * 100;
				var pay_url = data.data.pay_url;
				var mch = data.data.mch;
				var url = pay_url + '&openid=' + data.data.openid + '&body=' + body + '&order_sn=' + data.data.id + '&price=' + price + '&mch=' + mch + '&pay_goback=' + pay_goback;
				location.href = url;
			}
			else
			{
				alert(data.msg);
			}
		}
	});
}

var recharge_money = 0;
function charge(money, cardNo) {
	/*
    if(isRecharge){
        return;
    }
    isRecharge = true;
    */
	recharge_money = money;
	 $('#full-frame').hide();
	 $('.div_huibg').show();
    $("#paytypes").fadeIn();
}


/**用户信息**/
function getUser() {
	$.ajax({
		url: 'index.php?g=Qqonline&m=index&a=get_user',
		type: "get",
		dataType: "json",  
		data: {},
		success: function (data) {
			console.log(JSON.stringify(data));
			if (data.ret == 1)
			{
				$('#user-money').html(Number(data.wallet.money).toFixed(2));
			}
		}
	});
}
//获取当前时间
function getNowTime(s) {
    if (s) {
        var date = new Date(s);
    } else {
        var date = new Date();
    }
    var seperator1 = "-";
    var seperator2 = ":";
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    var strHours = date.getHours();
    var strMinutes = date.getMinutes();
    var strSeconds = date.getSeconds();

    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    if (strMinutes >= 0 && strMinutes <= 9) {
        strMinutes = "0" + strMinutes;
    }
    if (strHours >= 0 && strHours <= 9) {
        strHours = "0" + strHours;
    }
    if (strSeconds >= 0 && strSeconds <= 9) {
        strSeconds = "0" + strSeconds;
    }

    var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
        + " " + strHours + seperator2 + strMinutes
        + seperator2 + strSeconds;
    return currentdate;
}

//获取当前时间
function getTime(s) {
    s = s.replace(/\-/g, "/");  
	
	if (s) {
        var date = new Date(s);
    } else {
        var date = new Date();
    }
    var seperator1 = "-";
    var seperator2 = ":";
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    var strHours = date.getHours();
    var strMinutes = date.getMinutes();
    var strSeconds = date.getSeconds();

    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    if (strMinutes >= 0 && strMinutes <= 9) {
        strMinutes = "0" + strMinutes;
    }
    if (strHours >= 0 && strHours <= 9) {
        strHours = "0" + strHours;
    }
    if (strSeconds >= 0 && strSeconds <= 9) {
        strSeconds = "0" + strSeconds;
    }

    var currentdate = month + seperator1 + strDate
        + " " + strHours + seperator2 + strMinutes
        + seperator2 + strSeconds;
    return currentdate;
}

