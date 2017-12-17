<?php

/**
 * QQ投注管理
*/
namespace Agent\Controller;
use Common\Controller\AdminbaseController;
class RecordzpadminController extends AdminbaseController {
    function index() {

        $model = M('zp_lottery a');
        
        $where = "1";
        
        if (isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != null)
        {
            $where .= ' and a.user_id="' . $_REQUEST['user_id'] . '"';
        }

        if (isset($_REQUEST['status']) && $_REQUEST['status'] != null)
        {
        	if ($_REQUEST['status'] == '0')
            	$where .= ' and a.status=' . $_REQUEST['status'];
        	else if ($_REQUEST['status'] == '1')
        		$where .= ' and a.win>=a.buy_price';
        	else
        		$where .= ' and a.win<a.buy_price';
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
     
        $count=$model
        ->where($where)
        ->count();
        $page = $this->page($count, 20);
        $lists = $model
        //->join('__LOTTERY__ b on (b.id=a.lottery_id or b.no=a.no)', 'left')
        ->where($where)
        ->field('a.*')//,b.num3')
        ->order("id DESC")
        ->limit($page->firstRow . ',' . $page->listRows)
        ->select();
        
        $this->assign('filter', $_REQUEST);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }
}
