<?php
namespace Qqonline\Controller;

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
	
	public function StopAttack2($StrFiltKey,$StrFiltValue,$ArrFiltReq)
	{
		//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|union|into|load_file|outfile/';
		//$pregs = '/select|insert|drop|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
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
	
	public function filterAttack2()
	{
		foreach($_GET as $key=>$value)
		{
			$this->StopAttack2($key,$value,$getfilter);
		}
		foreach($_POST as $key=>$value)
		{
			$this->StopAttack2($key,$value,$postfilter);
		}
		foreach($_COOKIE as $key=>$value)
		{
			$this->StopAttack2($key,$value,$cookiefilter);
		}
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
		
		$this->assign('recharge_prices', C('RECHARGE_PRICES'));
		$this->assign('lottery_single_price', C('LOTTERY_SINGLE_PRICE'));
		$this->assign('lottery_ratio', $lottery_ratio);
		$this->assign('lastest_lottery', $lastest_lottery);
		$this->assign('wallet', $wallet);
		
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
    	$servicer = $servicer_db->where()->order("id desc")->find();
    	
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
        $lottery_order_db = M('lottery_order a');
        
        $lists = $lottery_order_db
            ->join('__USERS__ b on b.id=a.user_id', 'left')
            ->where("a.win>0")
            ->field('a.*,b.user_activation_key')
            ->order("a.id desc")
            ->limit(0, 20)->select();
        
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
        
        $lottery_order_db = M('lottery_order');
        $lottery['buy_lists'] = $lottery_order_db->where("no='$no' and user_id=" . $this->userid)->select();        
        
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
    	
    	//$my_channel = $channel_db->where("admin_user_id=$this->userid")->find();
    	
    	$wallet_change_log_db = M('wallet_change_log a');
    	
    	// 获取第一级子渠道列表
    	/*
    	$child_channels1 = $channel_db
    	->join('__USERS__ b on b.id=a.admin_user_id', 'left')
    	->field('a.admin_user_id')
    	->where("parent_id=" . $my_channel['id'])->select();
    	
    	$ids = $this->channels_user_ids($child_channels1);
    	$wallet_change_log_db = M('wallet_change_log a');
    	
    	$lists = array();
    	if (count($child_channels1) > 0)
    	{
    		
    		//$lists = $wallet_change_log_db->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')->where("a.type=4 and b.user_id in ($ids)")->order('a.id desc')->distinct(true)->field('b.id,b.user_id,b.price,b.create_time')->limit(0, 20)->select();
    		$lists = $wallet_change_log_db->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')->where("a.type=4 and b.user_id in ($ids)")->order('a.id desc')->field('b.id,b.user_id,b.price,b.create_time')->limit(0, 10)->select();
    	}
    	*/
    	
    	$lists = $wallet_change_log_db->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')->where("a.type=4 and a.user_id=$this->userid")->order('a.id desc')->field('b.id,b.user_id,b.price,b.create_time')->limit(0, 10)->select();
    	
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
    		if ($lottery_order_db->where("user_id=$this->userid")->count() < 2)
    		{
    			if ($lottery_order_zp_db->where("user_id=$this->userid")->count() < 2)
    			{
    				$this->ajaxReturn(array('ret' => -1, 'msg' => '首次提现需要玩两把后才能提现'));
    				return;
    			}
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
    			
    			$index = 0;//rand(0, 1);
    			$key = '';
    			if ($index == 0)
    			{
    				$gourl = str_replace('&amp;', '&', C('TIXIAN_OPENID_URL'));
    				$key = C('MCH_KEY');
    			}
    			else
    			{
    				$gourl = str_replace('&amp;', '&', C('TIXIAN_OPENID_URL2'));
    				$key = C('MCH_KEY2');
    			}
    			$ticket = time();
    			
    			$url = 'apply_drawcash_with_money_after' . $this->userid . $money . $moneyType . $ticket . $cookie;
    			$sign = $this->makeSign($url, $key);
    			
    			$goback= 'http://' . $_SERVER['HTTP_HOST'] . '/index.php?g=Qqonline&m=index&a=apply_drawcash_with_money_after&user_id=' . $this->userid . '&money=' . $money . '&moneyType=' . $moneyType. '&ticket2=' . $ticket . '&sign2=' . $sign . '&cookie=' . $cookie;
    			
    			$jsapi_ticket = time();
    			if ($index == 0)
    				$jsapi_sign = md5(strtolower(urlencode($goback) . $jsapi_ticket . C('MCH_KEY')));
    			else
    				$jsapi_sign = md5(strtolower(urlencode($goback) . $jsapi_ticket . C('MCH_KEY')));
    			
    			$gourl = $gourl  . '&index=' . $index . '&goback=' . urlencode($goback) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign;
    			
    			$this->ajaxReturn(array('ret' => 1, 'gourl' => $gourl));
    }
    
    public function apply_drawcash_with_money_after()
    {
    	$this->filterAttack2();
    	
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
    	
    	if ($openid == NULL || $openid == '')
    	{
    		redirect('index.php?g=Pig&m=index&a=index');
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
    					
    					/*
    					if ($total_recharges + floatval(C('DRAWCASH_OVER_RECHARGES_NEED_CHECK')) <= $total_today_prices)
    						$need_check = 1;
    						*/
    						$wallet = $drawcash_db->where("user_id=$user_id")->find();
    						
    						if (floatval(C('DRAWCASH_MAX_PRICE_PER_DAY')) <= $money)
    						{
    							$check_tips = '超过:' . C('DRAWCASH_MAX_PRICE_PER_DAY');
    							$need_check = 1;
    						}
    						
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
    									'appid' => $appid,
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
    								redirect('index.php?g=Pig&m=index&a=index');
    								return;
    						}
    	}
    }
            
    public function apply_drawcash2()
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
    	
    	if ($reset_count <= 0)
    	{
    		$this->ajaxReturn(array('ret' => -1, 'msg' => '已经达到最大提现次数'));
    		return;
    	}
    	

    	// 查找最近一次充值记录之后,有没有投注记录
    	$recharge_db = M('recharge_order');
    	$total_recharges = $recharge_db->where("user_id=$this->userid and `status`=1")->sum('price');
    	if ($total_recharges == null)
    	    $total_recharges = 0;
    	// 查找投注总金额
    	$lottery_order_db = M('lottery_order');
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

    	$url = 'apply_drawcash2_after' . $this->userid . $price . $ticket;
    	$sign = $this->makeSign($url, C('MCH_KEY'));

    	$goback= 'http://' . $_SERVER['HTTP_HOST'] . '/index.php?g=Qqonline&m=index&a=apply_drawcash2_after&user_id=' . $this->userid  . '&ticket2=' . $ticket . '&sign2=' . $sign;

    	$jsapi_ticket = time();
    	$jsapi_sign = md5(strtolower(urlencode($goback) . $jsapi_ticket . C('MCH_KEY')));
    	
    	$gourl = $gourl . '&goback=' . urlencode($goback) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign;
    	
    	$this->ajaxReturn(array('ret' => 1, 'gourl' => $gourl));
    }
    
    public function apply_drawcash2_after()
    {
    	$this->filterAttack();
    	
    	$moneyType = intval($_REQUEST['moneyType']);
    	
    	$drawcash_db = M('drawcash');
    	
    	$wallet_change_log_db = M('wallet_change_log');
    	
    	$user_id = $_REQUEST['user_id'];
    	$ticket2 = $_REQUEST['ticket2'];
    	$sign2 = $_REQUEST['sign2'];
    	$ticket = $_REQUEST['ticket'];
    	$sign = $_REQUEST['sign'];
    	$noncestr = $_REQUEST['noncestr'];
    	    	
    	$url = 'apply_drawcash2_after' . $user_id . $ticket2;
    	$new_sign2 = $this->makeSign($url, C('MCH_KEY'));

    	$openid = $_REQUEST['openid'];
    	$url = $openid . $ticket . $noncestr;
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
    	
    	$count = $drawcash_db->where("user_id=$user_id and TO_DAYS(now())=TO_DAYS(create_time)")->count();
    	
    	$reset_count = C("DRAWCASH_TIMES_PER_DAY") - $count;
    	
    	if ($reset_count <= 0)
    	{
    		$this->assign('tips', '今日提现次数已用完');
    	    $this->display(':error');
    		return;
    	}
    	
    	$price = $this->wallet_db->where("user_id=$user_id")->getField('money');
    	
    	// 判断是否是第一次提现
    	if ($drawcash_db->where("user_id=$user_id")->count() == 0)
    	{
    		if ($price < floatval(C('DRAWCASH_FIRST_BASE_PRICE')))
    		{
    			$this->assign('tips', '首次提现不能低于最低提现额度:' . C('DRAWCASH_FIRST_BASE_PRICE') . '元');
    			$this->display(':error');
    			return;
    		}
    	}
    	
    	// 判断是否低于最低提现额度
    	if ($price < floatval(C('DRAWCASH_BASE_PRICE')))
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
    			
    			$check_tips = '';
    			
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
    			
    				/*
    				if ($total_recharges + floatval(C('DRAWCASH_OVER_RECHARGES_NEED_CHECK')) <= $total_today_prices)
    				{
    					$check_tips = '充值数低于:' . 
    					$need_check = 1;
    				}
    				*/
    				
    				$wallet = $drawcash_db->where("user_id=$user_id")->find();
    				
    				if (!$this->wallet_db->where("user_id=$user_id and money>=$price")->setDec('money', $price))
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
    							'params' => '钱包余额:' . $price,
    							'ip' => get_client_ip(0, true),
    							'create_time' => date('Y-m-d H:i:s')
    					);
    					$action_log->add($log_data);
    					
    					$data = array(
    							'user_id' => $user_id,
    							'price' => $price,
    							'openid' => $openid,
    							'fee' => $price * C('DRAWCASH_RATIO') / 100.0,
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
    							'fee' => -$price,
    							'type' => 3,
    							'create_time' => date('Y-m-d H:i:s'),
    							'object_id' => 0,
    							'memo' => '提现:' . $price . '元'
    					);
    					
    					$wallet_change_log_db->add($wallet_change_log);
    					
    					if ($need_check == 1) 
    					{
    						$this->assign('tips', '该笔提现审核后会自动打款:' . $check_tips);
    					    $this->display(':error');
    					}
    					else
    						redirect('index.php?g=Pige&m=index&a=index');
    					return;
    				}
    	}
    }
    
    public function apply_drawcash3()
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
    	
    	if ($reset_count <= 0)
    	{
    		$this->ajaxReturn(array('ret' => -1, 'msg' => '已经达到最大提现次数'));
    		return;
    	}
    	
    	$gourl = str_replace('&amp;', '&', C('TIXIAN_OPENID_URL'));
    	$ticket = time();
    	
    	$url = 'apply_drawcash3_after' . $this->userid . $ticket;
    	$sign = $this->makeSign($url, C('MCH_KEY'));
    	
    	$goback = str_replace("{hostname}", $_SERVER['HTTP_HOST'], $goback);
    	$goback= 'http://' . $_SERVER['HTTP_HOST'] . '/index.php?g=Qqonline&m=index&a=apply_drawcash3_after&user_id=' . $this->userid . '&ticket2=' . $ticket . '&sign2=' . $sign;
    	
    	$jsapi_ticket = time();
    	$jsapi_sign = md5(strtolower(urlencode($goback) . $jsapi_ticket . C('MCH_KEY')));
    	 
    	$gourl = $gourl . '&goback=' . urlencode($goback) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign;
    	
    	$this->ajaxReturn(array('ret' => 1, 'gourl' => $gourl));
    }
    
    public function apply_drawcash3_after()
    {
    	$this->filterAttack();
    	
    	$moneyType = intval($_REQUEST['moneyType']);
    	
    	$drawcash_db = M('drawcash');
    	
        $user_id = $_REQUEST['user_id'];
    	$ticket2 = $_REQUEST['ticket2'];
    	$sign2 = $_REQUEST['sign2'];
    	$ticket = $_REQUEST['ticket'];
    	$sign = $_REQUEST['sign'];
    	$noncestr = $_REQUEST['noncestr'];
    	    	
    	$url = 'apply_drawcash3_after' . $user_id . $ticket2;
    	$new_sign2 = $this->makeSign($url, C('MCH_KEY'));

    	$openid = $_REQUEST['openid'];
    	$url = $openid . $ticket . $noncestr;
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
    	
    	$price = $this->wallet_db->where("user_id=$user_id")->getField('money2');
    	
    	// 判断是否是第一次提现
    	if ($drawcash_db->where("user_id=$this->userid")->count() == 0)
    	{
    		if ($price < floatval(C('DRAWCASH_FIRST_BASE_PRICE')))
    		{
    			$this->assign('tips', '首次提现不能低于最低提现额度:' . C('DRAWCASH_FIRST_BASE_PRICE') . '元');
    			$this->display(':error');
    			return;
    		}
    	}
    	
    	// 判断是否低于最低提现额度
    	if ($price < floatval(C('DRAWCASH_BASE_PRICE')))
    	{
    		$this->assign('tips', '不能低于最低提现额度:' . C('DRAWCASH_BASE_PRICE') . '元');
    		$this->display(':error');
    	}
    	else
    	{
    		// 统计当天提现的款数
    		$total_today_prices = $drawcash_db->where("user_id=$user_id and TO_DAYS(create_time)=TO_DAYS(now())")->sum('price');
    		
    		if ($total_today_prices == null)
    			$total_today_prices = 0;
    			
    			$total_today_prices += $price;
    			
    			
    			$check_tips = '';
    			
    			$need_check = 0;
    			/*
    			if ($total_today_prices >= floatval(C('DRAWCASH_MAX_PRICE_PER_DAY')))
    			{
    				$check_tips = '超过每日最大金额:' . $total_today_prices . ',限制:' . C('DRAWCASH_MAX_PRICE_PER_DAY');
    				
    				$need_check = 1;
    			}
    			*/
    			
    			if ($user['user_drawcash_status_disable'] == 1)
    			{
    				$check_tips = '用户被限制自动提现';
    				
    				$need_check = 1;    			
    			}
    				
    				$wallet = $drawcash_db->where("user_id=$user_id")->find();
    				
    				if (!$this->wallet_db->where("user_id=$user_id and money2>=$price")->setDec('money2', $price))
    				{
    					$this->assign('tips', '余额不足');
    					$this->display(':error');
    				} else {
    					
    					$action_log = M('user_action_log');
    					$log_data = array(
    							'user_id' => $user_id,
    							'action' => 'apply_drawcash2',
    							'params' => '佣金余额:' . $price,
    							'ip' => get_client_ip(0, true),
    							'create_time' => date('Y-m-d H:i:s')
    					);
    					$action_log->add($log_data);
    					
    					$data = array(
    							'user_id' => $user_id,
    							'price' => $price,
    							'openid' => $openid,
    							'fee' => $price * C('DRAWCASH_RATIO') / 100.0,
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
    							'fee' => - $price,
    							'type' => 3,
    							'create_time' => date('Y-m-d H:i:s'),
    							'object_id' => 0,
    							'memo' => '提现佣金:' . $price . '元'
    					);
    					
    					$rst = $wallet_change_log_db->add($wallet_change_log);
    					
    					if ($need_check == 1)
    					{
    						$this->assign('tips', '该笔提现审核后会自动打款');
    						$this->display(':error');
    					}
    					else
    							redirect('index.php?g=Pige&m=index&a=index');
    					return;
    				}
    	}
    }
    
    /*
    public function ajax_apply_drawcash2()
    {
        $moneyType = intval($_REQUEST['moneyType']);
        
        $drawcash_db = M('drawcash');
        
        $count = $drawcash_db->where("user_id=$this->userid and TO_DAYS(now())=TO_DAYS(create_time)")->count();
        
        $reset_count = C("DRAWCASH_TIMES_PER_DAY") - $count;
        
        if ($reset_count <= 0)
        {
            $this->ajaxReturn(array('ret' => -1,  msg => '已经超过当日提现额度'));
            return;
        }
        
        $price = $this->wallet_db->where("user_id=$this->userid")->getField('money');
    
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
            $total_today_prices = $drawcash_db->where("user_id=$this->userid and TO_DAYS(create_time)=TO_DAYS(now())")->sum('price');
            
            if ($total_today_prices == null)
                $total_today_prices = 0;
            
            $total_today_prices += $price; 
            
            $need_check = 0;
            if ($total_today_prices >= floatval(C('DRAWCASH_MAX_PRICE_PER_DAY')))
                $need_check = 1;
    
            $wallet = $drawcash_db->where("user_id=$this->userid")->find();
    
                if (!$this->wallet_db->where("user_id=$this->userid and money>=$price")->setDec('money', $price))
                {
                    $this->ajaxReturn(array('ret' => -1, msg => '余额不足'));
                }
                else
                {
                    $action_log = M('user_action_log');
                    $log_data = array(
                        'user_id' => $this->userid,
                        'action' => 'apply_drawcash',
                        'params' => '钱包余额:' . $price,
                        'ip' => get_client_ip(0, true),
                        'create_time' => date('Y-m-d H:i:s')
                    );
                    $action_log->add($log_data);
                    
                    $data = array(
                        'user_id' => $this->userid,
                        'price' => $price,
                        'fee' => $price * C('DRAWCASH_RATIO') / 100.0,
                        'create_time' => date('Y-m-d H:i:s'),
                        'need_check' => $need_check,
                        'status' => 0
                    );
    
                    $rst = $drawcash_db->add($data);
    
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
                    
                    $gourl = str_replace('&amp;', '&', C('TIXIAN_OPENID_URL'));
                    
                    $goback = str_replace('&amp;', '&', C('PAGE_URL'));
                    $goback = str_replace("{hostname}", $_SERVER['HTTP_HOST'], $goback);
                    $goback= urlencode($goback);
                    $gourl = $gourl . '&goback=' . $goback . '&order_id=' . $rst;
                    
                    $this->ajaxReturn(array('ret' => 1, 'gourl' => $gourl));
                }
        }
    }
    
    public function ajax_apply_drawcash3()
    {
        $moneyType = intval($_REQUEST['moneyType']);
    
        $drawcash_db = M('drawcash');
    
        $price = $this->wallet_db->where("user_id=$this->userid")->getField('money2');
    
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
            $total_today_prices = $drawcash_db->where("user_id=$this->userid and TO_DAYS(create_time)=TO_DAYS(now())")->sum('price');
    
            if ($total_today_prices == null)
                $total_today_prices = 0;
            
            $total_today_prices += $price; 
            
            $need_check = 0;
            if ($total_today_prices >= floatval(C('DRAWCASH_MAX_PRICE_PER_DAY')))
                $need_check = 1;
            
            $wallet = $drawcash_db->where("user_id=$this->userid")->find();
    
             if (!$this->wallet_db->where("user_id=$this->userid and money2>=$price")->setDec('money2', $price))
              {
                $this->ajaxReturn(array(
                    'ret' => - 1,
                    'msg' => '余额不足'
                ));
            } else {
                
                $action_log = M('user_action_log');
                $log_data = array(
                    'user_id' => $this->userid,
                    'action' => 'apply_drawcash2',
                    'params' => '佣金余额:' . $price,
                    'ip' => get_client_ip(0, true),
                    'create_time' => date('Y-m-d H:i:s')
                );
                $action_log->add($log_data);
                
                $data = array(
                    'user_id' => $this->userid,
                    'price' => $price,
                    'fee' => $price * C('DRAWCASH_RATIO') / 100.0,
                    'create_time' => date('Y-m-d H:i:s'),
                    'need_check' => $need_check,
                    'status' => 0
                );
                
                $rst = $drawcash_db->add($data);
                
                $wallet_change_log_db = M('wallet_change_log');
                $wallet_change_log = array(
                    'user_id' => $this->userid,
                    'divide_ratio' => 0,
                    'fee' => - $price,
                    'type' => 3,
                    'create_time' => date('Y-m-d H:i:s'),
                    'object_id' => 0,
                    'memo' => '提现佣金:' . $price . '元'
                );
                
                $rst = $wallet_change_log_db->add($wallet_change_log);
                
                $gourl = str_replace('&amp;', '&', C('TIXIAN_OPENID_URL'));
                
                $goback = str_replace('&amp;', '&', C('PAGE_URL'));
                $goback = str_replace("{hostname}", $_SERVER['HTTP_HOST'], $goback);
                $goback= urlencode($goback);
                $gourl = $gourl . '&goback=' . $goback . '&order_id=' . $rst;
                
                $this->ajaxReturn(array('ret' => 1, 'gourl' => $gourl));
            }
        }
    }
    */
    
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
}