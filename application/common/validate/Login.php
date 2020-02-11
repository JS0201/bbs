<?php
namespace app\common\validate;
use think\validate;

class Login extends validate
{
	protected $rule=[
		'name|用户名'=> 'require|length:2,20|chsAlphaNum',//chsAlphaNum仅允许汉字、字母和数字
		'password|密码'=>'require|length:6,20|alphaNum',  //alphaNum仅允许字母和数字，confirm自动进行相等验证
		'captcha|验证码'=>'require|captcha',
	];
}