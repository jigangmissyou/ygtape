<?php
namespace app\index\controller;
use app\common\controller\Common;
use app\index\model\ActionLog;
use app\index\model\GoodsCode;
use app\index\model\Bom;
use app\index\model\HongYan;
use app\index\model\Inventory;
use Exception;

class Index extends Common{

    private $goodsCode;
    private $bom;
    private $hongYan;
    private $actionLog;
    private $inventory;

    public function __construct()
    {
        parent::__construct();
        $this->goodsCode = new GoodsCode();
        $this->bom = new Bom();
        $this->hongYan = new HongYan();
        $this->actionLog = new ActionLog();
        $this->inventory = new Inventory();
    }
    
    /**
     * 存货编码页面
     */
    public function index(){
        $mapValue = config('map_log');
        return $this->fetch('goodsCode/index');
    }

    /**
     * 存货编码列表
     */
    public function ajaxGoodsCodeList(){
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
                $uname = $_SESSION["LOGIN_BYNAME"];
                $this->goodsCode->saveData('', $code, $date, $uname);
                return $this->_success();      
            }catch(Exception $e){
                return $this->_error($e->getMessage());
            }
        } else {
            return $this->_error('不支持其他的请求方式');
        }
    }

    /**
     * Log记录列表
     */
    public function logList(){
        $ticketNo = input('get.ticket_no');
        $actionType = input('get.action_type');
        $result = $this->actionLog->getList($actionType, $ticketNo);
        if (!empty($result)) {
            $mapLog = config('map_log');
            foreach ($result as &$item) {
                if (array_key_exists($item['action_type'], $mapLog)) {
                    $mapData = $mapLog[$item['action_type']];
                    if (array_key_exists($item['action_name'], $mapData)) {
                        $item['action_name'] = $mapData[$item['action_name']];
                    }
                }
                $item['from_data'] = iconv('GBK','UTF-8', $item['from_data']);
                $item['to_data'] = iconv('GBK','UTF-8', $item['to_data']);
                $item['uname'] = iconv('GBK','UTF-8', $item['uname']);
                $item['ctime'] = date('Y-m-d H:i:s', $item['ctime']);
                $item['mtime'] = date('Y-m-d H:i:s', $item['mtime']);
            }
        }
        $uname = $time = '无';
        // 变更人
        if (!empty($result)) {
            $uname = $result[0]['uname'];
            $time = $result[0]['mtime'];
        }
        // 或者批量赋值
        $this->assign([
            'uname'  => $uname,
            'time' => $time,
            'list' => $result,
        ]);
        return $this->fetch('log/index');
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
                $uname = $_SESSION["LOGIN_BYNAME"];
                $this->goodsCode->saveData($id, $code, $date, $uname);
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
            $param['model_no'] = iconv('UTF-8', 'GBK',input('post.model_no'));
            $param['unit'] = iconv('UTF-8', 'GBK', input('post.unit'));
            $param['price_with_tax'] = input('post.price_with_tax');
            $param['currency_type'] = iconv('UTF-8', 'GBK', input('post.currency_type'));
            $param['local_currency'] = input('post.local_currency');
            $param['price_without_tax'] = input('post.price_without_tax');
            $uname = $_SESSION["LOGIN_BYNAME"];
            $this->hongYan->saveData($param, $uname);
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
            $result['model_no'] = iconv('GBK','UTF-8', $result['model_no']);
            $result['unit'] = iconv('GBK','UTF-8', $result['unit']);
            $result['currency_type'] = iconv('GBK','UTF-8', $result['currency_type']);
            $this->assign('detail', $result);
            return $this->fetch('hongyan/edit');
        } elseif (request()->isPost()) {
            try{
                $param = [];
                $param['id'] = input('post.id');
                $param['code'] = input('post.code');
                $param['item_name'] = iconv('UTF-8', 'GBK', input('post.item_name'));
                $param['model_no'] = iconv('UTF-8', 'GBK',input('post.model_no'));
                $param['unit'] = iconv('UTF-8', 'GBK', input('post.unit'));
                $param['price_with_tax'] = input('post.price_with_tax');
                $param['currency_type'] = iconv('UTF-8', 'GBK', input('post.currency_type'));
                $param['local_currency'] = input('post.local_currency');
                $param['price_without_tax'] = input('price_without_tax');
                $uname = $_SESSION["LOGIN_BYNAME"];
                $this->hongYan->saveData($param, $uname);
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
        $code = input('get.key.code', '');
        $itemName = input('get.key.item_name', '');
        $beginDate = input('get.key.begin_date', '');
        $endDate = input('get.key.end_date', '');       
        if (!empty($beginDate)) {
            $beginDate = strtotime($beginDate);
        }
        if (!empty($endDate)) {
            $endDate = strtotime($endDate.' 23:59:59');
        }
        $result = $this->hongYan->getList($page, $limit, $code, $itemName, $beginDate, $endDate);
        foreach($result as &$item){
            $item['item_name'] = iconv('GBK','UTF-8', $item['item_name']);
            $item['currency_type'] = iconv('GBK','UTF-8', $item['currency_type']);
            $item['unit'] = iconv('GBK','UTF-8', $item['unit']);
            $item['model_no'] = iconv('GBK','UTF-8', $item['model_no']);
            $item['uname'] = iconv('GBK','UTF-8', $item['uname']);
            $item['ctime'] = date('Y-m-d H:i:s', $item['ctime']);
            $item['mtime'] = date('Y-m-d H:i:s', $item['mtime']);
        }
        $count = $this->hongYan->count($code, $itemName, $beginDate, $endDate);
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

    /**
     * 根据code查询inventory
     */
    public function ajaxFindInventory(){
        $code = input('get.code', '');
        $result = $this->inventory->findByCode($code);
        return $this->_success($result);
    }


}
?>