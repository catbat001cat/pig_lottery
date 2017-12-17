<?php

/**
 * 渠道Ads管理
*/
namespace Agent\Controller;
use Common\Controller\AdminbaseController;
class ChanneladsadminController extends AdminbaseController {
    function bdUrlAPI($type, $url){
        if($type)
            $baseurl = 'http://dwz.cn/create.php';
        else
            $baseurl = 'http://dwz.cn/query.php';
        
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$baseurl);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        if($type)
            $data=array('url'=>$url);
        else
            $data=array('tinyurl'=>$url);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $strRes=curl_exec($ch);
        curl_close($ch);
        $arrResponse=json_decode($strRes,true);
        if($arrResponse['status']!=0)
        {
            echo 'ErrorCode: ['.$arrResponse['status'].'] ErrorMsg: ['.$arrResponse['err_msg']."]<br/>";
            return 0;
        }
        if($type)
            return $arrResponse['tinyurl'];
        else
            return $arrResponse['longurl'];
    }
    
    //CURL  
    private function CURLQueryString($url){  
        //设置附加HTTP头  
        $addHead=array("Content-type: application/json");  
        //初始化curl  
        $curl_obj=curl_init();  
        //设置网址  
        curl_setopt($curl_obj,CURLOPT_URL,$url);  
        //附加Head内容  
        curl_setopt($curl_obj,CURLOPT_HTTPHEADER,$addHead);  
        //是否输出返回头信息  
        curl_setopt($curl_obj,CURLOPT_HEADER,0);  
        //将curl_exec的结果返回  
        curl_setopt($curl_obj,CURLOPT_RETURNTRANSFER,1);  
        //设置超时时间  
        curl_setopt($curl_obj,CURLOPT_TIMEOUT,8);  
        //执行  
        $result=curl_exec($curl_obj);  
        //关闭curl回话  
        curl_close($curl_obj);  
        return $result;  
    }  
    function index() {
    
        $model=M("ads_template a");
        $count=$model->where()->count();
        $page = $this->page($count, 20);
        $lists = $model
        ->where()
        ->order("id DESC")
        ->limit($page->firstRow . ',' . $page->listRows)
        ->select();
        
        $data = M('ads_template')->where()->find();
        
        $url = $data['url'];

        $channel_id = 0;
        $session_admin_id=session('ADMIN_ID');
         
        $channel_db = M('channels');
        $admin_channel = $channel_db->where("admin_user_id=$session_admin_id")->find();
        if ($admin_channel != null)
            $channel_id = $admin_channel['id'];
        
        $url = str_replace("{channel_id}", $channel_id, $url);
        
        $sina_url='http://api.t.sina.com.cn/short_url/shorten.json?source=31641035&url_long='.urlencode($url);  
        
        $result=json_decode($this->CURLQueryString($sina_url), true); 
        
        $this->assign('ads_url', $result[0]['url_short']);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }


    function add() {
    
        $this->display();
    }
    
    function add_post() {
        $db = M('ads_template');

        $title = $_REQUEST['title'];
        $memo = $_REQUEST['memo'];
        $url = $_REQUEST['url'];
        $add_x = $_REQUEST['add_x'];
        $add_y = $_REQUEST['add_y'];
        $width = $_REQUEST['width'];
        $height = $_REQUEST['height'];        
        
        if(!empty($_POST['photos_alt']) && !empty($_POST['photos_url'])){
            foreach ($_POST['photos_url'] as $key=>$url){
                $photourl=sp_asset_relative_url($url);
                $_POST['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$_POST['photos_alt'][$key]);
            }
        }
        $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);
    
        $data=array(
            'title' => $title,
            'memo' => $memo,
            'url' => $url,
            'add_x' => $add_x,
            'add_y' => $add_y,
            'width' => $width,
            'height' => $height,            
            'create_time' => date("Y-m-d H:i:s"),
        );
        
        $data['smeta']=json_encode($_POST['smeta']);
      
        $id = $db->add($data);
    
        $this->success('添加成功');
    }
    
    function edit($id) {
        $db = M('ads_template');
    
        $item = $db->where('id=' . $id)->find();
    
        $this->assign("smeta",json_decode($item['smeta'],true));
        $this->assign('vo', $item);
    
        $this->display();
    }
    
    function edit_post() {
        $db = M('ads_template');

        $id = $_REQUEST['id'];
        $title = $_REQUEST['title'];
        $memo = $_REQUEST['memo'];
        $url = $_REQUEST['url'];
        $add_x = $_REQUEST['add_x'];
        $add_y = $_REQUEST['add_y'];
        $width = $_REQUEST['width'];
        $height = $_REQUEST['height'];

        $data=array(
            'title' => $title,
            'memo' => $memo,
            'url' => $url,
            'add_x' => $add_x,
            'add_y' => $add_y,
            'width' => $width,
            'height' => $height,
            'create_time' => date("Y-m-d H:i:s"),
        );
        
        if(!empty($_POST['photos_alt']) && !empty($_POST['photos_url'])){
            foreach ($_POST['photos_url'] as $key=>$url){
                $photourl=sp_asset_relative_url($url);
                $_POST['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$_POST['photos_alt'][$key]);
            }
        }
        $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);
        $data['smeta']=json_encode($_POST['smeta']);
        
        $db->where('id=' . $id)->save($data);
    
        $this->success('修改成功');
    }
    
    function delete($id) {
        $db = M('ads_template');
    
        if ($db->where('id=' . $id)->count() ==  0)
            $this->error("删除失败！");
        else {
            $db->where('id=' . $id)->delete();
            $this->success("删除成功！");
        }
    }
    
    public function gen($id) {
        vendor('phpqrcode.phpqrcode');//导入类库

        include APP_PATH . "Common/Common/upload.php";
        
        $data = M('ads_template')->where("id=$id")->find();

        $url = $data['url'];
        $level = 'L';
        $size = 4;
        
        $channel_id = 0;
        $channel_user_id = 0;
        $session_admin_id=session('ADMIN_ID');
         
        $channel_db = M('channels');
        $admin_channel = $channel_db->where("admin_user_id=$session_admin_id")->find();
        if ($admin_channel != null)
        {
            $channel_id = $admin_channel['id'];
            $channel_user_id = $admin_channel['admin_user_id'];
        }
        
        $url = str_replace("{channel_id}", $channel_id, $url);
        
        // 获取可用域名做二维码
        $hosts_db = M('hostnames');
        $host = $hosts_db->where('status=1 and `type` in (0,2)')->order('update_time desc')->find();
        $url = str_replace("{hostname}", $host['hostname'], $url);
        
        $ticket = time();
        $sign = md5($channel_id . $ticket . C('LOGIN_KEY'));
        $url .= '&ticket=' . $ticket . '&sign=' . $sign;
        
        $ids = date('YmdHis') . $session_admin_id . '_' . $channel_id;
        
        $out_file = './data/upload/' . $ids . '.png';
        \QRcode::png($url,$out_file,$level,$size,2);
        
        $smeta = json_decode($data['smeta'],true);

        $bg_image = './data/upload/'.$smeta['thumb'];
        
        $ids = date('YmdHis') . $session_admin_id . '_' . $channel_id . '_out';
        
        $out_file2 = './data/upload/' . $ids . '.png';
        
        $bg_image_c = imagecreatefromstring(file_get_contents($bg_image));

        $col = imagecolorallocate($bg_image_c,255,255,255);
        $content = 'ID:' . $channel_user_id;
        imagestring($bg_image_c,5,20,30,$content,$col);
        
        image_copy_image($bg_image_c, $out_file, floatval($data['add_x']), floatval($data['add_y']), floatval($data['width']), floatval($data['height']), $out_file2);
        
        echo json_encode(array('code' => 0, 'img' => $out_file2, 'url' => $url));
    }
}
