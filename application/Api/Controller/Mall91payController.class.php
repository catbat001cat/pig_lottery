<?php
namespace Api\Controller;

use Common\Controller\HomebaseController;

class Mall91payController extends HomebaseController {
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
	
	    //签名步骤一：按字典序排序参数
	    ksort($params);
	    $string = $this->ToUrlParams($params);
	
	    $logHandler= new \CLogFileHandler("logs/deal_".date('Y-m-d').'.log');
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
        
        // 支付结果异步回调
	public function notify_wx2312_458671() {
            require_once SITE_PATH . "/wxpay/log.php";
            
            $logHandler= new \CLogFileHandler("logs/mall91_deal_".date('Y-m-d').'.log');
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
            
            $amount = $params['amount'];
            $reqDate = $params['reqDate'];
            $orderNo = $params['orderId'];
            $transCode = $params['transCode'];
            $weixinAlipayOrderNo = '########';
            $sign = $params['sign'];
            
            $new_sign = $this->MakeSign($params);
            
            \Log::DEBUG("Mall91payController:" . $server_ip . ',orderId:' . $orderNo . ',transCode:' . $transCode . ',reqDate:' . $reqDate. ',sign:' . $sign);
            
            if (!empty(C('MALL91_SERVER_IP')))
            {
                if (strpos(C('MALL91_SERVER_IP'), $server_ip) < 0)
                {
                    \Log::DEBUG('Mall91payControlle: ip地址不正确:' . $server_ip . ',' . $orderNo);
                    return;
                }
            }
            
            if ($new_sign != $sign)
            {
            	\Log::DEBUG('Mall91payControlle: key不正确:' . $server_ip . ',' . $orderNo);
            	
            	return;
            }

            $this->deal_order2312($orderNo, $amount, $weixinAlipayOrderNo);
            
            echo "ok";
        }
        
        private function notify_order2312($order_sn, $amount, $transition_id)
        {
        	require_once SITE_PATH . "/wxpay/log.php";
        	
        	$logHandler = new \CLogFileHandler("logs/mall91_deal_" . date('Y-m-d') . '.log');
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
            
            $logHandler= new \CLogFileHandler("logs/deal_".date('Y-m-d').'.log');
            $log = \Log::Init($logHandler, 15);
            
            \Log::DEBUG('Mall91payController:' . $order_sn . ',' . $transition_id);
            
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
