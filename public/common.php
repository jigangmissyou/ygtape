<?php
// [ 应用入口文件 ]
include_once("/inc/auth.inc.php");
include_once("/inc/utility_all.php");
include_once("/inc/utility_org.php");
include_once("/inc/utility_file.php");
include_once("/inc/check_type.php");
include_once 'inc/utility_sms1.php';


function send_msg($params){
    
    echo var_dump($params);exit;
    
    $CONTENT = "你的订单被退回!";
    $REMIND_URL = "1:erp4/view/orderNum/";
    $TO_ID = "lq";
    send_sms(date('Y-m-d H:i:s'),$_SESSION['LOGIN_USER_ID'],$TO_ID,7,$CONTENT,$REMIND_URL);
}
function send_msg1($params){
    echo "777";
}
$params=array_merge($_POST,$_GET);
call_user_func($opt,$params);//函数调用方法映射
