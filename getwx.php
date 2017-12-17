<?php
header('Access-Control-Allow-Origin: *');
require_once "jssdk.php";

class RandChar
{

    function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i ++) {
            $str .= $strPol[rand(0, $max)]; // rand($min,$max)鐢熸垚浠嬩簬min鍜宮ax涓や釜鏁颁箣闂寸殑涓�釜闅忔満鏁存暟
        }

        return $str;
    }
}

$jssdk = new JSSDK("wxbad9c4e348188922", "40da9b2b948f6a6066bf0225a56b5480");

    $url = $_REQUEST['req_url'];
    $signPackage = $jssdk->getSignPackage2($url);
    
    class Config{
        var $appId;
        var $timestamp;
        var $nonceStr;
        var $signature;
        var $url;
    }
    
    $config = new Config();
    
    $config->appId = $signPackage["appId"];
    $config->timestamp = $signPackage["timestamp"];
    $config->nonceStr = $signPackage["nonceStr"];
    $config->signature = $signPackage["signature"];
    $config->url = $signPackage["url"];
    
    echo json_encode($config);
?>