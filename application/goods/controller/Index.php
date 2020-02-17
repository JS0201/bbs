<?php
namespace app\goods\controller;
use app\common\controller\Base;
use think\facade\Session;

class Index extends Base
{
	//商城首页
    public function index()
    {
        // $uid = Session::get('user_id');
        // $contentServer = new Content();
        // $adsServer = new Ads();
        // $contentList = $contentServer->getList("recommend = 1", 'id, title',1, 5); //轮播推荐动态
        // $now = time();
        // $bannerList = $adsServer->get_list("status = 1 and {$now} >= starttime (and {$now} <= endtime or endtime = 0)");
        // //商品列表
        // $spuServer = new Spu();
        // $goodList = $spuServer->get_index();
        // $this->assign('uid', $uid);
        // $this->assign('goodList', $goodList);
        // $this->assign('bannerList', $bannerList);
        // $this->assign('contentList', $contentList);
        return $this->fetch();
    }

    //商品详情页
    public function single()
    {
        // $uid = Session::get('user_id');
        // // $contentServer = new Content();
        // // $adsServer = new Ads();
        // // $contentList = $contentServer->getList("recommend = 1", 'id, title',1, 5); //轮播推荐动态
        // // $now = time();
        // // $bannerList = $adsServer->get_list("status = 1 and {$now} >= starttime (and {$now} <= endtime or endtime = 0)");
        // // //商品列表
        // // $spuServer = new Spu();
        // // $goodList = $spuServer->get_index();
        // $this->assign('uid', $uid);
        // // $this->assign('goodList', $goodList);
        // // $this->assign('bannerList', $bannerList);
        // // $this->assign('contentList', $contentList);
        return $this->fetch();
    }

}
