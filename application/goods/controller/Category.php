<?php
namespace app\goods\controller;
use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request;
use app\goods\service\Category as cate;
use app\index\service\Member;


class Category extends Base
{
    /*
        分类列表
     */
    public function index(){
        $cate = new cate;
        $list=$cate->category_lists();
        $this->assign('list',$list);
        return $this->fetch();
    }

    /*
        添加分类
     */
	public function add(){
        if($data=Request::post()){
            $cate = new cate;
            if (!$cate->edit($data)) {
            $this->error('添加失败，请检查！');
            }
            $this->success('添加成功！');
        }else{
            // $parent_id=input('param.parent_id/d');

            // if($parent_id){

            //     $parent_info=$this->service->get_by_id(['id'=>$parent_id],true,true);

            //     $info=[];
            //     $info['parent_id']=$parent_info['id'];
            //     $info['path_id']=$parent_info['path_id'];
            //     $info['parent_name']=$parent_info['parent_name'];
            //     $this->assign('info',$info);

            }
            return $this->fetch('edit');
        }
    }
