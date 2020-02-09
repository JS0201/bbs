<?php 
namespace app\admin\controller;

use app\admin\common\controller\Base;
use app\admin\common\model\Cate as CateModel;
use think\facade\Request;
use think\facade\Session;

class Cate extends Base
{
	//分类管理首页
	public function index()
	{
    	$this->isLogin();
		return $this->redirect('catelist');
	}

	//分类列表
	public function cateList()
	{
    	$this->isLogin();
		$cateList = CateModel::all();
		$this->view->assign('title', '分类管理');
		$this->view->assign('empty','<span style="red">没有任何分类</span>');
		$this->view->assign('cateList', $cateList);
		return $this->view->fetch('catelist');
	}

	//分类编辑
	public function cateEdit()
	{
		$cateId = Request::param('id');
		$cateInfo = CateModel::where('id',$cateId)->find();
		$this->view->assign('title','编辑分类');
		$this->view->assign('cateInfo',$cateInfo);
		return $this->fetch('cateedit');
	}

	//编辑保存
	public function doEdit()
	{
		$data = Request::param();
		$id = $data['id'];
		if(CateModel::where('id',$data['id'])->data($data)->update()){
			return $this->success('更新成功','cateList');
		}
		$this->error('没有更新或更新失败');
	}

	//分类删除
	public function doDelete()
	{
		$id = Request::param('id');
		if(CateModel::where('id',$id)->delete()){
			$this->success('删除成功','cateList');
		}
		$this->error('删除失败');

	}

	//添加界面
	public function cateAdd()
	{
		$this->view->assign('title','添加分类');
		return $this->fetch('cateadd');
	}

	//添加操作
	public function doAdd()
	{
		$data = Request::param();
		if(CateModel::create($data)){
			$this->success('添加成功','catelist');
		}
		$this->error('添加失败','catelist');
	}
}









