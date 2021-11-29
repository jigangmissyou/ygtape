<?php
namespace app\index\model;
use think\Model;

class HongYan extends Model{

    // const MAP_HONGYAN = [
    //     'code' => '编码',
    //     'item_name' => '名称',
    //     'model_no' => '规格型号',
    //     'unit' => '单位',
    //     'price_with_tax' => '含税价格',
    //     'currency_type' => '币种',
    //     'local_currency' => '本币价格',
    //     'price_without_tax' => '不含税价格'
    // ];
    
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
    public function getList($page, $limit, $code = '', $itemName = '', $beginDate = '', $endDate = ''){
        $where = [];
        if (!empty($code)){
            $where = array_merge($where, ['code'=>$code]);
        } 
        if (!empty($itemName)) {
            $where = array_merge($where, ['item_name'=>$itemName]);
        }
        if (!empty($beginDate) && !empty($endDate)) {
            return db('hong_yan')->where($where)->whereBetween('ctime', $beginDate .','.$endDate)->limit($page-1, $page*$limit)->order(['id'=> 'desc'])->select()->toArray();
        }elseif(!empty($beginDate)) {
            $endDate = time();
            return db('hong_yan')->where($where)->whereBetween('ctime', $beginDate .','.$endDate)->limit($page-1, $page*$limit)->order(['id'=> 'desc'])->select()->toArray();

        }elseif(!empty($endDate)) {
            return db('hong_yan')->where($where)->where('ctime','LT',$endDate)->limit($page-1, $page*$limit)->order(['id'=> 'desc'])->select()->toArray();

        }
        return db('hong_yan')->where($where)->limit($page-1, $page*$limit)->order(['id'=> 'desc'])->select()->toArray();
    }

    // /**
    //  * 获取总数量
    //  */
    // public function count($code = ''){
    //     if(!empty($code)) {
    //         return db('hong_yan')->where(['code'=>$code])->count();
    //     }else{
    //         return db('hong_yan')->count();;
    //     }
    // }

    /**
     * 获取总数量
     */
    public function count($code = '', $itemName = '', $beginDate = '', $endDate = ''){
        $where = [];
        if (!empty($code)){
            $where = array_merge($where, ['code'=>$code]);
        } 
        if (!empty($itemName)) {
            $where = array_merge($where, ['item_name'=>$itemName]);
        }
        if (!empty($beginDate) && !empty($endDate)) {
            $beginDate = strtotime($beginDate);
            $endDate = strtotime($endDate);
            return db('hong_yan')->where($where)->whereBetween('ctime', $beginDate .','.$endDate)->count();
        }elseif(!empty($beginDate)) {
            $beginDate = strtotime($beginDate);
            $endDate = time();
            return db('hong_yan')->where($where)->whereBetween('ctime', $beginDate .','.$endDate)->count();
        }elseif(!empty($endDate)) {
            $endDate = strtotime($endDate);
            return db('hong_yan')->where($where)->where('ctime','LT',$endDate)->count();

        }
        return db('hong_yan')->where($where)->count();
    }

    /**
     * 保存数据
     */
    public function saveData($param, $uname){
        if (!empty($param['id'])) {
            $hongYan = HongYan::get($param['id']);
            if (empty($hongYan)) {
                throw Exception('没有查询到数据');
            } 
            $logModel = new ActionLog();
            $oldArray = $newArray = $hongYan->toArray();
            foreach($param as $key => $val) {
                $newArray[$key] = $val;
            }
            $logModel->insertLog($oldArray, $newArray, HYJG, $uname);    
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
        $hongYan->uname = $uname;
        if (empty($param['id'])) {
            $hongYan->ctime = time();
        }
        $hongYan->mtime = time();
        $hongYan->save();
    }
}
?>