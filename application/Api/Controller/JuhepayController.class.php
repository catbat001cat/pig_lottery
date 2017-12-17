<?php
namespace Api\Controller;

use Common\Controller\HomebaseController;

class JuhepayController extends HomebaseController {
    private $wx_pay_db = null;
    private $wx_mch_db = null;
	function _initialize(){
		parent::_initialize();
		
		$this->wx_pay_db = M('wx_pay');
		$this->wx_mch_db = M('wx_mch');
	}
        
        // 支付结果异步回调
	public function notify_wx2312_458671() {
            require_once SITE_PATH . "/wxpay/log.php";
            
            $logHandler= new \CLogFileHandler("logs/deal_".date('Y-m-d').'.log');
            $log = \Log::Init($logHandler, 15);
                        
            $server_ip = $_SERVER["REMOTE_ADDR"];
            
            $P_UserId= $_REQUEST['P_UserId'];
            $P_OrderId= $_REQUEST['P_OrderId'];
            $P_CardId= $_REQUEST['P_CardId'];
            $P_CardPass = $_REQUEST['P_CardPass'];
            $P_ChannelId = $_REQUEST['P_ChannelId'];
            $P_OrderId_out = $_REQUEST['P_OrderId_out'];
            $P_FaceValue = $_REQUEST['P_FaceValue'];
            $P_PayMoney = $_REQUEST['P_PayMoney'];
            $P_Subject = $_REQUEST['P_Subject'];
            $P_Price = $_REQUEST['P_Price'];
            $P_Quantity = $_REQUEST['P_Quantity'];
            $P_Descripton = $_REQUEST['P_Descripton'];
            $P_Notic = $_REQUEST['P_Notic'];
            $P_PostKey = $_REQUEST['P_PostKey'];
            $P_ErrCode = $_REQUEST['P_ErrCode'];
            
            $md5 = $P_UserId . '|' . $P_OrderId . '|' . $P_CardId . '|' . $P_CardPass . '|' . $P_FaceValue . '|' . $P_ChannelId . '|' . $P_OrderId_out . '|' . C('JUHE_MCH_KEY');
            
            \Log::DEBUG('juhe_makesign:' . $md5 . ',' . $P_PostKey);
            
            $new_sign = md5($md5);
            
            if ($new_sign != $P_PostKey)
            {
            	\Log::DEBUG('JuhepayController: key不正确:' . $server_ip);
            	return;
            }
            
            
            \Log::DEBUG("JuhepayController,P_OrderId:" . $P_OrderId. ',P_OrderId_out' . $P_OrderId_out. ',P_Price:' . $P_Price . ',P_PayMoney' . $P_PayMoney . ',' . $P_ErrCode);
            
            if (!empty(C('JUHE_SERVER_IP')))
            {
                if (strpos(C('JUHE_SERVER_IP'), $server_ip) < 0)
                {
                	\Log::DEBUG('JuhepayController: ip地址不正确:' . $server_ip . 'P_OrderId:' . $P_OrderId. ',P_OrderId_out' . $P_OrderId_out. ',P_Price:' . $P_Price . ',' . $P_ErrCode);
                    return;
                }
            }
            
            if (intval($P_ErrCode) != 0)
            {
            	return;
            }

            $this->deal_order2312($P_OrderId, $P_OrderId_out, $P_PayMoney);
            
            echo 'P_ErrCode=0';
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
        			
        			\Log::DEBUG("notify_callback:$order_sn,$transition_id,ok");
        		} else {
        			\Log::DEBUG("notify_callback:$order_sn,$transition_id,repeat");
        		}
        	} else {
        		\Log::DEBUG("notify_callback:$order_sn,$transition_id,repeat | null!");
        	}
        }
        
        // 处理订单
        private function deal_order2312($order_sn, $transition_id, $total_fee)
        {
            require_once SITE_PATH . "/wxpay/log.php";
            
            $logHandler= new \CLogFileHandler("logs/deal_".date('Y-m-d').'.log');
            $log = \Log::Init($logHandler, 15);
            
            \Log::DEBUG('JuhepayController:' . $order_sn .',' . $transition_id . ',' . ',' . $total_fee);
            
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
