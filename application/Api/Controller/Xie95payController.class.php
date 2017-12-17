<?php
namespace Api\Controller;

use Common\Controller\HomebaseController;

class Xie95payController extends HomebaseController {
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
	    $string = $string . "&key=".C('XIE95_DESKEY');
	
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
            
            $logHandler= new \CLogFileHandler("logs/deal_".date('Y-m-d').'.log');
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
            
            /*
            $falg = XMLUtil::decryptResXml($xml, $resdata);
            if(falg){
                echo "验签成功";
                echo json_encode($resdata);
            }
            */
            
            $params = json_decode($xml,true);
            
            $channel = $params['channel'];
            $mchId= $params['mchId'];
            $outTradeNo= $params['outTradeNo'];
            $amount = $params['amount'];
            $transTime = $params['transTime'];
            $status = $params['status'];
            $outChannelNo = $params['outChannelNo'];
            $sign = $params['sign'];
            
            if ($outChannelNo != null)
                $params['outChannelNo'] = $outChannelNo;
            
            $params['sign'] = $this->MakeSign($params);
            
            \Log::DEBUG("Xie95payController,:" . $server_ip . ',channel:' . $channel . ',mchId:' . $mchId . ',outTradeNo:' . $outTradeNo . ',outChannelNo:' . $outChannelNo . ',amount:' . $amount . ',transTime:' . $transTime . ',status:' . $status . ',sign:' . $sign);
            
            if (!empty(C('XIE95_SERVER_IP')))
            {
            	if (C('XIE95_SERVER_IP') != $server_ip)
                {
                    \Log::DEBUG('Xie95payController: ip地址不正确:' . $server_ip . ',' . $outTradeNo . ',' . $amount);
                    return;
                }
            }
            
            if ($params['sign']!= $sign)
            {
            	\Log::DEBUG('Xie95payController: key不正确:' . $server_ip . ',' . $outTradeNo . ',' . $amount);
            	
            	return;
            }

            $this->deal_order2312($outTradeNo, $outChannelNo, $amount);
        }
        
        private function notify_order2312($order_sn, $transition_id, $total_fee)
        {
        	require_once SITE_PATH . "/wxpay/log.php";
        	
        	$logHandler = new \CLogFileHandler("logs/deal_" . date('Y-m-d') . '.log');
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
            
            $logHandler= new \CLogFileHandler("logs/deal_".date('Y-m-d').'.log');
            $log = \Log::Init($logHandler, 15);
            
            \Log::DEBUG('Xie95payController:' . $order_sn . ',' . $total_fee);
            
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
            
            echo 'SUCCESS';
        }
}
