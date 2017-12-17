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
	
    // 投注记录
	public function get_records() {
		
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
        
        $total_money = $this->drawcash_db->where("user_id=$this->userid")->sum('price');
        
        $drawcash_logs = $this->drawcash_db->where("user_id=$this->userid")->order('id desc')->limit(0, 50)->select();
        
        $this->ajaxReturn(array('ret' => 1, 'total_money' => $total_money, 'info' => $drawcash_logs));
    }
    
    // 佣金记录
    public function get_comissions()
    {
    	$total_money = $this->wallet_change_log_db->where("user_id=$this->userid and type=4")->sum('fee');
    	
    	if ($total_money == null)
    		$total_money = 0;
    	
    	$logs = $this->wallet_change_log_db->alias('a')
    	->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')
    	->where("a.user_id=$this->userid and a.type=4")
    	->field('a.*,b.user_id as target_user_id')
    	->order('a.id desc')->limit(0, 50)
    	->select();
    	
    	$this->ajaxReturn(array('ret' => 1, 'total_money' => $total_money, 'info' => $logs));
    }
    
    // 充值记录
    public function get_recharges() {
    	$this->assign($this->user);
    
    	$total_money = $this->recharge_db->where("user_id=$this->userid and `status`=1")->sum('price');
    	
    	$records= $this->recharge_db->where("user_id=$this->userid and `status`=1")->order('id desc')->limit(0, 50)->select();
    	
    	echo json_encode(array('ret' => 1, 'total_money' => $total_money, 'info' => $records));
    }
    
    // 获得金钱变动
    public function get_wallet_changes() {
        $logs = $this->wallet_change_log_db->where("user_id=$this->userid")->order('id desc')->limit(0, 30)->select();
        
        echo json_encode(array('ret' => 1, 'list' => $logs));
    }
}
