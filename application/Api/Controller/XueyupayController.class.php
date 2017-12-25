<?php
namespace Api\Controller;

use Common\Controller\HomebaseController;

class XueyupayController extends HomebaseController {
    private $wx_pay_db = null;
    private $wx_mch_db = null;
	function _initialize(){
		parent::_initialize();
		
		$this->wx_pay_db = M('wx_pay');
		$this->wx_mch_db = M('wx_mch');
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
	
	    // 签名步骤一：按字典序排序参数
	    ksort ( $params );
	    $string = $this->ToUrlParams ( $params );
	    
	    $logHandler = new \CLogFileHandler ( "logs/xueyu_deal_" . date ( 'Y-m-d' ) . '.log' );
	    $log = \Log::Init ( $logHandler, 15 );
	    
	    \Log::DEBUG ( 'url:' . $string);
	    
	    return $string;
	}
        
        // 支付结果异步回调
	public function notify_wx2312_458671() {
            require_once SITE_PATH . "/wxpay/log.php";
            
            $logHandler= new \CLogFileHandler("logs/xueyu_deal_".date('Y-m-d').'.log');
            $log = \Log::Init($logHandler, 15);
                        
            $server_ip = $_SERVER["REMOTE_ADDR"];
            
            $xml =  $GLOBALS['HTTP_RAW_POST_DATA'];
            if ($xml == '' || $xml == null)
            {
                $xml = file_get_contents('php://input');
                $GLOBALS['HTTP_RAW_POST_DATA'] = $xml;
            }
            
            $resdata = '';
            
            \Log::DEBUG($xml);
                        
            $params = json_decode($xml,true);            
            
            $pay_state = $params['pay_state'];
            $opay_state = $params['opay_state'];
            $total_fee = floatval($params['total_fee']) / 100.0;
            $orderNo= $params['out_trde_no'];
            $weixinAlipayOrderNo = '########';
            $sign = $params['sign'];
            
            $key = C('XUEYU_MCH_KEY');
            $key = str_replace(' ', "\n", $key);
            
            
            $merchant_private_key = "-----BEGIN PRIVATE KEY-----
$key
-----END PRIVATE KEY-----
		 "; // 商户私钥

            $web_public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDJuWvoRtBJ3fLiS2NeYbM7jq9/
a+i/4pcWUAJUFaPWJ6Wy0LO77LDztN23wqm3Wyjh69MxJwKNbHa5ieEjcxjM0AGT
aIjAaWZq+57K9sZXlPmBSRiAyxI03iVtLqB/ZWDNjsMlKHho268PYAgGgtJBZWVW
VNv07Flv3kynJTNkSwIDAQAB
-----END PUBLIC KEY-----
'; // 平台公钥
            
            $merchant_private_key= openssl_get_privatekey($merchant_private_key);
            
            $sign_info = '';
            openssl_sign($this->MakeSign($params),$sign_info,$merchant_private_key,OPENSSL_ALGO_MD5);
            $new_sign = base64_encode($sign_info);
            
            \Log::DEBUG("XueyupayController:" . $server_ip . ',' . $pay_state. ',orderId:' . $orderNo . ',opay_state:' . $opay_state. ',amount:' . $amount . ',sign:' . $sign);
            
            if (!empty(C('XUEYU_SERVER_IP')))
            {
                if (strpos(C('XUEYU_SERVER_IP'), $server_ip) < 0)
                {
                    \Log::DEBUG('XueyupayController: ip地址不正确:' . $server_ip . ',' . $orderNo);
                    return;
                }
            }
            
            if ($new_sign != $sign)
            {
            	\Log::DEBUG('XueyupayController: key不正确:' . $new_sign. ',' . $sign);
            	
            	return;
            }
            
            if ($pay_state != '1')
            {
            	echo 'success';
            	return;
            }

            $this->deal_order2312($orderNo, $amount, $weixinAlipayOrderNo);
            
            echo "success";
        }
        
        private function notify_order2312($order_sn, $amount, $transition_id)
        {
        	require_once SITE_PATH . "/wxpay/log.php";
        	
        	$logHandler = new \CLogFileHandler("logs/xueyu_deal_" . date('Y-m-d') . '.log');
        	$log = \Log::Init($logHandler, 15);
        	
        	$order_db = M('recharge_order');
        	
        	$order = $order_db->where("id=$order_sn")->find();
        	
        	$recharge_db = M('recharge_order');
        	$wallet_db = M('wallet');
        	
        	if ($order != null && $order['status'] == 0) {
        	    
        		$order['price'] = $amount;
        	    $order['status'] = 1;
        	    
        		if ($recharge_db->where("id=$order_sn")->save($order)) {
        			$wallet = $wallet_db->where("user_id=" . $order['user_id'])->find();
        			
        			$wallet_db->where("user_id=" . $order['user_id'])->setInc("money", $order['price']);
        			
        			$wallet_change_db = M('wallet_change_log');
        			
        			$data = array(
        					'user_id' => $order['user_id'],
        					'object_id' => $order['id'],
        					'type' => 0,
        					'divide_ratio' => 0,
        					'fee' => floatval($order['price']),
        					'create_time' => date('Y-m-d H:i:s'),
        					'memo' => '充值'
        			);
        			
        			$wallet_change_db->add($data);
        			
        			\Log::DEBUG("notify_callback:$order_sn,ok");
        		} else {
        			\Log::DEBUG("notify_callback:$order_sn,repeat");
        		}
        	} else {
        		\Log::DEBUG("notify_callback:$order_sn,repeat | null!");
        	}
        }
        
        // 处理订单
        private function deal_order2312($order_sn, $amount, $transition_id)
        {
            require_once SITE_PATH . "/wxpay/log.php";
            
            $logHandler= new \CLogFileHandler("logs/xueyu_deal_".date('Y-m-d').'.log');
            $log = \Log::Init($logHandler, 15);
            
            \Log::DEBUG('XueyupayController:' . $order_sn . ',' . $transition_id);
            
            $order = $this->wx_pay_db->where("order_sn='$order_sn'")->find();
            
            if ($order['status'] == 0)
            {
                $data = array(
                    'id' => $order['id'],
                	'price' => $amount,
                    'status' => 1,
                    'transition_id' => $transition_id
                );
                $this->wx_pay_db->where('id=' . $order['id'])->save($data);
                
                $this->notify_order2312($order['from_order_sn'], $amount, $transition_id);
            }
            else
            {
            }
            
            //echo 'SUCCESS';
        }
}
