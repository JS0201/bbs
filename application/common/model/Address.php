<?php 
namespace app\common\model;

use think\Model;


class Address extends Model 
{
	protected $pk = 'id';
	protected $table = 'bbs_member_address';
	protected $autoWriteTimestamp = true;
	protected $createTime = 'create_time';
	protected $dateFormat = 'Y年m月d日';
	protected $auto = [];
	// 仅新增时设置  
	protected $insert = ['create_time','status'=>0,'is_top'=>0,'is_hot'=>0];


}