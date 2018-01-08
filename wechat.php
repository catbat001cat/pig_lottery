<?php
define("TOKEN", "peng123456");    //定义TOKEN, “peng”是自己随便定义，这一句很重要！！！
$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {

}else{
    $wechatObj->valid();    //调用valid函数进行基本配置
}

class wechatCallbackapiTest
{
    private $access_token;    //定义一个access_token，用于后续调用微信接口（此篇用不到）

    public function __construct(){    //构造函数

    }

    
    public function valid(){    //用于基本配置的函数
        $echoStr = $_GET["echostr"];
        
        
        
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}
?>