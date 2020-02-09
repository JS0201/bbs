<?php 
namespace app\index\controller;

use app\common\controller\Base;
use app\common\model\User as UserModel; 
use app\common\validate\User as UserValidate; 
use think\facade\Request; 
use think\facade\Session;
use think\facade\Hook;
use think\Config;

class User extends Base 
{
	//注册页面
	public function register()
	{
		//检测是否允许注册
		// $this->is_reg();

		// $data = [];
		// $params = Hook::listen('registerInit',$data);
		// $a=$params[0];
		// if($a['reg_status']===0){
  //           $this->error($a['reg_error'],'register');
 		
		// $this->assign('title','用户注册');
		// return $this->fetch();
	// }
	echo '123';
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
		  	}else { 
		  		if($user=UserModel::create($data)){
		  			$courentUser = UserModel::get($user->id);
		  			Session::set('user_id',$courentUser->id);
		  			Session::set('user_name',$courentUser->name);
		  			Session::set('is_admin',$courentUser->is_admin);

					return ['status'=>1, 'message'=>'注册成功！'];
				} else {
					return ['status'=>0, 'message'=>'注册失败！'];			
				}
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
		
		if(Request::isAjax()){
			$data = Request::post();
			$rule = ['name|用户名'=>'require|chsAlphaNum','password|密码'=>'require|alphaNum'];
			$res=$this->validate($data,$rule);
		  	if (true !== $res){  
		  		return ['status'=> -1, 'message'=>$res];
		  	}else { 
		  		$result = UserModel::get(
		  			function($query) use ($data){
		  			$query->where('name',$data['name'])
		  				  ->where('password',sha1($data['password']));
		  		}
		  	);
		  		if(null == $result){
		  			return ['status'=>0, 'message'=>'用户名或密码不正确,请检查!'];
				} else{
					Session::set('user_id', $result->id);
					Session::set('user_name', $result->name);
					Session::set('is_admin', $result->is_admin);
					return ['status'=>1, 'message'=>'登录成功！'];		
				}
			}			 
		}else{
			$this->error('请求类型错误','login');  //跳转到登录页面
		}
	}

	//退出登录
	public function logout()
	{
		Session::clear();
		$this->success('退出成功','index/index');
	}


}











