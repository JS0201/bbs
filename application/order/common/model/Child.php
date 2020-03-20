<?php 
namespace app\order\common\model;

use think\Model;

class Child extends Model 
{
	protected $pk = 'id';
	protected $table = 'bbs_order_child';
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
}