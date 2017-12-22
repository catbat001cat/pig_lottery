<?php

/**
 * 渠道管理
*/
namespace Agent\Controller;
use Common\Controller\AdminbaseController;
class RechargeadminController extends AdminbaseController {
    function index() {

        $model=M("recharge_order a");
        
        $where = "1";
        
        if (isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '')
        {
            $where .= ' and a.user_id=' . $_REQUEST['user_id'];
        }
        
        if (isset($_REQUEST['id']) && $_REQUEST['id'] != '')
        {
        	$where .= ' and a.id=' . $_REQUEST['id'];
        }
        
        
        if (isset($_REQUEST['status']) && $_REQUEST['status'] != '')
            $where .= ' and a.status=' . $_REQUEST['status'];
            if (isset($_REQUEST['start_ymd']) && $_REQUEST['start_ymd'] != null) {
            $start_date = $_REQUEST['start_ymd'];
            $date_format = '%Y-%m-%d';
            $date_arrs = explode('-', $start_date);
            if (count($date_arrs) == 2) {
                $date_format = '%Y-%m';
            }
            
            $where .= " and DATE_FORMAT( a.create_time,'" . $date_format . "')>='" . $start_date . "'";
        }
        else
        {
        	$where .= " and a.create_time>='" . date("Y-m-d"). " 00:00:00'";
        	
        	$_REQUEST['start_ymd'] = date("Y-m-d");
        }
        
        if (isset($_REQUEST['order_sn']) && $_REQUEST['order_sn'] != '')
        	$where .= ' and (a.order_sn like "%' . $_REQUEST['order_sn'] . '%") or (c.transition_id like "%' . $_REQUEST['order_sn'] . '%")';
        
        if (isset($_REQUEST['end_ymd']) && $_REQUEST['end_ymd'] != null) {
            $end_date = $_REQUEST['end_ymd'];
            $date_format = '%Y-%m-%d';
            $date_arrs = explode('-', $end_date);
            if (count($date_arrs) == 2) {
                $date_format = '%Y-%m';
            }
            
            $where .= " and DATE_FORMAT( a.create_time,'" . $date_format . "')<='" . $end_date . "'";
        }
     
        $count=$model
        ->join('__USERS__ b on b.id=a.user_id', 'left')
        ->join('__WX_PAY__ c on c.from_order_sn=a.id')
        ->where($where)
        ->count();
        $page = $this->page($count, 20);
        $lists = $model
        ->join('__USERS__ b on b.id=a.user_id', 'left')
        ->join('__WX_PAY__ c on c.from_order_sn=a.id')
        ->where($where)
        ->order("id DESC")
        ->field('a.*,b.user_nicename,c.price as real_price, c.status as real_status, c.is_ok,c.transition_id')
        ->limit($page->firstRow . ',' . $page->listRows)
        ->select();
        
        for ($i=0; $i<count($lists); $i++)
        {
        	if ($lists[$i]['real_price'] > 0)
        	{
        		$lists[$i]['real_price'] = $lists[$i]['real_price'];// / 100;
        		$lists[$i]['real_price_det'] = $lists[$i]['real_price'] - $lists[$i]['price'];
        	}
        }
        
        $this->assign('filter', $_REQUEST);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
    
        $this->display();
    }
    
    function wallet_change_index() {
    	
    	$model=M("wallet_change_log a");
    	
    	$where = "1";
    	
    	if (isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '')
    	{
    		$where .= ' and a.user_id=' . $_REQUEST['user_id'];
    	}
    	
    	if (isset($_REQUEST['status']) && $_REQUEST['status'] != '')
    		$where .= ' and a.status=' . $_REQUEST['status'];
    		if (isset($_REQUEST['start_ymd']) && $_REQUEST['start_ymd'] != null) {
    			$start_date = $_REQUEST['start_ymd'];
    			$date_format = '%Y-%m-%d';
    			$date_arrs = explode('-', $start_date);
    			if (count($date_arrs) == 2) {
    				$date_format = '%Y-%m';
    			}
    			
    			$where .= " and DATE_FORMAT( a.create_time,'" . $date_format . "')>='" . $start_date . "'";
    		}
    		
    			
    			if (isset($_REQUEST['end_ymd']) && $_REQUEST['end_ymd'] != null) {
    				$end_date = $_REQUEST['end_ymd'];
    				$date_format = '%Y-%m-%d';
    				$date_arrs = explode('-', $end_date);
    				if (count($date_arrs) == 2) {
    					$date_format = '%Y-%m';
    				}
    				
    				$where .= " and DATE_FORMAT( a.create_time,'" . $date_format . "')<='" . $end_date . "'";
    			}
    			
    			$count=$model
    			->join('__USERS__ b on b.id=a.user_id', 'left')
    			->where($where)
    			->count();
    			$page = $this->page($count, 20);
    			$lists = $model
    			->join('__USERS__ b on b.id=a.user_id', 'left')
    			->where($where)
    			->order("id DESC")
    			->field('a.*,b.user_nicename')
    			->limit($page->firstRow . ',' . $page->listRows)
    			->select();
    			
    			$recharge_order_model = M('recharge_order a');
    			
    			for ($i=0; $i<count($lists); $i++)
    			{
    				if ($lists[$i]['type'] == 0)
    				{
    					$order = $recharge_order_model
    					->join('__WX_PAY__ c on c.from_order_sn=a.id')
    					->where("a.id=" . $lists[$i]['object_id'])
    					->field("a.price,c.price as real_price")
    					->find();
    					
    					$lists[$i]['memo'] .= ',实际支付:' . $order['real_price']. '分';
    				}
    			}
    			
    			$this->assign('filter', $_REQUEST);
    			$this->assign('lists', $lists);
    			$this->assign("page", $page->show('Admin'));
    			
    			$this->display();
    }
    
    public function manual_repay()
    {
    	$id = $_REQUEST['id'];
    	$transition_id = $_REQUEST['transition_id'];
    	
    	$recharge_order_model = M('recharge_order a');
    	
    	$order = $recharge_order_model->where("id=$id")->find();
    	
    	if ($order['status'] != 0)
    	{
    		return $this->ajaxReturn(array('ret' => -1, 'msg' => '该订单状态不正确'));
    	}
    	
    	$wx_pay_db = M('wx_pay');
    	$wx_pay = $wx_pay_db->where("from_order_sn=$id")->find();
    	
    	if ($wx_pay['status'] != 0)
    	{
    		return $this->ajaxReturn(array('ret' => -1, 'msg' => '该订单状态不正确'));
    	}
   
    	$wx_pay['real_price'] = $wx_pay['price'];
    	$wx_pay['status'] = 1;
    	$wx_pay['transition_id'] = $transition_id;
    	$wx_pay['memo'] = '补单';
    	
    	if ($wx_pay_db->where("id=" . $wx_pay['id'])->save($wx_pay))
    	{
    		$order['status'] = 1;
    		$recharge_order_model->where("id=$id")->save($order);
    		
    		$wallet_db = M('wallet');
    		$wallet = $wallet_db->where("user_id=" . $order['user_id'])->find();
    		
    		$wallet_db->where("user_id=" . $order['user_id'])->setInc("money", $order['price']);
    		
    		$wallet_change_db = M('wallet_change_log');
    		
    		$data = array(
    				'user_id' => $order['user_id'],
    				'object_id' => $order['id'],
    				'type' => 0,
    				'divide_ratio' => 0,
    				'fee' => floatval($order['price']),
    				'create_time' => date('Y-m-d H:i:s'),
    				'memo' => '充值(补单)'
    		);
    		
    		$wallet_change_db->add($data);
    	}
    	
    	return $this->ajaxReturn(array('ret' => 1));
    }
}
