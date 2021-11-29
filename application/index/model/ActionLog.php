<?php
namespace app\index\model;
use think\Model;
class ActionLog extends Model{

    private $map = [];

    /**
     * 获取列表
     */
    public function getList($action_type, $ticket_no, $uname = ''){
        $where = [];
        if (!empty($type)){
            $where = array_merge($where, ['type'=>$action_type]);
        }
        if (!empty($ticket_no)){
            $where = array_merge($where, ['ticket_no'=>$ticket_no]);
        } 
        if (!empty($uname)) {
            $where = array_merge($where, ['uname'=>$uname]);
        }
        if(!empty($where)) {
            return db('action_log')->where($where)->order('id desc')->select()->toArray();
        } else {
            return db('action_log')->order('id desc')->select()->toArray();
        }

    }

    /**
     * 获取一条日志
     */
    public function findData($ticket_no, $action_type, $action_name){
        $where = [
            'ticket_no'=>$ticket_no, 
            'action_type'=>$action_type,
            'action_name'=>$action_name
        ];
        return db('action_log')->where($where)->find(
            ['from_data', 'to_data', 'uname']
        );
    }

    /**
     * 保存数据
     */
    public function saveData($param){
        $log = new ActionLog();
        $log->ticket_no = $param['ticket_no'];
        $log->action_name = $param['action_name'];
        $log->action_type = $param['action_type'];
        $log->from_data = $param['from_data'];
        $log->to_data = $param['to_data'];
        $log->uname = $param['uname'];
        $log->ctime = time();
        $log->mtime = time();
        return $log->save();
    }

    function insertLog($oldArray, $newArray, $type, $uname){
        // $old = ['id'=>10, 'name'=>'zhangsan', 'age'=> 10];
        // $new = ['id'=>10, 'name'=>'lisi', 'age'=> 20];
        // $ticket_no = $old['ticket_no'];
        // $action_type = $old['action_type'];
        // $action_name = $old['action_name'];
        // foreach($old as $key=>$val) {
            // $new = $this->findData($ticket_no, $action_type, $key);
            $diff = array_diff_assoc($oldArray, $newArray);
            // dump($diff);
            // die;
            if (!empty($diff)) {
                foreach($diff as $key=>$val){
                    $param = [];
                    $param['ticket_no'] = $oldArray['id'];
                    $param['action_name'] = $key;   
                    $param['action_type'] = $type;
                    $param['from_data'] = $val;  //old的值
                    $param['to_data'] = $newArray[$key]; //新的值
                    $param['action_type'] = $type;
                    $param['uname'] = $uname;
                    $this->saveData($param);
                }
            }
    }

}
?>