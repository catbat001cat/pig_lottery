<?php

/**
 * 渠道
*/
namespace Agent\Controller;
use Common\Controller\MemberbaseController;
class ChannelController extends MemberbaseController {
   public function users($level)
   {
       $channel_db = M('channels a');
       $recharge_db = M('recharge_order');
       $lottery_order_db = M('lottery_order');
       $wallet_change_log_db = M('wallet_change_log a');
       
       $my_channel = $channel_db->where("admin_user_id=$this->userid")->find();
       
       if ($level == 1)
           $child_channels = $channel_db->where("parent_id=" . $my_channel['id'])->select();
       else if ($level == 2)
           $child_channels = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')->where("b.parent_id=" . $my_channel['id'])->select();
       else if ($level == 3)
           $child_channels = $channel_db->join('__CHANNELS__ b on b.id=a.parent_id', 'left')->join('__CHANNELS__ c on c.id=b.parent_id', 'left')->where("c.parent_id=" . $my_channel['id'])->select();

       $channel_users = get_users_from_channels($child_channels);
       
       for ($i=0; $i<count($channel_users); $i++)
       {
           $channel_users[$i]['total_recharge'] = $recharge_db->where("user_id=" . $channel_users[$i]['id'] . " and `status`=1")->sum('price');
           $total_lottery_record = $lottery_order_db->where("user_id=" . $channel_users[$i]['id'])->field('sum(price) as total_price, sum(win) as total_win')->find();
           $channel_users[$i]['total_lottery_price'] = $total_lottery_record['total_price'];
           $channel_users[$i]['total_lottery_win'] = $total_lottery_record['total_win'];
           $channel_users[$i]['total_lottery_win'] = $wallet_change_log_db->join("__LOTTERY_ORDER__ b on b.id=a.object_id", 'left')->where("b.user_id=" . $channel_users[$i]['id'] . " and a.user_id=" . $channel_users[$i]['id'])->sum('fee');
       }
       
       $this->assign('users', $channel_users);
       
       $this->display(':channel_users');
   }
}
