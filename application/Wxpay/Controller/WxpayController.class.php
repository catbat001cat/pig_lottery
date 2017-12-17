<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class WxpayController extends HomebaseController {
    private $wx_pay_db = null;
    private $wx_mch_db = null;
	function _initialize(){
		parent::_initialize();
		
		$this->wx_pay_db = M('wx_pay');
		$this->wx_mch_db = M('wx_mch');
	}
	
	public function sign($par, $key)
	{
	    return md5($par. $key);
	}
	
	// 从别的平台进来
	public function entry() {
	    require_once "jssdk.php";
	
	    $appid = C('APPID');
	    $appsecret = C('APPSECRET');
	
	    $price = $_REQUEST['price'];
	    $body = '';
	    if (isset($_REQUEST['body']))
	        $body = $_REQUEST['body'];
	    $mchid = $_REQUEST['mch'];
	    $pay_goback = '';
	    if (isset($_REQUEST['pay_goback']))
	        $pay_goback = $_REQUEST['pay_goback'];
	    $memo = '';
	    if (isset($_REQUEST['memo']))
	        $memo = $_REQUEST['memo'];
	    $order_sn = $_REQUEST['order_sn'];
	    $from_openid = $_REQUEST['openid'];
	    $ticket = $_REQUEST['ticket'];
	    $sign = $_REQUEST['sign'];
	    
	    $params_url = $order_sn . $price . $from_openid . urlencode($pay_goback) . $ticket;
	     
	    $new_sign = $this->sign($params_url, C('MCH_KEY'));
	     
	    if ($new_sign != $sign)
	    {
	        $user = M('users')->where("openid='$from_openid'")->find();
	        // 日志
	        $action_log = M('user_action_log');
	        $log_data = array(
	            'user_id' => $user['id'],
	            'action' => 'hack',
	            'params' => 'WXPAY:支付签名不正确:' . $price,
	            'ip' => get_client_ip(0, true),
	            'create_time' => date('Y-m-d H:i:s')
	        );
	        $action_log->add($log_data);
	        	
	        echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
	        return;
	    }

	    $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/index.php?g=Wxpay&m=wxpay&a=login&mch=$mchid&memo=$memo&from_openid=$from_openid&order_sn=$order_sn&body=$body&price=$price&pay_goback=" . urlencode($pay_goback) . '&jsapi_ticket=' . $ticket . '&sha=' . $sign;
	    $jssdk = new \JSSDK($appid, $appsecret);
	    $url = $jssdk->gotoAuth($redirect_url, "code", "snsapi_base", "STATE");
	
	    redirect($url);
	}
	
	function getRandChar($length){
	    $str = null;
	    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	    $max = strlen($strPol)-1;
	
	    for($i=0;$i<$length;$i++){
	        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
	    }
	
	    return $str;
	}
	
	public function login() {
	    require_once "jssdk.php";

	    if (isset($_GET['code']))
	        $code = $_GET["code"];
	    else
	        $code = '';
	
	    $appid = C('APPID');
	    $appsecret = C('APPSECRET');
	
	    $jssdk = new \JSSDK($appid, $appsecret);
	    $res = $jssdk->getAuthAccessToke($code);
	    if (!property_exists($res, 'openid'))
	    {
	        //echo "<script>alert('请在微信打开');</script>";
	    }
	    else
	    {
	    }
	
	    $rand_string = $this->getRandChar(16);
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    $result = curl_exec($ch);
	    $obj = json_decode($result);
	    //curl_close($ch);
	    $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$obj->access_token}&type=jsapi";
	    curl_setopt($ch, CURLOPT_URL, $url);
	    $result = curl_exec($ch);
	    echo curl_error($ch);
	    $obj2 = json_decode($result);
	    curl_close($ch);
	    $timestamp = time();
	    $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
	    $str = "jsapi_ticket={$obj2->ticket}&noncestr={$rand_string}&timestamp={$timestamp}&url={$url}";
	    //print_r($str);
	    $signature = sha1($str);
	
	    if (!empty($_REQUEST['req_url']) || isset($_REQUEST['req_url'])) {
	        $req_url=$_REQUEST['req_url'];
	    } else {
	        $req_url=0;
	    }
	    
	    $price = $_REQUEST['price'];
	    $body = $_REQUEST['body'];
	    $mchid = $_REQUEST['mch'];
	    $pay_goback = $_REQUEST['pay_goback'];
	    $memo = $_REQUEST['memo'];
	    $from_order_sn = $_REQUEST['order_sn'];
	    $from_openid = $_REQUEST['from_openid'];
	    $ticket = $_REQUEST['jsapi_ticket'];
	    $sign = $_REQUEST['sha'];
	    
	    $params_url = $from_order_sn . $price . $from_openid . urlencode($pay_goback) . $ticket;
	    
	    $new_sign = $this->sign($params_url, C('MCH_KEY'));
	    
	    if ($new_sign != $sign)
	    {
	        $user = M('users')->where("openid='$from_openid'")->find();
	        // 日志
	        $action_log = M('user_action_log');
	        $log_data = array(
	            'user_id' => $user['id'],
	            'action' => 'hack',
	            'params' => 'WXPAY:支付签名不正确:' . $price,
	            'ip' => get_client_ip(0, true),
	            'create_time' => date('Y-m-d H:i:s')
	        );
	        $action_log->add($log_data);
	    
	        echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
	        return;
	    }
	    
	    $mch = $this->wx_mch_db->where("id=$mchid")->find();
	     
	    if ($mch == null)
	    {
	        echo '参数缺少';
	        return;
	    }
	     
	    $params_url = $from_order_sn . $price . $from_openid . urlencode($pay_goback) . $ticket;
	    
	    $new_sign = $this->sign($params_url, C('MCH_KEY'));
	    
	    if ($new_sign != $sign)
	    {
	        $user = M('users')->where("openid='$from_openid'")->find();
	        // 日志
	        $action_log = M('user_action_log');
	        $log_data = array(
	            'user_id' => $user['id'],
	            'action' => 'hack',
	            'params' => 'WXPAY:支付签名不正确:' . $price,
	            'ip' => get_client_ip(0, true),
	            'create_time' => date('Y-m-d H:i:s')
	        );
	        $action_log->add($log_data);
	    
	        echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
	        return;
	    }
	    
	    $data = $this->wx_pay_db->where("from_order_sn='$from_order_sn' and mch=$mchid")->find();
	    
	    if ($data == null)
	    {	    
    	    $order_sn = sp_get_order_sn();

    	    $data = array(
    	        'price' => $price,
    	        'body' => $body,
    	        'mch' => $mchid,
    	        'openid' => $res->openid,
    	        'order_sn' => $order_sn,
    	        'from_order_sn' => $from_order_sn,
    	        'channel' => 'WX_PAY',
    	        'channel_mch' => C('MCHID'),
    	        'status' => 0,
    	        'create_time' => date('Y-m-d H:i:s'),
    	        'memo' => $memo
    	    );
    	     
    	    $rst = $this->wx_pay_db->add($data);
    	    
    	    $data['id'] = $rst;
	    }
	    
	    $data['openid'] = $res->openid;

	    $this->pay($data, $pay_goback);
	}
	
	public function pay($order, $goback)
	{
	    ini_set('date.timezone','Asia/Shanghai');

	    require_once "wxpay/lib/WxPay.Api.php";
	    require_once "wxpay/WxPay.JsApiPay.php";

	    $order_sn = $order['order_sn'];
	    
	    //打印输出数组信息
	    function printf_info($data)
	    {
	        foreach($data as $key=>$value){
	            echo "<font color='#00ff55;'>$key</font> : $value <br/>";
	        }
	    }
	    
	    //①、获取用户openid
	    $tools = new \JsApiPay();
	    $openId = $order['openid'];
	    
	    \WxPayConfig::$APPID = C('APPID');
	    \WxPayConfig::$APPSECRET = C('APPSECRET');
	    \WxPayConfig::$MCHID = C('MCHID');
	    \WxPayConfig::$KEY = C('MCH_KEY');
	    
	    
	    //②、统一下单
	    $input = new \WxPayUnifiedOrder();
	    $input->SetBody("商品信息：".$order['body']);//商品或支付单简要描述
	    $input->SetAttach($_GET['order_sn']);//附加数据
	    //$input->SetAttach("地址".$_GET['addre']);//附加数据
	    //$input->SetOut_trade_no(WxPayConfig::$MCHID.date("YmdHis"));//订单号
	    $input->SetOut_trade_no($order['order_sn']);//订单号
	    $input->SetTotal_fee($order['price'] * 100);//总价
	    $input->SetTime_start(date("YmdHis"));//开始时间
	    $input->SetTime_expire(date("YmdHis", time() + 3600));
	    $input->SetGoods_tag("test");//商品标记
	    $input->SetNotify_url("http://" . $_SERVER['HTTP_HOST'] . "/api/wxpay/notify_wx");//回调通知地址
	    $input->SetTrade_type("JSAPI");
	    $input->SetOpenid($openId);
	    $order = \WxPayApi::unifiedOrder($input);

	    $jsApiParameters = $tools->GetJsApiParameters($order);
	    
	    $this->assign('jsApiParameters', $jsApiParameters);
	    $this->assign('goback', $goback);
	    
	    $this->display(':wx_pay');
	}
	
	/*
    public function pay()
    {
    	$price = $_REQUEST['price'];
    	$body = '';
    	if (isset($_REQUEST['body']))
    	   $body = $_REQUEST['body'];
    	$openid = $_REQUEST['openid'];
    	$mchid = $_REQUEST['mch'];
    	$pay_notify = '';
    	if (isset($_REQUEST['pay_notify']))
    	   $pay_notify = $_REQUEST['pay_notify'];
    	$pay_goback = '';
    	if (isset($_REQUEST['pay_goback']))
    	   $pay_goback = $_REQUEST['pay_goback'];
    	$memo = '';
    	if (isset($_REQUEST['memo']))
    	   $memo = $_REQUEST['memo'];
    	
    	$mch = $this->wx_mch_db->where("id=$mchid")->find();
    	
    	if ($mch == null)
    	{
    	    echo '参数缺少';
    	    return;
    	}   
    	
    	$order_sn = sp_get_order_sn();
    	
    	$data = array(
    			'price' => $price,
    	        'body' => $body,
    	        'mch' => $mchid,
    			'openid' => $openid,
    	        'order_sn' => $order_sn,
    	        'from_order_sn' => $_REQUEST['order_sn'],
    			'pay_notify' => $pay_notify,
    			'status' => 0,
    			'create_time' => date('Y-m-d H:i:s'),
    	        'memo' => $memo
    	);
    	
    	$rst = $this->wx_pay_db->add($data);
    	
    	$url = "wxpay/jsapi.php?body=$body&total=$price&openid=$openid&order_sn=$order_sn&goback=" . urlencode($pay_goback);
    	redirect($url);
    }
    */
}
