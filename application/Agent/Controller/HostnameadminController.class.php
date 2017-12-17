<?php

/**
 * 域名管理
*/
namespace Agent\Controller;
use Common\Controller\AdminbaseController;
class HostnameadminController extends AdminbaseController {
    function index() {

        $model=M("hostnames a");
        
        $where = '1';

        if (isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != null)
            $where .= ' and a.hostname like "%' . $_REQUEST['keyword'] . '%"';

        if (isset($_REQUEST['status']) && $_REQUEST['status'] != null)
            $where .= ' and a.status=' . $_REQUEST['status'] . '';
        else 
        	$where .= ' and a.status>-1';
        
        if (isset($_REQUEST['type']) && $_REQUEST['type'] != null)
            $where .= ' and a.type=' . $_REQUEST['type'] . '';

        $count=$model->where($where)->count();
        $page = $this->page($count, 40);
        $lists = $model
        ->where($where)
        ->order("id asc")
        ->limit($page->firstRow . ',' . $page->listRows)
        ->select();
        
        $this->assign('filter', $_REQUEST);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
    
        $this->display();
    }
    
    function add() {
        $this->display();
    }
    
    function add_post() {
        $db = M('hostnames');
    
        $hostname = $_REQUEST['hostname'];
        $status = $_REQUEST['status'];
        $type = intval($_REQUEST['type']);
    
        $data = array(
            'hostname' => $hostname,
            'status' => $status,
        	'type' => $type,
            'create_time' => date('Y-m-d H:i:s')
        );
         
        $db->where()->add($data);
    
        $this->success('添加成功');
    }
    
    function edit($id) {
        $db = M('hostnames');
        
        $vo = $db->where('id=' . $id)->find();

        $this->assign('vo', $vo);
        
        $this->display();
    }
    
    function edit_post() {
        $db = M('hostnames');
    
        $id = intval($_REQUEST['id']);
        $hostname = $_REQUEST['hostname'];
        $status = intval($_REQUEST['status']);
        $type = intval($_REQUEST['type']);
        
        $data = array(
        	'id' => $id,
            'hostname' => $hostname,
            'status' => $status,
        	'type' => $type,
        );
       
        $db->where('id=' . $id)->save($data);
    
        $this->success('修改成功');
    }
    
    function delete($id) {
        $db = M('hostnames');
        
        if ($db->where('id=' . $id)->count() ==  0)
            $this->error("删除失败！");
        else {
            $db->where('id=' . $id)->delete();
            $this->success("删除成功！");
        }        
    }
    
    function get_msg($url){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_TIMEOUT,5);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }
    
    function check_domain($id) {
    
        return;
        
        include_once 'wxpay/DomainDetech.php';
        $db = M('hostnames');
        $host = $db->where('id=' . $id)->find();
        
        $detech_url = $host['hostname']; //填写你要检测的域
        
        $domain_obj = new \DomainDetech('0', '1');
        
        $cret = $domain_obj->http_request('http://' . $detech_url . '/check_hostname.txt', null, 10);
        
        if ($cret != 'ok')
        {
            $data = array(
                'update_time' => date('Y-m-d H:i:s'),
                'status' => 2
            );
            $db->where('id=' . $id)->save($data);
        
            $this->error('域名配置错误');
            return;
        }        
    
        $api_url = "http://vip.weixin139.com/weixin/wx_domain.php?user=735999&key=7e477bbdb11a01199ea96ed41c7dda90&domain=".$detech_url;
        $content = $this->get_msg($api_url);
        $data = json_decode($content,true);
        
        if ($data['status'] == 0)
        {
            $data = array(
                'update_time' => date('Y-m-d H:i:s'),
                'status' => 1
            );
            $db->where('id=' . $id)->save($data);
    
            $this->success('域名检测正常');
        }
        else if ($data['status']==2)
        {
            $data = array(
                'update_time' => date('Y-m-d H:i:s'),
                'status' => -1
            );
            $db->where('id=' . $id)->save($data);
    
            $this->error('域名被封');
        }
        else
        {
            $this->error("API检测其他错误:" . json_encode($data));
        }
    }
    
    function check_domain2($id) {
        
        $db = M('hostnames');
        $host = $db->where('id=' . $id)->find();
        
        include_once 'wxpay/DomainDetech.php';
        $app_id     =  'o3KnIv3tEWIfo7S_Ll6axTAqwraU';
        $app_secret = 'aa325103'; //填写你设置的密码
        
        
        //单个域名查询
        $detech_url = 'http://' . $host['hostname']; //填写你要检测的域

        $domain_obj = new \DomainDetech($app_id, $app_secret);
        
        $cret = $domain_obj->http_request($detech_url . '/check_hostname.txt', null, 10);
        
        if ($cret != 'ok')
        {
            $data = array(
                'update_time' => date('Y-m-d H:i:s'),
                'status' => 2
            );
            $db->where('id=' . $id)->save($data);
            
            $this->error('域名配置错误');
            return;
        }

        $rs         = $domain_obj->run($detech_url);

        $ret = $rs;//json_decode($rs, true);
        
        // 检测域名对应的服务器是否正常

        if ($ret['status'] == 401)
            $this->error("API授权已过期");
        else if ($ret['status'] == 1)
        {
            $data = array(
                'update_time' => date('Y-m-d H:i:s'),
                'status' => 1
            );
            $db->where('id=' . $id)->save($data);
            
            $this->success('域名检测正常');
        }
        else if ($ret['status'] == -1)
        {
            $data = array(
                'update_time' => date('Y-m-d H:i:s'),
                'status' => -1
            );
            $db->where('id=' . $id)->save($data);
            
            $this->error('域名被封');
        }
        else
        {
            $this->error("API检测其他错误:" . json_encode($ret['status']));
        }
    }
    
    function batch_check_domain() {
    
        include_once 'wxpay/DomainDetech.php';
        $app_id     =  'o3KnIv3tEWIfo7S_Ll6axTAqwraU';
        $app_secret = 'aa325103'; //填写你设置的密码
        
        $domain_obj = new \DomainDetech($app_id, $app_secret);
        
        $ids = join(',' , $_POST['ids']);
        
        $db = M('hostnames');
        $hosts = $db->where('id in (' . $ids . ')')->select();
    
        include_once 'wxpay/DomainDetech.php';
        $domain_obj = new \DomainDetech('000', '1111');
        
        for ($i=0; $i<count($hosts); $i++)
        {
            $host = $hosts[$i];
            
            $detech_url = $host['hostname']; //填写你要检测的域
            
            $cret = $domain_obj->http_request('http://' . $detech_url . '/check_hostname.txt', null, 10);
            
            if ($cret != 'ok')
            {
                $data = array(
                    'update_time' => date('Y-m-d H:i:s'),
                    'status' => 2
                );
                $db->where('id=' . $host['id'])->save($data);
                continue;
            }
                        
    
            $api_url = "http://vip.weixin139.com/weixin/wx_domain.php?user=735999&key=7e477bbdb11a01199ea96ed41c7dda90&domain=".$detech_url;
            $content = $this->get_msg($api_url);
            $ret = json_decode($content,true);

            if ($ret['status'] == 0)
            {
                $data = array(
                    'update_time' => date('Y-m-d H:i:s'),
                    'status' => 1
                );
                $db->where('id=' . $host['id'])->save($data);
            }
            else if ($ret['status'] == 2)
            {
                $data = array(
                    'update_time' => date('Y-m-d H:i:s'),
                    'status' => -1
                );
                $db->where('id=' . $host['id'])->save($data);
            }
            else
            {
                //$this->error("域名检测[$detech_url]其他错误:" . $ret['status']);
            }
        }
        

        $this->success("域名检测完成");
    }
    
    function batch_check_domain2() {
    
        include_once 'wxpay/DomainDetech.php';
        $app_id     =  'o3KnIv3tEWIfo7S_Ll6axTAqwraU';
        $app_secret = 'aa325103'; //填写你设置的密码
    
        $domain_obj = new \DomainDetech($app_id, $app_secret);
    
        $ids = join(',' , $_POST['ids']);
    
        $db = M('hostnames');
        $hosts = $db->where('id in (' . $ids . ')')->select();
    
        for ($i=0; $i<count($hosts); $i++)
        {
        $host = $hosts[$i];
    
        //单个域名查询
        $detech_url = 'http://' . $host['hostname']; //填写你要检测的域
    
            $cret = $domain_obj->http_request($detech_url . '/check_hostname.txt', null, 10);
    
            if ($cret != 'ok')
            {
                $data = array(
                'update_time' => date('Y-m-d H:i:s'),
                    'status' => 2
                );
                    $db->where('id=' . $host['id'])->save($data);
                        continue;
        }
    
        $rs         = $domain_obj->run($detech_url);
    
        $ret = $rs;//json_decode($rs, true);
    
        if ($ret['status'] == 401)
        {
        $this->error("API授权已过期");
        return;
        }
        else if ($ret['status'] == 1)
                    {
                    $data = array(
                    'update_time' => date('Y-m-d H:i:s'),
                    'status' => 1
                    );
                    $db->where('id=' . $host['id'])->save($data);
                    }
                    else if ($ret['status'] == -1)
                    {
                        $data = array(
                        'update_time' => date('Y-m-d H:i:s'),
                            'status' => -1
                        );
                            $db->where('id=' . $host['id'])->save($data);
                            }
                            else
                                {
                                $this->error("域名检测[$detech_url]其他错误:" . $ret['status']);
    
                                return;
                    }
                    }
    
    
                        $this->success("域名检测完成");
                    }
    
    public function batch_delete()
    {
        $ids = join(',' , $_POST['ids']);
        
        $db = M('hostnames');
        $db->where('id in (' . $ids . ')')->delete();
        
        $this->success("删除完成");
    }
    
    public function batch_set_status()
    {
        $ids = join(',' , $_POST['ids']);
        $status = $_REQUEST['status'];
        
        $db = M('hostnames');
        $db->where('id in (' . $ids . ')')->setField('status', $status);
        
        $this->success("设置状态 完成");
    }
    
    public function batch_set_type()
    {
        $ids = join(',' , $_POST['ids']);
        $type = $_REQUEST['type'];
    
        $db = M('hostnames');
        $db->where('id in (' . $ids . ')')->setField('type', $type);
        $db->where('id in (' . $ids . ')')->setField('status', 1);
    
        $this->success("设置状态 完成");
    }    
}
