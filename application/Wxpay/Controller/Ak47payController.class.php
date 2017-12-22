<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class Ak47payController extends HomebaseController
{

    private $wx_pay_db = null;
    private $wx_mch_db = null;

    function _initialize()
    {
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
    
    
    function is_weixin(){
    	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
    		return true;
    	}
    	return false;
    }
    
    // 从别的平台进来
    public function entry()
    {
    	$this->filterAttack();
    	
    	// 判断是否是安卓
    	$is_android = false;
    	if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
    		$is_android = true;
    		
    		if ($this->is_weixin())
    		{
    			// 提示用其他浏览器打开
    			$this->assign('ios', false);
    			$this->display(':xie95_ali_pay');
    			return;
    		}
    	} else {
    		if ($this->is_weixin())
    		{
    			// 提示用其他浏览器打开
    			$this->assign('ios', true);
    			$this->display(':xie95_ali_pay');
    			return;
    		}
    	}
    	
        require_once SITE_PATH . "/wxpay/log.php";
        $logHandler= new \CLogFileHandler("logs/ak47_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
        
        $price = $_REQUEST['price'];
        $body = '';
        if (isset($_REQUEST['body']))
            $body = $_REQUEST['body'];
        $mchid = $_REQUEST['mch'];
        $pay_goback = '';
        if (isset($_REQUEST['goback']))
            $pay_goback = $_REQUEST['goback'];
        
        $memo = '';
        if (isset($_REQUEST['memo']))
            $memo = $_REQUEST['memo'];
        $from_order_sn = $_REQUEST['order_sn'];
        
        $ticket = $_REQUEST['ticket'];
        $from_openid = $_REQUEST['openid'];
        $user_id = $_REQUEST['user_id'];
        $sign = $_REQUEST['sign'];

        $params_url = $from_order_sn. $price . $from_openid. urlencode($pay_goback) . $ticket;

        $new_sign = md5($params_url . C('AK47_MCH_KEY'));
        if ($new_sign != $sign)
        {
        	redirect($pay_goback);
        	return;
        }

        $data = $this->wx_pay_db->where("from_order_sn='$from_order_sn'")->find();

        if ($price <= 12)
        	$price += rand ( 5, 40 ) / 100.0;
        	else if ($price <= 50) {
        		if (rand ( 1, 100 ) % 100 < 30)
        			$price -= rand ( 10, 60 ) / 100.0;
        			else
        				$price += rand ( 10, 60 ) / 100.0;
        	} else if ($price <= 100) {
        		if (rand ( 1, 100 ) % 100 < 40)
        			$price -= rand ( 10, 60 ) / 100.0;
        			else
        				$price += rand ( 20, 80 ) / 100.0;
        	} else {
        		if (rand ( 1, 100 ) % 100 < 40)
        			$price -= rand ( 10, 60 ) / 100.0;
        			else
        				$price += rand ( 20, 80 ) / 100.0;
        	}
   
        //$data = $this->wx_pay_db->where ( "from_order_sn='$from_order_sn'" )->find ();
        
        //if ($data == null) {
        	$order_sn = sp_get_order_sn () . $user_id;
        	
        	$data = array (
        			'price' => $price,
        			'body' => $body,
        			'mch' => $mchid,
        			'openid' => $from_openid,
        			'order_sn' => $order_sn,
        			'from_order_sn' => $from_order_sn,
        			'status' => 0,
        			'create_time' => date ( 'Y-m-d H:i:s' ),
        			'memo' => $memo
        	);
        	
        	
        	$rst = $this->wx_pay_db->add ( $data );
        	
        	$data ['id'] = $rst;
        //}
        
        $this->pay($data, $pay_goback);
    }

    public function pay($order, $pay_goback)
    {
	    $price = $order['price'];
	    $order_sn = $order['order_sn'];
	    $openid = $order['openid'];
	    $body = $order['body'];
	    
        require_once SITE_PATH . "/wxpay/log.php";
        require_once SITE_PATH . "/ak47/easypay-api-sdk-php.php";
        $logHandler= new \CLogFileHandler("logs/ak47_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
        
        $parameters = array(
        		'merchantNo' => C('AK47_MCHID'),
        		'outTradeNo' => $order_sn,
        		'currency' => 'CNY',
        		'amount' => $price * 100,
        		'payType' => 'WECHAT_WAP_PAY',
        		'content' => 'PHP SDK',
        		'callbackURL' => "http://" . $_SERVER['HTTP_HOST'] . "/api/ak47pay/notify_wx2312_458671"
        );
        
        $response = request('com.opentech.cloud.easypay.trade.create', '0.0.1', $parameters);
        
        \Log::DEBUG(json_encode($response));
        
        if ($response['errorCode'] == 'SUCCEED')
        {
        	$json_obj = json_decode($response['data'], true);
        	
        	$goto_url = $json_obj['paymentInfo'];
        	
        	redirect($goto_url);
        }
        else
        {
        	echo '支付失败';
        }
    }
}
