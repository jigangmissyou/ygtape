<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Inventory extends Model{
    
    private $db;
    
    function __construct()
    {
        $this->db = Db::connect('db_con2');
    }

    /**
     * 根据code查询inventory
     */
    function findByCode($cno){
        if (empty($cno)) return [];
        $result = $this->db->query('select * from Inventory where cInvCode = :cno',['cno'=>$cno]);
        if (!empty($result)) {
            return $result[0];
        }
        return [];
    }

    /**
     * 查找所有A的数据
     */
    function getAll($beginPage = 0, $endPage = 20, $code = ''){
        if (!empty($code)) {
            $sql = "select * from (
                　　　　select cInvName, cInvCCode, cInvStd, ROW_NUMBER() OVER(Order by cInvCCode ) AS RowId from Inventory where cInvCCode =  '".$code."'
                　　) as b
                where RowId between $beginPage and $endPage";
        } else {
            $sql = "select * from (
                　　　　select cInvName, cInvCCode, cInvStd, ROW_NUMBER() OVER(Order by cInvCCode ) AS RowId from Inventory where left(cInvCCode,1) = 'A'
                　　) as b
                where RowId between $beginPage and $endPage";
        }
        $result = $this->db->query($sql);
        if (!empty($result)) {
            return $result;
        }
        return [];
    }

    function getCount($code = ''){
        if (!empty($code)) {
            $sql = "select  count(*) as count from Inventory where cInvCCode =  '".$code."' ";
        } else {
            $sql = "select  count(*) as count from Inventory where left(cInvCCode,1) = 'A'";
        }
        $result = $this->db->query($sql);
        if (!empty($result[0])) {
            return $result[0]['count'];
        }
        return 0;
    }

}
?>