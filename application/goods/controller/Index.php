<?php
namespace app\goods\controller;
use app\common\controller\Base;
use think\facade\Session;
use app\goods\service\Spu;
use app\goods\service\Detail;

class Index extends Base
{
	//商城首页
    public function index()
    {
        $spu = new Spu;
        $list=$spu->getAll();
        $this->assign('list',$list);
        $this->assign('title','书店首页');
        return $this->fetch();
    }

    //商品详情页
    public function detail()
    {
        $detail = new Detail;
        if($cid=input('cid/d'))
        {
            $data=[];
            $list=$detail->getOne($cid);
            foreach ($list[0] as $key => $value) {
                $lists = $value;
            }
            $imgs = $list[1];
            $this->assign('imgs', $imgs);
            $this->assign('list', $lists);
        }
        $this->assign('title','商品详情');
        return $this->fetch();
    }

}
