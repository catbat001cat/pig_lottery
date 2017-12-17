<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "lib/WxPay.Api.php";
require_once 'lib/WxPay.Notify.php';
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
    public $controller = null;
    
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
        $transaction_id = $data['transaction_id'];
        $total_fee = $data['total_fee'];
        $scan = "can";
        
        $ret = $this->controller->deal_order($order_sn, $transaction_id, $total_fee);
        Log::DEBUG("order_sn:" . $order_sn . ',transaction_id:' . $transaction_id .',total_fee:' . $total_fee . ',deal_ret:' . $ret);
        
        return true;
    }
}

?>