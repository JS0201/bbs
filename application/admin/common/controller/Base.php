<?php 
namespace app\admin\common\controller;
use think\Controller;
use think\facade\Request;
use think\facade\Session;
use app\admin\common\model\Site;

class Base extends Controller 
{
    protected function initialize()
    {
        
    }
    protected function isLogin()
    {
        if (!Session::has('user_id')) {
            $this->error('请先登录!','admin/user/login');
        }
    }
}