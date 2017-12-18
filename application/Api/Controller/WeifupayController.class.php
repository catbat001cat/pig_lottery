<?php
namespace Api\Controller;

use Common\Controller\HomebaseController;

class WeifupayController extends HomebaseController {
    private $wx_pay_db = null;
    private $wx_mch_db = null;
	function _initialize(){
		parent::_initialize();
		
		$this->wx_pay_db = M('wx_pay');
		$this->wx_mch_db = M('wx_mch');
	}
	
	public function notify_wx2312() {
	    require_once SITE_PATH . "/wxpay/log.php";
	
	    require_once SITE_PATH . '/swiftpass/Utils.class.php';
	    require_once SITE_PATH . '/swiftpass/config.php';
	    require_once SITE_PATH . '/swiftpass/RequestHandler.class.php';
	    require_once SITE_PATH . '/swiftpass/ClientResponseHandler.class.php';
	    require_once SITE_PATH . '/swiftpass/PayHttpClient.class.php';
	
	    
	    $logHandler= new \CLogFileHandler("logs/deal_".date('Y-m-d').'.log');
	    $log = \Log::Init($logHandler, 15);
	
	    \Log::DEBUG('WeifupayController支付回调开始');
	
	    $resHandler = new \ClientResponseHandler();
	    $reqHandler = new \RequestHandler();
	
	    $xml = file_get_contents('php://input');
	
	    \Log::DEBUG('WeifupayController:' . $xml);
	
	    $resHandler->setContent($xml);
	    //var_dump($this->resHandler->setContent($xml));
	    $resHandler->setKey(C('WFT_MCH_KEY'));
	    if($resHandler->isTenpaySign()){
	        if($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0)
	        {
	            $res = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	
	            $orderid = $res->out_trade_no;
	            $transaction_id = $res->transaction_id;
	            $total_fee = $res->total_fee / 100;
	
	            $this->deal_order2312($orderid, $transaction_id, $total_fee);
	        }
	        else
	        {
	
	            echo 'fail';
	        }
	    }
	    else
	    {
	        echo 'fail';
	    }
	}
	        
        // 微信支付结果异步回调
	private function notify_order2312($order_sn, $total_fee)
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
        public function deal_order2312($order_sn, $transition_id, $total_fee)
        {
        	\Log::DEBUG('WeifupayController:' . $order_sn . '[' . $transition_id . ']');
            
            $order = $this->wx_pay_db->where("order_sn='$order_sn'")->find();
            
            /*
            if (intval($order['price']) != $total_fee)
            {
                $data = array(
                    'id' => $order['id'],
                    'status' => 2,  // 订单有问题
                	'from_source' => 'WFT:' . C('WFT_MCHID'),
                    'memo' => '金额不正确:' . $total_fee,
                    'transition_id' => $transition_id
                );
                $this->wx_pay_db->where('id=' . $order['id'])->save($data);
                
                echo 'fail';
                
                return;
            }
            */
            
            if ($order['status'] == 0)
            {
                $data = array(
                    'id' => $order['id'],
                    'status' => 1,
                    'price' => $total_fee,
                    'real_price' => $total_fee,
                	'from_source' => 'WFT:' . C('WFT_MCHID'),
                	'transition_id' => $transition_id
                );

                $this->wx_pay_db->where('id=' . $order['id'])->save($data);
               
                
                \Log::DEBUG('WeifupayController:' . $this->wx_pay_db->getLastSql());
                
                
                $this->notify_order2312($order['from_order_sn'], $total_fee);

                echo 'success';
            }
            else
            {
                echo "error,status:" . $order['status'];
            }
        }
}
