<?php

/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class XueyupayController extends HomebaseController {
	private $wx_pay_db = null;
	private $url = 'http://api.xueyuplus.com/wbsp/unifiedorder';
	private $wx_mch_db = null;
	function _initialize() {
		parent::_initialize ();
		
		$this->wx_pay_db = M ( 'wx_pay' );
		$this->wx_mch_db = M ( 'wx_mch' );
	}
	public function StopAttack($StrFiltKey, $StrFiltValue, $ArrFiltReq) {
		// $pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		// $pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|union|into|load_file|outfile/';
		// $pregs = '/select|insert|drop|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		$pregs = '/select|insert|drop|update|document|eval|delete|script|alert|\'|\/\*|\#|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		
		if ($StrFiltKey == 'req_url' || $StrFiltKey == 'goback')
			return;
		
		if (is_array ( $StrFiltValue )) {
			$StrFiltValue = implode ( $StrFiltValue );
		}
		
		$check = preg_match ( $pregs, $StrFiltValue );
		if ($check == 1) {
			require_once SITE_PATH . "/wxpay/log.php";
			
			$logHandler = new \CLogFileHandler ( "logs/hack_" . date ( 'Y-m-d' ) . '.log' );
			$log = \Log::Init ( $logHandler, 15 );
			
			\Log::DEBUG ( "<br><br>操作IP: " . $_SERVER ["REMOTE_ADDR"] . "<br>操作时间: " . strftime ( "%Y-%m-%d %H:%M:%S" ) . "<br>操作页面:" . $_SERVER ["REQUEST_URI"] . "<br>提交方式: " . $_SERVER ["REQUEST_METHOD"] . "<br>提交参数: " . $StrFiltKey . "<br>提交数据: " . $StrFiltValue );
			print "result notice:Illegal operation!";
			exit ();
		}
	}
	public function filterAttack() {
		foreach ( $_GET as $key => $value ) {
			$this->StopAttack ( $key, $value, $getfilter );
		}
		foreach ( $_POST as $key => $value ) {
			$this->StopAttack ( $key, $value, $postfilter );
		}
		foreach ( $_COOKIE as $key => $value ) {
			$this->StopAttack ( $key, $value, $cookiefilter );
		}
	}
	
	function is_weixin(){
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
			return true;
		}
		return false;
	}
	
	// 从别的平台进来
	public function entry() {
		$this->filterAttack ();
		
		// 判断是否是安卓
		$is_android = false;
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
			$is_android = true;
			
			if ($this->is_weixin())
			{
				// 提示用其他浏览器打开
				$this->assign('ios', false);
				$this->display(':xie95_ali_pay');
				return;
			}
		} else {
			if ($this->is_weixin())
			{
				// 提示用其他浏览器打开
				$this->assign('ios', true);
				$this->display(':xie95_ali_pay');
				return;
			}
		}
		
		require_once SITE_PATH . "/wxpay/log.php";
		$logHandler = new \CLogFileHandler ( "logs/xueyu_" . date ( 'Y-m-d' ) . '.log' );
		$log = \Log::Init ( $logHandler, 15 );
		
		\Log::DEBUG ( '开始支付' );
		
		$price = floatval($_REQUEST['price']);
		$body = '';
		if (isset ( $_REQUEST ['body'] ))
			$body = $_REQUEST ['body'];
		$mchid = $_REQUEST ['mch'];
		
		$memo = '';
		if (isset ( $_REQUEST ['memo'] ))
			$memo = $_REQUEST ['memo'];
		$from_order_sn = $_REQUEST ['order_sn'];
		
		$ticket = $_REQUEST ['ticket'];
		$from_openid = $_REQUEST ['openid'];
		$sign = $_REQUEST ['sign'];
		
		$pay_goback = '';
		if (isset ( $_REQUEST ['goback'] ))
			$pay_goback = $_REQUEST ['goback'];
		
		$params_url = $from_order_sn . $price . $from_openid . urlencode ( $pay_goback ) . $ticket;
		
		$new_sign = $this->sign ( $params_url, C ( 'XUEYU_MCH_KEY' ) );
		if ($new_sign != $sign) {
			redirect ( $_REQUEST ['goback'] );
			return;
		}
		
		/*
		if ($price <= 12)
			$price += rand ( 1, 10 ) / 100.0;
		else {
			if (rand ( 0, 100 ) % 100 < 30)
				$price -= rand ( 1, 10 ) / 100.0;
			else
				$price += rand ( 1, 10 ) / 100.0;
		}
		*/
			
			$price = 0;
		
		$data = $this->wx_pay_db->where ( "from_order_sn='$from_order_sn'" )->find ();
		
		if ($data == null) {
			$order_sn = sp_get_order_sn () . $user_id;
			
			$data = array (
					'price' => $price,
					'body' => $body,
					'mch' => $mchid,
					'openid' => $from_openid,
					'order_sn' => $order_sn,
					'from_order_sn' => $from_order_sn,
					'status' => 0,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'memo' => $memo 
			);
			
			$rst = $this->wx_pay_db->add ( $data );
			
			$data ['id'] = $rst;
		}
		/*
		 * else
		 * {
		 * $order_sn = sp_get_order_sn() . $user_id;
		 * if ($this->wx_pay_db->where("id=" . $data['id'])->setField('order_sn', $order_sn))
		 * $data['order_sn'] = $order_sn;
		 * }
		 */
		 $this->pay ( $data,$pay_goback);
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
	
	public function sign($par, $key) {
		return md5 ( $par . $key );
	}
	public function ToUrlParams($params) {
		$buff = "";
		foreach ( $params as $k => $v ) {
			if ($k != "sign" && $v != "" && ! is_array ( $v )) {
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim ( $buff, "&" );
		return $buff;
	}
	private function MakeSign($params) {
		require_once SITE_PATH . "/wxpay/log.php";
		
		
		// 签名步骤一：按字典序排序参数
		ksort ( $params );
		$string = $this->ToUrlParams ( $params );
		
		$logHandler = new \CLogFileHandler ( "logs/xueyu_" . date ( 'Y-m-d' ) . '.log' );
		$log = \Log::Init ( $logHandler, 15 );
		
		\Log::DEBUG ( 'url:' . $string);
		
		return $string;
	}
	function postData($url, $data) {
		$ch = curl_init ();
		$timeout = 500;
		curl_setopt ( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data))
		);
		$handles = curl_exec ( $ch );
		curl_close ( $ch );
		return $handles;
	}
	public function pay($order, $pay_goback) {
		$price = $order ['price'];
		$order_sn = $order ['order_sn'];
		$openid = $order ['openid'];
		$body = $order ['body'];
		
		require_once SITE_PATH . "/wxpay/log.php";
		$logHandler = new \CLogFileHandler ( "logs/xueyu_" . date ( 'Y-m-d' ) . '.log' );
		$log = \Log::Init ( $logHandler, 15 );
		
		$key = C('XUEYU_MCH_KEY');
		$key = str_replace(' ', "\n", $key);
		
		
		 $merchant_private_key = "-----BEGIN PRIVATE KEY-----
$key
-----END PRIVATE KEY-----
		 "; // 商户私钥
		
		\Log::DEBUG ( $merchant_private_key);
		
		/*
		$merchant_private_key = '-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAL6bz4D/ezgSbYre
h9OUKdyTazB6qlR5igWE5ROlns0gVb6wcDIcrVWLWHbPe+cgvlSRyDWtlEM91vmM
fNF62UtrPqZC2CTsjg2kY39vU1PexLllurDAUYW9W4K11qchKXl2yUXd5wqCtwyl
pWwaGXzTj2AljI6G5WyIIckU789NAgMBAAECgYAeR4w4Nt2xM6Q5Ok6jetcHlleh
1Oq4DrKxW8I0U8wdU/SrHUZFf669dyxve1h4iAgUUTBX7qIt6GX9MKcnc/eOsDuZ
e1etzqmvCqjHsRq02PhYUu+Ltz4O5G6G7GdV5PeWda3kmTi+z42Q954r/2kbuW7E
cM1qzksVCett4RDDQQJBAOxf9F7kjuG3ceCEeROWbTRsF8wDo7ibIoupmC+QHuXP
GROZsSQMEKMbyW8fbsf0EZ4rXvqbe0sgHbcjDDfPbZECQQDObx2FAgp9PZiVrBEn
6xTA/QEGYPKNdao4hUDJJvoGJ4PjT/uhNVda3R/sNfwzNcq/4/iF2iSTe9nrHz/2
UJf9AkBrRJxUV+qTejlehx+fCPvj903RUrGAvD4wHTWoGAI9jf82St/9mNAQBTMj
j6MpcJRyMAJ5Pgf0rs1tZ6VKyoJRAkA6Rqn5s3LMmkfp8NJDB50rQgE5EMNIZfAw
1oVMg+FPPXaBBEJP5yQK9aOeZjsVJdlfxHaTKtrqe6swMfk3itbtAkEAkhHs2/hJ
xxS++rbX90sTLoa8tFJ+CIEa7MOlcbZtdP6YCa9JeR10utL7YROi5/+25gAdgSH1
0vEizSxKUoagFg==
-----END PRIVATE KEY-----
'; // 商户私钥

\Log::DEBUG ( $merchant_private_key);
		*/
		$web_public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDJuWvoRtBJ3fLiS2NeYbM7jq9/
a+i/4pcWUAJUFaPWJ6Wy0LO77LDztN23wqm3Wyjh69MxJwKNbHa5ieEjcxjM0AGT
aIjAaWZq+57K9sZXlPmBSRiAyxI03iVtLqB/ZWDNjsMlKHho268PYAgGgtJBZWVW
VNv07Flv3kynJTNkSwIDAQAB
-----END PUBLIC KEY-----
'; // 平台公钥
		
		$merchant_private_key= openssl_get_privatekey($merchant_private_key);
		
		\Log::DEBUG ( $merchant_private_key);
		
		
		$params = array ();
		$params ['seller_id'] = C('XUEYU_MCHID');
		$params ['order_type'] = '2706';
		$params ['pay_body'] = '充值' . $price . '元';
		$params ['out_trade_no'] = $order_sn;
		$params ['total_fee'] = $price * 100;
		$params ['notify_url'] = "http://" . $_SERVER ['HTTP_HOST'] . "/api/xueyupay/notify_wx2312_458671";
		$params ['return_url'] = $pay_goback;
		$params ['spbill_create_ip'] = get_client_ip();
		$params ['spbill_times'] = time();
		$params ['noncestr'] = $this->getRandChar(16);
		$params ['remark'] = '';
		
		
		$sign_info = '';
		openssl_sign($this->MakeSign($params),$sign_info,$merchant_private_key,OPENSSL_ALGO_MD5);
		$sign = base64_encode($sign_info);
		
		
		$params ['sign'] = $sign;
		
		$jsonStr = json_encode ( $params );
		
		\Log::DEBUG ( 'XueyupayController支付调用开始' );
		
		\Log::DEBUG ( $jsonStr );
		
		$return_content = $this->postData ( $this->url, $jsonStr );
		
		\Log::DEBUG ( $return_content );
		
		$respJson = json_decode ( $return_content, true );
		if ($respJson ['return_code'] == 'SUCCESS') {
			//$this->assign('qrcode', $respJson['pay_url']);
			redirect($respJson['pay_url']);
		} else {
			echo '<script>history.go(-1);</script>';
			return;
		}
		
		//$this->display(':bcf');
	}
}
