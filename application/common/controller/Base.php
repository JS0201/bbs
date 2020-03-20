<?php 
namespace app\common\controller;
use think\Controller;
use think\Facade\Session;
use think\facade\Request;
use app\common\model\ArtCate; 
use app\common\model\Article;
use app\common\model\User;
use app\admin\common\model\Site;  
use app\goods\common\model\Category;

class Base extends Controller
{
	
    protected function initialize()
    {
        //检测站点是否已关闭
        $this->is_open();
        //论坛导航
        $this->showNav();
        //热门浏览
        $this->getHotArt();
        //书店导航
        $this->showBookNav();
        //书店后台导航
        $this->nav();
    }

    //检查是否已登录
    protected function logined()
    {
    	if(Session::has('user_id')){
    		$this->error('您已登陆，请不要重复登录！','index/index');
    	} 
    }

    //检查是否未登录
    protected function isLogin()
    {
        if (!Session::has('user_id')) {
            $this->error('您还未登陆，请登陆！','/index/user/login');
        }
    }

    //论坛导航
    protected function showNav()
    {
        $cateList = ArtCate::all(function($query){
            $query->where('status',1)->order('sort','asc');
        });
        $mem = User::get(function($query){
            $query->where('id',session::get('user_id'));
        });
        $this->view->assign('mem', $mem);
        $this->view->assign('cateList', $cateList);
        
    }

    //书店导航
    protected function showBookNav()
    {
        $uid = Session::get('user_id');
        $this->view->assign('uid', $uid);
    }


     //检测站点是否已关闭
    public function is_open()
    {
        $isOpen = Site::where('status',1)->value('is_open');
        if ($isOpen==0 && Request::module()=='index') {
            $info = <<<INFO
            <body style="background-color:#333">
            <h1 style="color:#eee;text-align:center;margin:200px">站点维护中...</h1>
            </body>
INFO;
            exit($info );
        }
    }


    //检测注册是否关闭
    public function is_reg()
    {
        $isReg = Site::where('status',1)->value('is_reg');
        if ($isReg == 0) {            

            $this->error('注册已关闭','index/index');
        }
    }

    //热门排行
    public function getHotArt()
    {
        $hotArtList = Article::where('status',1)->order('pv','desc')->limit(12)->select();

        $this->view->assign('hotArtList', $hotArtList);
    }

    //书店后台导航
    protected function nav()
    {
        $data = Category::all();
        $this->view->assign('data',$data);
    }
}












