<?php
namespace Zp\Controller;

use Common\Controller\MemberbaseController;

class RecordController extends MemberbaseController {
    private $lottery_order_db = null;
	function _initialize(){
		parent::_initialize();

		$this->lottery_order_db= M('zp_lottery');
	}
	
	public function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq)
	{
		//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|union|into|load_file|outfile/';
		$pregs = '/select|insert|drop|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		
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
			
			\Log::DEBUG("<br><br>操作IP: ".$_SERVER["REMOTE_ADDR"]."<br>操作时间: ".strftime("%Y-%m-%d %H:%M:%S")."<br>操作页面:".$_SERVER["PHP_SELF"]."<br>提交方式: ".$_SERVER["REQUEST_METHOD"]."<br>提交参数: ".$StrFiltKey."<br>提交数据: ".$StrFiltValue);
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
		foreach($_COOKIE as $key=>$value)
		{
			$this->StopAttack($key,$value,$cookiefilter);
		}
	}
	
    // 投注记录
	public function get_records() {
		
		$this->assign($this->user);
		
		$total_money = $this->lottery_order_db->where("user_id=$this->userid")->sum('buy_price');
		
		if ($total_money == null)
			$total_money = 0;
		
		$records = $this->lottery_order_db
		->alias('a')
		->where("user_id=$this->userid")
		->field('a.*')
		->order('id desc')
		->limit(0, 10)
		->select();
		
		$this->ajaxReturn(array('ret' => 1, 'total_money' => $total_money, 'list' => $records));
    }
    
    // 佣金记录
    public function get_comissions()
    {
    	$wallet_change_log_db = M('wallet_change_log');
    	
    	$total_money = $wallet_change_log_db->where("user_id=$this->userid and type=8")->sum('fee');
    	
    	if ($total_money == null)
    		$total_money = 0;
    		
    	$logs = $wallet_change_log_db->alias('a')
    		->join('__ZP_LOTTERY__ b on b.id=a.object_id', 'left')
    		->where("a.user_id=$this->userid and a.type=8")
    		->field('a.*,b.user_id as target_user_id')
    		->order('a.id desc')->limit(0, 50)
    		->select();
    		
    		$this->ajaxReturn(array('ret' => 1, 'total_money' => $total_money, 'info' => $logs));
    }
}
