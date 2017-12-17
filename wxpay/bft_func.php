<?php
function encrypt_http($input,$key){//数据加密
    $size = mcrypt_get_block_size(MCRYPT_3DES,'ecb');
    $input = pkcs5_pad($input, $size);
    $key = str_pad($key,24,'0');
    // ECHO $this->key;
    //echo "<br>";
    $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
    $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    @mcrypt_generic_init($td, $key, $iv);
    $data = mcrypt_generic($td, $input);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    // ECHO $key;
    //echo "<br>";
    // $data = base64_encode($this->PaddingPKCS7($data));
    $data = base64_encode($data);
    return $data;
}
//加密函数调用不要修改。//
function pkcs5_pad ($text, $blocksize) {
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
}

function pkcs5_unpad($text){
    $pad = ord($text{strlen($text)-1});
    if ($pad > strlen($text)) {
        return false;
    }
    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
        return false;
    }
    return substr($text, 0, -1 * $pad);
}
//加密函数调用不要修改。//

function http_request_json($url,$post_data){
     
    $ch = curl_init();//打开
    $Pt=http_build_query($post_data);
    //echo $Pt;
    //	exit;
    //exit;
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$Pt);
    //echo $post_data;
    //exit;
    $response  = curl_exec($ch);
    curl_close($ch);//关闭
    //pr($response);
    $result = json_decode($response,true);
    return $result;
}
function http_request_json2($url,$post_data){
     
    $ch = curl_init();//打开
    $Pt=http_build_query($post_data);
    //echo $Pt;
    //	exit;
    //exit;
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$Pt);
    //echo $post_data;
    //exit;
    $response  = curl_exec($ch);
    curl_close($ch);//关闭
    pr($response);
    //$result = json_decode($response,true);
    return $result;
}
function pr($rs)
{
    echo "<pre>";
    print_r($rs);
    echo "</pre>";


}



/**
 * 数组批量转编码 协议使用
 * @param string $encrypted 解密内容
 * @return array 数组类型的返回结果
 */
function ArrEncode($arr)
{

    foreach($arr as $k=>$v){
        if(is_array($v)){
            $arr[$k] =ArrEncode($v);
        }else{
            if (is_numeric($k))
            {
                unset($arr[$k]);
            }else
            {
                $arr[$k]=urlencode($v);

                	
            }
        }
    }
    return $arr;
}




/**
 * http加密 协议使用
 * @param string $string 解密内容
 * @param string $signkey 秘钥
 * @return string 字符串类型的返回结果
 */

function EnTosignHP($string,$signkey)
{
    $nd=$string."#".$signkey;
    $string=md5($nd);
    //echo "加密前:".$nd."<br>";
    //echo $nd;
    //exit;
    return strtoupper($string);
}


/**
 * http数组拼接 协议使用
 * @param string $ar 解密内容
 * @return string 字符串类型的返回结果
 */

function TosignHttp($ar)
{
    ksort($ar);

    $httpcontent="";
    foreach($ar as $key=>$val)
    {
        if($httpcontent=="")
        {
            	
            $httpcontent="#";
        }else
        {
            	
            $httpcontent.="#";
        }

        $httpcontent.=$ar[$key];

    }

    return $httpcontent;

}


/**
 * 数据解码 协议使用
 * @param string $ar 解码内容
 * @return array 数组类型的返回结果
 */
function ArrDecode($arr)
{

    foreach($arr as $k=>$v){
        if(is_array($v)){
            $arr[$k] =ArrDecode($v);
        }else{
            if (is_numeric($k))
            {
                unset($arr[$k]);
            }else
            {
                $arr[$k]=urldecode($v);

                	
            }
        }
    }
    return $arr;
}