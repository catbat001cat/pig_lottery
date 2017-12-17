<?php
namespace Pig\Controller;

use Common\Controller\MemberbaseController;

class IndexController extends MemberbaseController {
    private $wallet_db = null;
    private $lottery_db = null;
    private $lottery_ratio_db = null;
	function _initialize(){
		parent::_initialize();
		
		$this->wallet_db = M('wallet');
		$this->lottery_db = M('lottery');
		$this->lottery_ratio_db = M('lottery_ratio');
	}
	
	public function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq)
	{
		//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|union|into|load_file|outfile/';
		//$pregs = '/select|insert|drop|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		$pregs = '/select|insert|drop|update|document|eval|delete|script|alert|\'|\/\*|\#|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		
		
		if ($StrFiltKey == 'req_url' || $StrFiltKey == 'goback' || $StrFiltKey == 'noncestr')
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
	
    // 主页
	public function index() {
		
		$this->assign($this->user);
		
		$wallet = $this->wallet_db->where("user_id=" . $this->userid)->find();
		$open_lottery = $this->lottery_db->where("status=0 and open_time>now()")->order('open_time asc')->find();

		$this->assign('open_lottery', $open_lottery);
		
		$lastest_lottery = $this->lottery_db->where("status=1")->order("open_time desc")->find();
		
		$this->assign('lastest_lottery', $lastest_lottery);
		
		$lastest_lottery = $this->lottery_db->where("status=1")->order("open_time desc")->limit(0, 2)->select();
		$lottery_ratio = $this->lottery_ratio_db->where()->order('id desc')->find();
				
		// 客服
		$servicer_db = M('servicer');
		$servicer = $servicer_db->where("type=0")->order("id desc")->find();
		$servicer1 = $servicer_db->where("type=1")->order("id desc")->find();
		$servicer2 = $servicer_db->where("type=2")->order("id desc")->find();
		$smeta = json_decode($servicer['smeta'],true);
		$smeta1 = json_decode($servicer1['smeta'],true);
		$smeta2 = json_decode($servicer2['smeta'],true);
		$this->assign('servicer_qr', sp_get_asset_upload_path($smeta['thumb']));
		$this->assign('servicer1_qr', sp_get_asset_upload_path($smeta1['thumb']));
		$this->assign('servicer2_qr', sp_get_asset_upload_path($smeta2['thumb']));
		
		$this->assign('recharge_prices', C('RECHARGE_PRICES'));
		$this->assign('lottery_single_price', C('LOTTERY_SINGLE_PRICE'));
		$this->assign('lottery_ratio', $lottery_ratio);
		$this->assign('lastest_lottery', $lastest_lottery);
		$this->assign('wallet', $wallet);
		if ($_SESSION['is_tips'] != 1)
		{
		    $this->assign('is_tips', 0);
		    $_SESSION['is_tips'] = 1;
		}
		else
		{
		    $this->assign('is_tips', 1);
		}
		
		if (C('IS_STOPPED') == '1')
		{
			if (session('is_admin_enter') == '0')
			{
			    if (empty(C('STOP_GOURL')))
        	    {
        	        echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
        	    }
        	    else
        	    {
        	       $goto_url = str_replace('&amp;', '&', C('STOP_GOURL'));
        		   redirect($goto_url);
        	    }
        		
				
				return;
			}
		}
		
		$this->display(":index");
    }
    
    // 教程
    public function jiaocheng()
    {
    	$this->display(':jiaocheng');
    }
    
    // 代理
    public function daili1()
    {
    	$this->assign('ratio1', C('COMMISION_DIVIDE_RATIO'));
    	$this->assign('ratio2', C('COMMISION_DIVIDE_RATIO2'));
    	$this->assign('ratio3', C('COMMISION_DIVIDE_RATIO3'));
    	$this->assign('ratio4', C('COMMISION_DIVIDE_RATIO4'));
    	$this->assign('ratio5', C('COMMISION_DIVIDE_RATIO5'));
    	$this->display(':daili1');
    }
    
    private function channels_user_ids($child_channels)
    {
    	$str = '';
    	for ($i=0; $i<count($child_channels); $i++)
    	{
    		if ($i==0)
    			$str = $child_channels[$i]['admin_user_id'];
    		else
    			$str = $str . ',' .  $child_channels[$i]['admin_user_id'];
    	}
    	return $str;
    }
    
    // 佣金
    public function daili2()
    {
    	$channel_db = M('channels a');
    	
    	$my_channel = $channel_db->where("admin_user_id=$this->userid")->find();
    	
    	$child_channels1 = $channel_db->where("parent_id=" . $my_channel['id'])->select();
    	$child_channels2 = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')
    	->where("b.parent_id=" . $my_channel['id'])
    	->field('a.*')
    	->select();
    	$child_channels3 = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')
    	->join('__CHANNELS__ c on c.id=b.parent_id', 'left')
    	->where("c.parent_id=" . $my_channel['id'])
    	->field('a.*')
    	->select();
    	$child_channels4 = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')
    	->join('__CHANNELS__ c on c.id=b.parent_id', 'left')
    	->join('__CHANNELS__ d on d.id=c.parent_id', 'left')
    	->where("d.parent_id=" . $my_channel['id'])
    	->field('a.*')
    	->select();
    	$child_channels5 = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')
    	->join('__CHANNELS__ c on c.id=b.parent_id', 'left')
    	->join('__CHANNELS__ d on d.id=c.parent_id', 'left')
    	->join('__CHANNELS__ e on e.id=d.parent_id', 'left')
    	->where("e.parent_id=" . $my_channel['id'])
    	->field('a.*')
    	->select();

    	$this->assign('level1_childusers', count($child_channels1));
    	$this->assign('level2_childusers', count($child_channels2));
    	$this->assign('level3_childusers', count($child_channels3));
    	$this->assign('level4_childusers', count($child_channels4));
    	$this->assign('level5_childusers', count($child_channels5));
    	
    	$ids1 = $this->channels_user_ids($child_channels1);
    	$ids2 = $this->channels_user_ids($child_channels2);
    	$ids3 = $this->channels_user_ids($child_channels3);
    	$ids4 = $this->channels_user_ids($child_channels4);
    	$ids5 = $this->channels_user_ids($child_channels5);
    	
    	$wallet_change_log_db = M('wallet_change_log a');
    	
    	$total1 = 0;
    	if (count($child_channels1) > 0)
    	{
    		$total1 = $wallet_change_log_db
    		->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')
    		->where("a.type=4 and a.user_id=$this->userid and b.user_id in ($ids1)")->sum('fee');
    	}
    	$total2 = 0;
    	if (count($child_channels2) > 0)
    	{
    		$total2 = $wallet_change_log_db
    		->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')
    		->where("a.type=4 and a.user_id=$this->userid and b.user_id in ($ids2)")->sum('fee');
    	}
    	$total3 = 0;
    	if (count($child_channels3) > 0)
    	    $total3 = $wallet_change_log_db->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')->where("a.type=4 and a.user_id=$this->userid and b.user_id in ($ids3)")->sum('fee');
    	$total4 = 0;
    	if (count($child_channels4) > 0)
    	    $total4 = $wallet_change_log_db->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')->where("a.type=4 and a.user_id=$this->userid and b.user_id in ($ids4)")->sum('fee');
    	$total5 = 0;
    	if (count($child_channels5) > 0)
    	    $total5 = $wallet_change_log_db->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')->where("a.type=4 and a.user_id=$this->userid and b.user_id in ($ids5)")->sum('fee');
    	
    	if ($total1 == null)
    	    $total1 = 0;
    	if ($total2 == null)
    	    $total2 = 0;
    	if ($total3 == null)
    	    $total3 = 0;
    	if ($total4 == null)
    	    $total4 = 0;
    	if ($total5 == null)
    	    $total5 = 0;
    	
    	$this->assign('total1', round($total1, 2));
    	$this->assign('total2', round($total2, 2));
    	$this->assign('total3', round($total3, 2));
    	$this->assign('total4', round($total4, 2));
    	$this->assign('total5', round($total5, 2));
    	
    	$this->display(':daili2');
    }
    
    // 下线
    public function daili3()
    {
    	$this->display(':daili3');
    }
    
    // 进入游戏
    public function newduobao()
    {
    	$this->filterAttack();
    	
    	if (C('IS_STOPPED_LOTTERY') == '1')
    	{
    		if (session('is_admin_enter') == '0')
    		{
    			echo "<script>history.go(-1);</script>";

    			return;
    		}
    	}
    	
    	
    	// 判断是否能够直接处理
    	$tx_appid = session('tx_appid');
    	$tx_openid = session('tx_openid');
    	
    	if ($tx_appid == null || $tx_appid == '' || $tx_openid == null || $tx_openid == '')
    	{
    		// 这里先跳转到提现入口，成功再进入
    		$ticket = time();
    		
    		$from_srouce = 'newduobao';
    		
    		$index = 0;
    		/*if (session('tx_index') == 0)
    		 {
    		 $index = 1;
    		 }
    		 else
    		 {
    		 $index = 0;
    		 }
    		 */
    		
    		if ($index == 0)//rand(0, 1) == 0)
    		{
    			$gourl = str_replace('&amp;', '&', C('TIXIAN_OPENID_URL'));
    			
    			$sign =  md5(strtolower($from_srouce. $ticket. C('MCH_KEY')));
    			
    			$key = C('MCH_KEY');
    		}
    		else
    		{
    			$index = 1;
    			
    			$gourl = str_replace('&amp;', '&', C('TIXIAN_OPENID_URL2'));
    			
    			$sign =  md5(strtolower($from_srouce. $ticket. C('MCH_KEY2')));
    			
    			$key = C('MCH_KEY2');
    		}
    		
    		session('tx_index', $index);
    		
    		$goback= 'http://' . $_SERVER['HTTP_HOST'] . '/index.php?g=Pig&m=index&a=newduobao_back&lottery_ticket=' . $ticket . '&lottery_sign=' . $sign;
    		
    		$jsapi_ticket = time();
    		$jsapi_sign = md5(strtolower(urlencode($goback) . $jsapi_ticket . $key));
    		
    		$gourl = $gourl . '&index=' . $index . '&goback=' . urlencode($goback) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign;
    		
    		header("Location: $gourl"); 
    	}
    	else 
    	{
    		$this->newduobao_direct();
    	}
    }
    
    public function newduobao_back()
    {
    	$this->filterAttack();
    	
    	$from_srouce = 'newduobao';
    	$lottery_ticket = $_REQUEST['lottery_ticket'];
    	$lottery_sign = $_REQUEST['lottery_sign'];
    	$noncestr = $_REQUEST['noncestr'];
    	$openid = $_REQUEST['openid'];
    	$appid = C('APPID');//$_REQUEST['appid'];
    	$ticket = $_REQUEST['ticket'];
    	$sign = $_REQUEST['sign'];
    	$url = $appid . $openid . $ticket . $noncestr;
    	$new_sign = md5(strtolower($url . C('LOGIN_KEY')));
    	
    	if ($new_sign != $sign)
    	{
    		// 日志
    		$action_log = M('user_action_log');
    		$log_data = array(
    				'user_id' => $this->userid,
    				'action' => 'hack',
    				'params' => '签名不正确',
    				'ip' => get_client_ip(0, true),
    				'create_time' => date('Y-m-d H:i:s')
    		);
    		$action_log->add($log_data);
    		
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	

    	//if ($appid == C('APPID'))
    		$key = C('MCH_KEY');
    	//else
    		//$key = C('MCH_KEY2');
    	
    	$new_sign =  md5(strtolower($from_srouce. $lottery_ticket. $key));

    	if ($lottery_sign!= $new_sign)
    	{
    		// 日志
    		$action_log = M('user_action_log');
    		$log_data = array(
    				'user_id' => $this->userid,
    				'action' => 'hack',
    				'params' => '签名不正确',
    				'ip' => get_client_ip(0, true),
    				'create_time' => date('Y-m-d H:i:s')
    		);
    		$action_log->add($log_data);
    		
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	session('tx_appid', $appid);
    	session('tx_openid', $openid);
    	
    	$lottery_ratio = $this->lottery_ratio_db->where()->order('id desc')->find();
    	$this->assign('ratio', $lottery_ratio);
    	$this->assign('lottery_single_price', C('LOTTERY_SINGLE_PRICE'));
    	$this->assign('recharge_prices', C('RECHARGE_PRICES'));
    	$this->assign('lottery_price_ratio', C('LOTTERY_PRICE_RATIO'));
    	
    	$this->display(':newduobao');
    }
    
    public function newduobao_direct()
    {
    	$lottery_ratio = $this->lottery_ratio_db->where()->order('id desc')->find();
    	$this->assign('ratio', $lottery_ratio);
    	$this->assign('lottery_single_price', C('LOTTERY_SINGLE_PRICE'));
    	$this->assign('recharge_prices', C('RECHARGE_PRICES'));
    	$this->assign('lottery_price_ratio', C('LOTTERY_PRICE_RATIO'));
    	
    	$this->display(':newduobao');
    }
    
    // 如何验证
    public function chakandanhao()
    {
    	$this->display(':chakandanhao');
    }
    
    // 充值
    public function newchongzhi()
    {
    	$this->filterAttack();
    	
        // 列举出来可以充值的金额列表
        $this->assign('recharge_prices', C('RECHARGE_PRICES'));
        
        $wxpay_is_enabled = (C('WXPAY_ENABLED') == '1');
        $yubwx_is_enabled = (C('YUBWX_ENABLED') == '1');
        $yubwx_ali_is_enabled = (C('YUBWX_ALI_ENABLED') == '1');
        $bft_is_enabled = (C('BFT_ENABLED') == '1');
        $bft_ali_is_enabled = (C('BFT_ALI_ENABLED') == '1');
        $zszf_is_enabled = (C('ZSZF_ENABLED') == '1');
        $juhe_is_enabled = (C('JUHE_ENABLED') == '1');
        $xie95_is_enabled = (C('XIE95_ENABLED') == '1');
        $xie95_ali_is_enabled = (C('XIE95_ALI_ENABLED') == '1');
        $mall91_is_enabled = (C('MALL91_ENABLED') == '1');
        $mall91_ali_is_enabled = (C('MALL91_ALI_ENABLED') == '1');
        $mall91_wx_is_enabled = (C('MALL91_WX_ENABLED') == '1');  
        $fubei51_is_enabled = (C('FUBEI51_ENABLED') == '1');
        $bcf_is_enabled = (C('BCF_ENABLED') == '1');
        $wft_is_enabled = (C('WFT_ENABLED') == '1');
        $xueyu_is_enabled = (C('XUEYU_ENABLED') == '1');
        $ymf_is_enabled = (C('YMF_ENABLED') == '1');
        $ak47_is_enabled = (C('AK47_ENABLED') == '1');
        
        if (!$bft_is_enabled && $_SESSION['is_admin_enter']== '1')
        	$bft_is_enabled = (C('BFT_TEST_ENABLED') == '1');
        
       	if (!$bft_ali_is_enabled && $_SESSION['is_admin_enter']== '1')
        	$bft_ali_is_enabled = (C('BFT_TEST_ENABLED') == '1');
        	
        if (!$zszf_is_enabled && $_SESSION['is_admin_enter']== '1')
        	$zszf_is_enabled = (C('ZSZF_TEST_ENABLED') == '1');
        
        if (!$yubwx_is_enabled&& $_SESSION['is_admin_enter']== '1')
        	$yubwx_is_enabled= (C('YUBWX_TEST_ENABLED') == '1');
        
        if (!$yubwx_ali_is_enabled&& $_SESSION['is_admin_enter']== '1')
        	$yubwx_ali_is_enabled= (C('YUBWX_TEST_ENABLED') == '1');
        
        if (!$juhe_is_enabled&& $_SESSION['is_admin_enter']== '1')
            $juhe_is_enabled= (C('JUHE_TEST_ENABLED') == '1');
        
         if (!$xie95_is_enabled&& $_SESSION['is_admin_enter']== '1')
           	$xie95_is_enabled= (C('XIE95_TEST_ENABLED') == '1');
         
         if (!$xie95_ali_is_enabled&& $_SESSION['is_admin_enter']== '1')
           	$xie95_ali_is_enabled= (C('XIE95_TEST_ENABLED') == '1');
         
         if (!$mall91_is_enabled && $_SESSION['is_admin_enter']== '1')
             $mall91_is_enabled = (C('MALL91_TEST_ENABLED') == '1');
         
         if (!$mall91_ali_is_enabled && $_SESSION['is_admin_enter']== '1')
             $mall91_ali_is_enabled = (C('MALL91_TEST_ENABLED') == '1');  
         
         if (!$mall91_wx_is_enabled && $_SESSION['is_admin_enter']== '1')
             $mall91_wx_is_enabled = (C('MALL91_TEST_ENABLED') == '1'); 
         
         if (!$fubei51_is_enabled && $_SESSION['is_admin_enter']== '1')
             $fubei51_is_enabled = (C('FUBEI51_TEST_ENABLED') == '1');
        
         if (!$bcf_is_enabled && $_SESSION['is_admin_enter']== '1')
             $bcf_is_enabled = (C('BCF_TEST_ENABLED') == '1');
         
         if (!$wft_is_enabled && $_SESSION['is_admin_enter']== '1')
         	 $wft_is_enabled= (C('WFT_TEST_ENABLED') == '1');
         
       	 if (!$xueyu_is_enabled && $_SESSION['is_admin_enter']== '1')
       	 	$xueyu_is_enabled= (C('XUEYU_TEST_ENABLED') == '1');
       	 
       	 if (!$ymf_is_enabled && $_SESSION['is_admin_enter']== '1')
       	 	$ymf_is_enabled= (C('YMF_TEST_ENABLED') == '1');
       	 
       	 if (!$ak47_is_enabled && $_SESSION['is_admin_enter']== '1')
       	 	$ak47_is_enabled= (C('AK47_TEST_ENABLED') == '1');
             
        $channels = array();
        
        if ($wxpay_is_enabled)
        {
            $data = array(
                'name' => '微信公众号支付',
                'type' => 'wxpay',
                'wx' => 1
            );
        
            array_push($channels, $data);
        }
        
        
        if ($yubwx_is_enabled)
        {
        	$data = array(
        			'name' => '微信扫码支付',
        			'type' => 'yubwx_pay',
        			'wx' => 1
        	);
        	
        	array_push($channels, $data);
        }
        
        
        if ($yubwx_ali_is_enabled)
        {
        	$data = array(
        			'name' => '支付宝扫码支付',
        			'type' => 'yubwx_ali_pay',
        			'wx' => 0
        	);
        	
        	array_push($channels, $data);
        }
        
        if ($juhe_is_enabled)
        {
            $data = array(
                'name' => '微信聚合快捷支付',
                'type' => 'juhe_pay',
                'wx' => 1
            );
        
            array_push($channels, $data);
        }
        
        if ($xie95_is_enabled)
        {
        	$data = array(
        			'name' => '微信支付1',
        			'type' => 'xie95_pay',
        			'wx' => 1
        	);
        	
        	array_push($channels, $data);
        }
        
        if ($xie95_ali_is_enabled)
        {
        	$data = array(
        			'name' => '支付宝快捷支付',
        			'type' => 'xie95_ali_pay',
        			'wx' => 0
        	);
        	
        	array_push($channels, $data);
        }  
        
        if ($mall91_is_enabled)
        {
            $data = array(
                'name' => '微信91扫码支付',
                'type' => 'mall91_pay',
                'wx' => 1
            );
        
            array_push($channels, $data);
        }
        
        if ($mall91_ali_is_enabled)
        {
            $data = array(
                'name' => '91支付宝扫码支付',
                'type' => 'mall91_ali_pay',
                'wx' => 1
            );
        
            array_push($channels, $data);
        }    
         
        
        if ($bft_is_enabled)
        {
        	$data = array(
        			'name' => '公众号快捷支付',
        			'type' => 'bft_pay',
        			'wx' => 1
        	);
        	
        	array_push($channels, $data);
        }
        
        if ($bft_ali_is_enabled)
        {
        	$data = array(
        			'name' => '支付宝支付',
        			'type' => 'bft_ali_pay',
        			'wx' => 0
        	);
        	
        	array_push($channels, $data);
        }
        
        if ($zszf_is_enabled)
        {
        	$data = array(
        			'name' => '掌上支付',
        			'type' => 'zszf_pay',
        			'wx' => 1
        	);
        	
        	array_push($channels, $data);
        }
        
        if ($fubei51_is_enabled)
        {
        	$data = array(
        			'name' => '51支付',
        			'type' => 'fubei51_pay',
        			'wx' => 1
        	);
        	
        	array_push($channels, $data);
        }
        
        if ($bcf_is_enabled)
        {
        	$data = array(
        			'name' => '微信公众号B支付',
        			'type' => 'bcf_pay',
        			'wx' => 1
        	);
        	
        	array_push($channels, $data);
        }
        
        if ($ak47_is_enabled)
        {
        	$data = array(
        			'name' => '微信支付3',
        			'type' => 'ak47_ali_pay',
        			'wx' => 1
        	);
        	
        	array_push($channels, $data);
        }  
        
        if ($mall91_wx_is_enabled)
        {
        	$data = array(
        			'name' => 'QQ钱包支付',
        			'type' => 'mall91_wx_pay',
        			'wx' => 1
        	);
        	
        	array_push($channels, $data);
        }   
        
        if ($wft_is_enabled)
        {
        	$data = array(
        			'name' => '微信支付2',
        			'type' => 'wft_pay',
        			'wx' => 1
        	);
        	
        	array_push($channels, $data);
        }  
        
        if ($xueyu_is_enabled)
        {
        	$data = array(
        			'name' => 'H5支付',
        			'type' => 'xueyu_pay',
        			'wx' => 1
        	);
        	
        	array_push($channels, $data);
        } 
        
        if ($ymf_is_enabled)
        {
        	$data = array(
        			'name' => '支付宝支付',
        			'type' => 'ymf_ali_pay',
        			'wx' => 0
        	);
        	
        	array_push($channels, $data);
        }
        
        $wallet = $this->wallet_db->where("user_id=" . $this->userid)->find();
        $this->assign('wallet', $wallet);
        $this->assign('channels', $channels);
        
    	$this->display(':newchongzhi');
    }
    
    // 提现
    public function txselect()
    {
    	$wallet = $this->wallet_db->where("user_id=" . $this->userid)->find();
    	$this->assign('wallet', $wallet);
    	$this->assign('base_price', C('DRAWCASH_BASE_PRICE'));
    	$this->assign('max_times', C('DRAWCASH_TIMES_PER_DAY'));
    	
    	$this->display(':txselect');
    }
    
    // 代理佣金提现
    public function dailiyongjintixian()
    {
    	$wallet = $this->wallet_db->where("user_id=" . $this->userid)->find();
    	$this->assign('wallet', $wallet);
    	$this->assign('base_price', C('DRAWCASH_BASE_PRICE'));
    	$this->assign('max_times', C('DRAWCASH_TIMES_PER_DAY'));
    	
    	$this->display(':dailiyongjintixian');
    }
    
    // 我的
    public function newmy()
    {
    	$this->display(':newmy');
    }
    
    // 夺宝记录
    public function newjiaoyimingxi()
    {
    	$this->display(':newjiaoyimingxi');
    }
    
    // 夺宝资金
    public function newzijinmingxi()
    {
    	$this->display(':newzijinmingxi');
    }
    
    // 常见问题
    public function help()
    {
    	$this->display(':help');
    }
    
    // 夺宝规则
    public function jiaoyiguize()
    {
    	$this->display(':jiaoyiguize');
    }
    
    // 充值相关
    public function chongzhixiangguan()
    {
    	$this->display(':chongzhixiangguan');
    }
    
    // 提现相关
    public function tixianxiangguan()
    {
    	$this->display(':tixianxiangguan');
    }
    
    // 每日签到
    public function signed()
    {
        $wallet_change_log_db = M('wallet_change_log');
        
        $total_fee = $wallet_change_log_db->where("user_id=$this->userid and `type`=6")->sum('fee');
        
        $this->assign('total_bonus', $total_fee);
        
    	$this->display(':signed');
    }
    
    // 联系客服
    public function lxKF()
    {
    	// 客服
    	$servicer_db = M('servicer');
    	$servicer = $servicer_db->where("type=0")->order("id desc")->find();
    	$servicer1 = $servicer_db->where("type=1")->order("id desc")->find();
    	$servicer2 = $servicer_db->where("type=2")->order("id desc")->find();
    	$smeta = json_decode($servicer['smeta'],true);
    	$smeta1 = json_decode($servicer1['smeta'],true);
    	$smeta2 = json_decode($servicer2['smeta'],true);
    	$this->assign('servicer_qr', sp_get_asset_upload_path($smeta['thumb']));
    	$this->assign('servicer1_qr', sp_get_asset_upload_path($smeta1['thumb']));
    	$this->assign('servicer2_qr', sp_get_asset_upload_path($smeta2['thumb']));
    	
    	$this->display(':lxKF');
    }
}
