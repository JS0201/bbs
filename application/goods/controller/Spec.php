<?php
namespace app\goods\controller;
use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request;
use app\goods\service\Spec as spe;

class Spec extends Base
{
    public function index(){
    	$spec = new spe;
        $list=$spec->get_list();
        $this->assign('list',$list);
        return $this->fetch();
    }



    public function add(){
        if(Request::post()) {
        	$spec = new spe;
            if(!$spec->edit(input('post.'))) {
                return ['status'=>0,'message'=>'添加失败！'];
            }
             return ['status'=>1,'message'=>'添加成功！'];
        } else {
            return $this->fetch();
        }
    }

    public function edit(){
    	$spec = new spe;
        $info=$spec->get_find(['id'=>input('id/d')]);
        if(Request::post()) {
            if(!$spec->edit(input('post.'),true)) {
                return ['status'=>0,'message'=>'修改成功！'];
            }
            return ['status'=>0,'message'=>'修改失败！'];
        } else {
            $this->assign('info',$info);
            return $this->fetch();
        }

    }




    public function delete()
    {

        $ids=input('param.id/a');

        if(!$this->service->del($ids)) {
            showmessage($this->service->errors);
        }

        showmessage(lang('_operation_success_'), url('index'), 1);
    }






}