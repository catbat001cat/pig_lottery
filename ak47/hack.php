<?php
    
	require_once SITE_PATH ."/ak47/common.php";

    if (!function_exists('getallheaders')) { 
        function getallheaders() { 
            $headers = array(); 
            foreach ($_SERVER as $name => $value) { 
                if (substr($name, 0, 5) == 'HTTP_') {
                    $key = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))));
                    if(0 == strcmp($key, SIGN)) {
                        $headers[$key] = $value; 
                    } else {
                        $headers[$key] = urldecode($value); 
                    }
                } 
            } 
            return $headers; 
        }
    }

?>