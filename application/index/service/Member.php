<?php
namespace app\index\service;

use app\common\controller\Base;
use think\facade\Session;
use app\common\model\User as UserModel; 

class Member extends Base
{
	/*
        注册   
     */
    public function mem_insert($data)
	{	
        $data['encrypt'] = bin2hex(random_bytes(3));
        $data['password'] = $this->create_password($data['password'],$data['encrypt']);
		if($user=UserModel::create($data))
		{
		  $courentUser = UserModel::get($user->id);
		  Session::set('user_id',$courentUser->id);
		  Session::set('user_name',$courentUser->name);
		  Session::set('is_admin',$courentUser->is_admin);	
		  return 1;	
		}	  			
	} 
	/*
        登录   
     */
    public function mem_loginCheck($data)
	{
		$result = UserModel::get(
                    function($query) use ($data){
                    $query->where('name',$data['name']);
                }
            );
		if (!$result || $this->create_password($data['password'],$result['encrypt']) != $result['password']) {
            return -1;
        }
        if ($result->status == 1) {
        	$data['login_num'] = $result['login_num'] + 1;
        	$data['login_time'] = time();
        	$hja = UserModel::where('name',$data['name'])
        	->update(['login_time'=>$data['login_time'],'login_num'=>$data['login_num']]);
            Session::set('user_id', $result->id);
			Session::set('user_name', $result->name);
			Session::set('is_admin', $result->is_admin);
			return 1;
        }else{
        	return 0;
        }
		
	}
    /*
        密码加密
     */
    public function create_password($password,$encrypt) {
        $salt = '$2a$11$' . substr(md5($password.$encrypt), 5, 23);//Blowfish
        return substr(crypt($password, $salt),7);
    }
}