<?php
namespace app\admin\common\model;
use think\Model;
use think\Db;
class Finance extends Model
{
	protected $pk = 'id';
    protected $table='bbs_money_finance';

    public function getByUid($id)
    {
        $re = array();
        $re = DB::table($this->table)->where(array('uid' => $id))->find();
        return $re;
    }

     public function updated($where, $data)
    {
        $re = '';
        $re = DB::table($this->table)->where($where)->update($data);
        return $re;
    }
}