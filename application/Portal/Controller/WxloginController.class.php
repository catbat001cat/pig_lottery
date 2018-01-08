<?php
/*
 * _______ _ _ _ _____ __ __ ______
 * |__ __| | (_) | | / ____| \/ | ____|
 * | | | |__ _ _ __ | | _| | | \ / | |__
 * | | | '_ \| | '_ \| |/ / | | |\/| | __|
 * | | | | | | | | | | <| |____| | | | |
 * |_| |_| |_|_|_| |_|_|\_\\_____|_| |_|_|
 */
/*
 * _________ ___ ___ ___ ________ ___ __ ________ _____ ______ ________
 * |\___ ___\\ \|\ \|\ \|\ ___ \|\ \|\ \ |\ ____\|\ _ \ _ \|\ _____\
 * \|___ \ \_\ \ \\\ \ \ \ \ \\ \ \ \ \/ /|\ \ \___|\ \ \\\__\ \ \ \ \__/
 * \ \ \ \ \ __ \ \ \ \ \\ \ \ \ ___ \ \ \ \ \ \\|__| \ \ \ __\
 * \ \ \ \ \ \ \ \ \ \ \ \\ \ \ \ \\ \ \ \ \____\ \ \ \ \ \ \ \_|
 * \ \__\ \ \__\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \__\ \ \__\ \__\
 * \|__| \|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|__| \|__|\|__|
 */
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;

use Common\Controller\HomebaseController;

/**
 * 微信登录
 */
class WxloginController extends HomebaseController
{
    function _initialize() {
        header("Access-Control-Allow-Origin: *");
    }
    
    public function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq)
    {
    	//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
    	//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|union|into|load_file|outfile/';
    	//$pregs = '/select|insert|drop|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
    	$pregs = '/select|insert|drop|update|document|eval|delete|script|alert|\'|\/\*|\#|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
    	
    	if ($StrFiltKey == 'req_url' || $StrFiltKey == 'goback')
    		return;
    	
    	if(is_array($StrFiltValue))
    	{
    		$StrFiltValue=implode($StrFiltValue);
    	}
    	
    	$check= preg_match($pregs,$StrFiltValue);
    	if($check == 1){
    		require_once SITE_PATH . "/wxpay/log.php";
    		
    		$logHandler = new \CLogFileHandler ( "logs/hack_" . date ( 'Y-m-d' ) . '.log' );
    		$log = \Log::Init ( $logHandler, 15 );
    		
    		\Log::DEBUG("<br><br>操作IP: ".$_SERVER["REMOTE_ADDR"]."<br>操作时间: ".strftime("%Y-%m-%d %H:%M:%S")."<br>操作页面:".$_SERVER["REQUEST_URI"]."<br>提交方式: ".$_SERVER["REQUEST_METHOD"]."<br>提交参数: ".$StrFiltKey."<br>提交数据: ".$StrFiltValue);
    		print "result notice:Illegal operation!";
    		exit();
    	}
    }
    
    public function filterAttack()
    {
    	foreach($_GET as $key=>$value)
    	{
    		$this->StopAttack($key,$value,$getfilter);
    	}
    	foreach($_POST as $key=>$value)
    	{
    		$this->StopAttack($key,$value,$postfilter);
    	}
    	/*
    	foreach($_COOKIE as $key=>$value)
    	{
    		$this->StopAttack($key,$value,$cookiefilter);
    	}
    	*/
    }
    
    // 从别的平台进来
    public function entry() {
    	$this->filterAttack();
    	
        require_once "jssdk.php";
        
        $appid = C('LOGIN_APPID');
        $appsecret = C('LOGIN_APPSECRET');
        
        $req_url = $_REQUEST['req_url'];
        $jsapi_ticket = $_REQUEST['jsapi_ticket'];
        $jsapi_sign = $_REQUEST['sha'];
        
        $new_sign = md5(strtolower(urlencode($req_url) . $jsapi_ticket . C('LOGIN_KEY')));
        if ($jsapi_sign != $new_sign)
        {
            if (empty(C('BAN_GOURL')))
                echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
            else
                redirect(C('BAN_GOURL'));
            return;
        }
  
        $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/portal/wxlogin/login?req_url=" . urlencode($req_url) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign;
        $jssdk = new \JSSDK($appid, $appsecret);
        $url = $jssdk->gotoAuth($redirect_url, "code", "snsapi_base", "STATE");
        
        header("Location: $url");
        
        //redirect($url);
    }
    
    function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
    
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
    
        return $str;
    }
    
    public function login() {
    	$this->filterAttack();
    	
    	
        require_once "jssdk.php";
        	
        
        $jsapi_ticket = $_REQUEST['jsapi_ticket'];
        $jsapi_sign = $_REQUEST['sha'];
        
        $new_sign = md5(strtolower(urlencode($_REQUEST['req_url']) . $jsapi_ticket . C('LOGIN_KEY')));
        if ($jsapi_sign != $new_sign)
        {
            echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
            return;
        }        

        if (isset($_GET['code']))
            $code = $_GET["code"];
        else
            $code = '';
        
        $appid = C('LOGIN_APPID');
        $appsecret = C('LOGIN_APPSECRET');

        $jssdk = new \JSSDK($appid, $appsecret);
        $res = $jssdk->getAuthAccessToke($code);
        if (!property_exists($res, 'openid'))
        {
            echo "<script>alert('请在微信打开');</script>";
        }
        else
        {
        }

        $rand_string = $this->getRandChar(16);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        echo curl_error($ch);
        $obj = json_decode($result);
        
        echo '111233';
        
        //curl_close($ch);
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$obj->access_token}&type=jsapi";
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        //echo curl_error($ch);
        $obj2 = json_decode($result);
        curl_close($ch);
        $timestamp = time();
        $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
        $str = "jsapi_ticket={$obj2->ticket}&noncestr={$rand_string}&timestamp={$timestamp}&url={$url}";
        print_r($str);
        $signature = sha1($str);
        
        $rand_string = $this->getRandChar(32);
        
        if (!empty($_REQUEST['req_url']) || isset($_REQUEST['req_url'])) {
            $req_url=$_REQUEST['req_url'];
        } else {
            $req_url=0;
        }
        
        $ticket = time();
        $sign = md5(strtolower($res->openid . $ticket . $rand_string . C('LOGIN_KEY')));
        
        $goto_url = urldecode($req_url) . '&openid=' . $res->openid . '&noncestr=' . $rand_string . '&ticket=' . $ticket . '&sign=' . $sign;
        
        //header("Location: $goto_url");
        
       // redirect($goto_url);
    }
    
    
    public function login_zp() {
    	$this->filterAttack();
    	
    	
    	require_once "jssdk.php";
    	
    	
    	$jsapi_ticket = $_REQUEST['jsapi_ticket'];
    	$jsapi_sign = $_REQUEST['sha'];
    	
    	$new_sign = md5(strtolower(urlencode($_REQUEST['req_url']) . $jsapi_ticket . C('LOGIN_KEY')));
    	if ($jsapi_sign != $new_sign)
    	{
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	if (isset($_GET['code']))
    		$code = $_GET["code"];
    		else
    			$code = '';
    			
    			$appid = C('LOGIN_APPID');
    			$appsecret = C('LOGIN_APPSECRET');
    			
    			$jssdk = new \JSSDK($appid, $appsecret);
    			$res = $jssdk->getAuthAccessToke($code);
    			if (!property_exists($res, 'openid'))
    			{
    				echo "<script>alert('请在微信打开');</script>";
    			}
    			else
    			{
    			}
    			
    			$rand_string = $this->getRandChar(32);
    			
    			if (!empty($_REQUEST['req_url']) || isset($_REQUEST['req_url'])) {
    				$req_url=$_REQUEST['req_url'];
    			} else {
    				$req_url=0;
    			}
    			
    			$ticket = time();
    			$sign = md5(strtolower($res->openid . $ticket . $rand_string . C('LOGIN_KEY')));
    			
    			$goto_url = urldecode($req_url) . '&openid=' . $res->openid . '&noncestr=' . $rand_string . '&ticket=' . $ticket . '&sign=' . $sign;
    			
    			header("Location: $goto_url");
    }
}


