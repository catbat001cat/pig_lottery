<?php
/**
* 	配置账号信息
* 	配置要和证书在一起！！！！
*/

class WxTransfersConfig
{
	//=======【基本信息设置】=====================================
	//
	/**
	 * TODO: 修改这里配置为您自己申请的商户信息
	 * 微信公众号信息配置
	 * 
	 * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
	 * 
	 * MCHID：商户号（必须配置，开户邮件中可查看）
	 * 
	 * KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
	 * 设置地址：https://pay.weixin.qq.com/index.php/account/api_cert
	 * 
	 */
    public static $APPID = '';
    public static $MCHID = '';
    public static $KEY = '';
	public static $APPSECRET = '';
	
	//=======【证书路径设置】=====================================
	/**
	 * TODO：设置商户证书路径
	 * 证书路径,注意应该填写绝对路径,发送红包和查询需要，可登录商户平台下载
	 * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
	 * @var path 跟这个文件同一目录下的cert文件夹放置证书！！！！
	 */
	const SSLCRET12 = 'cert/apiclient_cert_11.p12';
	const SSLCERT_PATH = 'cert/apiclient_cert_11.pem';
	const SSLKEY_PATH = 'cert/apiclient_key_11.pem';
	const SSLROOTCA = 'cert/rootca_11.pem';
	
	//=======【证书路径设置】=====================================
	/**
	 * 获取文件的路径，证书需要完整路径
	 * @return string
	 */
	public static function getRealPath(){
		//return __DIR__.'/';
		return '/usr/local/';
	}
}
