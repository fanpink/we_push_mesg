<?php
/*
QQQQQQQQQQQQQ-------------官方批量获取方法--------------QQQQQQQQQQQQQQQQ
*/
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
//$token = get_server_token();
//echo $token;
$data= [];
get_users_info($data);
$table = new xtable();
$titles =["No","subscribe","openid","nickname","sex","language","city","province","中国 ","subscribe_time","remark","groupid","tagid_list","subscribe_scene","qr_scene","qr_scene_str"];
$table->titles( $titles);
$table->background(array("pink","gold"));
for($i=0;$i<count($data);$i++){
	$table->addrow(array($no=$i+1,$subscribe=$data[$i]["subscribe"],$openid=$data[$i]["openid"],$nickname=$data[$i]["nickname"],$sex=$data[$i]["sex"],$language=$data[$i]["language"],$city=$data[$i]["city"],$province=$data[$i]["province"],$country =$data[$i]["country"],$subscribe_time=$data[$i]["subscribe_time"],$remark=$data[$i]["remark"],$groupid=$data[$i]["groupid"],$tagid_list=json_encode($data[$i]["tagid_list"],JSON_UNESCAPED_UNICODE),$subscribe_scene=$data[$i]["subscribe_scene"],$qr_scene=$data[$i]["qr_scene"],$qr_scene_str=$data[$i]["qr_scene_str"]));
}
echo $table->html()."<hr />";
//print_r(count($data));


function get_users_info(array &$data1){
$openids = json_decode(get_openids(), true);//调用get_openids方法获取openids[],并解码
$openid = $openids['openid'];//openID列表(数组)
$access_token = get_token();
//printf($access_token."<br>");
//print_r($openid);
//////////////////////////////////////////////
//$data1= [];
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
	if($i<count($openid)/$step-1){$jstep = $step;}else{$jstep =(count($openid)%$step);}
	for($j=0;$j<$jstep;$j++){
	        		$json[]=
	                    [
	                        "openid" => $openid[$i*$step+$j],
	                        "lang"  => "zh_CN"
	                    ];
	    }
	$idjson = ["user_list" =>$json];
	$idjsons = json_encode($idjson);
	$url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=".$access_token;
	$userinfo = postjson($url,$idjsons);
	$datatemp = json_decode($userinfo,true);
	$datatmp[] =$datatemp["user_info_list"];
	$data1 = array_merge($data1,$datatmp[$i]);
	}
	$myfile = fopen('Usersinfo.txt', 'w+') or die("Unable to open file!");
	$tit="No"."\t"."subscribe"."\t"."openid"."\t"."nickname"."\t"."sex"."\t"."language"."\t"."city"."\t"."province"."\t"."中国 "."\t"."subscribe_time"."\t"."remark"."\t"."groupid"."\t"."tagid_list"."\t"."subscribe_scene"."\t"."qr_scene"."\t"."qr_scene_str"."\t"."\n";
	fwrite($myfile, $tit);
	for($i=0;$i<count($data1);$i++){
	$hang = ($i+1)."\t".$subscribe=$data1[$i]["subscribe"]."\t".$openid=$data1[$i]["openid"]."\t".$nickname=$data1[$i]["nickname"]."\t".$sex=$data1[$i]["sex"]."\t".$language=$data1[$i]["language"]."\t".$city=$data1[$i]["city"]."\t".$province=$data1[$i]["province"]."\t".$country =$data1[$i]["country"]."\t".$subscribe_time=$data1[$i]["subscribe_time"]."\t".$remark=$data1[$i]["remark"]."\t".$groupid=$data1[$i]["groupid"]."\t".$tagid_list=json_encode($data1[$i]["tagid_list"]."\t".JSON_UNESCAPED_UNICODE)."\t".$subscribe_scene=$data1[$i]["subscribe_scene"]."\t".$qr_scene=$data1[$i]["qr_scene"]."\t".$qr_scene_str=$data1[$i]["qr_scene_str"]."\t"."\n";
	fwrite($myfile, $hang);
}
    fclose($myfile);
}
////////////////////////////////////////////////////
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

class xtable
{
 private $tit,$arr,$fons,$sextra;
 public function __construct()
 {
  $this->tit=array();       // strings with titles for first row
  $this->arr=array();       // data to show on cells
  $this->fons=array("#EEEEEE","#CCEEEE");  // background colors for odd and even rows
  $this->sextra="";       // extra html code for table tag
 }

 public function extra($s)      // add some html code for the tag table
 {
  $this->sextra=$s;
 }
 public function background($arr) {if (is_array($arr)) $this->fons=$arr; else $this->fons=array($arr,$arr);}
 public function titles($text,$style="") {$this->tit=$text; $this->sesttit=$style;}
 public function addrow($a) {$this->arr[]=$a;}
 public function addrows($arr) {$n=count($arr); for($i=0;$i<$n;$i++) $this->addrow($arr[$i]);}
 public function html()
 {
  $cfondos=$this->fons;
  $titulos="<tr>";
  $t=count($this->tit);
  for($k=0;$k<$t;$k++)
  {
   $titulos.=sprintf("<th>%s</th>",$this->tit[$k]);
  }
  $titulos.="</tr>";

  $celdas="";
  $n=count($this->arr);
  for($i=0;$i<$n;$i++)
  {
   $celdas.=sprintf("<tr style='background-color:%s'>",$this->fons[$i%2]);
   $linea=$this->arr[$i];
   $m=count($linea);
   for($j=0;$j<$m;$j++)
    $celdas.=sprintf("<td  %s>%s</td>","",$linea[$j]);
   $celdas.="</tr>";
  }
  return sprintf("<table cellpadding='0' cellspacing='0' border='1' %s>%s%s</table>",$this->sextra,$titulos,$celdas);
 }
 public function example()
 {
  $tit=array("Apellidos","Nombre","Telefono");
  $r1=array("Garcia","Ivan","888");
  $r2=array("Marco","Alfonso","555");
  $x=new xtable();
  $x->titles($tit);      //take titles array
  $x->addrows(array($r1,$r2));   // take all rows at same time
  return $x->html();     //return html code to get/show/save it
 }
}
?>
