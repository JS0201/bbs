<?php 
//zh_user表的模型
namespace app\common\model;
use think\Model;

class User extends Model 
{
	protected $pk = 'id';  
	protected $table = 'bbs_user';  

	protected $autoWriteTimestamp = true; 
	protected $createTime = 'create_time'; 
	protected $updateTime = 'update_time'; 
	protected $dateFormat = 'Y年m月d日'; 
	protected $datetime = 'Y年m月d日'; 
    //用户状态
} 