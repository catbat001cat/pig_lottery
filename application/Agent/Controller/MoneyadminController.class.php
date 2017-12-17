<?php

/**
 * QQ财务管理
*/
namespace Agent\Controller;
use Common\Controller\AdminbaseController;
class  MoneyadminController extends AdminbaseController {
    function stat() {

        $where = '1';
        $where2 = '1';
        $where3 = '1';
        
        if (isset($_REQUEST['start_ymd']) && $_REQUEST['start_ymd'] != null) {
            $start_date = $_REQUEST['start_ymd'];
            $date_format = '%Y-%m-%d';
            $date_arrs = explode('-', $start_date);
            if (count($date_arrs) == 2) {
                $date_format = '%Y-%m';
            }
            
            $where .= " and DATE_FORMAT( a.create_time,'" . $date_format . "')>='" . $start_date . "'";
            $where2 .= " and DATE_FORMAT( a.completed_time,'" . $date_format . "')>='" . $start_date . "'";
        }
        else
        {
        	$where .= " and a.create_time>='" . date("Y-m-d"). " 00:00:00'";
        	$where2 .= " and a.completed_time>='" . date("Y-m-d"). " 00:00:00'";
        	
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
            $where2 .= " and DATE_FORMAT( a.completed_time,'" . $date_format . "')<='" . $end_date . "'";
        }
     
        $users_db = M('users a');
        $new_users_count = $users_db->where($where)->count();
        $recharge_order_db = M('recharge_order a');
        $total_recharge_price = $recharge_order_db->where($where . ' and a.status=1')->sum('price');    // 充值
        $total_incompleted_recharge_count = $recharge_order_db->where($where . ' and a.status=0')->count();    // 未支付次数
        $total_completed_recharge_count = $recharge_order_db->where($where . ' and a.status=1')->count();    // 支付次数

        $lottery_order_db = M('lottery_order a');
        $zp_lottery_order_db = M('zp_lottery a');
        $wallet_change_log_db = M('wallet_change_log a');
        
        // 夺宝
        $curday_stat = $lottery_order_db->where($where)
        ->field('sum(a.price) as total_price, sum(a.win) as total_win')
        ->find();
        $curday_total_price = $curday_stat['total_price'] == null ? 0 : $curday_stat['total_price'];
        $curday_commission = $wallet_change_log_db->where("a.type=4 and $where")->sum('fee');
        if ($curday_commission == null)
            $curday_commission = 0;
                
        // 转盘
        $curday_stat_zp = $zp_lottery_order_db->where($where)
        ->field('sum(a.buy_price) as total_price, sum(a.win) as total_win')
        ->find();
        $curday_total_price_zp = $curday_stat_zp['total_price'] == null ? 0 : $curday_stat_zp['total_price'];
        $curday_total_win_zp = $curday_stat_zp['total_win'] == null ? 0 : $curday_stat_zp['total_win'];        
        $curday_commission_zp = $wallet_change_log_db->where("a.type=8 and $where")->sum('fee');
        if ($curday_commission_zp == null)
            $curday_commission_zp = 0;
        
        $curday_total_win = $curday_stat['total_win'] == null ? 0 : $curday_stat['total_win'];
        $curday_total = ($curday_total_price + $curday_total_price_zp) - ($curday_total_win + $curday_total_win_zp) - ($curday_commission + $curday_commission_zp);
        $win_ratio = 0;
        if ($curday_total_price + $curday_total_price_zp > 0)
            $win_ratio = round($curday_total * 100.0 / ($curday_total_price + $curday_total_price_zp), 3);
        
        $lottery_db = M('lottery a');
       
        
        // 统计下注金额/中奖
        $lottery_stat = $lottery_order_db->where($where)->field('sum(price) as total_price, sum(win) as total_win')->find();
        $lottery_stat['total_no'] = $lottery_db->where($where)->count();
        
        // 统计转盘下注金额/中奖
        $lottery_stat_zp = $zp_lottery_order_db->where($where)->field('sum(buy_price) as total_price, sum(win) as total_win')->find();
       
        $lottery_stat_zp['total_no'] = $zp_lottery_order_db->where($where)->count();
        
        // 佣金
        $total_commission = $wallet_change_log_db->where($where . " and a.type=4")->sum('fee');
        $total_commission_zp = $wallet_change_log_db->where($where . " and a.type=8")->sum('fee');
        
        // 总提现
        $drawcash_db = M('drawcash a');
        $total_apply_drawcash = $drawcash_db->where($where . " and a.status=0")->sum('price');  // 申请
        $total_passed_drawcash = $drawcash_db->where($where3 . " and a.status=1")->sum('price');  // 通过
        $total_completed_drawcash = $drawcash_db->where($where2 . " and a.status=2")->sum('price');  // 打款
        $total_completed_drawcash_tx = M('wx_transfer a')->where($where . " and a.status=2")->sum('amount/100');  // 打款
        
        // 被黑的次数
        $action_log_db = M('user_action_log a');
        $hack_times = $action_log_db->where($where . " and a.action='hack'")->count();
        
        // 渠道支付实际总金额
        $wx_pay_db = M('wx_pay a');
        $total_ch_recharge_price = $wx_pay_db->where($where . " and a.status=1")->sum('price');
        $total_real_ch_recharge_price = $wx_pay_db->where($where . " and a.status=1")->sum('real_price');
        
        $this->assign('filter', $_REQUEST);
        $this->assign('new_users_count', $new_users_count);
        $this->assign('total_recharge_price', $total_recharge_price);
        $this->assign('total_incompleted_recharge_count', $total_incompleted_recharge_count);
        $this->assign('total_completed_recharge_count', $total_completed_recharge_count);
        $this->assign('lottery_stat', $lottery_stat);
        $this->assign('lottery_stat_zp', $lottery_stat_zp);
        $this->assign('total_commission', $total_commission);
        $this->assign('total_commission_zp', $total_commission_zp);
        $this->assign('total_apply_drawcash', $total_apply_drawcash);
        $this->assign('total_passed_drawcash', $total_passed_drawcash);
        $this->assign('total_completed_drawcash', $total_completed_drawcash);
        $this->assign('total_completed_drawcash_tx', $total_completed_drawcash_tx);
        $this->assign('total_income', $curday_total_price);
        $this->assign('total_outcome', $curday_total_win + $curday_commission);
        $this->assign('total_income_zp', $curday_total_price_zp);
        $this->assign('total_outcome_zp', $curday_total_win_zp + $curday_commission_zp);
        $this->assign('total_result', $curday_total);
        $this->assign('total_result_ratio', $win_ratio);
        $this->assign('hack_times', $hack_times);
        $this->assign('total_ch_recharge_price', $total_ch_recharge_price);
        $this->assign('total_real_ch_recharge_price', $total_real_ch_recharge_price);
    
        $this->display();
    }
}
