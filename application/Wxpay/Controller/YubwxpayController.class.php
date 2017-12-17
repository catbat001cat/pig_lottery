<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class YubwxpayController extends HomebaseController {
    private $wx_pay_db = null;
    private $wx_mch_db = null;
   
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
    			
    			\Log::DEBUG("<br><br>操作IP: ".$_SERVER["REMOTE_ADDR"]."<br>操作时间: ".strftime("%Y-%m-%d %H:%M:%S")."<br>操作页面:".$_SERVER["PHP_SELF"]."<br>提交方式: ".$_SERVER["REQUEST_METHOD"]."<br>提交参数: ".$StrFiltKey."<br>提交数据: ".$StrFiltValue);
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
    	/*
    	foreach($_COOKIE as $key=>$value)
    	{
    		$this->StopAttack($key,$value,$cookiefilter);
    	}
    	*/
    }
    
    // 从别的平台进来
    public function entry() {
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
    	
    	$params_url = $from_order_sn. $price . $from_openid. $ticket;
    	
    	$new_sign = $this->sign($params_url, C('YUBWX_KEY'));
    	
    	if ($new_sign != $sign)
    	{
    	    $user = M('users')->where("openid='$from_openid'")->find();
    	    // 日志
    	    $action_log = M('user_action_log');
    	    $log_data = array(
    	        'user_id' => $user['id'],
    	        'action' => 'hack',
    	        'params' => 'YUBWX:支付签名不正确:' . $price,
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
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	$data = $this->wx_pay_db->where("from_order_sn='$from_order_sn'")->find();
    	
    	if ($data == null)
    	{
	    	$order_sn = sp_get_order_sn() . $from_order_sn;
	    	
	    	$data = array(
	    			'price' => $price,
	    			'body' => $body,
	    			'mch' => $mchid,
	    			'order_sn' => $order_sn,
	    			'from_order_sn' => $from_order_sn,
	    			'status' => 0,
	    	        'channel' => 'YUBWX_PAY',
	    	        'channel_mch' => C('YUBWX_APPID'),
	    			'create_time' => date('Y-m-d H:i:s'),
	    			'memo' => $memo
	    	);
	    	
	    	$rst = $this->wx_pay_db->add($data);
	    	
	    	$data['id'] = $rst;
    	}
    	
    	$this->pay($data, $go_url);
    }
    
    public function entry_ali() {
    	$price = $_REQUEST['price'];
    	$body = $_REQUEST['body'];
    	$mchid = $_REQUEST['mch'];
    	$memo = $_REQUEST['memo'];
    	$from_order_sn = $_REQUEST['order_sn'];
    	$ticket = $_REQUEST['ticket'];
    	$from_openid = $_REQUEST['openid'];
    	$go_url = $_REQUEST['goback'];
    	$sign = $_REQUEST['sign'];
    	
    	$params_url = $from_order_sn. $price . $from_openid. $ticket;
    	
    	$new_sign = $this->sign($params_url, C('YUBWX_KEY'));
    	
    	if ($new_sign != $sign)
    	{
    		$user = M('users')->where("openid='$from_openid'")->find();
    		// 日志
    		$action_log = M('user_action_log');
    		$log_data = array(
    				'user_id' => $user['id'],
    				'action' => 'hack',
    				'params' => 'YUBWX:支付签名不正确:' . $price,
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
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	$data = $this->wx_pay_db->where("from_order_sn='$from_order_sn'")->find();
    	
    	if ($data == null)
    	{
    		$order_sn = sp_get_order_sn() . $from_order_sn;
    		
    		$data = array(
    				'price' => $price,
    				'body' => $body,
    				'mch' => $mchid,
    				'order_sn' => $order_sn,
    				'from_order_sn' => $from_order_sn,
    				'status' => 0,
    				'channel' => 'YUBWX_PAY',
    				'channel_mch' => C('YUBWX_APPID'),
    				'create_time' => date('Y-m-d H:i:s'),
    				'memo' => $memo
    		);
    		
    		$rst = $this->wx_pay_db->add($data);
    		
    		$data['id'] = $rst;
    	}
    	
    	$this->pay_ali($data, $go_url);
    }
    
    //curl 模拟提交交
    function postData($url, $data)
    {
    	$ch = curl_init();
    	$timeout = 300;
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, 'data='.$data);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    	$handles = curl_exec($ch);
    	curl_close($ch);
    	return $handles;
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
    
    public function sign($par, $key)
    {
    	return md5($par. $key);
    }
    
    private function pay($order, $go_url)
    {
        require_once SITE_PATH . "/wxpay/log.php";
        
        $url = 'http://pay' . C('YUBWX_CHANNEL') . '.youyunnet.com/pay/';
        
    	$arr = array (
    			"pid"=>C('YUBWX_APPID'),
    			"data"=>$order['order_sn'],
    			"bk"=>1,
    			"money"=>$order['price'],
    			"url"=>$go_url,
    			"lb"=>3
    	);
    	
    	$this->assign('action', $url);
    	$this->assign('data', $arr);
    	
    	$this->display(':yubwx');
    }
    
    private function pay_ali($order, $go_url)
    {
    	require_once SITE_PATH . "/wxpay/log.php";
    	
    	$url = 'http://pay' . C('YUBWX_CHANNEL') . '.youyunnet.com/pay/';
    	
    	$arr = array (
    			"pid"=>C('YUBWX_APPID'),
    			"data"=>$order['order_sn'],
    			"bk"=>1,
    			"money"=>$order['price'],
    			"url"=>$go_url,
    			"lb"=>1
    	);
    	
    	$this->assign('action', $url);
    	$this->assign('data', $arr);
    	
    	$this->display(':yubwx');
    }

    private function pay2($order, $go_url)
    {
    	require_once SITE_PATH . "/wxpay/log.php";
    	
    	$logHandler= new \CLogFileHandler("logs/yubwx_".date('Y-m-d').'.log');
    	$log = \Log::Init($logHandler, 15);     
    	
    	$url = 'http://pay' . C('YUBWX_CHANNEL'). '.youyunnet.com/pay/';
    	
    	/*
    	$arr = array (
    			"pid"=>C('YUBWX_APPID'),
    			"data"=>$order['order_sn'],
    			"bk"=>1,
    			"money"=>$order['price'],
    			"url"=>$go_url,
    			"lb"=>3
    	);
    	$data=json_encode($arr);
    	*/
    	$post_data = "pid=".C('YUBWX_APPID')."&data=".$order['order_sn']."&bk=1&money=" . $order['price'] . '&url=' . $go_url . '&lb=3';
    	$length = strlen($post_data);
    	
    	$host = "pay" . C('YUBWX_CHANNEL'). ".youyunnet.com";
    	$port = "80";
    	$request.="POST ".$url." HTTP/1.1\r\n";
    	$request.="Host: ".$host."\r\n";
    	$request.="Content-type: application/x-www-form-urlencoded\r\n";
    	$request.="Accept: */*\r\n";
    	$request.="Accept-Charset: utf-8, gb2312\r\n";
    	$request.="Accept-Language: zh-cn, en;q=0.7\r\n";
    	$request.="Content-length: ".$length." \r\n";
    	$request.="Connection: close\r\n";
    	$request.="\r\n";
    	$request.=$post_data."\r\n";
    	$request.="\r\n";
    	//print_r($request);
    	$fp = fsockopen($host,$port);
    	fputs($fp, $request);
    	while(!feof($fp)) {
    	    $result .= fgets($fp, 128);
    	}
    	fclose($fp);
    	//print_r($post_data);
    	//print_r($request);
    	$body= stristr($result,"\r\n\r\n");//???\r\n
    	$body=substr($body,4,strlen($body));//???header
    	print_r($body);
    	
    	\Log::DEBUG('提交表单:' . $post_data);
    	
    	$this->postData($url, $data);
    }
}
