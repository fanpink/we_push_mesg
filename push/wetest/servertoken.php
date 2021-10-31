<?php
/*
QQQQQQQQQQQQQ-------------官方批量获取方法--------------QQQQQQQQQQQQQQQQ
*/
//常量定义
// //测试版
// define('TEMPLATE_ID', 'jW7PVGk8fEOBCd4J4t26OBPhHJNWL_8pwvoRVInIzEA');
// define('APPID', 'wx7d7dd2d3c64abffa');
// define('SECRET', 'eda5b3dbfe16174ecbba3efcdb268911');
//正式版///////
define('TEMPLATE_ID', '1VacxXdvi0X-j7P4XVkGb4vXpaiQcNr7zKqWJ2RPjU4');
define('APPID', 'wx94d55259a906c9eb');
define('SECRET', '4c504beabd94be2af9760f948fe0e5cb');
/////////////
////////////
$token = get_server_token();
echo $token;
//////////////////
/////////////////
////////////////

function get_server_token(){
    $grant_type ='client_credential';
    $get_token_url='https://api.weixin.qq.com/cgi-bin/token?grant_type='.$grant_type.'&appid='.APPID.'&secret='.SECRET;
    $access_token = get_curl($get_token_url);
    $access_token = json_decode($access_token,true);
    print_r($access_token);

    $token = $access_token['access_token'];
    date_default_timezone_set("PRC");
    $time = date('Y-m-d h:i:s');

    $tokens = json_encode(['get_time'=>$time,'access_token'=>$token]);
    $myfile = fopen('../access_token.txt', 'w+') ;
    fwrite($myfile, $tokens);
    fclose($myfile);
    echo "token server获取：";
    print_r($totkens);
    return $token;
}
function get_curl($url){///get访问http地址，返回参数
    //用curl传参
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

    //关闭ssl验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);


    curl_setopt($ch,CURLOPT_HEADER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    //return json_decode($output, true);
    return $output;
}