<?php
namespace app\index\model;
use think\Model;

class HongYan extends Model{
    
    /**
     * 获取单条数据
     */
    public function findData($id){
        return HongYan::get($id)->toArray();
    }

    /**
     * 根据code获取最新的单条数据
     */
    public function findByCode($code){
        return db('hong_yan')->where(['code'=>$code])->order(['ctime'=> 'desc'])->find();
    }

    /**
     * 获取列表
     */
    public function getList($page, $limit, $code = ''){
        if(!empty($code)) {
           return db('hong_yan')->where(['code'=>$code])->limit($page-1, $page*$limit)->order(['ctime'=> 'desc'])->select()->toArray();
        }else{
            return db('hong_yan')->limit(($page-1)*$limit, $limit)->select()->toArray();
        }
    }

    /**
     * 获取总数量
     */
    public function count($code = ''){
        if(!empty($code)) {
            return db('hong_yan')->where(['code'=>$code])->count();
        }else{
            return db('hong_yan')->count();;
        }
    }

    /**
     * 保存数据
     */
    public function saveData($param){
        if (!empty($param['id'])) {
            $hongYan = HongYan::get($param['id']);
            if (empty($hongYan)) {
                throw Exception('没有查询到数据');
            }   
        } else {
            $hongYan = $this;
        }
        $hongYan->code = $param['code'];
        $hongYan->item_name = $param['item_name'];
        $hongYan->model_no = $param['model_no'];
        $hongYan->unit = $param['unit'];
        $hongYan->price_with_tax = $param['price_with_tax'];
        $hongYan->currency_type = $param['currency_type'];
        $hongYan->local_currency = $param['local_currency'];
        $hongYan->price_without_tax = $param['price_without_tax'];
        $hongYan->ctime = time();
        $hongYan->save();
    }

}
?>