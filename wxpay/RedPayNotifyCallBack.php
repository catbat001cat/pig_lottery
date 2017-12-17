<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "lib/WxPay.Api.php";
require_once 'lib/WxPay.Notify.php';
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class RedPayNotifyCallBack extends WxPayNotify
{
    public $red_times_db = null;    
    public $orders_db = null;
    public $topic_db = null;
    public $answer_log_db = null;
    public $pool_db = null;
    public $group_log_db = null;
    public $games_orders_db = null;
    
    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        Log::DEBUG("query:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }
    
    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        Log::DEBUG("wx-pay:" . json_encode($data));

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        /* 这里调用慢
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            Log::DEBUG('request_error');
            return false;
        }
        */
        if ($data['result_code'] != 'SUCCESS')
        {
            $msg = "支付失败";
            Log::DEBUG('result_error');
            return false;
        }
        
        $order_sn = $data['attach'];
        $scan = "can";
        
        $order_info = $this->orders_db->where('order_sn="' . $order_sn . '"')->find();
        
        $data = array(
            'status' => 1
        );
        
        $this->orders_db->where('order_sn="' . $order_sn . '"')->save($data);
        
        if (strpos($order_sn, 'RD') == 0)
        {
            $red_times = $this->red_times_db->where('user_id=' . $order_info['user_id'])->find();
            
            if ($red_times == null)
            {
                $data = array(
                    'user_id' => $order_info['user_id'],
                    'times' => ceil($order_info['price']/2)
                );
                
                $this->red_times_db->add($data);
            }
            else
            {
                $this->red_times_db->where('user_id=' . $order_info['user_id'])->setInc('times', ceil($order_info['price']/2));
            }
            
            // 存入奖金池
            $data = array(
                'type' => 1,
                'money' => $order_info['price'],
                'create_time' => date('Y-m-d H:i:s', time()),
                'user_id' => $order_info['user_id'],
                'memo' => '订单：' . $order_info['order_sn']
            );
            $this->pool_db->add($data);
        }
        if (strpos($order_sn, 'RA') == 0)
        {
            $topic_id = $order_info['memo'];
            $data = array(
                'status' => 0,
            );
            $this->answer_log_db->where('user_id=' . $order_info['user_id'] . " and topic_id=$topic_id")->save($data);
            
            Log::DEBUG('处理问答:' . $topic_id);
        }
        if (strpos($order_sn, 'RI') == 0)
        {
            $goods_id = $order_info['memo'];
            $data = array(
                'status' => 1
            );
            
            $this->group_log_db->where("order_sn='$order_sn'")->save($data);
        }
        
        $content = "订单号" . $order_sn . ",支付完成.";
        Log::DEBUG('处理:' . $content);
        
        return true;
    }
}

?>