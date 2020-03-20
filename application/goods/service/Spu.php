<?php
namespace app\goods\service;

use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request; 
use app\goods\common\model\Spu as Sp;
use app\goods\common\model\Brand;
use think\Db;

class Spu extends Base
{
    public function initialize()
    {
        $this->spu = new Sp;
        $this->brand = new Brand;
    }
    
    public function getAll() {
         $result = Db::name('bbs_goods_spu')->alias('s')
         ->leftjoin('bbs_goods_category c','s.catid=c.id','LEFT')
         ->leftjoin('bbs_goods_brand b','s.brand_id=b.id','LEFT')
         ->field('s.*,c.name cate_name,b.name brand_name')
         ->select();
         foreach ($result as $key => $value) {
            $result[$key]['imgs']=substr($value['imgs'],0,strpos($value['imgs'],'&'));
         }

        return $result;

    }

    public function getOne($catid) {
         $result = Db::name('bbs_goods_spu')->alias('s')
         ->leftjoin('bbs_goods_category c','s.catid=c.id','LEFT')
         ->leftjoin('bbs_goods_brand b','s.brand_id=b.id','LEFT')
         ->where("c.id","$catid")
         ->field('s.*,c.name cate_name,b.name brand_name')
         ->select();
         foreach ($result as $key => $value) {
            $result[$key]['imgs']=substr($value['imgs'],0,strpos($value['imgs'],'&'));
         }

         return $result;
     }

     public function getGood($where)
     {
         return $this->spu->where($where)->find();
     }


     public function add($goods)
     {
       $message=[];
       $data=[];
       $data['imgs']='';
       $files = request()->file('image');
       foreach($files as $file){
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( 'uploads/');
        if($info){
         $message[] = $info->getsavename(); 
     }else{
            // 上传失败获取错误信息
        echo $file->getError();
    }    
}
    foreach ($message as $key => $value) {
        $data['imgs'] .= $value.'&';
    }
    $goods['imgs'] = $data['imgs'];
    $brand_id = $goods['brand_id'];
    $result=[];
    $result = Brand::get(function($query) use ($brand_id){
        $query->field('name')->where('id',$brand_id);
    });
    $goods['brand_name'] = $result['name'];
    $goods['thumb'] = substr($goods['imgs'],0,strpos($goods['imgs'], '&'));
    return Sp::create($goods);
}





/**
    /**
     * 创建订单商品
     * @param  array    $params 订单商品相关参数
     * @return [boolean]
     */
    public function create_all($params) {
        if (count(array_filter($params)) < 1) {
            $this->errors = lang('order_goods_empty');
            return FALSE;
        }
        foreach ($params as $key => $val) {
            $sku_info['sku_thumb'] = $val['sku_thumb'];
            $sku_info['sku_name'] = $val['sku_name'];
            $sku_info['sku_spec'] = $val['sku_spec'];
            $sku_info['sku_price'] = $val['sku_price'];
            $sku_info['real_price'] = $val['real_price'];
            $val['spu_id'] = $val['spu_id'];
            $val['sku_info'] = json_encode($sku_info);
            $result = $this->model->insert($val);
            if (!$result) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * 指定商品减少库存
     * @param [int] $id 商品ID
     * @param [int] $number变更数量
     * @return bool
     */
    public function set_dec_number($spu_id, $number) {
        $spu_id = (int) $spu_id;
        $number = (int) $number;
        if($spu_id < 1 || $number < 1) {
            $this->error('参数错误！');
        }
        // 查询数据
        $data = $this->spu->where(array('id' => $spu_id))->find();
        if(!$data){
            $this->error('商品不存在！');
        }
        $data = $data->toArray();
        // 判断库存是否足够
        if($data['total'] < $number){
            $this->error('库存不足！');
        }
        $result = $this->spu->where(array('id' => $spu_id))->setDec('total', $number);
        if(!$result){
            return false;
        }else{
            return true;
        }
    }

    public function updated($where, $data)
    {
        return $this->model->updated($where, $data);
    }
    public function getList($where, $select)
    {
        return $this->model->getList($where, $select);
    }
}