<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class Fubei51wxpayController extends HomebaseController {
    private $wx_pay_db = null;
    private $wx_mch_db = null;
    const GATEWAY = "https://shq-api-test.51fubei.com/gateway";
   
    function _initialize(){
        parent::_initialize();
    
        $this->wx_pay_db = M('wx_pay');
        $this->wx_mch_db = M('wx_mch');
    }
    
    public function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq)
    {
    	//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
    	//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|union|into|load_file|outfile/';
    	//$pregs = '/select|insert|drop|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
    	$pregs = '/select|insert|drop|update|document|eval|delete|script|alert|\'|\/\*|\#|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
    	
    	if ($StrFiltKey == 'req_url' || $StrFiltKey == 'goback')
    		return;
    		
    		if(is_array($StrFiltValue))
    		{
    			$StrFiltValue=implode($StrFiltValue);
    		}
    		
    		$check= preg_match($pregs,$StrFiltValue);
    		if($check == 1){
    			require_once SITE_PATH . "/wxpay/log.php";
    			
    			$logHandler = new \CLogFileHandler ( "logs/hack_" . date ( 'Y-m-d' ) . '.log' );
    			$log = \Log::Init ( $logHandler, 15 );
    			
    			\Log::DEBUG("<br><br>操作IP: ".$_SERVER["REMOTE_ADDR"]."<br>操作时间: ".strftime("%Y-%m-%d %H:%M:%S")."<br>操作页面:".$_SERVER["REQUEST_URI"]."<br>提交方式: ".$_SERVER["REQUEST_METHOD"]."<br>提交参数: ".$StrFiltKey."<br>提交数据: ".$StrFiltValue);
    			print "result notice:Illegal operation!";
    			exit();
    		}
    }
    
    public function filterAttack()
    {
    	foreach($_GET as $key=>$value)
    	{
    		$this->StopAttack($key,$value,$getfilter);
    	}
    	foreach($_POST as $key=>$value)
    	{
    		$this->StopAttack($key,$value,$postfilter);
    	}
    	foreach($_COOKIE as $key=>$value)
    	{
    		$this->StopAttack($key,$value,$cookiefilter);
    	}
    }
    
    private static function generateSign($content,$key)
    {
    	return strtoupper(static::sign(static::getSignContent($content).$key));
    	
    }
    private static function getSignContent($content)
    {
    	ksort($content);
    	$signString = "";
    	foreach ($content as $key=>$val){
    		if(!empty($val)){
    			$signString .= $key."=".$val."&";
    		}
    	}
    	$signString = rtrim($signString,"&");
    	return $signString;
    	
    }
    private static function sign($data)
    {
    	return md5($data);
    }
    
    public static function execute($content,$key)
    {
    	$content['sign'] = static::generateSign($content,$key);
    	$result = static::mycurl(static::GATEWAY,$content);
    	return $result;
    	
    }
    private static function mycurl($url,$params = [])
    {
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_FAILONERROR, false);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($ch, CURLOPT_POST, true);
    	
    	if (!empty($params)) {
    		
    		curl_setopt($ch, CURLOPT_POST, true);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    		
    	}
    	$header = array("content-type: application/json");
    	curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    	$reponse = curl_exec($ch);
    	if (curl_errno($ch)) {
    		throw new Exception(curl_error($ch), 0);
    	} else {
    		$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    		if (200 !== $httpStatusCode) {
    			throw new Exception($reponse, $httpStatusCode);
    		}
    	}
    	curl_close($ch);
    	return $reponse;	
    }
    
    public function entry()
    {
    	$this->filterAttack();
    	
    	$price = $_REQUEST['price'];
    	$body = $_REQUEST['body'];
    	$mchid = $_REQUEST['mch'];
    	$memo = $_REQUEST['memo'];
    	$from_order_sn = $_REQUEST['order_sn'];
    	$ticket = $_REQUEST['ticket'];
    	$from_openid = $_REQUEST['openid'];
    	$go_url = $_REQUEST['goback'];
    	$sign = $_REQUEST['sign'];
    	
    	$params_url = $from_order_sn. $price . $from_openid . urlencode($go_url) . $ticket;
    	
    	$new_sign = static::sign($params_url . C('FUBEI51_KEY'));
    	
    	if ($new_sign != $sign)
    	{
    		$user = M('users')->where("openid='$from_openid'")->find();
    		// 日志
    		$action_log = M('user_action_log');
    		$log_data = array(
    				'user_id' => $user['id'],
    				'action' => 'hack',
    				'params' => 'FUBEI51:支付签名不正确:' . $price,
    				'ip' => get_client_ip(0, true),
    				'create_time' => date('Y-m-d H:i:s')
    		);
    		$action_log->add($log_data);
    		
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	$go_url = urlencode($go_url);
    	
    	$mch = $this->wx_mch_db->where("id=$mchid")->find();
    	
    	if ($mch == null)
    	{
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	$data = $this->wx_pay_db->where("from_order_sn='$from_order_sn'")->find();
    	
    	if ($data == null)
    	{
    		$order_sn = sp_get_order_sn();
    		
    		$data = array(
    				'price' => $price,
    				'body' => $body,
    				'mch' => $mchid,
    				'order_sn' => $order_sn,
    				'from_order_sn' => $from_order_sn,
    				'status' => 0,
    				'channel' => 'FUBEI51_PAY',
    				'channel_mch' => C('FUBEI51_APPID'),
    				'create_time' => date('Y-m-d H:i:s'),
    				'memo' => $memo
    		);
    		
    		$rst = $this->wx_pay_db->add($data);
    		
    		$data['id'] = $rst;
    	}
    	
    	$this->pay($data, $go_url);
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
    
    private function pay($order, $go_url)
    {
    	echo json_encode($order);
    	require_once SITE_PATH . "/wxpay/log.php";
    	
    	$logHandler= new \CLogFileHandler("logs/fubei51_".date('Y-m-d').'.log');
    	$log = \Log::Init($logHandler, 15);     
    	
	    $price = $order['price'];
	    $order_sn = $order['order_sn'];
	    $from_openid = $order['openid'];
	    $body = $order['body'];
	    
	    $data = [
	    		"app_id"=>C('FUBEI51_APPID'),
	    		"method"=>"openapi.payment.order.routescan",
	    		"format"=>"json",
	    		"sign_method"=>"md5",
	    		"nonce"=>$this->getRandChar(6)
	    ];
	    $content = [
	    		"merchant_order_sn"=>date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
	    		"total_fee"=>0.0
	    ];
	    $key = C('FUBEI51_KEY');
	    $data['biz_content'] = json_encode($content);
	    $result = static::execute($data,$key);
	    
	    echo $result;
	    
	    \Log::DEBUG("Fubei51wxpayController支付调用:" . $result);

	    if ($result['result_code'] == '200')
	    {
	    	/*
	    	$prepay_id = $result['data']['prepay_id'];
	    	$order_sn = $result['data']['order_sn'];
	    	$merchant_order_sn = $result['data']['merchant_order_sn'];
	    	
	    	$url = "http://shg-api-test.51fubei.com/paypage";
	    	$pay_url = $url . "?prepay_id=$prepay_id&callback_url=$go_url";
	    	redirect($pay_url);
	    	*/
	    }
	    else
	    {
	    	echo '<script>history.go(-2);</script>';
	    }
    }
}
