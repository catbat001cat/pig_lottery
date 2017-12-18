<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class WeifuwappayController extends HomebaseController {
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
    	$pregs = '/select|insert|drop|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
    	
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
    
    function is_weixin(){
    	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
    		return true;
    	}
    	return false;
    } 
    
    // 从别的平台进来
    public function entry() {
    	$this->filterAttack();
    	
    	$is_android = false;
    	if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
    		$is_android = true;
    		
    		if ($this->is_weixin())
    		{
    			// 提示用其他浏览器打开
    			$this->assign('ios', false);
    			$this->display(':wft_wap_pay');
    			return;
    		}
    	} else {
    		if ($this->is_weixin())
    		{
    			// 提示用其他浏览器打开
    			$this->assign('ios', true);
    			$this->display(':wft_wap_pay');
    			return;
    		}
    	}   
    	
        require_once "jssdk.php";

        $price = intval($_REQUEST['price']);
        
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
        
        $from_order_sn= $_REQUEST['order_sn'];
        $from_openid= $_REQUEST['openid'];
        $sign = $_REQUEST['sign'];
        $ticket = $_REQUEST['ticket'];
        
        $params_url = $from_order_sn. $price . $from_openid . urlencode($pay_goback) . $ticket;
        
        $new_sign = md5($params_url . C('WFT_MCH_KEY'));

        if ($new_sign != $sign)
        {
        	$user = M('users')->where("openid='$from_openid'")->find();
        	// 日志
        	$action_log = M('user_action_log');
        	$log_data = array(
        			'user_id' => $user['id'],
        			'action' => 'hack',
        			'params' => 'WFT:支付签名不正确:' . $price,
        			'ip' => get_client_ip(0, true),
        			'create_time' => date('Y-m-d H:i:s')
        	);
        	$action_log->add($log_data);
        
        	echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
        	return;
        }
        
        if ($price <= 12)
        	$price += rand ( 2, 20 ) / 100.0;
        	else {
        		if (rand ( 1, 100 ) % 100 < 30)
        			$price -= rand ( 3, 30 ) / 100.0;
        			else
        				$price += rand ( 5, 50 ) / 100.0;
        	}
        	
        	$data = $this->wx_pay_db->where ( "from_order_sn='$from_order_sn'" )->find ();
        	
        	
        	if ($data == null) {
        		$order_sn = sp_get_order_sn ();
        		
        		$data = array (
        				'price' => $price * 100,
        				'body' => $body,
        				'mch' => $mchid,
        				'openid' => $from_openid,
        				'order_sn' => $order_sn,
        				'from_order_sn' => $from_order_sn,
        				'transition_id' => '',
        				'status' => 0,
        				'channel' => 'WFT',
        				'create_time' => date ( 'Y-m-d H:i:s' ),
        				'memo' => $memo
        		);
        		
        		$rst = $this->wx_pay_db->add ( $data );
        		
        		$data ['id'] = $rst;
        	}
        
        $this->pay($data, $pay_goback);
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
    
    
    
    public function pay($order, $goback)
    {
    	
    	require_once SITE_PATH . "/wxpay/log.php";
    	
    	$logHandler= new \CLogFileHandler("logs/wft_".date('Y-m-d').'.log');
    	$log = \Log::Init($logHandler, 15);   
    	
	    $price = $order['price'];
	    $order_sn = $order['order_sn'];
	    $openid = $order['openid'];
	    $body = $order['body'];
	    
        require_once SITE_PATH . '/swiftpass/Utils.class.php';
        require_once SITE_PATH . '/swiftpass/config.php';
        require_once SITE_PATH . '/swiftpass/RequestHandler.class.php';
        require_once SITE_PATH . '/swiftpass/ClientResponseHandler.class.php';
        require_once SITE_PATH . '/swiftpass/PayHttpClient.class.php';
        
        $resHandler = new \ClientResponseHandler();
        $reqHandler = new \RequestHandler();
        $pay = new \PayHttpClient();
        $cfg = new \Config();
        
        $reqHandler->setGateUrl($cfg->C('url'));
        $reqHandler->setKey(C('WFT_MCH_KEY'));
        
        //通知地址，必填项，接收威富通通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
        //$notify_url = 'http://'.$_SERVER['HTTP_HOST'];
        //$this->reqHandler->setParameter('notify_url',$notify_url.'/payInterface/request.php?method=callback');
        $reqHandler->setParameter('service','pay.weixin.wappay');//接口类型
        $reqHandler->setParameter('mch_id',C('WFT_MCHID'));//必填项，商户号，由平台分配
        $reqHandler->setParameter('version',$cfg->C('version'));
        
        $callback_url = $goback;
        
        //通知地址，必填项，接收平台通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
        $reqHandler->setParameter('notify_url', "http://" . $_SERVER['HTTP_HOST'] . "/api/weifupay/notify_wx2312");//
        $reqHandler->setParameter('callback_url', $callback_url);
        $reqHandler->setParameter('nonce_str',mt_rand(time(),time()+rand()));//随机字符串，必填项，不长于 32 位
        $reqHandler->setParameter("total_fee", $price);
        $reqHandler->setParameter("body", '充值' . ($price / 100.0) . '元');
        $reqHandler->setParameter("out_trade_no", $order_sn);
        $reqHandler->setParameter("device_info", 'AND_WAP');
        $reqHandler->setParameter("mch_app_name", 'jd');
        $reqHandler->setParameter('mch_app_id', "https://m.jd.com");
        $reqHandler->setParameter("mch_create_ip", '0.0.0.0');//get_client_ip());
        $reqHandler->setParameter('sign_type', 'MD5');
        $reqHandler->createSign();//创建签名
        
        $params = $reqHandler->getAllParameters();
        $data = \Utils::toXml($params);
        
        \Log::DEBUG('WftpayController:' . $data);

        $pay->setReqContent($reqHandler->getGateURL(), $data);
        if($pay->call()){
            $resHandler->setContent($pay->getResContent());
            $resHandler->setKey($reqHandler->getKey());
            \Log::DEBUG('WftpayController:' . $reqHandler->getKey() . ',' . C('WFT_MCHID'));
            
            if($resHandler->isTenpaySign()){
                //当返回状态与业务结果都为0时才返回，其它结果请查看接口文档
                if($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0){
                	$url = $resHandler->getParameter("pay_info");
                    redirect($url);
                }else{
                	\Log::DEBUG('WftpayController Error Code:' . $resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message'));
                	
                    
                    redirect($goback);
                    
                    return;
                }
            }
            
            \Log::DEBUG('WftpayController Error Code:' . $resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message'));
            
            redirect($goback);
            
            //echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message')));
        }else{
        	\Log::DEBUG('WftpayController Error Code:' . $resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message'));
        	
            $pay_goback = $_REQUEST['pay_goback'];
            
            redirect($goback);
        }
    }
}
