var choose_tag = 0;
var choose_method = 0;
var buy_lotterys = new Array();

var wallet = null;
var open_time = null;
var wallet_money = 0;
var current_lottery = null;
var can_lottery = false;
var count = 1;
var result = null;
var lottery_result = null;
var is_showing = false;
var buy_no = '';
var is_waitting;
var continues_count = 0;

function get_wallet() {
	$.ajax({
		url : 'index.php?g=Qqonline&m=index&a=ajax_get_wallet',
		type : "get",
		dataType : "json",
		data : {},
		success : function(data) {
			if (data.ret == 1) {
				//$('#user-money').html((Number(data.info.money)+Number(data.info.money3)).toFixed(2));
				$('#user-money').html((Number(data.info.money)).toFixed(2));
				wallet = data.info;
				wallet_money = Number(wallet.money) + Number(wallet.money3);
			}
		}
	});
}

function load_history()
{
        $.ajax({
            type: 'get',
            dataType: 'json',
            async: false,
            url: 'index.php?g=Qqonline&m=record&a=get_records',
            data: { 'num': 10 },
            success: function (data) {
                if (data.ret == 1) {
                    var html = '';
                    for (var i=0; i<data.list.length; i++) {
                        if (data.list[i].buy_type == 1) {
                            var trend = '猜大';
                        }
                        else if (data.list[i].buy_type == -1) {
                            var trend = '猜小';
                        }
                        else if (data.list[i].buy_type == 10) {

                            var trend = '猜单';
                        }
                        else if (data.list[i].buy_type == 11) {

                            var trend = '猜双';
                        }
                        else if (data.list[i].buy_type == 0) {

                            var trend = '猜合';
                        }
                        
                        var last_number = data.list[i].number.substr(-1, 1);
                        
                        if (last_number > 4) {

                            var color = '#ED5758';
                        }
                        else {
                            var color = '#1DBAA4';
                        }
                        
                        var num1 = data.list[i].num3.substr(-1, 1);
                        var num2 = data.list[i].num3.substr(-2, 1);
                        var num3 = data.list[i].num3.substr(-3, 1);
                        
                        if (data.list[i].win > 0) {

                            html += '<div style="width:299px; border:1px solid #EEEEEE;margin:0 auto;border-radius:4px;margin-top:10px;background:white;float:left;box-sizing: border-box;height:39px; color:#A8A8A8;">' +
													'<div style="float:left; line-height:18px;font-size:12PX; margin-top:10PX; margin-left:10PX;">' + trend + ' ' + data.list[i].price + ' 元  <font style="color:#ED5758;">盈利 ' + data.list[i].win + '</font> 元</div>' +
													'<div style=" float:right;width:20px; height:18px; background:' + color + ';color:white; border-radius:4px; font-size:12px; padding-top:2px; margin-top:8.5px; margin-right:10px;line-height:16px;">' + num1 + '</div>' +
													'<div style=" float:right;width:20px; height:18px; background:' + color + ';color:white; border-radius:4px; font-size:12px; padding-top:2px; margin-top:8.5px; margin-right:10px;line-height:16px;">' + num2 + '</div>' +
													'<div style=" float:right;width:20px; height:18px; background:' + color + ';color:white; border-radius:4px; font-size:12px; padding-top:2px; margin-top:8.5px; margin-right:10px;line-height:16px;">' + num3 + '</div>' +
													'<div style="float:right; font-size:12PX; margin-top:10PX; margin-right:10PX;line-height:18px;">单号尾数</div>' +
												'</div>';
                        }
                        else {
                            html += '<div style="width:299px; border:1px solid #EEEEEE;margin:0 auto;border-radius:4px;margin-top:10px;background:white;float:left;box-sizing: border-box;height:39px; color:#A8A8A8;">' +
													'<div style="float:left; line-height:18px;font-size:12PX; margin-top:10PX; margin-left:10PX;">' + trend + ' ' + data.list[i].price + ' 元  <font style="color:#1DBAA4;">损失 ' + data.list[i].price + '</font> 元</div>' +
													'<div style=" float:right;width:20px; height:18px; background:' + color + ';color:white; border-radius:4px; font-size:12px; padding-top:2px; margin-top:8.5px; margin-right:10px;line-height:16px;">' + num1 + '</div>' +
													'<div style=" float:right;width:20px; height:18px; background:' + color + ';color:white; border-radius:4px; font-size:12px; padding-top:2px; margin-top:8.5px; margin-right:10px;line-height:16px;">' + num2 + '</div>' +
													'<div style=" float:right;width:20px; height:18px; background:' + color + ';color:white; border-radius:4px; font-size:12px; padding-top:2px; margin-top:8.5px; margin-right:10px;line-height:16px;">' + num3 + '</div>' +
													'<div style="float:right; font-size:12PX; margin-top:10PX; margin-right:10PX;line-height:18px;">单号尾数</div>' +
												'</div>';
                        }
                    }
                    $("#quanquan").html(html);
                }
            }
        });
}

function get_open_lottery_result() {
	console.log('获取开奖结果');
	$
			.ajax({
				url : 'index.php?g=Qqonline&m=index&a=ajax_get_open_lottery_pig_result',
				type : "get",
				dataType : "json",
				data : {
					not_auto_mark : true,
				},
				success : function(data) {
					if (data.ret == 1) {	// 显示结果
						// 已经开奖,判断是否需要
						if (data.lottery.is_read == 1)	// 已经通知过了,直接显示结果即可
						{
							/*
							var diff = parseInt(data.lottery.diff);
							
							if (diff > 0)
							{
								clearTimeout(ts_timer);
								
								buy_no = data.lottery.no;
								
								wait_for(diff);
							}
							else
							{
								var is_event = 0;
								var is_type = 0;
								
								if (data.lottery.number.substr(-1,1) == data.lottery.number.substr(-2,1))
								{
									is_type = 0;
								}
								else
								{
									if (data.lottery.number.substr(-1,1) % 2 == 0)
								    	is_event = 1;

									if(data.lottery.number.substr(-1,1) >= 5){
								        is_type = 1;
								    }else{
								    	is_type = -1;
								    }
							}
								
						    	var buy_price = data.result.total_price;
						    	var buy_type = data.lottery.buy_type;
						    	var win_price = data.result.total_win;
						    	var transition_id = data.lottery.number;
						    	var last_num = data.lottery.number.substr(-1,1);
						    	
								// 显示结果
							    if(data.result.is_win == 1){ //中奖
							    	
							    	show_win(buy_price, buy_type, win_price, transition_id, last_num, is_type)
							    	
							    }else if(data.result.is_win == 0){ //未中奖
							        
							    	show_lost(buy_price, buy_type, transition_id, last_num, is_type)
							    }
							    
						        get_wallet();							    
							}
							*/
						}
						else
						{
					    	var buy_price = data.result.total_price;
					    	var buy_type = data.lottery.buy_type;
					    	var win_price = data.result.total_win;
					    	var transition_id = data.lottery.number;
					    	var last_num = data.lottery.number.substr(-1,1);
					    	
					    	if (parseInt(win_price) == 0)
					    		win_price = buy_price * cur_ratio.big_ratio;
					    	
					    	current_lottery = data.lottery;
					    	
							var is_event = 0;
							var is_type = 0;
							
							/*
							if (data.lottery.number.substr(-1,1) == data.lottery.number.substr(-2,1))
							{
								is_type = 0;
							}
							else*/
							{
								if (data.lottery.number.substr(-1,1) % 2 == 0)
							    	is_event = 1;

								if(data.lottery.number.substr(-1,1) >= 5){
							        is_type = 1;
							    }else{
							    	is_type = -1;
								}
							}
					    	
							// 显示结果
						    if(data.result.is_win == 1){ //中奖
						    	
						    	show_win(buy_price, buy_type, win_price, transition_id, last_num, is_type);
						    	
						    }else if(data.result.is_win == 0){ //未中奖
						        if (is_type == buy_type)	// 这个是中奖了
						        	show_win(buy_price, buy_type, win_price, transition_id, last_num, is_type);
						        else if (is_type != 0 && ((buy_type == 10 && is_event == 0) || (buy_type == 11 && is_event == 1)))
						        	show_win(buy_price, buy_type, win_price, transition_id, last_num, is_type);
						        else
						        	show_lost(buy_price, buy_type, transition_id, last_num, is_type)
						    }
							
							get_wallet();							
						}
					}
					else if (data.ret == 2)	// 等待开奖
					{
						buy_no = data.lottery.no;
						
						clearTimeout(ts_timer);
						
						var diff = parseInt(data.lottery.diff);

						if (diff <= 0)
						{
							is_waitting = false;
							
							setTimeout(function() {
								get_open_lottery_result();
							}, 1000);
						}
						else
						{
							wait_for(diff);
						}
					}
				}
			});
}

var ts_timer;
var update_count = 0;
function countdown(s) {
	s--;
	update_count++;
	if (s < 0) {
		get_open_lottery_result();
	} else {
		$('#time').html('' + s);
		ts_timer = setTimeout(function() {
			countdown(s)
		}, 1000);
	}
}

function sub_count() {
	count--;

	if (count < 1)
		count = 1;

	compute_price();
}

function add_count() {
	count++;

	if (count > 50)
		count = 50;

	compute_price();
}

function mul()
{
	count *= 2;
	
	if (count > 50)
		count = 50;

	compute_price();
}

function div()
{
	count = Math.floor(count / 2);
	if (count < 1)
		count = 1;
	
	compute_price();
}

function max() {
	count = 50;
	
	compute_price();
}

function min() {
	count = 1;
	compute_price();
}

function select_method(type)
{
	buy_lotterys = new Array();
	
	count = 1;
	for (var i=0; i<3; i++)
		$('#method0_tag' + i).removeClass('active');
	for (var i=0; i<=4; i++)
		$('#method1_tag' + i).removeClass('active');
	for (var i=10; i<=12; i++)
		$('#method1_tag' + i).removeClass('active');
	for (var i=0; i<=9; i++)
		$('#method2_tag' + i).removeClass('active');

	choose_tag = 0;
	
	$('#method' + choose_method).removeClass('active');
	
	$('#method' + choose_method + '_panel').hide();
	choose_method = type;
	
	if (choose_method == 0)
	{
		buy_lotterys.push(1);
		
		$('#method0_tag2').addClass('active');
	}
	
	$('#method' + choose_method + '_panel').show();
	$('#method' + choose_method).addClass('active');
	
	compute_price();
}

function select_record(idx)
{
	for (var i=0; i<3; i++)
		$('#record' + i).removeClass('active');
	
    $('#full-record .record-content .big-small').hide();
    $('#full-record .record-content .record-exact').hide();
    $('#full-record .record-content .record-prev').hide();
    
    if (idx == 0)
    	$('#full-record .record-content .big-small').show();
    else if (idx == 1)
    	$('#full-record .record-content .record-exact').show();
    else
    	$('#full-record .record-content .record-prev').show();
	
	$('#record' + idx).addClass('active');
}

Array.prototype.remove=function(dx)
{
	if(isNaN(dx)||dx>this.length){return false;}
	for(var i=0,n=0;i<this.length;i++)
	{
		if(this[i]!=this[dx])
		{
			this[n++]=this[i]
		}
	}
	this.length-=1
}

function select_tag(type) {
	for (var i=0; i<buy_lotterys.length; i++)
	{
		if (buy_lotterys[i] == type)
		{
			// 选中效果
			if (choose_method == 0)
			{
				$('#method0_tag' + (buy_lotterys[i] + 1)).removeClass('active');
			}
			else if (choose_method == 1)
			{
				$('#method1_tag' + buy_lotterys[i]).removeClass('active');
			}
			else if (choose_method == 2)
			{
				$('#method2_tag' + buy_lotterys[i]).removeClass('active');
			}
			
			buy_lotterys.remove(i);
			
			compute_price();
			
			return;
		}
	}
	
	buy_lotterys.push(type);
	
	// 选中效果
	if (choose_method == 0)
	{
		for (var i=0; i<3; i++)
			$('#method0_tag' + i).removeClass('active');
		
		for (var i=0; i<buy_lotterys.length; i++)
		{
			$('#method0_tag' + (buy_lotterys[i] + 1)).addClass('active');
		}
	}
	else if (choose_method == 1)
	{
		for (var i=0; i<=4; i++)
			$('#method1_tag' + i).removeClass('active');
		for (var i=10; i<=12; i++)
			$('#method1_tag' + i).removeClass('active');
		
		for (var i=0; i<buy_lotterys.length; i++)
		{
			$('#method1_tag' + buy_lotterys[i]).addClass('active');
		}
	}
	else if (choose_method == 2)
	{
		for (var i=0; i<=9; i++)
			$('#method2_tag' + i).removeClass('active');
		
		for (var i=0; i<buy_lotterys.length; i++)
		{
			$('#method2_tag' + buy_lotterys[i]).addClass('active');
		}
	}
	
	compute_price();
}

function compute_price()
{
	var total_price = buy_lotterys.length * count;
	
	$('#myorder').html('' + total_price);
	
	var low_gain = 999999;
	var high_gain = 0; 
	
	for (var i=0; i<buy_lotterys.length; i++)
	{
		var cur_gain = base_price * count;
		
		if (buy_lotterys[i] == 1)
			cur_gain *= cur_ratio.big_ratio;
		else if (buy_lotterys[i] == 0)
			cur_gain *= cur_ratio.mid_ratio;
		else if (buy_lotterys[i] == -1)
			cur_gain *= cur_ratio.small_ratio;
		else if (buy_lotterys[i] == 10)
			cur_gain *= cur_ratio.odd_ratio;
		else if (buy_lotterys[i] == 11)
			cur_gain *= cur_ratio.event_ratio;
		
		if (low_gain >= cur_gain)
			low_gain = cur_gain;
		
		if (high_gain <= cur_gain)
			high_gain = cur_gain;
	}
	
	if (buy_lotterys.length == 0)
	{
		$('#yuqi').html('0.00');
		$('#yuqi_he').html('0.00');
	}
	else
	{
		if (low_gain != high_gain)
			$('#yuqi').html(low_gain.toFixed(2) + '~' + high_gain.toFixed(2));
		else
			$('#yuqi').html(high_gain.toFixed(2));
		
		$('#yuqi_he').html(Number(base_price * count * cur_ratio.mid_ratio).toFixed(2));
	}
	
	var cur_price_ratio = price_ratio;
	if (cur_price_ratio.charAt(cur_price_ratio.length - 1) == '%')
	{
		cur_price_ratio = cur_price_ratio.replace("%","") / 100.0;
		discount_price = base_price * total_price * cur_price_ratio;
		
		$('#shouxu').html(discount_price.toFixed(2));
	}
	else
	{
		discount_price = parseFloat(cur_price_ratio);
		
		$('#shouxu').html(discount_price.toFixed(2));
	}
}



//-----
//初始化滚动数字
function paint(){
    $(".num1 .num-img").css({'backgroundPositionY':ranArr[openBalls[0]],});
    $(".num2 .num-img").css({'backgroundPositionY':ranArr[openBalls[1]],});
    $(".num3 .num-img").css({'backgroundPositionY':ranArr[openBalls[2]],});
    $(".num4 .num-img").css({'backgroundPositionY':ranArr[openBalls[3]],});
    $(".num5 .num-img").css({'backgroundPositionY':ranArr[openBalls[4]],});
    $(".num6 .num-img").css({'backgroundPositionY':ranArr[openBalls[5]],});
    $(".num7 .num-img").css({'backgroundPositionY':ranArr[openBalls[6]],});
    $(".num8 .num-img").css({'backgroundPositionY':ranArr[openBalls[7]],});
    $(".num9 .num-img").css({'backgroundPositionY':ranArr[openBalls[8]],});
    
	var is_event = 0;
	if (openBalls[8] % 2 == 0)
		is_event = 1;
    
    if(openBalls[7] == openBalls[8]){ //合
        $('#ball1').html(openBalls[7]);
        $('#ball2').html(openBalls[8]);
        $('#ball3').html('合');
    }else if(openBalls[8] >= 5){
        $('#ball1').html('-');
        $('#ball2').html(openBalls[8]);
        $('#ball3').html('大');
        
        if (is_event)
        	$('#ball1').html('双');
        else
        	$('#ball1').html('单');
        
    }else if(openBalls[8] < 5){
        $('#ball1').html('-');
        $('#ball2').html(openBalls[8]);
        $('#ball3').html('小');
        
        if (is_event)
        	$('#ball1').html('双');
        else
        	$('#ball1').html('单');
    }
}

function showResult (result)
{
	$('#full-result .ball .openBall1').html(openBalls[6]);
    $('#full-result .ball .openBall2').html(openBalls[7]);
    $('#full-result .ball .openBall3').html(openBalls[8]);

    $('#full-result .result-num').html(lottery_result.lottery.no);
    var openStr = '';
    if(openBalls[7] == openBalls[8]){
        openStr = '<span class="roll">'+openBalls[7]+'</span>'
                + '<span class="roll">'+openBalls[8]+'</span>'
                + '<span class="roll">合</span>';
        
    var is_event = 0;
    if (openBalls[8] % 2 == 0)
    	is_event = 1;
    	
    }else if(openBalls[8] >= 5){
        openStr = '<span class="roll">大</span>';
        
		if (is_event)
			result += '单<span class="red">双</span>';
		else
			result += '<span class="red">单</span>双';
    }else{
        openStr = '<span class="roll">小</span>';
        
        if (is_event)
			result += '单<span class="red">双</span>';
		else
			result += '<span class="red">单</span>双';
    }

    var ballsStr='';
    for (var i=openBalls.length-3; i<openBalls.length; i++)
    	 ballsStr += '<span class="roll">'+openBalls[i]+'</span>';
  
    $('#full-result .openResult').html(openStr);

    $('#full-result .buy-balls').html(ballsStr);
    $('#full-result .money').html(lottery_result.result.total_price);

    if(lottery_result.result.is_win == 1){ //中奖
        $('#full-result .order-title').html('恭喜您');
        $('#full-result .fucture-money').html(Number(lottery_result.result.total_win).toFixed(2));
        if($('#full-result .order-title').hasClass('lose')){
            $('#full-result .order-title').removeClass('lose');
            $('#full-result .order-box').removeClass('lose');
        }
        getUser();
    }else if(lottery_result.result.is_win == 0){ //未中奖
        $('#full-result .order-title').html('很遗憾');
        $('#full-result .fucture-money').html(0);
        if(!$('#full-result .order-title').hasClass('lose')){
            $('#full-result .order-title').addClass('lose');
            $('#full-result .order-box').addClass('lose');
        }
    }
    $('#full-result').show();
    
    get_wallet();
}

//数字滚动
function run(delay){
    $(".num-img").css({
        'backgroundPositionY':0,
    });
    
    is_showing = true;
    
    $(".num-img").each(function (index) {
        var _num = $(this);
        var u = 26;
        _num.animate({
            backgroundPositionY: (u*60) - (u*openBalls[index]),
        }, {
            duration: index* delay,
            easing: "easeOutCubic",
            complete: function() {
            	
            	is_event = (openBalls[8] % 2 == 0) ? 1 : 0;
            	
                if(index == 8) {
                    if(openBalls[7] == openBalls[8]){ //合
                        $('#ball1').html(openBalls[7]);
                        $('#ball2').html(openBalls[8]);
                        $('#ball3').html('合');
                    }else if(openBalls[8] >= 5){
                        $('#ball1').html('-');
                        $('#ball2').html(openBalls[8]);
                        $('#ball3').html('大');
                        
                        if (is_event)
                        	$('#ball1').html('双');
                        else
                        	$('#ball1').html('单');
                        
                    }else if(openBalls[8] < 5){
                        $('#ball1').html('-');
                        $('#ball2').html(openBalls[8]);
                        $('#ball3').html('小');
                        
                        if (is_event)
                        	$('#ball1').html('双');
                        else
                        	$('#ball1').html('单');
                    }
                    if (lottery_result != null && lottery_result.result != null)
                    {
                    	if (lottery_result.result.buy_count > 0)
                    		showResult(lottery_result);
                    }
                }
                
                is_showing = false;
            }
        })
    })
}

function closeModal() {
    $('.modal').css('opacity',0);
    $('.modal').hide();    
}

function openCharge()
{
	$("#shclDefault").hide();
	
	success("余额不足,请充值");
	
	setTimeout("location.href='index.php?g=Pig&m=index&a=newchongzhi'", 1000);
}

function submit() {
	$("#shclDefault").hide();
	
	if (buy_lotterys.length == 0)
	{
		is_waitting = false;
		success("请下注");		
		return;
	}

	var price = count * base_price * buy_lotterys.length;
	var discount_price = 0;
	var cur_price_ratio = price_ratio;
	if (cur_price_ratio.charAt(cur_price_ratio.length - 1) == '%')
	{
		cur_price_ratio = cur_price_ratio.replace("%","") / 100.0;
		discount_price = price * cur_price_ratio;
	}
	else
	{
		discount_price = price_ratio;
	}

	var total_price = parseFloat(price) + parseFloat(discount_price);
	var det =  wallet_money - total_price;
	var money_det = parseFloat(wallet['money']) - parseFloat(price);
	
	var buy_type = '';
	
	for (var i=0; i<buy_lotterys.length; i++)
	{
		if (i == 0)
			buy_type = buy_lotterys[i];
		else
			buy_type += ',' + buy_lotterys[i];
	}
	
	var ticket = Date.parse(new Date());
	var sign = hex_md5('ajax_create_pig_lottery_order' + price + buy_type + choose_method + ticket);
	
	continues_count++;
	
	if (det < 0 || money_det < 0)
	{
		is_waitting = false;
		
		openCharge();
	}
	else
	{
		var url = 'index.php?g=Qqonline&m=pay&a=ajax_create_pig_lottery_order&price=' + price + '&buy_method=' + choose_method + '&buy_type=' + buy_type + '&ticket=' + ticket + '&sign=' + sign;
		
		$.ajax({
			url : url,
			type : "get",
			dataType : "json",
			data : {
			},
			success : function(data) {
				if (data.ret == 1) {
					get_open_lottery_result();
				}
				else if (data.ret == -2) {
					location.reload();
				}
				else
				{
					success(data.msg);
				}
			}
		});
	}
}

get_wallet();
get_open_lottery_result();
load_history();

function success(title) {
    $("#modal2").fadeIn('fast');
    if (title) {
        $("#modal2").text(title);
    }
    $("#modal2").css('display', 'inline-block');
    $("#modal2").fadeIn('fast');
    window.setTimeout(function () {
        $("#modal2").fadeOut(1000);
    }, 2000);
}

function countdown_delay(delay)
{
	$('#wait_for_modal').text('等待' + delay + '秒开奖');
	if (delay > 0)
	{
		ts_timer = window.setTimeout(function() {
			countdown_delay(delay - 1);
		}, 1000);	
	}
	else
	{
		is_waitting = false;
		
		window.setTimeout(function() {
			get_open_lottery_result();
		}, 500);
	}
}

function wait_for(delay) {
	$("#wait_for_modal").fadeIn('fast');
    $("#wait_for_modal").css('display', 'inline-block');
    $("#wait_for_modal").fadeIn('fast');
    
    console.log("wait_for:" + delay);
    
    countdown_delay(delay);
    
    window.setTimeout(function () {
        $("#wait_for_modal").fadeOut(1000);
    }, delay * 1000);
}

//往期
function openedRecord() {
    //默认展示第一个重庆时彩
    $.ajax({
        url: 'index.php?g=Qqonline&m=index&a=ajax_get_lotterys',
        type:'GET',
        dataType:'json',
        data:{firstRow:0, limitRows:20},
        success:function (res) {
            var dataArr = res.lottery_history;
            var html1 = '';
            var html2 = '';
            var html3 = '';
            $.each(dataArr,function (v,k) {
				    k.balls = new Array();
				    for (var i=0; i<k.number.length; i++)
				    	k.balls.push(k.number.substr(i,1) - '0');
				    
                   	var is_event = 0;
                	if (k.balls[8] % 2 == 0)
                		is_event = 1;
				    
	                if(k.balls[7] == k.balls[8]){
                        html1 += '<tr><td>'+k.no+'</td>'
                            +'<td></td><td></td><td><span class="active"></span><td></td><td></td></td></tr>';

                        html3 += '<tr><td>'+k.no+'</td>'
                            +'<td><span>'+k.balls[6]+'</span><span class="bg-red">'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            +'<td><span>大</span><span>小</span><span class="bg-red">合</span><span>单</span><span>双</span></td></tr>';

                    }else if(k.balls[8] >= 5){
                    	
                        html1 += '<tr><td>'+k.no+'</td>'
                            +'<td><span class="active"></span></td><td></td><td></td>';

                        html3 += '<tr><td>'+k.no+'</td>'
                            +'<td><span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            +'<td><span class="bg-red">大</span><span>小</span><span>合</span>';
                        
                       	if (is_event)
                       	{
                       		html1 += '<td></td><td><span class="active"></span></td>';
                    		html3 += '<span>单</span><span class="bg-red">双</span>';
                       	}
                       	else
                       	{
                       		html1 += '<td><span class="active"></span></td><td></td>';
                    		html3 += '<span class="bg-red">单</span><span>双</span>';
                       	}
                        
                       	html1 += '</tr>';
                        html3 += '</td></tr>';
                    }else{
                        html1 += '<tr><td>'+k.no+'</td>'
                            +'<td></td><td><span class="active"></span></td><td></td>';

                        html3 += '<tr><td>'+k.no+'</td>'
                            +'<td><span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            +'<td><span>大</span><span class="bg-red">小</span><span>合</span>';
                        
                       	if (is_event)
                       	{
                       		html1 += '<td></td><td><span class="active"></span></td>';
                    		html3 += '<span>单</span><span class="bg-red">双</span>';
                       	}
                       	else
                       	{
                       		html1 += '<td><span class="active"></span></td><td></td>';
                    		html3 += '<span class="bg-red">单</span><span>双</span>';
                       	}
                        
                       	html1 += '</tr>';
                        html3 += '</td></tr>';
                    }
                    if(k.balls[8] == 1){
                        html2 += '<tr><td>'+k.no+'</td><td><span class="active"></span></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 2){
                        html2 += '<tr><td>'+k.no+'</td><td></td><td><span class="active"></span></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 3){
                        html2 += '<tr><td>'+k.no+'</td><td></td><td></td><td><span class="active"></span></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 4){
                        html2 += '<tr><td>'+k.no+'</td><td></td><td></td><td></td><td><span class="active"></span></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 5){
                        html2 += '<tr><td>'+k.no+'</td><td></td><td></td><td></td><td></td><td><span class="active"></span></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 6){
                        html2 += '<tr><td>'+k.no+'</td><td></td><td></td><td></td><td></td><td></td><td><span class="active"></span></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 7){
                        html2 += '<tr><td>'+k.no+'</td><td></td><td></td><td></td><td></td><td></td><td></td><td><span class="active"></span></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 8){
                        html2 += '<tr><td>'+k.no+'</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><span class="active"></span></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 9){
                        html2 += '<tr><td>'+k.no+'</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><span class="active"></span></td><td></td></tr>'
                    }else if(k.balls[8] == 0){
                        html2 += '<tr><td>'+k.issue+'</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><span class="active"></span></td></tr>'
                    }
            })
            $('#full-record .record-content .big-small tbody').html(html1);
            $('#full-record .record-content .record-exact tbody').html(html2);
            $('#full-record .record-content .record-prev tbody').html(html3);
        }
    })
}

    //打开往期窗口
    $('#openPrev').click(function () {
        $('#full-record').slideDown();
        openedRecord();
    })
    //关闭往期窗口
    $('#full-record .close-btn').click(function () {
        $('#full-record').slideUp();
        openedRecord();
    })
    //打开游戏说明窗口
    $('#openGame').click(function () {
        $('#full-game').slideDown();
    })
    //关闭游戏说明窗口
    $('#full-game .close-btn').click(function () {
        $('#full-game').slideUp();
    })
    
    select_method(0);
    
    
//======================================================
//    滚动条
//======================================================
function get_marquee()
{
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'index.php?g=Qqonline&m=index&a=ajax_get_win_list',
        data: { 'type': 'wsdb', 'is_moni': 0 },
        success: function (data) {
            if (data.ret == 1) {
            	
            	for (var i=0; i<data.lists.length && i<10; i++)
            	{
            		$('#marquee' + (1+i)).show();
            		$("#lun" + (1+i)).text('玩家' + data.lists[i].user_id);
                    $("#qian" + (1+i)).text(data.lists[i].win);
            	}
            }
        }
    });	
}

get_marquee();

setInterval(function () {
	get_marquee();
}, 15000);

// 显示成功
function show_win(buy_price, buy_type, win_price, transition_id, last_num, is_type)
{
    $("#modal4").show();
    $("#hidebg").show();
    $("#caidanum").text(Math.floor(buy_price / base_price));
    
    var is_event = 0;
    if (is_type != 0)
    {
    	if (last_num % 2 == 0)
    		is_event = true;
    }
    
    var result_str = '';
    if (is_type == 0)
    	result_str = '<span class="roll">合</span>';
    else if (is_type == -1)
    {
    	result_str = '<span class="roll">小</span>';
    	
    	if (is_event == 0)
    		result_str += '<span class="roll">单</span>';
    	else
    		result_str += '<span class="roll">双</span>';
    }
    else
    {
    	result_str = '<span class="roll">大</span>';
    	
    	if (is_event == 0)
    		result_str += '<span class="roll">单</span>';
    	else
    		result_str += '<span class="roll">双</span>';
    }
    
    if (last_num > 4) {
        $("#yingqian").text(transition_id.substr(0, transition_id.length-2));
        $("#yinghou").text(transition_id.substr(-2,2));
        $("#yinghou").css('color', '#EE5859');
    }
    else {
        $("#yingqian").text(transition_id.substr(0, transition_id.length-2));
        $("#yinghou").text(transition_id.substr(-2,2));
        $("#yinghou").css('color', '#1DBAA4');
    }
    
    $('#result').html(result_str);

    if (buy_type == -1)
        $("#trend_ying").text('猜 小');
    else if (buy_type == 1)
    	$("#trend_ying").text('猜 大');
    else if (buy_type == 10)
    	$("#trend_ying").text('猜 单');
    else if (buy_type == 11)
    	$("#trend_ying").text('猜 双');
    else
        $("#trend_ying").text('猜 合');

    $("#caidawin").text('+' + win_price); 
    
    load_history();
}

// 显示失败
function show_lost(buy_price, buy_type, transition_id, last_num, is_type)
{
	$("#modal5").show();
    $("#hidebg").show();
    $("#caixiaonum").text(Math.floor(buy_price / base_price));
    
    var is_event = 0;
    if (is_type != 0)
    {
    	if (last_num % 2 == 0)
    		is_event = true;
    }
    
    var result_str = '';
    if (is_type == 0)
    	result_str = '<span class="roll">合</span>';
    else if (is_type == -1)
    {
    	result_str = '<span class="roll">小</span>';
    	
    	if (is_event == 0)
    		result_str += '<span class="roll">单</span>';
    	else
    		result_str += '<span class="roll">双</span>';
    }
    else
    {
    	result_str = '<span class="roll">大</span>';
    	
    	if (is_event == 0)
    		result_str += '<span class="roll">单</span>';
    	else
    		result_str += '<span class="roll">双</span>';
    }
    	
    
    if (last_num > 4) {
        $("#shibaiqian").text(transition_id.substr(0, transition_id.length-2));
        $("#shibaihou").text(transition_id.substr(-2,2));
        $("#shibaihou").css('color', '#EE5859');
    }
    else {
        $("#shibaiqian").text(transition_id.substr(0, transition_id.length-2));
        $("#shibaihou").text(transition_id.substr(-2,2));
        $("#shibaihou").css('color', '#1DBAA4');
    }
    
    if (buy_type == -1)
        $("#trend_shu").text('猜 小');
    else if (buy_type == 1)
    	$("#trend_shu").text('猜 大');
    else if (buy_type == 10)
    	$("#trend_shu").text('猜 单');
    else if (buy_type == 11)
    	$("#trend_shu").text('猜 双');
    else
        $("#trend_shu").text('猜 合');
    
    $('#result_lost').html(result_str);
    
    $("#caixiaowin").text(0 - buy_price);	
    
    load_history();
}

function buy(type)
{
	buy_lotterys = new Array();
	buy_lotterys.push(type);

	$("#modal3").show();
	$("#hidebg").show();	
}

//操作
//猜大按钮
$("#caida").click(function () {
	if (is_waitting)
		return;
	
	is_waitting = true;
	
	 $("#shclDefault").show();
     $('#shclDefault').shCircleLoader();
     
     buy(1);
});

//猜小按钮
$("#caixiao").click(function () {
	if (is_waitting)
		return;
	
	is_waitting = true;
	
	 $("#shclDefault").show();
     $('#shclDefault').shCircleLoader();
     
     buy(-1);
});

//猜合按钮
$("#caihe").click(function () {
	
	if (is_waitting)
		return;
	
	is_waitting = true;
	
	 $("#shclDefault").show();
     $('#shclDefault').shCircleLoader();
     
     buy(0);
});

$("#caidan").click(function () {
	if (is_waitting)
		return;
	
	is_waitting = true;
	
	 $("#shclDefault").show();
    $('#shclDefault').shCircleLoader();
    
    buy(10);
});

$("#caishuang").click(function () {
	if (is_waitting)
		return;
	
	is_waitting = true;
	
	$("#shclDefault").show();
    $('#shclDefault').shCircleLoader();
   
   buy(11);
});

$("#know").click(function () {
    $("#modal3").hide();
    $("#hidebg").hide();
    
    submit();
});

$(".caidaknow").click(function () {
    //猜大确认，不刷新准备
    //更新余额
    $("#modal4").hide();
    $("#modal5").hide();
    $("#hidebg").hide();
    
    // 重新开始
    get_wallet();
    
	buy_lotterys = new Array();
	buy_lotterys.push(1);
	count = 1;
	
	$.ajax({
		url : 'index.php?g=Qqonline&m=index&a=ajax_mark_lottery_pig_result_read',
		type : "get",
		dataType : "json",
		data : {
			no:current_lottery.no
		},
		success : function(data) {
			
			if (continues_count >= 999999)
			{
				location.href = 'index.php?g=Pig&m=index&a=newduobao&change=true';
			}
			
			if (data.ret == 1) {
				//$('#user-money').html((Number(data.info.money)+Number(data.info.money3)).toFixed(2));
				$('#user-money').html((Number(data.info.money)).toFixed(2));
				wallet = data.info;
				wallet_money = Number(wallet.money) + Number(wallet.money3);
			}
		}
	});

	compute_price();
});
