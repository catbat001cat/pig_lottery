<?php
// 上传图片
function uploadbase64($image_info)
{
    $base64 =$image_info;

    // return $this->show(200, '数据获取成功',$base64);
     
    preg_match("/data:image\/(.*);base64,/",$base64,$res);
     
    $ext = $res[1];
    
    if(!in_array($ext,array("jpg","jpeg","png","gif")))
     
    {
        $array_result[] = array("error"=>11);die();
    }
    // =========================给图片生成4位随机数  begin===============================
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    );
    $charsLen = count($chars) - 1;
    shuffle($chars);
    $output = "";
    for ($j=0; $j<4; $j++)
    {
    $output .= $chars[mt_rand(0, $charsLen)];
    }
    // =========================给图片生成4位随机数  end===============================
    $picname = time().$output.'.'.$ext;
    $file='./data/upload/'.$picname; //

    $data = preg_replace("/data:image\/(.*);base64,/","",$base64);

    if (file_put_contents($file,base64_decode($data))===false) {
        return null;
    }else{
        return $file;
    }
 }
 
 function uploadbase64_base($image_info)
 {
     $base64 =$image_info;
 
     // return $this->show(200, '数据获取成功',$base64);
      
     preg_match("/data:image\/(.*);base64,/",$base64,$res);
      
     $ext = $res[1];
      
     if(!in_array($ext,array("jpg","jpeg","png","gif")))
      
     {
         $array_result[] = array("error"=>11);die();
     }
     // =========================给图片生成4位随机数  begin===============================
     $chars = array(
         "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
         "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
         "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
         "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
         "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
         "3", "4", "5", "6", "7", "8", "9"
     );
     $charsLen = count($chars) - 1;
     shuffle($chars);
     $output = "";
     for ($j=0; $j<4; $j++)
     {
         $output .= $chars[mt_rand(0, $charsLen)];
     }
     // =========================给图片生成4位随机数  end===============================
     $picname = time().$output.'.'.$ext;
     $file='./data/upload/'.$picname; //
 
     $data = preg_replace("/data:image\/(.*);base64,/","",$base64);
 
     if (file_put_contents($file,base64_decode($data))===false) {
         return null;
     }else{
         return $picname;
     }
 }