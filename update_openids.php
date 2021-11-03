<?php
header("Content-Type:text/html;charset=utf-8");
require_once './push_core.php';
$send = new push_core();
$openid = $send->get_server_openid();
echo $openid;
?>