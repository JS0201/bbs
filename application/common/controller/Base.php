<?php 
namespace app\common\controller;
use think\Controller;
use think\Facade\Session;
use think\facade\Request;
use app\common\model\ArtCate; 
use app\common\model\Article;
use app\admin\common\model\Site;  

class Base extends Controller
{
	
    protected function initialize()
    {
        //检测站点是否已关闭
        $this->is_open();
        //显示分类导航
        $this->showNav();
        //热门浏览
        $this->getHotArt();
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
            $this->error('您还未登陆，请登陆！','user/login');
        }
    }

    //显示分类导航
    protected function showNav()
    {
        $cateList = ArtCate::all(function($query){
            $query->where('status',1)->order('sort','asc');
        });
        $this->view->assign('cateList', $cateList);
        
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

}












