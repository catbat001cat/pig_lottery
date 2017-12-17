<?php

/**
 * 渠道管理
*/
namespace Agent\Controller;
use Common\Controller\AdminbaseController;
class ChanneladminController extends AdminbaseController {
    function index() {

        $model=M("channels a");
                
        $session_admin_id=session('ADMIN_ID');
        
        $channel = $model->where("admin_user_id=$session_admin_id")->find();
        
        $where = "1";
        
        $_GET['keyword'] = $_REQUEST['keyword'];
        
        if (isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != null)
            $where .= ' and (a.name like "%' . $_REQUEST['keyword'] . '%" or a.memo like "%' . $_REQUEST['keyword'] . '%" or a.contact like "%' . $_REQUEST['keyword'] . '%" or a.telephone like "%'. $_REQUEST['keyword'].'%")';

        $count=$model->where($where)->count();
        $page = $this->page($count, 20);
        $lists = $model
        ->where($where)
        ->order("id DESC")
        ->field('a.*,(select COUNT(*) from sp_channel_user_relation b where b.channel_id=a.id and b.is_visible=1) as user_count
            ,(select COUNT(*) from sp_channel_user_relation b where b.channel_id=a.id and b.is_visible=0) as user_count_deduct')
        ->limit($page->firstRow . ',' . $page->listRows)
        ->select();
        
        $order_db = M('orders a');
        
        for ($i=0; $i<count($lists); $i++)
        {
            $channel_id = $lists[$i]['id'];
            
            // 获取下面所有子渠道的收入
            $all_childs = get_all_channel_childs($channel_id);
            $all_childs_ids = get_id_from_all_channel_childs($all_childs);
            if ($all_childs_ids == '')
                $all_childs_ids = $channel_id;
            else
                $all_childs_ids .= ',' . $channel_id;
            
            $lists[$i]['total_income'] = $order_db->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id and b.is_visible=1')->where("b.channel_id in ($all_childs_ids) and is_payed=1 and is_deduct=0")->sum('a.price');
            $lists[$i]['total_income_orign'] = $order_db->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id and b.is_visible=1')->where("b.channel_id in ($all_childs_ids) and is_payed=1 and is_deduct=0")->sum('a.price');
            //$lists[$i]['total_income_deduct'] = $order_db->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id and b.is_visible=0')->where("b.channel_id=$channel_id and is_payed=1")->sum('a.price');
            $lists[$i]['total_income_deduct'] = $order_db->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id and b.is_visible=1')->where("b.channel_id in ($all_childs_ids) and is_payed=1 and is_deduct=1")->sum('a.price');
        }
        
        // 计算分成
        for ($i=0; $i<count($lists); $i++)
        {
            if ($lists[$i]['id'] == null)
            {
                $divide_ratio = 1;
                
                $divide_ratio_channel = 1;
            }
            else
            {
                $divide_ratio = $this->get_divide_ratio($lists[$i]['id']);
                
                $divide_ratio_channel = $this->get_divide_ratio_to_channel($channel['id'], $lists[$i]['id']);
            }
            
            $lists[$i]['divide_ratio'] = $divide_ratio;
            $lists[$i]['divide_ratio_channel'] = $divide_ratio_channel;
            $lists[$i]['total_income'] = $lists[$i]['total_income'] * $divide_ratio;
        }
        
        $this->assign('filter', $_GET);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
    
        $this->display();
    }
    
    function get_divide_ratio($channel_id) {
        $channel = M('channels')->where("id=$channel_id")->find();
    
        $parents = array();
        get_all_parents($parents, $channel_id);
    
        $ratio = 1.0;
    
        for ($i=0; $i<count($parents); $i++)
            $ratio *= $parents[$i]['divide_ratio'];
    
        $ratio *= $channel['divide_ratio'];
    
        return $ratio;
    }
    
    function get_divide_ratio_to_channel($cur_channel_id, $channel_id) {
        $channel = M('channels')->where("id=$channel_id")->find();
        
        $parents = array();
        get_all_parents_channel($parents, $cur_channel_id, $channel_id);
        

        $ratio = 1.0;
        
        for ($i=0; $i<count($parents); $i++)
            $ratio *= $parents[$i]['divide_ratio'];
        
        
        //if ($cur_channel_id == 0)
            $ratio *= $channel['divide_ratio'];
    
        return $ratio;
    }
    
    function password() {
        $session_admin_id=session('ADMIN_ID');
        
        $this->assign('id', $session_admin_id);
        
        $this->display();
    }
    
    function password_post() {
        $session_admin_id=session('ADMIN_ID');
        
        $password = $_REQUEST['user_pass'];
        
        $user_data=array(
            'user_pass' => sp_password($password),
        );
        
        $users_model = M('users');
        $id = $users_model->where("id=$session_admin_id")->save($user_data);
        
        $this->success('修改密码成功');
    }
    
    function channel_index() {
        $model=M("channels a");
        
        $session_admin_id=session('ADMIN_ID');
        $channel = $model->where("admin_user_id=$session_admin_id")->find();

        if ($channel == null)
        {
            $this->index();
            return;
        }

        $child_channels = get_all_channel_childs($channel['id']);

        $ids = get_id_from_all_channel_childs($child_channels);
        
        if ($ids == '')
            $ids = $channel['id'];
        else
            $ids = $channel['id'] . ',' . $ids;
        
        $where = "id in ($ids)";

        if (isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != null)
            $where .= ' and (a.name like "%' . $_REQUEST['keyword'] . '%" or a.memo like "%' . $_REQUEST['keyword'] . '%" or a.contact like "%' . $_REQUEST['keyword'] . '%" or a.telephone like "%'. $_REQUEST['keyword'].'%")';
        
        $_GET['keyword'] = $_REQUEST['keyword'];
        
        $count=$model->where("id in (" . $ids . ')')->count();
        $page = $this->page($count, 20);
        $lists = $model
        ->where($where)
        ->order("id DESC")
        ->field('a.*,(select COUNT(*) from sp_channel_user_relation b where b.channel_id=a.id and b.is_visible=1) as user_count')
        ->limit($page->firstRow . ',' . $page->listRows)
        ->select();

        $order_db = M('orders a');
    
        for ($i=0; $i<count($lists); $i++)
        { 
            $channel_id = $lists[$i]['id'];
            
            if ($channel_id == $channel['id'])
            {
                $lists[$i]['parent_id'] = '本渠道';
            }
            else if ($lists[$i]['parent_id'] == $channel['id'])
            {
                $lists[$i]['parent_id'] = '子渠道';
            }
            
            // 获取下面所有子渠道的收入
            $all_childs = get_all_channel_childs($channel_id);
            $all_childs_ids = get_id_from_all_channel_childs($all_childs);
            if ($all_childs_ids == '')
                $all_childs_ids = $channel_id;
            else
                $all_childs_ids .= ',' . $channel_id;
            
            $lists[$i]['total_income'] = $order_db->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id')->where("b.channel_id in ($all_childs_ids) and is_payed=1 and is_deduct=0")->sum('a.price');
            $lists[$i]['total_income_deduct'] = $order_db->join('__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id')->where("b.channel_id in ($all_childs_ids) and is_payed=1 and is_deduct=1")->sum('a.price');
        }

        // 计算分成
        for ($i=0; $i<count($lists); $i++)
        {
            if ($lists[$i]['id'] == null)
            {
                $divide_ratio = 1;
            
                $divide_ratio_channel = 1;
            }
            else
            {
                    $divide_ratio = $this->get_divide_ratio($lists[$i]['id']);

                    $divide_ratio_channel = $this->get_divide_ratio_to_channel($channel['id'], $lists[$i]['id']);
            }
        
            $lists[$i]['divide_ratio'] = $divide_ratio_channel;
            $lists[$i]['divide_ratio_channel'] = $divide_ratio_channel;
            $lists[$i]['total_income'] = $lists[$i]['total_income'] * $divide_ratio;
        }
    
        $this->assign('filter', $_GET);
        $this->assign('channel', $channel);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
    
        $this->display();
    }

    function add() {
        
        $session_admin_id=session('ADMIN_ID');
        
        $model=M("channels a");
        
        $channel = $model->where("admin_user_id=$session_admin_id")->find();
        
        $this->assign('channel', $channel);
        
        $this->display();
    }
    
    function add_post() {
        $db = M('channels');
        $users_model=M("Users");
        
        $name = $_REQUEST['name'];
        $account = $_REQUEST['account'];
        $password = $_REQUEST['password'];
        $parent_id = $_REQUEST['parent_id'];
        $status = $_REQUEST['status'];
        $contact = $_REQUEST['contact'];
        $memo = $_REQUEST['memo'];
        $telephone = $_REQUEST['telephone'];
        $divide_ratio = floatval($_REQUEST['divide_ratio']);
        $bank = $_REQUEST['bank'];
        $cardno = $_REQUEST['cardno'];
        $card_owner = $_REQUEST['card_owner'];
        
        if ($users_model->where("user_login='$account'")->find() != null)
        {
            $this->error('渠道帐号相同');
            return;
        }
        

        if ($divide_ratio < 0 || $divide_ratio > 1.0)
        {
            return $this->error('分成比例不能大于1');
        }
        
        if (isset($_REQUEST['amount_deduct']))
        {
            if (intval($_REQUEST['amount_deduct']) < 2 && intval($_REQUEST['amount_deduct']) != 0)
            {
                return $this->error('扣量不正确');
            }
        }
        
        $user_data=array(
            'user_login' => $account,
            'user_nicename' => $name,
            'user_pass' => sp_password($password),
            'user_email' => $account . '@channel.com',
            'user_nicename' => $name,
            'last_login_ip' => get_client_ip(),
            'create_time' => date("Y-m-d H:i:s"),
            'last_login_time' => date("Y-m-d H:i:s"),
            'user_status' => 1,
            'telephone' => $telephone,
            'divide_ratio' => $divide_ratio,
            "user_type"=>1,
        );
        
        $id = $users_model->add($user_data);
        
        $channel_role_id = 2;
        $channel_user_data = array(
            'role_id' => $channel_role_id,
            'user_id' => $id
        );
        M('role_user')->add($channel_user_data);

        $data = array(
            'name' => $name,
            'parent_id' => $parent_id,
            'status' => $status,
            'memo' => $memo,
            'contact' => $contact,
            'telephone' => $telephone,
            'create_time' => date('Y-m-d H:i:s'),
            'divide_ratio' => $divide_ratio,
            'bank' => $bank,
            'cardno' => $cardno,
            'card_owner' => $card_owner,
            'admin_user_id' => $id
        );
        
        if (isset($_REQUEST['amount_deduct']))
            $data['amount_deduct'] = intval($_REQUEST['amount_deduct']);

        $db->add($data);
        
        $this->success('添加成功');
    }
    
    function edit($id) {
        $db = M('channels');
        
        $item = $db->where('id=' . $id)->find();
 
        $session_admin_id=session('ADMIN_ID');
        
        $channel = $db->where("admin_user_id=$session_admin_id")->find();
        
        if ($channel != null && $channel['id'] == $id)
        {
            $this->error('请联系上级代理修改');
            return;
        }
        
        $channel_user = M('users')->where('id=' . $item['admin_user_id'])->find();
 
        $this->assign('channel', $channel);
        $this->assign('user_login', $channel_user['user_login']);

        $this->assign('vo', $item);
        
        $this->display();
    }
    
    function edit_post() {
        $db = M('channels');
    
        $id = $_REQUEST['tid'];
        $name = $_REQUEST['name'];
        $parent_id = $_REQUEST['parent_id'];
        $status = $_REQUEST['status'];
        $contact = $_REQUEST['contact'];
        $memo = $_REQUEST['memo'];
        $telephone = $_REQUEST['telephone'];
        $divide_ratio = floatval($_REQUEST['divide_ratio']);
        $password = $_REQUEST['password'];
        $bank = $_REQUEST['bank'];
        $cardno = $_REQUEST['cardno'];
        $card_owner = $_REQUEST['card_owner'];
        
        if ($divide_ratio < 0 || $divide_ratio > 1.0)
        {
            return $this->error('分成比例不能大于1');
        }

        $data = array(
            'name' => $name,
            'parent_id' => $parent_id,
            'status' => $status,
            'memo' => $memo,
            'contact' => $contact,
            'telephone' => $telephone,
            'divide_ratio' => $divide_ratio,
            'bank' => $bank,
            'cardno' => $cardno,
            'card_owner' => $card_owner,
            'create_time' => date('Y-m-d H:i:s')
        );
       
        if (isset($_REQUEST['amount_deduct']) && $_REQUEST['amount_deduct'] != null && $_REQUEST['amount_deduct'] != '')
        {
            if (intval($_REQUEST['amount_deduct']) < 2 && intval($_REQUEST['amount_deduct']) != 0)
            {
                return $this->error('扣量不正确');
            }
            
            $data['amount_deduct'] = intval($_REQUEST['amount_deduct']);
            
        } 
    
        $db->where('id=' . $id)->save($data);
        
        if (strlen($password) >= 6)
        {
            $channel = $db->where("id=$id")->find();
            $user_data=array(
                'user_pass' => sp_password($password),
            );
            
            M('users')->where('id=' . $channel['admin_user_id'])->save($user_data);
        }
    
        $this->success('修改成功');
    }
    
    function delete($id) {
        $db = M('channels');
        
        if ($db->where('id=' . $id)->count() ==  0)
            $this->error("删除失败！");
        else {
            $db->where('id=' . $id)->delete();
            $this->success("删除成功！");
        }        
    }   
}
