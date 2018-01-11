<?php
/*
 * _______ _ _ _ _____ __ __ ______
 * |__ __| | (_) | | / ____| \/ | ____|
 * | | | |__ _ _ __ | | _| | | \ / | |__
 * | | | '_ \| | '_ \| |/ / | | |\/| | __|
 * | | | | | | | | | | <| |____| | | | |
 * |_| |_| |_|_|_| |_|_|\_\\_____|_| |_|_|
 */
/*
 * _________ ___ ___ ___ ________ ___ __ ________ _____ ______ ________
 * |\___ ___\\ \|\ \|\ \|\ ___ \|\ \|\ \ |\ ____\|\ _ \ _ \|\ _____\
 * \|___ \ \_\ \ \\\ \ \ \ \ \\ \ \ \ \/ /|\ \ \___|\ \ \\\__\ \ \ \ \__/
 * \ \ \ \ \ __ \ \ \ \ \\ \ \ \ ___ \ \ \ \ \ \\|__| \ \ \ __\
 * \ \ \ \ \ \ \ \ \ \ \ \\ \ \ \ \\ \ \ \ \____\ \ \ \ \ \ \ \_|
 * \ \__\ \ \__\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \__\ \ \__\ \__\
 * \|__| \|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|__| \|__|\|__|
 */
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;

use Common\Controller\HomebaseController;

/**
 * 首页
 */
class IndexController extends HomebaseController
{
    function _initialize() {
        header("Access-Control-Allow-Origin: *");
    }
    // 首页 小夏是老猫除外最帅的男人了
    public function index()
    {
        $this->display(":index");
    }
    
    public function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq)
    {
    	//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
    	//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|union|into|load_file|outfile/';
    	//$pregs = '/select|insert|and|or|update|delete|union|into|load_file|outfile|from|count\(|drop table|update|truncate|asc\(|mid\(|char\(|xp_cmdshell|exec|master|\/\*|\*|\.\.\/|\.\//i';
    	
    	//$pregs = '/select|insert|drop|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
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

    private function do_register($username)
    {
        $channel = 0;
        $lat = 0;
        $lng = 0;
        $ua = '';
        if (isset($_REQUEST['channel']))
            $channel = intval($_REQUEST['channel']);
        if (isset($_REQUEST['ua']))
            $ua = $_REQUEST['ua'];
        $users_model = M("Users");
        
        $code = '';
            for ($i=0; $i<3; $i++)
            {
                $code = $this->getNonceStr(6);
                if ($users_model->where("user_activation_key='$code'")->find() == null)
                    break;
            }
        
        // 自动创建帐号和密码
        // 随机创建
        $email = $username . '@any.com';
        $password = '123456';
        $data = array(
            'user_login' => $username,
            'user_email' => $email,
        	'level' => 0,
            'user_nicename' => '见习新手',
            'user_pass' => sp_password($password),
            'last_login_ip' => get_client_ip(0, true),
            'create_time' => date("Y-m-d H:i:s"),
            'last_login_time' => date("Y-m-d H:i:s"),
            'user_activation_key' => $code,
            'user_status' => 1,
            "user_type" => 2
        ) // 会员
;
        $rst = $users_model->add($data);
        if ($rst) {
            // 注册成功页面跳转
            $data['id'] = $rst;
            
            $ch_user_db = M('channel_user_relation');
            
            $wallet = array(
            		'user_id' => $rst,
                    'money' => 0,
            		'money3' => floatval(C('BEGINNER_MONEY_GIFT')),
                    'money2' => 0
            );
            
            $wallet_db = M('wallet');
            $wallet_db->add($wallet);
            
            // 记录日志
            $wallet_change_log_db = M('wallet_change_log');
            $change_log = array(
            		'user_id' => $rst,
            		'divide_ratio' => 0,
            		'fee' => 0,
            		'type' => 5,
            		'create_time' => date('Y-m-d H:i:s'),
            		'object_id' => 0,
            		'memo' => '新手注册赠送:' . C('BEGINNER_MONEY_GIFT')
            );
            $wallet_change_log_db->add($change_log);
            
            $channel_db = M('channels');
            // 获取父渠道信息
            $parent_channels = '';
            $parent_channel = $channel_db->where("id=$channel")->find();
            if ($parent_channel != null)
            {
                $parent_channels = $parent_channel["parent_channels"];
                if ($parent_channels == '')
                    $parent_channels = '' . $channel;
                else 
                    $parent_channels .= ',' . $channel;
            }
            
            // 注册渠道
           	$channel_data = array(
           			'name' => $username,
           			'status' => 0,
           			'parent_id' => $channel,
           			'create_time' => date('Y-m-d H:i:s'),
           			'admin_user_id' => $rst,
           	        'parent_channels' => $parent_channels,
           			'divide_ratio' => 0.3
           	);
           	$channel_db->add($channel_data);
            
            /*
            $total_visible_count = $ch_user_db->where("channel_id=$channel and is_visible=1")->count();
            $total_invisible_count = $ch_user_db->where("channel_id=$channel and is_visible=0")->count();
            
            $channel_data = M('channels')->where("id=$channel")->find();
            $is_visible = 1;
            if ($channel_data != null && $channel_data['amount_deduct'] > 0)
            {
                $need_count_count = floor(($total_visible_count + $total_invisible_count) / $channel_data['amount_deduct']);
                
                if ($total_invisible_count < $need_count_count)
                    $is_visible = 0;
            }
            */
            
            $is_visible = 1;
            
            $ch_data = array(
                'user_id' => $rst,
                'channel_id' => $channel,
                'is_visible' => $is_visible
            );
            $ch_user_db->add($ch_data);
            
            $action_log = M('user_action_log');
            $user_data = array(
                'user_id' => $rst,
                'action' => 'login',
                'ip' => get_client_ip(0, true),
                'create_time' => date('Y-m-d H:i:s'),
                'ua' => $ua,
            );
            $action_log->add($user_data);

            session('user', $data);
            
            return $rst;
        }
        else
        {
            return -1;
        }
    }

    public function any_login()
    {
    	return;
        $users_model = M("Users a");
        
        $city = '深圳市';
        if (isset($_REQUEST['city']))
            $city = urldecode($_REQUEST['city']);
        
        session('city', $city);
        
        if (! isset($_COOKIE["login_name"])) {
            $username = date("YmdHis") . rand(100, 999);
            $this->do_register($username);
            setcookie("login_name", $username);
        } else {
            $username = $_COOKIE["login_name"];
            $user = $users_model->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left')->where("user_login='$username'")->field('a.*,b.channel_id')->find();
            if ($user == null)
                $this->do_register($username);
            else {
                $ch_user_db = M('channel_user_relation');
                if ($user['channel_id'] == 0 && isset($_REQUEST['channel']))
                {
                    $user['channel_id'] = $_REQUEST['channel'];
                    
                    $ch_user_db = M('channel_user_relation');
                    
                    $ch_data = array(
                        'channel_id' => intval($_REQUEST['channel'])
                    );
                    $ch_user_db->where('user_id=' . $user['id'])->save($ch_data);
                }
                
                $wallet_db = M('wallet');
                if ($wallet_db->where("user_id=" . $user['id'])->find() == null)
                {
                	$wallet = array(
                			'user_id' => $user['id'],
                			'money' => 0
                	);
                	$wallet_db->add($wallet);
                }
                
                $action_log = M('user_action_log');
                $data = array(
                    'user_id' => $user['id'],
                    'action' => 'login',
                    'ip' => get_client_ip(0, true),
                    'create_time' => date('Y-m-d H:i:s'),
                );
                $action_log->add($data);
                
                echo json_encode($user);
                
                session('user', $user);
            }
        }
        
        $this->redirect('list/index', array(
            'id' => 6
        ));
    }

    public function any_login_from()
    {
    	return;
    	
        $users_model = M("Users a");
    
        $city = '深圳市';
        if (isset($_REQUEST['city']))
            $city = urldecode($_REQUEST['city']);
        $login_name = $_REQUEST['login_name'];
        $ua = '';
        if (isset($_REQUEST['ua']))
            $ua = $_REQUEST['ua'];
        session('ua', $ua);
        
        if ($_REQUEST['channel'] == C('TEST_CHANNEL'))
        	session('is_admin_enter', '1');
        else
        {
            session('is_admin_enter', '0');
        }
        
        if ($login_name == null)
        {
            echo "<script>history.go(-1);</script>";
            return;
        }
    
        session('city', $city);
    
            $username = $login_name;
            $user = $users_model->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left')->where("user_login='$username'")->field('a.*,b.channel_id')->find();
            $ret = 0;
            if ($user == null)
                $ret = $this->do_register($username);
            else {
                $ch_user_db = M('channel_user_relation');
                /*
                if ($user['channel_id'] == 0 && isset($_REQUEST['channel']))
                {
                    $user['channel_id'] = $_REQUEST['channel'];
    
                    $ch_user_db = M('channel_user_relation');
    
                    $ch_data = array(
                        'channel_id' => intval($_REQUEST['channel'])
                    );
                    $ch_user_db->where('user_id=' . $user['id'])->save($ch_data);
                }*/
                
                $wallet_db = M('wallet');
                if ($wallet_db->where("user_id=" . $user['id'])->find() == null)
                {
                	$wallet = array(
                			'user_id' => $user['id'],
                            'money' => 0,
            		        'money3' => floatval(C('BEGINNER_MONEY_GIFT')),
                	        'money2' => 0
                	);
                	$wallet_db->add($wallet);
                }
                
                
                $action_log = M('user_action_log');
                $data = array(
                    'user_id' => $user['id'],
                    'action' => 'login',
                    'ip' => get_client_ip(0, true),
                    'create_time' => date('Y-m-d H:i:s'),
                    'ua' => $ua
                );
                $action_log->add($data);
    
                session('user', $user);
                
                $ret = 1;
            }
           
        if ($ret <= 0)
        {
            echo "<script>history.go(-1);</script>";
        }
        else
        {
            $_SESSION['is_tips'] = 0;
            
            redirect('index.php?g=Pig&m=index&a=index');
        }
    }
    
    public function ajax_login()
    {
    	return;
        $users_model = M("Users a");
  
        $login_name = $_REQUEST['login_name'];
        
        session('is_admin_enter', '1');
    
        if ($login_name == null)
        {
            echo "error";
            return;
        }
    
        $username = $login_name;
        $user = $users_model->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left')->where("user_login='$username'")->field('a.*,b.channel_id')->find();
        $ret = 0;
        if ($user == null)
            $ret = $this->do_register($username);
        else {
            $ch_user_db = M('channel_user_relation');
            
            $wallet_db = M('wallet');
            if ($wallet_db->where("user_id=" . $user['id'])->find() == null)
            {
                $wallet = array(
                    'user_id' => $user['id'],
                    'money' => 0,
                    'money3' => floatval(C('BEGINNER_MONEY_GIFT')),
                    'money2' => 0
                );
                $wallet_db->add($wallet);
            }
    
    
            $action_log = M('user_action_log');
            $data = array(
                'user_id' => $user['id'],
                'action' => 'login',
                'ip' => get_client_ip(0, true),
                'create_time' => date('Y-m-d H:i:s'),
                'ua' => 'robot'
            );
            $action_log->add($data);
    
            session('user', $user);
    
            $ret = 1;
        }
         
        if ($ret <= 0)
        {
            echo "error";
        }
        else
        {
            echo 'success';
        }
    }
    
    public function any_login_from_zp()
    {
    	$this->filterAttack();
    	
    	return;
        $users_model = M("Users a");
    
        $city = '深圳市';
        if (isset($_REQUEST['city']))
            $city = urldecode($_REQUEST['city']);
        $login_name = $_REQUEST['login_name'];
        $ua = '';
        if (isset($_REQUEST['ua']))
            $ua = $_REQUEST['ua'];
        session('ua', $ua);
    
        if ($_REQUEST['channel'] == C('TEST_CHANNEL'))
            session('is_admin_enter', '1');
        else
        {
            session('is_admin_enter', '0');
        }
    
        if ($login_name == null)
        {
            echo "<script>history.go(-1);</script>";
            return;
        }
    
        session('city', $city);
    
        $username = $login_name;
        $user = $users_model->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left')->where("user_login='$username'")->field('a.*,b.channel_id')->find();
        $ret = 0;
        if ($user == null)
            $ret = $this->do_register($username);
        else {
            $ch_user_db = M('channel_user_relation');
            /*
             if ($user['channel_id'] == 0 && isset($_REQUEST['channel']))
             {
             $user['channel_id'] = $_REQUEST['channel'];
    
             $ch_user_db = M('channel_user_relation');
    
             $ch_data = array(
             'channel_id' => intval($_REQUEST['channel'])
             );
             $ch_user_db->where('user_id=' . $user['id'])->save($ch_data);
            }*/
    
            $wallet_db = M('wallet');
            if ($wallet_db->where("user_id=" . $user['id'])->find() == null)
            {
                $wallet = array(
                    'user_id' => $user['id'],
                    'money' => 0,
                    'money3' => floatval(C('BEGINNER_MONEY_GIFT')),
                    'money2' => 0
                );
                $wallet_db->add($wallet);
            }
    
    
            $action_log = M('user_action_log');
            $data = array(
                'user_id' => $user['id'],
                'action' => 'login',
                'ip' => get_client_ip(0, true),
                'create_time' => date('Y-m-d H:i:s'),
                'ua' => $ua
            );
            $action_log->add($data);
    
            session('user', $user);
    
            $ret = 1;
        }
         
        if ($ret <= 0)
        {
            echo "<script>history.go(-1);</script>";
        }
        else
        {
            $_SESSION['is_tips'] = 0;
    
            redirect('index.php?g=Zp&m=index&a=main');
        }
    }
    
    public function any_login_from_ssc()
    {
    	return;
    	
        $this->filterAttack();
         
        $users_model = M("Users a");
    
        $city = '深圳市';
        if (isset($_REQUEST['city']))
            $city = urldecode($_REQUEST['city']);
        $login_name = $_REQUEST['login_name'];
        $ua = '';
        if (isset($_REQUEST['ua']))
            $ua = $_REQUEST['ua'];
        session('ua', $ua);
    
        if ($_REQUEST['channel'] == C('TEST_CHANNEL'))
            session('is_admin_enter', '1');
        else
        {
            session('is_admin_enter', '0');
        }
    
        if ($login_name == null)
        {
            echo "<script>history.go(-1);</script>";
            return;
        }
    
        session('city', $city);
    
        $username = $login_name;
        $user = $users_model->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left')->where("user_login='$username'")->field('a.*,b.channel_id')->find();
        $ret = 0;
        if ($user == null)
            $ret = $this->do_register($username);
        else {
            $ch_user_db = M('channel_user_relation');
            /*
             if ($user['channel_id'] == 0 && isset($_REQUEST['channel']))
             {
             $user['channel_id'] = $_REQUEST['channel'];
    
             $ch_user_db = M('channel_user_relation');
    
             $ch_data = array(
             'channel_id' => intval($_REQUEST['channel'])
             );
             $ch_user_db->where('user_id=' . $user['id'])->save($ch_data);
            }*/
    
            $wallet_db = M('wallet');
            if ($wallet_db->where("user_id=" . $user['id'])->find() == null)
            {
                $wallet = array(
                    'user_id' => $user['id'],
                    'money' => 0,
                    'money3' => floatval(C('BEGINNER_MONEY_GIFT')),
                    'money2' => 0
                );
                $wallet_db->add($wallet);
            }
    
    
            $action_log = M('user_action_log');
            $data = array(
                'user_id' => $user['id'],
                'action' => 'login',
                'ip' => get_client_ip(0, true),
                'create_time' => date('Y-m-d H:i:s'),
                'ua' => $ua
            );
            $action_log->add($data);
    
            session('user', $user);
    
            $ret = 1;
        }
         
        if ($ret <= 0)
        {
            echo "<script>history.go(-1);</script>";
        }
        else
        {
            $_SESSION['is_tips'] = 0;
    
            redirect('index.php?g=Qqonline&m=index&a=main');
        }
    }
    
    public function wx_login_direct()
    {
    	$this->filterAttack();
    	
    	$users_model = M("Users a");
    	
    	$city = '深圳市';
    	if (isset($_REQUEST['city']))
    		$city = urldecode($_REQUEST['city']);
    		$login_name = session('login_openid');
    		$ticket = session('login_ticket');
    		$sign = session('login_sign');
    		$ticket2 = $_REQUEST['ticket2'];
    		$sign2 = $_REQUEST['sign2'];
    		$noncestr = session('login_noncestr');
    		$channel = $_REQUEST['channel'];
    		
    		$openid = session('login_openid');
    		$url = $openid . $ticket . $noncestr;
    		$new_sign = md5(strtolower($url . C('LOGIN_KEY')));
    		
    		if ($new_sign != $sign)
    		{
    			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    			return;
    		}
    		
    		
    		$url = 'wx_login' . $channel . $ticket2;
    		$new_sign2 = md5(strtolower($url . C('LOGIN_KEY')));
    		
    		if ($new_sign2 != $sign2)
    		{
    			echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    			return;
    		}
    		
    		$ua = '';
    		if (isset($_REQUEST['ua']))
    			$ua = $_REQUEST['ua'];
    			session('ua', $ua);
    			session('openid',  session('login_openid'));
    			
    			if ($login_name == null)
    			{
    				echo "<script>history.go(-1);</script>";
    				return;
    			}
    			
    			session('city', $city);
    			
    			if ($_REQUEST['channel'] == C('TEST_CHANNEL'))
    				session('is_admin_enter', '1');
    				else
    				{
    					session('is_admin_enter', '0');
    				}
    				
    				$username = $login_name;
    				$user = $users_model->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left')->where("user_login='$username'")->field('a.*,b.channel_id,b.is_ban')->find();
    				$ret = 0;
    				if ($user == null)
    					$ret = $this->do_register($username);
    					else {
    						$ch_user_db = M('channel_user_relation');
    						
    						if ($user['channel_id'] == 0 && isset($_REQUEST['channel']))
    						{
    							$user['channel_id'] = $_REQUEST['channel'];
    							
    							$ch_user_db = M('channel_user_relation');
    							
    							$ch_data = array(
    									'channel_id' => intval($_REQUEST['channel'])
    							);
    							$ch_user_db->where('user_id=' . $user['id'])->save($ch_data);
    						}
    						
    						$wallet_db = M('wallet');
    						if ($wallet_db->where("user_id=" . $user['id'])->find() == null)
    						{
    							$wallet = array(
    									'user_id' => $user['id'],
    									'money' => 0,
    									'money3' => floatval(C('BEGINNER_MONEY_GIFT')),
    									'money2' => 0
    							);
    							$wallet_db->add($wallet);
    						}
    						
    						$action_log = M('user_action_log');
    						$log_data = array(
    								'user_id' => $user['id'],
    								'action' => 'login',
    								'ip' => get_client_ip(0, true),
    								'create_time' => date('Y-m-d H:i:s'),
    								'ua' => $ua
    						);
    						$action_log->add($log_data);
    						
    						// 不允许登录,跳转到别的地方
    						if ($user['is_ban'] == 1)
    						{
    							echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    							return;
    						}
    						
    						$user['openid'] = $_REQUEST['openid'];
    						$users_model->where("id=" . $user['id'])->setField('openid', $_REQUEST['openid']);
    						
    						session('user', $user);
    						
    						$ret = 1;
    					}
    					
    					if ($ret <= 0)
    					{
    						echo "<script>history.go(-1);</script>";
    					}
    					else
    					{
    						$_SESSION['is_tips'] = 0;
    						
    						$this->redirect('index.php?g=Pig&m=index&a=index');
    					}
    }
    
    
    public function wx_login()
    {
    	$this->filterAttack();
    	
        $users_model = M("Users a");
    
        $city = '深圳市';
        if (isset($_REQUEST['city']))
            $city = urldecode($_REQUEST['city']);
        $login_name = $_REQUEST['openid'];
        $ticket = $_REQUEST['ticket'];
        $sign = $_REQUEST['sign'];
        $ticket2 = $_REQUEST['ticket2'];
        $sign2 = $_REQUEST['sign2'];
        $noncestr = $_REQUEST['noncestr'];
        $channel = $_REQUEST['channel'];
        
    	$openid = $_REQUEST['openid'];
    	$url = $openid . $ticket . $noncestr;
        $new_sign = md5(strtolower($url . C('LOGIN_KEY')));
        
        if ($openid == '')
        {
        	$this->redirect('index/newentry_after_pay');
        	return;
        }
        
        if ($new_sign != $sign)
        {
            echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";         
            return;
        }
        
        session('login_openid', $openid);
        session('login_ticket', $ticket);
        session('login_noncestr', $noncestr);
        session('login_sign', $sign);
 
        $url = 'wx_login' . $channel . $ticket2;
        $new_sign2 = md5(strtolower($url . C('LOGIN_KEY')));
        
        if ($new_sign2 != $sign2)
        {
            echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
            return;
        }
                
        $ua = '';
        if (isset($_REQUEST['ua']))
            $ua = $_REQUEST['ua'];
        session('ua', $ua);
        session('openid',  $_REQUEST['openid']);
                
        if ($login_name == null)
        {
            echo "<script>history.go(-1);</script>";
            return;
        }
    
        session('city', $city);
        
        if ($_REQUEST['channel'] == C('TEST_CHANNEL'))
        	session('is_admin_enter', '1');
        else
        {
            session('is_admin_enter', '0');
        }
    
        $username = $login_name;
        $user = $users_model->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left')->where("user_login='$username'")->field('a.*,b.channel_id,b.is_ban')->find();
        $ret = 0;
        if ($user == null)
            $ret = $this->do_register($username);
        else {
            $ch_user_db = M('channel_user_relation');

            if ($user['channel_id'] == 0 && isset($_REQUEST['channel']))
            {
                $user['channel_id'] = $_REQUEST['channel'];
            
                $ch_user_db = M('channel_user_relation');
            
                $ch_data = array(
                    'channel_id' => intval($_REQUEST['channel'])
                );
                $ch_user_db->where('user_id=' . $user['id'])->save($ch_data);
            }
            
            $wallet_db = M('wallet');
            if ($wallet_db->where("user_id=" . $user['id'])->find() == null)
            {
                $wallet = array(
                    'user_id' => $user['id'],
                    'money' => 0,
            		'money3' => floatval(C('BEGINNER_MONEY_GIFT')),
                    'money2' => 0
                );
                $wallet_db->add($wallet);
            }
            
            $action_log = M('user_action_log');
            $log_data = array(
                'user_id' => $user['id'],
                'action' => 'login',
                'ip' => get_client_ip(0, true),
                'create_time' => date('Y-m-d H:i:s'),
                'ua' => $ua
            );
            $action_log->add($log_data);
            
            // 不允许登录,跳转到别的地方
            if ($user['is_ban'] == 1)
            {
            	echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
                return;
            }
            
            $user['openid'] = $_REQUEST['openid'];
            $users_model->where("id=" . $user['id'])->setField('openid', $_REQUEST['openid']);
    
            session('user', $user);
    
            $ret = 1;
        }
         
        if ($ret <= 0)
        {
            echo "<script>history.go(-1);</script>";
        }
        else
        {
            $_SESSION['is_tips'] = 0;
            
           $this->redirect('index.php?g=Pig&m=index&a=index');
        }
    }
    
    
    public function wx_login_zp()
    {
    	$this->filterAttack();
    	
        $users_model = M("Users a");
    
        $city = '深圳市';
        if (isset($_REQUEST['city']))
            $city = urldecode($_REQUEST['city']);
        $login_name = $_REQUEST['openid'];
        $ticket = $_REQUEST['ticket'];
        $sign = $_REQUEST['sign'];
        $ticket2 = $_REQUEST['ticket2'];
        $sign2 = $_REQUEST['sign2'];
        $noncestr = $_REQUEST['noncestr'];
        $channel = $_REQUEST['channel'];
    
        $openid = $_REQUEST['openid'];
        $url = $openid . $ticket . $noncestr;
        $new_sign = md5(strtolower($url . C('LOGIN_KEY')));
    
        if ($new_sign != $sign)
        {
            echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
            return;
        }
    
        $url = 'wx_login' . $channel . $ticket2;
        $new_sign2 = md5(strtolower($url . C('LOGIN_KEY')));
    
        if ($new_sign2 != $sign2)
        {
            echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
            return;
        }
    
        $ua = '';
        if (isset($_REQUEST['ua']))
            $ua = $_REQUEST['ua'];
        session('ua', $ua);
        session('openid',  $_REQUEST['openid']);
    
        if ($login_name == null)
        {
            echo "<script>history.go(-1);</script>";
            return;
        }
    
        session('city', $city);
    
        if ($_REQUEST['channel'] == C('TEST_CHANNEL'))
            session('is_admin_enter', '1');
        else
        {
            session('is_admin_enter', '0');
        }
    
        $username = $login_name;
        $user = $users_model->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left')->where("user_login='$username'")->field('a.*,b.channel_id,b.is_ban')->find();
        $ret = 0;
        if ($user == null)
            $ret = $this->do_register($username);
        else {
            $ch_user_db = M('channel_user_relation');
    
            if ($user['channel_id'] == 0 && isset($_REQUEST['channel']))
            {
                $user['channel_id'] = $_REQUEST['channel'];
    
                $ch_user_db = M('channel_user_relation');
    
                $ch_data = array(
                    'channel_id' => intval($_REQUEST['channel'])
                );
                $ch_user_db->where('user_id=' . $user['id'])->save($ch_data);
            }
    
            $wallet_db = M('wallet');
            if ($wallet_db->where("user_id=" . $user['id'])->find() == null)
            {
                $wallet = array(
                    'user_id' => $user['id'],
                    'money' => 0,
                    'money3' => floatval(C('BEGINNER_MONEY_GIFT')),
                    'money2' => 0
                );
                $wallet_db->add($wallet);
            }
    
            $action_log = M('user_action_log');
            $log_data = array(
                'user_id' => $user['id'],
                'action' => 'login',
                'ip' => get_client_ip(0, true),
                'create_time' => date('Y-m-d H:i:s'),
                'ua' => $ua
            );
            $action_log->add($log_data);
    
            // 不允许登录,跳转到别的地方
            if ($user['is_ban'] == 1)
            {
                echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
                return;
            }
    
            $user['openid'] = $_REQUEST['openid'];
            $users_model->where("id=" . $user['id'])->setField('openid', $_REQUEST['openid']);
    
            session('user', $user);
    
            $ret = 1;
        }
         
        if ($ret <= 0)
        {
            echo "<script>history.go(-1);</script>";
        }
        else
        {
            $_SESSION['is_tips'] = 0;
    
            $this->redirect('index.php?g=Zp&m=index&a=main');
        }
    }
    
    public function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }    
    
    public function wx_login_ssc()
    {
        $this->filterAttack();
         
        $users_model = M("Users a");
    
        $city = '深圳市';
        if (isset($_REQUEST['city']))
            $city = urldecode($_REQUEST['city']);
        $login_name = $_REQUEST['openid'];
        $ticket = $_REQUEST['ticket'];
        $sign = $_REQUEST['sign'];
        $ticket2 = $_REQUEST['ticket2'];
        $sign2 = $_REQUEST['sign2'];
        $noncestr = $_REQUEST['noncestr'];
        $channel = $_REQUEST['channel'];
    
        $openid = $_REQUEST['openid'];
        $url = $openid . $ticket . $noncestr;
        $new_sign = md5(strtolower($url . C('LOGIN_KEY')));
    
        if ($new_sign != $sign)
        {
            echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
            return;
        }
    
        $url = 'wx_login' . $channel . $ticket2;
        $new_sign2 = md5(strtolower($url . C('LOGIN_KEY')));
    
        if ($new_sign2 != $sign2)
        {
            echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
            return;
        }
    
        $ua = '';
        if (isset($_REQUEST['ua']))
            $ua = $_REQUEST['ua'];
        session('ua', $ua);
        session('openid',  $_REQUEST['openid']);
    
        if ($login_name == null)
        {
            echo "<script>history.go(-1);</script>";
            return;
        }
    
        session('city', $city);
    
        if ($_REQUEST['channel'] == C('TEST_CHANNEL'))
            session('is_admin_enter', '1');
        else
        {
            session('is_admin_enter', '0');
        }
    
        $username = $login_name;
        $user = $users_model->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.id', 'left')->where("user_login='$username'")->field('a.*,b.channel_id,b.is_ban')->find();
        $ret = 0;
        if ($user == null)
            $ret = $this->do_register($username);
        else {
        	
            $ch_user_db = M('channel_user_relation');
    
            if ($user['channel_id'] == 0 && isset($_REQUEST['channel']))
            {
                $user['channel_id'] = $_REQUEST['channel'];
    
                $ch_user_db = M('channel_user_relation');
    
                $ch_data = array(
                    'channel_id' => intval($_REQUEST['channel'])
                );
                $ch_user_db->where('user_id=' . $user['id'])->save($ch_data);
            }
    
            
            $wallet_db = M('wallet');
            if ($wallet_db->where("user_id=" . $user['id'])->find() == null)
            {
                $wallet = array(
                    'user_id' => $user['id'],
                    'money' => 0,
                    'money3' => floatval(C('BEGINNER_MONEY_GIFT')),
                    'money2' => 0
                );
                $wallet_db->add($wallet);
            }
    
            $action_log = M('user_action_log');
            $log_data = array(
                'user_id' => $user['id'],
                'action' => 'login',
                'ip' => get_client_ip(0, true),
                'create_time' => date('Y-m-d H:i:s'),
                'ua' => $ua
            );
            $action_log->add($log_data);
    
            // 不允许登录,跳转到别的地方
            if ($user['user_status'] == 0)
            {
                echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
                return;
            }
            
            // 不允许登录,跳转到别的地方
            if ($user['is_ban'] == 1)
            {
            	echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
            	return;
            }
            
            if (empty($user['user_activation_key']))
            {
                for ($i=0; $i<3; $i++)
                {
                    $code = $this->getNonceStr(6);
                    if ($users_model->where("user_activation_key='$code'")->find() == null)
                    {
                        $user['user_activation_key'] = $code;
                         
                        $users_model->where("id=" . $user['id'])->setField('user_activation_key' , $user['user_activation_key']);
            
                        break;
                    }
                }
            }            
    
            $user['openid'] = $_REQUEST['openid'];
            $users_model->where("id=" . $user['id'])->setField('openid', $_REQUEST['openid']);
    
            session('user', $user);
    
            $ret = 1;
        }
         
        if ($ret <= 0)
        {
            echo "<script>history.go(-1);</script>";
        }
        else
        {
            $_SESSION['is_tips'] = 0;
    
            $this->redirect('index.php?g=Qqonline&m=index&a=main');
        }
    }
    
    public function newentry_ssc()
    {
    	if (C('IS_OPEN_SSC') != '1')
    	{
    		//$this->newentry();
    		return;
    	}
    	
        $this->filterAttack();
         
        $channel = '0';
        $is_admin = '0';
        if (isset($_REQUEST['channel']))
            $channel = $_REQUEST['channel'];
    
        if ($channel == C('TEST_CHANNEL'))
        {
            $_SESSION['is_admin_enter'] = '1';
        }
        else
        {
            $_SESSION['is_admin_enter'] = '0';
        }
    
        if (C('IS_STOPPED') == '1')
        {
            if ($_SESSION['is_admin_enter'] != '1')
            {
                if (empty(C('STOP_GOURL')))
                {
                    echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
                }
                else
                {
                    $goto_url = str_replace('&amp;', '&', C('STOP_GOURL'));
                    redirect($goto_url);
                }
    
                return;
            }
        }
    
        $ticket = $_REQUEST['ticket'];
        $sign = $_REQUEST['sign'];
        $new_sign = md5($channel . $ticket . C('LOGIN_KEY'));
    
        if ($new_sign != $sign)
        {
            if ($_SESSION['is_admin_enter'] != '1')
            {
                echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
                return;
            }
        }
    
        $hosts_db = M('hostnames');
    
        if (!$this->is_weixin())
        {
            vendor('phpqrcode.phpqrcode');//导入类库
    
            include APP_PATH . "Common/Common/upload.php";
    
            $data = M('ads_template')->where()->order("id asc")->find();
    
            // 不是微信,显示新的二维码
            $url = $data['url'];
            $level = 'L';
            $size = 4;
    
            $channel_id = $channel;
    
            $channel_db = M('channels');
            $admin_channel = $channel_db->where("id=$channel_id")->find();
            $channel_user_id = 0;
            if ($admin_channel != null)
            {
                $channel_user_id = $admin_channel['admin_user_id'];
            }
    
            $url = str_replace("{channel_id}", $channel_id, $url);
    
            // 获取域名生成二进制
            $hosts = $hosts_db->where('status=1 and `type` in (0,2)')->order('`type` asc, update_time desc')->select();
    
            shuffle($hosts);
            $host = $hosts[0];
    
            $url = str_replace("{hostname}", $host['hostname'], $url);
            $ticket = time();
            $sign = md5($channel_id . $ticket . C('LOGIN_KEY'));
            $url .= '&ticket=' . $ticket . '&sign=' . $sign;
    
            $ids = date('YmdHis') . '_c' . '_' . $channel_id;
    
            $out_file = './data/upload/' . $ids . '.png';
            \QRcode::png($url,$out_file,$level,$size,2);
    
            $smeta = json_decode($data['smeta'],true);
    
            $bg_image = './data/upload/'.$smeta['thumb'];
    
            $ids = date('YmdHis') . '_c' . '_' . $channel_id . '_out';
    
            $out_file2 = './data/upload/' . $ids . '.png';
    
            $bg_image_c = imagecreatefromstring(file_get_contents($bg_image));
    
    
            $col = imagecolorallocate($bg_image_c,255,255,255);
            $content = 'ID:' . $channel_user_id;
            imagestring($bg_image_c,5, floatval($data['add_x']), floatval($data['add_y']) + floatval($data['height']) + 10,$content,$col);
    
            image_copy_image($bg_image_c, $out_file, floatval($data['add_x']), floatval($data['add_y']), floatval($data['width']), floatval($data['height']), $out_file2);
    
            $this->assign('src', $out_file2);
    
            $this->display(':qr');
    
            return;
        }
    
        // 随机选择一个中转域名
        $hosts = $hosts_db->where('status=1 and `type` in (3,2)')->order('`type` desc, update_time desc')->select();
        shuffle($hosts);
        $host = $hosts[0];
    
        $ticket = time();
        $url = 'redir' . $channel . $ticket;
        $sign = md5(strtolower($url . C('LOGIN_KEY')));
        $goto_url = "http://"  . $host['hostname'] . '/portal/index/redir_ssc?channel=' . $channel . '&ticket=' . $ticket . '&sign=' . $sign;
    
        redirect($goto_url);
    }
    
 
    function is_weixin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }
    
    public function entry()
    {
    	echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},1000);</script>";
    }
    
    public function logout()
    {
    	session_unset(session('login_openid'));
    	session_unset(session('login_ticket'));
    	session_unset(session('login_noncestr'));
    	session_unset(session('login_sign'));
    	session_unset(session('tx_appid'));
    	session_unset(session('tx_openid'));
    	
    	echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},1000);</script>";
    }
    
 // 入口
    public function newentry()
    {
    	if (C('IS_OPEN_DUOBAO') != '1')
    	{
    		//$this->newentry();
    		return;
    	}
    	
    	$this->filterAttack();
    	
        $channel = '0';
        $is_admin = '0';
        if (isset($_REQUEST['channel']))
            $channel = $_REQUEST['channel'];
        
            if ($channel == C('TEST_CHANNEL'))
            {
            	$_SESSION['is_admin_enter'] = '1';
            }
            else
            {
            	$_SESSION['is_admin_enter'] = '0';
            }
        
        if (C('IS_STOPPED') == '1')
        {
        	if ($_SESSION['is_admin_enter'] != '1')
        	{
        	    if (empty(C('STOP_GOURL')))
        	    {
        	        echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
        	    }
        	    else
        	    {
        	       $goto_url = str_replace('&amp;', '&', C('STOP_GOURL'));
        		   redirect($goto_url);
        	    }
        		
        		return;
        	}
        }
        
        $ticket = $_REQUEST['ticket'];
        $sign = $_REQUEST['sign'];
        $new_sign = md5($channel . $ticket . C('LOGIN_KEY'));
        
        if ($new_sign != $sign)
        {
        	if ($_SESSION['is_admin_enter'] != '1')
        	{
        		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
        		return;
        	}
        }
        
        // 这里做个清空
        session_unset(session('tx_appid'));
        session_unset(session('tx_openid'));
        
        $hosts_db = M('hostnames');
        
        if (!$this->is_weixin())
        {
            vendor('phpqrcode.phpqrcode');//导入类库
            
            include APP_PATH . "Common/Common/upload.php";
            
            $data = M('ads_template')->where()->order("id asc")->find();
            
            // 不是微信,显示新的二维码
            $url = $data['url'];
            $level = 'L';
            $size = 4;
            
            $channel_id = $channel;
            
            $channel_db = M('channels');
            $admin_channel = $channel_db->where("id=$channel_id")->find();
            $channel_user_id = 0;
            if ($admin_channel != null)
            {
                $channel_user_id = $admin_channel['admin_user_id'];
            }
            
            $url = str_replace("{channel_id}", $channel_id, $url);

            // 获取域名生成二进制            
            $hosts = $hosts_db->where('status=1 and `type` in (0,2)')->order('`type` asc, update_time desc')->select();
            
            shuffle($hosts);
            $host = $hosts[0];
            
            $url = str_replace("{hostname}", $host['hostname'], $url);
            $ticket = time();
            $sign = md5($channel_id . $ticket . C('LOGIN_KEY'));
            $url .= '&ticket=' . $ticket . '&sign=' . $sign;

            $ids = date('YmdHis') . '_c' . '_' . $channel_id;
            
            $out_file = './data/upload/' . $ids . '.png';
            \QRcode::png($url,$out_file,$level,$size,2);
            
            $smeta = json_decode($data['smeta'],true);
            
            $bg_image = './data/upload/'.$smeta['thumb'];
            
            $ids = date('YmdHis') . '_c' . '_' . $channel_id . '_out';
            
            $out_file2 = './data/upload/' . $ids . '.png';
            
            $bg_image_c = imagecreatefromstring(file_get_contents($bg_image));
            
  
            $col = imagecolorallocate($bg_image_c,255,255,255);
            $content = 'ID:' . $channel_user_id;
            imagestring($bg_image_c,5, floatval($data['add_x']), floatval($data['add_y']) + floatval($data['height']) + 10,$content,$col);            
            
            image_copy_image($bg_image_c, $out_file, floatval($data['add_x']), floatval($data['add_y']), floatval($data['width']), floatval($data['height']), $out_file2);
            
            $this->assign('src', $out_file2);
            
            $this->display(':qr');
            
            return;
        }
        
        // 随机选择一个中转域名
        //$hosts = $hosts_db->where('status=1 and `type` in (3,2)')->order('`type` desc, update_time desc')->select();
        //shuffle($hosts);
        //$host = $hosts[0];
        //$host = $hosts_db->where('status=1 and `type` in (3,2)')->order('`type` desc, update_time desc')->find();
        $host = $hosts_db->where('status=1 and `type` in (3,2)')->order('id desc')->find();
        
        $ticket = time();
        $url = 'redir' . $channel . $ticket;
        $sign = md5(strtolower($url . C('LOGIN_KEY')));
        $goto_url = "http://"  . $host['hostname'] . '/portal/index/redir?channel=' . $channel . '&ticket=' . $ticket . '&sign=' . $sign;
        
        header("Location: $goto_url");
        
        
        //redirect($goto_url);
    }
    
    public function newentry_zp()
    {
    	if (C('IS_OPEN_ZP') != '1')
    	{
    		//$this->newentry();
    		return;
    	}
    	
    	$this->filterAttack();
    	
        $channel = '0';
        $is_admin = '0';
        if (isset($_REQUEST['channel']))
            $channel = $_REQUEST['channel'];
    
        if ($channel == C('TEST_CHANNEL'))
        {
            $_SESSION['is_admin_enter'] = '1';
        }
        else
        {
            $_SESSION['is_admin_enter'] = '0';
        }
    
        if (C('IS_STOPPED') == '1')
        {
            if ($_SESSION['is_admin_enter'] != '1')
            {
                if (empty(C('STOP_GOURL')))
                {
                    echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
                }
                else
                {
            	       $goto_url = str_replace('&amp;', '&', C('STOP_GOURL'));
            	       redirect($goto_url);
                }
    
                return;
            }
        }
    
        $ticket = $_REQUEST['ticket'];
        $sign = $_REQUEST['sign'];
        $new_sign = md5($channel . $ticket . C('LOGIN_KEY'));
    
        if ($new_sign != $sign)
        {
            if ($_SESSION['is_admin_enter'] != '1')
            {
                echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
                return;
            }
        }
    
        $hosts_db = M('hostnames');
    
        if (!$this->is_weixin())
        {
            vendor('phpqrcode.phpqrcode');//导入类库
    
            include APP_PATH . "Common/Common/upload.php";
    
            $data = M('ads_template')->where()->order("id asc")->find();
    
            // 不是微信,显示新的二维码
            $url = $data['url'];
            $level = 'L';
            $size = 4;
    
            $channel_id = $channel;
    
            $channel_db = M('channels');
            $admin_channel = $channel_db->where("id=$channel_id")->find();
            $channel_user_id = 0;
            if ($admin_channel != null)
            {
                $channel_user_id = $admin_channel['admin_user_id'];
            }
    
            $url = str_replace("{channel_id}", $channel_id, $url);
    
            // 获取域名生成二进制
            $hosts = $hosts_db->where('status=1 and `type` in (0,2)')->order('`type` asc, update_time desc')->select();
    
            shuffle($hosts);
            $host = $hosts[0];
    
            $url = str_replace("{hostname}", $host['hostname'], $url);
            $ticket = time();
            $sign = md5($channel_id . $ticket . C('LOGIN_KEY'));
            $url .= '&ticket=' . $ticket . '&sign=' . $sign;
    
            $ids = date('YmdHis') . '_c' . '_' . $channel_id;
    
            $out_file = './data/upload/' . $ids . '.png';
            \QRcode::png($url,$out_file,$level,$size,2);
    
            $smeta = json_decode($data['smeta'],true);
    
            $bg_image = './data/upload/'.$smeta['thumb'];
    
            $ids = date('YmdHis') . '_c' . '_' . $channel_id . '_out';
    
            $out_file2 = './data/upload/' . $ids . '.png';
    
            $bg_image_c = imagecreatefromstring(file_get_contents($bg_image));
    
    
            $col = imagecolorallocate($bg_image_c,255,255,255);
            $content = 'ID:' . $channel_user_id;
            imagestring($bg_image_c,5, floatval($data['add_x']), floatval($data['add_y']) + floatval($data['height']) + 10,$content,$col);
    
            image_copy_image($bg_image_c, $out_file, floatval($data['add_x']), floatval($data['add_y']), floatval($data['width']), floatval($data['height']), $out_file2);
    
            $this->assign('src', $out_file2);
    
            $this->display(':qr');

            return;
        }
    
        // 随机选择一个中转域名
        $hosts = $hosts_db->where('status=1 and `type` in (3,2)')->order('`type` desc, update_time desc')->select();
        shuffle($hosts);
        $host = $hosts[0];

        $ticket = time();
        $url = 'redir' . $channel . $ticket;
        $sign = md5(strtolower($url . C('LOGIN_KEY')));
        $goto_url = "http://"  . $host['hostname'] . '/portal/index/redir_zp?channel=' . $channel . '&ticket=' . $ticket . '&sign=' . $sign;

        redirect($goto_url);
    }
    
    public function newentry_after_pay()
    {
    	$this->filterAttack();
    	
    		$hosts_db = M('hostnames');
    		
    		// 随机选择一个中转域名
    		$hosts = $hosts_db->where('status=1 and `type` in (3,2)')->order('`type` desc, update_time desc')->select();
    		shuffle($hosts);
    		$host = $hosts[0];
    		
    		$ticket = time();
    		$url = 'redir' . $channel . $ticket;
    		$sign = md5(strtolower($url . C('LOGIN_KEY')));
    		$goto_url = "http://"  . $host['hostname'] . '/portal/index/redir?channel=' . $channel . '&ticket=' . $ticket . '&sign=' . $sign;
    		
    		redirect($goto_url);
    }
    
    public function newentry_after_pay_zp()
    {
    	$this->filterAttack();
    	
    	$hosts_db = M('hostnames');
    	
    	// 随机选择一个中转域名
    	$hosts = $hosts_db->where('status=1 and `type` in (3,2)')->order('`type` desc, update_time desc')->select();
    	shuffle($hosts);
    	$host = $hosts[0];
    	
    	$ticket = time();
    	$url = 'redir' . $channel . $ticket;
    	$sign = md5(strtolower($url . C('LOGIN_KEY')));
    	$goto_url = "http://"  . $host['hostname'] . '/portal/index/redir_zp?channel=' . $channel . '&ticket=' . $ticket . '&sign=' . $sign;
    	
    	redirect($goto_url);
    }
    
    // 重新做多一次跳转
    public function redir()
    {
    	$this->filterAttack();
    	
    	$hosts_db = M('hostnames');
    	
    	$channel = $_REQUEST['channel'];
    	$ticket = $_REQUEST['ticket'];
    	$sign = $_REQUEST['sign'];
    	
    	$url = 'redir' . $channel . $ticket;
    	$new_sign = md5(strtolower($url . C('LOGIN_KEY')));

    	if ($new_sign != $sign)
    	{
    		echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
    		return;
    	}

    	$login_openid= session('login_openid');
    	$login_ticket = session('login_ticket');
    	$login_noncestr= session('login_noncestr');
    	$login_sign= session('login_sign');
    	
    	if ($login_openid== null || $login_openid== '' || $login_ticket== null || $login_ticket== '' || $login_noncestr== null || $login_noncestr== '' || $login_sign== null || $login_sign== '')
    	{
	    	$goto_url = C('LOGIN_URL');
	    	
	    	// 落地域名做一次随机
	    	
	    	$hosts = $hosts_db->where('status=1 and `type` in (1,2)')->order('`type` asc, update_time desc')->select();
	    	shuffle($hosts);
	    	$host = $hosts[0];
	    	
	    	$ticket = time();
	    	$url = 'wx_login' . $channel . $ticket;
	    	$sign = md5(strtolower($url . C('LOGIN_KEY')));
	    	$return_url = "http://"  . $host['hostname']. '/portal/index/wx_login?channel=' . $channel . '&ticket2=' . $ticket . '&sign2=' . $sign;
	    	
	    	$jsapi_ticket = time();
	    	$jsapi_sign = md5(strtolower(urlencode($return_url) . $jsapi_ticket . C('LOGIN_KEY')));
	    	
	    	$gotourl = $goto_url . '?req_url=' . urlencode($return_url) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign;
	    	
	    	header("Location: $gotourl");
    	}
    	else
    	{
    		
    		// 落地域名做一次随机
    		
    		$hosts = $hosts_db->where('status=1 and `type` in (1,2)')->order('`type` asc, update_time desc')->select();
    		shuffle($hosts);
    		$host = $hosts[0];
    		
    		//$host = $_SERVER['HTTP_HOST'];
    		$ticket = time();
    		$url = 'wx_login' . $channel . $ticket;
    		$sign = md5(strtolower($url . C('LOGIN_KEY')));
    		$return_url = "http://"  . $host['hostname'] . '/portal/index/wx_login_direct?channel=' . $channel . '&ticket2=' . $ticket . '&sign2=' . $sign;
    		
    		$jsapi_ticket = time();
    		$jsapi_sign = md5(strtolower(urlencode($return_url) . $jsapi_ticket . C('LOGIN_KEY')));
    		
    		$goto_url = $return_url. '&openid=' . $login_openid. '&noncestr=' . $login_noncestr. '&ticket=' . $login_ticket . '&sign=' . $login_sign;

    		//header("Location: $goto_url");
    		$this->redirect('index/wx_login_direct',
    				array(
    						'channel' => $channel,
    						'ticket2' => $ticket,
    						'sign2' => $sign,
    						'openid' => $login_openid,
    						'noncestr' => $login_noncestr,
    						'ticket' => $login_ticket,
    						'sign' => $login_sign
    				));
    	}
    	
    	//redirect($goto_url . '?req_url=' . urlencode($return_url) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign);
    }
    
    public function redir_zp()
    {
    	if (C('IS_OPEN_ZP') != '1')
    	{
    		$this->redir();
    		return;
    	}
    	
    	$this->filterAttack();
    	
        $hosts_db = M('hostnames');
         
        $channel = $_REQUEST['channel'];
        $ticket = $_REQUEST['ticket'];
        $sign = $_REQUEST['sign'];
         
        $url = 'redir' . $channel . $ticket;
        $new_sign = md5(strtolower($url . C('LOGIN_KEY')));
         
        if ($new_sign != $sign)
        {
            echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
            return;
        }
        
        $openid = session('login_openid');
        $ticket = session('login_ticket');
        $noncestr = session('login_noncestr');
        $sign = session('login_sign');
    
        if (true)//if ($openid == null || $openid == '' || $ticket == null || $ticket == '' || $noncestr == null || $noncestr == '' || $sign == null || $sign == '')
        {
	        $goto_url = C('LOGIN_URL');
	         
	        // 落地域名做一次随机
	        $hosts = $hosts_db->where('status=1 and `type` in (1,2)')->order('`type` asc, update_time desc')->select();
	        shuffle($hosts);
	        $host = $hosts[0];
	        $ticket = time();
	        $url = 'wx_login' . $channel . $ticket;
	        $sign = md5(strtolower($url . C('LOGIN_KEY')));
	        $return_url = "http://"  . $host['hostname'] . '/portal/index/wx_login_zp?channel=' . $channel . '&ticket2=' . $ticket . '&sign2=' . $sign;
	         
	        $jsapi_ticket = time();
	        $jsapi_sign = md5(strtolower(urlencode($return_url) . $jsapi_ticket . C('LOGIN_KEY')));
	         
	        redirect($goto_url . '?req_url=' . urlencode($return_url) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign);
        } 
        else
        {
        	
        }
   }
   
   public function redir_ssc()
   {
       $this->filterAttack();
   
       $hosts_db = M('hostnames');
   
       $channel = $_REQUEST['channel'];
       $ticket = $_REQUEST['ticket'];
       $sign = $_REQUEST['sign'];
   
       $url = 'redir' . $channel . $ticket;
       $new_sign = md5(strtolower($url . C('LOGIN_KEY')));
   
       if ($new_sign != $sign)
       {
           echo "<script>setTimeout(function(){WeixinJSBridge.call('closeWindow');},2000);</script>";
           return;
       }
        
       $openid = session('login_openid');
       $ticket = session('login_ticket');
       $noncestr = session('login_noncestr');
       $sign = session('login_sign');
        
       if (true)//if ($openid == null || $openid == '' || $ticket == null || $ticket == '' || $noncestr == null || $noncestr == '' || $sign == null || $sign == '')
       {
           $goto_url = C('LOGIN_URL');
            
           // 落地域名做一次随机
           $hosts = $hosts_db->where('status=1 and `type` in (1,2)')->order('`type` asc, update_time desc')->select();
           shuffle($hosts);
           $host = $hosts[0];
           $ticket = time();
           $url = 'wx_login' . $channel . $ticket;
           $sign = md5(strtolower($url . C('LOGIN_KEY')));
           $return_url = "http://"  . $host['hostname'] . '/portal/index/wx_login_ssc?channel=' . $channel . '&ticket2=' . $ticket . '&sign2=' . $sign;
            
           $jsapi_ticket = time();
           $jsapi_sign = md5(strtolower(urlencode($return_url) . $jsapi_ticket . C('LOGIN_KEY')));
            
           redirect($goto_url . '?req_url=' . urlencode($return_url) . '&jsapi_ticket=' . $jsapi_ticket . '&sha=' . $jsapi_sign);
       }
       else
       {
   
       }
   }
    
    public function locate () {
        $latitude = $_REQUEST['latitude'];
        $longitude = $_REQUEST['longitude'];
        $cityname = $_REQUEST['cityname'];
        
        session('latitude', $latitude);
        session('longitude', $longitude);
        session('cityname', $cityname);
        
        echo json_encode(array('code' => 0));
    }
    
    public function ban_url() {
        $this->assign('tips', '系统维护中');
        $this->display(':error');
    }

}


