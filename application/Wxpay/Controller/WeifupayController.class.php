<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class WeifupayController extends HomebaseController {
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
    
    // 从别的平台进来
    public function entry() {
    	$this->filterAttack();
    	
        require_once "jssdk.php";
    
        $appid = C('WFT_APPID');
        $appsecret = C('WFT_APPSECRET');
    
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
         
        $redirect_url = "http://" . $_SERVER["HTTP_HOST"] . "/index.php?g=Wxpay&m=weifupay&a=login&mch=$mchid&memo=$memo&order_sn=$order_sn&body=$body&price=$price&pay_goback=" . urlencode($pay_goback);
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
    	$this->filterAttack();
    	
        require_once "jssdk.php";
        require_once SITE_PATH . "/wxpay/log.php";
    
        if (isset($_GET['code']))
            $code = $_GET["code"];
        else
            $code = '';
    
        $appid = C('WFT_APPID');
        $appsecret = C('WFT_APPSECRET');
    
        $jssdk = new \JSSDK($appid, $appsecret);
        $res = $jssdk->getAuthAccessToke($code);
        if (!property_exists($res, 'openid'))
        {
            //echo "<script>alert('请在微信打开');</script>";
        }
        else
        {
        }
        
        $logHandler= new \CLogFileHandler("logs/wft_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
        
        \Log::DEBUG('WftpayController支付调用开始:' . json_encode($res));
    
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
        
        \Log::DEBUG('WftpayController支付调用开始:' . json_encode($result));
    
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
            'openid' => $res->openid,
            'order_sn' => $order_sn,
            'from_order_sn' => $from_order_sn,
            'status' => 0,
            'create_time' => date('Y-m-d H:i:s'),
            'memo' => $memo
        );
    
        $rst = $this->wx_pay_db->add($data);
        
        $data['id'] = $rst;
        
        $this->pay($data, $pay_goback);
    }
    
    public function pay($order, $goback)
    {
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
        $reqHandler->setParameter('service','pay.weixin.jspay');//接口类型
        $reqHandler->setParameter('mch_id',C('WFT_MCHID'));//必填项，商户号，由平台分配
        $reqHandler->setParameter('version',$cfg->C('version'));
        
        $callback_url = $goback;
        
        $reqHandler->setParameter('is_raw','0');
        //通知地址，必填项，接收平台通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
        $reqHandler->setParameter('notify_url', "http://" . $_SERVER['HTTP_HOST'] . "/api/weifupay/notify_wx2312");//
        $reqHandler->setParameter('callback_url', $callback_url);
        $reqHandler->setParameter('nonce_str',mt_rand(time(),time()+rand()));//随机字符串，必填项，不长于 32 位
        $reqHandler->setParameter('sub_appid',C('WFT_APPID'));
        $reqHandler->setParameter('sub_openid', $openid);
        $reqHandler->setParameter("total_fee", $price);
        $reqHandler->setParameter("body", '充值' . $price . '元');
        $reqHandler->setParameter("out_trade_no", $order_sn);
        $reqHandler->setParameter("mch_create_ip", get_client_ip());
        $reqHandler->createSign();//创建签名
        
        $params = $reqHandler->getAllParameters();
        $data = \Utils::toXml($params);

        $pay->setReqContent($reqHandler->getGateURL(),$data);
        if($pay->call()){
            $resHandler->setContent($pay->getResContent());
            $resHandler->setKey($reqHandler->getKey());
            if($resHandler->isTenpaySign()){
                //当返回状态与业务结果都为0时才返回，其它结果请查看接口文档
                if($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0){
                    //echo json_encode(array('token_id'=>$resHandler->getParameter('token_id'),
                      //  'pay_info'=>$resHandler->getParameter('pay_info')));

                    $token_id  = $resHandler->getParameter('token_id');
                    $url = "https://pay.swiftpass.cn/pay/jspay?token_id={$token_id}&showwxtitle=1";
                    redirect($url);
                }else{
                    //echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message')));
                    //exit();
                	\Log::DEBUG('WftpayController Error Code:' . $resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message'));
                    
                    $ret_data = array(
                        'status' => 2,
                        'create_time' => date('Y-m-d H:i:s'),
                        'memo' => '支付失败:' . $resHandler->getParameter('message')
                    );
                    
                    $rst = $this->wx_pay_db->where('id=' . $order['id'])->save($ret_data);
                    
                    $pay_goback = $_REQUEST['pay_goback'];
                    
                    redirect($pay_goback);
                }
            }
            
            \Log::DEBUG('WftpayController Error Code:' . $resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message'));
            
            //echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message')));
        }else{
        	\Log::DEBUG('WftpayController Error Code:' . $resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message'));
        	
            //echo json_encode(array('status'=>500,'msg'=>'Response Code:'.$pay->getResponseCode().' Error Info:'.$pay->getErrInfo()));
            
            $ret_data = array(
                'status' => 2,
                'create_time' => date('Y-m-d H:i:s'),
                'memo' => '支付失败:' . $pay->getErrInfo()
            );
            
            $rst = $this->wx_pay_db->where('id=' . $order['id'])->save($ret_data);
            
            $pay_goback = $_REQUEST['pay_goback'];
            
            redirect($pay_goback);
        }
    }
}
