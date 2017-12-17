<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class CoinController extends MemberbaseController{
	
    // 钱包首页
	public function index(){
	    
	    $action_log = M('user_action_log');
	    $data = array(
	        'user_id' => $this->user['id'],
	        'action' => 'open:wallet',
	        'ip' => get_client_ip(0, true),
	        'create_time' => date('Y-m-d H:i:s'),
	    );
	    $action_log->add($data);
	    
	    $db = M('orders');
	    
	    $total_price = $db->where("user_id=$this->userid and is_payed=1 and is_up_coin=0")->sum('price');
	    
	    if ($total_price == null)
	        $total_price = 0;
	    
	    if ($total_price >= 200)
	        $coin = $total_price * 15;
	    else if ($total_price >= 150)
	        $coin = $total_price * 12;
	    else if ($total_price >= 100)
	        $coin = $total_price * 10;
	    else if ($total_price >= 50)
	        $coin = $total_price * 10;
	    else if ($total_price >= 30)
	        $coin = $total_price * 10;
	    else
	        $coin = $total_price * 10;
	    
	    $vip_db = M('vip');
	    $user_db = M('users');
	    
	    $db->where("user_id=$this->userid and is_payed=1 and is_up_coin=0")->setField("is_up_coin", 1);
	    
	    $user_db->where("id=$this->userid")->setInc('coin', $coin);
	    
	    $vip = $vip_db->where('price<=' . $total_price)->order('price desc')->find();
	    
	    if ($vip != null)
	    {
	        $user = $user_db->where("id=$this->userid")->find();
	        if ($vip['level'] != $user['level'])
	        {
	            $user_data = array(
	                'level' => $vip['level']
	            );
	    
	            $user_db->where("id=$this->userid")->save($user_data);
	        }
	    }
	    
	    
	    // 距离下一级充值
	    $level = $this->user['level'];
	    $next_level = $level + 1;
	    $next_level_vo = $vip_db->where("level=$next_level")->find();
	    if ($next_level_vo != null)
	        $this->assign('next_level', $next_level_vo);
	    
	    $order_db = M('orders');
	    
	    $this->user = $user_db->where("id=$this->userid")->find();

		$this->assign($this->user);
		$this->display(":wallet");
	}
	
}