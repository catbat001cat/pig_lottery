<?php

/**
 * 渠道数据管理
*/
namespace Agent\Controller;

use Common\Controller\AdminbaseController;

class ChanneldataadminController extends AdminbaseController
{

    function users($channel_id)
    {
        $ids = $channel_id;
        
        $child_channels = get_all_channel_childs($channel_id);
        
        if (count($child_channels) > 0)
            $ids .= ',' . get_id_from_all_channel_childs($child_channels);
        
        $where .= 'a.channel_id in (' . $ids . ') and a.is_visible=1';
        
        $channel_user_model = M("channel_user_relation a");
        $count = $channel_user_model->where($where)->count();
        $page = $this->page($count, 20);
        $lists = $channel_user_model->join('__USERS__ b on b.id=a.user_id')
            ->where($where)
            ->field('b.*')
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display();
    }

    function users_deduct($channel_id)
    {
        $ids = $channel_id;
        
        $child_channels = get_all_channel_childs($channel_id);
        
        if (count($child_channels) > 0)
            $ids .= ',' . get_id_from_all_channel_childs($child_channels);
        
        $where .= 'a.channel_id in (' . $ids . ') and a.is_visible=0';
        
        $channel_user_model = M("channel_user_relation a");
        $count = $channel_user_model->where($where)->count();
        $page = $this->page($count, 20);
        $lists = $channel_user_model->join('__USERS__ b on b.id=a.user_id')
            ->where($where)
            ->field('b.*')
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display();
    }

    function get_divide_ratio($channel_id)
    {
        $channel = M('channels')->where("id=$channel_id")->find();
        
        $parents = array();
        get_all_parents($parents, $channel_id);
        
        $ratio = 1.0;
        
        for ($i = 0; $i < count($parents); $i ++)
            $ratio *= $parents[$i]['divide_ratio'];
        
        $ratio *= $channel['divide_ratio'];
        
        return $ratio;
    }

    function get_divide_ratio_to_channel($cur_channel_id, $channel_id)
    {
        $channel = M('channels')->where("id=$channel_id")->find();
        
        $parents = array();
        get_all_parents_channel($parents, $cur_channel_id, $channel_id);
        
        $ratio = 1.0;
        
        for ($i = 0; $i < count($parents); $i ++)
            $ratio *= $parents[$i]['divide_ratio'];
            
            // if ($cur_channel_id == 0)
        $ratio *= $channel['divide_ratio'];
        
        return $ratio;
    }

    function incomes($channel_id)
    {
        $admin_channel_id = 0;
        $session_admin_id = session('ADMIN_ID');
        
        $channel_db = M('channels');
        $admin_channel = $channel_db->where("admin_user_id=$session_admin_id")->find();
        if ($admin_channel != null)
            $admin_channel_id = $admin_channel['id'];
        
        $ids = $channel_id;
        $child_channels = get_all_channel_childs($channel_id);
        
        if (count($child_channels) > 0)
            $ids .= ',' . get_id_from_all_channel_childs($child_channels);
        
        $where .= ' and b.channel_id in (' . $ids . ')';
        
        $is_deduct_cond = ' and 1';
        
        if ($admin_channel != null)
            $is_deduct_cond = ' and a.is_deduct=0';
        
        $order_model = M('orders a');
        $count = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->where("b.channel_id=$channel_id and a.is_payed=1" . $is_deduct_cond)
            ->count();
        $page = $this->page($count, 20);
        $lists = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", "left")
            ->join("__CHANNELS__ d on d.id=b.channel_id", "left")
            ->join("__USERS__ c on c.id=a.user_id", "left")
            ->where("b.channel_id in ($ids) and a.is_payed=1" . $is_deduct_cond)
            ->field('a.*,c.user_nicename,d.name as channel_name,b.channel_id')
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        
        for ($i = 0; $i < count($lists); $i ++) {
            
            // 计算分成
            $divide_ratio = $this->get_divide_ratio($lists[$i]['channel_id']);
            
            $divide_ratio_channel = $this->get_divide_ratio_to_channel($admin_channel_id, $lists[$i]['channel_id']);
            
            $lists[$i]['divide_ratio'] = $divide_ratio;
            if ($_REQUEST['orign_price'] != '1')
                $lists[$i]['price'] = $lists[$i]['price'] * $divide_ratio;
            
            if ($admin_channel == null) {
                if ($lists[$i]['is_deduct'] == 1)
                    $lists[$i]['price'] .= '(扣量)';
            }
            
            $lists[$i]['divide_ratio_channel'] = $divide_ratio_channel;
        }
        
        $this->assign('channel', $admin_channel);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display();
    }

    function incomes_deduct($channel_id)
    {
        $admin_channel_id = 0;
        $session_admin_id = session('ADMIN_ID');
        
        $channel_db = M('channels');
        $admin_channel = $channel_db->where("admin_user_id=$session_admin_id")->find();
        if ($admin_channel != null)
            $admin_channel_id = $admin_channel['id'];
        
        $ids = $channel_id;
        $child_channels = get_all_channel_childs($channel_id);
        
        if (count($child_channels) > 0)
            $ids .= ',' . get_id_from_all_channel_childs($child_channels);
        
        $where .= ' and b.channel_id in (' . $ids . ')';
        
        $is_deduct_cond = ' and a.is_deduct=1';
        
        $order_model = M('orders a');
        $count = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->where("b.channel_id=$channel_id and a.is_payed=1" . $is_deduct_cond)
            ->count();
        $page = $this->page($count, 20);
        $lists = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", "left")
            ->join("__CHANNELS__ d on d.id=b.channel_id", "left")
            ->join("__USERS__ c on c.id=a.user_id", "left")
            ->where("b.channel_id in ($ids) and a.is_payed=1" . $is_deduct_cond)
            ->field('a.*,c.user_nicename,d.name as channel_name,b.channel_id')
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        
        for ($i = 0; $i < count($lists); $i ++) {
            // 计算分成
            $divide_ratio = $this->get_divide_ratio($lists[$i]['channel_id']);
            
            $divide_ratio_channel = $this->get_divide_ratio_to_channel($admin_channel_id, $lists[$i]['channel_id']);
            
            $lists[$i]['divide_ratio'] = $divide_ratio;
            if ($_REQUEST['orign_price'] != '1')
                $lists[$i]['price'] = $lists[$i]['price'] * $divide_ratio;
            $lists[$i]['divide_ratio_channel'] = $divide_ratio_channel;
        }
        
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display('incomes');
    }

    function month_incomes()
    {
        $session_admin_id = session('ADMIN_ID');
        
        $model = M('channels');
        $channel = $model->where("admin_user_id=$session_admin_id")->find();
        
        $admin_channel_id = 0;
        if ($channel != null)
            $admin_channel_id = $channel['id'];
        
        $where = 'a.is_payed=1';
        
        $ids = '';
        
        if ($channel != null) {
            $ids = $channel['id'];
            
            $child_channels = get_all_channel_childs($channel['id']);
            
            if (count($child_channels) > 0)
                $ids .= ',' . get_id_from_all_channel_childs($child_channels);
            
            $where .= ' and b.channel_id in (' . $ids . ')';
        }
        
        $order_model = M('orders a');
        $count = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->where($where)
            ->group("DATE_FORMAT( a.create_time,'%Y-%m')")
            ->count();
        $page = $this->page($count, 20);
        
        $lists = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->join("__CHANNELS__ c on c.id=b.channel_id", "left")
            ->where($where)
            ->field("c.id as channel_id, c.name, c.create_time, DATE_FORMAT( a.create_time,'%Y-%m') as date_month, sum(a.price) as total_incomes")
            ->group("DATE_FORMAT( a.create_time,'%Y-%m')")
            ->order("date_month DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        
        // 计算分成
        for ($i = 0; $i < count($lists); $i ++) {
            if ($lists[$i]['channel_id'] == null) {
                $divide_ratio = 1;
                
                $divide_ratio_channel = 1;
            } else {
                $divide_ratio = $this->get_divide_ratio($lists[$i]['channel_id']);
                
                $divide_ratio_channel = $this->get_divide_ratio_to_channel($admin_channel_id, $lists[$i]['channel_id']);
            }
            
            $lists[$i]['divide_ratio'] = $divide_ratio;
            $lists[$i]['divide_ratio_channel'] = $divide_ratio_channel;
            $lists[$i]['total_incomes'] = $lists[$i]['total_incomes'] * $divide_ratio;
        }
        
        $this->assign('channel', $channel);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display();
    }

    function day_incomes()
    {
        $session_admin_id = session('ADMIN_ID');
        
        $model = M('channels');
        $channel = $model->where("admin_user_id=$session_admin_id")->find();
        
        $admin_channel_id = 0;
        if ($channel != null)
            $admin_channel_id = $channel['id'];
        
        $where = 'a.is_payed=1';
        
        $ids = '';
        
        if ($channel != null) {
            $ids = $channel['id'];
            
            $child_channels = get_all_channel_childs($channel['id']);
            
            if (count($child_channels) > 0)
                $ids .= ',' . get_id_from_all_channel_childs($child_channels);
            
            $where .= ' and b.channel_id in (' . $ids . ')';
        }
        
        $order_model = M('orders a');
        $count = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->where($where)
            ->group("DATE_FORMAT( a.create_time,'%Y-%m-%d')")
            ->count();
        $page = $this->page($count, 20);
        
        $lists = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->join("__CHANNELS__ c on c.id=b.channel_id", "left")
            ->where($where)
            ->field("c.id as channel_id, c.name, c.create_time, DATE_FORMAT( a.create_time,'%Y-%m-%d') as date_month, sum(a.price*IF(a.is_deduct=0,1,0)) as total_incomes,sum(a.price*IF(a.is_deduct=1,1,0)) as total_incomes_deduct")
            ->group("DATE_FORMAT( a.create_time,'%Y-%m-%d')")
            ->order("date_month DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        
        // 计算分成
        for ($i = 0; $i < count($lists); $i ++) {
            if ($lists[$i]['channel_id'] == null) {
                $divide_ratio = 1;
                
                $divide_ratio_channel = 1;
            } else {
                $divide_ratio = $this->get_divide_ratio($lists[$i]['channel_id']);
                
                $divide_ratio_channel = $this->get_divide_ratio_to_channel($admin_channel_id, $lists[$i]['channel_id']);
            }
            
            $lists[$i]['divide_ratio'] = $divide_ratio;
            $lists[$i]['divide_ratio_channel'] = $divide_ratio_channel;
            $lists[$i]['total_incomes'] = $lists[$i]['total_incomes'];
        }
        
        $this->assign('channel', $channel);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display('month_incomes');
    }

    function channel_day_incomes($channel_id)
    {
        $session_admin_id = session('ADMIN_ID');
        
        $model = M('channels');
        $admin_channel = $model->where("admin_user_id=$session_admin_id")->find();
        
        $admin_channel_id = 0;
        if ($admin_channel != null)
            $admin_channel_id = $admin_channel['id'];
        
        $channel = $model->where("id=$channel_id")->find();
        
        $where = 'a.is_payed=1';
        
        if ($admin_channel_id != null)
            $where .= ' and a.is_deduct=0';
        
        $ids = '';
        
        if ($channel != null) {
            $ids = $channel['id'];
            
            $child_channels = get_all_channel_childs($channel['id']);
            
            if (count($child_channels) > 0)
                $ids .= ',' . get_id_from_all_channel_childs($child_channels);
            
            $where .= ' and b.channel_id in (' . $ids . ')';
        }
        
        $order_model = M('orders a');
        $count = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->where($where)
            ->group("DATE_FORMAT( a.create_time,'%Y-%m-%d')")
            ->count();
        $page = $this->page($count, 20);
        
        $lists = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->join("__CHANNELS__ c on c.id=b.channel_id", "left")
            ->where($where)
            ->field("c.id as channel_id, c.name, c.create_time, DATE_FORMAT( a.create_time,'%Y-%m-%d') as date_month, sum(a.price*IF(a.is_deduct=0,1,0)) as total_incomes, sum(a.price*IF(a.is_deduct=1,1,0)) as total_incomes_deduct")
            ->group("DATE_FORMAT( a.create_time,'%Y-%m-%d')")
            ->order("date_month DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        
        $divide_ratio = $this->get_divide_ratio($channel_id);
        
        $divide_ratio_channel = $this->get_divide_ratio_to_channel($admin_channel_id, $channel_id);
        
        // 计算分成
        for ($i = 0; $i < count($lists); $i ++) {
            $lists[$i]['name'] = $channel['name'] . '&nbsp;(' . $lists[$i]['name'] . ')';
            $lists[$i]['divide_ratio'] = $divide_ratio;
            $lists[$i]['divide_ratio_channel'] = $divide_ratio_channel;
            $lists[$i]['total_incomes'] = $lists[$i]['total_incomes'] * $divide_ratio;
            $lists[$i]['total_incomes_deduct'] = $lists[$i]['total_incomes_deduct'] * $divide_ratio;
        }
        
        $this->assign('channel', $admin_channel);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display();
    }

    public function channel_day_incomes_more($date_month)
    {
        $session_admin_id = session('ADMIN_ID');
        
        $model = M('channels');
        $admin_channel = $model->where("admin_user_id=$session_admin_id")->find();
        
        $admin_channel_id = 0;
        if ($admin_channel != null)
            $admin_channel_id = $admin_channel['id'];
        
        $channel_id = 0;
        if (isset($_REQUEST['channel_id']))
            $channel_id = intval($_REQUEST['channel_id']);
        
        $channel = $model->where("id=$channel_id")->find();
        
        $where = 'a.is_payed=1';
        
        if ($admin_channel_id != null)
            $where .= ' and a.is_deduct=0';
        
        $ids = '';
        
        $date_format = '%Y-%m-%d';
        $date_arrs = explode('-', $date_month);
        if (count($date_arrs) == 2)
            $date_format = '%Y-%m';
        
        if ($channel != null) {
            $ids = $channel['id'];
            
            $child_channels = get_all_channel_childs($channel['id']);
            
            if (count($child_channels) > 0)
                $ids .= ',' . get_id_from_all_channel_childs($child_channels);
            
            $where .= ' and b.channel_id in (' . $ids . ')';
        }
        
        $where .= " and DATE_FORMAT( a.create_time,'" . $date_format . "')='" . $date_month . "'";
        
        $order_model = M('orders a');
        $count = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        
        $lists = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->join("__CHANNELS__ d on d.id=b.channel_id", "left")
            ->join("__USERS__ c on c.id=a.user_id", "left")
            ->where($where)
            ->field('a.*,c.user_nicename,d.name as channel_name,b.channel_id')
            ->order("a.create_time DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        
        $divide_ratio = $this->get_divide_ratio($channel_id);
        
        $divide_ratio_channel = $this->get_divide_ratio_to_channel($admin_channel_id, $channel_id);
        
        // 计算分成
        for ($i = 0; $i < count($lists); $i ++) {
            $lists[$i]['name'] = $channel['name'] . '&nbsp;(' . $lists[$i]['name'] . ')';
            $lists[$i]['divide_ratio'] = $divide_ratio;
            $lists[$i]['divide_ratio_channel'] = $divide_ratio_channel;
            $lists[$i]['price'] = $lists[$i]['price']; // * $divide_ratio;
            
            if ($admin_channel_id == null) {
                if ($lists[$i]['is_deduct'] == 1)
                    $lists[$i]['price'] .= '(扣量)';
            }
        }
        
        $this->assign('channel', $admin_channel);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display('incomes');
    }

    public function day_incomes_more()
    {
        $session_admin_id = session('ADMIN_ID');
        
        $model = M('channels');
        $admin_channel = $model->where("admin_user_id=$session_admin_id")->find();
        
        $admin_channel_id = 0;
        if ($admin_channel != null)
            $admin_channel_id = $admin_channel['id'];
        
        $where = 'a.is_payed=1';
        
        if ($admin_channel_id != null)
            $where .= ' and a.is_deduct=0';
        
        $ids = '';
        
        $_GET['channel_name'] = $_REQUEST['channel_name'];
        $_GET['order_id'] = $_REQUEST['order_id'];
        $_GET['start_ymd'] = $_REQUEST['start_ymd'];
        $_GET['end_ymd'] = $_REQUEST['end_ymd'];
        
        if (isset($_REQUEST['channel_name']) && $_REQUEST['channel_name'] != null) {
            $channels = $model->where("name like '%" . $_REQUEST['channel_name'] . "%'")->select();
            
            for ($i = 0; $i < count($channels); $i ++) {
                $child_channels = get_all_channel_childs($channels[$i]['id']);
                
                $ids = $channels[$i]['id'];
                
                if (count($child_channels) > 0)
                    $ids .= ',' . get_id_from_all_channel_childs($child_channels);
            }
            
            if (count($channels) == 0)
                $where .= ' and false';
        }
        
        if ($admin_channel_id > 0) {
            $child_channels = get_all_channel_childs($admin_channel_id);
            
            if ($ids != '')
                $ids .= ',' . $admin_channel_id;
            else
                $ids .= $admin_channel_id;
            
            if (count($child_channels) > 0)
                $ids .= ',' . get_id_from_all_channel_childs($child_channels);
        }
        
        if ($ids != '')
            $where .= ' and d.id in (' . $ids . ')';
        
        if (isset($_REQUEST['date_month']) && $_REQUEST['date_month'] != null) {
            $date_month = $_REQUEST['date_month'];
            $date_format = '%Y-%m-%d';
            $date_arrs = explode('-', $date_month);
            if (count($date_arrs) == 2) {
                $date_format = '%Y-%m';
            }
            
            $where .= " and DATE_FORMAT( a.create_time,'" . $date_format . "')='" . $date_month . "'";
        }
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
        
        if (isset($_REQUEST['order_id']) && $_REQUEST['order_id'] != null) {
            $where .= " and a.order_id like '%" . $_REQUEST['order_id'] . "%'";
        }
        
        if (isset($_REQUEST['user_id'])) {
            $where .= " and a.user_id=" . $_REQUEST['user_id'];
        }
        
        $order_model = M('orders a');
        $count = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->join("__CHANNELS__ d on d.id=b.channel_id", "left")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        
        $lists = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
            ->join("__CHANNELS__ d on d.id=b.channel_id", "left")
            ->join("__USERS__ c on c.id=a.user_id", "left")
            ->where($where)
            ->field('a.*,c.user_nicename,d.name as channel_name,b.channel_id')
            ->order("a.create_time DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        
        // 计算分成
        for ($i = 0; $i < count($lists); $i ++) {
            
            $channel_id = $lists[$i]['channel_id'];
            
            $channel_name = $lists[$i]['channel_name'];
            
            $parent_channel_arr = array();
            
            if ($channel_id == 0) {
                $divide_ratio = 1;
                $divide_ratio_channel = 1;
            } else {
                $divide_ratio = $this->get_divide_ratio($channel_id);
                
                $divide_ratio_channel = $this->get_divide_ratio_to_channel($admin_channel_id, $channel_id);
                
                get_all_parents_channel($parent_channel_arr, $admin_channel_id, $channel_id);
            }
            
            if ($channel_id == 0)
                $lists[$i]['channel_name'] = '平台';
            else {
                if (count($parent_channel_arr) == 0)
                    $lists[$i]['channel_name'] = $channel_name . '[' . $channel_id . ']';
                else
                    $lists[$i]['channel_name'] = $parent_channel_arr[0]['name'] . '[' . $parent_channel_arr[0]['id'] . ']' . '&nbsp;(' . $lists[$i]['channel_name'] . '[' . $channel_id . ']' . ')';
            }
            
            $lists[$i]['divide_ratio'] = $divide_ratio;
            $lists[$i]['divide_ratio_channel'] = $divide_ratio_channel;
            $lists[$i]['price'] = $lists[$i]['price'];
            
            if ($admin_channel_id == null) {
                if ($lists[$i]['is_deduct'] == 1)
                    $lists[$i]['price'] .= '(扣量)';
            }
        }
        
        $this->assign('channel', $admin_channel);
        $this->assign('filter', $_GET);
        $this->assign('action', U('Channeldataadmin/day_incomes_more'));
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display('incomes');
    }

    function set_deduct($id, $is_deduct)
    {
        $order_db = M('orders');
        
        $order_db->where("id=$id")->setField('is_deduct', $is_deduct);
        
        return $this->success('设置成功');
    }

    function all_channel_day_incomes()
    {
        $ymd = date('Y-m-d');
        $type = 1;
        
        if (isset($_REQUEST['ymd']))
            $ymd = $_REQUEST['ymd'];
        if (isset($_REQUEST['type']))
            $type = intval($_REQUEST['type']);
        
        $_GET['ymd'] = $ymd;
        $_GET['type'] = $type;
        
        $channel_db = M('channels');
        
        $session_admin_id = session('ADMIN_ID');
        
        $model = M('channels');
        $admin_channel = $model->where("admin_user_id=$session_admin_id")->find();
        
        $admin_channel_id = 0;
        if ($admin_channel != null)
            $admin_channel_id = $admin_channel['id'];
        
        $channel_where = '1';
        if ($admin_channel_id == 0)
        {
            if ($type == 1)
                $channel_where .= ' and parent_id=' . $admin_channel_id;
            
            $channels = $channel_db->where($channel_where)->select();
        }
        else 
        {
            if ($type == 1)
            {
                $channel_where .= ' and parent_id=' . $admin_channel_id;
                
                $channels = $channel_db->where($channel_where)->select();
                
                array_push($channels, $admin_channel);
            }
            else
            {
                $child_channels = get_all_channel_childs($admin_channel_id);
                
                $ids = $admin_channel_id;
                
                if (count($child_channels) > 0)
                    $ids .= ',' . get_id_from_all_channel_childs($child_channels);
                
                $channels = $channel_db->where('id in(' . $ids . ')')->select();
            }
        }
        
        $order_model = M('orders a');

        for ($i = 0; $i < count($channels); $i ++) {
            $where = 'a.is_payed=1 and DATE_FORMAT( a.create_time,"%Y-%m-%d")="' . $ymd . '"';
            
            $ids = '';
            
            $channel = $channels[$i];
            
            $ids = $channel['id'];
            
            $child_channels = get_all_channel_childs($channel['id']);
            
            if (count($child_channels) > 0)
                $ids .= ',' . get_id_from_all_channel_childs($child_channels);
            
            $where .= ' and b.channel_id in (' . $ids . ')';
            
            $lists = $order_model->join("__CHANNEL_USER_RELATION__ b on b.user_id=a.user_id", 'left')
                ->join("__CHANNELS__ c on c.id=b.channel_id", "left")
                ->where($where)
                ->field("c.id as channel_id, c.name, c.create_time, DATE_FORMAT( a.create_time,'%Y-%m-%d') as date_month, sum(a.price*IF(a.is_deduct=0,1,0)) as total_incomes,sum(a.price*IF(a.is_deduct=1,1,0)) as total_incomes_deduct")
                ->group("DATE_FORMAT( a.create_time,'%Y-%m-%d')")
                ->order("date_month DESC")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->find();
            
            // 计算分成
            $divide_ratio = $this->get_divide_ratio($channel['id']);
            
            $divide_ratio_channel = $this->get_divide_ratio_to_channel($admin_channel_id, $channel['id']);
            
            $channels[$i]['divide_ratio'] = $divide_ratio;
            $channels[$i]['divide_ratio_channel'] = $divide_ratio_channel;
            
            if ($lists != null)
            {
                $channels[$i]['total_incomes'] = $lists['total_incomes'];
                $channels[$i]['total_incomes_divide'] = $lists['total_incomes'] * $divide_ratio;
                $channels[$i]['total_incomes_deduct'] = $lists['total_incomes_deduct'];
            }
            
            if ($channels[$i]['id'] == $admin_channel_id)
                $channels[$i]['id'] = '本渠道';
        }

        $this->assign('filter', $_GET);
        $this->assign('lists', $channels);
        
        $this->display();
    }
    
    public function commisions()
    {
        $where .= 'a.type=4';
        
        if (isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '')
            $where .= ' and a.user_id=' . $_REQUEST['user_id'];
        
            if (isset($_REQUEST['date_month']) && $_REQUEST['date_month'] != null) {
            $date_month = $_REQUEST['date_month'];
            $date_format = '%Y-%m-%d';
            $date_arrs = explode('-', $date_month);
            if (count($date_arrs) == 2) {
                $date_format = '%Y-%m';
            }
            
            $where .= " and DATE_FORMAT( a.create_time,'" . $date_format . "')='" . $date_month . "'";
        }
        
        if (isset($_REQUEST['no']) && $_REQUEST['no'] != '')
            $where .= ' and c.no like "%' . $_REQUEST['no'] . '%"';
        
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
        
        $wallet_change_log_model = M("wallet_change_log a");
        $count = $wallet_change_log_model->join('__LOTTERY_ORDER__ c on c.id=a.object_id', 'left')->where($where)->count();
        $page = $this->page($count, 20);
        $lists = $wallet_change_log_model
        ->join('__USERS__ b on b.id=a.user_id', 'left')
        ->join('__LOTTERY_ORDER__ c on c.id=a.object_id', 'left')
        ->where($where)
        ->field('a.*,c.no,c.price as total_price,c.win as total_win')
        ->order("id DESC")
        ->limit($page->firstRow . ',' . $page->listRows)
        ->select();
        
        for ($i=0; $i<count($lists); $i++)
        {
            $lists[$i]['divide_fee'] = $lists[$i]['fee'];// * $lists[$i]['divide_ratio'];
        }
        
        $this->assign('filter', $_REQUEST);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display();
    }
}
