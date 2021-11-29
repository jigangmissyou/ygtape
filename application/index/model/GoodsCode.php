<?php
namespace app\index\model;

use app\index\model\GoodsCode as ModelGoodsCode;
use Exception;
use think\Model;
use think\db;

class GoodsCode extends Model{

    /**
     * 获取列表
     */
    public function getList($page, $limit, $code = '', $date = ''){
        $where = [];
        if (!empty($date)) {
            $where = array_merge($where, ['ctime'=>$date]);
        //    return db('goods_code')->where(['ctime'=>$date])->limit($page-1, $page*$limit)->select()->toArray();
        }
        if (!empty($code)){
            $where = array_merge($where, ['code'=>$code]);
        } 
        if(!empty($where)) {
            return db('goods_code')->where($where)->limit(($page-1)*$limit, $limit)->select()->toArray();
        } else {
            return db('goods_code')->limit(($page-1)*$limit, $limit)->select()->toArray();
        }

    }

    /**
     * 获取总数量
     */
    public function count($code = '', $date = ''){
        $where = [];
        if (!empty($date)) {
            $where = array_merge($where, ['ctime'=>$date]);
        }
        if (!empty($code)){
            $where = array_merge($where, ['code'=>$code]);
        } 
        if (!empty($where)) {
            return db('goods_code')->where($where)->count();
        } else {
            return db('goods_code')->count();;
        }
    }
    
    /**
     * 获取单条数据
     */
    public function findData($id){
        return GoodsCode::get($id)->toArray();
    }

    /**
     * 根据code获取单条数据
     */
    public function findDataByCode($code){
        return db('goods_code')->where(['code'=>$code])->find();
    }

    /**
     * 保存数据
     */
    public function saveData($id, $code, $date, $uname){
        if (!empty($id)) {
            $goodsCode = GoodsCode::get($id);
            if (empty($goodsCode)) {
                throw Exception('没有查询到数据');
            }
            $ret = $this->findDataByCode($code);
            if (!empty($ret) && $ret['id'] != $id) {
                throw Exception('此库存编码已经存在了');
            }
            $logModel = new ActionLog();
            $oldArray = $newArray = $goodsCode->toArray();
            $newArray['code'] = $code;
            $logModel->insertLog($oldArray, $newArray, CHBM, $uname);
        } else {
            $ret = $this->findDataByCode($code);
            if (!empty($ret)) {
                throw Exception('此库存编码已经存在了');
            }
            $goodsCode = $this;
        }
        $goodsCode->code = $code;
        $goodsCode->uname = $uname;
        $goodsCode->ctime = $date;
        $goodsCode->save();
    }

    /**
     * 删除数据
     */
    public function delData($id)
    {
        $goodsCode = GoodsCode::get($id);
        if (empty($goodsCode)) {
            throw Exception('没有查询到数据');
        }
        $goodsCode->delete();    
    }

}
?>