<?php
namespace Zp\Controller;

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
			
			\Log::DEBUG("<br>用户ID:" . $this->userid  . "<br>操作IP: ".$_SERVER["REMOTE_ADDR"]."<br>操作时间: ".strftime("%Y-%m-%d %H:%M:%S")."<br>操作页面:".$_SERVER["REQUEST_URI"]."<br>提交方式: ".$_SERVER["REQUEST_METHOD"]."<br>提交参数: ".$StrFiltKey."<br>提交数据: ".$StrFiltValue);
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
	public function main() {
		
		$this->assign($this->user);
		
		$wallet = $this->wallet_db->where("user_id=" . $this->userid)->find();
		$open_lottery = $this->lottery_db->where("status=0 and open_time>now()")->order('open_time asc')->find();

		$this->assign('open_lottery', $open_lottery);
		
		$lastest_lottery = $this->lottery_db->where("status=1")->order("open_time desc")->find();
		
		$this->assign('lastest_lottery', $lastest_lottery);
		
		$lastest_lottery = $this->lottery_db->where("status=1")->order("open_time desc")->limit(0, 2)->select();
		$lottery_ratio = $this->lottery_ratio_db->where()->order('id desc')->find();
		
		
		$wxpay_is_enabled = (C('WXPAY_ENABLED') == '1');
		$yubwx_is_enabled = (C('YUBWX_ENABLED') == '1');
		$bft_is_enabled = (C('BFT_ENABLED') == '1');
		$bft_ali_is_enabled = (C('BFT_ALI_ENABLED') == '1');
		$zszf_is_enabled = (C('ZSZF_ENABLED') == '1');
		$xie95_is_enabled = (C('XIE95_ENABLED') == '1');
		$xie95_ali_is_enabled = (C('XIE95_ALI_ENABLED') == '1');
		$mall91_is_enabled = (C('MALL91_ENABLED') == '1');
		$mall91_ali_is_enabled = (C('MALL91_ALI_ENABLED') == '1');
		$ak47_is_enabled = (C('AK47_ENABLED') == '1');
		
		if (!$bft_is_enabled && $_SESSION['is_admin_enter']== '1')
		    $bft_is_enabled = (C('BFT_TEST_ENABLED') == '1');
		
		if (!$bft_ali_is_enabled && $_SESSION['is_admin_enter']== '1')
			$bft_ali_is_enabled= (C('BFT_TEST_ENABLED') == '1');
		
		if (!$zszf_is_enabled && $_SESSION['is_admin_enter']== '1')
		    $zszf_is_enabled = (C('ZSZF_TEST_ENABLED') == '1');
		
		if (!$xie95_is_enabled && $_SESSION['is_admin_enter']== '1')
		    $xie95_is_enabled = (C('XIE95_TEST_ENABLED') == '1');
		
		if (!$xie95_ali_is_enabled&& $_SESSION['is_admin_enter']== '1')
		    $xie95_ali_is_enabled= (C('XIE95_TEST_ENABLED') == '1');
		
		if (!$mall91_is_enabled && $_SESSION['is_admin_enter']== '1')
		    $mall91_is_enabled = (C('MALL91_TEST_ENABLED') == '1');
		
		if (!$mall91_ali_is_enabled && $_SESSION['is_admin_enter']== '1')
		    $mall91_ali_is_enabled = (C('MALL91_TEST_ENABLED') == '1');
		
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
		
		if ($bft_is_enabled)
		{
		    $data = array(
		        'name' => '微信快捷支付',
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
		
		
		if ($yubwx_is_enabled)
		{
		    $data = array(
		        'name' => '微信扫码支付',
		        'type' => 'yubwx_pay',
		        'wx' => 1
		    );
		     
		    array_push($channels, $data);
		}
		
		if ($zszf_is_enabled)
		{
		    $data = array(
		        'name' => '支付宝-QQ钱包',
		        'type' => 'zszf_pay',
		        'wx' => 0
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
		
		if ($ak47_is_enabled)
		{
			$data = array(
					'name' => '微信支付3',
					'type' => 'ak47_ali_pay',
					'wx' => 1
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
		        'name' => '微信91支付宝扫码支付',
		        'type' => 'mall91_ali_pay',
		        'wx' => 1
		    );
		
		    array_push($channels, $data);
		}		
				
		
		$this->assign('recharge_prices', C('RECHARGE_PRICES'));
		$this->assign('lottery_single_price', C('LOTTERY_SINGLE_PRICE'));
		$this->assign('lottery_ratio', $lottery_ratio);
		$this->assign('lastest_lottery', $lastest_lottery);
		$this->assign('wallet', $wallet);
		$this->assign('channels', $channels);
		
		if ($_SESSION['is_tips'] != 1)
		{
		    $this->assign('is_tips', 0);
		    $_SESSION['is_tips'] = 1;
		}
		else
		{
		    $this->assign('is_tips', 1);
		}
		
		$servicer_db = M('servicer');
		//$servicer = $servicer_db->where("type=0")->order("id desc")->find();
		//$servicer1 = $servicer_db->where("type=1")->order("id desc")->find();
		$servicer2 = $servicer_db->where("type=2")->order("id desc")->find();
		
		//$smeta = json_decode($servicer['smeta'],true);
		//$smeta1 = json_decode($servicer1['smeta'],true);
		$smeta2 = json_decode($servicer2['smeta'],true);
		//$this->assign('servicer_qr', sp_get_asset_upload_path($smeta['thumb']));
		//$this->assign('servicer1_qr', sp_get_asset_upload_path($smeta1['thumb']));
		$this->assign('servicer2_qr', sp_get_asset_upload_path($smeta2['thumb']));
		
		
		// 生成分享地址
		{
			$data = M('ads_template')->where()->order('id desc')->find();
			
			$url = $data['url'];
			$level = 'L';
			$size = 4;
			
			$channel_id = 0;
			
			$channel_db = M('channels');
			$admin_channel = $channel_db->where("admin_user_id=$this->userid")->find();
			$channel_user_id = 0;
			if ($admin_channel != null)
			{
				$channel_id = $admin_channel['id'];
				$channel_user_id = $admin_channel['admin_user_id'];
			}
			
			$hosts_db = M('hostnames');
			$hosts = $hosts_db->where('status=1 and `type` in (0,2)')->order('`type` asc, update_time desc')->select();
			shuffle($hosts);
			$host = $hosts[0];
			
			$share_url= str_replace("{hostname}", $host['hostname'], $url);
			$share_url= str_replace("{channel_id}", $channel_id, $url);
			
			$ticket = time();
			$sign = md5($channel_id . $ticket . C('LOGIN_KEY'));
			$share_url .= '&ticket=' . $ticket . '&sign=' . $sign; 
			
			$this->assign('share_url', $share_url);
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
		
		$this->display(":main");
    }
    
    public function index()
    {
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
    	
        $lottery_ratio = $this->lottery_ratio_db->where()->order('id desc')->find();
        $this->assign('ratio', $lottery_ratio);
        $this->assign('lottery_single_price', C('LOTTERY_SINGLE_PRICE'));
        $this->assign('recharge_prices', C('RECHARGE_PRICES'));
        $this->assign('lottery_price_ratio', C('LOTTERY_PRICE_RATIO'));
        $this->assign('game1_url', C('GAME1_URL'));
        $this->display(':index');
    }
    
    public function searching()
    {
        $this->display(':searching');
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

    public function proxy()
    {
    	$level1_childusers = 0;
    	$level2_childusers = 0;
    	$level3_childusers = 0;
    	$level4_childusers = 0;
    	$level5_childusers = 0;
    	
    	$channel_db = M('channels a');
    	
    	$my_channel = $channel_db->where("admin_user_id=$this->userid")->find();
    	
    	// 获取第一级子渠道列表
    	$child_channels1 = $channel_db->where("parent_id=" . $my_channel['id'])->select();
    	$child_channels2 = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')
    	->where("b.parent_id=" . $my_channel['id'])->select();
    	$child_channels3 = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')
    	->join('__CHANNELS__ c on c.id=b.parent_id', 'left')
    	->where("c.parent_id=" . $my_channel['id'])->select();
    	$child_channels4 = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')
    	->join('__CHANNELS__ c on c.id=b.parent_id', 'left')
    	->join('__CHANNELS__ d on d.id=c.parent_id', 'left')
    	->where("d.parent_id=" . $my_channel['id'])->select();
    	$child_channels5 = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')
    	->join('__CHANNELS__ c on c.id=b.parent_id', 'left')
    	->join('__CHANNELS__ d on d.id=c.parent_id', 'left')
    	->join('__CHANNELS__ e on e.id=d.parent_id', 'left')
    	->where("e.parent_id=" . $my_channel['id'])->select();
    	
    	$ids1 = $this->channels_user_ids($child_channels1);
    	$ids2 = $this->channels_user_ids($child_channels2);
    	$ids3 = $this->channels_user_ids($child_channels3);
    	$ids4 = $this->channels_user_ids($child_channels4);
    	$ids5 = $this->channels_user_ids($child_channels5);

    	$total1 = 0;
    	$total2 = 0;
    	$total3 = 0;
    	$total4 = 0;
    	$total5 = 0;
    	
    	/*
    	$level1_childusers_arr = get_users_from_channels($child_channels1);
    	$level2_childusers_arr = get_users_from_channels($child_channels2);
    	$level3_childusers_arr = get_users_from_channels($child_channels3);
    	$level4_childusers_arr = get_users_from_channels($child_channels4);
    	$level5_childusers_arr = get_users_from_channels($child_channels5);
    	*/
    	
    	$servicer_db = M('servicer');
    	$servicer = $servicer_db->where()->order("id desc")->find();
    	
    	$smeta = json_decode($servicer['smeta'],true);
    	
    	$this->assign('servicer_qr', sp_get_asset_upload_path($smeta['thumb']));
    	
    	$this->assign('level1_childusers', count($child_channels1));
    	$this->assign('level2_childusers', count($child_channels2));
    	$this->assign('level3_childusers', count($child_channels3));
    	$this->assign('level4_childusers', count($child_channels4));
    	$this->assign('level5_childusers', count($child_channels5));
    	
    	$this->assign('total1', $total1);
    	$this->assign('total2', $total2);
    	$this->assign('total3', $total3);
    	$this->assign('total4', $total4);
    	$this->assign('total5', $total5);
    	
        $this->display(':proxy');
    }
    
    public function service()
    {
    	$servicer_db = M('servicer');
    	$servicer = $servicer_db->where("type=0")->order("id desc")->find();
    	
    	$smeta = json_decode($servicer['smeta'],true);
    	
    	$this->assign('servicer_qr', sp_get_asset_upload_path($smeta['thumb']));
    	
        $this->display(':service');
    }
    
    // 充值
    public function record()
    {
        $this->display(':record');
    }
    
    // 充值记录
    public function record_charge()
    {
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
    	
        $this->display(':record_charge');
    }
    
    // 兑换记录
    public function record_cash()
    {
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
    	
        $this->display(':record_cash');
    }
    
    // 佣金记录
    public function record_money()
    {
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
    	
        $this->display(':record_money');
    }
    
    // 竞猜记录
    public function record_guess()
    {
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
    	
        $this->display(':record_guess');
    }
    
    // 竞猜榜单
    public function guess_list()
    {
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
    	
        $this->display(':guess_list');
    }
    
    public function records()
    {
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
    	
    	$this->display(':records');
    }
    
    public function recharges()
    {
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
    	
    	$this->display(':recharges');
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
    
    public function ajax_get_user_info()
    {
    	$wallet = $this->wallet_db->where("user_id=" . $this->userid)->find();
    	
    	$this->ajaxReturn(array('ret' => 1, 'info' => $this->user, 'wallet' => $wallet, 'ishongbao' => 1));
    }
    
    public function ajax_get_wallet()
    {
        $this->assign($this->user);
        
        $wallet = $this->wallet_db->where("user_id=" . $this->userid)->find();
        
        $this->ajaxReturn(array('ret' => 1, 'info' => $wallet));
    }
    
    public function ajax_get_win_list()
    {
        $lottery_order_db = M('zp_lottery');
        
        $lists = $lottery_order_db->where("win>buy_price")->order("id desc")->limit(0, 40)->select();
        
        shuffle($lists);
        
        //$lists = $lottery_order_db->where("win>0")->order("id desc")->limit(0, 5)->select();
        $this->ajaxReturn(array('ret' => 1, 'lists' => $lists));
    }
    
    public function ajax_get_lottery_info()
    {
        // 获取最新的投注信息,0-开启投注,1-关闭投注,2-出结果(没有最新的投注信息)
        $lottery = $this->lottery_db->where()
        ->order("open_time desc")
        ->field("id, no, num3, create_time, open_time, type, status, timestampdiff(SECOND,now(), open_time) as diff")
        ->find();
        
        // 数据矫正
        $lottery['diff'] -= 5;

        $lottery_history = $this->lottery_db->where("status=2")
        ->order("open_time desc")
        ->field('id, no, number, num3, create_time, open_time, type, status')
        ->limit(0, 3)->select();
        
        $lottery_ratio = $this->lottery_ratio_db->where()->order('id desc')->find();
        
        $this->ajaxReturn(array('ret' => 1, 'current_lottery' => $lottery, 'lottery_history' => $lottery_history, 'ratio' => $lottery_ratio));
    }

    public function ajax_get_open_lottery_result($no)
    {
        // 获取最新的投注信息,0-开启投注,1-关闭投注,2-出结果(没有最新的投注信息)
        $lottery = $this->lottery_db->where("no='$no'")
        ->order("open_time desc")
        ->field('id, no, number, num3, create_time, open_time, type, status')
        ->find();
        
        $buy_count = 0;
        if ($lottery['status'] == 2)
        {
        	// 我中奖了吗？
        	$lottery_order_db = M('lottery_order');
        	$buy_count = $lottery_order_db->where("no='$no' and user_id=" . $this->userid)->count();
        	$total_win = 0;
        	$total_price = 0;
        	$is_win = 0;
        	if ($buy_count > 0)
        	{
        		$total_win = $lottery_order_db->where("no='$no' and `status`=1 and user_id=" . $this->userid)->sum('win');
        		$total_price = $lottery_order_db->where("no='$no' and user_id=" . $this->userid)->sum('price');
        		
        		if ($total_win == null)
        			$total_win = 0;
        		
        		if ($total_win > 0)
        			$is_win = true;
        	}
        	
        	$result = array(
        		'buy_count' => $buy_count,
        		'is_win' => $is_win,
        		'total_price' => $total_price,
        		'total_win' => $total_win,
        	);
        	
        	$this->ajaxReturn(array('ret' => 1, 'lottery' => $lottery, 'result' => $result));
        }
        else
        {
            $this->ajaxReturn(array('ret' => -1));
        }
    }
    
    public function ajax_get_lotterys()
    {
    	$this->filterAttack();
    	
        $firstRow = 0;
        $limitRows = 50;
        
        if (isset($_REQUEST['firstRow']))
            $firstRow = intval($_REQUEST['firstRow']);
        if (isset($_REQUEST['limitRows']))
            $limitRows= intval($_REQUEST['limitRows']);
        
        $lottery_history = $this->lottery_db->where("status=2")
        ->order("open_time desc")
        ->limit($firstRow, $limitRows)
        ->field('id, no, number, num3, create_time, open_time, type, status')
        ->select();
        
        $this->ajaxReturn(array('ret' => 1, 'lottery_history' => $lottery_history));
    }
    
    public function ajax_get_ranks($type)
    {
    	$this->filterAttack();
    	
    	$user_model = M('users a');
    	if ($type == 'cur_day')
    	{
    		$lists = $user_model->where("cur_day_rank>0")->field('a.id,a.cur_day_rank as rank,a.cur_day_total_win as total_win')->order('cur_day_rank asc')->limit(0, 10)->select();
    	}
    	else if ($type == 'last_day')
    	{
    		$lists = $user_model->where("last_day_rank>0")->field('a.id,a.last_day_rank as rank,a.last_day_total_win as total_win')->order('last_day_rank asc')->limit(0, 10)->select();
    	}
    	else if ($type == 'cur_month')
    	{
    		$lists = $user_model->where("cur_month_rank>0")->field('a.id,a.cur_month_rank as rank,a.cur_month_total_win as total_win')->order('cur_month_rank asc')->limit(0, 10)->select();
    	}
    	else if ($type == 'last_month')
    	{
    		$lists = $user_model->where("last_month_rank>0")->field('a.id,a.last_month_rank as rank,a.last_month_total_win as total_win')->order('last_month_rank asc')->limit(0, 10)->select();
    	}
    	
    	$my_user = $user_model->where("id=$this->userid")->find();
    	
    	$this->ajaxReturn(array('ret' => 1, 'my' => $my_user, 'info' => $lists));
    }
    
    public function ajax_get_daili1()
    {
    	$channel_db = M('channels a');
    	
    	$my_channel = $channel_db->where("admin_user_id=$this->userid")->find();
    	
    	// 获取第一级子渠道列表
    	$child_channels1 = $channel_db
    	->join('__USERS__ b on b.id=a.admin_user_id', 'left')
    	->field('a.admin_user_id')
    	->where("parent_id=" . $my_channel['id'])->select();
    	
    	$ids = $this->channels_user_ids($child_channels1);
    	$wallet_change_log_db = M('wallet_change_log a');
    	
    	$lists = array();
    	if (count($child_channels1) > 0)
    	    $lists = $wallet_change_log_db->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')->where("a.type=4 and b.user_id in ($ids)")->order('a.id desc')->field('b.user_id,b.price,b.create_time')->select();
    	
    	$this->ajaxReturn(array('ret' => 1, 'lists' => $lists));
    }
    
    public function lotterys()
    {
        $this->display(':lotterys');
    }
    
    public function drawcash()
    {
        $wallet = $this->wallet_db->where("user_id=" . $this->userid)->find();
        
        $this->assign('wallet', $wallet);

        $this->display(':drawcash');
    }
    
    public function my_channel()
    {
        $level1_childusers = 0;
        $level2_childusers = 0;
        $level3_childusers = 0;
        
        $channel_db = M('channels a');
        
        $my_channel = $channel_db->where("admin_user_id=$this->userid")->find();
        
        // 获取第一级子渠道列表
        $child_channels1 = $channel_db->where("parent_id=" . $my_channel['id'])->select();
        $child_channels2 = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')->where("b.parent_id=" . $my_channel['id'])->select();
        $child_channels3 = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')->join('__CHANNELS__ c on c.id=b.parent_id', 'left')->where("c.parent_id=" . $my_channel['id'])->select();
        
        $level1_childusers_arr = get_users_from_channels($child_channels1);
        $level2_childusers_arr = get_users_from_channels($child_channels2);
        $level3_childusers_arr = get_users_from_channels($child_channels3);
        
        $this->assign('level1_childusers', count($level1_childusers_arr));
        $this->assign('level2_childusers', count($level2_childusers_arr));
        $this->assign('level3_childusers', count($level3_childusers_arr));
        
        $this->display(':my_channel');
    }
    
    /*
    public function ajax_apply_drawcash()
    {
        $price = floatval($_REQUEST['price']);
        
        $drawcash_db = M('drawcash');
        
        // 判断是否是第一次提现
        if ($drawcash_db->where("user_id=$this->userid")->count() == 0)
        {
        	if ($price < floatval(C('DRAWCASH_FIRST_BASE_PRICE')))
        	{
        		$this->ajaxReturn(array('ret' => -1,  msg => '首次提现不能低于最低提现额度:' . C('DRAWCASH_FIRST_BASE_PRICE') . '元'));
        		return;
        	}
        }
        
        // 判断是否低于最低提现额度
        if ($price < floatval(C('DRAWCASH_BASE_PRICE')))
        {
        	$this->ajaxReturn(array('ret' => -1,  msg => '不能低于最低提现额度:' . C('DRAWCASH_BASE_PRICE') . '元'));
        }
        else
        {
            // 统计当天提现的款数
            $total_today_price = $drawcash_db->where("user_id=$this->userid and TO_DAYS(create_time)=TO_DAYS(now())")->sum('price');
            
            $need_check = 0;
            if ($total_today_price != null && $total_today_price >= floatval(C('DRAWCASH_MAX_PRICE_PER_DAY') - $price))
                $need_check = 1;

            $wallet = $drawcash_db->where("user_id=$this->userid")->find();
            
            if ($wallet['money'] > $price)
            {
                $this->ajaxReturn(array('ret' => -1, msg => '余额不足'));
            }
            else
            {
                if (!$this->wallet_db->where("user_id=$this->userid")->setDec('money', $price))
                {
                    $this->ajaxReturn(array('ret' => -1, msg => '余额不足'));
                }
                else
                {
                    $data = array(
                        'user_id' => $this->userid,
                        'price' => $price,
                        'fee' => $price * C('DRAWCASH_RATIO') / 100.0,
                        'create_time' => date('Y-m-d H:i:s'),
                        'need_check' => $need_check,
                        'status' => 0
                    );
                    
                    $drawcash_db->add($data);
                    
                    $wallet_change_log_db = M('wallet_change_log');
                    $wallet_change_log = array(
                    		'user_id' => $this->userid,
                    		'divide_ratio' => 0,
                    		'fee' => -$price,
                    		'type' => 3,
                    		'create_time' => date('Y-m-d H:i:s'),
                    		'object_id' => 0,
                    		'memo' => '提现:' . $price . '元'
                    );
                    $wallet_change_log_db->add($wallet_change_log);
                    
                    $this->ajaxReturn(array('ret' => 1));
                }
            }
        }
    }
    */
            
    public function makeSign($par, $key)
    {
        return md5(strtolower($par. $key));
    }
    
    public function apply_drawcash_with_money()
    {
    	$this->filterAttack();
    	
    	if (session ( 'time_tx_ticket' ) != null) {
    		$ticket = session ( 'time_tx_ticket' );
    		
    		if (time () - $ticket >= 0 && time () - $ticket <= 10) {
    			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    			exit();
    		}
    	}
    	
    	session ( 'time_tx_ticket', time () );
    	
    	$cookie = time() . rand(10, 999999);
    	session('time_tx_cookie', $cookie);
    	
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
    	
    	if (C('IS_STOPPED_DRAWCASH') == '1')
    	{
    		if (session('is_admin_enter') == '0')
    		{
    			$this->ajaxReturn(array('ret' => -1, 'msg' => '系统维护,暂停提现'));
    			
    			return;
    		}
    	}
    	
    	$drawcash_db = M('drawcash');
    	$count = $drawcash_db->where("user_id=$this->userid and TO_DAYS(now())=TO_DAYS(create_time) and type=0")->count();
    	
    	$reset_count = C("DRAWCASH_TIMES_PER_DAY") - $count;
    	
    	if ($reset_count <= 0)
    	{
    		$this->ajaxReturn(array('ret' => -1, 'msg' => '已经达到最大提现次数'));
    		return;
    	}
    	
    	$lottery_order_db = M('lottery_order');
    	$lottery_order_zp_db = M('zp_lottery');
    	
    	// 判断是否是首次提现
    	if ($drawcash_db->where("user_id=$this->userid and `type`=0")->find() == null)
    	{
    		if ($lottery_order_zp_db->where("user_id=$this->userid")->count() < 2)
    		{
    			$this->ajaxReturn(array('ret' => -1, 'msg' => '首次提现需要玩两把后才能提现'));
    			return;
    		}
    	}
    	
    	$money = $_REQUEST['money'];
    	$moneyType = $_REQUEST['moneyType'];
    	$ticket = $_REQUEST['ticket'];
    	$sign = $_REQUEST['sign'];
    	
    	if (!is_numeric($money))
    	{
    		// 日志
    		$action_log = M('user_action_log');
    		$log_data = array(
    				'user_id' => $this->userid,
    				'action' => 'hack',
    				'params' => '提现参数不正确:' . $moneyType . ',money:' . $money,
    				'ip' => get_client_ip(0, true),
    				'create_time' => date('Y-m-d H:i:s')
    		);
    		$action_log->add($log_data);
    		
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	$new_sign = md5('apply_drawcash_with_money' . $moneyType . $money . $ticket);
    	if ($sign != $new_sign)
    	{
    		// 日志
    		$action_log = M('user_action_log');
    		$log_data = array(
    				'user_id' => $this->userid,
    				'action' => 'hack',
    				'params' => '提现参数不正确:' . $moneyType . ',money:' . $money,
    				'ip' => get_client_ip(0, true),
    				'create_time' => date('Y-m-d H:i:s')
    		);
    		$action_log->add($log_data);
    		
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	$wallet = $this->wallet_db->where("user_id=$this->userid")->find();
    	
    	if (($moneyType != 0 && $moneyType != 1) || $money <= 0)
    	{
    		// 日志
    		$action_log = M('user_action_log');
    		$log_data = array(
    				'user_id' => $this->userid,
    				'action' => 'hack',
    				'params' => '提现参数不正确:' . $moneyType . ',money:' . $money,
    				'ip' => get_client_ip(0, true),
    				'create_time' => date('Y-m-d H:i:s')
    		);
    		$action_log->add($log_data);
    		
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	if ($moneyType == 0 && $money > $wallet['money'])
    	{
    		$this->ajaxReturn(array('ret' => -1, 'msg' => '零钱余额不足'));
    		return;
    	}
    	else if ($moneyType == 1 && $money > $wallet['money2'])
    	{
    		$this->ajaxReturn(array('ret' => -1, 'msg' => '佣金余额不足'));
    		return;
    	}
    	
    	// 查找最近一次充值记录之后,有没有投注记录
    	$recharge_db = M('recharge_order');
    	$total_recharges = $recharge_db->where("user_id=$this->userid and `status`=1")->sum('price');
    	if ($total_recharges == null)
    		$total_recharges = 0;
    		// 查找投注总金额
    		$total_lottery_price = $lottery_order_db->where("user_id=$this->userid")->sum('price');
    		
    		if ($total_lottery_price == null)
    			$total_lottery_price = 0;
    			
    			$lottery_price_ratio = floatval(C('DRAWCASH_LOTTERY_PRICE_RATIO')) / 100.0;
    			
    			if ($total_lottery_price < $total_recharges * $lottery_price_ratio)
    			{
    				$this->ajaxReturn(array('ret' => -1, 'msg' => '未完成最低投注额度' . C('DRAWCASH_LOTTERY_PRICE_RATIO') . '%,不能提现'));
    				return;
    			}
    			
    			$gourl = str_replace('&amp;', '&', C('TIXIAN_OPENID_URL'));
    			$ticket = time();
    			
    			$url = 'apply_drawcash_with_money_after' . $this->userid . $money . $moneyType . $ticket . $cookie;
    			$sign = $this->makeSign($url, C('MCH_KEY'));
    			
    			$goback= 'http://' . $_SERVER['HTTP_HOST'] . '/index.php?g=Zp&m=index&a=apply_drawcash_with_money_after&user_id=' . $this->userid . '&money=' . $money . '&moneyType=' . $moneyType. '&ticket2=' . $ticket . '&sign2=' . $sign . '&cookie=' . $cookie;
    			
    			$jsapi_ticket = time();
    			$jsapi_sign = md5(strtolower(urlencode($goback) . $jsapi_ticket . C('MCH_KEY')));
    			
    			$gourl = $gourl . '&goback=' . urlencode($goback) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign;
    			
    			$this->ajaxReturn(array('ret' => 1, 'gourl' => $gourl));
    }
    
    public function apply_drawcash_with_money_after()
    {
    	$this->filterAttack();
    	
    	if (session ( 'time_tx_ticket2' ) != null) {
    		$ticket = session ( 'time_tx_ticket2' );
    		
    		if (time () - $ticket >= 0 && time () - $ticket <= 10) {
    			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    			exit();
    		}
    	}
    	
    	session ( 'time_tx_ticket2', time () );
    	
    	$moneyType = intval($_REQUEST['moneyType']);
    	
    	$drawcash_db = M('drawcash');
    	
    	$wallet_change_log_db = M('wallet_change_log');
    	
    	$user_id = $_REQUEST['user_id'];
    	$ticket2 = $_REQUEST['ticket2'];
    	$sign2 = $_REQUEST['sign2'];
    	$ticket = $_REQUEST['ticket'];
    	$sign = $_REQUEST['sign'];
    	$noncestr = $_REQUEST['noncestr'];
    	$money = $_REQUEST['money'];
    	$cookie = $_REQUEST['cookie'];
    	
    	$url = 'apply_drawcash_with_money_after' . $user_id . $money . $moneyType . $ticket2 . $cookie;
    	$new_sign2 = $this->makeSign($url, C('MCH_KEY'));
    	
    	$openid = $_REQUEST['openid'];
    	$appid = $_REQUEST['appid'];
    	$url = $appid . $openid . $ticket . $noncestr;
    	$new_sign = $this->makeSign($url, C('LOGIN_KEY'));
    	
    	if ($new_sign2 != $sign2)
    	{
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	if ($new_sign != $sign)
    	{
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	// 判断cookie是否已经用过
    	if ($cookie != session('time_tx_cookie'))
    	{
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}
    	
    	$_SESSION['time_tx_cookie'] = '';
    	
    	if (C('IS_STOPPED_DRAWCASH') == '1')
    	{
    		if (session('is_admin_enter') == '0')
    		{
    			$this->assign('tips', '系统维护,暂停提现');
    			$this->display(':error');
    			return;
    		}
    	}
    	
    	$user_db = M('users');
    	$user = $user_db->where("id=$user_id")->find();
    	
    	$count = $drawcash_db->where("user_id=$user_id and TO_DAYS(now())=TO_DAYS(create_time) and `type`=0")->count();
    	
    	$reset_count = C("DRAWCASH_TIMES_PER_DAY") - $count;
    	
    	if ($reset_count <= 0)
    	{
    		$this->assign('tips', '今日提现次数已用完');
    		$this->display(':error');
    		return;
    	}
    	
    	if ($moneyType == 0)
    		$wallet_money = $this->wallet_db->where("user_id=$user_id")->getField('money');
    		else
    			$wallet_money = $this->wallet_db->where("user_id=$user_id")->getField('money2');
    			
    			if ($money > $wallet_money)
    			{
    				$this->assign('tips', '余额不足');
    				$this->display(':error');
    				return;
    			}
    			
    			// 判断是否是第一次提现
    			if ($drawcash_db->where("user_id=$user_id")->count() == 0)
    			{
    				if ($money< floatval(C('DRAWCASH_FIRST_BASE_PRICE')))
    				{
    					$this->assign('tips', '首次提现不能低于最低提现额度:' . C('DRAWCASH_FIRST_BASE_PRICE') . '元');
    					$this->display(':error');
    					return;
    				}
    			}
    			
    			// 判断是否低于最低提现额度
    			if ($money< floatval(C('DRAWCASH_BASE_PRICE')))
    			{
    				$this->assign('tips', '不能低于最低提现额度:' . C('DRAWCASH_BASE_PRICE') . '元');
    				$this->display(':error');
    				return;
    			}
    			else
    			{
    				// 统计当天提现的款数
    				$total_today_prices = $drawcash_db->where("user_id=$user_id and TO_DAYS(create_time)=TO_DAYS(now())")->sum('price');
    				
    				//if ($total_today_prices == null)
    				//$total_today_prices = 0;
    				
    				$total_today_prices += $price;
    				
    				$check_tips = '无';
    				$need_check = 0;
    				/*
    				 if ($total_today_prices >= floatval(C('DRAWCASH_MAX_PRICE_PER_DAY')))
    				 {
    				 $check_tips = '超过每日最大金额:' . $total_today_prices . ',限制:' . C('DRAWCASH_MAX_PRICE_PER_DAY');
    				 
    				 $need_check = 1;
    				 }*/
    				
    				if ($user['user_drawcash_status_disable'] == 1)
    				{
    					$check_tips = '状态为需要审核';
    					
    					$need_check = 1;
    				}
    				
    				// 判断用户充值数
    				$total_recharges = $wallet_change_log_db->where("user_id=$user_id and `type`=0")->sum('fee');
    				
    				if ($total_recharges == null)
    					$total_recharges = 0;
    				
    					if (floatval(C('DRAWCASH_MAX_PRICE_PER_DAY')) <= $money)
    						$need_check = 1;
    					
    					/*
    					 if ($total_recharges + floatval(C('DRAWCASH_OVER_RECHARGES_NEED_CHECK')) <= $total_today_prices)
    					 $need_check = 1;
    					 */
    					 $wallet = $drawcash_db->where("user_id=$user_id")->find();
    					 
    					 $ret = false;
    					 
    					 if ($moneyType == 0)
    					 	$ret = $this->wallet_db->where("user_id=$user_id and money>=$money")->setDec('money', $money);
    					 	else
    					 		$ret = $this->wallet_db->where("user_id=$user_id and money2>=$money")->setDec('money2', $money);
    					 		
    					 		if (!$ret)
    					 		{
    					 			$this->assign('tips', '余额不足');
    					 			$this->display(':error');
    					 			return;
    					 		}
    					 		else
    					 		{
    					 			$action_log = M('user_action_log');
    					 			$log_data = array(
    					 					'user_id' => $user_id,
    					 					'action' => 'apply_drawcash',
    					 					'params' => '钱包余额:' . $wallet['money'] . ',佣金:' . $wallet['money2'],
    					 					'ip' => get_client_ip(0, true),
    					 					'create_time' => date('Y-m-d H:i:s')
    					 			);
    					 			$action_log->add($log_data);
    					 			
    					 			$data = array(
    					 					'user_id' => $user_id,
    					 					'price' => $money,
    					 					'openid' => $openid,
    					 					'fee' => $money * C('DRAWCASH_RATIO') / 100.0,
    					 					'create_time' => date('Y-m-d H:i:s'),
    					 					'need_check' => $need_check,
    					 					'check_tips' => $check_tips,
    					 					'status' => 0
    					 			);
    					 			
    					 			$rst = $drawcash_db->add($data);
    					 			
    					 			$wallet_change_log_db = M('wallet_change_log');
    					 			$wallet_change_log = array(
    					 					'user_id' => $user_id,
    					 					'divide_ratio' => 0,
    					 					'fee' => -$money,
    					 					'type' => 3,
    					 					'create_time' => date('Y-m-d H:i:s'),
    					 					'object_id' => 0,
    					 					'memo' => '提现:' . $money. '元,类型:' . $moneyType
    					 			);
    					 			
    					 			$wallet_change_log_db->add($wallet_change_log);
    					 			
    					 			if ($need_check == 1)
    					 			{
    					 				$this->assign('tips', '该笔提现审核后会自动打款');
    					 				$this->display(':error');
    					 			}
    					 			else
    					 				redirect('index.php?g=Zp&m=index&a=main');
    					 				return;
    					 		}
    			}
    }
    
    public function get_user()
    {
    	$wallet = $this->wallet_db->where("user_id=" . $this->userid)->find();
    	
    	$this->ajaxReturn(array('ret' => 1, 'user' => $this->user, 'wallet' => $wallet));
    }
    
    public function check_drawcash()
    {
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
         
        if (C('IS_STOPPED_DRAWCASH') == '1')
        {
            if (session('is_admin_enter') == '0')
            {
                $this->ajaxReturn(array('ret' => -1, 'msg' => '系统维护,暂停提现'));
                 
                return;
            }
        }
        
        $drawcash_db = M('drawcash');
        
        $count = $drawcash_db->where("user_id=$this->userid and TO_DAYS(now())=TO_DAYS(create_time)")->count();
        
        $reset_count = C("DRAWCASH_TIMES_PER_DAY") - $count;
        
        $msg = '';
        if ($reset_count > 0)
        	$ret = 1;
        else
        {
        	$ret = -1;
        	$msg = '兑换次数已用完';
        }
        
        
        $this->ajaxReturn(array('ret' => $ret, 'msg' => $msg, 'reset_count' => $reset_count));
    }
    
    public function ajax_get_open_lottery_pig_result()
    {
    	$this->filterAttack();
    	
        $auto_mark = true;
        if (isset($_REQUEST['not_auto_mark']))
            $auto_mark = false;
        
        // 获取最新的投注信息,0-开启投注,1-关闭投注,2-出结果(没有最新的投注信息)
        $lottery_order_db = M('lottery_order a');
        $lottery = $lottery_order_db
        ->join('__LOTTERY__ b on b.id=a.lottery_id')
        ->where("a.user_id=$this->userid and b.status in (1,2)")
        ->order("b.open_time desc")
        ->field('b.id, b.no, b.number, b.num3, b.create_time, b.open_time, b.type, a.buy_type, b.status,a.win as total_win,a.price as total_price, a.is_read, timestampdiff(SECOND,now(), b.open_time) as diff')
        ->find();
        
        if ($lottery != null)
        {
            if ($lottery['status'] == 2)
            {
                if ($lottery['is_read'] == 0 && $auto_mark)
                    $lottery_order_db->where("no='" . $lottery['no'] . "'")->setField('is_read', 1);

                $result = array(
                    'buy_count' => 1,
                    'is_win' => $lottery['total_win'] > 0 ? 1 : 0,
                    'total_price' => $lottery['total_price'],
                    'total_win' => $lottery['total_win'],
                );
                 
                $this->ajaxReturn(array('ret' => 1, 'lottery' => $lottery, 'result' => $result));
            }
            else
            {
                // 还没有开奖,等待开奖
                $this->ajaxReturn(array('ret' => 2, 'lottery' => $lottery));
            }
        }
        else
        {
            $this->ajaxReturn(array('ret' => -1));
        }
    }
    
    public function ajax_mark_lottery_pig_result_read($no)
    {
        // 获取最新的投注信息,0-开启投注,1-关闭投注,2-出结果(没有最新的投注信息)
        $lottery_order_db = M('lottery_order a');
        $lottery_order_db->where("no='$no' and user_id=$this->userid")->setField('is_read', 1);
        
        $this->ajaxReturn(array('ret' => 1));
    }
    
    public function ajax_get_lotterys_pig()
    {
    	$this->filterAttack();
    	
        $firstRow = 0;
        $limitRows = 50;
    
        if (isset($_REQUEST['firstRow']))
            $firstRow = intval($_REQUEST['firstRow']);
        if (isset($_REQUEST['limitRows']))
            $limitRows= intval($_REQUEST['limitRows']);
    
        $lottery_history = $this->lottery_db->alias('a')
        ->join('__LOTTERY_ORDER__ b on b.no=a.no', 'left')
        ->where("a.status=2 and b.user_id=$this->userid")
        ->order("a.open_time desc")
        ->limit($firstRow, $limitRows)
        ->field('a.id, a.no, a.number, a.num3, a.create_time, a.open_time, a.type, a.status')
        ->select();
    
        $this->ajaxReturn(array('ret' => 1, 'lottery_history' => $lottery_history));
    }
    
    //判断两天是否相连
    function isStreakDays($last_date,$this_date){
    
        if(($last_date['year']===$this_date['year'])&&($this_date['yday']-$last_date['yday']===1)){
            return TURE;
        }elseif(($this_date['year']-$last_date['year']===1)&&($last_date['mon']-$this_date['mon']=11)&&($last_date['mday']-$this_date['mday']===30)){
            return TURE;
        }else{
            return FALSE;
        }
    }
    //判断两天是否是同一天
    function isDiffDays($last_date,$this_date){
    
        if(($last_date['year']===$this_date['year'])&&($this_date['yday']===$last_date['yday'])){
            return FALSE;
        }else{
            return TRUE;
        }
    }
    
    // 判断有没有签到
    public function is_signin ()
    {
        $db=M('signin');
    
        $sign_data = $db->where('user_id=' . $this->userid)->find();
    
        if ($sign_data)
        {
            // 判断时间是否是连续
            $last_signin_date = getdate(strtotime($sign_data['signin_time']));
            $cur_date = getdate(strtotime(date("Y-m-d H:i:s")));
    
            if (!$this->isDiffDays($last_signin_date, $cur_date))
            {
                $ret['code'] = -1;
                $ret['msg'] = '已经签到!';
    
                echo json_encode($ret);
    
                return;
            }
        }
    
        echo json_encode(array('code' => 0));
    }
    
    // 签到
    public function ajax_signin()
    {
        // 获取最近签到数据
        $db=M('signin');
        $wallet_change_log_db = M('wallet_change_log');
        $sign_data = $db->where('user_id=' . $this->userid)->find();
    
        if ($sign_data)
        {
            // 判断时间是否是连续
            $last_signin_date = getdate(strtotime($sign_data['signin_time']));
            $cur_date = getdate(strtotime(date("Y-m-d H:i:s")));
    
            if (!$this->isDiffDays($last_signin_date, $cur_date))
            {
                $ret['code'] = -1;
                $ret['msg'] = '已经签到!';
    
                return $this->ajaxReturn($ret);
            }
            else
            {
    
                if ($this->isStreakDays($last_signin_date, $cur_date))
                {
                    $extra = false;
                    $days = $sign_data['signin_day'] + 1;
    
                    if ($days >= 30)
                    {
                        $extra = true;
                        $days = 1;
                    }
    
                    $data=array(
                        'user_id' => $this->userid,
                        'signin_day' => $days,
                        'signin_time' => date("Y-m-d H:i:s")
                    );
    
                    $db->save($data);
    
                    $sign_core = floatval(C('SIGN_BONUS'));
                    
                    $this->wallet_db->where("user_id=$this->userid")->setInc('money3', $sign_core);
                    
                    $wallet_change_log = array(
                        'user_id' => $this->userid,
                        'divide_ratio' => 0,
                        'fee' => $sign_core,
                        'type' => 6,
                        'create_time' => date('Y-m-d H:i:s'),
                        'object_id' => 0,
                        'memo' => '签到:' . $sign_core . '元'
                    );
                    $wallet_change_log_db->add($wallet_change_log);                    

                    $ret['code'] = 0;
                    $ret['signin_day'] = $days;
                    $ret['signin_bonus'] = $sign_core;
                    return $this->ajaxReturn($ret);
                }
                else
                {
                    $data=array(
                        'user_id' => $this->userid,
                        'signin_day' => 1,
                        'signin_time' => date("Y-m-d H:i:s")
                    );
    
                    $db->save($data);
                    
                    // 签到积分
                    $sign_core = floatval(C('SIGN_BONUS'));
                    
                    $this->wallet_db->where("user_id=$this->userid")->setInc('money3', $sign_core);
                    
                    $wallet_change_log = array(
                        'user_id' => $this->userid,
                        'divide_ratio' => 0,
                        'fee' => $sign_core,
                        'type' => 6,
                        'create_time' => date('Y-m-d H:i:s'),
                        'object_id' => 0,
                        'memo' => '签到:' . $sign_core . '元'
                    );
                    $wallet_change_log_db->add($wallet_change_log);                    
    
                    $ret['code'] = 0;
    
                    $ret['signin_day'] = 1;
                    $ret['signin_bonus'] = $sign_core;
                    return $this->ajaxReturn($ret);
                }
            }
        }
        else
        {
            $data=array(
                'user_id' => $this->userid,
                'signin_day' => 1,
                'signin_time' => date("Y-m-d H:i:s")
            );
    
            $db->add($data);
    
            // 签到积分
            $sign_core = floatval(C('SIGN_BONUS'));
    
            $this->wallet_db->where("user_id=$this->userid")->setInc('money3', $sign_core);
            
            $wallet_change_log = array(
                'user_id' => $this->userid,
                'divide_ratio' => 0,
                'fee' => $sign_core,
                'type' => 6,
                'create_time' => date('Y-m-d H:i:s'),
                'object_id' => 0,
                'memo' => '签到:' . $sign_core . '元'
            );
            $wallet_change_log_db->add($wallet_change_log);            
    
            $ret['code'] = 0;
            $ret['signin_day'] = 1;
            $ret['signin_bonus'] = $sign_core;
            return $this->ajaxReturn($ret);
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
            'type' => 8,
            'divide_ratio' => $divide_ratio / 100.0,
            'fee' => $comission_fee,
            'create_time' => date ( 'Y-m-d H:i:s' ),
            'memo' => '下线投注的佣金'
        );
    
        $db->add ( $data );
    }
    
    // 处理佣金
    function process_commision($order) {
            $channel_db = M ( 'channels b' );
    
        // 有效日期
        $n = C ( 'COMMISION_VALID_TIME' );
    
        $from_start_time = date ( "Y-m-d H:i:s", time () - $n * 3600 );
    
        $my_channel = $channel_db->where ( "b.admin_user_id=" . $order ['user_id'] . " and b.create_time>='" . $from_start_time . "'" )->find ();
    
        // 超过限制了
        if ($my_channel == null)
            return;
    
        $fee = floatval ( $order ['buy_price'] ); // - floatval($order['win']);
    
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
    
    private function randFloat($min=0, $max=1){
        return $min + mt_rand()/mt_getrandmax() * ($max-$min);
    }

    // 转盘逻辑
    public function create_lottery_order()
    {
    	$this->filterAttack();
    	
        $discType = $_REQUEST['discType'];
        $price = intval($_REQUEST['price']);
        $ticket = $_REQUEST['ticket'];
        $sign = $_REQUEST['sign'];
        
        $new_sign = md5('zp_lottery' . $discType . $price . $ticket);
        
        if ($sign != $new_sign)
        {
            $action_log = M('user_action_log');
            $log_data = array(
                'user_id' => $this->userid,
                'action' => 'hack',
                'params' => '下注参数不正确:' . $discType . ',price:' . $price,
                'ip' => get_client_ip(0, true),
                'create_time' => date('Y-m-d H:i:s')
            );
            $action_log->add($log_data);
            
            return $this->ajaxReturn(array('code' => -2));
        }
        
        // 判断是否暂停投注
        if (C ( 'IS_STOPPED_LOTTERY' ) == '1') {
        	if (session ( 'is_admin_enter' ) == '0') {
        		echo json_encode ( array (
        				'ret' => -1,
        				'msg' => '系统维护,暂停投注'
        		) );
        		return;
        	}
        }
        
        $is_finded = false;
        $prices_arr = [2, 5, 10, 30, 100, 300, 1000, 2000];
        
        for ($i=0; $i<count($prices_arr); $i++)
        {
            if ($prices_arr[$i] == $price)
            {
                $is_finded = true;
                break;
            }
        }
        
        if (! $is_finded) {
            // 日志
            $action_log = M ( 'user_action_log' );
            $log_data = array (
                'user_id' => $this->userid,
                'action' => 'hack',
                'params' => '下注金额不正确:' . $price,
                'ip' => get_client_ip ( 0, true ),
                'create_time' => date ( 'Y-m-d H:i:s' )
            );
            $action_log->add ( $log_data );
            	
            return $this->ajaxReturn(array('code' => -2));
        }
        
        $wallet_db = M('wallet');
        $wallet = $wallet_db->where ( "user_id=" . $this->userid )->find ();
        
		$total_price = $price;
		
		$det = $wallet ['money'] - $total_price;
		$money_det = $wallet ['money'] - $price;
		
		if ($det < 0 || $money_det < 0)
			echo json_encode ( array (
					'ret' => - 1,
					'msg' => '余额不足' 
			) );
		else {
		    $ret = $this->wallet_db->where ( "user_id=" . $this->userid )->setDec ( 'money', $total_price );
		    
		    if ($ret <= 0)
		        echo json_encode ( array (
		            'ret' => - 1,
		            'msg' => '余额不足'
		        ) );
		    else {
		        
		        $types_arr = [
		            "小盘",
		            "中盘",
		            "大盘"
		        ];
		        
		        $zp_lottery_db = M('zp_lottery');
		        
		        $weight_db = M('zp_weight');
		        
		        $weight = $weight_db->where("type=$discType and slot=" . C('ZP_CONTROL_METHOD_PRICE_' . $price))->find();
		        
		        $total_weight = 0;
		        for ($i = 1; $i <= 12; $i ++)
		            $total_weight += $weight['weight_' . $i];
		        
		        $cur_weight = $this->randFloat();
		        
		        
		        $d1 = 0;
		        $d2 = 0;
		        $rand_index = 0;
		        for ($i = 0; $i < 12; $i ++) {
		            $d2 += $weight['weight_' . ($i + 1)] / $total_weight;
		            if ($i == 0)
		                $d1 = 0;
		            else
		                $d1 += $weight['weight_' . $i] / $total_weight;
		            if ($cur_weight >= $d1 && $cur_weight <= $d2) {
		                $rand_index = $i;
		                break;
		            }
		        }
		
		        $win_db = M('zp_win');
		        
		        $status = 1; // 0-未开奖,1-中奖,2-未中奖
		        $prize_id = $rand_index + 1;
		        $win_table = $win_db->where("price=$price and prize_id=$prize_id")->find();
		        $win_prize = $win_table['win' . $discType]; // 中奖
		        $data = array(
		            'user_id' => $this->userid,
		            'buy_price' => $price,
		            'status' => $status,
		            'prize_id' => $prize_id,
		            'type' => $discType,
		            'win' => $win_prize,
		            'create_time' => date('Y-m-d H:i:s')
		        );
		        $data['id'] = $zp_lottery_db->add($data);
		        
		        $action_log = M ( 'user_action_log' );
		        $log_data = array (
		            'user_id' => $this->userid,
		            'action' => 'buy_zp_lottery',
		            'params' => '类型:' . $types_arr[$discType] . ',金额:' . $price . ',订单:' . $data ['id'],
		            'ip' => get_client_ip ( 0, true ),
		            'create_time' => date ( 'Y-m-d H:i:s' )
		        );
		        $action_log->add ( $log_data );
		        
		        $change_db = M ( 'wallet_change_log' );
		        
		        $change_data = array (
		            'user_id' => $this->userid,
		            'object_id' => $data ['id'],
		            'type' => 1,
		            'fee' => $total_price,
		            'create_time' => date ( 'Y-m-d H:i:s' ),
		            'memo' => '投注'
		        );
		        
		        $change_db->add ( $change_data );
		        
		        // 计算佣金
		        $this->process_commision($data);
		        
		        // 如果中奖了
		        if ($status == 1)
		        {
		            $wallet_db->where("user_id=" . $this->userid)->setInc('money', $win_prize);
		            
		            $change_db = M ( 'wallet_change_log' );
		            
		            $change_data = array (
		                'user_id' => $this->userid,
		                'object_id' => $data ['id'],
		                'type' => 2,
		                'fee' => $win_prize,
		                'create_time' => date ( 'Y-m-d H:i:s' ),
		                'memo' => '中奖:' . $cur_weight
		            );
		            
		            $change_db->add ( $change_data );
		            
		            $this->ajaxReturn(array('ret' => 1, 'is_win' => 1, 'prize_id' => $prize_id, 'win_prize' => $win_prize));
		        }
		        else
		        {
		            $this->ajaxReturn(array('ret' => 1, 'is_win' => 0, 'prize_id' => $prize_id, 'win_prize' => 0));
		        }
		    }
		}
    }
}