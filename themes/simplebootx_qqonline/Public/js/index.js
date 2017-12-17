/**
 * Created by admin on 2017/5/31.
 */


//订单数据
var order = {
    'orderArr':[],//订单详情
    'balls':null,//订单数字
    'odds':null,//订单赔率
    'issue':'',     //期数
    'money':'',     //金额
    'time':'', //订单时间
    'type':'001', //订单类型
};
var nowIssue = '';
var gameTimer = null;   //游戏倒计时定时器
var checkTimer = null;  //查询开奖定时器
var msgTimer = null;  //封盘定时器

//滚动的高度
var _height = 26;
var ranArr = [0,-_height,-_height*2,-_height*3,-_height*4,-_height*5,-_height*6,-_height*7,-_height*8,-_height*9];
var openBalls = [];

var isFirst = true;//是否刷新页面

$(function () {
    //初始化页面
    loadData('1');
    getOpenResult();
    clearInterval(msgTimer);
    msgTimer = null;
    // isRevord = false;

    $('#money').html(orderNum[0]);


    //玩法选择
    $('#toolbar').on('click','div.col-xs-4',function () {
        $(this).siblings('.active').removeClass('active');
        $(this).addClass('active');
        var index = $(this).index();
        $(this).parent().children().children('.borderRight').removeClass('borderRight');
        if(index == 0){
            $('#toolbar div.col-xs-4 span').eq(1).addClass('borderRight');

        }else if(index == 2){
            $('#toolbar div.col-xs-4 span').eq(0).addClass('borderRight');
        }
        loadData(++index);
    })
    //选择筹码
    $('#play-box').on('click','.play-btn>div',function () {
        if($(this).hasClass('col-sm-12')){
            return;
        }
        $(this).siblings('.active').removeClass('active');
        $(this).addClass('active');
    })
    //下注金额
    $('#xia-box').on('click','div.xia-btn',function () {
        var indexOfArr = indexOf(orderNum, Number($('#money').html()));
        if($(this).hasClass('sure')){
            if(Number($('#user-money').html())>=5){ //直接押注
                $('#full-buy').show();
                var selectObj = $('#play-box').children('.play-content').children().children('.active');

                var nowNum = nowIssue;
                var typeArr = $(selectObj).children('.type').html().split(' ');
                var odds = '';
                var money = $('#money').html();
                $('#full-buy .buy-num').html(nowNum);

                var ballsStr = '';
                if(typeArr.length==2){ //数组场
                     odds = $('.play-btn.array .col-xs-12').eq(0).children('.odds').html();
                }else if(typeArr.length==3){
                     odds = $('.play-btn.array .col-xs-12').eq(1).children('.odds').html();
                }else if(typeArr[0]=='大' || typeArr[0]=='小'|| typeArr[0]=='合'){
                    var odds = $(selectObj).children().children('.odds').html();
                }else{
                    odds = $('.play-btn.exact .col-xs-12').children('.odds').html();
                }
                $.each(typeArr,function (k,v) {
                    ballsStr += '<span class="roll">'+v+'</span>';
                })
                var fuctureMoney = Number(money) * Number(odds);
                $('#full-buy .ball').html(ballsStr);
                $('#full-buy .odds').html(odds);
                $('#full-buy .money').html(money);
                $('#full-buy .fucture-money').html(fuctureMoney);

                //保存订单信息
                var oderStr = $(selectObj).attr('data-index').split(',');
                order.orderArr.push(oderStr);
                order.issue= nowNum;
                order.money= money;
                order.odds= odds;
                order.balls = ballsStr;

            }else{
                $('#full-frame').show();
            }
            return;
        }
        if($(this).hasClass('max')){
            indexOfArr = orderNum.length-1;
        }else if($(this).hasClass('jian')){
            if(indexOfArr == 0){
                indexOfArr = orderNum.length-1;
            }else{
                indexOfArr--;
            }
        }else if($(this).hasClass('jia')){
            if(indexOfArr == orderNum.length-1){
                indexOfArr = 0;
            }else{
                indexOfArr++;
            }
        }
        $('#money').html(orderNum[indexOfArr]);
    })
    //往期选择
    $('#tool').on('click','div.col-xs-4',function () {
        $(this).siblings('.active').removeClass('active');
        $(this).addClass('active');
        var index = $(this).index();
        $('.record-content>div').removeClass('active');
        $('.record-content>div').eq(index).addClass('active');

    })


    //关闭押注
    $('#buy-close').click(function () {
        console.log(222222)
        $('#full-buy').hide();
    })
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
    //关闭购买成功窗口
    $('#full-success .buy-btn').click(function () {
        $('#full-success').hide();
    })
    //关闭开奖结果窗口
    $('#full-result .buy-btn').click(function () {
        $('#full-result').hide();
        // isRevord = false;
    })
    //确认购买支付
    $('#payConfirm').click(function () {
        payConfirm();
    })
});

//查询是否中奖
function getResult(issue){
    $.ajax({
        url: localStorage['baseUrl'] + 'lottery/selectBet',
        type: 'GET',
        data: {'type': order.type,'issue':issue},
        success: function (res) {
            if(res.error_code == 0){
                // alert(res.msg+"，本期输赢："+res.data.over);
                var data = res.data;
                if(data.bets[0].status == '还未开奖'){
                    $('#full-result').hide();
                    localStorage['issue']='';
                    return;
                }
                $('#full-result .ball .openBall1').html(data.bets[0].lottery[6]);
                $('#full-result .ball .openBall2').html(data.bets[0].lottery[7]);
                $('#full-result .ball .openBall3').html(data.bets[0].lottery[8]);

                $('#full-result .result-num').html(data.bets[0].issue);
                var openStr = '';
                if(data.bets[0].lottery[7] == data.bets[0].lottery[8]){
                    openStr = '<span class="roll">'+data.bets[0].lottery[7]+'</span>'
                            + '<span class="roll">'+data.bets[0].lottery[8]+'</span>'
                            + '<span class="roll">合</span>';
                }else if(data.bets[0].lottery[8] >= 5){
                    openStr = '<span class="roll">大</span>';
                }else{
                    openStr = '<span class="roll">小</span>';
                }
                var typeArr = data.bets[0].valueString.split(' ');
                var ballsStr='';
                $.each(typeArr,function (k,v) {
                    ballsStr += '<span class="roll">'+v+'</span>';
                })
                $('#full-result .openResult').html(openStr);
                console.log(JSON.parse(localStorage['issue']))
                $('#full-result .buy-balls').html(ballsStr);
                $('#full-result .money').html(data.bets[0].money);

                if(res.data.bets[0].statusCode == 1){ //中奖
                    $('#full-result .order-title').html('恭喜您');
                    $('#full-result .fucture-money').html(Number(res.data.bets[0].winMoney) + Number(data.bets[0].money));
                    if($('#full-result .order-title').hasClass('lose')){
                        $('#full-result .order-title').removeClass('lose');
                        $('#full-result .order-box').removeClass('lose');
                    }
                    getUser();
                }else if(res.data.bets[0].statusCode == 2){ //未中奖
                    $('#full-result .order-title').html('很遗憾');
                    $('#full-result .fucture-money').html(0);
                    if(!$('#full-result .order-title').hasClass('lose')){
                        $('#full-result .order-title').addClass('lose');
                        $('#full-result .order-box').addClass('lose');
                    }
                }
                localStorage['issue'] = '';
            }else{
               // alert(res.msg);
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            //alert('请求错误')
        }
    })
}
//function openInerval(){
    // clearInterval(checkTimer);
    // checkTimer = null;
    // checkTimer = setInterval(function () {
    //     getOpenMsg(nowIssue);
    // },1000)
//}

//查询是否开奖
function getOpenMsg(issue){
    $.ajax({
        url: localStorage['baseUrl'] + 'lottery/getNewLottery',
        type: 'GET',
        data: {'type': order.type,'issue':issue},
        success: function (res) {
            if(res.error_code == 0){
                var data = res.data;
                $('#user-money').html(data.money);

                //清定时器
                // clearInterval(checkTimer);
                // checkTimer = null;
                kaiTimer(data.serverTime);//获取数据
                getOpenResult();    //开奖记录
                //期数
                $('#openNum,.now-num,.buy-num').html(data.nextOpen);
                $('#percentNum').html(data.arealyOpen);
                order.issue = data.nextOpen;
                //开奖号码
                var balls = data.balls;
                openBalls = balls;
                nowIssue = data.nextOpen;
                run();

            }else{  //还未开奖
                // if(issue == nowIssue){
                //     return;
                // }
                setTimeout(function () {
                    getOpenMsg(issue);
                },1000)
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            //alert('请求错误')
        }
    })
}
//清空订单数据
function clearOrder(){
    order.orderArr = [];
    // order.money='';
    order.time='';
    order.issue='';
}
var buying = false;
//确认购买
function payConfirm() {
    if(buying){return;}
    buying = true;
    order.time = getNowTime();
    $.ajax({
        url: localStorage['baseUrl'] + 'lottery/bet',
        type: 'POST',
        data: {
            'type': order.type,
            'money': order.money,
            'nowTime': order.time,
            'issue':order.issue,
            'playType':JSON.stringify(order.orderArr)
        },
        success: function (res) {
            if(res.error_code == 0){
                $('#full-success').show();
                $('#full-buy').hide();
                localStorage['issue'] = order.issue;
                $('#full-success .full-msg').html('<p class="msg">恭喜投注成功！</p>');
                buying = false;
                $('#user-money').html(res.data.money);
                //清空选中的
                 clearOrder();
                // clearData();
            }else if(res.error_code == '204'){
                $('#full-buy').hide();
                $('#full-success').show();
                $('#full-success .full-msg').html('<p class="msg">本期您已下注,请等下期再来</p>');
            }else if(res.error_code == '401'){ //余额不足
                $('#full-frame').show();
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            //alert('请求错误')
        }
    })
}

//根据玩法类型加载数据
function loadData(class1){
    $('#order-box').html('');
    $.ajax({
        url:localStorage['baseUrl'] + 'lottery/gameStart',
        type:'GET',
        data:{'type':order.type,'class1':class1},
        success:function(res){
            $('.shopMain-left span').html(0);
            //期数、余额
            $('#openNum,.now-num,.buy-num').html(res.data.nextOpen);
            $('#percentNum').html(res.data.arealyOpen);
            nowIssue = res.data.nextOpen;
            $('#user-money').html(res.data.money);

            if(res.error_code==0){
                var data = res.data;
                // if(data.serverTime<0){
                //     return;
                // }
                var html ='';
                clearTimeout(gameTimer);
                gameTimer = null;
                kaiTimer(data.serverTime);
                //开奖号码
                var balls = data.balls;
                openBalls = balls;
                paint();

                if(class1 == 1){
                    var str = '<div class="play-btn row max-min">';
                }else if(class1 == 2){
                    var str = '<div class="play-btn row array">';
                }else if(class1 == 3){
                    var str = '<div class="play-btn row exact">';
                }
                $.each(data.value[0].playType,function(v,g){
                        $.each(g.name, function (j,name) {
                            if(class1 == 1){
                                if (name !== "NULL") {
                                    if(v==0 && j==0){
                                        str +='<div class="col-xs-4 col-sm-4 active" data-index="'+ g.type + ',' + g.tp1 + ',' + g.tp2 + ',' + j+'">'
                                            +'<p class="type">'+name+'</p>'
                                            +'<p>1赔<span class="odds">'+g.odds[j]+'</span></p></div>'
                                    }else{
                                        str +='<div class="col-xs-4 col-sm-4" data-index="'+ g.type + ',' + g.tp1 + ',' + g.tp2 + ',' + j+'">'
                                            +'<p class="type">'+name+'</p>'
                                            +'<p>1赔<span class="odds">'+g.odds[j]+'</span></p></div>'
                                    }
                                }
                            }else if(class1 == 2){
                                if (name !== "NULL") {
                                    if(v==0 && j==0){
                                        if(g.tp1 == 'A3'){
                                            str +='<div class="col-xs-4 col-sm-4 active" data-index="'+ g.type + ',' + g.tp1 + ',' + g.tp2 + ',' + j+'">'
                                                +'<p class="type">'+name+'</p></div>'
                                        }else if(g.tp1 == 'A2'){
                                            str +='<div class="col-xs-2 col-sm-2 active" data-index="'+ g.type + ',' + g.tp1 + ',' + g.tp2 + ',' + j+'">'
                                                +'<p class="type">'+name+'</p></div>'
                                        }
                                    }else{
                                        if(g.tp1 == 'A3'){
                                            str +='<div class="col-xs-4 col-sm-4" data-index="'+ g.type + ',' + g.tp1 + ',' + g.tp2 + ',' + j+'">'
                                                +'<p class="type">'+name+'</p></div>'
                                        }else if(g.tp1 == 'A2'){
                                            str +='<div class="col-xs-2 col-sm-2" data-index="'+ g.type + ',' + g.tp1 + ',' + g.tp2 + ',' + j+'">'
                                                +'<p class="type">'+name+'</p></div>'
                                        }
                                    }
                                }
                            }else if(class1 == 3){
                                if (name !== "NULL") {
                                    if(v==0 && j==0){
                                        str +='<div class="col-xs-2 col-sm-2 active" data-index="'+ g.type + ',' + g.tp1 + ',' + g.tp2 + ',' + j+'">'
                                            +'<p class="type">'+name+'</p></div>'
                                    }else{
                                        str +='<div class="col-xs-2 col-sm-2" data-index="'+ g.type + ',' + g.tp1 + ',' + g.tp2 + ',' + j+'">'
                                            +'<p class="type">'+name+'</p></div>'
                                    }
                                }
                            }
                        })
                        if(class1 == 2){
                            str += '<div class="col-xs-12 col-sm-12">1赔<span class="odds">'+g.odds[v]+'</span></div>';
                        }
                    })
                if(class1 == 3){
                    str += '<div class="col-xs-12 col-sm-12">1赔<span class="odds">'+data.value[0].playType[0].odds[0]+'</span></div>';
                }
                str += '</div>';
                    $('#play-box .play-content').html(str)
                $('#order-box').html(html);
                if(localStorage['issue']){
                    if(res.data.nextOpen != localStorage['issue']){
                        // isRevord = true;
                        getResult(localStorage['issue']);
                        $('#full-result').show();
                    }
                }
            }else{
                //alert(res.msg);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert('请求错误')
        }
    });
}

//数组查找元素
function indexOf(arr,item){
    if(Array.prototype.indexOf){
        return arr.indexOf(item);
    }else{
        for( var i=0;i<arr.length;i++){
            if(arr[i]===item)
                return i;
            else return -1;
        }
    }
}
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
        $('#ball3').html('大');
    }else if(openBalls[8] < 5){
        $('#ball1').html('-');
        $('#ball2').html(openBalls[8]);
        $('#ball3').html('小');
    }
}
//数字滚动
function run(){
    $(".num-img").css({
        'backgroundPositionY':0,
    });
    console.log(111111)
    $(".num-img").each(function (index) {
        var _num = $(this);
        var u = 26;
        _num.animate({
            backgroundPositionY: (u*60) - (u*openBalls[index]),
        }, {
            duration: index* 800,
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
                        $('#ball3').html('大');
                    }else if(openBalls[8] < 5){
                        $('#ball1').html('-');
                        $('#ball2').html(openBalls[8]);
                        $('#ball3').html('小');
                    }
                    if(localStorage['issue']){
                        //查询是否中奖
                        getResult(localStorage['issue']);
                        $('#full-result').show();
                    }
                }
                clearInterval(msgTimer);
                msgTimer = null;
            }
        })
    })
}
//倒计时
function kaiTimer(intDiff) {
    intDiff = parseInt(intDiff);
    clearInterval(gameTimer);
    if (intDiff >=0) {
        var timeObj = turnTime(intDiff);
        var second = String(timeObj.second);
        $('#time-up').html(second.substring(0,1));
        $('#time-low').html(second.substring(1,2));
        gameTimer = setTimeout(function () {
            kaiTimer(intDiff - 1);
        }, 1000);
    }else{ //倒计时为00时，
        $('#full-waite').show();
        msgInter(5);
    }
}
//封盘倒计时
function msgInter(datime) {
    $('#full-waite #time').html(datime);
    clearInterval(msgTimer);
    msgTimer = setInterval(function () {
        datime--;
        $('#full-waite #time').html(datime);
        if(datime < 0){
            getOpenMsg(nowIssue);
            clearInterval(msgTimer);
            $('#full-waite').hide();
            //run();
        }
    },1000)
}
//时间格式转换
function turnTime(time){
    day = Math.floor(time / (60 * 60 * 24));
    hour = Math.floor(time / (60 * 60)) - (day * 24);
    minute = Math.floor(time / 60) - (day * 24 * 60) - (hour * 60);
    second = Math.floor(time) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);
    if (minute <= 9) minute = '0' + minute;
    if (second <= 9) second = '0' + second;
    var timeObj = {};
    timeObj.day=day;
    timeObj.hour=hour;
    timeObj.minute = minute;
    timeObj.second = second;
    return timeObj;
}
//往期
function openedRecord() {
    //默认展示第一个重庆时彩
    $.ajax({
        url: localStorage['baseUrl'] + 'lottery/getLotteryHistory',
        type:'GET',
        data:{'type':order.type,'haveDay':'999','startPage':1},
        success:function (res) {
            var dataArr = res.data.lotterys;
            var html1 = '';
            var html2 = '';
            var html3 = '';
            $.each(dataArr,function (v,k) {
                    if(k.balls[7] == k.balls[8]){
                        html1 += '<tr><td>'+k.issue+'</td>'
                            +'<td></td><td></td><td><span class="active"></span></td></tr>';

                        html3 += '<tr><td>'+k.issue+'</td>'
                            +'<td><span>'+k.balls[6]+'</span><span class="bg-red">'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            +'<td><span>大</span><span>小</span><span class="bg-red">合</span></td></tr>';

                    }else if(k.balls[8] >= 5){
                        html1 += '<tr><td>'+k.issue+'</td>'
                            +'<td><span class="active"></span></td><td></td><td></td></tr>';

                        html3 += '<tr><td>'+k.issue+'</td>'
                            +'<td><span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            +'<td><span class="bg-red">大</span><span>小</span><span>合</span></td></tr>';
                    }else{
                        html1 += '<tr><td>'+k.issue+'</td>'
                            +'<td></td><td><span class="active"></span></td><td></td></tr>';

                        html3 += '<tr><td>'+k.issue+'</td>'
                            +'<td><span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span class="bg-red">'+k.balls[8]+'</span></td>'
                            +'<td><span>大</span><span class="bg-red">小</span><span>合</span></td></tr>';
                    }
                    if(k.balls[8] == 1){
                        html2 += '<tr><td>'+k.issue+'</td><td><span class="active"></span></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 2){
                        html2 += '<tr><td>'+k.issue+'</td><td></td><td><span class="active"></span></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 3){
                        html2 += '<tr><td>'+k.issue+'</td><td></td><td></td><td><span class="active"></span></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 4){
                        html2 += '<tr><td>'+k.issue+'</td><td></td><td></td><td></td><td><span class="active"></span></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 5){
                        html2 += '<tr><td>'+k.issue+'</td><td></td><td></td><td></td><td></td><td><span class="active"></span></td><td></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 6){
                        html2 += '<tr><td>'+k.issue+'</td><td></td><td></td><td></td><td></td><td></td><td><span class="active"></span></td><td></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 7){
                        html2 += '<tr><td>'+k.issue+'</td><td></td><td></td><td></td><td></td><td></td><td></td><td><span class="active"></span></td><td></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 8){
                        html2 += '<tr><td>'+k.issue+'</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><span class="active"></span></td><td></td><td></td></tr>'
                    }else if(k.balls[8] == 9){
                        html2 += '<tr><td>'+k.issue+'</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><span class="active"></span></td><td></td></tr>'
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
//开奖记录
function getOpenResult() {
    //默认展示第一个重庆时彩
    $.ajax({
        url: localStorage['baseUrl'] + 'lottery/getLotteryHistory',
        type:'GET',
        data:{'type':order.type,'haveDay':'999','startPage':1},
        success:function (res) {
            var dataArr = res.data.lotterys;
            var html = '';
            $.each(dataArr,function (v,k) {
                if(v<3 &&v>0){
                    html += '<div class="row">'
                        +'<div class="col-xs-6 col-sm-6">'+k.issue+'期</div>'
                        +'<div class="col-xs-3 col-sm-3">'
                        +'<span>'+k.balls[6]+'</span><span>'+k.balls[7]+'</span><span>'+k.balls[8]+'</span></div>';

                    if(k.balls[7] == k.balls[8]){
                        html +='<div class="col-xs-3 col-sm-3" >大小<span class="red">合</span></div></div><div class="row line"></div>';
                    }else if(k.balls[8] >= 5){
                        html +='<div class="col-xs-3 col-sm-3" ><span class="red">大</span>小合</div></div><div class="row line"></div>';
                    }else{
                        html +='<div class="col-xs-3 col-sm-3" >大<span class="red">小</span>合</div></div><div class="row line"></div>';
                    }

                }
            })
            $('.content-opened').html(html);
        }
    })
}