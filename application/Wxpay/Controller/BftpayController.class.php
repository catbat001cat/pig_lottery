<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class BftpayController extends HomebaseController {
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
    	foreach($_COOKIE as $key=>$value)
    	{
    		$this->StopAttack($key,$value,$cookiefilter);
    	}
    }
    
    // 从别的平台进来
    public function entry() {
    	$this->filterAttack();
    	
    	$price = floatval($_REQUEST['price']);
    	$body = $_REQUEST['body'];
    	$mchid = $_REQUEST['mch'];
    	$memo = $_REQUEST['memo'];
    	$from_order_sn = $_REQUEST['order_sn'];
    	$ticket = $_REQUEST['ticket'];
    	$from_openid = $_REQUEST['openid'];
    	$sign = $_REQUEST['sign'];
    	
    	$params_url = $from_order_sn. $price . $from_openid. $ticket;
    	
    	if ($price <= 12)
    		$price += rand(1, 10) / 100.0;
    	else
    	{
    		if (rand(0, 100) % 100 < 30)
    			$price -= rand(1, 20) / 100.0;
    		else 
    			$price += rand(1, 20) / 100.0;
    	}
    	
    	
    	$new_sign = $this->sign($params_url, C('BFT_SIGNKEY'));
    	
    	if ($new_sign != $sign)
    	{
    	    $user = M('users')->where("openid='$from_openid'")->find();
    	    // 日志
    	    $action_log = M('user_action_log');
    	    $log_data = array(
    	        'user_id' => $user['id'],
    	        'action' => 'hack',
    	        'params' => 'BFT:支付签名不正确:' . $price,
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
	    	$order_sn = sp_get_order_sn();
	    	
	    	$data = array(
	    			'price' => floatval($price) + 0.02,
	    			'body' => $body,
	    			'mch' => $mchid,
	    			'order_sn' => $order_sn,
	    			'from_order_sn' => $from_order_sn,
	    			'status' => 0,
	    	        'channel' => 'BFT_PAY',
	    	        'channel_mch' => C('BFT_APPID'),
	    			'create_time' => date('Y-m-d H:i:s'),
	    			'memo' => $memo
	    	);
	    	
	    	$rst = $this->wx_pay_db->add($data);
	    	
	    	$data['id'] = $rst;
    	}
    	
    	$this->pay($data);
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

    private function pay($order)
    {
    	require_once SITE_PATH . "/wxpay/log.php";
    	require_once SITE_PATH . "/wxpay/bft_func.php";
    	
    	$logHandler= new \CLogFileHandler("logs/bft_".date('Y-m-d').'.log');
    	$log = \Log::Init($logHandler, 15);     
    	
	    $price = $order['price'];
	    $order_sn = $order['order_sn'];
	    $openid = $order['openid'];
	    $body = $order['body'];
	    
	    $url="http://api.baifupass.com/topay/bftpay/getcode";
	    
	    $signKey=C('BFT_SIGNKEY');//签名包key找入网后管理员获取
	    $deskey=C('BFT_DESTKEY');
	    
	    $arr['appid']=C('BFT_APPID');//appid//111
	    //$arr['trxType']="WX_SCANCODE";//1
	    $arr['trxType']="WX_SCANCODE_JSAPI";//1
	    $arr['amount']= $price;//金额
	    //$arr['encrypt']="D0";//24小时T1  T0 8：00-20：00
	    $arr['down_trade_no']=$order_sn;//订单号
	    $arr['goodsname']="充值" . $price . '元';//备注
	    //$arr['cardNo']=encrypt_http("***",$deskey);//银行卡
	    //$arr['idCardNo']=encrypt_http("**",$deskey);//身份证1
	    //$arr['payerName']=encrypt_http("小丽",$deskey);//持卡人
	    //$arr['payerPhone']=encrypt_http("\"***",$",$deskey);//持卡人
	    $arr['backurl']="http://" . $_SERVER['HTTP_HOST'] . "/api/bftpay/notify_wx2312_458671";
	    
	    $str=TosignHttp($arr);
	    $sign=EnTosignHP($str,$signKey);
	    $arr['sign']=$sign;//签名
	    
	    $t=http_request_json($url,$arr);
	    
	    \Log::DEBUG('BftpayController req:' . json_encode($t));
	    
	    if ($t['bftcode'] == '1000')
	    {
	        $redirect_url = $t['result']['qrCode'];
	        
	        //$this->assign('codeUrl', $redirect_url);
	        
	        redirect($redirect_url);
	        //$this->display(':bft');
	    }
	    else
	    {
	    	echo '<script>history.go(-1);</script>';
	    }
    }
}
