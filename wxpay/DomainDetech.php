<?php
class DomainDetech
{
    private $app_id     = null;
    private $app_secret = null;

    public function __construct($app_id='', $app_secret='')
    {
        $this->app_id     = $app_id;
        $this->app_secret = $app_secret;
    }

    public function run($detech_url='')
    {

        $url = $this->genarateUrl($this->app_id, $this->app_secret, $detech_url);
        $data = $this->http_request($url, null, 10);
        $data = json_decode($data, true);

        return $data;
    }

    /**
     * ��ɽӿ�URL
     *
     * @param unknown $app_id
     * @param string $app_secret
     */
    private function genarateUrl($app_id='', $app_secret='', $detech_url='')
    {

        $args               = array();
        $args['timestamp']  = time();
        $args['app_id']     = $app_id;
        $args['app_secret'] = $app_secret;
        $args['url']        = urlencode($detech_url);
        $sign               = $this->getSign($args);

        $args_str = array();
        unset($args['app_secret']);
        foreach ($args as $k => $v) {
            $args_str[] = $k . '=' . $v;
        }
        $arg_str = implode('&', $args_str);

        $url = 'http://api.917fenxiang.cn/wxapi/detechdomain';
        $url .= '?' . $arg_str . '&sign=' . $sign;

        return $url;
    }

    /**
     * ץhttps���
     *
     * @param unknown $url
     * @param string $data
     * @param string $timeout
     */
    public function http_request($url, $data = null, $timeout = 30)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if ($timeout) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }

    /**
     * �ӿڲ������
     */
    private function getSign($args=array())
    {
        $tmpArr = array();
        foreach ($args as $arg) {
            $tmpArr[] = $arg;
        }
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        return $tmpStr;
    }
}