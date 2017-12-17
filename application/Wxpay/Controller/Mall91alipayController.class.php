<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class Mall91payController extends HomebaseController
{

    private $wx_pay_db = null;
    private $url = 'http://boss.vc-group.cn/onlinepay/vcQbScanCodePayRest';
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
    	$is_android = false;
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
			$is_android = true;
			
			if ($this->is_weixin())
			{
				// 提示用其他浏览器打开
				$this->assign('ios', false);
				$this->display(':mall91_ali_pay');
				return;
			}
		} else {
			if ($this->is_weixin())
			{
				// 提示用其他浏览器打开
				$this->assign('ios', true);
				$this->display(':mall91_ali_pay');
				return;
			}
		}     
        
        require_once SITE_PATH . "/wxpay/log.php";
        $logHandler= new \CLogFileHandler("logs/mall91_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
        
        $price = $_REQUEST['price'];
        $body = '';
        if (isset($_REQUEST['body']))
            $body = $_REQUEST['body'];
        $mchid = $_REQUEST['mch'];

        $memo = '';
        if (isset($_REQUEST['memo']))
            $memo = $_REQUEST['memo'];
        $from_order_sn = $_REQUEST['order_sn'];
        
        $ticket = $_REQUEST['ticket'];
        $from_openid = $_REQUEST['openid'];
        $sign = $_REQUEST['sign'];
        
        $pay_goback = '';
        if (isset($_REQUEST['goback']))
            $pay_goback = $_REQUEST['goback'];        

        $params_url = $from_order_sn. $price . $from_openid. urlencode($pay_goback) . $ticket;

        $new_sign = $this->sign($params_url, C('MALL91_MCH_KEY'));
        if ($new_sign != $sign)
        {
        	redirect($_REQUEST['goback']);
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
        $this->pay($data);
    }
    
    public function sign($par, $key)
    {
    	return md5($par. $key);
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

    private function MakeSign($params)
    {
        require_once SITE_PATH . "/wxpay/log.php";
        
        //签名步骤一：按字典序排序参数
        ksort($params);
        $string = $this->ToUrlParams($params);
        
        $logHandler= new \CLogFileHandler("logs/mall91_".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
        
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".C('MALL91_MCH_KEY');
        
        \Log::DEBUG('makesign:' . $string);
        
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        
        return $result;
    }
    
    function postData($url, $data)
    {
    	$ch = curl_init();
    	$timeout = 300;
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    	$handles = curl_exec($ch);
    	curl_close($ch);
    	return $handles;
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
        $params['merchantNo'] = C('MALL91_MCHID');
        $params['orderNo'] = $order_sn;
        $params['transType'] = 'alipay';
        $params['amount'] = $price;
        $params["productName"] = '充值' . $price . '元';
        $params['notifyUrl'] = "http://" . $_SERVER['HTTP_HOST'] . "/api/mall91pay/notify_wx2312_458671";
        
        $params['sign'] = $this->MakeSign($params);        
        
        $jsonStr=json_encode($params);
        
        \Log::DEBUG('mall91alipayController支付调用开始');
        
        \Log::DEBUG($jsonStr);
        
        $return_content = $this->postData($this->url, $jsonStr);
        \Log::DEBUG($return_content);
        
        $respJson=json_decode($return_content, true);
        if($respJson['response']['ok']){
            redirect ( $respJson ['payUrl'] );
        }
        else
        {
            redirect($pay_goback);
        }
    }
}
