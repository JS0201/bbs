<?php
namespace app\goods\controller;
use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request;
use app\goods\service\Brand as bra;
use app\index\service\Member;


class Brand extends Base
{
    /*
        分类列表
     */
    public function index(){
        $bra = new bra;
        $list=$bra->category_lists();
        $this->assign('list',$list);
        return $this->fetch();
    }

    /*
        添加分类
     */
	public function add(){
        if($data=Request::post()){
            $brand = new bra;
            if (!$brand->edit($data)) {
            return ['status'=>0,'message'=>'添加失败！'];
            }
            return ['status'=>1,'message'=>'添加成功！'];
        }
        // else{
        //     // $parent_id=input('param.parent_id/d');

        //     // if($parent_id){

        //     //     $parent_info=$this->service->get_by_id(['id'=>$parent_id],true,true);

        //     //     $info=[];
        //     //     $info['parent_id']=$parent_info['id'];
        //     //     $info['path_id']=$parent_info['path_id'];
        //     //     $info['parent_name']=$parent_info['parent_name'];
        //     //     $this->assign('info',$info);

        //     }
            return $this->fetch();
        }

    /*
        编辑分类
     */
        public function edit()
    {

        $sqlmap = [];
        $sqlmap['id'] = input('id/d');
        $brand = new bra;
        $info = $brand->get_by_id($sqlmap);
        if(Request::post()) {

            if(!$brand->edit(input('post.'), true)) {
                return ['status'=>0,'message'=>'修改失败！'];
            }
            return ['status'=>1,'message'=>'修改成功！','id'=>$sqlmap['id']];
        } else {
            $this->assign('info', $info);
            return $this->fetch();
        }
    }

        public function delete()
    	{

        $ids = input('param.id/a');
        $brand = new bra;
        if(!$brand->del($ids)) {
            $this->redirect('index');
        }
            $this->redirect('index');
    	}

    }
