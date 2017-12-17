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
function get_wallet() {
	$.ajax({
		url : 'index.php?g=Qqonline&m=index&a=ajax_get_wallet',
		type : "get",
		dataType : "json",
		data : {},
		success : function(data) {
			if (data.ret == 1) {
				$('#user-money').html(Number(data.info.money).toFixed(2));
				wallet = data.info;
				wallet_money = wallet.money;
			}
		}
	});
}

function append_history_lottery_item(item) {
	
	var result = '';
	if (item.type == 1)
		result = '<span class="red">龙</span>合';
	else if (item.type == 0)
		result = '龙虎<span class="red">合</span>';
	else if (item.type == -1)
		result = '龙<span class="red">虎</span>合';
	
	var item_template = $('#history_lottery_template').html();
	item_template = item_template.replace(/{no}/g, item.no)
			.replace(/{num3_0}/g, item.num3.substr(0,1))
			.replace(/{num3_1}/g, item.num3.substr(1,1))
			.replace(/{num3_2}/g, item.num3.substr(2,1))
			.replace(/{result}/g, result);

	$('#history_lottery_container').append(item_template);
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
					console.log(JSON.stringify(data));
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
						    
						    console.log(JSON.stringify(openBalls));
						    
						    lottery_result = data;
						    
						    console.log(JSON.stringify(lottery_result));
						    
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

function get_lottery_info() {
	$.ajax({
		url : 'index.php?g=Qqonline&m=index&a=ajax_get_lottery_info',
		type : "get",
		dataType : "json",
		data : {},
		success : function(data) {
			if (data.ret == 1) {
				console.log(JSON.stringify(data));
				
				
				if (!is_showing && $('#percentNum').html() != data.lottery_history[0].no)
				{
					$('#percentNum').html(data.lottery_history[0].no);

					// 显示结果效果
					
				    openBalls = new Array();
				    for (var i=0; i<data.lottery_history[0].number.length; i++)
				    	openBalls.push(data.lottery_history[0].number.substr(i,1) - '0');

				    run(0);	
				}
				
				
				$('#openNum').html(data.current_lottery.no);
				$('.now-num').html(data.current_lottery.no);
				current_lottery = data.current_lottery;
				$('#big_ratio').html(data.ratio.big_ratio);
				$('#mid_ratio').html(data.ratio.mid_ratio);
				$('#small_ratio').html(data.ratio.small_ratio);
				$('#num_ratio').html(data.ratio.num_ratio);
				$('#num2_ratio').html(data.ratio.num2_ratio);
				$('#num3_ratio').html(data.ratio.num3_ratio);

				can_lottery = false;

				// 即将开奖
				if (data.current_lottery.status == 1) {
					$('#full-waite').show();
					$('#delay_panel').hide();
					
					$('#time-up').html(0);
					$('#time-low').html(0);
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

				$('#history_lottery_container').html('');

				for ( var i = 1; i < data.lottery_history.length; i++)
					append_history_lottery_item(data.lottery_history[i]);
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
		
		$('#time-up').html(up);
		$('#time-low').html(low);
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

	if (count > 14)
		count = 14;

	compute_price();
}

function max() {
	count = 14;
	
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
	
	compute_price();
	
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

var price_mul = [1, 2, 4, 6, 10, 20, 40, 60, 100, 160, 200, 400, 800, 1000];
var base_price = 2;

function compute_price()
{
	var total_price = buy_lotterys.length * base_price * price_mul[count-1];
	
	$('#money').html('' + total_price);
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
    if(openBalls[7] == openBalls[8]){ //合
        $('#ball1').html(openBalls[7]);
        $('#ball2').html(openBalls[8]);
        $('#ball3').html('合');
    }else if(openBalls[8] >= 5){
        $('#ball1').html('-');
        $('#ball2').html(openBalls[8]);
        $('#ball3').html('龙');
    }else if(openBalls[8] < 5){
        $('#ball1').html('-');
        $('#ball2').html(openBalls[8]);
        $('#ball3').html('虎');
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
    }else if(openBalls[8] >= 5){
        openStr = '<span class="roll">龙</span>';
    }else{
        openStr = '<span class="roll">虎</span>';
    }

    var ballsStr='';
    for (var i=openBalls.length-3; i<openBalls.length; i++)
    	 ballsStr += '<span class="roll">'+openBalls[i]+'</span>';
  
    $('#full-result .openResult').html(openStr);

    $('#full-result .buy-balls').html(ballsStr);
    $('#full-result .money').html(lottery_result.result.total_price);

    if(result.result.is_win == 1){ //中奖
        $('#full-result .order-title').html('恭喜您');
        $('#full-result .fucture-money').html(Number(lottery_result.result.total_win).toFixed(2));
        if($('#full-result .order-title').hasClass('lose')){
            $('#full-result .order-title').removeClass('lose');
            $('#full-result .order-box').removeClass('lose');
        }
        getUser();
    }else if(result.result.is_win == 0){ //未中奖
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
                if(index == 8) {
                    if(openBalls[7] == openBalls[8]){ //合
                        $('#ball1').html(openBalls[7]);
                        $('#ball2').html(openBalls[8]);
                        $('#ball3').html('合');
                    }else if(openBalls[8] >= 5){
                        $('#ball1').html('-');
                        $('#ball2').html(openBalls[8]);
                        $('#ball3').html('龙');
                    }else if(openBalls[8] < 5){
                        $('#ball1').html('-');
                        $('#ball2').html(openBalls[8]);
                        $('#ball3').html('虎');
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
	
	var buy_type = '';
	
	for (var i=0; i<buy_lotterys.length; i++)
	{
		if (i == 0)
			buy_type = buy_lotterys[i];
		else
			buy_type += ',' + buy_lotterys[i];
	}
	
	if (price > wallet_money)
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
				    
	                if(k.balls[7] == k.balls[8]){
                        html1 += '<tr><td>'+k.no+'</td>'
                            +'<td></td><td></td><td><span class="active"></span></td></tr>';

                        html3 += '<tr><td>'+k.no+'</td>'
                            +'<td><span>'+k.balls[6]+'</span><span class="bg-red">'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            +'<td><span>大</span><span>小</span><span class="bg-red">合</span></td></tr>';

                    }else if(k.balls[8] >= 5){
                        html1 += '<tr><td>'+k.no+'</td>'
                            +'<td><span class="active"></span></td><td></td><td></td></tr>';

                        html3 += '<tr><td>'+k.no+'</td>'
                            +'<td><span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            +'<td><span class="bg-red">大</span><span>小</span><span>合</span></td></tr>';
                    }else{
                        html1 += '<tr><td>'+k.no+'</td>'
                            +'<td></td><td><span class="active"></span></td><td></td></tr>';

                        html3 += '<tr><td>'+k.no+'</td>'
                            +'<td><span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            +'<td><span>大</span><span class="bg-red">小</span><span>合</span></td></tr>';
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