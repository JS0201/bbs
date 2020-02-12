<?php 
namespace app\common\validate;

use think\Validate;

class MemEdite extends Validate
{
	protected $rule = [
		'profile|文章内容'=>'require|length:5,1000',
		'nickname|昵称'=> 'require|length:2,20|chsAlphaNum',//chsAlphaNum仅允许汉字、字母和数字
		'email|邮箱'=> 'require|email|unique:user',  //unique该字段必须在表中唯一
		'mobile|手机号'=>'require|mobile|unique:user',
	];
	
}