<?php
$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=aaaaa&secret=bbbb&code=cccc&grant_type=authorization_code";

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 500);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //

$res = curl_exec($curl);

echo '---->' . $res;

curl_close($curl);

?>
