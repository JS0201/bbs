<?php 
namespace app\admin\controller;

use app\admin\common\controller\Base;
use app\admin\common\model\Site as SiteModel;
use think\facade\Request;

class Site extends Base 
{
	//站点管理
	public function index()
	{
		$siteInfo = SiteModel::get(['status'=>1]);
		$this->view->assign('siteInfo', $siteInfo);
		$this->view->assign('title','站点管理');
		return $this->view->fetch('index');

	}

	//保存站点修改
	public function save()
	{
		$data = Request::param();
		if (SiteModel::update($data)){
			$this->success('更新成功','index');
		} 
		$this->error('无更新或更新失败','index');
	}
}