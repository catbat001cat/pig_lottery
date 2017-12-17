<?php
namespace Api\Controller;

use Common\Controller\HomebaseController;

class WxpayController extends HomebaseController {
    private $wx_pay_db = null;
    private $wx_mch_db = null;
	function _initialize(){
		parent::_initialize();
		
		$this->wx_pay_db = M('wx_pay');
		$this->wx_mch_db = M('wx_mch');
	}
        
        // 微信支付结果异步回调
        public function notify_wx() {
            require_once SITE_PATH . "/wxpay/log.php";
            require_once SITE_PATH . "/wxpay/PayNotifyCallBack.php";
            
            $notify = new \PayNotifyCallBack();
            
            \WxPayConfig::$APPID = C('APPID');
            \WxPayConfig::$APPSECRET = C('APPSECRET');
            \WxPayConfig::$MCHID = C('MCHID');
            \WxPayConfig::$KEY = C('MCH_KEY');
            
            $xml =  $GLOBALS['HTTP_RAW_POST_DATA'];
            if ($xml == '' || $xml == null)
            {
                $xml = file_get_contents('php://input');
                $GLOBALS['HTTP_RAW_POST_DATA'] = $xml;
            }

            $notify->controller = $this;
            $notify->Handle(false);
        }
        
        private function notify_order2312($order_sn, $transition_id, $total_fee)
        {
            require_once SITE_PATH . "/wxpay/log.php";
             
            $logHandler = new \CLogFileHandler("logs/deal_" . date('Y-m-d') . '.log');
            $log = \Log::Init($logHandler, 15);
             
            $order_db = M('recharge_order');
             
            $order = $order_db->where("id=$order_sn")->find();
             
            if (intval($order['price']) != intval($total_fee))
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
             
            $recharge_db = M('recharge_order');
            $wallet_db = M('wallet');
             
            if ($order != null && $order['status'] == 0) {
                if ($recharge_db->where("id=$order_sn")->setField('status', 1)) {
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
        private function deal_order($order_sn, $transition_id, $total_fee)
        {
            $logHandler = new \CLogFileHandler("logs/deal_" . date('Y-m-d') . '.log');
            $log = \Log::Init($logHandler, 15);
                        
            $order = $this->wx_pay_db->where("from_order_sn='$order_sn'")->find();
            

            // 需要把分转换成元
            $total_fee = intval($total_fee) / 100;        
            
            \Log::DEBUG('WxpayController:' . $order_sn . ',status:' . $order['status'] . ',price:' . $order['price'] . ',real_fee:' . $total_fee);

            if ($order['status'] == 0)
            {
                // 判断金额是否正确
                if (intval($order['price']) != intval($total_fee))
                {
                    $data = array(
                        'id' => $order['id'],
                        'status' => 2,
                        'real_price' => $total_fee,
                        'memo' => '错误金额:' . $total_fee,
                        'transition_id' => $transition_id
                    );
                    $this->wx_pay_db->where('id=' . $order['id'])->save($data);
                    
                    echo 'ok';
                    
                    return;
                }
                
                $data = array(
                    'id' => $order['id'],
                    'status' => 1,
                    'real_price' => $total_fee,
                    'transition_id' => $transition_id
                );
                $this->wx_pay_db->where('id=' . $order['id'])->save($data);
                
                // 调用远程接口
                $mch = $this->wx_mch_db->where("id=" . $order['mch'])->find();
                
                if (empty($mch['return_url']))
                    $this->notify_order2312($order['from_order_sn'], $transition_id, $total_fee);
                else
                {
                    $ticket = time();
                    
                    $sign = md5($order['from_order_sn'] . $total_fee . $transition_id . $ticket . C('MCH_KEY'));
                    
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
                
                echo 'ok';
            }
            else
            {
                echo "error,status:" . $order['status'];
            }
        }
}
