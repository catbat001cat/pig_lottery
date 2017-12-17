<?php
namespace Api\Controller;

use Common\Controller\HomebaseController;

class YubwxpayController extends HomebaseController {
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
            
            $orderNumber= $_REQUEST['name'];
            $transition_id= $_REQUEST['ddh'];
            $amount = $_REQUEST['money'];
            $key = $_REQUEST['key'];
            $real_total_fee = $_REQUEST['money'];
            $lb = $_REQUEST['lb'];
            $paytime = $_REQUEST['paytime'];
            $sign = $_REQUEST['sign'];
            
            \Log::DEBUG("YubwxpayController:$server_ip," . C('YUBWX_SERVER_IP'). ",orderNumber:". $orderNumber . ',transition_id' . $transition_id . ',amount:' . $amount . ',paytime:' . $paytime);
            
            /*
            if (!empty(C('YUBWX_SERVER_IP')))
            {
                if (strpos(C('YUBWX_SERVER_IP'), $server_ip) == false)
                {
                    \Log::DEBUG('YubwxpayController: ip地址不正确:' . $server_ip . ',' . $orderNumber . ',' . $real_total_fee);
                    return;
                }
            }
            */
            
            if ($key != C('YUBWX_KEY'))
            {
            	\Log::DEBUG('YubwxpayController: key不正确:' . $server_ip . ',' . $orderNumber . ',' . $real_total_fee);
            	
            	echo "key error";
            	return;
            }

            $this->deal_order2312($orderNumber, $transition_id, $amount);
        }
        
        private function notify_order2312($order_sn, $transition_id, $total_fee)
        {
        	require_once SITE_PATH . "/wxpay/log.php";
        	
        	$logHandler = new \CLogFileHandler("logs/deal_" . date('Y-m-d') . '.log');
        	$log = \Log::Init($logHandler, 15);
        	
        	$order_db = M('recharge_order');
        	
        	$order = $order_db->where("id=$order_sn")->find();
        	
        	/*
        	if (floatval($order['price']) != floatval($total_fee))
        	{
        		$data = array(
        				'status' => 2,
            		    'transition_id' => $transition_id,
        				'memo' => '错误金额:' . $total_fee
        				
        		);
        		$this->wx_pay_db->where('from_order_sn=' . $order_sn)->save($data);
        		
        		\Log::DEBUG('支付失败，金额对不上');
        		return;
        	}
        	*/
        	
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

				echo 'ok';
        		} else {
				echo 'ok';
        			\Log::DEBUG("notify_callback:$order_sn,repeat");
        		}
        	} else {
			echo 'error';
        		\Log::DEBUG("notify_callback:$order_sn,repeat | null!");
        	}
        }
        
        private function notify_order2312_re($order_sn, $transition_id, $total_fee)
        {
        	require_once SITE_PATH . "/wxpay/log.php";
        	
        	$logHandler = new \CLogFileHandler("logs/deal_" . date('Y-m-d') . '.log');
        	$log = \Log::Init($logHandler, 15);
        	
        	$order_db = M('recharge_order');
        	
        	$order = $order_db->where("id=$order_sn")->find();
        	
        	$recharge_db = M('recharge_order');
        	$wallet_db = M('wallet');
        	
        	if ($order != null) {
        		
        		$order['price'] = $order['price'] + $total_fee;
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
        					'memo' => '充值(合并)'
        			);
        			
        			$wallet_change_db->add($data);
        			
        			\Log::DEBUG("notify_callback:$order_sn,ok");
        			
        			echo 'ok';
        		} else {
        			echo 'ok';
        			\Log::DEBUG("notify_callback:$order_sn,repeat");
        		}
        	} else {
        		echo 'error';
        		\Log::DEBUG("notify_callback:$order_sn,repeat | null!");
        	}
        }
        
        // 处理订单
        private function deal_order2312($order_sn, $transition_id, $total_fee)
        {
            require_once SITE_PATH . "/wxpay/log.php";
            
            $logHandler= new \CLogFileHandler("logs/deal_".date('Y-m-d').'.log');
            $log = \Log::Init($logHandler, 15);
            
            \Log::DEBUG('YubwxpayController:' . $order_sn . ',' . $total_fee);
            
            $order = $this->wx_pay_db->where("order_sn='$order_sn'")->find();

		if ($order == null)
		{
		\Log::DEBUG('YubwxpayControoller:null');
		
		echo 'error';
		return;
		}        
    
            if ($order['status'] == 0)
            {
                // 判断金额是否正确
                /*
                if (intval($order['price']) != intval($total_fee))
                {
                    $data = array(
                        'id' => $order['id'],
                        'status' => 2,
                        'real_price' => $real_total_fee,
                        'memo' => '错误金额:' . $total_fee,
                        'transition_id' => $transition_id
                    );
                    $this->wx_pay_db->where('id=' . $order['id'])->save($data);

                    return;
                }
                */
                
                $data = array(
                    'id' => $order['id'],
                    'status' => 1,
                    'price' => $total_fee,
                    'real_price' => $total_fee,
                    'transition_id' => $transition_id
                );
                $this->wx_pay_db->where('id=' . $order['id'])->save($data);
                
                // 调用远程接口
                //$mch = $this->wx_mch_db->where("id=" . $order['mch'])->find();
                
                if (true)//empty($mch['return_url']))
                    $this->notify_order2312($order['from_order_sn'], $transition_id, $total_fee);
                else
                {
                    $ticket = time();
                    
                    $sign = md5($order['from_order_sn'] . $total_fee . $ticket . C('MCH_KEY'));
                    
                    $notify_url = $mch['return_url'] . '&order_sn=' . $order['from_order_sn'] . '&transition_id=' . $transition_id . '&total_fee=' . $total_fee . '&ticket=' . $ticket . '&sign=' . $sign;
                    
                    \Log::DEBUG('call:' . $notify_url);
                    
                    $ch = curl_init();
                    $timeout = 5;
                    curl_setopt ($ch, CURLOPT_URL, $notify_url);
                    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                    $html = curl_exec($ch);
                    curl_close($ch);
                    
                    if ($html)
                        $this->wx_pay_db->where("order_sn='$order_sn'")->setField('is_ok', 1);
                }
            }
            else
            {
				\Log::DEBUG('error status:' . $order['status']);
				
				// 这里应该是产生了同一个单号，两笔交易
				if ($order['transition_id'] != '' && strpos($order['transition_id'], $transition_id) < 0)
				{
					\Log::DEBUG('合并订单:' . $order['id'] . ',transition_id:' . $transition_id . ',add:' . $total_fee. ',price:' . $order['price'] . $order['status']);
					
					/*
					$data = array(
							'id' => $order['id'],
							'memo' => $order['memo'] . '+[' . $total_fee . '|' . $transition_id . ']'
					);
					$this->wx_pay_db->where('id=' . $order['id'])->save($data);
					*/
					
					// 查询有没有这条交易单号存在，有的话，就不合并
					if ($this->wx_pay_db->where("transition_id='" . $transition_id . "'")->count() == 0)
					{
						$data = array(
								'id' => $order['id'],
								'status' => 1,
								'price' => $total_fee + $order['price'],
								'real_price' => $total_fee + $order['real_price'],
								'transition_id' => $order['transition_id'] . ',' . $transition_id,
								'memo' => $order['memo'] . ',' . $total_fee 
						);
						$this->wx_pay_db->where('id=' . $order['id'])->save($data);
						
						// 合并订单
						$this->notify_order2312_re($order['from_order_sn'], $transition_id, $total_fee);
					}
					else 
					{
						\Log::DEBUG('订单:' . $order['id'] . ',transition_id:' . $transition_id . '已存在');
					
						echo "ok";
					}
				}
				else
				{
					echo 'ok';
				}
            }
        }
}
