<?php

namespace User\Controller;

use Common\Controller\AdminbaseController;

class IndexadminController extends AdminbaseController {
	
	// 后台本站用户列表
	public function index() {
		$this->index_recharges();
		
		return;
		$request = I ( 'request.' );
		
		$where = '1';
		
		$order = "a.id desc";
		
		if (! empty ( $request ['uid'] ))
			$where .= ' and a.id=' . intval ( $request ['uid'] );
		
		if (! empty ( $_REQUEST ['order_type'] )) {
			if ($_REQUEST ['order_type'] == '1')
				$order = 'total_recharge_price desc';
			else if ($_REQUEST ['order_type'] == '2')
				$order = 'total_drawcash_out desc';
			else if ($_REQUEST ['order_type'] == '3')
				$order = 'total_lottery_price desc';
			else if ($_REQUEST ['order_type'] == '4')
				$order = 'total_lottery_price_win desc';
			else if ($_REQUEST ['order_type'] == '5')
				$order = 'total_lottery_price_win - total_lottery_price desc';
			else if ($_REQUEST ['order_type'] == '6')
				$order = 'hack_times desc';
		}
		
		if (isset ( $_REQUEST ['error_status'] )) {
			if ($_REQUEST ['error_status'] == '0') {
				$where .= ' and (select sum(d.price-e.price/100) from sp_recharge_order d left join sp_wx_pay e on e.from_order_sn=d.id where d.user_id=a.id and d.`status`=1)=0';
			} else if ($_REQUEST ['error_status'] == '-1') {
				$where .= ' and (select sum(d.price-e.price/100) from sp_recharge_order d left join sp_wx_pay e on e.from_order_sn=d.id where d.user_id=a.id and d.`status`=1)<>0';
			} else if ($_REQUEST ['error_status'] == '-2') {
				$where .= ' and (select count(j.id) from sp_user_action_log j where j.user_id=a.id and j.action="hack")>0';
			}
		}
		
		$users_model = M ( "Users" );
		
		$count = $users_model->alias ( 'a' )->where ( $where )->count ();
		$page = $this->page ( $count, 20 );
		
		/*
		$lists = $users_model->alias ( 'a' )->join ( '__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left' )->join ( '__CHANNELS__ c on c.id=b.channel_id', 'left' )->where ( $where )->field ( 'a.*,c.name as channel_name,c.id as channel_id,c.admin_user_id as parent_channel_user_id,
		 (select sum(d.price)  from sp_recharge_order d left join sp_wx_pay e on e.from_order_sn=d.id where d.user_id=a.id and d.`status`=1) as total_recharge_price,
		 (select sum(e.price/100)  from sp_recharge_order d left join sp_wx_pay e on e.from_order_sn=d.id where d.user_id=a.id and d.`status`=1) as total_recharge_real_price,
		 (select sum(f.price) from sp_drawcash f where f.user_id=a.id and f.`status`=2) as total_drawcash_out,
		 (select sum(g.price) from sp_drawcash g where g.user_id=a.id and g.`status` in (0,1)) as total_drawcash_apply,
		 (select sum(h.price) from sp_lottery_order h where h.user_id=a.id) as total_lottery_price,
		 (select sum(i.win) from sp_lottery_order i where i.user_id=a.id) as total_lottery_price_win,
		 (select count(j.id) from sp_user_action_log j where j.user_id=a.id and j.action="hack") as hack_times' )->order ( $order )->limit ( $page->firstRow . ',' . $page->listRows )->select ();
		*/
		$lists = $users_model->alias ( 'a' )
		->join ( '__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left' )->join ( '__CHANNELS__ c on c.id=b.channel_id', 'left' )
		->where ( $where )->field ( 'a.*,c.name as channel_name,c.id as channel_id,c.admin_user_id as parent_channel_user_id,
		 (select sum(d.price)  from sp_recharge_order d left join sp_wx_pay e on e.from_order_sn=d.id where d.user_id=a.id and d.`status`=1) as total_recharge_price,
		 (select sum(f.price) from sp_drawcash f where f.user_id=a.id and f.`status`=2) as total_drawcash_out,
		 (select sum(g.price) from sp_drawcash g where g.user_id=a.id and g.`status` in (0,1)) as total_drawcash_apply,
		 (select count(j.id) from sp_user_action_log j where j.user_id=a.id and j.action="hack") as hack_times' )->order ( $order )->limit ( $page->firstRow . ',' . $page->listRows )->select ();
		
		$rechrages_db = M ( 'recharge_order a' );
		$drawcash_db = M ( 'drawcash a' );
		$wallet_db = M ( 'wallet' );
		
		for($i = 0; $i < count ( $lists ); $i ++) {
			$wallet = $wallet_db->where ( "user_id=" . $lists [$i] ['id'] )->find ();
			
			$lists [$i] ['wallet'] = $wallet;
			$lists [$i] ['total_recharge_real_price_det'] = $lists [$i] ['total_recharge_price'] - $lists [$i] ['total_recharge_real_price'];
		}
		
		$this->assign ( 'filter', $_REQUEST );
		$this->assign ( 'list', $lists );
		$this->assign ( "page", $page->show ( 'Admin' ) );
		
		$this->display ( ":index" );
	}
	
	// 查看充值记录
	public function index_recharges() {
		$request = I ( 'request.' );
		
		$where = '1';
		
		$order = "a.id desc";
		
		if (! empty ( $request ['uid'] ))
			$where .= ' and a.id=' . intval ( $request ['uid'] );
		else
			$where .= ' and a.level=0';
		
		if (! empty ( $_REQUEST ['order_type'] )) {
			if ($_REQUEST ['order_type'] == '1')
				$order = 'total_recharge_price desc';
		}
		
		$users_model = M ( "Users" );
		
		$count = $users_model->alias ( 'a' )->where ( $where )->count ();
		$page = $this->page ( $count, 20 );
		
		$lists = $users_model->alias ( 'a' )->
		 	//join ( '__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left' )->
		// ->join ( '__CHANNELS__ c on c.id=b.channel_id', 'left' )
		/*
		where ( $where )->field ( 'a.*,
		 (select sum(d.price)  from sp_recharge_order d left join sp_wx_pay e on e.from_order_sn=d.id where d.user_id=a.id and d.`status`=1) as total_recharge_price,
		 (select sum(f.price) from sp_drawcash f where f.user_id=a.id and f.`status`=2) as total_drawcash_out,
		 (select sum(g.price) from sp_drawcash g where g.user_id=a.id and g.`status` in (0,1)) as total_drawcash_apply' )->order ( $order )->limit ( $page->firstRow . ',' . $page->listRows )->select ();
		*/
		where ( $where )->field ( 'a.*,
		 (select sum(d.price)  from sp_recharge_order d left join sp_wx_pay e on e.from_order_sn=d.id where d.user_id=a.id and d.`status`=1) as total_recharge_price,
		 (select sum(f.price) from sp_drawcash f where f.user_id=a.id and f.`status`=2) as total_drawcash_out')->order ( $order )->limit ( $page->firstRow . ',' . $page->listRows )->select ();
		$rechrages_db = M ( 'recharge_order a' );
		$drawcash_db = M ( 'drawcash a' );
		$wallet_db = M ( 'wallet' );
		
		for($i = 0; $i < count ( $lists ); $i ++) {
			$wallet = $wallet_db->where ( "user_id=" . $lists [$i] ['id'] )->find ();
			
			$lists [$i] ['wallet'] = $wallet;
			// $lists [$i] ['total_recharge_real_price_det'] = $lists [$i] ['total_recharge_price'] - $lists [$i] ['total_recharge_real_price'];
		}
		
		$this->assign ( 'filter', $_REQUEST );
		$this->assign ( 'list', $lists );
		$this->assign ( "page", $page->show ( 'Admin' ) );
		
		$this->display ( ":index_recharges" );
	}
	
	// 投注
	public function index_lottery() {
		$request = I ( 'request.' );
		
		$where = '1';
		
		$order = "a.id desc";
		
		if (! empty ( $request ['uid'] ))
			$where .= ' and a.id=' . intval ( $request ['uid'] );
		else
			$where .= ' and a.level=0';
		
		if (! empty ( $_REQUEST ['order_type'] )) {
			if ($_REQUEST ['order_type'] == '3')
				$order = 'total_lottery_price desc';
			else if ($_REQUEST ['order_type'] == '4')
				$order = 'total_lottery_price_win desc';
			else if ($_REQUEST ['order_type'] == '5')
				$order = 'total_lottery_price_win - total_lottery_price desc';
		}
		
		$users_model = M ( "Users" );
		
		$count = $users_model->alias ( 'a' )->where ( $where )->count ();
		$page = $this->page ( $count, 20 );
		
		$lists = $users_model->alias ( 'a' )->
		//join ( '__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left' )->
		// ->join ( '__CHANNELS__ c on c.id=b.channel_id', 'left' )
		where ( $where )->field ( 'a.*,
		(select sum(h.price) from sp_lottery_order h where h.user_id=a.id) as total_lottery_price,
		 (select sum(i.win) from sp_lottery_order i where i.user_id=a.id) as total_lottery_price_win' )->order ( $order )->limit ( $page->firstRow . ',' . $page->listRows )->select ();
		
		$rechrages_db = M ( 'recharge_order a' );
		$drawcash_db = M ( 'drawcash a' );
		$wallet_db = M ( 'wallet' );
		
		for($i = 0; $i < count ( $lists ); $i ++) {
			$wallet = $wallet_db->where ( "user_id=" . $lists [$i] ['id'] )->find ();
			
			$lists [$i] ['wallet'] = $wallet;
		}
		
		$this->assign ( 'filter', $_REQUEST );
		$this->assign ( 'list', $lists );
		$this->assign ( "page", $page->show ( 'Admin' ) );
		
		$this->display ( ":index_lottery" );
	}
	
	// 黑次数
	public function index_hack() {
		$request = I ( 'request.' );
		
		$where = '1';
		
		$order = "a.id desc";
		
		if (! empty ( $request ['uid'] ))
			$where .= ' and a.id=' . intval ( $request ['uid'] );
		else
			$where .= ' and a.level=0';
		
		if (! empty ( $_REQUEST ['order_type'] )) {
			if ($_REQUEST ['order_type'] == '6')
				$order = 'hack_times desc';
		}
		
		$users_model = M ( "Users" );
		
		$count = $users_model->alias ( 'a' )
		->where ( $where )->count ();
		$page = $this->page ( $count, 20 );
		
		$lists = $users_model->alias ( 'a' )
		//->join ( '__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left' )
		//->join ( '__CHANNELS__ c on c.id=b.channel_id', 'left' )
		->where ( $where )->field ( 'a.*,
		 (select count(j.id) from sp_user_action_log j where j.user_id=a.id and j.action="hack") as hack_times' )
		->order ( $order )
		->limit ( $page->firstRow . ',' . $page->listRows )->select ();
		
		$rechrages_db = M ( 'recharge_order a' );
		$drawcash_db = M ( 'drawcash a' );
		$wallet_db = M ( 'wallet' );
		$channel_user_relation_db = M('channel_user_relation');
		
		for($i = 0; $i < count ( $lists ); $i ++) {
			$wallet = $wallet_db->where ( "user_id=" . $lists [$i] ['id'] )->find ();
			
			$lists [$i] ['wallet'] = $wallet;
			$lists[$i]['is_ban'] = $channel_user_relation_db->where("user_id=" . $lists[$i]['id'])->getField('is_ban');
		}
		
		$this->assign ( 'filter', $_REQUEST );
		$this->assign ( 'list', $lists );
		$this->assign ( "page", $page->show ( 'Admin' ) );
		
		$this->display ( ":index_hack" );
	}
	
	// 后台本站用户禁用
	public function ban() {
		$id = I ( 'get.id', 0, 'intval' );
		if ($id) {
			
			$db = M('users');
			$db->execute("update sp_channel_user_relation set is_ban=1 where user_id=$id");
			
			/*
			$result = M ( "Users" )->where ( array (
					"id" => $id,
					"user_type" => 2 
			) )->setField ( 'user_status', 0 );
			*/
			
			$result = 1;
			
			if ($result) {
				$this->success ( "会员拉黑成功！", U ( "indexadmin/index" ) );
			} else {
				$this->error ( '会员拉黑失败,会员不存在,或者是管理员！' );
			}
		} else {
			$this->error ( '数据传入失败！' );
		}
	}
	public function disable_auto_drawcash_out() {
		$id = I ( 'get.id', 0, 'intval' );
		if ($id) {
			/*
			$result = M ( "Users" )->where ( array (
					"id" => $id,
					"user_type" => 2 
			) )->setField ( 'user_drawcash_status_disable', 1 );
			*/
			$db = M('users');
			$db->execute("update sp_users set user_drawcash_status_disable=1 where id=$id");
			
			$result = 1;
			
			if ($result) {
				$this->success ( "会员自动提现功能取消成功！", U ( "indexadmin/index" ) );
			} else {
				$this->error ( '会员自动提现功能取消失败,会员不存在,或者是管理员！' );
			}
		} else {
			$this->error ( '数据传入失败！' );
		}
	}
	
	// 后台本站用户启用
	public function cancelban() {
		$id = I ( 'get.id', 0, 'intval' );
		if ($id) {
			
			$db = M('users');
			$db->execute("update sp_channel_user_relation set is_ban=0 where user_id=$id");
			/*
			$result = M ( "Users" )->where ( array (
					"id" => $id,
					"user_type" => 2 
			) )->setField ( 'user_status', 1 );
			*/
			
			$result = 1;
			
			if ($result) {
				$this->success ( "会员启用成功！", U ( "indexadmin/index" ) );
			} else {
				$this->error ( '会员启用失败！' );
			}
		} else {
			$this->error ( '数据传入失败！' );
		}
	}
	public function enable_auto_drawcash_out() {
		$id = I ( 'get.id', 0, 'intval' );
		if ($id) {
			/*
			$result = M ( "Users" )->where ( array (
					"id" => $id,
					"user_type" => 2 
			) )->setField ( 'user_drawcash_status_disable', 0 );
			*/
			$db = M('users');
			$db->execute("update sp_users set user_drawcash_status_disable=0 where id=$id");
			
			$result = 1;
			
			if ($result) {
				$this->success ( "会员自动提现功能激活成功！", U ( "indexadmin/index" ) );
			} else {
				$this->error ( '会员自动提现功能激活失败,会员不存在,或者是管理员！' );
			}
		} else {
			$this->error ( '数据传入失败！' );
		}
	}
	
	public function add_money() {
		
		if (session('security_password') != '458671')
		{
			$this->error('请输入安全密码');
			return;
		}
		
		$id = I ( 'get.id', 0, 'intval' );
		$money = I('get.money', 0, 'floatval');
		
		if ($money > 1000 || $money <= 0)
		{
			$this->ajaxReturn(array('code' => -1, msg => '上分失败'));
			return;
		}
		
		$wallet_db = M('wallet');
		$wallet_change_db= M('wallet_change_log');
		
		$old_money = $wallet_db->where("user_id=$id")->getField('money');
		
		if ($wallet_db->where("user_id=$id")->setInc('money', $money))
		{
			$data = array(
					'user_id' => $id,
					'object_id' => 0,
					'type' => 9,
					'divide_ratio' => 0,
					'fee' => $money,
					'create_time' => date('Y-m-d H:i:s'),
					'memo' => '上分,原有:' . $old_money
			);
			
			$wallet_change_db->add($data);
			
			$this->ajaxReturn(array('code' => 0, msg => '上分成功'));
		}
		else
		{
			$this->ajaxReturn(array('code' => -1, msg => '上分失败'));
		}
	}
	
	public function sub_money() {
		
		if (session('security_password') != '458671')
		{
			$this->error('请输入安全密码');
			return;
		}
		
		$id = I ( 'get.id', 0, 'intval' );
		$money = I('get.money', 0, 'floatval');
		
		if ($money > 1000 || $money <= 0)
		{
			$this->ajaxReturn(array('code' => -1, msg => '下分失败'));
			return;
		}
		
		$wallet_db = M('wallet');
		$wallet_change_db= M('wallet_change_log');
		
		$old_money = $wallet_db->where("user_id=$id")->getField('money');
		
		if ($wallet_db->where("user_id=$id and money>=$money")->setDec('money', $money))
		{
			$data = array(
					'user_id' => $id,
					'object_id' => 0,
					'type' => 10,
					'divide_ratio' => 0,
					'fee' => -$money,
					'create_time' => date('Y-m-d H:i:s'),
					'memo' => '上分,原有:' . $old_money
			);
			
			$wallet_change_db->add($data);
			
			$this->ajaxReturn(array('code' => 0, msg => '下分成功'));
		}
		else
		{
			$this->ajaxReturn(array('code' => -1, msg => '下分失败'));
		}
	}
}