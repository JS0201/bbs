<?php
namespace app\admin\common\model;
use think\Model;
use think\Db;
class MemberLog extends Model{
	protected $pk = 'id';
    // protected $append=['username'];
    protected $table='bbs_member_log';

    public function getValue($where, $value)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->value($value);
        return $re;
    }
    public function add($data)
    {
        $re = false;
        $re = DB::table($this->table)->insertGetId($data);
        return $re;
    }
}