<?php
namespace app\index\model;
use think\Model;
use think\Db;
use app\index\model\HongYan;
use think\Cache;
use think\Log;
use PHPExcel_IOFactory;
use PHPExcel;
// use PHPExcel;

class Bom extends Model{
    
    private $db;
    
    function __construct()
    {
        $this->db = Db::connect('db_con2');
    }

    /**
     * 查询存储过程
     */
    function getList($ddate, $dend, $cno = [], $download = 0){
        $jsonParam = json_encode(['ddate'=>$ddate, 'dend'=>$dend, 'cno'=>$cno]);
        $msg = '存储过程输入参数：'. $jsonParam;
        Log::record($msg);
        $result = Cache::get($jsonParam);
        if (empty($result)) {
            $result = $this->db->query('exec qbo_mcxq_erp :ddate,:dend,:cno',['ddate'=>$ddate,'dend'=>$dend,'cno'=>$cno]);
            Cache::set($jsonParam, $result, 3600);
        }
        $msg = '存储过程输出结果：'. json_encode($result);
        Log::record($msg);
        if (!empty($result)) {
            $result = $result[0];
            // $result = $this->convertData($result);
            $result = $this->processExcel($result, $download);
        }
        return $result;
    }

    /**
     * 获取单耗
     */
    private function findUnitConsumption($item){
        //=(Q2/R2+W2)
        //基本用量/基础用量+折扣
        if (!empty($item['jcyl'])){
            return $item['jbyl']/$item['jcyl']+$item['dzdsl'];
        } else {
            return 0;
        }
        
    }

    /**
     * 根据code获取最新的hongyan价格
     */
    function findHongYanPrice($code){
        $hongYan = new HongYan();
        $data = $hongYan->findByCode($code);
        return $data;
    }

    /**
     * 获取打折的数量（膜和胶95%）
     */
    private function findDicount($item){
        $array1 = ["胶", "和纸硅纸", "丁苯乳胶", "硅油", "美纹硅纸", "防渗纸", "PVC膜", "OPP膜", "色母", "聚乙烯醇"];
        $array2 = ["美纹原纸", "防渗美纹纸"];
        $array3 = ["EVA", "IXPE", "PE泡棉"];
        if (!empty($item['jcyl'])) {
            if (in_array($item['zjdl'], $array1) || ($item['zjdl'] == "粒子" && $item['mjdl'] != "粒子")) {
                //基本用量/基础用量/0.95 - 基本用量/基础用量
                // $value = Q2/R2/0.95-Q2/R2;
                return ($item['jbyl']/$item['jcyl']/0.95) - ($item['jbyl']/$item['jcyl']); 
            } elseif (in_array($item['zjdl'], $array2)) {
                //基本用量/基础用量/0.93 - 基本用量/基础用量
                return ($item['jbyl']/$item['jcyl']/0.93) - ($item['jbyl']/$item['jcyl']); 
            } elseif ($item['zjdl'] == "和纸原纸") {
                //基本用量/基础用量/0.9 - 基本用量/基础用量
                //Q2/R2/0.9-Q2/R2
                return ($item['jbyl']/$item['jcyl']/0.9) - ($item['jbyl']/$item['jcyl']); 
            } elseif ($item['zjdl'] == "网格布") {
                //基本用量/基础用量/0.97 - 基本用量/基础用量
                //Q2/R2/0.97-Q2/R2
                return ($item['jbyl']/$item['jcyl']/0.97) - ($item['jbyl']/$item['jcyl']); 
            } elseif (in_array($item['zjdl'], $array3)) {
                //基本用量/基础用量/0.98 - 基本用量/基础用量
                //Q2/R2/0.98-Q2/R2
                return ($item['jbyl']/$item['jcyl']/0.98) - ($item['jbyl']/$item['jcyl']); 
            } elseif ($item['zjdl'] == "乙酸乙酯") {
                //-基本用量/基础用量/0.5
                // -(Q2/R2*0.5)
                return -($item['jbyl']/$item['jcyl']/0.5); 
            } else {
                return 0;
            }
        } else {
            return 0;
        }
        
    }

    /**
     * BOM 平方克重
     */
    private function findBomWeight($item){
        //=IF(OR(I3="PVC膜",I3="胶"),(Q3/0.95/R3*1000)/F3,"")
        if (!empty($item['jcyl']) && !empty($item['mjjdpf'])) {
            if ($item['zjdl'] == "PVC膜" || $item['zjdl'] == "胶") {
                //基本用量/0.95/基础用量*1000/母件卷到平方
                //(Q3/0.95/R3*1000)/F3
                return $item['jbyl']/0.95/$item['jcyl']*1000/$item['mjjdpf'];
            }
        }
        
    }

    /**
     * 母件辅助单位
     */
    private function findMotherAuxiliaryUnit($item) {
        $array = ["卷", "米", "支"];
        //=IF(AND(F2<>"",OR(G2="卷",G2="米",G2="支")),"平方",G2)
        if (!empty($item['mjjdpf']) && in_array($item['mjjldw'], $array)) {
            return "平方";
        } else {
            return $item['mjjldw'];
        }
    }

    /**
     * Bom辅助价格
     */
    private function findBomAuxiliaryPrice($item) {
        $array = ["卷", "米", "支"];
        //=IF(AND(F6<>"",OR(G6="卷",G6="米",G6="支")),AB6/F6,AB6)
        if (!empty($item['mjjdpf']) && in_array($item['mjjldw'], $array)) {
            return $item['dwcbhj']/$item['mjjdpf'];
        } else {
            return $item['dwcbhj'];
        }

    }

    /**
     * 数据单位转换
     */
    private function convertData($list){
        $id = 0;
        //初始化价格
        foreach($list as &$item){
            $item['id'] = ++$id;
            //设置,MAX(不含税):bbwsdj(本币无税单价)和hongyan价格中的最大值
            $hongYanData = $this->findHongYanPrice($item['zjbm']);
            $hongYanPrice = !empty($hongYanData) ? $hongYanData['price_without_tax'] : 0;
            $item['zdjg'] = round(max($item['bbwsdj'] ?: 0, $hongYanPrice), 2);
            //洪研价格
            $item['hyjg'] = $hongYanPrice;
            //涨幅:hongyan价格减去bbwsdj,除以bbwsdj
            $item['zf'] = !empty($item['bbwsdj']) ? ($hongYanPrice - $item['bbwsdj']) / $item['bbwsdj'] .'%' : 0 .'%';
            //打折的数量
            $item['dzdsl'] = round($this->findDicount($item), 2);
            //BOM 平方克重
            $item['bompfkz'] = round($this->findBomWeight($item), 2); 
            //单耗
            $item['dh'] = round($this->findUnitConsumption($item), 2);
            if(substr($item['zjbm'], 0, 1) != 'A'){
                $item['cldj'] = 0;
                $item['dwcbzc'] = 0;
            } else {
                $item['cldj'] = $item['zdjg'];
                $item['dwcbzc'] = $item['dh']*$item['zdjg'];
            }
        }
        $array = $this->searchArray($list);
        foreach($list as &$item){
            //开始遍历list
           if(substr($item['zjbm'], 0, 1) != 'A'){
               $item['cldj'] = round($this->digui($item['zjbm'], $array), 2);
               $item['dwcbzc'] = $item['dh'] * $item['cldj'];
           }
        }
        foreach($list as &$item){
            $item['dwcbhj'] = $this->compute($list, $item['mjbm']);
            //母件辅助单位
            $item['mjfzdw'] = $this->findMotherAuxiliaryUnit($item);
            //Bom辅助单位成本
            $item['bomfzdwcb'] = $this->findBomAuxiliaryPrice($item);
        }
        return $list;
    }

    /**
     * 组装新数组
     */
    private function searchArray($list){
        //组装新数组
        $array = [];
        foreach($list as &$item){
            if(substr($item['zjbm'], 0, 1) != 'A'){
                foreach($list as $li){
                    if($li['mjbm'] == $item['zjbm']){
                        $array[$item['zjbm']][$li['zjbm']] = $li;
                    }
                }
            }
        }
        return $array;
    }

    /**
     * 递归计算半成品
     */
    private function digui($param, $combination, &$sum=0, $dh = 1){
        foreach($combination as $key => $val){
            if($param == $key){
                foreach($val as $k=>$v){
                    //如果不为A那么递归遍历
                    if(substr($k, 0, 1) != 'A'){
                        $this->digui($k, $combination, $sum, $v['dh']);
                    }else{
                        $sum += $v['dwcbzc']*$dh;
                    }
                }
            }
        }
        return $sum;
    }

    /**
     * 计算单位成本合计
     */
    private function compute($list, $key){
        $sum = 0;
        foreach($list as $item){
            if($key == $item['mjbm']) {
                $sum += $item['dwcbzc'];
            }
        }
        return $sum;
    }

    /**
     * 数据转EXCEL
     */
    public function processExcel($list, $download = 0){
        //创建PHPExcel对象
        $objPHPExcel = new PHPExcel();
        //写入excel操作
        //使用工厂的方式创建excel写入对象
        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'excel2007');
        $objPHPExcel->getProperties()->setTitle("江西卖给胶粘BOM数据");  
        $objPHPExcel->getProperties()->setSubject("江西卖给胶粘BOM数据");  
        $objPHPExcel->getProperties()->setDescription("江西卖给胶粘BOM数据");  
        $objPHPExcel->getProperties()->setKeywords("BOM");  
        // $objPHPExcel->getProperties()->setCategory("bom result file"); 
        $objPHPExcel->setActiveSheetIndex(0);             
        $objActSheet = $objPHPExcel->getActiveSheet();  
        if(!empty($list)){
            $i = 1;
            $objActSheet->setCellValue( 'A'.$i, '母件大类')
                            ->setCellValue( 'B'.$i, '母件编码')
                            ->setCellValue( 'C'.$i, '母件名称' )         
                            ->setCellValue( 'D'.$i, '母件存货代码' )      
                            ->setCellValue( 'E'.$i, '母件规格型号')            
                            ->setCellValue( 'F'.$i, '母件卷到平方')            
                            ->setCellValue( 'G'.$i, '母件计量单位' )          
                            ->setCellValue( 'H'.$i, '子件序号' )           
                            ->setCellValue( 'I'.$i, '子件大类' )           
                            ->setCellValue( 'J'.$i, '子件编码' )           
                            ->setCellValue( 'K'.$i, '子件名称' )           
                            ->setCellValue( 'L'.$i, '子件代码' )           
                            ->setCellValue( 'M'.$i, '子件规格' )           
                            ->setCellValue( 'N'.$i, '子件卷到平方' )          
                            ->setCellValue( 'O'.$i, '计量单位' )           
                            ->setCellValue( 'P'.$i, '供应类型' )           
                            ->setCellValue( 'Q'.$i, '基本用量' )          
                            ->setCellValue( 'R'.$i, '基础用量' )          
                            ->setCellValue( 'S'.$i, '本币无税单价' )  
                            ->setCellValue( 'T'.$i, '取价MAX(不含税)' )          
                            ->setCellValue( 'U'.$i, '洪研价格' )
                            ->setCellValue( 'V'.$i, '涨幅' )
                            ->setCellValue( 'W'.$i, '打折的数量（膜和胶95%）' )
                            ->setCellValue( 'X'.$i, 'BOM平方克重' )
                            ->setCellValue( 'Y'.$i, '单耗')
                            ->setCellValue( 'Z'.$i, '材料单价' )
                            ->setCellValue( 'AA'.$i, '单位成本组成' )
                            ->setCellValue( 'AB'.$i, '单位成本合计' )
                            ->setCellValue( 'AC'.$i, '母件辅助单位' )
                            ->setCellValue( 'AD'.$i, 'BOM辅助单位成本(纯专用材料)' );
            foreach($list as &$item){
                $i++;
                $hongYanData = $this->findHongYanPrice($item['zjbm']);
                $hongYanPrice = !empty($hongYanData) ? $hongYanData['price_without_tax'] : 0;
                //如果本币无税单价为空，那么读取hongyan表里面的local_currency
                if(empty($item['bbwsdj'])) {
                    $item['bbwsdj'] = !empty($hongYanData) ? (string)$hongYanData['local_currency'] : (string)0;
                }
                $objActSheet->setCellValue( 'A'.$i, $item['mjdl'])
                            ->setCellValue( 'B'.$i, $item['mjbm'])
                            ->setCellValue( 'C'.$i, $item['mjmc'] )         
                            ->setCellValue( 'D'.$i, $item['mjchdm'] )      
                            ->setCellValue( 'E'.$i, $item['mjggxh'])            
                            ->setCellValue( 'F'.$i, $item['mjjdpf'])            
                            ->setCellValue( 'G'.$i, $item['mjjldw'] )           
                            ->setCellValue( 'H'.$i, $item['zjxh'] )           
                            ->setCellValue( 'I'.$i, $item['zjdl'] )           
                            ->setCellValue( 'J'.$i, $item['zjbm'] )           
                            ->setCellValue( 'K'.$i, $item['zjmc'] )          
                            ->setCellValue( 'L'.$i, $item['zjdm'] )           
                            ->setCellValue( 'M'.$i, $item['zjgg'] )           
                            ->setCellValue( 'N'.$i, $item['zjjdpf'] )           
                            ->setCellValue( 'O'.$i, $item['jldw'] )           
                            ->setCellValue( 'P'.$i, $item['gylx'] )          
                            ->setCellValue( 'Q'.$i, $item['jbyl'] )           
                            ->setCellValue( 'R'.$i, $item['jcyl'] )         
                            ->setCellValue( 'S'.$i, $item['bbwsdj'])
                            ->setCellValue( 'T'.$i, '=MAX(S'.$i.',IFERROR(U'.$i.',))' ) //取价MAX
                            ->setCellValue( 'U'.$i, $hongYanPrice )     //洪研价格
                            ->setCellValue( 'V'.$i, '=(U'.$i.'-S'.$i.')/S'.$i.'' )  //涨幅
                            ->setCellValue( 'W'.$i, round($this->findDicount($item), 2) )  //打折的数量
                            ->setCellValue( 'X'.$i, $this->findBomWeight($item) ) //BOM平方克数
                            ->setCellValue( 'Y'.$i, '=(Q'.$i.'/R'.$i.'+W'.$i.')' ) //单耗
                            ->setCellValue( 'Z'.$i, '=IF(LEFT(J'.$i.',1)="A",T'.$i.',SUMIFS(AA:AA,B:B,J'.$i.'))' )  //材料单价
                            ->setCellValue( 'AA'.$i, '=(Y'.$i.'*Z'.$i.')' )  //单位成本组成
                            ->setCellValue( 'AB'.$i, '=SUMIFS(AA:AA,B:B,B'.$i.')' )//单位成本合计
                            ->setCellValue( 'AC'.$i, '=IF(AND(F'.$i.'<>"",OR(G'.$i.'="卷",G'.$i.'="米",G'.$i.'="支")),"平方",G'.$i.')' )
                            ->setCellValue( 'AD'.$i, '=IF(AND(F'.$i.'<>"",OR(G'.$i.'="卷",G'.$i.'="米",G'.$i.'="支")),AB'.$i.'/F'.$i.',AB'.$i.')' );
                    
            }
            $mapKey = [
                'mjdl',
                'mjbm',
                'mjmc',
                'mjchdm',
                'mjggxh',
                'mjjdpf',
                'mjjldw',
                'zjxh',
                'zjdl',
                'zjbm',
                'zjmc',
                'zjdm',
                'zjgg',
                'zjjdpf',
                'jldw',
                'gylx',
                'jbyl',
                'jcyl',
                'bbwsdj',
                'zdjg',
                'hyjg',
                'zf',
                'dzdsl',
                'bompfkz',
                'dh',
                'cldj',
                'dwcbzc',
                'dwcbhj',
                'mjfzdw',
                'bomfzdwcb'
            ];
            $objWorksheet = $objPHPExcel->getActiveSheet();//取得总行数
            $highestRow = $objWorksheet->getHighestRow();//取得总列数
            if ($download) {
                $j = 1;
            } else {
                $j = 2;
            }
            $array = [];
            for ($row = $j; $row <= $highestRow; $row++)
            {
                for ($col = 0; $col < count($mapKey); $col++)
                {
                    $array[$row][$mapKey[$col]] = $objActSheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                    
                }
                // dump($array[$row]);
            }
            // die;
            if ($download) {
                $filename = 'bom_'.date('Y-m-d').'.xls';
                //也可以浏览器输出
                header('Content-Type: application/vnd.ms-execl');
                header('Content-Disposition: attachment;filename='.$filename);
                header('Cache-Control: max-age=0');
                $writer->save('php://output');
            } else {
                return $array;
            }
        }
    }
}
?>