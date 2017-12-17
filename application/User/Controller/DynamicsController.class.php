<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class DynamicsController extends MemberbaseController {
	
	function _initialize(){
		parent::_initialize();
	}
	
    // 会员中心首页
	public function index() {
		$this->assign($this->user);
    	$this->display(':dynamics');
    }
}
