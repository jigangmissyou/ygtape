<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
const CHBM = 1;
const HYJG = 2;

/**
 * 返回跨域JSON数据
 * @param type $result
 */
function json_api($data){
    return json($data,200,array(
        "Access-Control-Allow-Origin"=>" *",
        "Access-Control-Allow-Headers"=>" Origin, X-Requested-With, Content-Type, Accept",
        "Access-Control-Allow-Methods"=>" GET, POST, PUT",
        /*"Content-Type:application/json"=>" charset=utf-8"*/
    ));
}