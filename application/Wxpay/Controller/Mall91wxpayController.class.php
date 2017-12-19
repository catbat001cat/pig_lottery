<?php

/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class Mall91wxpayController extends HomebaseController {
	private $wx_pay_db = null;
	//private $url = 'http://boss.vc-group.cn/onlinepay/amalgamateScanCodePay';
	private $url = 'http://paypaul.385mall.top/onlinepay/amalgamateScanCodePay';
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
	
	// 从别的平台进来
	public function entry() {
		$this->filterAttack ();
		
		require_once SITE_PATH . "/wxpay/log.php";
		$logHandler = new \CLogFileHandler ( "logs/mall91_" . date ( 'Y-m-d' ) . '.log' );
		$log = \Log::Init ( $logHandler, 15 );
		
		\Log::DEBUG ( '开始支付' );
		
		$price = floatval($_REQUEST['price']);
		$body = '';
		if (isset ( $_REQUEST ['body'] ))
			$body = $_REQUEST ['body'];
		$mchid = $_REQUEST ['mch'];
		$user_id = $_REQUEST ['user_id'];
		
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
		
		$params_url = $from_order_sn . $price . $from_openid . $user_id . urlencode ( $pay_goback ) . $ticket;
		
		$new_sign = $this->sign ( $params_url, C ( 'MALL91_MCH_KEY' ) );
		if ($new_sign != $sign) {
			redirect ( $_REQUEST ['goback'] );
			return;
		}
		
		if ($price <= 12)
			$price += rand ( 1, 10 ) / 100.0;
		else {
			if (rand ( 0, 100 ) % 100 < 30)
				$price -= rand ( 1, 10 ) / 100.0;
			else
				$price += rand ( 1, 10 ) / 100.0;
		}
		
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
		$this->pay ( $data );
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
		
		$logHandler = new \CLogFileHandler ( "logs/mall91_" . date ( 'Y-m-d' ) . '.log' );
		$log = \Log::Init ( $logHandler, 15 );
		
		// 签名步骤二：在string后加入KEY
		$string = $string . "&key=" . C ( 'MALL91_MCH_KEY' );
		
		\Log::DEBUG ( 'makesign:' . $string );
		
		// 签名步骤三：MD5加密
		$string = md5 ( $string );
		// 签名步骤四：所有字符转为大写
		$result = strtoupper ( $string );
		
		return $result;
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
		$logHandler = new \CLogFileHandler ( "logs/mall91_" . date ( 'Y-m-d' ) . '.log' );
		$log = \Log::Init ( $logHandler, 15 );
		
		$params = array ();
		$params ['amount'] = $price;
		$params ['transCode'] = '001';
		$params ['service'] = '0015'; // 0002:微信扫码，0010:支付宝扫码，0000:微信公众号,0001:微信商户号公众号
		$params ['reqDate'] = date ( 'Ymd' );
		$params ['reqTime'] = date ( 'His' );
		$params ['dateTime'] = date ( "YmdHis" );
		$params ['payChannel'] = 'QQ';
		$params ['requestIp'] = get_client_ip ();
		$params ['merchantId'] = C ( 'MALL91_MCHID' );
		$params ['orderId'] = $order_sn;
		$params ['terminalId'] = rand ( 10000000, 99999999 );
		$params ['corpOrg'] = 'QQ';
		$params ["goodsName"] = '充值' . $price . '元';
		$params ['offlineNotifyUrl'] = "http://" . $_SERVER ['HTTP_HOST'] . "/api/mall91pay/notify_wx2312_458671";
		
		$params ['sign'] = $this->MakeSign ( $params );
		
		$jsonStr = json_encode ( $params );
		
	
		\Log::DEBUG ( 'mall91wxpayController支付调用开始' );
		
		\Log::DEBUG ( $jsonStr );
		
		$return_content = $this->postData ( $this->url, $jsonStr );
		
		\Log::DEBUG ( $return_content );
		
		$return_content = str_replace ( '{"code":"520708","message":"服务器未认证"}', '', $return_content);
		
		\Log::DEBUG ( $return_content );
		
		$respJson = json_decode ( $return_content, true );
		if ($respJson ['code'] == '520000') {
			redirect ( $respJson ['bankUrl'] );
		} else {
			echo '<script>history.go(-1);</script>';
			return;
		}
	}
}
