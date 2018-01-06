var orderNo;
var totalFee;
var data_disc;
var discType;
var callbackParam = getLocationParam("callbackParam");
if (callbackParam != null) {
	var callArray = callbackParam.split(",");
	if (callArray[0] == 3) {
		orderNo = callArray[1];
		totalFee = callArray[2];
		discType = callArray[3];
		if (discType == 1) {
			data_disc = 'little'
		} else if (discType == 2) {
			data_disc = 'middle'
		} else {
			data_disc = 'big'
		}
	}
}
var disc_size;
var timerId;
var timerCount = 0;
var win_prize_id;
var win_times = 3;
var data_rmb;
var swiper;
$(function() {
	if (totalFee != null) {
		$(".tab2 .tab2_actives").removeClass("tab2_actives");
		var p = $(".tab2 .tab_item");
		var pLenght = p.length;
		for (var i = 0; i < pLenght; i += 1) {
			if (parseInt($(p[i]).attr("data-type") * 100) == totalFee) {
				$(p[i]).addClass("tab2_actives");
				break
			}
		}
	}
	if (data_rmb == null) {
		if ($(".tab_item").hasClass("tab2_actives")) {
			data_rmb = $(".tab2 .tab2_actives").attr("data-type")
		}
	} else {
		$(".tab2 .tab2_actives").removeClass("tab2_actives");
		var p = $(".tab2 .tab_item");
		var pLenght = p.length;
		for (var i = 0; i < pLenght; i += 1) {
			if (parseInt($(p[i]).attr("data-type")) == data_rmb) {
				$(p[i]).addClass("tab2_actives");
				break
			}
		}
	}
	if (data_disc != null) {
		$(".tab1 .tab_actives").removeClass("tab_actives");
		$("." + data_disc).addClass("tab_actives");
		$("#" + data_rmb + "_" + data_disc).children("img").attr("src", "http://oqnv2oygi.bkt.clouddn.com/" + data_rmb + "_rmbn_" + data_disc + ".png");
		$("#" + data_rmb + "_" + data_disc).removeClass("cpm-hide").addClass("star_disc").siblings().addClass("cpm-hide").removeClass("star_disc")
	} else {
		if ($(".tab_item_disc").hasClass("tab_actives")) {
			data_disc = $(".tab_item_disc").attr("data-type")
		}
	}
	$(".tab_item_disc").on("click", function() {
		$(this).addClass("tab_actives").siblings().removeClass("tab_actives");
		data_disc = $(this).attr("data-type");
		$("#" + data_rmb + "_" + data_disc).children("img").attr("src", "http://oqnv2oygi.bkt.clouddn.com/" + data_rmb + "_rmbn_" + data_disc + ".png");
		$("#" + data_rmb + "_" + data_disc).removeClass("cpm-hide").addClass("star_disc").siblings().addClass("cpm-hide").removeClass("star_disc")
	})
});
$(function() {
	$(".tab_item").on("click", function() {
		$(this).addClass("tab2_actives").siblings().removeClass("tab2_actives");
		data_rmb = $(this).attr("data-type");
		$("#" + data_rmb + "_" + data_disc).children("img").attr("src", "http://oqnv2oygi.bkt.clouddn.com/" + data_rmb + "_rmbn_" + data_disc + ".png");
		$("#" + data_rmb + "_" + data_disc).removeClass("cpm-hide").addClass("star_disc").siblings().addClass("cpm-hide").removeClass("star_disc")
	});
	$(".qx").on("click", function() {
		$(".prize_box").addClass("cpm-hide");
		$(".mask_blank").addClass("cpm-hide")
	})
});
$(function() {
	showdiv("请稍候...");
	queryWaitOpenOrder();
	$(".cz .chongzhi").on("click", function() {
		if ($(".cz .chongzhi").text() == "立即抽奖") {
			$(".prize_box").addClass("cpm-hide");
			$(".mask_blank").removeClass("cpm-hide");
			openDisc()
		} else if ($(".cz .chongzhi").text() == "前往支付") {
			$(".prize_box").addClass("cpm-hide");
			$(".cz").addClass("cpm-hide");
			payOpenDisc()
		}
	});
	$("#start").on("click", function() {
		data_rmb = $(".tab2 .tab2_actives").attr("data-type");
		alert(111);
		if (parseFloat(data_rmb) > parseFloat(amount)) {
			$(".cz").removeClass("cpm-hide");
			$(".zj").addClass("cpm-hide");
			$(".cz .prize_txt").html("您的帐户余额不足<br>需要支付" + parseFloat(data_rmb) + "元进行抽奖!");
			$(".prize_box").removeClass("cpm-hide");
			$(".cz .chongzhi").text("前往支付");
			$(".mask_blank").addClass("cpm-hide")
		} else {
			$(".cz").removeClass("cpm-hide");
			$(".zj").addClass("cpm-hide");
			$(".cz .prize_txt").html("本次抽奖需扣除" + parseFloat(data_rmb) + "金币<br>是否立即抽奖？");
			$(".prize_box").removeClass("cpm-hide");
			$(".cz .chongzhi").text("立即抽奖")
		}
	});
	$(".prize_content .goDraw").on("click", function() {
		$(".prize_box").addClass("cpm-hide");
		$(".mask_blank").addClass("cpm-hide");
		setAmount(amount)
	})
});
var winParams = {};
$.ajax({
	type: "POST",
	url: hbturetableObj.apiList.getWinList,
	contentType: "application/json",
	data: JSON.stringify(winParams),
	dataType: "json",
	beforeSend: function(request) {
		request.setRequestHeader("X-Auth-Token", token)
	},
	success: function(data) {
		if (data.data != null) {
			var list = data.data;
			var htmlC = '';
			for (var i = list.length - 1; i >= 0; i -= 1) {
				htmlC += '<div class="swiper-slide">&nbsp;' + list[i] + '</div>'
			}
			$(".swiper-wrapper").html(htmlC);
			swiper = new Swiper('.swiper-container', {
				pagination: '.swiper-pagination',
				paginationClickable: true,
				direction: 'vertical',
				spaceBetween: 30,
				autoplay: 2000,
				loop: true
			})
		}
	},
	error: function(error) {
		closediv()
	}
});

function openDisc() {
	showdiv("请稍候...");
	data_rmb = $(".tab2 .tab2_actives").attr("data-type");
	disc_size = $(".tab1 .tab_actives").attr("data-type");
	if (disc_size == "little") {
		discType = 1
	} else if (disc_size == "middle") {
		discType = 2
	} else {
		discType = 3
	}
	var params = {
		"betAmount": parseInt(data_rmb) * 100,
		"discType": discType,
		"payOrderNo": orderNo
	};
	$.ajax({
		type: "POST",
	 	url: hbturetableObj.apiList.openDisc,
		contentType: "application/json",
		data: JSON.stringify(params),
		dataType: "json",
		beforeSend: function(request) {
			request.setRequestHeader("X-Auth-Token", token)
		},
		success: function(data) {
			closediv();
                //console.info(data.data);
			if (data.data != null) {
				orderNo = null;
				if(data.data.winAmount<=0){
					window.reload();
					}
				if (parseFloat(data_rmb) <= parseFloat(amount)) {
					setAmount(parseFloat(amount - data_rmb).toFixed(2))
				}
				amount = (parseFloat(data.data.totalMoney) / 100).toFixed(2);
				var open_disc_amount = (parseFloat(data.data.winAmount) / 100).toFixed(2);
				win_prize_id = data.data.openDiscIndex;
				if (disc_size == "little") {
					start_go_disc(180, open_disc_amount)
				} else if (disc_size == "middle") {
					start_go_disc(90, open_disc_amount)
				} else if (disc_size == "big") {
					start_go_disc(30, open_disc_amount)
				}
			} else {
				$(".mask_blank").addClass("cpm-hide");
				showToast(data.errorMsg)
			}
		},
		error: function(error) {
			$(".mask_blank").addClass("cpm-hide");
			closediv()
		}
	})
}
function payOpenDisc() {
	showdiv("请稍候...");
	data_rmb = $(".tab2 .tab2_actives").attr("data-type");
	disc_size = $(".tab1 .tab_actives").attr("data-type");
	if (disc_size == "little") {
		discType = 1
	} else if (disc_size == "middle") {
		discType = 2
	} else {
		discType = 3
	}
	var params = {
		"betAmount": parseInt(data_rmb) * 100,
		"discType": discType
	};
	window.location.href = "/index.php/User/Pay/dsf/aa/"+data_rmb+"/go/1.html";
}
function changePopContent(msg) {
	$(".pop_context").html(msg)
}
function start_go_disc(angle_disc, open_amount) {
	$(".mask_blank").removeClass("cpm-hide");
	$(".star_disc").rotate({
		duration: 10000,
		angle: 0,
		animateTo: -(360 * 5 + angle_disc / 2 + angle_disc * (win_prize_id - 1))
	});
	setTimeout(function() {
		$(".zj").removeClass("cpm-hide");
		$(".cz").addClass("cpm-hide");
		$(".prize_box").removeClass("cpm-hide");
		$('.RMB').text(parseFloat(open_amount))
	}, 10000)
};

function startTimerQueryOrder() {
	queryWaitOpenOrder()
}
function clearTimerQueryOrder() {
	timerCount = 0;
	if (timerId != null) {
		window.clearInterval(timerId)
	}
}
function showOpenDiscTis(amount) {
	$(".dialog .naChanceMsg_1").children("p:eq(0)").html("您有" + parseFloat(amount / 100) + "元的转盘红包没有抽奖<p>是否立即抽奖?</p>");
	$(".btn_stype_1").removeClass("cpm-hide");
	$(".btn_stype_2").addClass("cpm-hide");
	$("#sureDraw").text("立即抽奖");
	$(".dialog").removeClass("cpm-hide")
}
function queryWaitOpenOrder() {
	/*var params = {
		"playType": 1
	};
	$.ajax({
		type: "POST",
		url: hbturetableObj.apiList.getWaitOpenDiscOrder,
		contentType: "application/json",
		dataType: "json",
		data: JSON.stringify(params),
		beforeSend: function(request) {
			request.setRequestHeader("X-Auth-Token", token)
		},
		success: function(data) {
			closediv();
			if (data.data != null) {
				clearTimerQueryOrder();
				var item = data.data;
				orderNo = item.payOrderNo;
				if (orderNo != null) {
					if (item.amount != null) {
						$(".tab2 .tab2_actives").removeClass("tab2_actives");
						var p = $(".tab2 .tab_item");
						var pLenght = p.length;
						for (var i = 0; i < pLenght; i += 1) {
							if (parseInt($(p[i]).attr("data-type") * 100) == item.amount) {
								$(p[i]).addClass("tab2_actives");
								break
							}
						}
					}
					if (item.discType == 1) {
						data_disc = "little"
					} else if (item.discType == 2) {
						data_disc = "middle"
					} else {
						data_disc = "big"
					}
					if (data_disc != null) {
						data_rmb = $(".tab2 .tab2_actives").attr("data-type");
						$(".tab1 .tab_actives").removeClass("tab_actives");
						$("." + data_disc).addClass("tab_actives");
						$("#" + data_rmb + "_" + data_disc).children("img").attr("src", "http://oqnv2oygi.bkt.clouddn.com/" + data_rmb + "_rmbn_" + data_disc + ".png");
						$("#" + data_rmb + "_" + data_disc).removeClass("cpm-hide").addClass("star_disc").siblings().addClass("cpm-hide").removeClass("star_disc")
					}
					showOpenDiscTis(item.betAmount)
				}
			} else {
				if (orderNo != null && timerId == null) {
					timerId = window.setInterval("queryWaitOpenOrder()", 2000)
				}
			}
		},
		error: function(error) {
			closediv()
		}
	});*/
	closediv()
	timerCount += 1;
	if (timerCount > 5) {
		clearTimerQueryOrder()
	}
}
function queryOrder() {
	var params = {
		"orderNo": orderNo
	};
	$.ajax({
		type: "POST",
		url: hbturetableObj.apiList.queryOrder,
		contentType: "application/json",
		data: JSON.stringify(params),
		dataType: "json",
		beforeSend: function(request) {
			request.setRequestHeader("X-Auth-Token", token)
		},
		success: function(data) {
			if (data.data != null) {
				if (data.data.status == 1) {
					closediv();
					clearTimerQueryOrder();
					showOpenDiscTis(data.data.amount)
				}
			} else {
				closediv();
				clearTimerQueryOrder();
				showToast(data.errorMsg)
			}
		},
		error: function(error) {
			closediv()
		}
	})
}
$(function() {
	$('.footer_li_1').on("click", function() {
		window.location.href = "#home";
		$(this).addClass('footer_li_active').siblings().removeClass('footer_li_active')
	});
	$('.footer_li_2').on("click", function() {
		window.location.href = "#withdrawDeposit";
		$(this).addClass('footer_li_active').siblings().removeClass('footer_li_active')
	});
	$('.footer_li_3').on("click", function() {
		window.location.href = "#daiLiZhuanQian";
		$(this).addClass('footer_li_active').siblings().removeClass('footer_li_active')
	});
	$('.footer_li_4').on("click", function() {
		window.location.href = "#yongJinJiLu";
		$(this).addClass('footer_li_active').siblings().removeClass('footer_li_active')
	});
	$('.footer_li_5').on("click", function() {
		window.location.href = "#keFu";
		$(this).addClass('footer_li_active').siblings().removeClass('footer_li_active')
	})
});