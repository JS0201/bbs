<?php 
namespace app\common\validate;

use think\Validate;

class Article extends Validate 
{
	protected $rule = [
		'title|标题'=> 'require|length:5,50',
		'content|文章内容'=>'require|length:5,100000',
		'user_id|作者'=>'require',
		'cate_id|栏目名称'=>'require',
	];
}