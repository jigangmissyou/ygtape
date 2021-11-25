<?php
namespace app\index\controller;
use app\common\controller\Common;
use app\index\model\GoodsCode;
use app\index\model\Bom;
use app\index\model\HongYan;
use Exception;

class Index extends Common{

    private $goodsCode;
    private $bom;
    private $hongYan;

    public function __construct()
    {
        parent::__construct();
        $this->goodsCode = new GoodsCode();
        $this->bom = new Bom();
        $this->hongYan = new HongYan();
    }
    
    /**
     * 存货编码页面
     */
    public function index(){
        return $this->fetch('goodsCode/index');
    }

    /**
     * 存货编码列表
     */
    public function ajaxGoodsCodeList(){
        // var_dump($_GET);
        // die;
        $page = input('get.page', 1);
        $limit = input('get.limit', 10);
        $code = input('get.key.code', '');
        $date = input('get.key.date', '');
        $goodsCode = $this->goodsCode->getList($page, $limit, $code, $date);
        $count = $this->goodsCode->count($code, $date);
        return $this->_success($goodsCode, $count);
    }

    /*
     * 添加存货编码
     */
    public function addGoodsCode()
    {
        if (request()->isGet()){
            return $this->fetch('goodsCode/add');
        } elseif (request()->isPost()){
            try{
                $code = input('post.code');
                if(empty($code)) throw Exception('参数不正确');
                $date = date('Y-m-d', time());
                $this->goodsCode->saveData('', $code, $date);
                return $this->_success();      
            }catch(Exception $e){
                return $this->_error($e->getMessage());
            }
        } else {
            return $this->_error('不支持其他的请求方式');
        }
    }

    /*
     * 编辑存货编码
     */
    public function editGoodsCode()
    {
        if (request()->isGet()){
            $id = $_GET['id'];
            $result = $this->goodsCode->findData($id);
            $this->assign('detail', $result);
            return $this->fetch('goodsCode/edit');
        } elseif (request()->isPost()){
            try{
                $id = input('post.id');
                $code = input('post.code');
                if(empty($id) || empty($code)) throw Exception('参数不正确');
                $date = date('Y-m-d', time());
                $this->goodsCode->saveData($id, $code, $date);
                return $this->_success();      
            }catch(Exception $e){
                return $this->_error($e->getMessage());
            }
        } else {
            return $this->_error('不支持其他的请求方式');
        }
    }

    /**
     * 删除存货编码
     */
    public function delGoodsCode()
    {
        try{
            $id = input('post.id');
            if(empty($id)) throw Exception('参数不正确');
            $this->goodsCode->delData($id);
            return $this->_success();      
            // $json = array("status" => 200, "msg" => '成功', "data" => []);            
        }catch(Exception $e){
            return $this->_error($e->getMessage());
        }
    }

    /**
     * 获取bom页面
     */
    public function bomIndex(){
        return $this->fetch('index');
    }

    /**
     * Bom价格列表
     */
    public function ajaxBomList(){
        $page = input('get.page');
        $limit = input('get.limit');
        $code = input('get.code');
        $ddate = date('Y-m-d', strtotime("- 18 month"));
        $dend = date('Y-m-d');
        $result = $this->bom->getList($ddate, $dend, $code);
        $count = count($result);
        $start = ($page-1)*$limit;
        $result = array_slice($result, $start, $limit);
        return $this->_success($result, $count);    
    }

    /*
     * 添加洪研价格
     */
    public function addHongYan()
    {
        if (request()->isGet()){
            return $this->fetch('hongYan/add');
        } elseif (request()->isPost()){
            $param = [];
            $param['code'] = input('post.code');
            $param['item_name'] = iconv('UTF-8', 'GBK', input('post.item_name'));
            $param['model_no'] = input('post.model_no');
            $param['unit'] = iconv('UTF-8', 'GBK', input('post.unit'));
            $param['price_with_tax'] = input('post.price_with_tax');
            $param['currency_type'] = iconv('UTF-8', 'GBK', input('post.currency_type'));
            $param['local_currency'] = input('post.local_currency');
            $param['price_without_tax'] = input('post.price_without_tax');
            $this->hongYan->saveData($param);
            return $this->_success();  
        }
    }

    /*
     * 编辑洪研价格
     */
    public function editHongYan(){
        if (request()->isGet()){
            $id = $_GET['id'];
            $result = $this->hongYan->findData($id);
            $result['item_name'] = iconv('GBK','UTF-8', $result['item_name']);
            $result['currency_type'] = iconv('GBK','UTF-8', $result['currency_type']);
            $this->assign('detail', $result);
            return $this->fetch('hongyan/edit');
        } elseif (request()->isPost()) {
            try{
                $param = [];
                $param['id'] = input('post.id');
                $param['code'] = input('post.code');
                $param['item_name'] = iconv('UTF-8', 'GBK', input('post.item_name'));
                $param['model_no'] = input('post.model_no');
                $param['unit'] = iconv('UTF-8', 'GBK', input('post.unit'));
                $param['price_with_tax'] = input('post.price_with_tax');
                $param['currency_type'] = iconv('UTF-8', 'GBK', input('post.currency_type'));
                $param['local_currency'] = input('post.local_currency');
                $param['price_without_tax'] = input('price_without_tax');
                $this->hongYan->saveData($param);
                return $this->_success();  
            }catch(Exception $e){
                return $this->_error($e->getMessage());
            }
        } else {
            return $this->_error('不支持其他的请求方式');
        }
        
    }

    /**
     * 洪研页面列表
     */
    public function hongYanIndex(){
        return $this->fetch('hongYan/index');
    }

    /**
     * 洪研价格列表
     */
    public function ajaxHongYanList(){
        $page = input('get.page', 1);
        $limit = input('get.limit', 10);
        $result = $this->hongYan->getList($page, $limit);
        foreach($result as &$item){
            $item['item_name'] = iconv('GBK','UTF-8', $item['item_name']);
            $item['currency_type'] = iconv('GBK','UTF-8', $item['currency_type']);
            $item['ctime'] = date('Y-m-d H:i:s', $item['ctime']);
        }
        $count = $this->hongYan->count();
        return $this->_success($result, $count);  
    }

    /**
     * 下载bom数据
     */
    public function exportData(){
        $code = input('get.code');
        $ddate = date('Y-m-d', strtotime("- 18 month"));
        $dend = date('Y-m-d');
        return $this->bom->getList($ddate, $dend, $code, 1);
    }

}
?>