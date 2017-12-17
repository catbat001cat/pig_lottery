<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class WeifuqqpayController extends HomebaseController {
    private $wx_pay_db = null;
    private $wx_mch_db = null;
    function _initialize(){
        parent::_initialize();
    
        $this->wx_pay_db = M('wx_pay');
        $this->wx_mch_db = M('wx_mch');
    }
    // 从别的平台进来
    public function entry() {
    	$price = $_REQUEST['price'];
    	$body = $_REQUEST['body'];
    	$mchid = $_REQUEST['mch'];
    	$pay_goback = $_REQUEST['pay_goback'];
    	$memo = $_REQUEST['memo'];
    	$from_order_sn = $_REQUEST['order_sn'];
    	$ticket = $_REQUEST['ticket'];
    	$from_openid = $_REQUEST['openid'];
    	$sign = $_REQUEST['sign'];
    	
    	$params_url = $from_order_sn. $price . $from_openid. urlencode($pay_goback) . $ticket;
    	
    	$new_sign = $this->sign($params_url, C('WFT_QQ_MCH_KEY'));
    	
    	if ($new_sign != $sign)
    	{
    		echo '<script>window.close()</script>';
    		return;
    	}
    	
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

    public function pay($order, $goback)
    {
    	require_once SITE_PATH . "/wxpay/log.php";
    	
    	$logHandler= new \CLogFileHandler("logs/wft_qq_".date('Y-m-d').'.log');
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
        $reqHandler->setKey(C('WFT_QQ_MCH_KEY'));
        
        //通知地址，必填项，接收威富通通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
        //$notify_url = 'http://'.$_SERVER['HTTP_HOST'];
        //$this->reqHandler->setParameter('notify_url',$notify_url.'/payInterface/request.php?method=callback');
        $reqHandler->setParameter('service','pay.tenpay.wappay');//接口类型
        $reqHandler->setParameter('mch_id',C('WFT_QQ_MCHID'));//必填项，商户号，由平台分配
        $reqHandler->setParameter('version',$cfg->C('version'));
        
        $callback_url = $goback;
        
        $reqHandler->setParameter('is_raw','0');
        //通知地址，必填项，接收平台通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
        $reqHandler->setParameter('notify_url', "http://" . $_SERVER['HTTP_HOST'] . "/api/weifuqqpay/notify_wx2312");//
        $reqHandler->setParameter('callback_url', $callback_url);
        $reqHandler->setParameter('nonce_str',mt_rand(time(),time()+rand()));//随机字符串，必填项，不长于 32 位
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
            
            \Log::DEBUG('WftqqpayController req:' . $pay->getResContent());
            
            if($resHandler->isTenpaySign()){
                //当返回状态与业务结果都为0时才返回，其它结果请查看接口文档
                if($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0){
                    //echo json_encode(array('token_id'=>$resHandler->getParameter('token_id'),
                      //  'pay_info'=>$resHandler->getParameter('pay_info')));

                    $token_id  = $resHandler->getParameter('token_id');
                    $url = $resHandler->getParameter('pay_url');
                    redirect($url);
                }else{
                    //echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message')));
                    //exit();
                	\Log::DEBUG('WftqqpayController Error Code:' . $resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message'));
                    
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
            
            \Log::DEBUG('WftqqpayController Error Code:' . $resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message'));
            
            //echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message')));
        }else{
        	\Log::DEBUG('WftqqpayController Error Code:' . $resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message'));
        	
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
