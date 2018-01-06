var choose_tag = 0;
var choose_method = 0;
var buy_lotterys = new Array();


var has_he = false;

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
function get_wallet() {
	$.ajax({
		url : 'index.php?g=Qqonline&m=index&a=ajax_get_wallet',
		type : "get",
		dataType : "json",
		data : {},
		success : function(data) {
			if (data.ret == 1) {
				$('.user-money').html(Number(data.info.money).toFixed(2));
				$('#user-money').html(Number(data.info.money).toFixed(2));
				wallet = data.info;
				wallet_money = wallet.money;
			}
		}
	});
}


function append_history_lottery_item(item) {
	
	var result = '';
	var last_num = item.num3.substr(2,1);
	var is_event = 0;
	if ((last_num - '0') % 2 == 0)
		is_event = 1;
	
	if (has_he)
	{
		if (item.type == 1)
		{
			result = '<span class="red">大</span>小合';
			
			if (is_event)
				result += '单<span class="red">双</span>';
			else
				result += '<span class="red">单</span>双';
		}
		else if (item.type == 0)
			result = '大小<span class="red">合</span>';
		else if (item.type == -1)
		{
			result = '大<span class="red">小</span>合';
			if (is_event)
				result += '单<span class="red">双</span>';
			else
				result += '<span class="red">单</span>双';
		}	
	}
	else
	{
		if (item.type == 1)
		{
			result = '<span class="red">大</span>小';
			
			if (is_event)
				result += '单<span class="red">双</span>';
			else
				result += '<span class="red">单</span>双';
		}
		else if (item.type == 0)
		{
			
		}
		else if (item.type == -1)
		{
			result = '大<span class="red">小</span>';
			if (is_event)
				result += '单<span class="red">双</span>';
			else
				result += '<span class="red">单</span>双';
		}	
	}
	
	var item_template = $('#history_lottery_template').html();
	item_template = item_template.replace(/{no}/g, item.no)
			.replace(/{num3_0}/g, item.num3.substr(0,1))
			.replace(/{num3_1}/g, item.num3.substr(1,1))
			.replace(/{num3_2}/g, item.num3.substr(2,1))
			.replace(/{result}/g, result);

	$('#history_lottery_container').append(item_template);
}

function process_balls(openBalls)
{
}

function get_open_lottery_result() {
	console.log('获取开奖结果:' + current_lottery.no);
	$
			.ajax({
				url : 'index.php?g=Qqonline&m=index&a=ajax_get_open_lottery_result',
				type : "get",
				dataType : "json",
				data : {
					no : current_lottery.no
				},
				success : function(data) {
					if (data.ret == 1) {
						if (data.lottery.status != 2) {
							get_open_lottery_result();
						} else {
							$('#percentNum').html(data.lottery.no);
							
							$('#full-waite').hide();

							// 显示结果效果
							
						    openBalls = new Array();
						    for (var i=0; i<data.lottery.number.length; i++)
						    	openBalls.push(data.lottery.number.substr(i,1) - '0');
						    
						    process_balls(openBalls);
						    
						    lottery_result = data;
						    
						    run(800);
						    
						    // 判断我有没有中奖

							// 开启新的投票
							setTimeout(function() {
								get_lottery_info();
							}, 1000);
						}
					}
					else
					{
						setTimeout(function() {
							get_open_lottery_result();
						}, 1000);
					}
				}
			});
}


var is_first = true;

function get_lottery_info() {
	$.ajax({
		url : 'index.php?g=Qqonline&m=index&a=ajax_get_lottery_info',
		type : "get",
		dataType : "json",
		data : {},
		success : function(data) {
			if (data.ret == 1) {
				
				if (!is_showing && $('#percentNum').html() == data.lottery_history[0].no)
				{
					$('#percentNum').html(data.lottery_history[0].no);

					// 显示结果效果
					
				    openBalls = new Array();
				    for (var i=0; i<data.lottery_history[0].number.length; i++)
				    	openBalls.push(data.lottery_history[0].number.substr(i,1) - '0');
				    
				    process_balls(openBalls);

				    run(0);	
				}
				
				var open_date = data.current_lottery.open_time.split(' ');
				var open_times = open_date[1].split(':');
				
				$('#openNum').html(data.current_lottery.no);
				$('#openTime').html(open_times[0] + ':' + open_times[1]);
				$('.now-num').html(data.current_lottery.no);
				current_lottery = data.current_lottery;
				$('#big_ratio').html(data.ratio.big_ratio);
				$('#mid_ratio').html(data.ratio.mid_ratio);
				$('#small_ratio').html(data.ratio.small_ratio);
				$('#odd_ratio').html(data.ratio.odd_ratio);
				$('.num_ratio').html(data.ratio.num_ratio);
				$('#event_ratio').html(data.ratio.event_ratio);
				$('#num_ratio').html(data.ratio.num_ratio);
				$('#num2_ratio').html(data.ratio.num2_ratio);
				$('#num3_ratio').html(data.ratio.num3_ratio);
				$('#cur_percentNum').html(data.lottery_history[0].no);

				cur_ratio = data.ratio; 

				can_lottery = false;

				// 即将开奖
				if (data.current_lottery.status == 1) {
					$('#full-waite').show();
					$('#delay_panel').hide();
					
					$('.time-up').html(0);
					$('.time-low').html(0);
					clearTimeout(ts_timer);
					
					buy_no = current_lottery.no;

					countdown2(caiji_delay);
				} else if (data.current_lottery.status == 2) {
					//$('#lottery_number').html(data.current_lottery.number);
					// 显示结果
					$('#full-waite').hide();
					$('#delay_panel').hide();
					
					buy_no = current_lottery.no;
				} else {
					open_time = data.current_lottery.open_time;
					$('#full-waite').hide();
					$('#delay_panel').hide();

					/*
					var current_time = new Date();
					var end_time = new Date(open_time.replace(/-/g, '/'));
					var diff = Math
							.floor((end_time.getTime() - current_time
									.getTime()) / 1000) - 5;
					*/
					var diff = parseInt(data.current_lottery.diff);

					if (diff <= 0) {
						//$('#delay_panel').show();

						ts_timer = setTimeout(function() {
							get_lottery_info();
						}, 1000);
					} else {
						can_lottery = true;
						
						clearTimeout(ts_timer);

						countdown(diff);
					}
				}
				
				if (is_first)
				{
				    openBalls = new Array();
				    for (var i=0; i<data.lottery_history[0].number.length; i++)
				    	openBalls.push(data.lottery_history[0].number.substr(i,1) - '0');
				    
				    process_balls(openBalls);
				    
				    //lottery_result = data;
				    
				    run(0);
				    
					is_first = false;
				}
				
				// 这里不需要显示历史

				//$('#history_lottery_container').html('');

				//for ( var i = 1; i < data.lottery_history.length; i++)
					//append_history_lottery_item(data.lottery_history[i]);
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
		get_lottery_info();
	} else {
		var up = parseInt(s / 10);
		var low = s - up * 10;
		
		if (update_count > 4)
		{
			get_lottery_info();
			
			update_count = 0;
		}
		
		$('.time-up').html(up);
		$('.time-low').html(low);
		ts_timer = setTimeout(function() {
			countdown(s)
		}, 1000);
	}
}

function countdown2(s) {
	s--;
	if (s == 0) {
		setTimeout(function() {
			get_open_lottery_result();
		}, 1000);
	} else {
		$('#time').html(s);
		setTimeout(function() {
			countdown2(s)
		}, 1000);
		
		if (s <= 2)
			get_open_lottery_result();
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

	if (count > 100)
		count = 100;

	compute_price();
}

function mul()
{
	count *= 2;
	
	if (count > 100)
		count = 100;

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
	count = 100;
	
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
		
	$('#buy_confirm_panel').show();
}

function confirm()
{
	$('#buy_confirm_panel').hide();
	
	submit();
}

function select_record(idx)
{
	for (var i=0; i<4; i++)
		$('#record' + i).removeClass('active');
	
	$('#full-record .record-content .record-history').hide();
    $('#full-record .record-content .big-small').hide();
    $('#full-record .record-content .record-exact').hide();
    $('#full-record .record-content .record-prev').hide();
    
    if (idx == 0)
    	$('#full-record .record-content .big-small').show();
    else if (idx == 1)
    	$('#full-record .record-content .record-exact').show();
    else if (idx == 2)
    	$('#full-record .record-content .record-prev').show();
    else if (idx == 3)
    	$('#full-record .record-content .record-history').show();
	
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

function select_tag2(type) {
	choose_method = 2;
	
	// 判断是否超过200元
	if (base_price * count >= 200)
	{
        $('#modal .title').html('选择数字不能超过200元下注！');
        $('#modal .btnSure').attr('onclick','closeModal()');
        $('.modal').css('opacity',100);
        $('#modal').show();
        return;
	}
	
	$('#method0_tag0').removeClass('active');
	$('#method0_tag1').removeClass('active');
	$('#method0_tag2').removeClass('active');
	$('#method0_tag12').removeClass('active');
	$('#method0_tag11').removeClass('active');
	
	for (var i=0; i<10; i++)
	{
		$('#method2_tag' + i).removeClass('active');
	}
	
	buy_lotterys = new Array();
	
	buy_lotterys.push(type);
	
	for (var i=0; i<=9; i++)
		$('#method2_tag' + i).removeClass('active');
	
	for (var i=0; i<buy_lotterys.length; i++)
	{
		$('#method2_tag' + buy_lotterys[i]).addClass('active');
	}
	
	compute_price();
	
	$('#cur_buy_type').html(type);			
	
	$('#buy_confirm_panel').show();
}


function select_tag(type) {
	choose_method = 0;
	
	$('#method0_tag0').removeClass('active');
	$('#method0_tag1').removeClass('active');
	$('#method0_tag2').removeClass('active');
	$('#method0_tag12').removeClass('active');
	$('#method0_tag11').removeClass('active');
	
	for (var i=0; i<10; i++)
	{
		$('#method2_tag' + i).removeClass('active');
	}
	
	buy_lotterys = new Array();
	
	buy_lotterys.push(type);
	
	for (var i=0; i<3; i++)
		$('#method0_tag' + i).removeClass('active');
	
	for (var i=0; i<buy_lotterys.length; i++)
	{
		$('#method0_tag' + (buy_lotterys[i] + 1)).addClass('active');
	}
	
	compute_price();
	
	if (type == 1)
		$('#cur_buy_type').html('大');
	else if (type == -1)
		$('#cur_buy_type').html('小');
	if (type == 10)
		$('#cur_buy_type').html('单');
	else if (type == 11)
		$('#cur_buy_type').html('双');
	
	$('#buy_confirm_panel').show();
}

/*
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
*/

function compute_price()
{
	var total_price = buy_lotterys.length * count;
	
	$('#money').html('' + total_price);
	
	var low_gain = 999999;
	var high_gain = 0; 
	
	/*
	for (var i=0; i<buy_lotterys.length; i++)
	{
		var cur_gain = base_price * count;
		
		if (choose_method == 0)
		{
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
		}
		else
		{
			cur_gain *= cur_ratio.num_ratio;
		}
		
		if (low_gain >= cur_gain)
			low_gain = cur_gain;
		
		if (high_gain <= cur_gain)
			high_gain = cur_gain;
	}
	
	if (buy_lotterys.length == 0)
	{
		$('#future-money').html('0.00');
	}
	else
	{
		if (low_gain != high_gain)
			$('#future-money').html(low_gain.toFixed(2) + '~' + high_gain.toFixed(2));
		else
			$('#future-money').html(high_gain.toFixed(2));		
	}
	*/
	
	var f1 = base_price * count * cur_ratio.big_ratio;
	var f2 = base_price * count * cur_ratio.num_ratio;
	
	$('#future-money').html(f1.toFixed(2));
	$('#future-money2').html(f2.toFixed(2));
	
	var cur_price_ratio = price_ratio;
	if (cur_price_ratio.charAt(cur_price_ratio.length - 1) == '%')
	{
		cur_price_ratio = cur_price_ratio.replace("%","") / 100.0;
		discount_price = total_price * base_price * cur_price_ratio;
		
		$('#price_ratio').html(discount_price.toFixed(2));
	}
	else
	{
		discount_price = parseFloat(cur_price_ratio);
		
		$('#price_ratio').html(discount_price.toFixed(2));
	}
}



//-----
//初始化滚动数字
function paint(){
    $(".num1 .num-img").css({'backgroundPositionY':ranArr[openBalls[0]],});
    $(".num2 .num-img").css({'backgroundPositionY':ranArr[openBalls[1]],});
    $(".num3 .num-img").css({'backgroundPositionY':ranArr[openBalls[2]],});
    $(".num4 .num-img").css({'backgroundPositionY':ranArr[openBalls[3]],});
    $(".num5 .num-img").css({'backgroundPositionY':ranArr[openBalls[8]],});
    $(".num6 .num-img").css({'backgroundPositionY':ranArr[openBalls[7]],});
    $(".num7 .num-img").css({'backgroundPositionY':ranArr[openBalls[6]],});
    $(".num8 .num-img").css({'backgroundPositionY':ranArr[openBalls[5]],});
    $(".num9 .num-img").css({'backgroundPositionY':ranArr[openBalls[4]],});
    
	var is_event = 0;
	if (openBalls[8] % 2 == 0)
		is_event = 1;
    
	if (has_he)
	{
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
	else
	{
		if(openBalls[8] >= 5){
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
}

function showResult (result)
{
	$('#full-result .ball .openBall1').html(openBalls[6]);
    $('#full-result .ball .openBall2').html(openBalls[7]);
    $('#full-result .ball .openBall3').html(openBalls[8]);

    $('#full-result .result-num').html(lottery_result.lottery.no + '期');
    var openStr = '';
    
    var is_event = 0;
    
    if (has_he)
    {
        if(openBalls[7] == openBalls[8]){
            openStr = '<span class="roll">'+openBalls[7]+'</span>'
                    + '<span class="roll">'+openBalls[8]+'</span>'
                    + '<span class="roll">合</span>';
            
        if (openBalls[8] % 2 == 0)
        	is_event = 1;
        	
        }else if(openBalls[8] >= 5){
            openStr = '<span class="roll">大</span>';
        }else{
            openStr = '<span class="roll">小</span>';
        }
        
        if (is_event)
        	openStr += '<span class="roll">双</span>';
		else
			openStr += '<span class="roll">单</span>';        
    }
    else
    {
        if (openBalls[8] % 2 == 0)
        	is_event = 1;
        
        openStr = '<span class="roll">'+openBalls[8]+'</span>'
        
        if(openBalls[8] >= 5){
            openStr += '<span class="roll">大</span>';
        }else{
            openStr += '<span class="roll">小</span>';
        }
        
        if (is_event)
        	openStr += '<span class="roll">双</span>';
		else
			openStr += '<span class="roll">单</span>';        
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
    
    lottery_result = null;
    
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
        var i = index;
        if (index >= 4)
        	i = 8 - (index - 4);
        _num.animate({
            backgroundPositionY: (u*60) - (u*openBalls[i]),
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

function submit() {
	if (!can_lottery) {
		$('#modal .title').html('请等待结果');
		$('.modal').css('opacity',100);
        $('#modal .btnSure').attr('onclick','closeModal()');
        $('#modal').show();
		return;
	}
	
	if (buy_lotterys.length == 0)
	{
        $('#modal .title').html('请下注');
        $('.modal').css('opacity',100);
        $('#modal .btnSure').attr('onclick','closeModal()');
        $('#modal').show();		
		return;
	}

	var price = price_mul[count-1] * base_price * buy_lotterys.length;
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

	var buy_type = '';
	
	for (var i=0; i<buy_lotterys.length; i++)
	{
		if (i == 0)
			buy_type = buy_lotterys[i];
		else
			buy_type += ',' + buy_lotterys[i];
	}
	
	var ticket = Date.parse(new Date());
	var sign = hex_md5('create_lottery_order' + current_lottery.no + price + buy_type + choose_method + ticket);
	
	if (det < 0)
		openCharge();
	else {
		$.ajax({
			url : 'index.php?g=Qqonline&m=pay&a=create_lottery_order',
			type : "get",
			dataType : "json",
			data : {
				no : current_lottery.no,
				price : price,
				buy_method : choose_method,
				buy_type : buy_type,
				ticket : ticket,
				sign : sign
			},
			success : function(data) {
				if (data.ret == 1) {
					get_wallet();
					
                    $('#modal .title').html('下注成功！');
                    $('#modal .btnSure').attr('onclick','closeModal()');
                    $('.modal').css('opacity',100);
                    $('#modal').show();
				}
				else
				{
					get_wallet();
					
					$('#modal .title').html(data.msg);
			        $('#modal .btnSure').attr('onclick','closeModal()');
			        $('.modal').css('opacity',100);
			        $('#modal').show();
				}
			}
		});
	}
}

get_wallet();
get_lottery_info();

// 历史记录
function loadRecord(){
    $.ajax({
        url: 'index.php?g=Qqonline&m=record&a=get_records',
        type: 'GET',
        dataType : "json",
        data: {},
        success: function (obj) {
            if(obj.ret == 1){
            	
            	var res = {
            		list : new Array()
            	};
            	
            	for (var i=0; i<obj.list.length; i++)
            	{
            		var is_finded = false;
            		for (var j=0; j<res.list.length; j++)
            		{
            			if (res.list[j].no == obj.list[i].no)
            			{
            				res.list[j].buy_lists.push(obj.list[i]);
            				
            				is_finded = true;
            				
            				break;
            			}
            		}
            		
            		if (!is_finded)
            		{
            			obj.list[i].buy_lists = new Array();
            			obj.list.push(obj.list[i]);
            			res.list.push(obj.list[i]);
            		}
            	}
            	
                var str = '';
            	var html3 = '';
                
                $.each(res.list,function (k,v) {

                    if(v.status == 0) {	//还未开奖
                    }else {
                    	
                    	var balls = new Array();
    				    for (var i=0; i<v.number.length; i++)
    				    	balls.push(v.number.substr(i,1) - '0');
                    	
                    	var buyStr = '';
                    	
                        for (var i=0; i<v.buy_lists.length; i++)
                        {
                        	if (v.buy_lists[i].buy_method == "0")
                        	{
                        		if (v.buy_lists[i].buy_type == "1")
                        			cur_buy = '<span class="roll red-bg">大</span>';
                        		else if (v.buy_lists[i].buy_type == "-1")
                        			cur_buy = '<span class="roll red-bg">小</span>'
                        		if (v.buy_lists[i].buy_type == "10")
                                	cur_buy = '<span class="roll red-bg">单</span>';
                                else if (v.buy_lists[i].buy_type == "11")
                                	cur_buy = '<span class="roll red-bg">双</span>'
                        	}
                        	else if (v.buy_lists[i].buy_method == "2")
                        	{
                        		cur_buy = '<span class="roll">' +  v.buy_lists[i].buy_type + '</span>';
                        	}
                        	
                        	if (i == 0)
                        		buyStr = cur_buy;
                        	else
                        		buyStr += cur_buy;
                        }                    	
                    
                    	html3 += '<tr><td>'+v.no+'</td>'
                    		+'<td><span>'+balls[6]+'</span><span>'+balls[7]+'</span><span class="bg-red">'+balls[8]+'</span></td>'
                    		+'<td>' +　buyStr　+　'</td></tr>';
                    }
                });
                
                $('#full-record .record-content .record-history tbody').html(html3);
            }else{
                alert(res.msg);
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            //alert('请求错误')
        }
    })
}

//往期
function openedRecord() {
    $.ajax({
        url: 'index.php?g=Qqonline&m=index&a=ajax_get_lotterys_pig',
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
				    
	                if(k.balls[7] == k.balls[8] && has_he){
                        html1 += '<tr><td>'+k.no+'</td>'
                            +'<td></td><td></td><td><span class="active"></span><td></td><td></td></td></tr>';

                        	html3 += '<tr><td>'+k.no+'</td>'
                            	+'<td><span>'+k.balls[6]+'</span><span class="bg-red">'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            	+'<td><span>大</span><span>小</span><span class="bg-red">合</span><span>单</span><span>双</span></td></tr>';

                    }else if(k.balls[8] >= 5){
                    	
                        if (has_he)
                        {
                            html1 += '<tr><td>'+k.no+'</td>'
                            	+'<td><span class="active"></span></td><td></td><td></td>';
                        	
                        	html3 += '<tr><td>'+k.no+'</td>'
                            	+'<td><span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            	+'<td><span class="bg-red">大</span><span>小</span><span>合</span>';
                        }
                        else
                        {
                        	html1 += '<tr><td>'+k.no+'</td>'
                        		+'<td><span class="active"></span></td><td></td>';
                        	
                        	html3 += '<tr><td>'+k.no+'</td>'
                        	+'<td><span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                        	+'<td><span class="bg-red">大</span><span>小</span>';                        	
                        }
                        
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

                        if (has_he)
                        {
                            html1 += '<tr><td>'+k.no+'</td>'
                            +'<td></td><td><span class="active"></span></td><td></td>';
                            
                            html3 += '<tr><td>'+k.no+'</td>'
                            	+'<td><span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            	+'<td><span>大</span><span class="bg-red">小</span><span>合</span>';	
                        }
                        else
                        {
                            html1 += '<tr><td>'+k.no+'</td>'
                            +'<td></td><td><span class="active"></span></td>';
                            
                            html3 += '<tr><td>'+k.no+'</td>'
                        		+'<td><span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                        		+'<td><span>大</span><span class="bg-red">小</span>';                        	
                        }
                        
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
        loadRecord();
    })
    $('#gameRule').click(function() {
    	//$('#rule_panel').show();
    	location.href = 'index.php?g=Qqonline&m=index&a=rule';
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
    
    var swiper;
    function get_marquee()
    {
        $.ajax({
            type: 'get',
            dataType: 'json',
            url: 'index.php?g=Qqonline&m=index&a=ajax_get_win_list',
            data: { },
            success: function (data) {
            	console.log(JSON.stringify(data));
                if (data.ret == 1) {
        			var htmlC = '';
        			document.getElementById('swiper_wrapper').style = 'transform: translate3d(0px, 0px, 0px); transition-duration: 0ms;';
        			for (var i = data.lists.length - 1; i >= 0; i -= 1) {
        				var region = '';
        				if (data.lists[i].buy_method == 0)
        					region = '大小区';
        				else
        					region = '数字区';
        				var str = '恭喜会员［' + data.lists[i].user_activation_key + '］在' + region + '投中' + data.lists[i].win + '元！';
        				htmlC += '<div class="swiper-slide">&nbsp;' + str + '</div>';
        			}
        			$(".swiper-wrapper").html(htmlC);
        			if (swiper != null)
        				swiper.stopAutoplay();
    				swiper = new Swiper('.swiper-container', {
        				pagination: '.swiper-pagination',
        				paginationClickable: true,
        				direction: 'vertical',
        				spaceBetween: 30,
        				autoplay: 2000,
        				loop: true
        			});
                }
            }
        });	
    }

    get_marquee();

    setInterval(function () {
    	get_marquee();
    }, 15000);    