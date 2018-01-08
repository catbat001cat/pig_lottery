<?php

/**
 * 渠道Ads
*/
namespace Agent\Controller;
use Common\Controller\MemberbaseController;
class ChanneladsController extends MemberbaseController {
	
	public function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq)
	{
		//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		//$pregs = '/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\*|\+|\~|\*@|\*!|\$|\%|\^|\(|\)|union|into|load_file|outfile/';
		
		$pregs = '/select|insert|drop|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/';
		
		
		if ($StrFiltKey == 'req_url' || $StrFiltKey == 'goback')
			return;
			
			if(is_array($StrFiltValue))
			{
				$StrFiltValue=implode($StrFiltValue);
			}
			
			$check= preg_match($pregs,$StrFiltValue);
			if($check == 1){
				require_once SITE_PATH . "/wxpay/log.php";
				
				$logHandler = new \CLogFileHandler ( "logs/hack_" . date ( 'Y-m-d' ) . '.log' );
				$log = \Log::Init ( $logHandler, 15 );
				
				\Log::DEBUG("<br><br>操作IP: ".$_SERVER["REMOTE_ADDR"]."<br>操作时间: ".strftime("%Y-%m-%d %H:%M:%S")."<br>操作页面:".$_SERVER["REQUEST_URI"]."<br>提交方式: ".$_SERVER["REQUEST_METHOD"]."<br>提交参数: ".$StrFiltKey."<br>提交数据: ".$StrFiltValue);
				print "result notice:Illegal operation!";
				exit();
			}
	}
	
	public function filterAttack()
	{
		foreach($_GET as $key=>$value)
		{
			$this->StopAttack($key,$value,$getfilter);
		}
		foreach($_POST as $key=>$value)
		{
			$this->StopAttack($key,$value,$postfilter);
		}
		foreach($_COOKIE as $key=>$value)
		{
			$this->StopAttack($key,$value,$cookiefilter);
		}
	}
	
    function bdUrlAPI($type, $url){
    	$this->filterAttack();
    	
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
    

    public function gen($id) {
    	$this->filterAttack();
    	
        vendor('phpqrcode.phpqrcode');//导入类库
    
        include APP_PATH . "Common/Common/upload.php";
    
        $data = M('ads_template')->where()->order('id desc')->find();
    
        $url = $data['url'];
        $level = 'L';
        $size = 4;
    
        $channel_id = 0;
         
        $channel_db = M('channels');
        $admin_channel = $channel_db->where("admin_user_id=$this->userid")->find();
        $channel_user_id = 0;
        if ($admin_channel != null)
        {
            $channel_id = $admin_channel['id'];
            $channel_user_id = $admin_channel['admin_user_id'];
        }
    
        $hosts_db = M('hostnames');
        $hosts = $hosts_db->where('status=1 and `type` in (0,2)')->order('`type` asc, update_time desc')->select();
        shuffle($hosts);
        $host = $hosts[0];
        
        $url = str_replace("{hostname}", $host['hostname'], $url);
        $url = str_replace("{channel_id}", $channel_id, $url);
        
        $ticket = time();
        $sign = md5($channel_id . $ticket . C('LOGIN_KEY'));
        $url .= '&ticket=' . $ticket . '&sign=' . $sign; 
    
        $ids = date('YmdHis') . $channel_user_id . '_' . $channel_id;
    
        $out_file = './data/upload/' . $ids . '.png';
        \QRcode::png($url,$out_file,$level,$size,2);
    
        $smeta = json_decode($data['smeta'],true);
    
        $bg_image = './data/upload/'.$smeta['thumb'];
    
        $ids = date('YmdHis') . $channel_user_id . '_' . $channel_id . '_out';
    
        $out_file2 = './data/upload/' . $ids . '.png';
    
        $bg_image_c = imagecreatefromstring(file_get_contents($bg_image));
    
        $col = imagecolorallocate($bg_image_c,255,255,255);
        $content = 'Code:' . $this->user['user_activation_key'] . '    ID:' . $channel_user_id;
        imagestring($bg_image_c,5, floatval($data['add_x']), floatval($data['add_y']) + floatval($data['height']) + 10,$content,$col);
    
        image_copy_image($bg_image_c, $out_file, floatval($data['add_x']), floatval($data['add_y']), floatval($data['width']), floatval($data['height']), $out_file2);
    
        echo json_encode(array('code' => 0, 'img' => $out_file2, 'url' => $url));
    }
}
