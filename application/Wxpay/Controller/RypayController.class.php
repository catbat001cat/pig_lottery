<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class RypayController extends HomebaseController
{

    private $wx_pay_db = null;
    
    private $url = 'http://www.zhuangshizhixiao.com:8080/payment';
    private $wx_mch_db = null;

    function _initialize()
    {
        parent::_initialize();
        
        $this->wx_pay_db = M('wx_pay');
        $this->wx_mch_db = M('wx_mch');
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
        require_once "jssdk.php";
        
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
        $from_order_sn= $_REQUEST['order_sn'];
        
        $ticket = $_REQUEST['ticket'];
        $from_openid = $_REQUEST['openid'];
        $sign = $_REQUEST['sign'];
        
        $params_url = $from_order_sn. $price . $from_openid. urlencode($pay_goback) . $ticket;
        
        $new_sign = $this->sign($params_url, C('RY_MCH_KEY'));

        if ($new_sign != $sign)
        {
        	redirect($_REQUEST['goback']);
        	return;
        }
        
        /*
        if ($price <= 12)
        	$price += rand ( 1, 10 ) / 100.0;
        	else {
        		if (rand ( 0, 100 ) % 100 < 30)
        			$price -= rand ( 1, 10 ) / 100.0;
        			else
        				$price += rand ( 1, 10 ) / 100.0;
        	}
        	*/

        $data = $this->wx_pay_db->where("from_order_sn='$from_order_sn'")->find();
        
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
        			'channel' => 'RY_PAY',
        			'channel_mch' => C('RY_MCHID'),
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

    function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        
        for ($i = 0; $i < $length; $i ++) {
            $str .= $strPol[rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        
        return $str;
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
    
    public function MakeSign($params)
    {
        require_once SITE_PATH . "/wxpay/log.php";
        
        //签名步骤一：按字典序排序参数
        $string = 'merId=' . $params['merId'] . '&serialNo=' . $params['serialNo'] . '&money=' . $params['money'] . '&notify_url=' . $params['notify_url'];
        
        $logHandler= new \CLogFileHandler("logs/ry_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
        
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".C('RY_MCH_KEY');
                
        \Log::DEBUG('makesign:' . $string);
    
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = $string;//strtoupper($string);
    
        return $result;
    }
    
    private function httpGet($url) {
    	$curl = curl_init();
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    	curl_setopt($curl, CURLOPT_URL, $url);
    	
    	$res = curl_exec($curl);
    	curl_close($curl);
    	
    	return $res;
    }
    
    public function http_post_data($url, $data_string) {
    	$cacert = ''; // CA根证书 (目前暂不提供)
    	$CA = false; // HTTPS时是否进行严格认证
    	$TIMEOUT = 30; // 超时时间(秒)
    	$SSL = substr ( $url, 0, 8 ) == "https://" ? true : false;
    	
    	$ch = curl_init ();
    	if ($SSL && $CA) {
    		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, true ); // 只信任CA颁布的证书
    		curl_setopt ( $ch, CURLOPT_CAINFO, $cacert ); // CA根证书（用来验证的网站证书是否是CA颁布）
    		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 2 ); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
    	} else if ($SSL && ! $CA) {
    		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false ); // 信任任何证书
    		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 1 ); // 检查证书中是否设置域名
    	}
    	
    	curl_setopt ( $ch, CURLOPT_TIMEOUT, $TIMEOUT );
    	curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $TIMEOUT - 2 );
    	curl_setopt ( $ch, CURLOPT_POST, 1 );
    	curl_setopt ( $ch, CURLOPT_URL, $url );
    	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data_string );
    	curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
    			'Content-Type:application/xml;charset=utf-8',
    			'Content-Length:' . strlen ( $data_string )
    	) );
    	
    	ob_start ();
    	curl_exec ( $ch );
    	$return_content = ob_get_contents ();
    	ob_end_clean ();
    	
    	$return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
    	return array (
    			$return_code,
    			$return_content
    	);
    }

    public function pay($order, $goback)
    {
	    $price = $order['price'];
	    $order_sn = $order['order_sn'];
	    $openid = $order['openid'];
	    $body = $order['body'];
	    
        require_once SITE_PATH . "/wxpay/log.php";

        $logHandler= new \CLogFileHandler("logs/ry_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);

        $params = array();
        $params['merId'] = C('RY_MCHID');
        $params['serialNo'] = $order_sn;
        $params['productInfo'] = '充值' . $price . '元';
        $params['codeType'] = '02';
        $params['money'] = $price * 100;
        $params['date'] = date('Ymd H:i:s');
        $params['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/api/rypay/notify_wx2312_458671";
     
        $sign = $this->MakeSign($params);
        
        $params['sign'] = $sign;
        
        //$jsonStr = json_encode ( $params );
        $full_url = $this->url . '?' . $this->ToUrlParams($params) . '&sign=' . $sign;
        
        \Log::DEBUG ( 'RypayController支付调用开始:' . $full_url );
        
        $return_content = $this->httpGet( $this->url );
        \Log::DEBUG ( $return_content );
        $respJson = json_decode ( $return_content, true );
        if (!empty($respJson ['codeURL'])) {
            redirect ( $respJson['codeURL'] );
             
        } else {
            redirect ( $goback);
            return;
        }
        
        //list ( $return_code, $return_content ) = $this->http_post_data ( $this->url, $jsonStr );
        /*
        list ( $return_code, $return_content ) = $this->http_post_data ( $this->url, $jsonStr );
        \Log::DEBUG ( $return_content );
        $respJson = json_decode ( $return_content, true );
        if (!empty($respJson ['codeURL'])) {
        	redirect ( $respJson['codeURL'] );
        	
        } else {
        	redirect ( $goback);
        	return;
        }
        */
    }
}
