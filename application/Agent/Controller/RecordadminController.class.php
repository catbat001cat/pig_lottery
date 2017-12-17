<?php

/**
 * QQ投注管理
*/
namespace Agent\Controller;
use Common\Controller\AdminbaseController;
class RecordadminController extends AdminbaseController {
    function index() {

        $model = M('lottery_order a');
        
        $where = "1";
        
        if (isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != null)
        {
            $where .= ' and a.user_id="' . $_REQUEST['user_id'] . '"';
        }
        else 
        {
        	$where .= ' and b.level=0';
        }

        if (isset($_REQUEST['no']) && $_REQUEST['no'] != null)
        {
            $where .= ' and a.no="' . $_REQUEST['no'] . '"';
        }
        
        if (isset($_REQUEST['status']) && $_REQUEST['status'] != null)
        {
            $where .= ' and a.status=' . $_REQUEST['status'];
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
        ->join('__USERS__ b on b.id=a.user_id', 'left')
        ->where($where)
        ->count();
        $page = $this->page($count, 20);
        $lists = $model
        ->join('__USERS__ b on b.id=a.user_id', 'left')
        //->join('__LOTTERY__ b on (b.id=a.lottery_id or b.no=a.no)', 'left')
        ->where($where)
        ->field('a.*')//,b.num3')
        ->order("id DESC")
        ->limit($page->firstRow . ',' . $page->listRows)
        ->select();
        
        $lottery_db = M('lottery');
        
        for ($i=0; $i<count($lists); $i++)
        {
        	if ($lists[$i]['lottery_id'] == null)
        	{
        		$item = $lottery_db->where("no='" . $lists[$i]['no'] . "'")->find();
        	}
        	else
        	{
        		$item = $lottery_db->where("id=" . $lists[$i]['lottery_id'])->find();
        	}
        	
        	$lists[$i]['num3'] = $item['num3'];
        }
        
        $this->assign('filter', $_REQUEST);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }
    
    
    public function ajax_set_number($id, $num)
    {
    	$model=M("lottery");
    	
    	$lottery = $model->where("id=$id")->find();
    	
    	if ($lottery['status'] == 2)
    		$this->error('已经开奖，不能设置');
    	else
    	{
    		$model->where("id=$id")->setField('last_num', $num);
    		
    		$this->success('设置中奖个位数成功');
    	}
    }
    
    // 计算昨天的排名
    public function last_day_rank() {
    	$user_db = M('users');
    	$sql = "update sp_users d inner join (select @rowno:=@rowno + 1 as rowno,id, IFNULL((SELECT SUM(win) from sp_lottery_order b where b.user_id=a.id and TO_DAYS(b.create_time)<=TO_DAYS(now())-1),0) as total_win  from sp_users a,(SELECT @rowno:=0) c2  order by total_win desc) e on e.id=d.id set d.last_day_rank=rowno,d.last_day_total_win=total_win;";
    	
    	$user_db->execute($sql);
    	
    	$this->success('统计昨天排名成功', 'javascript:window.close()');
    }
    
    // 每天排名一次
    public function per_day_rank() {
    	$user_db = M('users');
    	
    	/* 这里统计排名，很实用
    	 update sp_users d inner join 
(select @rowno:=@rowno + 1 as rowno,id, IFNULL((SELECT SUM(win) from sp_lottery_order b where b.user_id=a.id),0) as total_win  from sp_users a,(SELECT @rowno:=0) c2  order by total_win desc) e
 on e.id=d.id set d.cur_day_rank=rowno;
    	 */

    	$sql = "update sp_users d inner join (select @rowno:=@rowno + 1 as rowno,id, IFNULL((SELECT SUM(win) from sp_lottery_order b where b.user_id=a.id and TO_DAYS(b.create_time)<=TO_DAYS(now())),0) as total_win  from sp_users a,(SELECT @rowno:=0) c2  order by total_win desc) e on e.id=d.id set d.cur_day_rank=rowno,d.cur_day_total_win=total_win;";
    	
    	$user_db->execute($sql);

    	$this->success('统计今天排名成功', 'javascript:window.close()');
    }
    
    // 当月排名
    public function per_month_rank() {
    	$user_db = M('users');
    	
    	$sql = "update sp_users d inner join (select @rowno:=@rowno + 1 as rowno,id, IFNULL((SELECT SUM(win) from sp_lottery_order b where b.user_id=a.id and DATE_FORMAT(b.create_time,'%Y%m') = DATE_FORMAT(CURDATE(),'%Y%m')),0) as total_win  from sp_users a,(SELECT @rowno:=0) c2  order by total_win desc) e on e.id=d.id set d.cur_month_rank=rowno,d.cur_month_total_win=total_win;";
    	
    	$user_db->execute($sql);
    	
    	$this->success('统计本月排名成功', 'javascript:window.close()');
    }
    
    // 上月排名
    public function last_month_rank() {
    	$user_db = M('users');
    	
    	$sql = "update sp_users d inner join (select @rowno:=@rowno + 1 as rowno,id, IFNULL((SELECT SUM(win) from sp_lottery_order b where b.user_id=a.id and DATE_FORMAT(b.create_time,'%Y%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH),'%Y%m')),0) as total_win  from sp_users a,(SELECT @rowno:=0) c2  order by total_win desc) e on e.id=d.id set d.last_month_rank=rowno,d.last_month_total_win=total_win;";
    	
    	$user_db->execute($sql);
    	
    	$this->success('更新上月排名成功', 'javascript:window.close()');
    }
}
