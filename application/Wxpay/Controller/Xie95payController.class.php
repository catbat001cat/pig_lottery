<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class Xie95payController extends HomebaseController
{

    private $wx_pay_db = null;
    private $url = 'http://mer.xie95.com:25141/acquire/acquirePlatform/api/transfer.html';
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
        $logHandler= new \CLogFileHandler("logs/xie95_".date('Y-m-d').'.log');
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
        $sign = $_REQUEST['sign'];

        $params_url = $from_order_sn. $price . $from_openid. urlencode($pay_goback) . $ticket;

        \Log::DEBUG($params_url . ',' . $new_sign . '|' . $sign);
        
        $new_sign = $this->sign($params_url, C('XIE95_DESKEY'));
        if ($new_sign != $sign)
        {
        	redirect($_REQUEST['goback']);
        	return;
        }

        $data = $this->wx_pay_db->where("from_order_sn='$from_order_sn'")->find();

        /*
        if ($price <= 12)
        	$price += rand ( 5, 40 ) / 100.0;
         else {
        		if (rand ( 1, 100 ) % 100 < 30)
        			$price -= rand ( 3, 30 ) / 100.0;
        		else
        			$price += rand ( 5, 50 ) / 100.0;
        }
        */
        /*
        if ($price <= 12)
        	$price += rand ( 10, 40 ) / 100.0;
       	else if ($price <= 50) {
        	if (rand ( 1, 100 ) % 100 < 30)
        		$price -= rand ( 10, 100 ) / 100.0;
        	else
        		$price += rand ( 30, 200 ) / 100.0;
        } else if ($price <= 100) {
        	if (rand ( 1, 100 ) % 100 < 30)
        		$price -= rand ( 10, 200 ) / 100.0;
        		else
        			$price += rand ( 40, 1000 ) / 100.0;
        } else {
        	if (rand ( 1, 100 ) % 100 < 30)
        		$price -= rand ( 10, 300 ) / 100.0;
        		else
        			$price += rand ( 40, 3000 ) / 100.0;
        }*/
        
        if ($data == null)
        {
	        $order_sn = sp_get_order_sn();
	        
	        $data = array(
	            'price' => $price,
	            'body' => $body,
	            'mch' => $mchid,
	            'openid' => $from_openid,
	            'order_sn' => $order_sn,
	            'from_order_sn' => $from_order_sn,
	            'status' => 0,
	            'create_time' => date('Y-m-d H:i:s'),
	            'memo' => $memo
	        );
	        
	        $rst = $this->wx_pay_db->add($data);
	        
	        $data['id'] = $rst;
        }
        
        $this->pay($data, $pay_goback);
    }
    
    public function sign($par, $key)
    {
    	return md5($par. $key);
    }
    
    
    private function postXmlCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    
        curl_setopt($ch,CURLOPT_URL, $url);
        //curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        //curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("curl出错，错误码:$error");
        }
    }
    
    public function ToUrlParams($params)
    {
        $buff = "";
        foreach ($params as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
    
        $buff = trim($buff, "&");
        return $buff;
    }

    private function MakeSign($params, $key)
    {
        require_once SITE_PATH . "/wxpay/log.php";
        
        //签名步骤一：按字典序排序参数
        ksort($params);
        $string = $this->ToUrlParams($params);
        
        $logHandler= new \CLogFileHandler("logs/xie95_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
        
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$key;
        
        \Log::DEBUG('makesign:' . '[' . $string . ']');
        
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        
        return $result;
    }
    
    public function http_post_data($url, $data_string ) {
    
        $cacert = '';	//CA根证书  (目前暂不提供)
        $CA = false ; 	//HTTPS时是否进行严格认证
        $TIMEOUT = 30;	//超时时间(秒)
        $SSL = substr($url, 0, 8) == "https://" ? true : false;
    
        $ch = curl_init ();
        if ($SSL && $CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 	// 	只信任CA颁布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $cacert); 			// 	CA根证书（用来验证的网站证书是否是CA颁布）
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 		//	检查证书中是否设置域名，并且是否与提供的主机名匹配
        } else if ($SSL && !$CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 	// 	信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); 		// 	检查证书中是否设置域名
        }
    
        curl_setopt ( $ch, CURLOPT_TIMEOUT, $TIMEOUT);
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $TIMEOUT-2);
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data_string );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
        'Content-Type:application/xml;charset=utf-8',
        'Content-Length:' . strlen( $data_string )
        ) );
    
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
    
        $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
        return array (
            $return_code,
            $return_content
        );
    }
    

    public function pay($order, $pay_goback)
    {
	    $price = $order['price'];
	    $order_sn = $order['order_sn'];
	    $openid = $order['openid'];
	    $body = $order['body'];
	    
        require_once SITE_PATH . "/wxpay/log.php";
        $logHandler= new \CLogFileHandler("logs/xie95_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
                
        $params = array();
        $params['tradeType'] = 'pay.submit';
        //$params['tradeType'] = 'polling.pay.submit';
        $params['version'] = '1.7';
        
        $params['settleCycle'] = '0';
        $params['channel'] = 'wxH5';//'wxPubQR';
        $dest_key = '';
        $params['mchId'] = C('XIE95_MCHID');
        $dest_key = C('XIE95_DESKEY');
        $params['body'] = '充值' . $price . '元';
        $params["outTradeNo"] = $order_sn;
        $params["amount"] = $price;
        $params['notifyUrl'] = "http://" . $_SERVER['HTTP_HOST'] . "/api/xie95pay/notify_wx2312_458671";
        $params['callbackUrl'] = $pay_goback;
        
        $params['sign'] = $this->MakeSign($params, $dest_key);        
        
        $jsonStr=json_encode($params);
        
        \Log::DEBUG('Xie95payController支付调用开始:' . $params['mchId']);
        
        \Log::DEBUG($jsonStr);
        
        list ( $return_code, $return_content ) = $this->http_post_data($this->url, $jsonStr);
        \Log::DEBUG($return_content);
        $respJson=json_decode($return_content, true);
        if($respJson['returnCode'] == '0' && $respJson['resultCode'] == '0'){
            //$this->assign('codeUrl', $respJson['payCode']);
        	\Log::DEBUG($respJson['payCode']);
        	
        	redirect($respJson['payCode']);
        }else{
            redirect($pay_goback);
            return;
        }
        
        //$this->display(':xie95');
    }
}
