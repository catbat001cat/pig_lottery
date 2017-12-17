<?php

require_once "/log.php";
require_once "/PayNotifyCallBack.php";

\Log::DEBUG("call by wx");

$notify = new \PayNotifyCallBack();
$notify->Handle(false);
//$notify->test();