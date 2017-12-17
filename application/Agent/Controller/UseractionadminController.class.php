<?php

/**
 * 渠道管理
*/
namespace Agent\Controller;
use Common\Controller\AdminbaseController;
class UseractionadminController extends AdminbaseController {
    function index() {

        $model=M("user_action_log a");
        
        $_GET['ip'] = $_REQUEST['ip'];
        $_GET['channel_name'] = $_REQUEST['channel_name'];
        $_GET['action'] = $_REQUEST['action'];
        $_GET['start_ymd'] = $_REQUEST['start_ymd'];
        $_GET['end_ymd'] = $_REQUEST['end_ymd'];
        $_GET['user_id'] = $_REQUEST['user_id'];

        $where = '1';
        if (isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != null && $_REQUEST['user_id'] != '')
            $where .= " and a.user_id=" . $_REQUEST['user_id'];
        if (isset($_REQUEST['ip']) && $_REQUEST['ip'] != null)
            $where .= " and a.ip like '%" . $_REQUEST['ip'] . "%'";
        /*if (isset($_REQUEST['channel_name']) && $_REQUEST['channel_name'] != null)
            $where .= " and d.name like '%" . $_REQUEST['channel_name'] . "%'";
        */
        if (isset($_REQUEST['action']) && $_REQUEST['action'] != null && $_REQUEST['action'] != '')
            $where .= " and a.action like '%" . $_REQUEST['action'] . "%'";
        if (isset($_REQUEST['start_ymd']) && $_REQUEST['start_ymd'] != null && $_REQUEST['start_ymd'] != '')
            $where .= " and DATE_FORMAT( a.create_time,'%Y-%m-%d')>='" . $_REQUEST['start_ymd'] . "'";
        else
            {
            	$where .= " and a.create_time>='" . date("Y-m-d"). " 00:00:00'";
            	
            	$_REQUEST['start_ymd'] = date("Y-m-d");
            }
            
        if (isset($_REQUEST['end_ymd']) && $_REQUEST['end_ymd'] != null && $_REQUEST['end_ymd'] != '')
            $where .= " and DATE_FORMAT( a.create_time,'%Y-%m-%d')<='" . $_REQUEST['end_ymd'] . "'";
        
        $count = $model
        ->join('__USERS__ b on b.id=a.user_id', 'left')
        //->join('__CHANNEL_USER_RELATION__ c on c.user_id=b.id', 'left')
        //->join('__CHANNELS__ d on d.id=c.channel_id', 'left')
        ->where($where . ' and b.level=0')->count();
        $page = $this->page($count, 20);
        $lists = $model
        ->join('__USERS__ b on b.id=a.user_id', 'left')
        //->join('__CHANNEL_USER_RELATION__ c on c.user_id=b.id', 'left')
        //->join('__CHANNELS__ d on d.id=c.channel_id', 'left')
        ->where($where)
        //->field('a.*,b.user_nicename,d.name as channel_name, d.id as channel_id')
        ->field('a.*,b.user_nicename')
        ->order('a.id desc')
        ->limit($page->firstRow, $page->listRows)
        ->select();

        $this->assign('filter', $_GET);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
    
        $this->display();
    }
}
