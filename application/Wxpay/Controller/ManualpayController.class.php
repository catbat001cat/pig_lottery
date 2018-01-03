<?php
/**
 * 供别的平台使用的支付接口
 */
namespace Wxpay\Controller;

use Common\Controller\HomebaseController;

class ManualpayController extends HomebaseController
{
	private $user_id = null;
	
    function _initialize()
    {
        parent::_initialize();
    }
    
    public function asset_login() {
    	$this->user_id = $_SESSION ["user"] ["id"];
    	if ($this->user_id == 0) {
    		$result ["code"] = - 1;
    		$result ["msg"] = "需要登录：" . session_id () . ',' . $_REQUEST ["session_id"];
    		echo json_encode ( $result );
    		exit ();
    	}
    }
    
    // 从别的平台进来
    public function entry()
    {
    	$this->asset_login();
    	
    	$qq = C('MANUAL_QQ');
    	
    	$this->assign('qq', $qq);
    	$this->assign('id', $this->user_id);
    	
     	$this->display(':manual');   
    }
}
