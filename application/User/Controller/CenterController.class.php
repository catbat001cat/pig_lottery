<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class CenterController extends MemberbaseController {
	
	function _initialize(){
		parent::_initialize();
	}
	
    // 会员中心首页
	public function index() {
	    $user_db = M('users');
	    
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
	    
	    $_SESSION['user'] = $user_db->where("id=$this->userid")->find();
		$this->assign($this->user);
    	$this->display(':center');
    }
    
    public function agent() {
        $this->display(':agent');
    }
    
    public function compaint () {
        $this->display(':compaint');
    }
    
    public function compaint_yes () {
        $this->display(':compaint_yes');
    }
}
