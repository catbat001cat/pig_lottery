window.location.href = "#home";
$.ajaxSetup({
	cache: false
});
var token = getLocationParam("token");
if (token != null) {
	setToken(token)
} else {
	token = getToken()
}
var amount = 0;
setAmount(amount);
var userNo = "";
$('.name').text("会员ID:" + userNo);
var dialogDiv = false;
var apiHomeSrc = location.origin;
var hbturetableObj = {
	'apiList': {
		'openDisc': apiHomeSrc + '/index.php/User/Zhuanpan/bet.html',
		'payOpenDisc': apiHomeSrc + '/index.php/User/Zhuanpan/rechargeBet',
		'weixinPay': apiHomeSrc + '/pay',
		'enPayment': apiHomeSrc + '/index.php/User/Zhuanpan/withdrawCash',
		'getTransferRecord': apiHomeSrc + '/index.php/User/Zhuanpan/queryByTypeWithDrawList',
		'accountInfo': apiHomeSrc + '/index.php/User/Zhuanpan/getAccountInfo',
		'getCommissionList': apiHomeSrc + '/yongjin/getBrokerageRecord',
		'queryOrder': apiHomeSrc + '/index.php/User/Zhuanpan/queryOrderStatus',
		'getWaitOpenDiscOrder': apiHomeSrc + '/index.php/User/Zhuanpan/getWaitOpenDisc',
		'tixian': apiHomeSrc + '/index.php/User/Daili/tixian',
		'getAgentUrl': apiHomeSrc + '/index.php/User/Zhuanpan/userCodeUrl',
		'getAgentCount': apiHomeSrc + '/index.php/User/Zhuanpan/getAgentCount',
		'getGamePromote': apiHomeSrc + '/index.php/User/Zhuanpan/getGamePromote',
		'getTransferCount': apiHomeSrc + '/index.php/User/Zhuanpan/getTransferCount',
		'getWinList': apiHomeSrc + '/index.php/User/Zhuanpan/windiscLogList'
	}
};
$(function() {
	$("#sureDraw").on("click", function() {
		$(".dialog").addClass("cpm-hide");
		if ($("#sureDraw").text() == "立即抽奖") {
			$(".mask_blank").removeClass("cpm-hide");
			openDisc()
		} else if ($("#sureDraw").text() == "确认") {
			$(".dialog").addClass("cpm-hide")
		}
	});
	$(".tk").on("click", function() {
		$(".toast_tk").addClass("cpm-hide")
	});
	$(".sureDraw_left").on("click", function() {
		$(".dialog").addClass("cpm-hide");
		withdraw(1)
	});
	$(".naChanceMsg_2 .yj_sure").on("click", function() {
		$(".dialog_yj").addClass("cpm-hide")
	});
	$(".dialog .sureDraw_right").on("click", function() {
		$(".dialog").addClass("cpm-hide")
	});
	$(".cash_postal").on("click", function() {
		if (amount >= 1) {
			var getTransferCountParams = {
				"transferType": "1"
			};
			$.ajax({
				type: "POST",
				url: hbturetableObj.apiList.getTransferCount,
				contentType: "application/json",
				data: JSON.stringify(getTransferCountParams),
				dataType: "json",
				async: false,
				beforeSend: function(request) {
					request.setRequestHeader("X-Auth-Token", token)
				},
				success: function(data) {
					if (data.data != null) {
						var transferCount = data.data;
						if (transferCount <= 0) {
							$(".dialog .naChanceMsg_1").children("p:eq(0)").html("您今日的提现次数不足<br>不能提现!");
							$(".btn_stype_1").removeClass("cpm-hide");
							$(".btn_stype_2").addClass("cpm-hide");
							$("#sureDraw").text("确认");
							$(".dialog").removeClass("cpm-hide")
						} else {
							  if (data.noauto=="1") {
                                     window.location.href = "/index.php/User/Daili/tixian.html"
							  }else{

                                    $(".dialog .naChanceMsg_1").children("p:eq(0)").html("您今日能提现剩余" + transferCount + "次<br>是否要提现?");
									$(".btn_stype_1").addClass("cpm-hide");
									$(".btn_stype_2").removeClass("cpm-hide");
									$(".dialog").removeClass("cpm-hide");

							  }
							
						}
					}
				},
				error: function(error) {
					var eData = JSON.parse(error.responseText);
					if (eData != null) {
						showToast(eData.msg)
					}
				}
			})
		} else {
			$(".dialog .naChanceMsg_1").children("p:eq(0)").html("您能提现的余额不足1元<br>不能提现!");
			$(".btn_stype_1").removeClass("cpm-hide");
			$(".btn_stype_2").addClass("cpm-hide");
			$("#sureDraw").text("确认");
			$(".dialog").removeClass("cpm-hide")
		}
	});
	getAccountInfo()
});

function getAccountInfo() {
	var getAccountInfoParam = {};
	$.ajax({
		type: "POST",
		url: hbturetableObj.apiList.accountInfo,
		contentType: "application/json",
		data: JSON.stringify(getAccountInfoParam),
		dataType: "json",
		beforeSend: function(request) {
			request.setRequestHeader("X-Auth-Token", token)
		},
		success: function(data) {
			if (data.data != null) {
				amount = (parseFloat(data.data.amount) / 100).toFixed(2);
				userNo = data.data.userNo;
				$('.name').text("会员ID:" + userNo);
				setAmount(amount)
			}
		},
		error: function(error) {}
	})
}
function weixinPay(totalFee) {
	var params = {
		"title": "充值",
		"totalFee": totalFee
	};
	$.ajax({
		type: "POST",
		url: hbturetableObj.apiList.weixinPay,
		contentType: "application/json",
		data: JSON.stringify(params),
		dataType: "json",
		beforeSend: function(request) {
			request.setRequestHeader("X-Auth-Token", token)
		},
		success: function(data) {
			if (data.data != null) {}
		},
		error: function(error) {}
	})
};

function getLocationParam(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
	var r = window.location.search.substr(1).match(reg);
	if (r != null) {
		return unescape(r[2])
	}
	return null
};

function showToast(content) {
	$(".toast_tk").removeClass("cpm-hide");
	$(".toast_tk .naChanceMsg_tk").children("p:eq(0)").html(content)
};

function setAmount(amount) {
	$('.amount').text(parseFloat(amount))
};

function withdraw(transferType) {
	showdiv();
	var params = {
		"remark": "提现",
		"transferType": transferType
	};
	$.ajax({
		type: "POST",
		url: hbturetableObj.apiList.enPayment,
		contentType: "application/json",
		data: JSON.stringify(params),
		dataType: "json",
		beforeSend: function(request) {
			request.setRequestHeader("X-Auth-Token", token)
		},
		success: function(data) {
			closediv();
			/*if (data.data != null) {
				showToast(data.errorMsg);
				if (transferType == 1) {
					amount = 0;
					setAmount(amount)
				} else {
					yongjinAmount = 0;
					setYongjinAmount(yongjinAmount)
				}
			} else {
				showToast(data.errorMsg)
			}*/
			if (data.data != null) {
				showToast(data.errorMsg);
				if(data.data==1){
					if (transferType == 1) {
						amount = 0;
						setAmount(amount)
					} else {
						yongjinAmount = 0;
						setYongjinAmount(yongjinAmount)
						window.location.href="#yongJinJiLu";
					}
				}else if(data.data==2){
					window.location.href=hbturetableObj.apiList.tixian+'?typef='+transferType;
				}
			} else {
				showToast(data.errorMsg)
			}
			
		},
		error: function(error) {
			var eData = JSON.parse(error.responseText);
			if (eData != null) {
				showToast(eData.msg);
				if (eData.code == '0015') {
					if (transferType == 1) {
						amount = 0;
						setAmount(amount)
					} else {
						yongjinAmount = 0;
						setYongjinAmount(yongjinAmount)
					}
				}
			}
			closediv()
		}
	})
}
function closediv() {
	$('.loading').hide()
}
function getToken() {
	return localStorage.getItem("hbturetableToken")
}
function setToken(token) {
	localStorage.setItem("hbturetableToken", token)
}
function showdiv() {
	$('.loading').show()
}
document.onkeydown = mykeydown;

function mykeydown() {
	if (event.keyCode == 116) {
		window.event.keyCode = 0;
		return false
	}
}