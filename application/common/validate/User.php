<?php 
namespace app\common\validate;

use think\Validate;

class User extends Validate
{
	protected $rule = [
		'name|用户名'=> 'require|length:2,20|chsAlphaNum',//chsAlphaNum仅允许汉字、字母和数字
		'email|邮箱'=> 'require|email|unique:zh_user',  //unique该字段必须在表中唯一
		'mobile|手机号'=>'require|mobile|unique:zh_user',
		'password|密码'=>'require|length:6,20|alphaNum|confirm'  //alphaNum仅允许字母和数字，confirm自动进行相等验证
	];
	
}