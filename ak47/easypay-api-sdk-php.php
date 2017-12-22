<?php
	
	/*
	 * easypay-sdk-php
	 *
	 * 函数：
	 *   
	 *        request($apiName, $apiVersion, $parameters)			调用远程API
	 *		  process_callback4trade();								处理交易回调
	 *		  process_callback4pay();								处理出款回调
	 *
	 *
	 */

	require_once SITE_PATH ."/ak47/common.php";
	require_once SITE_PATH ."/ak47/hack.php";

	$debug = false;

	$gateway = NULL;
	$certNo = NULL;
	$password = NULL;
	$publicKey = NULL;
	$privateKey = NULL;

	/**
	 * 请求API
	 * @param $apiName       API名称
	 * @param $apiVersion    API版本
	 * @param $parameters    API参数
	 * @param $files    	 文件集合 array(
	 *										"key"  => array("fileName" => "1.jpg", "contentType" => "application/x-jpg", "content" => array())
	 *												)
	 * 
	 * @return
	 *			array(
	 *				"errorCode" => 错误码 SUCCEED 表示成功
	 *				"msg"       => 错误提示
	 *				"data"		=> 成功时 JSON格式的响应数据
	 *          )
	 *
	 */
	function request($apiName, $apiVersion, $parameters, $files = NULL) {
		global $gateway, $certNo;
		$url = $gateway . $apiName . "/" . $apiVersion;
		$httpHeaders = array();
		$headers = array();
		$headers[PROTOCOL_VERSION] = PROTOCOL_VERSION_VALUE;
		$headers[SDK_VERSION] = SDK_VERSION_VALUE;
		$headers[SK] = $certNo;

		$ch = curl_init($url);

		//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:7777');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);     
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		
		$body = NULL;
		if(!isset($files) || count($files) == 0) {
			$body = json_encode($parameters);
			$headers[SIGN] = sign($url, $headers, $body);
			array_push($httpHeaders, "Content-Type:application/json;charset=utf-8");
		} else {
			$headers[SIGN] = sign($url, $headers, null);

			$boundary = '-------------' . md5(time());
			array_push($httpHeaders, 'Content-Type: multipart/form-data; boundary=' . $boundary);

			// 参数
			$body = '';
			foreach ($parameters as $key => $value) {
		        $body .= "--" . $boundary . "\r\n"
		            . 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n"
		            . $value . "\r\n";
		    }

		    // 文件
		    foreach ($files as $key => $file) {
		    	$body .= "--" . $boundary . "\r\n";
                $body .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . $file['fileName'] . "\" \r\n";
                $contentType = isset($file['contentType']) ? $file['contentType'] : 'application/octet-stream';
                $body .= 'Content-Type: ' . $contentType . "\r\n\r\n";
                $body .= $file['content'] . "\r\n";
		    }

		    $body .= "--" . $boundary . "--";
		}

		foreach ($headers as $key => $value) {
			array_push($httpHeaders, $key . ':' . $value);
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);


		$response = curl_exec($ch);

		if (curl_errno($ch)) {
    		echo 'HTTP Request Error: ' . curl_error($ch);
    		return;
		}


		$parsedResponse = parse_http_response($ch, $response);
		$httpStatus = $parsedResponse["httpStatus"];
		$headers = $parsedResponse["headers"];
		$body = $parsedResponse["body"];

		//print_r($parsedResponse);

		curl_close($ch);


		//printf("url: %s\n", $url);
		//print_r($headers);
		//printf("body: %s\n", $body);
		//print_r($parameters);

		$result = array(
			"errorCode" => $headers[ERROR_CODE],
			"msg" => $headers[MSG],
			"data" => $body
			);

		return $result;
	}

	/**
	 * 处理交易回调
	 * @param $handlerFunctionName  业务处理函数名  function handle($data) {}   $data 为JSON格式数据
	 *
	 */
	function process_callback4trade($handlerFunctionName) {

		$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$headers = getallheaders();
		$body = file_get_contents("php://input");

		//print_r($headers);

		$signed = $headers[SIGN];
		unset($headers[SIGN]);

		if(!validate_sign(base64_decode($signed), $url, $headers, $body)) {
			echo "Wrong sign";
			return;
		}

		// do your business $body is JSON 格式的数据
		call_user_func($handlerFunctionName, json_decode($body));

		echo 'SUCCEED';
	}

	/**
	 * 处理代付回调
	 * @param $handlerFunctionName  业务处理函数名  function handle($data) {}   $data 为JSON格式数据
	 *
	 */
	function process_callback4pay($handlerFunctionName) {

		$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$headers = getallheaders();
		$body = file_get_contents("php://input");

		//print_r($headers);

		$signed = $headers[SIGN];
		unset($headers[SIGN]);

		if(!validate_sign(base64_decode($signed), $url, $headers, $body)) {
			echo "Wrong sign";
			return;
		}

		call_user_func($handlerFunctionName, json_decode($body));

		// do your business $body is JSON 格式的数据


		echo 'SUCCEED';
	}

	/**
	 * 签名
	 * @param $url
	 * @param $headers
	 * @param $body
	 * @return
	 *				签名值
	 *
	 */
	function sign($url, $headers, $body) {
		global $privateKey;
		
		ksort($headers);

		$str = $url;
		foreach ($headers as $key => $value) {
			if(strpos($key, PROTOCOL_PREFIX) === 0) {
				$str = $str . "&" . $key . "=" . $value ;
			}
		}

		if(isset($body)) {
			$str = $str . "&" . $body;
		}

		//print_r($sorted);
		//print_r($str);
		
		$buffer = openssl_sign($str, $signature, $privateKey['pkey']);

		//printf("sign str: %s\n", $str);
		return base64_encode($signature);
	}

	/**
	 * 验证签名
	 * @param $signed
	 * @param $url
	 * @param $headers;
	 * @param $body
	 * 
	 * @return
	 *				true    签名正确
	 *				false   签名失败
	 *
	 */
	function validate_sign($signed, $url, $headers, $body) {
		global $publicKey;
		$str = $url;
		ksort($headers);
		foreach ($headers as $key => $value) {
			if(strpos($key, PROTOCOL_PREFIX) === 0) {
				$str = $str . "&" . strtolower($key) . "=" . $value ;
			}
		}

		$str = $str . "&" . $body;

		return (bool) openssl_verify($str, $signed, $publicKey);
	}

	/**
	 * 初始化
	 * 请看配置文件sdk.ini
	 */
	function init() {
		global $gateway, $certNo, $password, $publicKey, $privateKey;
		$config = parse_ini_file("sdk.ini");
		$gateway = $config["gateway"];
		
		if(substr($gateway, $gateway.length - 1, 1) != '/') {
			$gateway = $gateway . '/';
		}

		$certNo = $config["certNo"];
		$password = file_get_contents(SITE_PATH . '/ak47/' . $config["password"]);
		
		$publicKey = openssl_pkey_get_public(file_get_contents(SITE_PATH . '/ak47/'. $config["publicKey"]));

		openssl_pkcs12_read(file_get_contents(SITE_PATH . '/ak47/' . $config["privateKey"]), $privateKey, $password);

		//print_r($config);

		//printf("password: %s\n", $password);
		//print_r($publicKey);
		//print_r($privateKey);
	}

	/**
	 * 解析HTTP响应
	 * 
	 * @param $ch
	 * @param $response
	 * 
	 * @return
	 *				array(
	 *					"status" 		=> HTTP状态码
	 *					"headers"	 	=> HTTP响应头
	 *					"body"			=> HTTP响应体 
	 *				)
	 * 
	 */
	function parse_http_response($ch, $response) {
    	$headers = array();

    	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

    	$header_text = substr($response, 0, $header_size);
		$body = substr($response, $header_size);

    	printf("header_text: %s\n", $header_text);

    	$status = 0;

    	foreach (explode("\r\n", $header_text) as $i => $line) {
    		if(!$line) {
    			continue;
    		}
	        if ($i === 0) {
	        	list ($protocol, $status) = explode(' ', $line);
	            $status = $status;
	        } else {
	            list ($key, $value) = explode(': ', $line);
	            $headers[strtolower($key)] = urldecode($value);
	        }
	    }

	    //print_r($headers);

    	return array("status" => $status, "headers" => $headers, "body" => $body);
	}


	init();
?>