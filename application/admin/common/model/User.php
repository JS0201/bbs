<?php 
namespace app\admin\common\model;
use think\Db;
use think\Model;

class User extends Model 
{
	protected $pk = 'id';
	protected $table = 'bbs_user';
	public function getOne($where)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->find();
        return $re;
    }


} 