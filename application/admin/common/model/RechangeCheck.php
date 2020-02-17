<?php 
namespace app\admin\common\model;
use think\Db;
use think\Model;


class RechangeCheck extends Model 
{
	protected $pk = 'id';
	protected $table = 'bbs_rechange_check';
	
	public function getOne($where)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->find();
        return $re;
    }

    public function updated($where, $data)
    {
        $re = '';
        $re = DB::table($this->table)->where($where)->update($data);
        return $re;
    }

}

 