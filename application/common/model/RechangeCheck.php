<?php 
namespace app\common\model;

use think\Model;

class RechangeCheck extends Model 
{
	protected $pk = 'id';
	protected $table = 'bbs_rechange_check';
	protected $autoWriteTimestamp = true; 
	protected $createTime = 'create_time'; 
	protected $dateFormat = 'Y年m月d日';
    //自动完成设置
	protected $auto = [];
	// 仅新增时设置  
	protected $insert = ['create_time','status'=>1];
}

