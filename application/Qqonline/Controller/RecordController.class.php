<?php
namespace Qqonline\Controller;

use Common\Controller\MemberbaseController;

class RecordController extends MemberbaseController {
    private $lottery_order_db = null;
    private $recharge_db = null;
    private $drawcash_db = null;
    private $wallet_change_log_db = null;
	function _initialize(){
		parent::_initialize();

		$this->lottery_order_db= M('lottery_order');
		$this->recharge_db = M('recharge_order');
		$this->drawcash_db = M('drawcash');
		$this->wallet_change_log_db = M('wallet_change_log');
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
	
    // 投注记录
	public function get_records() {
		$this->filterAttack();
		
		//$this->assign($this->user);
		
		//$total_money = $this->lottery_order_db->where("user_id=$this->userid")->sum('price');
		
		//if ($total_money == null)
			//$total_money = 0;
		
		$records = $this->lottery_order_db
		->alias('a')
		->join('__LOTTERY__ b on b.no=a.no', 'left')
		->where("user_id=$this->userid and b.status=2")
		->field('a.*,b.number,b.num3,b.open_time')
		->order('id desc')
		->limit(0, 10)
		->select();
		
		/*
		$lottery_db = M('lottery');
		
		for ($i=0; $i<count($records); $i++)
		{
			if ($records['lottery_id'] == null)
			{
				$item = $lottery_db->where("no='" . $records[$i]['no'] . "'")->find();
			}
			else
			{
				$item = $lottery_db->where("id=" . $records[$i]['lottery_id'])->find();
			}
			
			$records[$i]['number'] = $item['number'];
			$records[$i]['num3'] = $item['num3'];
			$records[$i]['open_time'] = $item['open_time'];
		}
		*/
		
		$this->ajaxReturn(array('ret' => 1, 'total_money' => 0, 'list' => $records));
    }
    
    // 兑换记录
    public function get_drawcashs() {
    	$this->filterAttack();
    	
        $total_money = $this->drawcash_db->where("user_id=$this->userid")->sum('price');
        
        $drawcash_logs = $this->drawcash_db->where("user_id=$this->userid")->order('id desc')->limit(0, 50)->select();
        
        $this->ajaxReturn(array('ret' => 1, 'total_money' => $total_money, 'info' => $drawcash_logs));
    }
    
    // 佣金记录
    public function get_comissions()
    {
    	$this->filterAttack();
    	
    	$total_money = $this->wallet_change_log_db->where("user_id=$this->userid and type=4")->sum('fee');
    	
    	if ($total_money == null)
    		$total_money = 0;
    	
    	$logs = $this->wallet_change_log_db->alias('a')
    	->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')
    	->join('__USERS__ e on e.id=b.user_id', 'left')
    	->where("a.user_id=$this->userid and a.type=4")
    	->field('a.*,b.user_id as target_user_id,e.user_activation_key as target_user_activation_key')
    	->order('a.id desc')->limit(0, 30)
    	->select();
    	
    	$this->ajaxReturn(array('ret' => 1, 'total_money' => $total_money, 'info' => $logs));
    }
    
    // 充值记录
    public function get_recharges() {
    	$this->filterAttack();
    	
    	$this->assign($this->user);
    
    	$total_money = $this->recharge_db->where("user_id=$this->userid and `status`=1")->sum('price');
    	
    	$records= $this->recharge_db->where("user_id=$this->userid and `status`=1")->order('id desc')->limit(0, 50)->select();
    	
    	echo json_encode(array('ret' => 1, 'total_money' => $total_money, 'info' => $records));
    }
    
    // 获得金钱变动
    public function get_wallet_changes() {
    	$this->filterAttack();
    	
        $logs = $this->wallet_change_log_db->where("user_id=$this->userid")->order('id desc')->limit(0, 30)->select();
        
        echo json_encode(array('ret' => 1, 'list' => $logs));
    }
}
