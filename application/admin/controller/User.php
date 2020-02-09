<?php 
namespace app\admin\controller;

use app\admin\common\controller\Base;
use app\admin\common\model\User as UserModel;
use think\facade\Request;
use think\facade\Session;

class User extends Base 
{
	//渲染登录界面
	public function login()
	{
		$this->view->assign('title','管理员登录');
		return $this->view->fetch('login'); 
	}

	//验证用户登录
	public function checkLogin()
	{
		$data = Request::param();

		$map[] = ['name','=',$data['name']];
		$map[] = ['password','=',sha1($data['password'])];
		$map[] = ['is_admin','=',1];
		$result = UserModel::where($map)->find();
		if($result){
			Session::set('user_id',$result['id']);
			Session::set('user_name',$result['name']);
			Session::set('is_admin',$result['is_admin']);			
			$this->success('登录成功','admin/user/userList');
		}

		$this->error('该功能只允许管理员使用！');
	}

	//退出登录
	public function logout()
	{
		Session::clear();
		$this->success('退出成功','login');
	}

    //用户列表
	public function userList()
	{
		$data['user_id'] = Session::get('user_id');
		$data['is_admin'] = Session::get('is_admin');
		if ($data['is_admin'] == 1){
			$userList = UserModel::select();
		}
		else{
			$userList = UserModel::where('id',$data['user_id'])->select();
		}
		$this->view->assign('title', '用户管理');
		$this->view->assign('empty','<span style="red">没有任何数据</span>');
		$this->view->assign('userList', $userList);
		return $this->view->fetch('userlist');
	}

	//用户编辑界面
	public function userEdit()
	{
		$userId = Request::param('id');
		$userInfo = UserModel::where('id',$userId)->find();
		$this->assign('title','编辑用户');
		$this->assign('userInfo',$userInfo);
		return $this->fetch('useredit');
	}

	//执行用户编辑操作
	public function doEdit()
	{
		$data = Request::param();
		$id = $data['id']; 	
		$userInfo = UserModel::where('id',$id)->find();
		if ($data['password'] == $userInfo['password']) {
			unset($data['password']); 
		} else {
			$data['password'] = sha1($data['password']);
		}
		if(UserModel::where('id',$data['id'])->data($data)->update()){
			return $this->success('更新成功','userList');
		}
		$this->error('没有更新或更新失败');
	}


	//用户删除
	public function doDelete()
	{
		$id = Request::param('id');
		if(UserModel::where('id',$id)->delete()){
			return $this->success('删除成功','userList');
		}
		$this->error('删除失败');

	}


}

















