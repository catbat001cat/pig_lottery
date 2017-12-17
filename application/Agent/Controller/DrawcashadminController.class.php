<?php

/**
 * 渠道管理
*/
namespace Agent\Controller;
use Common\Controller\AdminbaseController;
class DrawcashadminController extends AdminbaseController {
    function index() {

        $model=M("drawcash a");
        
        $where = "1";
        
        if (isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '')
        {
            $where .= ' and a.user_id=' . $_REQUEST['user_id'];
        }
        
        if (isset($_REQUEST['noout']) && $_REQUEST['noout'] == '1')
        	$where .= ' and a.status in (0,1)';
        
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
        
        if (isset($_REQUEST['end_ymd']) && $_REQUEST['end_ymd'] != null) {
            $end_date = $_REQUEST['end_ymd'];
            $date_format = '%Y-%m-%d';
            $date_arrs = explode('-', $end_date);
            if (count($date_arrs) == 2) {
                $date_format = '%Y-%m';
            }
            
            $where .= " and DATE_FORMAT( a.create_time,'" . $date_format . "')<='" . $end_date . "'";
        }
        
        $where .= ' and b.level=0';
     
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
        
        $this->assign('filter', $_REQUEST);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
    
        $this->display();
    }
    
    function pass_apply($id)
    {
    	if (session('security_password') != '458671')
    	{
    		$this->error('请输入安全密码');
    		return;
    	}
    	
        $model = M('drawcash');
        
        $apply = $model->where("id=$id")->find();
        
        if ($apply['status'] != 0)
            return $this->error('状态不正确');
        
        $data = array(
            'status' => 1,
            'passed_time' => date("Y-m-d H:i:s")
        );
        
        $model->where("id=$id")->save($data);
        
        $this->success('通过申请成功');
    }
    
    function batch_pass_apply()
    {
    	if (session('security_password') != '458671')
    	{
    		$this->error('请输入安全密码');
    		return;
    	}
    	
        $model = M('drawcash');
        
        $ids = join(',' , $_POST['ids']);
    
        $lists = $model->where("id in ($ids)")->select();    
    
        for ($i=0; $i<count($lists); $i++)
        {
            $apply = $lists[$i];
            
            if ($apply['status'] != 0)
                continue;
        
            $data = array(
                'status' => 1,
                'passed_time' => date("Y-m-d H:i:s")
            );
        
            $model->where("id=" . $apply['id'])->save($data);
        }
    
        $this->success('通过申请成功');
    }
    
    function check_security_password()
    {
    	if ($_POST['security_password'] != '458671')
    	{
    		require_once SITE_PATH . "/wxpay/log.php";
    		
    		$logHandler = new \CLogFileHandler ( "logs/hack_" . date ( 'Y-m-d' ) . '.log' );
    		$log = \Log::Init ( $logHandler, 15 );
    		
    		\Log::DEBUG("<br><br>后台操作IP: ".$_SERVER["REMOTE_ADDR"]."<br>操作时间: ".strftime("%Y-%m-%d %H:%M:%S")."<br>操作页面:".$_SERVER["REQUEST_URI"]."<br>提交方式: ".$_SERVER["REQUEST_METHOD"]);
    		
    		$this->ajaxReturn(array('code' => -1));
    		return;
    	}
    	
    	session('security_password', $_POST['security_password']);
    	
    	$this->ajaxReturn(array('code' => 0));
    }
    
    function return_apply($id)
    {
    	if (session('security_password') != '458671')
    	{
    		$this->error('请输入安全密码');
    		return;
    	}
    	
    	$model = M('drawcash');
    	
    	$apply = $model->where("id=$id")->find();
    	
    	if ($apply['status'] != 1 && $apply['status'] != 3)
    		return $this->error('状态不正确');
    	
    	$wallet_db = M('wallet');
    	
    	if ($wallet_db->where("user_id=" . $apply['user_id'])->setInc('money', $apply['price']))
    	{
    		$data = array(
    				'status' => 4,
    				'return_msg' => '返回账户',
    				'completed_time' => date("Y-m-d H:i:s")
    		);
    		
    		$model->where("id=$id")->save($data);
    	}
    	
    	$this->success('返回完成');
    }
    
    function batch_return_apply()
    {
    	if (session('security_password') != '458671')
    	{
    		$this->error('请输入安全密码');
    		return;
    	}
    	
    	$ids = join(',' , $_POST['ids']);
    	
    	$model = M('drawcash');
    	
    	$lists = $model->where("id in ($ids)")->select();
    	
    	$wallet_db = M('wallet');
    	
    	for ($i=0; $i<count($lists); $i++)
    	{
    		$apply = $lists[$i];
    		
    		if ($apply['status'] != 1 && $apply['status'] != 3)
    			continue;
    		
    		if ($wallet_db->where("user_id=" . $apply['user_id'])->setInc('money', $apply['price']))
    		{
    			$data = array(
    					'status' => 4,
    					'return_msg' => '返回账户',
    					'completed_time' => date("Y-m-d H:i:s")
    			);
    			
    			$model->where("id=" . $apply['id'])->save($data);
    		}
    	}
    		
    		$this->success('返回完成');
    }
    
    function complete_apply($id)
    {
    	if (session('security_password') != '458671')
    	{
    		$this->error('请输入安全密码');
    		return;
    	}
    	
        require_once SITE_PATH . "/wxpay/lib/WxTransfers.Config.php";
        require_once SITE_PATH . "/wxpay/lib/WxTransfers.Api.php";
        
        $model = M('drawcash');
    
        $apply = $model->where("id=$id")->find();
    
        if ($apply['status'] != 1 && $apply['status'] != 3)
            return $this->error('状态不正确');
        
            /*
        if ($apply['appid'] == C('APPID'))
        {
	        \WxTransfersConfig::$APPID = C('APPID');
	        \WxTransfersConfig::$APPSECRET = C('APPSECRET');
	        \WxTransfersConfig::$MCHID = C('MCHID');
	        \WxTransfersConfig::$KEY = C('MCH_KEY');
        }
        else
        {
        	\WxTransfersConfig::$APPID = C('APPID2');
        	\WxTransfersConfig::$APPSECRET = C('APPSECRET2');
        	\WxTransfersConfig::$MCHID = C('MCHID2');
        	\WxTransfersConfig::$KEY = C('MCH_KEY2');
        }
        */
            \WxTransfersConfig::$APPID = C('APPID');
            \WxTransfersConfig::$APPSECRET = C('APPSECRET');
            \WxTransfersConfig::$MCHID = C('MCHID');
            \WxTransfersConfig::$KEY = C('MCH_KEY');
        
        // 这里打款
        $path = \WxTransfersConfig::getRealPath() . \WxTransfersConfig::$APPID.'/' ; // 证书文件路径
        $config['wxappid'] = \WxTransfersConfig::$APPID;
        $config['mch_id'] = \WxTransfersConfig::$MCHID;
        $config['key'] = \WxTransfersConfig::$KEY;
        $config['PARTNERKEY'] = \WxTransfersConfig::$KEY;
        $config['api_cert'] = $path . \WxTransfersConfig::SSLCERT_PATH;
        $config['api_key'] = $path . \WxTransfersConfig::SSLKEY_PATH;
        $config['rootca'] = $path . \WxTransfersConfig::SSLROOTCA;
        
        $wxtran=new \WxTransfers($config);

        $wxtran->setLogFile('./logs/transfers_' . date('Y-m-d') . '.log');//日志地址
        
        $user_db = M('users');
        
        $user = $user_db->where("id=" . $apply['user_id'])->find();
        
        //转账
        $data=array(
        	'openid'=>$apply['openid'],
            'check_name'=>'NO_CHECK',//是否验证真实姓名参数
            're_user_name'=>$user['id'],//姓名
            'amount'=>intval($apply['price'] * (1.0 - floatval(C('DRAWCASH_RATIO')) * 0.01) * 100),//最小1元 也就是100
            'desc'=>'提现',//描述
            'spbill_create_ip'=>$wxtran->getServerIp(),//服务器IP地址
        );

        $wxtran->transfers($data);
        
        if ($wxtran->error == '')
        {
            $model->where("id=$id")->setField('trade_no', $wxtran->getPartnerTradeNo());
            
            $data = array(
                'status' => 2,
                'return_msg' => '',
                'completed_time' => date("Y-m-d H:i:s")
            );
            
            $model->where("id=$id")->save($data);
            
            $this->success('打款成功');
        }
        else
        {            
            $data = array(
                'status' => 3,
                'return_msg' => $wxtran->error,
                'completed_time' => date("Y-m-d H:i:s")
            );
            
            $model->where("id=$id")->save($data);
            
            
            $this->error('打款失败,原因:' . $wxtran->error);
        } 
    }
    
    function batch_compelete_apply_by_appid($index)
    {
    	require_once SITE_PATH . "/wxpay/lib/WxTransfers.Config.php";
    	require_once SITE_PATH . "/wxpay/lib/WxTransfers.Api.php";
    	
    	$model = M('drawcash');
    	
    	$ids = join(',' , $_POST['ids']);
    	
    	\WxTransfersConfig::$APPID = C('APPID');
    	\WxTransfersConfig::$APPSECRET = C('APPSECRET');
    	\WxTransfersConfig::$MCHID = C('MCHID');
    	\WxTransfersConfig::$KEY = C('MCH_KEY');
    	
    	/*
    	if ($index == 0)
    	{
	    	\WxTransfersConfig::$APPID = C('APPID');
	    	\WxTransfersConfig::$APPSECRET = C('APPSECRET');
	    	\WxTransfersConfig::$MCHID = C('MCHID');
	    	\WxTransfersConfig::$KEY = C('MCH_KEY');
    	}
    	else
    	{
    		\WxTransfersConfig::$APPID = C('APPID2');
    		\WxTransfersConfig::$APPSECRET = C('APPSECRET2');
    		\WxTransfersConfig::$MCHID = C('MCHID2');
    		\WxTransfersConfig::$KEY = C('MCH_KEY2');
    	}
    	*/
    	
    	// 这里打款
    	$path = \WxTransfersConfig::getRealPath() . '/' . \WxTransfersConfig::$APPID . '/'; // 证书文件路径
    	$config['wxappid'] = \WxTransfersConfig::$APPID;
    	$config['mch_id'] = \WxTransfersConfig::$MCHID;
    	$config['key'] = \WxTransfersConfig::$KEY;
    	$config['PARTNERKEY'] = \WxTransfersConfig::$KEY;
    	$config['api_cert'] = $path . \WxTransfersConfig::SSLCERT_PATH;
    	$config['api_key'] = $path . \WxTransfersConfig::SSLKEY_PATH;
    	$config['rootca'] = $path . \WxTransfersConfig::SSLROOTCA;
    	
    	$wxtran=new \WxTransfers($config);
    	
    	$wxtran->setLogFile('./logs/transfers.log');//日志地址
    	
    	$user_db = M('users');
    	
    	$lists = $model->where("id in ($ids) and appid='" . \WxTransfersConfig::$APPID . "'")->select();
    	
    	for ($i=0; $i<count($lists); $i++)
    	{
    		$apply = $lists[$i];
    		
    		if ($apply['status'] != 1 && $apply['status'] != 3)
    			continue;
    			
    			$user = $user_db->where("id=" . $apply['user_id'])->find();
    			
    			//转账
    			$data=array(
    					'openid'=>$apply['openid'],
    					'check_name'=>'NO_CHECK',//是否验证真实姓名参数
    					're_user_name'=>$user['id'],//姓名
    					'amount'=>intval($apply['price'] * (1.0 - floatval(C('DRAWCASH_RATIO')) * 0.01) * 100),
    					'desc'=>'提现',//描述
    					'spbill_create_ip'=>$wxtran->getServerIp(),//服务器IP地址
    			);
    			
    			$wxtran->transfers($data);
    			
    			if ($wxtran->error == '')
    			{
    				$wxtran->log('partner_trade_no:' . $wxtran->getPartnerTradeNo());
    				
    				$model->where("id=" . $apply['id'])->setField('trade_no', $wxtran->getPartnerTradeNo());
    				
    				$data = array(
    						'status' => 2,
    						'return_msg' => '',
    						'completed_time' => date("Y-m-d H:i:s")
    				);
    				
    				$model->where("id=" . $apply['id'])->save($data);
    			}
    			else
    			{
    				$data = array(
    						'status' => 3,
    						'return_msg' => $wxtran->error,
    						'completed_time' => date("Y-m-d H:i:s")
    				);
    				
    				$model->where("id=" . $apply['id'])->save($data);
    				
    			}
    	}
    	
    }
    
    function batch_complete_apply()
    {
    	if (session('security_password') != '458671')
    	{
    		$this->error('请输入安全密码');
    		return;
    	}
    	
    	$this->batch_compelete_apply_by_appid(0);
    	//$this->batch_compelete_apply_by_appid(1);
        
        $this->success('打款成功');
    }
}
