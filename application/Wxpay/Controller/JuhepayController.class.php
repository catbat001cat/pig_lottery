<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class JuhepayController extends HomebaseController
{

    private $wx_pay_db = null;
    
    private $url = 'http://ijuhepay.cn/GateWay/ReceiveOrder.aspx';
    private $wx_mch_db = null;

    function _initialize()
    {
        parent::_initialize();
        
        $this->wx_pay_db = M('wx_pay');
        $this->wx_mch_db = M('wx_mch');
    }
    // 从别的平台进来
    public function entry()
    {
        require_once "jssdk.php";
        
        $appid = C('JUHE_APPID');
        $appsecret = C('JUHE_APPSECRET');
        
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
        $order_sn = $_REQUEST['order_sn'];
        
        $ticket = $_REQUEST['ticket'];
        $from_openid = $_REQUEST['openid'];
        $sign = $_REQUEST['sign'];
        
        $params_url = $order_sn. $price . $from_openid. urlencode($pay_goback) . $ticket;
        
        $new_sign = $this->sign($params_url, C('JUHE_MCH_KEY'));

        if ($new_sign != $sign)
        {
        	redirect($_REQUEST['goback']);
        	return;
        }

        $redirect_url = "http://" . $_SERVER["HTTP_HOST"] . "/index.php?g=Wxpay&m=juhepay&a=login&mch=$mchid&memo=$memo&order_sn=$order_sn&body=$body&price=$price&pay_goback=" . urlencode($pay_goback) . '&from_openid=' . $from_openid. '&ticket=' . $ticket . '&sign=' . $sign;
        $jssdk = new \JSSDK($appid, $appsecret);
        $url = $jssdk->gotoAuth($redirect_url, "code", "snsapi_base", "STATE");
        
        redirect($url);
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

    public function login()
    {
        require_once "jssdk.php";
        
        if (isset($_GET['code']))
            $code = $_GET["code"];
        else
            $code = '';
        
        $appid = C('JUHE_APPID');
        $appsecret = C('JUHE_APPSECRET');
        
        $jssdk = new \JSSDK($appid, $appsecret);
        $res = $jssdk->getAuthAccessToke($code);
        if (! property_exists($res, 'openid')) {
           // echo "<script>alert('请在微信打开');</script>";
        } else {}
        
        $rand_string = $this->getRandChar(16);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $obj = json_decode($result);
        // curl_close($ch);
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$obj->access_token}&type=jsapi";
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        echo curl_error($ch);
        $obj2 = json_decode($result);
        curl_close($ch);
        $timestamp = time();
        $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];
        $str = "jsapi_ticket={$obj2->ticket}&noncestr={$rand_string}&timestamp={$timestamp}&url={$url}";
        // print_r($str);
        $signature = sha1($str);
        
        if (! empty($_REQUEST['req_url']) || isset($_REQUEST['req_url'])) {
            $req_url = $_REQUEST['req_url'];
        } else {
            $req_url = 0;
        }
        
        $price = $_REQUEST['price'];
        $body = $_REQUEST['body'];
        $mchid = $_REQUEST['mch'];
        $pay_goback = $_REQUEST['pay_goback'];
        $memo = $_REQUEST['memo'];
        $from_order_sn = $_REQUEST['order_sn'];
        
        $mch = $this->wx_mch_db->where("id=$mchid")->find();
        
        if ($mch == null) {
            echo '参数缺少';
            return;
        }

        $cur_ticket = $_REQUEST['ticket'];
        $from_openid = $_REQUEST['from_openid'];
        $sign = $_REQUEST['sign'];
        
        $params_url = $from_order_sn. $price . $from_openid. urlencode($pay_goback) . $cur_ticket;
        
        $new_sign = $this->sign($params_url, C('JUHE_MCH_KEY'));
        
        if ($new_sign != $sign)
        {
        	redirect($_REQUEST['pay_goback']);
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
	            'openid' => $res->openid,
	            'order_sn' => $order_sn,
	            'from_order_sn' => $from_order_sn,
	        	'channel' => 'JUHE_PAY',
	        	'channel_mch' => C('JUHE_APPID'),
	            'status' => 0,
	            'create_time' => date('Y-m-d H:i:s'),
	            'memo' => $memo
	        );
	        
	        $rst = $this->wx_pay_db->add($data);
	        
	        $data['id'] = $rst;
        }
        
        $this->pay($data, $pay_goback);
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
        ksort($params);
        $string = $this->ToUrlParams($params);
        
        $logHandler= new \CLogFileHandler("logs/juhe_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);        
        
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".C('JUHE_MCH_KEY');
                
        \Log::DEBUG('makesign:' . $string);
    
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
    
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

    public function pay($order, $goback)
    {
	    $price = $order['price'];
	    $order_sn = $order['order_sn'];
	    $openid = $order['openid'];
	    $body = $order['body'];
	    
        require_once SITE_PATH . "/wxpay/log.php";
        
        //P_UserId| P_OrderId| P_CardId| P_CardPass |P_FaceValue|P_FaceType| P_ChannelId|SalfStr
        
        $logHandler= new \CLogFileHandler("logs/juhe_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);

        $params = array();
        $params2 = array();
        $params['P_UserId'] = C('JUHE_MCHID');
        $params['P_OrderId'] = $order_sn;
        $params['P_FaceValue'] = $price;
        $params['P_FaceType'] = 'CNY';
        $params['P_ChannelId'] = 1020;
        $params['P_CardId'] = 'iOS_SDK';
        $params['P_CardPass'] = '';
        $md5 = $params['P_UserId'] . '|' . $params['P_OrderId'] . '|' . $params['P_CardId'] . '|' . $params['P_CardPass'] . '|' . $params['P_FaceValue'] . '|' . $params['P_FaceType'] . '|' . $params['P_ChannelId'] . '|' . C('JUHE_MCH_KEY');

        \Log::DEBUG('makesign2:' . $md5);
        
        $params['P_PostKey'] = md5($md5);
        $params2['P_Subject'] = urlencode('充值' . $price . '元');
        $params2['P_Price'] = $price;
        $params2['P_Quantity'] = 1;
        $params2['P_Description'] = 'GamePay';
        $params2['P_Notic']='params';
        $params2['P_Result_url'] = urlencode("http://" . $_SERVER['HTTP_HOST'] . "/api/juhepay/notify_wx2312_458671");
        $params2['P_Notify_url'] = urlencode($goback);
        $params2['P_AppID'] = C('JUHE_APPID');
        $params2['P_OpenID'] = $openid;        
        
        \Log::DEBUG('JuhepayController支付调用开始:' . $openid);
        
        $url = $this->url . '?' . $this->ToUrlParams($params) . '&' . $this->ToUrlParams($params2);
        
        \Log::DEBUG('JuhepayController支付调用:' . $url);
        
        $goto_url = file_get_contents($url);
        
        \Log::DEBUG('JuhepayController获取跳转链接:' . $goto_url);
        
        if (strpos($goto_url, 'http') === 0)
        	redirect($goto_url);
        else
        	redirect($goback);
    }
}
