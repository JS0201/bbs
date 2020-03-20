<?php
namespace app\goods\controller;
use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request;
use app\goods\service\Category as cate;
use app\index\service\Member;
use app\goods\common\model\Category as mcate;


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
            $cate = new cate;
            if($data=Request::post()){
            if($data['path_id']=='顶级分类') //顶级分类
            {
                $data['path_id']='0,';
            }
            
            if (!$result=$cate->add($data,true,true)) {
                $this->error('添加失败，请检查！');
            }
            $this->success('添加成功！','index');
        }
        if($parent_id=input('param.parent_id/d'))
        {
            $parent_info=$cate->getone($parent_id);
            $this->assign('parent_info',$parent_info);
        }
        $info=$cate->getall();
        $this->assign('info',$info);
        return $this->fetch();
    }

        /*
        添加子分类
     */
        public function child_add(){
            $cate = new cate;
            if($data=Request::post()){
                if (!$result=$cate->add($data,true,true)) {
                    $this->error('添加失败，请检查！');
                }
                $this->success('添加成功！','index');
            }
            $parent_id=input('param.parent_id/d');
            $info=$cate->getone($parent_id);
            $this->assign('info',$info);
            return $this->fetch(); 
        }
        

    /*
        编辑分类
     */
        public function edit(){
            if($data=Request::post())
            {   
                $cate = new cate;
                if($cate->edit($data))
                {
                    echo"<script>alert('修改成功！');window.location.href='index'</script>";

                }else{
                    $this->error('修改失败！','index');
                }
            }else{
                $parent_id=input('param.parent_id/d');
                if($parent_id){
                    $cate = new cate;
                    $parent_info=$cate->get_by_id(['id'=>$parent_id],true,true,true);
                    $info=[];
                    $info['parent_id']=$parent_info['id'];
                    $info['path_id']=$parent_info['path_id'];
                    $info['parent_name']=$parent_info['parent_name'];
                    $this->assign('info',$info);
                    return $this->fetch();
                }
            }

        }

     /*
        删除分类
     */
        public function delete()
        {

        $ids = input('param.id/a');
        $brand = new cate;
        if(!$brand->del($ids)) {
            $this->redirect('index');
        }
            $this->redirect('index');
        }
    }
