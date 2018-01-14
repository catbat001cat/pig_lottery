<?php

namespace Qqonline\Controller;

use Common\Controller\HomebaseController;

class PayController extends HomebaseController {
	private $wallet_db = null;
	private $lottery_db = null;
	private $lottery_ratio_db = null;
	private $lottery_order_db = null;
	private $recharge_db = null;
	private $user_id = 0;
	function _initialize() {
		parent::_initialize ();
		
		$this->wallet_db = M ( 'wallet' );
		$this->lottery_db = M ( 'lottery' );
		$this->lottery_ratio_db = M ( 'lottery_ratio' );
		$this->lottery_order_db = M ( 'lottery_order' );
		$this->recharge_db = M ( 'recharge_order' );
	}
	
	public function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq)
	{
		//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|union|into|load_file|outfile/';
		$pregs = '/select|insert|drop|update|document|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		
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
	
	public function asset_login() {
		$this->user_id = $_SESSION ["user"] ["id"];
		if ($this->user_id == 0) {
			$result ["code"] = - 1;
			$result ["msg"] = "需要登录：" . session_id () . ',' . $_REQUEST ["session_id"];
			echo json_encode ( $result );
			exit ();
		}
	}
	function is_weixin() {
		if (strpos ( $_SERVER ['HTTP_USER_AGENT'], 'MicroMessenger' ) !== false) {
			return true;
		}
		return false;
	}
	public function ali_pay_pre() {
		$this->filterAttack();
		
		$price = $_REQUEST ['price'];
		$body = $_REQUEST ['body'];
		$mchid = $_REQUEST ['mch'];
		$pay_goback = $_REQUEST ['pay_goback'];
		$memo = $_REQUEST ['memo'];
		$order_sn = $_REQUEST ['order_sn'];
		$pay_url = $_REQUEST ['pay_url'];
		$openid = $_REQUEST ['openid'];
		$sign = $_REQUEST ['sign'];
		$ticket = $_REQUEST ['ticket'];
		
		$params_url = $order_sn . $price . $openid . urlencode ( $pay_goback ) . $ticket;
		
		$new_sign = $this->makeSign ( $params_url, C ( 'YOUCHANG_MCH_KEY' ) );
		
		if ($new_sign != $sign) {
			$user_db = M ( 'users' );
			$user = $user_db->where ( "openid='$openid'" )->find ();
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $user ['id'],
					'action' => 'hack',
					'params' => '签名不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
			return;
		}
		
		if ($this->is_weixin ()) {
			$this->display ( ':ali_pay' );
			return;
		}
		
		$url = $pay_url . '&openid=' . $openid . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mchid . '&pay_goback=' . urlencode ( $pay_goback ) . '&ticket=' . $ticket;
		$url .= '&sign=' . $new_sign;
		
		redirect ( $url );
	}
	public function ali_pay_pre2() {
		$this->filterAttack();
		
		$price = $_REQUEST ['price'];
		$body = $_REQUEST ['body'];
		$mchid = $_REQUEST ['mch'];
		$memo = $_REQUEST ['memo'];
		$order_sn = $_REQUEST ['order_sn'];
		$pay_url = $_REQUEST ['pay_url'];
		$openid = $_REQUEST ['openid'];
		$sign = $_REQUEST ['sign'];
		$ticket = $_REQUEST ['ticket'];
		
		$params_url = $order_sn . $price . $openid . $ticket;
		
		$new_sign = $this->makeSign ( $params_url, C ( 'BFT_SIGNKEY' ) );
		
		if ($new_sign != $sign) {
			$user_db = M ( 'users' );
			$user = $user_db->where ( "openid='$openid'" )->find ();
			
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $user ['id'],
					'action' => 'hack',
					'params' => '签名不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo '<script>window.close();</script>';
			return;
		}
		
		if ($this->is_weixin ()) {
			$this->display ( ':ali_pay' );
			return;
		}
		
		$url = $pay_url . '&openid=' . $openid . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mchid . '&ticket=' . $ticket;
		$url .= '&sign=' . $new_sign;
		
		redirect ( $url );
	}
	public function qq_pay_pre() {
		$this->filterAttack();
		
		$price = $_REQUEST ['price'];
		$body = $_REQUEST ['body'];
		$mchid = $_REQUEST ['mch'];
		$pay_goback = $_REQUEST ['pay_goback'];
		$memo = $_REQUEST ['memo'];
		$order_sn = $_REQUEST ['order_sn'];
		$pay_url = $_REQUEST ['pay_url'];
		$openid = $_REQUEST ['openid'];
		$sign = $_REQUEST ['sign'];
		$ticket = $_REQUEST ['ticket'];
		
		$params_url = $order_sn . $price . $openid . urlencode ( $pay_goback ) . $ticket;
		
		$new_sign = $this->makeSign ( $params_url, C ( 'WFT_QQ_MCH_KEY' ) );
		
		if ($new_sign != $sign) {
			$user_db = M ( 'users' );
			$user = $user_db->where ( "openid='$openid'" )->find ();
			
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $user ['id'],
					'action' => 'hack',
					'params' => '签名不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo '<script>window.close();</script>';
			return;
		}
		
		if ($this->is_weixin ()) {
			$this->display ( ':ali_pay' );
			return;
		}
		
		$url = $pay_url . '&openid=' . $openid . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mchid . '&pay_goback=' . urlencode ( $pay_goback ) . '&ticket=' . $ticket;
		$url .= '&sign=' . $new_sign;
		
		redirect ( $url );
	}
	public function makeSign($par, $key) {
		return md5 ( $par . $key );
	}
	public function create_order() {
		$this->asset_login ();
		
		if (C ( 'IS_STOPPED_RECHARGE' ) == '1') {
			if (session ( 'is_admin_enter' ) == '0') {
				$this->assign ( 'tips', '系统维护,暂停充值' );
				$this->display ( ':error' );
				return;
			}
		}
		
		$price = $_REQUEST ['price'];
		
		$data = array (
				'user_id' => $this->user_id,
				'price' => $price,
				'order_sn' => sp_get_order_sn (),
				'status' => 0,
				'create_time' => date ( 'Y-m-d H:i:s' ) 
		);
		
		$recharge_prices = split ( ',', str_replace ( ' ', '', C ( 'RECHARGE_PRICES' ) ) );
		
		$is_finded = false;
		for($i = 0; $i < count ( $recharge_prices ); $i ++) {
			if ($price == intval ( $recharge_prices [$i] )) {
				$is_finded = true;
				break;
			}
		}
		
		$ticket = $_REQUEST ['ticket'];
		$sign = $_REQUEST ['sign'];
		
		$new_sign = md5 ( 'create_order' . $price . $ticket );
		if ($new_sign != $sign) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '请求支付验证不通过:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
			return;
		}
		
		if (! $is_finded || $price <= 0) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '充值不正确金额:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
			return;
		}
		
		$rst = $this->recharge_db->add ( $data );
		
		if ($rst <= 0) {
			echo "<script>history.go(-1);</script>";
			return;
		}
		
		// 日志
		$action_log = M ( 'user_action_log' );
		$log_data = array (
				'user_id' => $this->user_id,
				'action' => 'create_order',
				'params' => '订单:' . $rst . ',金额:' . $price,
				'ip' => get_client_ip ( 0, true ),
				'create_time' => date ( 'Y-m-d H:i:s' ) 
		);
		$action_log->add ( $log_data );
		
		$channels = array ();
		
		$ticket = time ();
		
		$wxpay_is_enabled = (C ( 'WXPAY_ENABLED' ) == '1');
		
		if (! $wxpay_is_enabled) {
			if (C ( 'WXPAY_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$wxpay_is_enabled = true;
		}
		
		// 判断是否充值过于频繁
		if (session ( 'time_recharge_ticket' ) != null) {
			$ticket = session ( 'time_recharge_ticket' );
			
			if (time () - $ticket <= 30) {
				echo '<script>alert("支付频率不能过快，请30秒后再支付该金额");history.go(-1);</script>';
				return;
			}
		}
		
		session ( 'time_recharge_ticket', time () );
		
		// 充值域名，除非特殊的只能指定
		$hosts_db = M ( 'hostnames' );
		$hosts = $hosts_db->where ( 'status=1 and `type` in (4,2)' )->order ( '`type` desc, update_time desc' )->select ();
		
		shuffle ( $hosts );
		$host = $hosts [0];
		
		if ($wxpay_is_enabled) {
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '微信公众号支付';
			$channel_data ['page_url'] = str_replace ( '&amp;', '&', C ( 'PAGE_URL' ) );
			$channel_data ['return_url'] = str_replace ( '&amp;', '&', C ( 'RETURN_URL' ) );
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'PAY_URL' ) );
			$channel_data ['mch'] = C ( 'TERM_ID' );
			
			$channel_data ['page_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['page_url'] );
			$channel_data ['return_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['return_url'] );
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['pay_url'] );
			
			$pay_goback = urlencode ( $channel_data ['page_url'] );
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $pay_goback . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&pay_goback=' . $pay_goback . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			array_push ( $channels, $channel_data );
		}
		
		$wft_is_enabled = (C ( 'WFT_ENABLED' ) == '1');
		
		if (! $wft_is_enabled) {
			if (C ( 'WFT_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$wft_is_enabled = true;
		}
		
		if ($wft_is_enabled) {
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '微信支付一';
			$channel_data ['page_url'] = str_replace ( '&amp;', '&', C ( 'WFT_PAGE_URL' ) );
			$channel_data ['return_url'] = str_replace ( '&amp;', '&', C ( 'WFT_RETURN_URL' ) );
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'WFT_PAY_URL' ) );
			$channel_data ['mch'] = C ( 'WFT_TERM_ID' );
			
			$channel_data ['page_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['page_url'] );
			$channel_data ['return_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['return_url'] );
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['pay_url'] );
			
			$pay_goback = urlencode ( $channel_data ['page_url'] );
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'] * 100;
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $pay_goback . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&pay_goback=' . $pay_goback . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'WFT_MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			array_push ( $channels, $channel_data );
		}
		
		$wft_qq_is_enabled = (C ( 'WFT_QQ_ENABLED' ) == '1');
		
		if (! $wft_qq_is_enabled) {
			if (C ( 'WFT_QQ_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$wft_qq_is_enabled = true;
		}
		
		if ($wft_qq_is_enabled) {
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = 'QQ钱包支付';
			$channel_data ['page_url'] = str_replace ( '&amp;', '&', C ( 'WFT_QQ_PAGE_URL' ) );
			$channel_data ['return_url'] = str_replace ( '&amp;', '&', C ( 'WFT_QQ_RETURN_URL' ) );
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'WFT_QQ_PAY_URL' ) );
			$channel_data ['mch'] = C ( 'WFT_QQ_TERM_ID' );
			
			$channel_data ['page_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['page_url'] );
			$channel_data ['return_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['return_url'] );
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			
			$pay_goback = urlencode ( $channel_data ['page_url'] );
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'] * 100;
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $pay_goback . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&pay_goback=' . $pay_goback . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'WFT_QQ_MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			array_push ( $channels, $channel_data );
		}
		
		if (C ( 'YOUCHANG_ENABLED' ) == '1') {
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '微信支付二';
			$channel_data ['page_url'] = str_replace ( '&amp;', '&', C ( 'YOUCHANG_PAGE_URL' ) );
			$channel_data ['return_url'] = str_replace ( '&amp;', '&', C ( 'YOUCHANG_RETURN_URL' ) );
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'YOUCHANG_PAY_URL' ) );
			$channel_data ['mch'] = C ( 'YOUCHANG_TERM_ID' );
			
			$channel_data ['page_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['page_url'] );
			$channel_data ['return_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['return_url'] );
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			
			$pay_goback = urlencode ( $channel_data ['page_url'] );
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'] * 100;
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $pay_goback . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&pay_goback=' . $pay_goback . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'YOUCHANG_MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			array_push ( $channels, $channel_data );
		}
		
		if (C ( 'YOUCHANG_ALI_ENABLED' ) == '1') {
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '支付宝支付';
			$channel_data ['page_url'] = str_replace ( '&amp;', '&', C ( 'YOUCHANG_ALI_PAGE_URL' ) );
			$channel_data ['return_url'] = str_replace ( '&amp;', '&', C ( 'YOUCHANG_ALI_RETURN_URL' ) );
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'YOUCHANG_ALI_PAY_URL' ) );
			$channel_data ['mch'] = C ( 'YOUCHANG_TERM_ID' );
			
			$channel_data ['page_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['page_url'] );
			$channel_data ['return_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['return_url'] );
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			
			$pay_goback = urlencode ( $channel_data ['page_url'] );
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'] * 100;
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $pay_goback . $ticket;
			$sign = $this->makeSign ( $params_url, C ( 'YOUCHANG_MCH_KEY' ) );
			
			$url = 'index.php?g=Qqonline&m=pay&a=ali_pay_pre&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&pay_goback=' . $pay_goback . '&pay_url=' . urlencode ( $pay_url ) . '&ticket=' . $ticket;
			
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '1';
			
			array_push ( $channels, $channel_data );
		}
		/*
		 * else
		 * {
		 * echo json_encode(array(
		 * 'ret' => 0,
		 * 'msg' => '没有可用的支付通道'
		 * ));
		 * }
		 */
		
		$bft_is_enabled = (C ( 'BFT_ENABLED' ) == '1');
		
		if (! $bft_is_enabled) {
			if (C ( 'BFT_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$bft_is_enabled = true;
		}
		
		if ($bft_is_enabled) {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '微信支付三';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'BFT_PAY_URL' ) );
			$channel_data ['mch'] = C ( 'BFT_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'BFT_SIGNKEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			array_push ( $channels, $channel_data );
			
			// 阿里支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '支付宝支付二';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'BFT_PAY_URL_ALI' ) );
			$channel_data ['mch'] = C ( 'BFT_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			// $url = $pay_url . '&openid=' . $channel_data['openid'] . '&body=' . $body . '&order_sn=' . $order_sn. '&price=' . $price . '&mch=' . $mch . '&ticket=' . $ticket;
			$url = 'index.php?g=Qqonline&m=pay&a=ali_pay_pre2&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&pay_url=' . urlencode ( $pay_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'BFT_SIGNKEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '1';
			
			array_push ( $channels, $channel_data );
		}
		
		$yubwx_is_enabled = (C ( 'YUBWX_ENABLED' ) == '1');
		
		if (! $yubwx_is_enabled) {
			if (C ( 'YUBWX_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$yubwx_is_enabled = true;
		}
		
		if ($yubwx_is_enabled) {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '微信二维码支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'YUBWX_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'YUBWX_GO_URL' ) );
			$channel_data ['mch'] = C ( 'YUBWX_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'YUBWX_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			array_push ( $channels, $channel_data );
		}
		
		$zszf_is_enabled = (C ( 'ZSZF_ENABLED' ) == '1');
		
		if (! $zszf_is_enabled) {
			if (C ( 'ZSZF_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$yubwx_is_enabled = true;
		}
		
		if ($zszf_is_enabled) {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '掌上支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'ZSZF_PAY_URL' ) );
			$channel_data ['mch'] = C ( 'ZSZF_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'ZSZF_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			array_push ( $channels, $channel_data );
		}
		
		if (count ( $channels ) == 1) {
			// 只有一个渠道,就不用这么麻烦了
			redirect ( $channels [0] ['url'] );
		} else {
			$this->assign ( 'channels', $channels );
			
			$this->display ( ':pay_channels' );
		}
	}
	public function create_lottery_order() {
		$this->filterAttack();
		
		$this->asset_login ();
		
		// 判断是否暂停投注
		if (C ( 'IS_STOPPED_LOTTERY' ) == '1') {
			if (session ( 'is_admin_enter' ) == '0') {
				echo json_encode ( array (
						'ret' => - 1,
						'msg' => '系统维护,暂停投注' 
				) );
				return;
			}
		}
		
		$no = $_REQUEST ['no'];
		$price = $_REQUEST ['price'];
		$buy_type = $_REQUEST ['buy_type'];
		$buy_method = $_REQUEST ['buy_method'];
		
		$buy_types = split ( ',', $buy_type );
		
		$price_mul = split ( ',', str_replace ( ' ', '', C ( 'LOTTERY_PRICE_MUL' ) ) );
		
		$ticket = $_REQUEST ['ticket'];
		$sign = $_REQUEST ['sign'];
		
		$new_sign = md5 ( 'create_lottery_order' . $no . $price . $buy_type . $buy_method . $ticket );
		if ($new_sign != $sign) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注验证不通过:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '数据错误' 
			) );
			return;
		}
		
		if ( $this->lottery_order_db->where ( "user_id=$this->user_id and no='$no' and buy_method=$buy_method")->count() > 0)
		{
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '请等待开奖'
			) );
			return;
		}
		
		if ($price <= 0) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注金额不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '数据出错' 
			) );
			return;
		}
		
		$is_finded = false;
		/*
		for($i = 0; $i < count ( $price_mul ); $i ++) {
			if (intval ( $price_mul [$i] ) * count ( $buy_types ) * intval ( C ( 'LOTTERY_SINGLE_PRICE' ) ) == intval ( $price )) {
				$is_finded = true;
				break;
			}
		}
		*/
		
	   if ($buy_method == '0' && $price >= intval(C('LOTTERY_SINGLE_PRICE')) && $price <= 100 * intval(C('LOTTERY_SINGLE_PRICE')))
	       $is_finded = true;
	   else if ($buy_method == '2' && $price >= intval(C('LOTTERY_SINGLE_PRICE')) && $price <= 20 * intval(C('LOTTERY_SINGLE_PRICE')))
	       $is_finded = true;
		
		$wallet = $this->wallet_db->where ( "user_id=" . $this->user_id )->find ();
		
		if (! $is_finded) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注金额不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '数据出错'
			) );
			return;
		}
		
		// 判断是否允许购买
		$lottery = $this->lottery_db->where ( "no='$no' and UNIX_TIMESTAMP(open_time)-UNIX_TIMESTAMP(now())>8 and `status`=0" )->find ();
		
		if ($lottery == null) {
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '请等待开奖' 
			) );
			return;
		}
		
		$buy_type_count = count ( $buy_types );
		
		if (substr ( C ( 'LOTTERY_PRICE_RATIO' ), - 1 ) == '%')
			$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) ) * $price / 100;
		else
			$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) );
		
		$total_price = $price + $discount_price;
		
		$det = $wallet ['money'] + $wallet ['money3'] - $total_price;
		$money_det = $wallet ['money'] - $price;
		
		if ($det < 0 || $money_det < 0)
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '余额不足' 
			) );
		else {
			
			// 先扣除手续费
			$need_sub_all = true;
			if ($wallet ['money3'] >= $discount_price) {
				$need_sub_all = false;
				
				$ret = $this->wallet_db->where ( "user_id=" . $this->user_id )->setDec ( 'money3', $discount_price );
				
				if ($ret <= 0)
					$need_sub_all = true;
			}
			
			if (need_sub_all)
				$ret = $this->wallet_db->where ( "user_id=" . $this->user_id )->setDec ( 'money', $total_price );
			else {
				$ret = $this->wallet_db->where ( "user_id=" . $this->user_id )->setDec ( 'money', $price );
			}
			
			if ($ret <= 0)
				echo json_encode ( array (
						'ret' => - 1,
						'msg' => '余额不足' 
				) );
			else {
				
				// 统计当前用户投注情况
				$total_orders = $this->lottery_order_db->where ( "user_id=$this->user_id and no='$no'" )->select ();
				
				$is_full = false;
				
				$big_prices = 0;
				$mid_prices = 0;
				$small_prices = 0;
				$odd_prices = 0;
				$event_prices = 0;
				
				for($j = 0; $j < count ( $total_orders ); $j ++) {
					$cur_buy_types = split ( ',', $total_orders [$j] ['buy_type'] );
					for($k = 0; $k < count ( $cur_buy_types ); $k ++) {
						if ($cur_buy_types [$k] == '1')
							$big_prices += floatval ( $total_orders [$j] ['price'] ) / count ( $cur_buy_types );
						else if ($cur_buy_types [$k] == '0')
							$mid_prices += floatval ( $total_orders [$j] ['price'] ) / count ( $cur_buy_types );
						else if ($cur_buy_types [$k] == '-1')
							$small_prices += floatval ( $total_orders [$j] ['price'] ) / count ( $cur_buy_types );
						else if ($cur_buy_types [$k] == '10')
							$odd_prices += floatval ( $total_orders [$j] ['price'] ) / count ( $cur_buy_types );
						else if ($cur_buy_types [$k] == '11')
							$event_prices += floatval ( $total_orders [$j] ['price'] ) / count ( $cur_buy_types );
					}
				}
				
				for($k = 0; $k < count ( $buy_types ); $k ++) {
					if ($buy_types [$k] == '1')
						$big_prices += floatval ( $price ) / count ( $buy_types );
					else if ($buy_types [$k] == '0')
						$mid_prices += floatval ( $price ) / count ( $buy_types );
					else if ($buy_types [$k] == '-1')
						$small_prices += floatval ( $price ) / count ( $buy_types );
					else if ($buy_types [$k] == '10')
						$odd_prices += floatval ( $price ) / count ( $buy_types );
					else if ($buy_types [$k] == '11')
						$event_prices += floatval ( $price ) / count ( $buy_types );
				}
				
				if ($big_prices > floatval ( C ( 'LOTTERY_SINGLE_PRICE_MAX' ) )) {
					echo json_encode ( array (
							'ret' => - 1,
							'msg' => '投大超过' . C ( 'LOTTERY_SINGLE_PRICE_MAX' ) . '限额' 
					) );
					return;
				} else if ($mid_prices > floatval ( C ( 'LOTTERY_SINGLE_PRICE_MAX' ) )) {
					echo json_encode ( array (
							'ret' => - 1,
							'msg' => '投合超过' . C ( 'LOTTERY_SINGLE_PRICE_MAX' ) . '限额' 
					) );
					return;
				} else if ($small_prices > floatval ( C ( 'LOTTERY_SINGLE_PRICE_MAX' ) )) {
					echo json_encode ( array (
							'ret' => - 1,
							'msg' => '投小超过' . C ( 'LOTTERY_SINGLE_PRICE_MAX' ) . '限额' 
					) );
					return;
				} else if ($odd_prices > floatval ( C ( 'LOTTERY_SINGLE_PRICE_MAX' ) )) {
					echo json_encode ( array (
							'ret' => - 1,
							'msg' => '投单超过' . C ( 'LOTTERY_SINGLE_PRICE_MAX' ) . '限额' 
					) );
					return;
				} else if ($event_prices > floatval ( C ( 'LOTTERY_SINGLE_PRICE_MAX' ) )) {
					echo json_encode ( array (
							'ret' => - 1,
							'msg' => '投双超过' . C ( 'LOTTERY_SINGLE_PRICE_MAX' ) . '限额' 
					) );
					return;
				}
				
				if (substr ( C ( 'LOTTERY_PRICE_RATIO' ), - 1 ) == '%') {
					$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) ) * $price / 100;
				} else {
					$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) );
				}
				
				$data = array (
						'user_id' => $this->user_id,
						'no' => $no,
						'price' => $price,
						'discount_price' => $discount_price,
						'buy_type' => $buy_type,
						'buy_method' => $buy_method,
						'buy_type_count' => $buy_type_count,
						'status' => 0,
						'win' => 0,
						'create_time' => date ( 'Y-m-d H:i:s' ),
						'is_payed' => 1 
				);
				
				for($i = 0; $i < $buy_type_count; $i ++) {
					$name = '' . $buy_method . '_' . $buy_types [$i];
					$data [$name] = 1;
				}
				
				$data ['id'] = $this->lottery_order_db->add ( $data );
				
				$action_log = M ( 'user_action_log' );
				$log_data = array (
						'user_id' => $this->userid,
						'action' => 'buy_lottery',
						'params' => '类型:' . $buy_type . ',组合:' . $buy_method . ',订单:' . $data ['id'],
						'ip' => get_client_ip ( 0, true ),
						'create_time' => date ( 'Y-m-d H:i:s' ) 
				);
				$action_log->add ( $log_data );
				
				$change_db = M ( 'wallet_change_log' );
				
				$change_data = array (
						'user_id' => $this->user_id,
						'object_id' => $data ['id'],
						'type' => 1,
						'fee' => $total_price,
						'create_time' => date ( 'Y-m-d H:i:s' ),
						'memo' => '投注' 
				);
				
				$change_db->add ( $change_data );
				
				// 这里处理佣金
				$this->process_commision($data);
				
				echo json_encode ( array (
						'ret' => 1,
						'data' => $data 
				) );
			}
		}
	}
	function insert_channel_commision($channel_user_id, $order_id, $divide_ratio, $fee) {
		$comission_fee = $fee * $divide_ratio / 100.0;
		
		if ($comission_fee <= 0 || $comission_fee > 1000)
			return;
		
		M ( 'wallet' )->where ( "user_id=$channel_user_id" )->setInc ( 'money2', $comission_fee );
		
		$db = M ( 'wallet_change_log' );
		
		$data = array (
				'user_id' => $channel_user_id,
				'object_id' => $order_id,
				'type' => 4,
				'divide_ratio' => $divide_ratio / 100.0,
				'fee' => $comission_fee,
				'create_time' => date ( 'Y-m-d H:i:s' ),
				'memo' => '下线投注的佣金' 
		);
		
		$db->add ( $data );
	}
	
	// 处理佣金
	function process_commision($order) {
		$order_db = M ( 'lottery_order' );
		
		$channel_db = M ( 'channels b' );
		
		// 有效日期
		$n = C ( 'COMMISION_VALID_TIME' );
		
		$from_start_time = date ( "Y-m-d H:i:s", time () - $n * 3600 );
		
		$my_channel = $channel_db->where ( "b.admin_user_id=" . $order ['user_id'] . " and b.create_time>='" . $from_start_time . "'" )->find ();
		
		// 超过限制了
		if ($my_channel == null)
			return;
		
		$fee = floatval ( $order ['price'] ); // - floatval($order['win']);
		
		if ($fee <= 0)
			return;

		if ($my_channel['parent_channels'] != null) { // 1,5
		                                          // 所有父渠道
			$parent_channels = $channel_db->where ( 'id in (' . $my_channel['parent_channels'] . ')' )->select ();
			
			$level_ratio = C ( 'COMMISION_DIVIDE_RATIO5' );
			
			$count = 0;
			
			for($j = count ( $parent_channels ) - 1; $j >= 0; $j --) {
				// 这里需要过滤已经不纳入佣金的渠道
				if ($count == 0)
					$level_ratio = C ( 'COMMISION_DIVIDE_RATIO' );
				else if ($count == 1)
					$level_ratio = C ( 'COMMISION_DIVIDE_RATIO2' );
				else if ($count == 2)
					$level_ratio = C ( 'COMMISION_DIVIDE_RATIO3' );
				else if ($count == 3)
					$level_ratio = C ( 'COMMISION_DIVIDE_RATIO4' );
				else
					$level_ratio = C ( 'COMMISION_DIVIDE_RATIO5' );
				
				$count ++;
				
				$parent_channel = $parent_channels [$j];
				
				$ratio = $level_ratio;
				
				// 插入一条
				$this->insert_channel_commision ( $parent_channel ['admin_user_id'], $order ['id'], $ratio, $fee );
				
				if ($count >= 5)
					break;
			}
		}
	}
	
	// 处理佣金(定时执行)
	function ajax_process_commision() {
		return;
		
		$order_db = M ( 'lottery_order' );
		
		$channel_db = M ( 'channels' );
		
		// 有效日期
		$n = C ( 'COMMISION_VALID_TIME' );
		
		/*
		 * $orders = $order_db->alias ( 'a' )
		 * ->join ( '__CHANNELS__ b on b.admin_user_id=a.user_id', 'left' )
		 * ->where ( "a.commision_status=0 and (a.status=1 or a.status=2) and hour(timediff(now(), b.create_time))<$n" )
		 * ->field ( 'a.*, b.admin_user_id as channel_user_id, b.divide_ratio as channel_divide_ratio,b.parent_channels' )
		 * ->select ();
		 */
		
		$from_start_time = date ( "Y-m-d H:i:s", time () - $n * 3600 );
		
		$orders = $order_db->alias ( 'a' )->join ( '__CHANNELS__ b on b.admin_user_id=a.user_id', 'left' )->where ( "a.commision_status=0 and (a.status=1 or a.status=2) and b.create_time>'" . $from_start_time . "'" )->field ( 'a.*, b.admin_user_id as channel_user_id, b.divide_ratio as channel_divide_ratio,b.parent_channels' )->select ();
		
		for($i = 0; $i < count ( $orders ); $i ++) {
			// 获取这笔订单的所有可以分的渠道佣金
			$order = $orders [$i];
			
			$fee = floatval ( $order ['price'] ); // - floatval($order['win']);
			
			if ($fee <= 0)
				continue;
			
			$order_db->where ( 'id=' . $order ['id'] )->setField ( 'commision_status', 1 );
			
			if ($order ['parent_channels'] != null) { // 1,5
			                                          // 所有父渠道
				$parent_channels = $channel_db->where ( 'id in (' . $order ['parent_channels'] . ')' )->select ();
				
				$level_ratio = C ( 'COMMISION_DIVIDE_RATIO5' );
				
				$count = 0;
				
				for($j = count ( $parent_channels ) - 1; $j >= 0; $j --) {
					// 这里需要过滤已经不纳入佣金的渠道
					if ($count == 0)
						$level_ratio = C ( 'COMMISION_DIVIDE_RATIO' );
					else if ($count == 1)
						$level_ratio = C ( 'COMMISION_DIVIDE_RATIO2' );
					else if ($count == 2)
						$level_ratio = C ( 'COMMISION_DIVIDE_RATIO3' );
					else if ($count == 3)
						$level_ratio = C ( 'COMMISION_DIVIDE_RATIO4' );
					else
						$level_ratio = C ( 'COMMISION_DIVIDE_RATIO5' );
					
					$count ++;
					
					$parent_channel = $parent_channels [$j];
					
					$ratio = $level_ratio;
					
					// 插入一条
					$this->insert_channel_commision ( $parent_channel ['admin_user_id'], $order ['id'], $ratio, $fee );
					
					if ($count >= 5)
						break;
				}
			}
		}
	}
	public function notify_by_platform() {
		$this->filterAttack();
		
		require_once SITE_PATH . "/wxpay/log.php";
		
		$logHandler = new \CLogFileHandler ( "logs/notify_" . date ( 'Y-m-d' ) . '.log' );
		$log = \Log::Init ( $logHandler, 15 );
		
		$order_sn = $_REQUEST ['order_sn'];
		$total_fee = $_REQUEST ['total_fee'];
		$transition_id = $_REQUEST ['transition_id'];
		$ticket = $_REQUEST ['ticket'];
		$sign = $_REQUEST ['sign'];
		
		$new_sign = $this->makeSign ( $order_sn . $total_fee . $transition_id . $ticket, C ( 'MCH_KEY' ) );
		
		$order_db = M ( 'recharge_order' );
		
		$order = $order_db->where ( "id=$order_sn" )->find ();
		
		if ($sign != $new_sign) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $order ['user_id'],
					'action' => 'hack',
					'params' => '签名不正确,支付订单:' . $order_sn,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			\Log::DEBUG ( '签名不正确:' . $new_sign . ',' . $sign );
			echo 'fail';
			return;
		}
		
		if (intval ( $order ['price'] ) != intval ( $total_fee )) {
			echo 'fail';
			return;
		}
		
		if ($order != null && $order ['status'] == 0) {
			$recharge_data = array (
					'transition_id' => $transition_id,
					'status' => 1 
			);
			
			if ($this->recharge_db->where ( "id=$order_sn" )->save ( $recharge_data )) {
				$wallet = $this->wallet_db->where ( "user_id=" . $order ['user_id'] )->find ();
				
				$this->wallet_db->where ( "user_id=" . $order ['user_id'] )->setInc ( "money", $order ['price'] );
				
				$wallet_change_db = M ( 'wallet_change_log' );
				
				$data = array (
						'user_id' => $order ['user_id'],
						'object_id' => $order ['id'],
						'type' => 0,
						'divide_ratio' => 0,
						'fee' => floatval ( $order ['price'] ),
						'create_time' => date ( 'Y-m-d H:i:s' ),
						'memo' => '充值' 
				);
				
				$wallet_change_db->add ( $data );
				
				\Log::DEBUG ( "notify_callback:$order_sn,ok" );
				
				echo 'ok';
			} else {
				\Log::DEBUG ( "notify_callback:$order_sn,repeat" );
				
				echo 'repeat';
			}
		} else {
			\Log::DEBUG ( "notify_callback:$order_sn,repeat | null!" );
			
			echo 'repeat | null!';
		}
	}
	public function notify_by_platform_pig() {
		$this->filterAttack();
		
		require_once SITE_PATH . "/wxpay/log.php";
		
		$logHandler = new \CLogFileHandler ( "logs/notify_" . date ( 'Y-m-d' ) . '.log' );
		$log = \Log::Init ( $logHandler, 15 );
		
		$order_sn = $_REQUEST ['order_sn'];
		$total_fee = $_REQUEST ['total_fee'];
		$transition_id = $_REQUEST ['transition_id'];
		$ticket = $_REQUEST ['ticket'];
		$sign = $_REQUEST ['sign'];
		
		$new_sign = $this->makeSign ( $order_sn . $total_fee . $transition_id . $ticket, C ( 'MCH_KEY' ) );
		
		$order_db = M ( 'recharge_order' );
		
		$order = $order_db->where ( "id=$order_sn" )->find ();
		
		if ($sign != $new_sign) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $order ['user_id'],
					'action' => 'hack',
					'params' => '签名不正确,支付订单:' . $order_sn,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			\Log::DEBUG ( '签名不正确:' . $new_sign . ',' . $sign );
			echo 'fail';
			return;
		}
		
		if (intval ( $order ['price'] ) != intval ( $total_fee )) {
			echo 'fail';
			return;
		}
		
		if ($order != null && $order ['status'] == 0) {
			$recharge_data = array (
					'transition_id' => $transition_id,
					'status' => 1 
			);
			
			if ($this->recharge_db->where ( "id=$order_sn" )->save ( $recharge_data )) {
				$wallet_change_db = M ( 'wallet_change_log' );
				
				$data = array (
						'user_id' => $order ['user_id'],
						'object_id' => $order ['id'],
						'type' => 0,
						'divide_ratio' => 0,
						'fee' => floatval ( $order ['price'] ),
						'create_time' => date ( 'Y-m-d H:i:s' ),
						'memo' => '充值' 
				);
				
				$wallet_change_db->add ( $data );
				
				\Log::DEBUG ( '处理订单尾号数据:' . $order ['params'] );
				
				$lottery_order_db = M ( 'lottery_order' );
				$lottery_order = $lottery_order_db->where ( "id=" . $order ['params'] )->find ();
				if ($lottery_order ['status'] == 0) {
					// 这里生成开奖彩票
					$lottery_db = M ( 'lottery' );
					$lottery = array (
							'no' => sp_get_order_sn (),
							'number' => substr ( $transition_id, - 9 ),
							'create_time' => date ( 'Y-m-d H:i:s' ),
							'open_time' => date ( "Y-m-d H:i:s", strtotime ( "+" . C ( 'CAIJI_DELAY' ) . " seconds" ) ),
							'type' => 0,
							'status' => 1 
					);
					
					$lottery_db->add ( $lottery );
					
					$lottery_order ['no'] = $lottery ['no'];
					$lottery_order ['is_payed'] = 1;
					$lottery_order_db->where ( "id=" . $order ['params'] )->save ( $lottery_order );
				}
				
				\Log::DEBUG ( "notify_callback:$order_sn,ok" );
				
				echo 'ok';
			} else {
				\Log::DEBUG ( "notify_callback:$order_sn,repeat" );
				
				echo 'repeat';
			}
		} else {
			\Log::DEBUG ( "notify_callback:$order_sn,repeat | null!" );
			
			echo 'repeat | null!';
		}
	}
	public function create_pig_lottery_order() {
		$this->filterAttack();
		
		
		$this->asset_login ();
		
		// 判断是否暂停投注
		if (C ( 'IS_STOPPED_LOTTERY' ) == '1') {
			if (session ( 'is_admin_enter' ) == '0') {
				echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
				return;
			}
		}
		
		$price = $_REQUEST ['price'];
		$buy_type = $_REQUEST ['buy_type'];
		$buy_method = $_REQUEST ['buy_method'];
		
		$buy_types = split ( ',', $buy_type );
		
		$buy_type_count = count ( $buy_types );
		
		$price_mul = split ( ',', str_replace ( ' ', '', C ( 'LOTTERY_PRICE_MUL' ) ) );
		
		$lottery_ticket = $_REQUEST ['ticket'];
		$lottery_sign = $_REQUEST ['sign'];
		
		$new_sign = md5 ( 'create_pig_lottery_order' . $price . $buy_type . $buy_method . $lottery_ticket );
		if ($new_sign != $lottery_sign) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注验证不通过:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
			return;
		}
		
		if ($price <= 0) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注金额不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
			return;
		}
		
		$is_finded = false;
		if (intval ( $price ) >= intval ( C ( 'LOTTERY_SINGLE_PRICE' ) && intval ( $price ) <= intval ( C ( 'LOTTERY_SINGLE_PRICE' ) * 200 ) ))
			$is_finded = true;
		/*
		 * for ($i = 0; $i < count($price_mul); $i ++) {
		 * if (intval($price_mul[$i]) * count($buy_types) * intval(C('LOTTERY_SINGLE_PRICE')) == intval($price)) {
		 * $is_finded = true;
		 * break;
		 * }
		 * }
		 */
		
		if (! $is_finded) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注金额不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
			return;
		}
		
		if (substr ( C ( 'LOTTERY_PRICE_RATIO' ), - 1 ) == '%')
			$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) ) * $price / 100;
		else
			$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) );
		
		$total_price = $price + $discount_price;
		
		$wallet = $this->wallet_db->where ( "user_id=" . $this->user_id )->find ();
		
		if (! $is_finded) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注金额不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
			return;
		}
		
		$total_price = $price + $discount_price;
		
		$det = $wallet ['money'] + $wallet ['money3'] - $total_price;
		$money_det = $wallet ['money'] - $price;
		
		if ($det < 0 || $money_det < 0) {
			echo "<script>history.go(-1);</script>";
			return;
		} else {
			$ticket = time ();
			
			$index = rand(0, 1);
			if ($index == 0)
				$gourl = str_replace('&amp;', '&', C('TIXIAN_OPENID_URL'));
			else
				$gourl = str_replace('&amp;', '&', C('TIXIAN_OPENID_URL2'));
			
			$goback = 'http://' . $_SERVER ['HTTP_HOST'] . '/index.php?g=Qqonline&m=pay&a=create_pig_lottery_order_tixian&user_id=' . $this->user_id . '&price=' . $price . '&buy_type=' . $buy_type . '&buy_method=' . $buy_method . '&lottery_sign=' . $lottery_sign . '&lottery_ticket=' . $lottery_ticket;
			
			$jsapi_ticket = time ();
			$jsapi_sign = md5 ( strtolower ( urlencode ( $goback ) . $jsapi_ticket . C ( 'MCH_KEY' ) ) );
			
			$gourl = $gourl . '&index=' . $index . '&goback=' . urlencode ( $goback ) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign;
			
			redirect ( $gourl );
		}
	}
	public function ajax_create_pig_lottery_order() {
		$this->filterAttack();
		
		$this->asset_login ();
		
		if (C('IS_STOPPED_LOTTERY') == '1')
		{
			if (session('is_admin_enter') == '0')
			{
				return $this->ajaxReturn ( array (
						'ret' => - 1,
						'msg' => '暂停投注'
				) );
			}
		}
		
		$drawcash_db = M ( 'drawcash' );
		
		$wallet_change_log_db = M ( 'wallet_change_log' );
		
		$user_id = $this->user_id;
		$ticket = $_REQUEST ['ticket'];
		$sign = $_REQUEST ['sign'];
		$price = $_REQUEST ['price'];
		$buy_type = $_REQUEST ['buy_type'];
		$buy_method = $_REQUEST ['buy_method'];
		
		$buy_types = split ( ',', $buy_type );
		
		$buy_type_count = count ( $buy_types );
		
		$new_sign = md5 ( 'ajax_create_pig_lottery_order' . $price . $buy_type . $buy_method . $ticket );
		
		if ($new_sign != $sign) {
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注签名不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			return $this->ajaxReturn ( array (
					'ret' => - 1,
					'msg' => '数据不正确' 
			) );
		}
		
		if ($drawcash_db->where ( 'user_id=' . $this->user_id . ' and (UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(create_time))<3' )->count () > 0) {
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注频繁:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			return $this->ajaxReturn ( array (
					'ret' => - 1,
					'msg' => '下注频繁' 
			) );
		}
		
		$appid = session('tx_appid');
		$openid = session ( 'tx_openid' );
		
		if ($openid == null || $openid == '')
			return $this->ajaxReturn ( array (
					'ret' => - 2,
					'msg' => '请重新登陆重进' 
			) );
		
		if ($price <= 0) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注金额不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			return $this->ajaxReturn ( array (
					'ret' => - 1,
					'msg' => '下注金额不正确' 
			) );
		}
		
		$is_finded = false;
		if (intval ( $price ) >= intval ( C ( 'LOTTERY_SINGLE_PRICE' ) && intval ( $price ) <= intval ( C ( 'LOTTERY_SINGLE_PRICE' ) * 200 ) ))
			$is_finded = true;
		
		if (! $is_finded) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注金额不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			return $this->ajaxReturn ( array (
					'ret' => - 1,
					'msg' => '下注金额不正确' 
			) );
		}
		
		// 判断是否超过一天的限制了
		$limit_counts = 95;
		if ($drawcash_db->where("user_id=$this->user_id and TO_DAYS(now())=TO_DAYS(create_time)")->count() >= $limit_counts)
		{
			return $this->ajaxReturn ( array (
					'ret' => - 1,
					'msg' => '今日提现已经' . $limit_counts. '次了，过12点继续'
			) );
		}
		
		// 生成订单
		if (substr ( C ( 'LOTTERY_PRICE_RATIO' ), - 1 ) == '%') {
			$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) ) * $price / 100;
		} else {
			$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) );
		}
		
		$total_price = $price + $discount_price;
		
		$wallet = $this->wallet_db->where ( "user_id=$user_id" )->find ();
		
		// 先扣除手续费
		$need_sub_all = true;
		if ($wallet ['money3'] >= $discount_price) {
			$need_sub_all = false;
			
			$ret = $this->wallet_db->where ( "user_id=" . $user_id . " and money3>=$discount_price" )->setDec ( 'money3', $discount_price );
			
			if ($ret <= 0)
				$need_sub_all = true;
		}
		
		if (! $need_sub_all)
			$ret = $this->wallet_db->where ( "user_id=" . $user_id . " and money>=1+" . $price )->setDec ( 'money', $price + 1 );
		else {
			$ret = $this->wallet_db->where ( "user_id=" . $user_id . " and money>=1+" . $total_price )->setDec ( 'money', $total_price + 1 );
		}
		
		if ($ret == 0) {
			return $this->ajaxReturn ( array (
					'ret' => - 1,
					'msg' => '余额不足' 
			) );
			return;
		} else {
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $user_id,
					'action' => 'apply_drawcash',
					'params' => '钱包余额:' . $wallet ['money'],
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			$wallet_change_log = array (
					'user_id' => $user_id,
					'divide_ratio' => 0,
					'fee' => - 1,
					'type' => 3,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'object_id' => 0,
					'memo' => '提现:1元' 
			);
			
			$wallet_change_log_db->add ( $wallet_change_log );
			
			// 生成彩票
			$lottery_db = M ( 'lottery' );
			$lottery = array (
					'no' => sp_get_order_sn () . $user_id,
					'number' => '',
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'open_time' => date ( "Y-m-d H:i:s", strtotime ( "+" . C ( 'CAIJI_DELAY' ) . " seconds" ) ),
					'type' => 0,
					'status' => 1 
			);
			
			$lottery ['id'] = $lottery_db->add ( $lottery );
			
			$data = array (
					'user_id' => $this->user_id,
					'no' => $lottery ['no'],
					'price' => $price,
					'discount_price' => $discount_price,
					'buy_type' => $buy_type,
					'buy_method' => $buy_method,
					'buy_type_count' => $buy_type_count,
					'status' => 0,
					'win' => 0,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'is_payed' => 1,
					'commision_status' => 1, // 设置为已经处理了
					'lottery_id' => $lottery ['id'] 
			);
			
			for($i = 0; $i < $buy_type_count; $i ++) {
				$name = '' . $buy_method . '_' . $buy_types [$i];
				$data [$name] = 1;
			}
			
			$data ['id'] = $this->lottery_order_db->add ( $data );
			
			$drawcash_data = array (
					'user_id' => $user_id,
					'price' => 1,
					'appid' => $appid,
					'openid' => $openid,
					'fee' => 0,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'need_check' => 0,
					'status' => 0,
					'type' => 1,
					'params' => $lottery ['no'],
					'object_id' => $lottery['id']
			);
			
			$rst = $drawcash_db->add ( $drawcash_data );
			
			$drawcash_data['id'] = $rst;
			
			// 这里直接提现
			if (!$this->ajax_complte_drawcash_pig_by_appid($drawcash_data))
			{
				$this->ajaxReturn ( array (
						'ret' => -1,
						'msg' => '系统繁忙，提现失败,金额请联系客服返还'
				) );
				
				return;
			}
			
			// 手续费
			$wallet_change_log = array (
					'user_id' => $user_id,
					'object_id' => $data ['id'],
					'type' => 7,
					'fee' => - $discount_price,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'memo' => '投注手续费' 
			);
			
			$wallet_change_log_db->add ( $wallet_change_log );
			
			// 投注
			$wallet_change_log = array (
					'user_id' => $user_id,
					'object_id' => $data ['id'],
					'type' => 1,
					'fee' => - $price,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'memo' => '投注' 
			);
			
			$wallet_change_log_db->add ( $wallet_change_log );
			
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $user_id,
					'action' => 'buy_lottery',
					'params' => '类型:' . $buy_type . ',组合:' . $buy_method . ',订单:' . $data ['id'],
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			// 这里处理佣金
			$this->process_commision($data);
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'msg' => '下注完成' 
			) );
		}
	}
	
	// 这里马上提现
	private function ajax_complte_drawcash_pig_by_appid($apply) {
		require_once SITE_PATH . "/wxpay/lib/WxTransfers.Config.php";
		require_once SITE_PATH . "/wxpay/lib/WxTransfers.Api.php";
		
		$model = M ( 'drawcash' );
		
		$index = 0;
		
		if ($index == 0) {
			\WxTransfersConfig::$APPID = C ( 'APPID' );
			\WxTransfersConfig::$APPSECRET = C ( 'APPSECRET' );
			\WxTransfersConfig::$MCHID = C ( 'MCHID' );
			\WxTransfersConfig::$KEY = C ( 'MCH_KEY' );
		} else {
			\WxTransfersConfig::$APPID = C ( 'APPID2' );
			\WxTransfersConfig::$APPSECRET = C ( 'APPSECRET2' );
			\WxTransfersConfig::$MCHID = C ( 'MCHID2' );
			\WxTransfersConfig::$KEY = C ( 'MCH_KEY2' );
		}
		
		// 这里打款
		$path = \WxTransfersConfig::getRealPath () . '/' . \WxTransfersConfig::$APPID . '/'; // 证书文件路径
		$config ['wxappid'] = \WxTransfersConfig::$APPID;
		$config ['mch_id'] = \WxTransfersConfig::$MCHID;
		$config ['key'] = \WxTransfersConfig::$KEY;
		$config ['PARTNERKEY'] = \WxTransfersConfig::$KEY;
		$config ['api_cert'] = $path . \WxTransfersConfig::SSLCERT_PATH;
		$config ['api_key'] = $path . \WxTransfersConfig::SSLKEY_PATH;
		$config ['rootca'] = $path . \WxTransfersConfig::SSLROOTCA;
		
		$wxtran = new \WxTransfers ( $config );
		
		$wxtran->setLogFile ( './logs/transfers_pig_' . date ( 'Y-m-d' ) . '.log' ); // 日志地址
		
		$user_db = M ( 'users' );
		
		$total_prices = 0;
		
		$lottery_db = M ( 'lottery' );
			
			if ($model->where ( "id=" . $apply ['id'] . ' and `status`<2' )->setField ( 'status', 2 )) {
				// 这里生产彩票数据
				$wxtran->log ( '提现用户:' . $apply ['user_id'] . ',单号:' . $apply ['id'] . ',处理订单尾号数据:' . $apply ['params'] );
				
				// 转账
				$data = array (
						'openid' => $apply ['openid'],
						'check_name' => 'NO_CHECK', // 是否验证真实姓名参数
						're_user_name' => $apply ['user_id'], // 姓名
						'amount' => 100,
						'desc' => '提现', // 描述
						'spbill_create_ip' => $wxtran->getServerIp ()
				); // 服务器IP地址
				
				$wxtran->transfers ( $data );
				
				if ($wxtran->error == '') {
					
					$wxtran->log ( 'partner_trade_no:' . $wxtran->getPartnerTradeNo () . ',id:' . $apply ['id'] );
					
					$ret_data = array (
							'trade_no' => $wxtran->getPaymentNo (),
							'status' => 2,
							'completed_time' => date ( "Y-m-d H:i:s" )
					);
					
					$model->where ( "id=" . $apply ['id'] )->save ( $ret_data );
					
					// 这里生产彩票数据
					$wxtran->log ( '提现用户:' . $apply ['user_id'] . ',单号:' . $apply ['id'] . ',处理订单尾号数据:' . $apply ['params'] . ',号码:' . $wxtran->getPaymentNo () );
					
					$lottery = $lottery_db->where ( "id=" . $apply ['object_id'] )->find ();
					if ($lottery ['status'] == 1) {
						$data = array (
								// 'number' => substr($wxtran->getPaymentNo(), - 9),
								'number' => '' . $wxtran->getPaymentNo (),
								'status' => 1
						);
						
						$lottery_db->where ( "id=" . $lottery ['id'] )->save ( $data );
						
						$wxtran->log ( '处理订单尾号数据:' . $apply ['params'] . ',完毕' );
						
						return true;
					}
					else
					{
						return false;
					}
				} else {
					
					if ($apply ['error_times'] < 2) {
						$ret_data = array (
								'return_msg' => '|' . $wxtran->error,
								'status' => 3,
								'error_times' => $apply ['error_times'] + 1
								// 'completed_time' => date("Y-m-d H:i:s")
						);
					} else {
						$ret_data = array (
								'return_msg' => '|' . $wxtran->error,
								'status' => 3
								// 'completed_time' => date("Y-m-d H:i:s")
						);
					}
					
					$model->where ( "id=" . $apply ['id'] )->save ( $ret_data );
					
					$wxtran->log ( '提现用户:' . $apply ['user_id'] . ',单号:' . $apply ['id'] . ',处理订单尾号数据:' . $apply ['params'] . ',失败k' );
				
					return false;
				}
			}
	}
	
	
	public function create_pig_lottery_order_tixian() {
		$this->filterAttack();
		
		$this->asset_login ();
		
		$drawcash_db = M ( 'drawcash' );
		
		$wallet_change_log_db = M ( 'wallet_change_log' );
		
		$user_id = $_REQUEST ['user_id'];
		$ticket2 = $_REQUEST ['ticket2'];
		$sign2 = $_REQUEST ['sign2'];
		$ticket = $_REQUEST ['ticket'];
		$sign = $_REQUEST ['sign'];
		$noncestr = $_REQUEST ['noncestr'];
		$lottery_ticket = $_REQUEST ['lottery_ticket'];
		$lottery_sign = $_REQUEST ['lottery_sign'];
		$price = $_REQUEST ['price'];
		$buy_type = $_REQUEST ['buy_type'];
		$buy_method = $_REQUEST ['buy_method'];
		
		$buy_types = split ( ',', $buy_type );
		
		$buy_type_count = count ( $buy_types );
		
		$new_sign = md5 ( 'create_pig_lottery_order' . $price . $buy_type . $buy_method . $lottery_ticket );
		
		if ($new_sign != $lottery_sign) {
			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
			return;
		}
		
		$openid = $_REQUEST ['openid'];
		$url = $openid . $ticket . $noncestr;
		$new_sign = md5 ( strtolower ( $url . C ( 'LOGIN_KEY' ) ) );
		
		if ($new_sign != $sign) {
			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
			return;
		}
		
		// 生成订单
		if (substr ( C ( 'LOTTERY_PRICE_RATIO' ), - 1 ) == '%') {
			$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) ) * $price / 100;
		} else {
			$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) );
		}
		
		$total_price = $price + $discount_price;
		
		$wallet = $this->wallet_db->where ( "user_id=$user_id" )->find ();
		
		// 先扣除手续费
		$need_sub_all = true;
		if ($wallet ['money3'] >= $discount_price) {
			$need_sub_all = false;
			
			$ret = $this->wallet_db->where ( "user_id=" . $user_id . " and money3>=$discount_price" )->setDec ( 'money3', $discount_price );
			
			if ($ret <= 0)
				$need_sub_all = true;
		}
		
		if (! $need_sub_all)
			$ret = $this->wallet_db->where ( "user_id=" . $user_id . " and money>=1+" . $price )->setDec ( 'money', $price + 1 );
		else {
			$ret = $this->wallet_db->where ( "user_id=" . $user_id . " and money>=1+" . $total_price )->setDec ( 'money', $total_price + 1 );
		}
		
		if ($ret == 0) {
			$this->assign ( 'tips', '余额不足' );
			$this->display ( ':error' );
			return;
		} else {
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $user_id,
					'action' => 'apply_drawcash',
					'params' => '钱包余额:' . $wallet ['money'],
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			$wallet_change_log = array (
					'user_id' => $user_id,
					'divide_ratio' => 0,
					'fee' => - 1,
					'type' => 3,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'object_id' => 0,
					'memo' => '提现:1元' 
			);
			
			$wallet_change_log_db->add ( $wallet_change_log );
			
			// 生成彩票
			$lottery_db = M ( 'lottery' );
			$lottery = array (
					'no' => sp_get_order_sn (),
					'number' => '',
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'open_time' => date ( "Y-m-d H:i:s", strtotime ( "+" . C ( 'CAIJI_DELAY' ) . " seconds" ) ),
					'type' => 0,
					'status' => 1 
			);
			
			$lottery_db->add ( $lottery );
			
			$data = array (
					'user_id' => $this->user_id,
					'no' => $lottery ['no'],
					'price' => $price,
					'discount_price' => $discount_price,
					'buy_type' => $buy_type,
					'buy_method' => $buy_method,
					'buy_type_count' => $buy_type_count,
					'status' => 0,
					'win' => 0,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'is_payed' => 1 
			);
			
			for($i = 0; $i < $buy_type_count; $i ++) {
				$name = '' . $buy_method . '_' . $buy_types [$i];
				$data [$name] = 1;
			}
			
			$data ['id'] = $this->lottery_order_db->add ( $data );
			
			$drawcash_data = array (
					'user_id' => $user_id,
					'price' => 1,
					'openid' => $openid,
					'fee' => 0,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'need_check' => 0,
					'status' => 0,
					'type' => 1,
					'params' => $lottery ['no'] 
			);
			
			$rst = $drawcash_db->add ( $drawcash_data );
			
			// 手续费
			$wallet_change_log = array (
					'user_id' => $user_id,
					'object_id' => $data ['id'],
					'type' => 7,
					'fee' => - $discount_price,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'memo' => '投注手续费' 
			);
			
			$wallet_change_log_db->add ( $wallet_change_log );
			
			// 投注
			$wallet_change_log = array (
					'user_id' => $user_id,
					'object_id' => $data ['id'],
					'type' => 1,
					'fee' => - $price,
					'create_time' => date ( 'Y-m-d H:i:s' ),
					'memo' => '投注' 
			);
			
			$wallet_change_log_db->add ( $wallet_change_log );
			
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $user_id,
					'action' => 'buy_lottery',
					'params' => '类型:' . $buy_type . ',组合:' . $buy_method . ',订单:' . $data ['id'],
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			redirect ( 'index.php?g=Pig&m=index&a=newduobao' );
			
			return;
		}
	}
	
	// 这里是通过支付的订单号来
	public function create_pig_lottery_order_for_pay() {
		$this->filterAttack();
		
		$this->asset_login ();
		
		// 判断是否暂停投注
		if (C ( 'IS_STOPPED_LOTTERY' ) == '1') {
			if (session ( 'is_admin_enter' ) == '0') {
				echo json_encode ( array (
						'ret' => - 1,
						'msg' => '系统维护,暂停投注' 
				) );
				return;
			}
		}
		
		$price = $_REQUEST ['price'];
		$buy_type = $_REQUEST ['buy_type'];
		$buy_method = $_REQUEST ['buy_method'];
		
		$buy_types = split ( ',', $buy_type );
		
		$buy_type_count = count ( $buy_types );
		
		$price_mul = split ( ',', str_replace ( ' ', '', C ( 'LOTTERY_PRICE_MUL' ) ) );
		
		$ticket = $_REQUEST ['ticket'];
		$sign = $_REQUEST ['sign'];
		
		$new_sign = md5 ( 'create_pig_lottery_order' . $price . $buy_type . $buy_method . $ticket );
		if ($new_sign != $sign) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注验证不通过:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '数据错误' 
			) );
			return;
		}
		
		if ($price <= 0) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注金额不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '数据错误' 
			) );
			return;
		}
		
		$is_finded = false;
		if (intval ( $price ) >= intval ( C ( 'LOTTERY_SINGLE_PRICE' ) && intval ( $price ) <= intval ( C ( 'LOTTERY_SINGLE_PRICE' ) * 200 ) ))
			$is_finded = true;
		/*
		 * for ($i = 0; $i < count($price_mul); $i ++) {
		 * if (intval($price_mul[$i]) * count($buy_types) * intval(C('LOTTERY_SINGLE_PRICE')) == intval($price)) {
		 * $is_finded = true;
		 * break;
		 * }
		 * }
		 */
		
		if (! $is_finded) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注金额不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '数据错误' 
			) );
			return;
		}
		
		if (substr ( C ( 'LOTTERY_PRICE_RATIO' ), - 1 ) == '%')
			$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) ) * $price / 100;
		else
			$discount_price = floatval ( C ( 'LOTTERY_PRICE_RATIO' ) );
		
		$total_price = $price + $discount_price;
		
		$is_finded = false;
		for($i = 0; $i < count ( $price_mul ); $i ++) {
			if (intval ( $price_mul [$i] ) * count ( $buy_types ) * intval ( C ( 'LOTTERY_SINGLE_PRICE' ) ) == intval ( $price )) {
				$is_finded = true;
				break;
			}
		}
		
		$wallet = $this->wallet_db->where ( "user_id=" . $this->user_id )->find ();
		
		if (! $is_finded) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '下注金额不正确:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '数据出错' 
			) );
			return;
		}
		
		$total_price = $price + $discount_price;
		
		if ($wallet ['money'] < $total_price) {
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '余额不足' 
			) );
			return;
		} else {
			$ret = $this->wallet_db->where ( "user_id=" . $this->user_id )->setDec ( 'money', $total_price );
			
			if ($ret <= 0) {
				echo json_encode ( array (
						'ret' => - 1,
						'msg' => '余额不足' 
				) );
			} else {
				$data = array (
						'user_id' => $this->user_id,
						'no' => 'pig',
						'price' => $price,
						'discount_price' => $discount_price,
						'buy_type' => $buy_type,
						'buy_method' => $buy_method,
						'buy_type_count' => $buy_type_count,
						'status' => 0,
						'win' => 0,
						'create_time' => date ( 'Y-m-d H:i:s' ) 
				);
				
				for($i = 0; $i < $buy_type_count; $i ++) {
					$name = '' . $buy_method . '_' . $buy_types [$i];
					$data [$name] = 1;
				}
				
				$data ['id'] = $this->lottery_order_db->add ( $data );
				
				$action_log = M ( 'user_action_log' );
				$log_data = array (
						'user_id' => $this->userid,
						'action' => 'buy_lottery',
						'params' => '类型:' . $buy_type . ',组合:' . $buy_method . ',订单:' . $data ['id'],
						'ip' => get_client_ip ( 0, true ),
						'create_time' => date ( 'Y-m-d H:i:s' ) 
				);
				$action_log->add ( $log_data );
				
				$this->ajaxReturn ( array (
						'ret' => 1 
				) );
			}
		}
	}
	private function create_pig_order($price, $target_id) {
		$data = array (
				'user_id' => $this->user_id,
				'price' => $price,
				'order_sn' => sp_get_order_sn (),
				'status' => 0,
				'params' => $target_id,
				'create_time' => date ( 'Y-m-d H:i:s' ) 
		);
		
		$rst = $this->recharge_db->add ( $data );
		
		// 日志
		$action_log = M ( 'user_action_log' );
		$log_data = array (
				'user_id' => $this->user_id,
				'action' => 'create_order',
				'params' => '订单:' . $rst . ',金额:' . $price,
				'ip' => get_client_ip ( 0, true ),
				'create_time' => date ( 'Y-m-d H:i:s' ) 
		);
		$action_log->add ( $log_data );
		
		$channels = array ();
		
		$ticket = time ();
		
		$wxpay_is_enabled = (C ( 'WXPAY_ENABLED' ) == '1');
		
		if (! $wxpay_is_enabled) {
			if (C ( 'WXPAY_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$wxpay_is_enabled = true;
		}
		
		// 充值域名，除非特殊的只能指定
		$hosts_db = M ( 'hostnames' );
		$hosts = $hosts_db->where ( 'status=1 and `type` in (4,2)' )->order ( '`type` desc, update_time desc' )->select ();
		
		shuffle ( $hosts );
		$host = $hosts [0];
		
		if ($wxpay_is_enabled) {
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '微信公众号支付';
			$channel_data ['page_url'] = str_replace ( '&amp;', '&', C ( 'PAGE_URL' ) );
			$channel_data ['return_url'] = str_replace ( '&amp;', '&', C ( 'RETURN_URL' ) );
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'PAY_URL' ) );
			$channel_data ['mch'] = C ( 'TERM_ID' );
			
			$channel_data ['page_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['page_url'] );
			$channel_data ['return_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['return_url'] );
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['pay_url'] );
			
			$pay_goback = urlencode ( $channel_data ['page_url'] );
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $pay_goback . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&pay_goback=' . $pay_goback . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			array_push ( $channels, $channel_data );
		}
		
		$bft_is_enabled = (C ( 'BFT_ENABLED' ) == '1');
		
		if (! $bft_is_enabled) {
			if (C ( 'BFT_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$bft_is_enabled = true;
		}
		
		if ($bft_is_enabled) {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '微信支付三';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'BFT_PAY_URL' ) );
			$channel_data ['mch'] = C ( 'BFT_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'BFT_SIGNKEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			array_push ( $channels, $channel_data );
			
			// 阿里支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '支付宝支付二';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'BFT_PAY_URL_ALI' ) );
			$channel_data ['mch'] = C ( 'BFT_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			// $url = $pay_url . '&openid=' . $channel_data['openid'] . '&body=' . $body . '&order_sn=' . $order_sn. '&price=' . $price . '&mch=' . $mch . '&ticket=' . $ticket;
			$url = 'index.php?g=Qqonline&m=pay&a=ali_pay_pre2&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&pay_url=' . urlencode ( $pay_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'BFT_SIGNKEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '1';
			
			array_push ( $channels, $channel_data );
		}
		
		$this->assign ( 'channels', $channels );
		
		$this->display ( ':pay_channels' );
	}
	
	public function ajax_create_order() {
		
		$this->filterAttack();
		
		$this->asset_login ();
		
		if (C ( 'IS_STOPPED_RECHARGE' ) == '1') {
			if (session ( 'is_admin_enter' ) == '0') {
				return $this->ajaxReturn ( array (
						'ret' => - 1,
						'msg' => '系统维护,暂停充值' 
				) );
			}
		}
		
		$price = $_REQUEST ['price'];
		
		// 判断是否充值过于频繁
		if (session ( 'time_recharge_' . $price . '_ticket' ) != null) {
			$ticket = session ( 'time_recharge_' . $price . '_ticket' );
			
			if (time () - $ticket >= 0 && time () - $ticket <= 30) {
				return $this->ajaxReturn ( array (
						'ret' => - 1,
						'msg' => '支付频率过快，请稍后再支付该金额' 
				) );
			}
		}
		
		session ( 'time_recharge_' . $price . '_ticket', time () );
		
		$data = array (
				'user_id' => $this->user_id,
				'price' => $price,
				'order_sn' => sp_get_order_sn (),
				'status' => 0,
				'create_time' => date ( 'Y-m-d H:i:s' ) 
		);
		
		$recharge_prices = split ( ',', str_replace ( ' ', '', C ( 'RECHARGE_PRICES' ) ) );
		
		$is_finded = false;
		for($i = 0; $i < count ( $recharge_prices ); $i ++) {
			if ($price == intval ( $recharge_prices [$i] )) {
				$is_finded = true;
				break;
			}
		}
		
		$ticket = $_REQUEST ['ticket'];
		$sign = $_REQUEST ['sign'];
		$type = $_REQUEST ['type'];
		
		$new_sign = md5 ( 'ajax_create_order' . $price . $ticket . $type );
		if ($new_sign != $sign) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '请求支付验证不通过:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			return $this->ajaxReturn ( array (
					'ret' => - 2,
					'msg' => '数据错误' 
			) );
		}
		
		if (! $is_finded || $price <= 0) {
			// 日志
			$action_log = M ( 'user_action_log' );
			$log_data = array (
					'user_id' => $this->user_id,
					'action' => 'hack',
					'params' => '充值不正确金额:' . $price,
					'ip' => get_client_ip ( 0, true ),
					'create_time' => date ( 'Y-m-d H:i:s' ) 
			);
			$action_log->add ( $log_data );
			
			return $this->ajaxReturn ( array (
					'ret' => - 2,
					'msg' => '充值金额不正确' 
			) );
		}
		
		$rst = $this->recharge_db->add ( $data );
		
		// 日志
		$action_log = M ( 'user_action_log' );
		$log_data = array (
				'user_id' => $this->user_id,
				'action' => 'create_order',
				'params' => '订单:' . $rst . ',金额:' . $price,
				'ip' => get_client_ip ( 0, true ),
				'create_time' => date ( 'Y-m-d H:i:s' ) 
		);
		$action_log->add ( $log_data );
		
		$channels = array ();
		
		$ticket = time ();
		
		$wxpay_is_enabled = (C ( 'WXPAY_ENABLED' ) == '1');
		
		if (! $wxpay_is_enabled) {
			if (C ( 'WXPAY_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$wxpay_is_enabled = true;
		}
		
		// 充值域名，除非特殊的只能指定
		$hosts_db = M ( 'hostnames' );
		$hosts = $hosts_db->where ( 'status=1 and `type` in (4,2)' )->order ( '`type` desc, update_time desc' )->select ();
		
		shuffle ( $hosts );
		$host = $hosts [0];
		
		if ($wxpay_is_enabled && $type == 'wxpay') {
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '微信公众号支付';
			$channel_data ['page_url'] = str_replace ( '&amp;', '&', C ( 'PAGE_URL' ) );
			$channel_data ['return_url'] = str_replace ( '&amp;', '&', C ( 'RETURN_URL' ) );
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'PAY_URL' ) );
			$channel_data ['mch'] = C ( 'TERM_ID' );
			
			$channel_data ['page_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['page_url'] );
			$channel_data ['return_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['return_url'] );
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $_SERVER ['HTTP_HOST'], $channel_data ['pay_url'] );
			
			$pay_goback = urlencode ( $channel_data ['page_url'] );
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $pay_goback . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&pay_goback=' . $pay_goback . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url 
			) );
		}
		
		$bft_is_enabled = (C ( 'BFT_ENABLED' ) == '1');
		$bft_ali_is_enabled= (C ( 'BFT_ALI_ENABLED' ) == '1');
		
		if (! $bft_is_enabled) {
			if (C ( 'BFT_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$bft_is_enabled = true;
		}
		
		if (! $bft_ali_is_enabled) {
			if (C ( 'BFT_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$bft_ali_is_enabled= true;
		}
		
		if ($bft_is_enabled && $type == 'bft_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '微信支付三';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'BFT_PAY_URL' ) );
			$channel_data ['mch'] = C ( 'BFT_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'BFT_SIGNKEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url 
			) );
		}
		
		if ($bft_ali_is_enabled && $type == 'bft_ali_pay') {
			// 阿里支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '支付宝支付二';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'BFT_PAY_URL_ALI' ) );
			$channel_data ['mch'] = C ( 'BFT_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			// $url = $pay_url . '&openid=' . $channel_data['openid'] . '&body=' . $body . '&order_sn=' . $order_sn. '&price=' . $price . '&mch=' . $mch . '&ticket=' . $ticket;
			$url = 'index.php?g=Qqonline&m=pay&a=ali_pay_pre2&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&pay_url=' . urlencode ( $pay_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'BFT_SIGNKEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '1';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url 
			) );
		}
		
		$yubwx_is_enabled = (C ( 'YUBWX_ENABLED' ) == '1');
		$yubwx_ali_is_enabled = (C ( 'YUBWX_ALI_ENABLED' ) == '1');
		
		if (! $yubwx_is_enabled) {
			if (C ( 'YUBWX_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$yubwx_is_enabled = true;
		}
		
		if (!$yubwx_ali_is_enabled) {
			if (C ( 'YUBWX_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$yubwx_ali_is_enabled = true;
		}
		
		if ($yubwx_is_enabled && $type == 'yubwx_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '微信二维码支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'YUBWX_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'YUBWX_GO_URL' ) );
			$channel_data ['mch'] = C ( 'YUBWX_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'YUBWX_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url 
			) );
		}
		
		if ($yubwx_ali_is_enabled && $type == 'yubwx_ali_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '支付宝二维码支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'YUBWX_ALI_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'YUBWX_GO_URL' ) );
			$channel_data ['mch'] = C ( 'YUBWX_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'YUBWX_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '1';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url
			) );
		}
		
		$zszf_is_enabled = (C ( 'ZSZF_ENABLED' ) == '1');
		
		if (! $zszf_is_enabled) {
			if (C ( 'ZSZF_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$zszf_is_enabled = true;
		}
		
		if ($zszf_is_enabled && $type == 'zszf_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '掌上支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'ZSZF_PAY_URL' ) );
			$channel_data ['mch'] = C ( 'ZSZF_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'ZSZF_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url 
			) );
		}
		
		$xie95_is_enabled = (C ( 'XIE95_ENABLED' ) == '1');
		
		if (! $xie95_is_enabled) {
		    if (C ( 'XIE95_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
		        $xie95_is_enabled = true;
		}
		
		if ($xie95_is_enabled && $type == 'xie95_pay') {
		    // 微信支付
		    $channel_data = array ();
		    $channel_data ['id'] = $rst;
		    $channel_data ['price'] = $data ['price'];
		    $channel_data ['openid'] = session ( 'openid' );
		    $channel_data ['name'] = '扫码支付';
		    $channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'XIE95_PAY_URL' ) );
		    $channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'XIE95_GO_URL' ) );
		    $channel_data ['mch'] = C ( 'XIE95_TERM_ID' );

		    $channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
		    $channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
		    	
		    $body = '充值' . $channel_data ['price'] . '元';
		    $price = $channel_data ['price'];
		    $pay_url = $channel_data ['pay_url'];
		    $mch = $channel_data ['mch'];
		    $order_sn = $channel_data ['id'];
		    $go_url = $channel_data ['go_url'];
		    	
		    $params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
		    	
		    $url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
		    	
		    $sign = $this->makeSign ( $params_url, C ( 'XIE95_DESKEY' ) );
		    $url .= '&sign=' . $sign;
		    	
		    $channel_data ['url'] = $url;
		    $channel_data ['need_uc'] = '0';
		    	
		    return $this->ajaxReturn ( array (
		        'ret' => 1,
		        'url' => $url
		    ) );
		}
		
		$xie95_ali_is_enabled = (C ( 'XIE95_ALI_ENABLED' ) == '1');
		
		if (! $xie95_ali_is_enabled) {
			if (C ( 'XIE95_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$xie95_ali_is_enabled = true;
		}
		
		if ($xie95_ali_is_enabled && $type == 'xie95_ali_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '支付宝快捷支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'XIE95_ALI_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'XIE95_GO_URL' ) );
			$channel_data ['mch'] = C ( 'XIE95_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'XIE95_DESKEY2' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '1';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url
			) );
		}
		
		
		$juhe_is_enabled = (C ( 'JUHE_ENABLED' ) == '1');
		
		if (! $juhe_is_enabled) {
		    if (C ( 'JUHE_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
		        $juhe_is_enabled = true;
		}
		
		if ($juhe_is_enabled && $type == 'juhe_pay') {
		    // 微信支付
		    $channel_data = array ();
		    $channel_data ['id'] = $rst;
		    $channel_data ['price'] = $data ['price'];
		    $channel_data ['openid'] = session ( 'openid' );
		    $channel_data ['name'] = '聚合支付';
		    $channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'JUHE_PAY_URL' ) );
		    $channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'JUHE_PAGE_URL' ) );
		    $channel_data ['mch'] = C ( 'JUHE_TERM_ID' );
		    	
		    $channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
		    $channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
		    	
		    $body = '充值' . $channel_data ['price'] . '元';
		    $price = $channel_data ['price'];
		    $pay_url = $channel_data ['pay_url'];
		    $mch = $channel_data ['mch'];
		    $order_sn = $channel_data ['id'];
		    $go_url = $channel_data ['go_url'];
		    	
		    $params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
		    	
		    $url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
		    	
		    $sign = $this->makeSign ( $params_url, C ( 'JUHE_MCH_KEY' ) );
		    $url .= '&sign=' . $sign;
		    	
		    $channel_data ['url'] = $url;
		    $channel_data ['need_uc'] = '0';
		    	
		    return $this->ajaxReturn ( array (
		        'ret' => 1,
		        'url' => $url
		    ) );
		}		
		
		
		$mall91_is_enabled = (C ( 'MALL91_ENABLED' ) == '1');
		$mall91_ali_is_enabled = (C ( 'MALL91_ALI_ENABLED' ) == '1');
		$mall91_wx_is_enabled = (C ( 'MALL91_WX_ENABLED' ) == '1');
		
		if (! $mall91_is_enabled) {
		    if (C ( 'MALL91_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
		        $mall91_is_enabled = true;
		}
		if (! $mall91_ali_is_enabled) {
		    if (C ( 'MALL91_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
		        $mall91_ali_is_enabled = true;
		}
		if (! $mall91_wx_is_enabled) {
			if (C ( 'MALL91_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$mall91_wx_is_enabled = true;
		}	
		
		if ($mall91_is_enabled && $type == 'mall91_pay') {
		    // 微信支付
		    $channel_data = array ();
		    $channel_data ['id'] = $rst;
		    $channel_data ['price'] = $data ['price'];
		    $channel_data ['openid'] = session ( 'openid' );
		    $channel_data ['name'] = '91支付';
		    $channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'MALL91_PAY_URL' ) );
		    $channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'MALL91_GO_URL' ) );
		    $channel_data ['mch'] = C ( 'MALL91_TERM_ID' );
		     
		    $channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
		    $channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
		     
		    $body = '充值' . $channel_data ['price'] . '元';
		    $price = $channel_data ['price'];
		    $pay_url = $channel_data ['pay_url'];
		    $mch = $channel_data ['mch'];
		    $order_sn = $channel_data ['id'];
		     
		    $params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
		     
		    $url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&ticket=' . $ticket .'&user_id=' . $this->user_id;
		     
		    $sign = $this->makeSign ( $params_url, C ( 'MALL91_MCH_KEY' ) );
		    $url .= '&sign=' . $sign;
		     
		    $channel_data ['url'] = $url;
		    $channel_data ['need_uc'] = '0';
		     
		    return $this->ajaxReturn ( array (
		        'ret' => 1,
		        'url' => $url
		    ) );
		}
		
		if ($mall91_ali_is_enabled && $type == 'mall91_ali_pay') {
		    // 微信支付
		    $channel_data = array ();
		    $channel_data ['id'] = $rst;
		    $channel_data ['price'] = $data ['price'];
		    $channel_data ['openid'] = session ( 'openid' );
		    $channel_data ['name'] = '91支付宝支付';
		    $channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'MALL91_ALI_PAY_URL' ) );
		    $channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'MALL91_GO_URL' ) );
		    $channel_data ['mch'] = C ( 'MALL91_TERM_ID' );
		     
		    $channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
		    $channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
		     
		    $body = '充值' . $channel_data ['price'] . '元';
		    $price = $channel_data ['price'];
		    $pay_url = $channel_data ['pay_url'];
		    $mch = $channel_data ['mch'];
		    $order_sn = $channel_data ['id'];
		     
		    $params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
		     
		    $url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&ticket=' . $ticket;
		     
		    $sign = $this->makeSign ( $params_url, C ( 'MALL91_MCH_KEY' ) );
		    $url .= '&sign=' . $sign;
		     
		    $channel_data ['url'] = $url;
		    $channel_data ['need_uc'] = '1';
		     
		    return $this->ajaxReturn ( array (
		        'ret' => 1,
		        'url' => $url
		    ) );
		}
		
		if ($mall91_wx_is_enabled && $type == 'mall91_wx_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '91QQ支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'MALL91_WX_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'MALL91_GO_URL' ) );
			$channel_data ['mch'] = C ( 'MALL91_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . $this->user_id . urlencode($go_url) . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&user_id=' . $this->user_id . '&mch=' . $mch . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'MALL91_MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url
			) );
		}
		
		
		
		$fubei51_is_enabled = (C ( 'FUBEI51_ENABLED' ) == '1');
		
		if (! $fubei51_is_enabled) {
			if (C ( 'FUBEI51_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$fubei51_is_enabled= true;
		}
		
		if ($fubei51_is_enabled&& $type == 'fubei51_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '51支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'FUBEI51_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'FUBEI51_GO_URL' ) );
			$channel_data ['mch'] = C ( 'FUBEI51_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'FUBEI51_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url
			) );
		}
		
		
		$bcf_is_enabled = (C ( 'BCF_ENABLED' ) == '1');
		
		if (! $bcf_is_enabled) {
			if (C ( 'BCF_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$bcf_is_enabled= true;
		}
		
		if ($bcf_is_enabled&& $type == 'bcf_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = 'BCF支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'BCF_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'BCF_GO_URL' ) );
			$channel_data ['mch'] = C ( 'BCF_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'BCF_MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url
			) );
		}
		
		$wft_is_enabled = (C ( 'WFT_ENABLED' ) == '1');
		
		if (! $wft_is_enabled) {
			if (C ( 'WFT_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$wft_is_enabled = true;
		}
		
		if ($wft_is_enabled && $type == 'wft_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '聚合支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'WFT_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'WFT_PAGE_URL' ) );
			$channel_data ['mch'] = C ( 'WFT_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'WFT_MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url
			) );
		}
		
		
		$xueyu_is_enabled = (C ( 'XUEYU_ENABLED' ) == '1');
		
		if (! $xueyu_is_enabled) {
			if (C ( 'XUEYU_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$xueyu_is_enabled= true;
		}
		
		if ($xueyu_is_enabled&& $type == 'xueyu_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '柬埔寨支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'XUEYU_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'XUEYU_GO_URL' ) );
			$channel_data ['mch'] = C ( 'XUEYU_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'XUEYU_MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url
			) );
		}		
		
		
		$ymf_is_enabled = (C ( 'YMF_ENABLED' ) == '1');
		
		if (! $ymf_is_enabled) {
			if (C ( 'YMF_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$ymf_is_enabled= true;
		}
		
		if ($ymf_is_enabled&& $type == 'ymf_ali_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '柬埔寨一码付支付宝支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'YMF_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'YMF_GO_URL' ) );
			$channel_data ['mch'] = C ( 'YMF_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket;
			
			$sign = $this->makeSign ( $params_url, C ( 'YMF_MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url
			) );
		}
		
		$ak47_is_enabled = (C ( 'AK47_ENABLED' ) == '1');
		
		if (! $ak47_is_enabled) {
			if (C ( 'AK47_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$ak47_is_enabled= true;
		}
		
		if ($ak47_is_enabled&& $type == 'ak47_ali_pay') {
			// 微信支付
			$channel_data = array ();
			$channel_data ['id'] = $rst;
			$channel_data ['price'] = $data ['price'];
			$channel_data ['openid'] = session ( 'openid' );
			$channel_data ['name'] = '柬埔寨AK47支付';
			$channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'AK47_PAY_URL' ) );
			$channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'AK47_GO_URL' ) );
			$channel_data ['mch'] = C ( 'AK47_TERM_ID' );
			
			$channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
			$channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
			
			$body = '充值' . $channel_data ['price'] . '元';
			$price = $channel_data ['price'];
			$pay_url = $channel_data ['pay_url'];
			$mch = $channel_data ['mch'];
			$order_sn = $channel_data ['id'];
			$go_url = $channel_data ['go_url'];
			
			$params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $ticket;
			
			$url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&ticket=' . $ticket . '&user_id=' . $this->user_id;
			
			$sign = $this->makeSign ( $params_url, C ( 'AK47_MCH_KEY' ) );
			$url .= '&sign=' . $sign;
			
			$channel_data ['url'] = $url;
			$channel_data ['need_uc'] = '0';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url
			) );
		}
		
		$ry_is_enabled = (C ( 'RY_ENABLED' ) == '1');
		
		if (! $ry_is_enabled) {
		    if (C ( 'RY_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
		        $ry_is_enabled= true;
		}
		
		if ($ry_is_enabled&& $type == 'ry_pay') {
		    // 微信支付
		    $channel_data = array ();
		    $channel_data ['id'] = $rst;
		    $channel_data ['price'] = $data ['price'];
		    $channel_data ['openid'] = session ( 'openid' );
		    $channel_data ['name'] = '瑞银支付';
		    $channel_data ['pay_url'] = str_replace ( '&amp;', '&', C ( 'RY_PAY_URL' ) );
		    $channel_data ['go_url'] = str_replace ( '&amp;', '&', C ( 'RY_GO_URL' ) );
		    $channel_data ['mch'] = C ( 'RY_TERM_ID' );
		    	
		    $channel_data ['pay_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['pay_url'] );
		    $channel_data ['go_url'] = str_replace ( "{hostname}", $host ['hostname'], $channel_data ['go_url'] );
		    	
		    $body = '充值' . $channel_data ['price'] . '元';
		    $price = $channel_data ['price'];
		    $pay_url = $channel_data ['pay_url'];
		    $mch = $channel_data ['mch'];
		    $order_sn = $channel_data ['id'];
		    $go_url = $channel_data ['go_url'];
		    	
		    $params_url = $order_sn . $price . $channel_data ['openid'] . urlencode($go_url) . $this->user_id . $ticket;
		    	
		    $url = $pay_url . '&openid=' . $channel_data ['openid'] . '&body=' . $body . '&order_sn=' . $order_sn . '&price=' . $price . '&mch=' . $mch . '&goback=' . urlencode ( $go_url ) . '&user_id=' . $this->user_id . '&ticket=' . $ticket;
		    	
		    $sign = $this->makeSign ( $params_url, C ( 'RY_MCH_KEY' ) );
		    $url .= '&sign=' . $sign;
		    	
		    $channel_data ['url'] = $url;
		    $channel_data ['need_uc'] = '0';
		    	
		    return $this->ajaxReturn ( array (
		        'ret' => 1,
		        'url' => $url
		    ) );
		}		
		
		$manual_is_enabled = (C ( 'MANUAL_ENABLED' ) == '1');
		
		if (! $manual_is_enabled) {
			if (C ( 'MANUAL_TEST_ENABLED' ) == '1' && session ( 'is_admin_enter' ) == '1')
				$manual_is_enabled= true;
		}
		
		if ($manual_is_enabled&& $type == 'manual_pay') {
			
			$url = 'index.php?g=Wxpay&m=Manualpay&a=entry';
			
			return $this->ajaxReturn ( array (
					'ret' => 1,
					'url' => $url
			) );
		}
	}
}
