<?php
header("Content-Type:text/html;charset=utf-8");
require_once './push_core.php';
//include "./config.php";

/////////////临时数据/////////////////
$data= [ "title"  => "标题",
    "time" =>"发震时间",
    "M" =>"震级",
    "local" =>"经纬度坐标",
    "deeth" =>"震源深度",
    "from"  =>"信息来源",
    "info"  =>"简介",
    "url" =>"http://www.baidu.com"
];

$title = $data['title'];//标题1111
    $time = $data['time'];//发震时间
    $M = $data['M'];//震级
    $local = $data['local'];//经纬度坐标
    $deeth = $data['deeth'];//震源深度
    $from = $data['from'];//信息来源
    $info = $data['info'];//简介
    $url = $data['url'];///链接地址
    
function json_msg($title,$time,$M,$local,$deeth,$from,$info){
    $template = [   'first' =>['value'=>$title,'color'=>'#333'],
                    'keyword1' => ['value'=>$time,'color'=>'#157efb'],
                    'keyword2' => ['value'=>$M,'color'=>'#157efb'],
                    'keyword3' => ['value'=>$local,'color'=>'#157efb'],
                    'keyword4' => ['value'=>$deeth,'color'=>'#157efb'],
                    'keyword5' => ['value'=>$from,'color'=>'#157efb'],
                    'remark'   => ['value'=>$info]
                ];
    $json_msg=json_encode($template,JSON_UNESCAPED_UNICODE );
 //echo $json_msg;
    return $json_msg;
}  
$send = new push_core();
$my_json_msg = json_msg($title,$time,$M,$local,$deeth,$from,$info);
//$access_token = $send->get_token();
//var_dump($access_token);
 $url = "www.baidu.com";
/////////////////////////
//////????????????????测试代码

    
    //////////////////////////////
    
    $openids = json_decode($send->get_openids(), true);//调用get_openids方法获取openids[],并解码
    $openid = $openids['openid'];//openID列表(数组)
$mypush_test = $send->push_tem($openid,$my_json_msg,$url);
//var_dump( $mypush_test);
echo $send->send_statistics($mypush_test);
/////////////////////////////////////////

// function push_tem($openid_list,$json_msgtem,$url)
// {   
//     $se = new push_core();
//   $posturl = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$se->get_token();
//     foreach ($openid_list as $id) {
//         $post_tempalte = $se->post_tempalte($id,$json_msgtem,$url );
// 		$jt[]=$post_tempalte;
//     }
//     //var_dump(	$jt);
//     $result_temp = $se->curl_multi_post($jt,$posturl);
//   return $result_temp;
// }



?>