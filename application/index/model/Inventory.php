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

}
?>