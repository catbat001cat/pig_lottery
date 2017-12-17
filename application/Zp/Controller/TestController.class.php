<?php
namespace Zp\Controller;

use Common\Controller\HomebaseController;

class TestController extends HomebaseController
{

    private $wallet_db = null;

    function _initialize()
    {
        parent::_initialize();
        
        $this->wallet_db = M('wallet');
    }

    private function randFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
    
    // 转盘逻辑
    public function create_lottery_order()
    {
        $discType = $_REQUEST['discType'];
        $price = intval($_REQUEST['price']);
        $robot_name = $_REQUEST['robot_name'];
        
        $user_db = M('users');
        $user = $user_db->where("user_login='$robot_name'")->find();
        $user_id = $user['id'];
        
        $prices_arr = [
            2,
            5,
            10,
            30,
            100,
            300,
            1000,
            2000
        ];
        
        $wallet_db = M('wallet');
        $wallet = $wallet_db->where("user_id=" . $user_id)->find();
        
        $win_db = M('zp_win');
        
        $total_price = $price;
        
        $det = $wallet['money'] - $total_price;
        $money_det = $wallet['money'] - $price;
        
        if ($det < 0 || $money_det < 0)
            echo json_encode(array(
                'ret' => - 1,
                'msg' => '余额不足,当前余额:' . $wallet['money'] . ',价格:' . $price
            ));
        else {
            $ret = $this->wallet_db->where("user_id=" . $user_id)->setDec('money', $total_price);
            
            if ($ret <= 0)
                echo json_encode(array(
                    'ret' => - 1,
                    'msg' => '余额不足,当前余额:' . $wallet['money'] . ',价格:' . $price
                ));
            else {
                
                $types_arr = [
                    "小盘",
                    "中盘",
                    "大盘"
                ];
                
                $zp_lottery_db = M('zp_lottery');
                
                $weight_db = M('zp_weight');
                
                $weight = $weight_db->where("type=$discType and slot=" . C('ZP_CONTROL_METHOD_PRICE_' . $price))->find();
                
                // 判断是否是新手
                $is_beginner = false;
                if ($zp_lottery_db->where("user_id=$user_id")->count() < 4)
                    $is_beginner = true;
                
                $total_weight = 0;
                for ($i = 1; $i <= 12; $i ++)
                    $total_weight += $weight['weight_' . $i];
                
                $cur_weight = $this->randFloat();
                                
                
                $d1 = 0;
                $d2 = 0;
                $rand_index = 0;
                for ($i = 0; $i < 12; $i ++) {
                    $d2 += $weight['weight_' . ($i + 1)] / $total_weight;
                    if ($i == 0)
                        $d1 = 0;
                    else
                        $d1 += $weight['weight_' . $i] / $total_weight;
                    if ($cur_weight >= $d1 && $cur_weight <= $d2) {
                        $rand_index = $i;
                        break;
                    }
                }
                
                $status = 1; // 0-未开奖,1-中奖,2-未中奖
                $prize_id = $rand_index + 1;
                $win_table = $win_db->where("price=$price and prize_id=$prize_id")->find();
                $win_prize = $win_table['win' . $discType]; // 中奖
                $data = array(
                    'user_id' => $user_id,
                    'buy_price' => $price,
                    'status' => $status,
                    'prize_id' => $prize_id,
                    'type' => $discType,
                    'win' => $win_prize,
                    'create_time' => date('Y-m-d H:i:s')
                );
                $data['id'] = $zp_lottery_db->add($data);
                
                $action_log = M('user_action_log');
                $log_data = array(
                    'user_id' => $user_id,
                    'action' => 'buy_zp_lottery',
                    'params' => '类型:' . $types_arr[$discType] . ',金额:' . $price . ',订单:' . $data['id'],
                    'ip' => get_client_ip(0, true),
                    'create_time' => date('Y-m-d H:i:s')
                );
                $action_log->add($log_data);
                
                $change_db = M('wallet_change_log');
                
                $change_data = array(
                    'user_id' => $user_id,
                    'object_id' => $data['id'],
                    'type' => 1,
                    'fee' => $total_price,
                    'create_time' => date('Y-m-d H:i:s'),
                    'memo' => '投注'
                );
                
                $change_db->add($change_data);
                
                // 如果中奖了
                if ($status == 1) {
                    $wallet_db->where("user_id=" . $user_id)->setInc('money', $win_prize);
                    
                    $change_db = M('wallet_change_log');
                    
                    $change_data = array(
                        'user_id' => $user_id,
                        'object_id' => $data['id'],
                        'type' => 2,
                        'fee' => $win_prize,
                        'create_time' => date('Y-m-d H:i:s'),
                        'memo' => '中奖:' . $cur_weight
                    );
                    
                    $change_db->add($change_data);
                    
                    $this->ajaxReturn(array(
                        'ret' => 1,
                        'is_win' => 1,
                        'prize_id' => $prize_id,
                        'win_prize' => $win_prize
                    ));
                } else {
                    $this->ajaxReturn(array(
                        'ret' => 1,
                        'is_win' => 0,
                        'prize_id' => $prize_id,
                        'win_prize' => 0
                    ));
                }
            }
        }
    }
    
    // 转盘逻辑
    public function create_lottery_order_old()
    {
        $discType = $_REQUEST['discType'];
        $price = intval($_REQUEST['price']);
        $robot_name = $_REQUEST['robot_name'];
        
        $user_db = M('users');
        $user = $user_db->where("user_login='$robot_name'")->find();
        $user_id = $user['id'];
        
        $prices_arr = [
            2,
            5,
            10,
            30,
            100,
            300,
            1000,
            2000
        ];
        
        $wallet_db = M('wallet');
        $wallet = $wallet_db->where("user_id=" . $user_id)->find();
        
        $total_price = $price;
        
        $det = $wallet['money'] - $total_price;
        $money_det = $wallet['money'] - $price;
        
        if ($det < 0 || $money_det < 0)
            echo json_encode(array(
                'ret' => - 1,
                'msg' => '余额不足,当前余额:' . $wallet['money'] . ',价格:' . $price
            ));
        else {
            $ret = $this->wallet_db->where("user_id=" . $user_id)->setDec('money', $total_price);
            
            if ($ret <= 0)
                echo json_encode(array(
                    'ret' => - 1,
                    'msg' => '余额不足,当前余额:' . $wallet['money'] . ',价格:' . $price
                ));
            else {
                
                $types_arr = [
                    "小盘",
                    "中盘",
                    "大盘"
                ];
                
                $zp_lottery_db = M('zp_lottery');
                
                $weight_db = M('zp_weight_' . C('ZP_CONTROL_METHOD'));
                
                $weights = $weight_db->where("price=$price")
                    ->order('prize_id asc')
                    ->select();
                
                // 判断是否是新手
                $is_beginner = false;
                if ($zp_lottery_db->where("user_id=$user_id")->count() < 4)
                    $is_beginner = true;
                
                $total_weight = 0;
                $weight_value_name = 'weight_normal' . $discType;
                if ($is_beginner)
                    $weight_value_name = 'weight_beginner' . $discType;
                for ($i = 0; $i < count($weights); $i ++)
                    $total_weight += $weights[$i][$weight_value_name];
                
                $cur_weight = $this->randFloat();
                
                $d1 = 0;
                $d2 = 0;
                $rand_index = 0;
                for ($i = 0; $i < count($weights); $i ++) {
                    $d2 += $weights[$i][$weight_value_name] / $total_weight;
                    if ($i == 0)
                        $d1 = 0;
                    else
                        $d1 += $weights[$i - 1][$weight_value_name] / $total_weight;
                    if ($cur_weight >= $d1 && $cur_weight <= $d2) {
                        $rand_index = $i;
                        break;
                    }
                }
                
                $status = 1; // 0-未开奖,1-中奖,2-未中奖
                $prize_id = $rand_index + 1;
                $win_prize = $weights[$rand_index]['win' . $discType]; // 中奖
                $data = array(
                    'user_id' => $user_id,
                    'buy_price' => $price,
                    'status' => $status,
                    'prize_id' => $prize_id,
                    'type' => $discType,
                    'win' => $win_prize,
                    'create_time' => date('Y-m-d H:i:s')
                );
                $data['id'] = $zp_lottery_db->add($data);
                
                $action_log = M('user_action_log');
                $log_data = array(
                    'user_id' => $user_id,
                    'action' => 'buy_zp_lottery',
                    'params' => '类型:' . $types_arr[$discType] . ',金额:' . $price . ',订单:' . $data['id'],
                    'ip' => get_client_ip(0, true),
                    'create_time' => date('Y-m-d H:i:s')
                );
                $action_log->add($log_data);
                
                $change_db = M('wallet_change_log');
                
                $change_data = array(
                    'user_id' => $user_id,
                    'object_id' => $data['id'],
                    'type' => 1,
                    'fee' => $total_price,
                    'create_time' => date('Y-m-d H:i:s'),
                    'memo' => '投注'
                );
                
                $change_db->add($change_data);
                
                // 如果中奖了
                if ($status == 1) {
                    $wallet_db->where("user_id=" . $user_id)->setInc('money', $win_prize);
                    
                    $change_db = M('wallet_change_log');
                    
                    $change_data = array(
                        'user_id' => $user_id,
                        'object_id' => $data['id'],
                        'type' => 2,
                        'fee' => $win_prize,
                        'create_time' => date('Y-m-d H:i:s'),
                        'memo' => '中奖:' . $cur_weight
                    );
                    
                    $change_db->add($change_data);
                    
                    $this->ajaxReturn(array(
                        'ret' => 1,
                        'is_win' => 1,
                        'prize_id' => $prize_id,
                        'win_prize' => $win_prize
                    ));
                } else {
                    $this->ajaxReturn(array(
                        'ret' => 1,
                        'is_win' => 0,
                        'prize_id' => $prize_id,
                        'win_prize' => 0
                    ));
                }
            }
        }
    }
}
