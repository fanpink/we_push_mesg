<?php
// 、、、、、
// // 、、、、、、多线程post方法获取
// 、、、、、
//常量定义
//测试版
//define('TEMPLATE_ID', 'jW7PVGk8fEOBCd4J4t26OBPhHJNWL_8pwvoRVInIzEA');
//define('APPID', 'wx7d7dd2d3c64abffa');
//define('SECRET', 'eda5b3dbfe16174ecbba3efcdb268911');
//正式版///////
define('TEMPLATE_ID', '1VacxXdvi0X-j7P4XVkGb4vXpaiQcNr7zKqWJ2RPjU4');
define('APPID', 'wx94d55259a906c9eb');
define('SECRET', '4c504beabd94be2af9760f948fe0e5cb');
/*此方法多线程同时请求一个token*/
$openids = json_decode(get_openids(),true);
$openid = $openids['openid'];
$access_token = get_token();
//print_r($openid);
$url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=";
$nodes = array();
for($i=0; $i <= count($openid);$i++)
{
    $info = array();
    $info['url'] = $url.$openid[$i];
    $nodes[] = $info;
}
$return = postMulti($nodes);
$result = "{";
for($i=0;$i<count($openid);$i++)
{
	$result .= '"'.$openid[$i] .'":'.$return[$i].',';
}
$result = substr($result,0,strlen($result)-1);
$result = $result .'}';
$myfile = fopen('Usersinfo.txt', 'w+') ;
fwrite($myfile, $result);
fclose($myfile);
print_r($result);

function get_openids(){//返回 （获取时间，关注总人数，openID列表），数据需要 json_decode后使用
    if (!file_exists('openids.txt')) {
        return get_server_openid();
    }
    $myfile = fopen('openids.txt', 'r') or die('Unable to open file!');
    $data = fread($myfile,filesize('openids.txt'));
    fclose($myfile);
    $openids = json_decode($data,true);
    date_default_timezone_set("PRC");
    $time = date('Y-m-d h:i:s');
    /////时间差计算////
    $get_time = $openids['get_time'];
    $time_distance =strtotime($time)-strtotime($get_time);
    if($time_distance<3600){//1个小时更新一次
        return $data;
    }else{
        return get_server_openid();
    }
}
//从服务器获取 openID 列表，返回 （获取时间，关注总人数，openID列表），数据需要 json_decode后使用
function get_server_openid(){/*///??非常重要，当关注人数超过一万以后需重写此函数???/////*/
    header('Content-Type: text/html; charset=utf-8');
    $access_token = get_token();//获取access_token
    ////??非常重要，当关注人数超过一万以后需重写此函数???//////
    $get_openid_url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$access_token.'&next_openid=';

    $userinfo = json_decode(get_curl($get_openid_url),true);
    date_default_timezone_set("PRC");
    $openids=json_encode(['get_time'=> date('Y-m-d h:i:s'),'total_number'=>$userinfo['count'],'openid'=>$userinfo['data']['openid']]);
    ///文件读写
    $myfile = fopen('openids.txt', 'w+') ;
    fwrite($myfile, $openids);
    fclose($myfile);
    return $openids;
}
function get_token(){
    if (!file_exists('access_token.txt')) {
        return get_server_token();
    }
    $myfile = fopen('access_token.txt', 'r') or die('Unable to open file!');
    $data = fread($myfile,filesize('access_token.txt'));
    fclose($myfile);
    $totkens=json_decode($data,true);
    //echo "token文件读取：";
    //print_r($totkens);
    date_default_timezone_set("PRC");
    $time = date('Y-m-d h:i:s');
    /////时间差计算////
    $get_time = $totkens['get_time'];
    $time_distance =strtotime($time)-strtotime($get_time);
    //printf("时间差:".$time_distance."<br>");////////////输出时间差
    if($time_distance<7000){//有效时间2小时（7200S）
        return $totkens['access_token'];
    }else{
        return get_server_token();
    }
}
function get_server_token(){
    $grant_type ='client_credential';
    $get_token_url='https://api.weixin.qq.com/cgi-bin/token?grant_type='.$grant_type.'&appid='.APPID.'&secret='.SECRET;
    $access_token = get_curl($get_token_url);
    $access_token = json_decode($access_token,true);
    //print_r($access_token);

    $token = $access_token['access_token'];
    date_default_timezone_set("PRC");
    $time = date('Y-m-d h:i:s');

    $tokens = json_encode(['get_time'=>$time,'access_token'=>$token]);
    $myfile = fopen('access_token.txt', 'w+') ;
    fwrite($myfile, $tokens);
    fclose($myfile);
    //echo "token server获取：";
    //print_r($totkens);
    return $token;
}
function get_curl($url){///访问http地址，返回参数
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

function postMulti($nodes,$timeOut = 5)
    {/*{{{*/
        try 
        {
            if (false == is_array($nodes)) 
            {
                return array();
            }
 
            $mh = curl_multi_init(); 
            $curlArray = array();
            foreach($nodes as $key => $info)
            {
                if(false == is_array($info))
                {
                    continue;
                }
                if(false == isset($info['url']))
                {
                    continue;
                }
 
                $ch = curl_init();
                // 设置url
                $url = $info['url'];
                curl_setopt($ch, CURLOPT_URL, $url);
 
                $data = isset($info['data']) ? $info['data'] :null;
                if(false == empty($data))
                {
                    curl_setopt($ch, CURLOPT_POST, 1); 
                    // array
                    if (is_array($data) && count($data) > 0) 
                    {
                        curl_setopt($ch, CURLOPT_POST, count($data));                
                    }
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
 
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                // 如果成功只将结果返回，不自动输出返回的内容
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                // user-agent
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0");
                // 超时
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);
                
                $curlArray[$key] = $ch;
                curl_multi_add_handle($mh, $curlArray[$key]); 
            }
 
            $running = NULL; 
            do { 
                usleep(10000); 
                curl_multi_exec($mh,$running); 
            } while($running > 0); 
 
            $res = array(); 
            foreach($nodes as $key => $info) 
            { 
                $res[$key] = curl_multi_getcontent($curlArray[$key]); 
            } 
            foreach($nodes as $key => $info){ 
                curl_multi_remove_handle($mh, $curlArray[$key]); 
            } 
            curl_multi_close($mh);     
            return $res;
        } 
        catch ( Exception $e ) 
        {
            return array();
        }
 
        return array();
    }/*}}}*/
 

?>