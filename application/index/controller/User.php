<?php 
namespace app\index\controller;

use app\common\controller\Base;
use app\common\model\User as UserModel;
use app\common\model\Article; 
use app\common\model\Comment;
use app\common\validate\User as UserValidate; 
use think\facade\Request; 
use think\facade\Session;
use app\index\service\Member;
use think\captcha\Captcha;

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
		if($data = Request::post()){
			$rule = 'app\common\validate\Reginster'; 
			$res=$this->validate($data,$rule);
		  	if (true !== $res){
		  		$this->error($res,'register');
		  	}
		  	$mem = new Member;
		  	if($mem->mem_insert($data)){
				$this->success('注册成功！','index/index');
			} else {
				$this->error('请检查用户名密码！','register');			
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

	//验证码
	public function verify()
    {	
    	$config =    [
    	'fontSize'    =>    30,    
    	'length'      =>    5,   
   	 	'fontttf' 	  =>    '6.ttf',
   	 	'useCurve'	  =>	false,
   	 	'bg'=>[244, 244, 244],
	];
        $captcha = new Captcha($config);
        return $captcha->entry();    
    }

	//用户登录
    public function loginCheck()
    {		

    	if(Request::isAjax())
    	{
    		$data = Request::param();
    		$mem = new Member;
    		if($mem->mem_loginCheck($data))
    		{
    			return ['status'=>1, 'message'=>'登录成功！'];
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

