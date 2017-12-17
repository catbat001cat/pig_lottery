<?php 
ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);
require_once "lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
//require_once 'log.php';

header('Access-Control-Allow-Origin: *');

//初始化日志
//$logHandler= new CLogFileHandler("logs/".date('Y-m-d').'.log');
//$log = Log::Init($logHandler, 15);

//打印输出数组信息
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}

//①、获取用户openid
$tools = new JsApiPay();
//$openId = $tools->GetOpenid();

$openId = $_GET['openId'];

//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("商品信息：".$_GET['body']);//商品或支付单简要描述
//$input->SetAttach("地址".$_GET['addre']);//
$input->SetAttach($_GET['order_sn']);//附加数据
$input->SetOut_trade_no(WxPayConfig::$MCHID.date("YmdHis"));//订单号
//$input->SetOut_trade_no($_GET['order_sn']);//订单号
$input->SetTotal_fee("1");//总价
$input->SetTime_start(date("YmdHis"));//开始时间
$input->SetTime_expire(date("YmdHis", time() + 3600));
$input->SetGoods_tag("test");//商品标记
$input->SetNotify_url("http://vbar.vwoof.com/api/pay/notify_wx_red");//回调通知地址
//$input->SetNotify_url("http://h5.vwoof.com/bargame_admin/wxpay/notify.php");//回调通知地址
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
$jsApiParameters = $tools->GetJsApiParameters($order);

echo $jsApiParameters;

?>