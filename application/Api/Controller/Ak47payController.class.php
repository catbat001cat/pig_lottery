<?php
namespace Api\Controller;

use Common\Controller\HomebaseController;

class Ak47payController extends HomebaseController {
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
	
	    $logHandler= new \CLogFileHandler("logs/ak47_deal_".date('Y-m-d').'.log');
	    $log = \Log::Init($logHandler, 15);
	
	    //签名步骤二：在string后加入KEY
	    $string = $string . "&key=".C('AK47_MCH_KEY');
	
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
            require_once SITE_PATH . "/ak47/easypay-api-sdk-php.php";
            
            $logHandler= new \CLogFileHandler("logs/ak47_deal_".date('Y-m-d').'.log');
            $log = \Log::Init($logHandler, 15);
                        
            $server_ip = $_SERVER["REMOTE_ADDR"];
            
            $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $headers = getallheaders();
            $body = file_get_contents("php://input");
            
            //print_r($headers);
            
            $signed = $headers[SIGN];
            unset($headers[SIGN]);
            
            if(!validate_sign(base64_decode($signed), $url, $headers, $body)) {
            	echo "Wrong sign";
            	return;
            }
            
            \Log::DEBUG($body);
            
            $json_obj = json_decode($body, true);
            
            if ($json_obj['status'] == 'SETTLED')
            {
	            $outTradeNo= $json_obj['outTradeNo'];
	            $outChannelNo= $json_obj['tradeNo'];
	            $amount = intval($json_obj['payedAmount']) / 100.0;
	            
	            $this->deal_order2312($outTradeNo, $outChannelNo, $amount);
            }
            
            
            echo 'SUCCEED';
        }
        
        private function notify_order2312($order_sn, $transition_id, $total_fee)
        {
        	require_once SITE_PATH . "/wxpay/log.php";
        	
        	$logHandler = new \CLogFileHandler("logs/ak47_deal_" . date('Y-m-d') . '.log');
        	$log = \Log::Init($logHandler, 15);
        	
        	$order_db = M('recharge_order');
        	
        	$order = $order_db->where("id=$order_sn")->find();
        	
        	$recharge_db = M('recharge_order');
        	$wallet_db = M('wallet');
        	
        	if ($order != null && $order['status'] == 0) {
        	    
        	    $order['price'] = $total_fee;
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
        private function deal_order2312($order_sn, $transition_id, $total_fee)
        {
            require_once SITE_PATH . "/wxpay/log.php";
            
            $logHandler= new \CLogFileHandler("logs/ak47_deal_".date('Y-m-d').'.log');
            $log = \Log::Init($logHandler, 15);
            
            \Log::DEBUG('Ak47payController:' . $order_sn . ',' . $total_fee);
            
            $order = $this->wx_pay_db->where("order_sn='$order_sn'")->find();
            
            if ($order['status'] == 0)
            {
                $data = array(
                    'id' => $order['id'],
                    'status' => 1,
                    'price' => $total_fee,
                    'real_price' => $total_fee,
                    'transition_id' => $transition_id
                );
                $this->wx_pay_db->where('id=' . $order['id'])->save($data);
                
                $this->notify_order2312($order['from_order_sn'], $transition_id, $total_fee);
            }
            else
            {
            }

        }
}
