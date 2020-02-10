<?php 
namespace app\index\controller;

use app\common\controller\Base;
use app\common\model\User as UserModel; 
use app\common\validate\User as UserValidate; 
use think\facade\Request; 
use think\facade\Session;
use app\index\service\Member;

class User extends Base 
{	
	//注册页面
	public function register()
	{
		$this->assign('title','用户注册');
		return $this->fetch();
}

	//处理注册信息
	public function  insert()
	{	
		if(Request::isAjax()){
			$data = Request::post(); 
			$rule = 'app\common\validate\User'; 
			$res=$this->validate($data,$rule);
		  	if (true !== $res){
		  		return ['status'=> -1, 'message'=>$res];
		  	}
		  	$mem = new Member;
		  	if($mem->mem_insert($data)){
				return ['status'=>1, 'message'=>'注册成功！'];
			} else {
				return ['status'=>0, 'message'=>'注册失败！'];			
			}			 
		}else{
			$this->error('请求类型错误','register');
		}
	}

	//登陆界面
	public function login()
	{
		$this->logined();
		return $this->view->fetch('login',['title'=>'用户登录']);
	}


	//用户登录
	public function loginCheck()
	{		
		
		if(Request::isAjax())
		{
			$data = Request::post();
            $rule = ['name|用户名'=>'require|chsAlphaNum','password|密码'=>'require|alphaNum'];
            $res=$this->validate($data,$rule);
            if (true !== $res){  
                return ['status'=> -1, 'message'=>$res];
            }
		  		$mem = new Member;
		 		$a = $mem->mem_loginCheck($data);
		  	switch($a)
		  	{
		  		case 1:
		  		return ['status'=>1, 'message'=>'登录成功！'];
		  		break;
		  		case 0:
		  		return ['status'=>0, 'message'=>'用户已被禁用！'];
		  		break;
		  		case -1:
		  		return ['status'=>-1, 'message'=>'检查用户名密码！'];	
		  		break;
		  	}
		}			 
}


	//退出登录
	public function logout()
	{
		Session::clear();
		$this->success('退出成功','index/index');
	}


}











