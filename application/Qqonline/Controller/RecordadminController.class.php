<?php

/**
 * 渠道管理
*/
namespace Qqonline\Controller;

use Common\Controller\AdminbaseController;

class RecordadminController extends AdminbaseController
{

    function get_commision($no)
    {
        $order_db = M('lottery_order');
        
        $channel_db = M('channels');
        
        // 有效日期
        $n = C('COMMISION_VALID_TIME');
        
        $total_fee = 0;
        
        $orders = $order_db->alias('a')
            ->join('__CHANNELS__ b on b.admin_user_id=a.user_id', 'left')
            ->
        // 是来自与哪个渠道,检测是否超过时效
        where("a.no='$no'")
            ->field('a.*, b.admin_user_id as channel_user_id, b.divide_ratio as channel_divide_ratio,b.parent_channels')
            ->select();
        
        for ($i = 0; $i < count($orders); $i ++) {
            // 获取这笔订单的所有可以分的渠道佣金
            $order = $orders[$i];
            
            $fee = floatval($order['price']); // - floatval($order['win']);
            
            if ($fee <= 0)
                continue;
            
            if ($order['parent_channels'] != null) {
                // 所有父渠道
                $parent_channels = $channel_db->where('id in (' . $order['parent_channels'] . ')')->select();
                
                $level_ratio = C('COMMISION_DIVIDE_RATIO5');
                
                for ($j = count($parent_channels); $j --; $j >= 0) {
                    // 这里需要过滤已经不纳入佣金的渠道
                    if ($j == 0)
                        $level_ratio = C('COMMISION_DIVIDE_RATIO');
                    else 
                        if ($j == 1)
                            $level_ratio = C('COMMISION_DIVIDE_RATIO2');
                        else 
                            if ($j == 2)
                                $level_ratio = C('COMMISION_DIVIDE_RATIO3');
                            else 
                                if ($j == 3)
                                    $level_ratio = C('COMMISION_DIVIDE_RATIO4');
                                else
                                    $level_ratio = C('COMMISION_DIVIDE_RATIO5');
                    
                    $parent_channel = $parent_channels[$j];
                    
                    $ratio = $level_ratio;
                    
                    $total_fee += $ratio * $fee / 100.0;
                }
            }
        }
        
        return $total_fee;
    }

    function index()
    {
        $model = M("lottery a");
        
        $wallet_change_log_db = M('wallet_change_log a');
        
        $where = "1";
        
        if (isset($_REQUEST['no']) && $_REQUEST['no'] != null) {
            $where .= ' and a.no="' . $_REQUEST['no'] . '"';
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
        
        $count = $model->where($where)->count();
        $page = $this->page($count, 10);
        $lists = $model->where($where)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        
        $lottery_order_model = M('lottery_order');
        
        // 获得赔率
        $lottery_ratio_db = M('lottery_ratio');
        $lottery_ratio = $lottery_ratio_db->where()->find();
        
        for ($i = 0; $i < count($lists); $i ++) {
            $buy = array();
            
            $order = $lists[$i];
            
            // 计算赔率,已出结果
            $r = array();
            $r['0_-1'] = 0;
            $r['0_0'] = 0;
            $r['0_1'] = 0;
            $r['1_0'] = 0;
            $r['1_1'] = 0;
            $r['1_2'] = 0;
            $r['1_3'] = 0;
            $r['1_4'] = 0;
            $r['1_10'] = 0;
            $r['1_11'] = 0;
            $r['1_12'] = 0;
            for ($j = 0; $j <= 9; $j ++)
                $r['2_' . $j] = 0;
            if ($order['status'] == 2) {
                if ($order['type'] == - 1) // 小
                    $r['0_-1'] = $lottery_ratio['small_ratio'];
                else 
                    if ($order['type'] == 0) // 合
                        $r['0_0'] = $lottery_ratio['mid_ratio'];
                    else
                        $r['0_1'] = $lottery_ratio['big_ratio'];
                
                $last_char = $order['num3']{strlen($order['num3']) - 1};
                
                if ($last_char == '0' || $last_char == '1')
                    $r['1_0'] = $lottery_ratio['num2_ratio'];
                else 
                    if ($last_char == '2' || $last_char == '3')
                        $r['1_1'] = $lottery_ratio['num2_ratio'];
                    else 
                        if ($last_char == '4' || $last_char == '5')
                            $r['1_2'] = $lottery_ratio['num2_ratio'];
                        else 
                            if ($last_char == '6' || $last_char == '7')
                                $r['1_3'] = $lottery_ratio['num2_ratio'];
                            else 
                                if ($last_char == '8' || $last_char == '9')
                                    $r['1_4'] = $lottery_ratio['num2_ratio'];
                
                if ($last_char == '0' || $last_char == '1' || $last_char == '2')
                    $r['1_10'] = $lottery_ratio['num3_ratio'];
                else 
                    if ($last_char == '3' || $last_char == '4' || $last_char == '5')
                        $r['1_11'] = $lottery_ratio['num3_ratio'];
                    else 
                        if ($last_char == '6' || $last_char == '7' || $last_char == '8')
                            $r['1_12'] = $lottery_ratio['num3_ratio'];
                
                $r['2_' . $last_char] = $lottery_ratio['num_ratio'];
            } else {
                $r['0_-1'] = (1 - $lottery_ratio['small_ratio']);
                $r['0_0'] = (1 - $lottery_ratio['mid_ratio']);
                $r['0_1'] = (1 - $lottery_ratio['big_ratio']);
                $r['1_0'] = (1 - $lottery_ratio['num2_ratio']);
                $r['1_1'] = (1 - $lottery_ratio['num2_ratio']);
                $r['1_2'] = (1 - $lottery_ratio['num2_ratio']);
                $r['1_3'] = (1 - $lottery_ratio['num2_ratio']);
                $r['1_4'] = (1 - $lottery_ratio['num2_ratio']);
                $r['1_10'] = (1 - $lottery_ratio['num3_ratio']);
                $r['1_11'] = (1 - $lottery_ratio['num3_ratio']);
                $r['1_12'] = (1 - $lottery_ratio['num3_ratio']);
                
                for ($j = 0; $j <= 9; $j ++)
                    $r['2_' . $j] = (1 - $lottery_ratio['num_ratio']);
            }
            
            $result_from = 'total_result';
            if ($order['status'] != 2)
                $result_from = 'result';
            
            $buy['0_-1'] = $lottery_order_model->where("no='" . $lists[$i]['no'] . "' and buy_method=0 and `0_-1`=1")
                ->field('sum(price/buy_type_count) as total_price, count(id) as total_count, sum((price-win)/buy_type_count) as total_result')
                ->find();
            $buy['0_-1']['result'] = $buy['0_-1']['total_price'] * $r['0_-1'];
            $buy['0_-1']['total_result'] = round($buy['0_-1'][$result_from], 3);
            $buy['0_0'] = $lottery_order_model->where("no='" . $lists[$i]['no'] . "' and buy_method=0 and `0_0`=1")
                ->field('sum(price/buy_type_count) as total_price, count(id) as total_count, sum((price-win)/buy_type_count) as total_result')
                ->find();
            $buy['0_0']['result'] = $buy['0_0']['total_price'] * $r['0_0'];
            $buy['0_0']['total_result'] = round($buy['0_0'][$result_from], 3);
            $buy['0_1'] = $lottery_order_model->where("no='" . $lists[$i]['no'] . "' and buy_method=0 and `0_1`=1")
                ->field('sum(price/buy_type_count) as total_price, count(id) as total_count, sum((price-win)/buy_type_count) as total_result')
                ->find();
            $buy['0_1']['result'] = $buy['0_1']['total_price'] * $r['0_1'];
            $buy['0_1']['total_result'] = round($buy['0_1'][$result_from], 3);
            
            for ($j = 0; $j <= 4; $j ++) {
                $buy['1_' . $j] = $lottery_order_model->where("no='" . $lists[$i]['no'] . "' and buy_method=1 and `1_" . $j . "`=1")
                    ->field('sum(price/buy_type_count) as total_price, count(id) as total_count, sum((price-win)/buy_type_count) as total_result')
                    ->find();
                $buy['1_' . $j]['result'] = $buy['1_' . $j]['total_price'] * $r['1_' . $j];
                $buy['1_' . $j]['total_result'] = round($buy['1_' . $j][$result_from], 3);
            }
            
            for ($j = 10; $j <= 12; $j ++) {
                $buy['1_' . $j] = $lottery_order_model->where("no='" . $lists[$i]['no'] . "' and buy_method=1 and `1_" . $j . "`=1")
                    ->field('sum(price/buy_type_count) as total_price, count(id) as total_count, sum((price-win)/buy_type_count) as total_result')
                    ->find();
                $buy['1_' . $j]['result'] = $buy['1_' . $j]['total_price'] * $r['1_' . $j];
                $buy['1_' . $j]['total_result'] = round($buy['1_' . $j][$result_from], 3);
            }
            
            for ($j = 0; $j <= 9; $j ++) {
                $buy['2_' . $j] = $lottery_order_model->where("no='" . $lists[$i]['no'] . "' and buy_method=2 and `2_" . $j . "`=1")
                    ->field('sum(price/buy_type_count) as total_price, count(id) as total_count, sum((price-win)/buy_type_count) as total_result')
                    ->find();
                $buy['2_' . $j]['result'] = $buy['2_' . $j]['total_price'] * $r['2_' . $j];
                $buy['2_' . $j]['total_result'] = round($buy['2_' . $j][$result_from], 3);
            }
            
            $lists[$i]['buy'] = $buy;
            
            $lists[$i]['total_count'] = $lottery_order_model->where("no='" . $lists[$i]['no'] . "'")->count();
            $lists[$i]['total_price'] = $lottery_order_model->where("no='" . $lists[$i]['no'] . "'")->sum('price');
            if ($order['status'] == 2) {
                $lists[$i]['total_result'] = '盈余:' . $lottery_order_model->where("no='" . $lists[$i]['no'] . "'")->sum('price-win');
                
                // 统计佣金
                
                $lists[$i]['total_commission'] = $wallet_change_log_db->join('__LOTTERY_ORDER__ b on b.id=a.object_id', 'left')
                    ->where("a.type=4 and b.no='" . $lists[$i]['no'] . "'")
                    ->sum('fee');
            } else {
                // 做预测
                // 计算所有数的数值
                $num = array();
                for ($j = 0; $j <= 9; $j ++)
                    $num[$j] = $buy['2_' . $j]['result'];
                    
                    // 计算数组
                $num['0'] += $buy['1_0']['result'] + $buy['1_10']['result'] + $buy['0_-1']['result'];
                $num['1'] += $buy['1_0']['result'] + $buy['1_10']['result'] + $buy['0_-1']['result'];
                $num['2'] += $buy['1_1']['result'] + $buy['1_10']['result'] + $buy['0_-1']['result'];
                $num['3'] += $buy['1_1']['result'] + $buy['1_11']['result'] + $buy['0_-1']['result'];
                $num['4'] += $buy['1_2']['result'] + $buy['1_11']['result'] + $buy['0_-1']['result'];
                $num['5'] += $buy['1_2']['result'] + $buy['1_11']['result'] + $buy['0_1']['result'];
                $num['6'] += $buy['1_3']['result'] + $buy['1_12']['result'] + $buy['0_1']['result'];
                $num['7'] += $buy['1_3']['result'] + $buy['1_12']['result'] + $buy['0_1']['result'];
                $num['8'] += $buy['1_4']['result'] + $buy['1_12']['result'] + $buy['0_1']['result'];
                $num['9'] += $buy['1_4']['result'] + $buy['0_1']['result'];
                
                arsort($num);
                
                $num_arr = array_keys($num);
                
                $lists[$i]['total_result'] = '0【选择个位:' . $num_arr[0] . '';
                
                for ($j = 0; $j < count($num_arr); $j ++) {
                    $html = '<a href="' . U('Recordadmin/ajax_set_number', array(
                        'id' => $lists[$i]['id'],
                        'num' => $num_arr[$j]
                    )) . '" data-msg="确定要开出个位数' . $num_arr[$j] . '?" class="js-ajax-delete">' . $num_arr[$j] . '</a>';
                    if ($j == 0)
                        $lists[$i]['number'] = '最优排序:【' . $html;
                    else
                        $lists[$i]['number'] .= ',' . $html;
                }
                
                $lists[$i]['number'] .= '】';
                
                $lists[$i]['total_commission'] = $this->get_commision($lists[$i]['no']);
            }
        }
        
        $this->assign('filter', $_REQUEST);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        
        $this->display();
    }
    
    public function ajax_set_number($id, $num)
    {
        $model = M("lottery");
        
        $lottery = $model->where("id=$id")->find();
        
        if ($lottery['status'] == 2)
            $this->ajaxReturn(array('ret' => -1, 'msg' => '已经开奖，不能设置'));
        else {
            $model->where("id=$id")->setField('last_num', $num);
            
            $this->ajaxReturn(array('ret' => 1, 'msg' => '设置中奖个位数成功'));
        }
    }
    
    // 计算昨天的排名
    public function last_day_rank()
    {
        $user_db = M('users');
        $sql = "update sp_users d inner join (select @rowno:=@rowno + 1 as rowno,id, IFNULL((SELECT SUM(win) from sp_lottery_order b where b.user_id=a.id and TO_DAYS(b.create_time)<=TO_DAYS(now())-1),0) as total_win  from sp_users a,(SELECT @rowno:=0) c2  order by total_win desc) e on e.id=d.id set d.last_day_rank=rowno,d.last_day_total_win=total_win;";
        
        $user_db->execute($sql);
        
        $this->success('统计昨天排名成功', 'javascript:window.close()');
    }
    
    // 每天排名一次
    public function per_day_rank()
    {
        $user_db = M('users');
        
        /*
         * 这里统计排名，很实用
         * update sp_users d inner join
         * (select @rowno:=@rowno + 1 as rowno,id, IFNULL((SELECT SUM(win) from sp_lottery_order b where b.user_id=a.id),0) as total_win from sp_users a,(SELECT @rowno:=0) c2 order by total_win desc) e
         * on e.id=d.id set d.cur_day_rank=rowno;
         */
        
        $sql = "update sp_users d inner join (select @rowno:=@rowno + 1 as rowno,id, IFNULL((SELECT SUM(win) from sp_lottery_order b where b.user_id=a.id and TO_DAYS(b.create_time)<=TO_DAYS(now())),0) as total_win  from sp_users a,(SELECT @rowno:=0) c2  order by total_win desc) e on e.id=d.id set d.cur_day_rank=rowno,d.cur_day_total_win=total_win;";
        
        $user_db->execute($sql);
        
        $this->success('统计今天排名成功', 'javascript:window.close()');
    }
    
    // 当月排名
    public function per_month_rank()
    {
        $user_db = M('users');
        
        $sql = "update sp_users d inner join (select @rowno:=@rowno + 1 as rowno,id, IFNULL((SELECT SUM(win) from sp_lottery_order b where b.user_id=a.id and DATE_FORMAT(b.create_time,'%Y%m') = DATE_FORMAT(CURDATE(),'%Y%m')),0) as total_win  from sp_users a,(SELECT @rowno:=0) c2  order by total_win desc) e on e.id=d.id set d.cur_month_rank=rowno,d.cur_month_total_win=total_win;";
        
        $user_db->execute($sql);
        
        $this->success('统计本月排名成功', 'javascript:window.close()');
    }
    
    // 上月排名
    public function last_month_rank()
    {
        $user_db = M('users');
        
        $sql = "update sp_users d inner join (select @rowno:=@rowno + 1 as rowno,id, IFNULL((SELECT SUM(win) from sp_lottery_order b where b.user_id=a.id and DATE_FORMAT(b.create_time,'%Y%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH),'%Y%m')),0) as total_win  from sp_users a,(SELECT @rowno:=0) c2  order by total_win desc) e on e.id=d.id set d.last_month_rank=rowno,d.last_month_total_win=total_win;";
        
        $user_db->execute($sql);
        
        $this->success('更新上月排名成功', 'javascript:window.close()');
    }
}
