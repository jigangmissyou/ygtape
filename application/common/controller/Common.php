<?php
namespace app\common\controller;
use think\Controller;
class Common extends Controller{
    public function __construct()
    {
        parent::__construct();
    }

    public function _success($data = [], $count = ''){
        if ($count === '') {
            $response = array("status" => 200, "msg" => "请求成功", "data" => $data);
        } else {
            $response = array("status" => 200, "msg" => "请求成功","count"=>$count, "data" => $data);
        }
        return $this->jsonApi($response);
    }

    public function _error($msg){
        $response = array("status" => 500, "msg" => $msg, "data" => []);
        return $this->jsonApi($response);
    }

    public function _request($key, $default = '', $isUtf8 = 0){
        if (!$isUtf8) {
            return iconv('UTF-8', 'GBK', input($key, $default));
        }
        return input($key, $default);
    }

    public function _convertGbk2Utf8($value){
            return iconv('GBK', 'UTF-8', $value);
    }

    private function jsonApi($data){
        return json($data,200,array(
            "Access-Control-Allow-Origin"=>" *",
            "Access-Control-Allow-Headers"=>" Origin, X-Requested-With, Content-Type, Accept",
            "Access-Control-Allow-Methods"=>" GET, POST, PUT",
            /*"Content-Type:application/json"=>" charset=utf-8"*/
        ));
    }


}
?>