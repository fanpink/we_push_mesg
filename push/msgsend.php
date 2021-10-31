<?php
//常量定义
//测试版
//define('TEMPLATE_ID', 'jW7PVGk8fEOBCd4J4t26OBPhHJNWL_8pwvoRVInIzEA');
//define('APPID', 'wx7d7dd2d3c64abffa');
//define('SECRET', 'eda5b3dbfe16174ecbba3efcdb268911');
//正式版///////
define('TEMPLATE_ID', '1VacxXdvi0X-j7P4XVkGb4vXpaiQcNr7zKqWJ2RPjU4');
define('APPID', 'wx94d55259a906c9eb');
define('SECRET', '4c504beabd94be2af9760f948fe0e5cb');
//常量定义

$data = json_decode(file_get_contents('php://input'), true);//获取客户端post上传json数据
if($data!=null){
send($data);
}
printf("空请求/检查提交参数");

//send方法向 腾讯服务器post提交模板信息
function send($data){
    $title = $data['title'];//标题
    $time = $data['time'];//发震时间
    $M = $data['M'];//震级
    $local = $data['local'];//经纬度坐标
    $deeth = $data['deeth'];//震源深度
    $from = $data['from'];//信息来源
    $info = $data['info'];//简介
    $url = $data['url'];///链接地址
    //////////////////////////////
    $openids = json_decode(get_openids(), true);//调用get_openids方法获取openids[],并解码
    $openid = $openids['openid'];//openID列表(数组)
    //$openid =["ohUpSwpVIwr-oFQjPGDCTgnaMYP0"];
    //	ohUpSwpVIwr-oFQjPGDCTgnaMYP0
    
    $total_number = $openids['total_number'];//openID总数
    //$total_number = count($openid);
    $access_token = get_token();
    //printf("<br>".$access_token);
    /////////////////////////////////
    date_default_timezone_set("PRC");
    $time1 = date('Y-m-d h:i:s');
   
    ///<——————————————————————————————————————————————————批量发送微信模板信息的三种方法————————————————————————————————————->////////////
    //////////改进方法（多线程）//////////
    foreach ($openid as $id) {
        $json_template = json_tempalte($id, $title, $time, $M, $local, $deeth, $from, $info, $url);
		$jt[]=$json_template;
    }
    /////以上为核心////
    $ok = 0;
    $errmsg_temp="";
    $errmsg="";
    $return = curl_multi_post($jt,"https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token);
    foreach($return as $x =>$y)
    {
    	foreach ($return[$x] as $z =>$c)
    	{
    		//echo "<br>".$c;
    		$data_temp = json_decode($c,true);
    		$errcode = $data_temp["errcode"];
    		if($errcode)
    		{
    			$ok++;
    			if($errmsg_temp != $data_temp["errmsg"])
    			{
    				$errmsg_temp = $data_temp["errmsg"];
    				$errmsg=$data_temp["errmsg"]."&&";
    			}
    		}
    	}
    }
    if($ok){
    	$re = "发送失败人数".$ok."。"."发送总人数：" . $total_number ."。\n"."错误代码：".$errmsg;
    }else{
		$re ="发送失败人数".$ok."。"."发送总人数：". $total_number ."。\n";}
    ////////改进方法/////////////
    /*
     /////////单线程方法///////////
     foreach ($openid as $id) {
        $json_template = json_tempalte($id, $title, $time, $M, $local, $deeth, $from, $info, $url);
        $res =postjson("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token,$json_template);
    }
     /////////单线程方法///////////
     */
     /*
     /////////fsockopen方法///////////
     foreach ($openid as $id) {
        $json_template = json_tempalte($id, $title, $time, $M, $local, $deeth, $from, $info, $url);
        $res = fsockopen_post($access_token, $json_template);
    }
     /////////fsockopen方法///////////
     */
     ///<——————————————————————————————————————————————————批量发送微信模板信息的三种方法————————————————————————————————————->////////////
     print_r($re);

    //$msg = "发送总人数：" . $total_number ."。\n";
    //echo  $msg;

    /////时间差计算////
    $time2 = date('Y-m-d h:i:s');
    $time_distance = strtotime($time2) - strtotime($time1);
    echo "所用时间： {$time_distance} s。";
}

//生成模板json
function json_tempalte($openid,$title,$time,$M,$local,$deeth,$from,$info,$url){
    $template = [
                'touser'=>$openid,  //用户openid
                'template_id'=>TEMPLATE_ID, //模板ID
                'url'=>$url, //点击模板消息会跳转的链接
                'data'=>
                    [
                        'first' =>['value'=>$title,'color'=>'#333'],
                        'keyword1' => ['value'=>$time,'color'=>'#157efb'],
                        'keyword2' => ['value'=>$M,'color'=>'#157efb'],
                        'keyword3' => ['value'=>$local,'color'=>'#157efb'],
                        'keyword4' => ['value'=>$deeth,'color'=>'#157efb'],
                        'keyword5' => ['value'=>$from,'color'=>'#157efb'],
                        'remark'   => ['value'=>$info]
                    ]
                ];
    $json_template=json_encode($template);
    return $json_template;
}
//////////
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
    //printf("时间差:".$time_distance."<br>");
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
function postjson($url,$data){
    //$data  = json_encode($data);
    $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
    //return json_decode($output,true);
}/////单线程post

function  fsockopen_post($access_token,$formwork){
	
    $fp = fsockopen("ssl://api.weixin.qq.com", 443, $error, $errstr, 1);
	$http = "POST /cgi-bin/message/template/send?access_token={$access_token} HTTP/1.1\r\nHost: api.weixin.qq.com\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($formwork) . "\r\nConnection:close\r\n\r\n$formwork\r\n\r\n";
	fwrite($fp, $http);
	fclose($fp);
	
}///fsockpen方法post
function curl_multi_post($jt,$url)//多线程 post
//$jt 为post提交数据
{
    $p = 30;//每次执行多少条
    $ring = ceil(count($jt) / $p);//$urls

    for($n = 0; $n < $ring; $n++)
    {
        $temp_data = array();//$temp_url
        $star = $n * $p;
        $end = ($n+1) * $p;
        for($ii = $star; $ii < $end; $ii++)
        {
            if (isset($jt[$ii]))
        {
            $temp_data[] = $jt[$ii];
        }

        }

        $mh = curl_multi_init();
        foreach ($temp_data as $i => $value) {
            $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
            //$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
            $conn[$i] = curl_init($url);
            curl_setopt($conn[$i], CURLOPT_URL, $url);
            curl_setopt($conn[$i], CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($conn[$i], CURLOPT_SSL_VERIFYHOST,FALSE);
            curl_setopt($conn[$i], CURLOPT_POST, 1);
            curl_setopt($conn[$i], CURLOPT_POSTFIELDS, $value);
            curl_setopt($conn[$i],CURLOPT_HTTPHEADER,$headerArray);
            curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);

            curl_multi_add_handle($mh,$conn[$i]);
        }

        do{
            curl_multi_exec($mh, $active);
        } while ($active);

        $active = null;

        foreach ($temp_data as $i => $value) {
            $data[$n][$i] = curl_multi_getcontent($conn[$i]); // 获得爬取的代码字符串

        }

        foreach ($temp_data as $i => $value) {
            curl_multi_remove_handle($mh, $conn[$i]);
            curl_close($conn[$i]);
        }

        curl_multi_close($mh);
    }
    return $data;
}
?>
