<?php
namespace app\goods\controller;
use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request;
use app\goods\service\Category;
use app\goods\service\Brand;
use app\goods\service\Spu;
use app\goods\common\model\Spu as Sp;

class Admin extends Base{

/*
    商品列表
 */
    public function index()
    {   
        $spu = new Spu;
        if($catid=input('catid/d'))
        {
            $list=$spu->getOne($catid);
        }else{
            $list=$spu->getAll();
        }
        // if(!$category) showmessage(lang('_param_error_'),url('goods/admin/init'));

        // $sqlmap=[];

        // //$sqlmap['path_id']=['like','%,'.$catid.',%'];
        // // 根据商户ID查询数据
        // if(ADMIN_ID != 1){
        //     $sqlmap['admin_id'] = ADMIN_ID;
        // }
        // $list=$this->spu_service->get_list($sqlmap);
        // $this->assign('imgUrl', $this->config['Url']);
        $this->assign('list',$list);

        return $this->fetch();
    }


/*
    添加商品
 */
    public function add(){
        if($data=Request::post()){
            $spu = new Spu;
            $data['admin_id'] = Request::session('user_id');
            $data['catid']=$data['cid'];
            if (!$spu->add($data)) {
                $this->error('添加失败!','/goods/admin/add/'.'catid/'.$data['cid']);
                // return ['status'=>0,'message'=>'添加失败！'];
            }
            $this->success('添加成功!','/goods/admin/add/'.'catid/'.$data['cid']);

        }else{
            $catid=input('catid/d');
            $cate = new Category;
            $category=$cate->get_by_id(['id'=>$catid],true,true,false);
            $bra = new Brand;
            $brands=$bra->get_column();
            $this->assign('category', $category);
            $this->assign('brands', $brands);
            return $this->fetch();
        }

    }
}