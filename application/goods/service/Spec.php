<?php
namespace app\goods\service;

use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request; 
use app\goods\common\model\Spec as spe;
use think\facade\Db;

class Spec extends Base
{
    public function initialize()
    {
        $this->spec = new spe;
    }

    public function getAll($where)
    {
        return $this->model->getAll($where, 'name, value, img, id');
    }



    public function get_list($sqlmap=[],$order='sort asc,id asc',$page=[]){

        $lists = $this->spec->where($sqlmap)->order($order)->paginate($page);
        return $lists;

    }
    public function edit($data, $isupdate = false, $valid = true, $msg = []){
        $result = $this->spec->isupdate($isupdate)->save($data);
        if ($result === false) {
            return 0;
        }
        return 1;
    }
    public function add($data) {
        return  $this->model->add($data);
    }
    public function updated($where, $data) {
        return $this->model->updated($where, $data);
    }
    public function getOne($where) {
        return $this->model->getOne($where);
    }

    public function get_find($sqlmap){

       if(!$info = $this->spec->where($sqlmap)->find()->toArray()){
            $this->error('参数错误！');
       }
       $info['value']=explode(',',$info['value']);
       return $info;
    }


    public function get_spec_name($sqlmap=[]){

        $result = $this->model->where($sqlmap)->order('sort asc,id asc')->column('id,name,value');
        foreach($result as $k=>$v){
            if($v['value']==''){
                unset($result[$k]);
            }else{
                $result[$k]['value'] = explode(',',$v['value']);
            }
        }

        return $result;
    }


    /**
     * @param array $ids id主键
     * @return bool
     */
    public function del($sqlmap){


        if(empty($sqlmap)){

            $this->errors = lang('_param_error_');
            return false;
        }

        $this->model->destroy($sqlmap);
        // 生成log记录
        $code = AdminLoginFormat($sqlmap);
        AdminLog(1, '', lang("admin_log_goods_spec"), lang('admin_log_goods_delete_spec'), $code);

        return true;
    }
    //获得商品图片
    public function getImg($spu_id, $name, $value)
    {
        $re = '';
        $spec = db("goods_spec")->where(["spu_id"=>$spu_id,"style"=>2,"img"=>array("neq",",")])->find();
        $sql = db("goods_spec")->getLastSql();
        if(!$spec) {
            return $re;
        }
        $imgs = explode(",",$spec['img']);
        $re = $imgs[0];
        return $re;
    }

}