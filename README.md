# 微信模板信息群发实现
# we_push_mesg
## 文件目录结构
|-->temp/

    |-->Usersinfo.txt       
    |-->access_token.json       
    |-->openids.json        

|-->Usersinfo.php       
|-->config.php      
|-->example.html.html      
|-->msgsend.php     
|-->push_core.php       
|-->server_check.php        
|-->update_openids.php      
|-->update_token.php        
|-->update_users.php        
## 微信模板信息群发

核心文件
push_core.php   包含和新方法
## get_openids(){//返回 （获取时间，关注总人数，openID列表），数据需要 json_decode后使用
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
    get_openids(){//返回 （获取时间，关注总人数，openID列表），数据需要 json_decode后使用
## get_server_openid(){/*///??非常重要，当关注人数超过一万以后需重写此函数???/////*/
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
## get_token()
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


## get_server_token(){
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

## curl_multi_post($jt,$url)
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
    
  ## send_statistics($return) 
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
  ## post_tempalte($openid,$json_msg,$url)
/*
    // 生成请求模板
    // $openid,  用户openid数组
    // $json_msg,模板信息的json数据包
    // $url  //点击模板消息会跳转的链接
    */

## push_tem($openid_list,$json_msgtem,$url)
//******** 批量发送模板信息**************
    //$openid_list,  用户openid数组
    // $json_msg,模板信息的json数据包
    // $url  //点击模板消息会跳转的链接
    */
    
 ## get_users_info(array &$data_key,array &$data_value)
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


