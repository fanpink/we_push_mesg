<?php
header("Content-Type:text/html;charset=utf-8");
require_once './push_core.php';

$datakey= [];
$datavalue = [];
 $see = new push_core();
 $see->get_users_info($datakey,$datavalue);
echo update($datakey,$datavalue).PHP_EOL;
var_dump($datakey);
var_dump($datavalue);


    
function update($data_key,$data_value){
    $myfile = fopen('./temp/Usersinfo.txt', 'w+') or die("Unable to open file!");
    $Separator ="\t";///分隔符
    	 fwrite($myfile, implode(',',$data_key).$Separator.PHP_EOL);
    	foreach ($data_value as $X_value) {
    	    foreach ( $X_value as $Y_value){
    	       if(!is_array( $Y_value)){
    	           fwrite($myfile,$Y_value.$Separator);
    	       }else{
    	           $teeem = json_encode($Y_value);
    	           fwrite($myfile,$teeem.$Separator);
    	       }
    	    }
    	    fwrite($myfile,PHP_EOL );
    	}
    	fclose($myfile);
    	return "文件更新成功：".'<br>';
}

?>