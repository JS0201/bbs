<?php 
namespace app\goods\common\model;

use think\Model;

class Spec extends Model 
{
	protected $pk = 'id';
	protected $table = 'bbs_goods_spec';
	protected $autoWriteTimestamp = true;  
	protected $createTime = 'create_time'; 
	protected $updateTime = 'update_time'; 
	protected $dateFormat = 'Y年m月d日';
    //自动完成设置
	protected $auto = [];
	// 仅新增时设置  
	protected $insert = ['create_time'];
	//仅更新时设置
	protected $update = ['update_time'];
    
    public function add($arr)
    {
        $re = false;
        $re = DB::table($this->table)->insertGetId($arr);
        return $re;
    }
    public function getByid($id)
    {
        $re = array();
        $re = DB::table($this->table)->where(array('id' => $id))->find();
        return $re;
    }
    public function getOne($where)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->find();
        return $re;
    }
    public function getAll($where, $select = '')
    {
        $re = array();
        $re = DB::table($this->table)->field($select)->where($where)->select();
        return $re;
    }
    public function updated($where, $data)
    {
        $re = '';
        $re = DB::table($this->table)->where($where)->update($data);
        return $re;
    }

    protected function setValueAttr($value,$data){

        $value=trim(join($value,','),',');

        if(!$value){
            return false;
        }

        return $value;


    }

}