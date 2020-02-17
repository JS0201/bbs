<?php 
namespace app\admin\controller;
use app\admin\common\controller\Base;
use app\admin\common\model\Article as ArtModel;
use app\admin\common\model\Cate;
use think\facade\Request;
use think\facade\Session;

class Article extends Base
{
	//文章列表
	public function artList()
	{
    	$this->isLogin();
    	$userId = Session::get('user_id');
    	$isAdmin = Session::get('is_admin');
    	$artList = ArtModel::where('user_id', $userId)->paginate(5);
    	if ($isAdmin == 1) {
    		$artList = ArtModel::paginate(5);
    	}
		$this->view->assign('title', '文章管理');
		$this->view->assign('empty','<span style="red">没有任何文章</span>');
		$this->view->assign('artList', $artList);
		return $this->view->fetch('artlist');
	}


	//编辑文章界面
	public function artEdit()
	{
		$artId = Request::param('id');
		$artInfo = ArtModel::where('id',$artId)->find();
		$cateList = Cate::all();
		$this->view->assign('title','编辑文章');
		$this->view->assign('artInfo',$artInfo);
		$this->view->assign('cateList',$cateList);
		return $this->fetch('artedit');
	}

	//文章编辑
	public function doEdit()
     {   
        $data = Request::param();
        $file = Request::file('title_img'); 
        if($file)
        {
            $info = $file -> validate([
            'size'=>5000000000,  
            'ext'=>'jpeg,jpg,png,gif' 
        ]) -> move('uploads/'); 
        }
        if(!empty($info)) {
            $data['title_img'] = $info->getSaveName();
        }
        if(ArtModel::update($data)){ 
            $this->success('文章更新成功','artList');
        } else {
            $this->error('文章更新失败');
        }
     }

     //文章删除
     public function doDelete()
     {
     	$artId = Request::param('id');
     	if(ArtModel::destroy($artId)){
     		$this->success('删除成功');
     	} 
     	$this->error('删除失败');
     }

	
}









