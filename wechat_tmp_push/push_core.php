<?php
 include "./config.php";
class push_core{
   
    
    /*
    获取openid数据
    数据以json为数据交换格式
    返回格式如下：
    example：
    {
    "get_time":"2021-11-01 12:50:13",    ///更新时间
    "total_number":2,                       ////openid个数
    "openid":["o6R6Y6OPcHvOyA63IadSpe28Rx4I","o6R6Y6AoSyrh8jqpEFVDDdrtGOgk"]    //openid 存储数组
    }
    */
    //优先从本地获取openID 列表，如果本地数据过期（默认过期时间10小时），再从服务器获取 openID 列表（调用get_server_openid()），返回 （获取时间，关注总人数，openID列表），数据需要 json_decode后使用
    public function get_openids(){//返回 （获取时间，关注总人数，openID列表），数据需要 json_decode后使用
        if (!file_exists('./temp/openids.json')) {
            return $this->get_server_openid();
        }
        $myfile = fopen('./temp/openids.json', 'r') or die('Unable to open file!');
        $data = fread($myfile,filesize('./temp/openids.json'));
        fclose($myfile);
        $openids = json_decode($data,true);
        date_default_timezone_set("PRC");
        $time = date('Y-m-d h:i:s');
        /////时间差计算////
        $get_time = $openids['get_time'];
        $time_distance =strtotime($time)-strtotime($get_time);
        if($time_distance<36000){//超过10个小时过期更新
            return $data;
        }else{
            return $this->get_server_openid();
        }
    }
    /*获取openid数据
    数据以json为数据交换格式
    返回格式如下：
    example：
    {
    "get_time":"2021-11-01 12:50:13",    ///更新时间
    "total_number":2,                       ////openid个数
    "openid":["o6R6Y6OPcHvOyA63IadSpe28Rx4I","o6R6Y6AoSyrh8jqpEFVDDdrtGOgk"]    //openid 存储数组
    }
    */
    //从服务器获取 openID 列表，返回 （获取时间，关注总人数，openID列表），数据需要 json_decode后使用
    public function get_server_openid(){/*///??非常重要，当关注人数超过一万以后需重写此函数???/////*/
        header('Content-Type: text/html; charset=utf-8');
        $access_token = $this->get_token();//获取access_token
        ////??非常重要，当关注人数超过一万以后需重写此函数???//////
        $get_openid_url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$access_token.'&next_openid=';
    
        $userinfo = json_decode($this->get_curl($get_openid_url),true);
        date_default_timezone_set("PRC");
        $openids=json_encode(['get_time'=> date('Y-m-d h:i:s'),'total_number'=>$userinfo['count'],'openid'=>$userinfo['data']['openid']]);
        ///文件读写
        $myfile = fopen('./temp/openids.json', 'w+') ;
        fwrite($myfile, $openids);
        fclose($myfile);
        return $openids;
    }
    
    /*/*获access_token数据
        数据以json为数据交换格式
        返回格式如下：
        example：
        {
        "get_time":"2021-11-01 08:48:04",
        get_token:"50_vKBiIet8eJdflQKDv2enXhFP6lmB-Z5IBE35ZM9eN3jYzKaNZZ-_inT15ag9pev0-1tH3Muo2vsNJ2VMpK8dCpwE2DcXwU0qUQ0aCevdLq5h1TTxWD54DQ_6Omqa9yE2efZUqfJ5FybW_8NPFXQeADAUMF"
        }
    */
    //优先从本地获取access_token列表，如果本地数据过期（官方默认过期时间2小时），再从服务器获取 access_token（调用get_server_token()），返回 （更新时间，access_token），数据需要 json_decode后使用
    public function get_token(){
        if (!file_exists('./temp/access_token.json')) {
            return $this-> get_server_token();
        }
        $myfile = fopen('./temp/access_token.json', 'r') or die('Unable to open file!');
        $data = fread($myfile,filesize('./temp/access_token.json'));
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
        if($time_distance<7000){//官方有效时间2小时（7200S）
            return $totkens['access_token'];
        }else{
            return $this->get_server_token();
        }
    }
    /*/*获access_token数据
        数据以json为数据交换格式
        返回格式如下：
        example：
        {
        "get_time":"2021-11-01 08:48:04",
        "access_token":"50_vKBiIet8eJdflQKDv2enXhFP6lmB-Z5IBE35ZM9eN3jYzKaNZZ-_inT15ag9pev0-1tH3Muo2vsNJ2VMpK8dCpwE2DcXwU0qUQ0aCevdLq5h1TTxWD54DQ_6Omqa9yE2efZUqfJ5FybW_8NPFXQeADAUMF"
        }
    */
    //从服务器获取 access_token（调用get_server_token()），返回 （更新时间，access_token），数据需要 json_decode后使用
    public function get_server_token(){
        $grant_type ='client_credential';
        $get_token_url='https://api.weixin.qq.com/cgi-bin/token?grant_type='.$grant_type.'&appid='.APPID.'&secret='.SECRET;
        $access_token = $this->get_curl($get_token_url);
        $access_token = json_decode($access_token,true);
        //print_r($access_token);
    
        $token = $access_token['access_token'];
        date_default_timezone_set("PRC");
        $time = date('Y-m-d h:i:s');
    
        $tokens = json_encode(['get_time'=>$time,'access_token'=>$token]);
        $myfile = fopen('./temp/access_token.json', 'w+') ;
        fwrite($myfile, $tokens);
        fclose($myfile);
        //echo "token server获取：";
        //print_r($totkens);
        return $token;
    }
    
    /*
        get请求方法
        get访问http地址url，返回结果
    */
    public function get_curl($url){///get访问http地址，返回参数
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
    
    /*
        post请求方法
        post访问http地址url，返回结果
        $url ：请求地址
        $data ：请求数据包
        返回结果为  json格式
    */
    public function postjson($url,$data){
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
  
    /*
        多线程执行post方法
        参数：
            $jt post发送模板数组,形如：
            array(2)（
                [0]=>  "{"touser":"o6R6Y6AoSyrh8jqpEFVsfsrtGOgk","template_id":"tjI4WVEMCpVPXSSksfsfYT8XAwyjR_gx_Wdv3mrDVvc", "url":"www.baidu.com","data":"first":{"value":"标题","color":"#333"},"keyword1":{"value":"发震时间","color":"#157efb"},"keyword2":{ "value":"震级","color":"#157efb"},"keyword3":{ "value":"经纬度坐标", "color":"#157efb"},"keyword4":{ "value":"震源深度", "color":"#157efb"},"keyword5":{ "value":"信息来源","color":"#157efb"},"remark":{"value":"简介" } }}" ,
                [1]=>  "{"touser":"o6R6Y6AoSyrh8jqpEFVDDdrtGOgk","template_id":"tjI4WVEMCpVPXSSkM62fYT8XAwyjR_gx_Wdv3mrDVvc", "url":"www.baidu.com","data":"first":{"value":"标题","color":"#333"},"keyword1":{"value":"发震时间","color":"#157efb"},"keyword2":{ "value":"震级","color":"#157efb"},"keyword3":{ "value":"经纬度坐标", "color":"#157efb"},"keyword4":{ "value":"震源深度", "color":"#157efb"},"keyword5":{ "value":"信息来源","color":"#157efb"},"remark":{"value":"简介" } }}" 
                ）
            $url 请求地址
        返回结果为数组：
        Array ( [0] => 
            Array ( 
                    [0] => {"errcode":0,"errmsg":"ok","msgid":2116434384290414596} 
                    [1] => {"errcode":0,"errmsg":"ok","msgid":2116434383837380616}
                ) 
            )
    */
    public function curl_multi_post($jt,$url){//多线程 post
        //$jt 为post提交数据
        //var_dump($jt);
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
                $data[$n][$i] = curl_multi_getcontent($conn[$i]); // 获得返回结果
    
            }
    
            foreach ($temp_data as $i => $value) {
                curl_multi_remove_handle($mh, $conn[$i]);
                curl_close($conn[$i]);
            }
    
            curl_multi_close($mh);
        }
        return $data;
    }
    
    /*
        // 统计发送情况
        // 参数$return为curl_multi_post($jt,$url)返回结果，
        // 形如：
        // array(  
        //     [0]=> "{"errcode":0,"errmsg":"ok","msgid":2117129114829553665}" 
        //     [1]=>  "{"errcode":0,"errmsg":"ok","msgid":2117129114091307008}" 
        //     ）
        // 返回统计情况
    */
    public function send_statistics($return) {
        //var_dump($return);
        $total_number = 0;
        $fail = 0;
        foreach($return as $x =>$y)
        {
        	foreach ($return[$x] as $z =>$c)
        	{
        		//echo "<br>".$c;
        		$total_number++;
        		$data_temp = json_decode($c,true);
        		$errcode = $data_temp["errcode"];
        		if($errcode)
        		{
        			$fail++;
        			$errmsg_temp = $data_temp["errmsg"];
        			$errmsg=$data_temp["errmsg"]."&|||&";
        		}
    	}
    }
    if($fail){
    	$re = "发送失败人数".$fail."。"."发送总人数：" . $total_number ."。\n"."错误代码：".$errmsg;
    }else{
		$re ="发送失败人数".$fail."。"."发送总人数：". $total_number ."。\n";}
    return $re;
    }
    
    /*
    // 生成请求模板
    // $openid,  用户openid数组
    // $json_msg,模板信息的json数据包
    // $url  //点击模板消息会跳转的链接
    */
    public function post_tempalte($openid,$json_msg,$url){
        $data=json_decode($json_msg);
        $template = [
                    'touser'=>$openid,  //用户openid
                    'template_id'=>TEMPLATE_ID, //模板ID
                    'url'=>urlencode($url), //点击模板消息会跳转的链接
                    'data'=> $data,
                    ];
        $json_template=json_encode($template);
        return$json_template;
    }
    
    
    /*
        //******** 批量发送模板信息**************
    //$openid_list,  用户openid数组
    // $json_msg,模板信息的json数据包
    // $url  //点击模板消息会跳转的链接
    */
    public function push_tem($openid_list,$json_msgtem,$url)
    {   
        foreach ($openid_list as $id) {
            $post_tempalte =$this->post_tempalte($id,$json_msgtem,$url );
    		$jt[]=$post_tempalte;
        }
        $result_temp = $this->curl_multi_post($jt,"https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->get_token());
       return $result_temp;
    }
    
    /*
    $datakey= [];
    $datavalue= [];
    参数
    &$data_key,健值
    &$data_value， 值
    调用方式
    $datakey= [];
    $datavalue= [];
    get_users_info($datakey,$datavalue);
    */
    function get_users_info(array &$data_key,array &$data_value){
    $id =$this->get_openids();
    $openids = json_decode($id, true);//调用get_openids方法获取openids[],并解码
    $openid = $openids['openid'];//openID列表(数组)
    $access_token = $this->get_token();
    //printf($access_token."<br>");
    //print_r($openid);
    //////////////////////////////////////////////
    $data1= [];
    $data = ["user_info_list"=>[]];
    $userinfo = "";
    $step = 90;//一次提交数
    //print_r( $openid);
    $n = Ceil(count($openid)/$step);
    //echo $n."<br>";
    for($i = 0;$i<$n;$i++){
    	$json=null;
    	$idjson=null;
    	$jstep = null;
    	if($i<count($openid)/$step-1)
    	{
    	    $jstep = $step;
    	    
    	}else{
    	    $jstep =(count($openid)%$step);
    	}
    	for($j=0;$j<$jstep;$j++){
    	        		$json[]=
    	                    [
    	                        "openid" => $openid[$i*$step+$j]
    	                    ];
    	    }
    	$idjson = ["user_list" =>$json];
    	$idjsons = json_encode($idjson);
    	$url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=".$access_token;
    	$userinfo = $this->postjson($url,$idjsons);
    	$datatemp = json_decode($userinfo,true);
    	$datatmp[] =$datatemp["user_info_list"];
    	$data1 = array_merge($data1,$datatmp[$i]);
    	}
    	$data_key = array_keys($data1[0]);
    	$data_value = array_values($data1);
    }
}
?>