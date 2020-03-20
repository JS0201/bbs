<?php
namespace app\order\service;

use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request; 
use app\order\common\model\Cart as car;
use app\goods\common\model\Spu;
use think\Db;

class Cart extends Base
{
	public function initialize()
	{
		$this->car = new car;
        $this->spu = new Spu;
	}

    // 合并cookie和数据库数据，同时保存到数据库
    public function cart_merge($buyer_id = 0){
        // 判断是否游客
        if($buyer_id > 0){ // 不是
            // 读取数据库数据
            $items =  $this->car->where(array('buyer_id' => $buyer_id))->order("id DESC")->select()->toArray();
            // 读取本地数据
            $cook_items = json_decode(cookie('cart_recharge'), TRUE);
            // 判断是否需要合并数据
            if($cook_items){

            }
            return $items;
        }

    }




    /**
     * 获取购物车列表
     * @param  integer $buyer_id 会员id(游客为0)
     * @param  string  $sku_ids   skuids (默认空,例：sku_id1[,number1][;sku_id2[,number2]])；多个sku用;分割。数量number可省略，代表购物车记录的件数。为空则获取购物车所有列表
     * @param  boolean $isgroup   是否根据商家分组(默认false)
     * @return [result]
     */
    public function get_cart_lists($buyer_id = 0) {
        $items = $this->car->where('buyer_id', $buyer_id)->order("id DESC")->field('id,spu_id,nums,img')->select()->toArray();
        // dump($items);
        $result = [];
        $all_prices = $numbers = $sold_count = 0;
        foreach ($items as $item) {
            $val = array();
            $spu_id = $item['spu_id'];
            $number = $item['nums'];
            $spu_info = Db::table('bbs_goods_spu')->find(['id' => $spu_id, 'status' => 1]);
            // dump($spu_info);
            unset($spu_info['description']);
             if($spu_info === false || ($spu_info['status'] == 0)) {
                continue;
            };
             $number = min($spu_info['total'], $number);
            $val['spu_id'] = $spu_id;
            $val['number'] = $number;
            $val['_spu_'] = $spu_info;
            $val['cartid'] = $item['id'];

            if($number == 0) {
                $val['issold'] = true;
            }
            $val['prices'] = sprintf("%.2f",$spu_info['min_price'] * $number);
            $result[] = $val;
        }
        return $result;
    }

    /**
     * 购物车添加商品
     * @param  int 		$buyer_id ：会员id (游客为0，默认0)
     * @param  boolean  $buynow ：是否立即购买(默认false)
     * @return [boolean]
     */
    public function cart_add($spu_id, $nums, $buyer_id = 0, $buynow = FALSE) {
        if (empty($spu_id) && empty($nums)) {
            $this->error('请先添加商品数量！');
            return ['message'=>'请先添加商品数量！'];
        };
        $spu_info = $this->spu->where(['id' => $spu_id, 'status' => 1])->find();	
        // 获取商品信息
        if($spu_info){
            $spu_info = $spu_info->toArray();
        }
        if($spu_info['total'] <= $nums){
            return ['message'=>'库存不足!'];
        };
        return $this->_add($spu_id , $nums , $buyer_id ,$buynow, $spu_info['thumb'], $spu_info['brand_name']);
    }

    /* 执行添加购物车 */
    private function _add($spu_id ,$nums ,$buyer_id = 0 ,$buynow = FALSE, $img, $brand) {
        $sqlmap = $data = [];
        if ($buyer_id > 0) {
            $data['buyer_id'] = $sqlmap['buyer_id'] = $buyer_id;
            $data['spu_id'] = $sqlmap['spu_id'] = $spu_id;
            $data['nums'] = $nums;
            // 获取数据
            $cart = $this->car->where($sqlmap)->find();
            // 已存在购物车，修改数据
            if ($cart) { 
                $cart = $cart->toArray();
                $this->car->isUpdate(true)->data($data)->save(array('id' => $cart['id']));
                return 1;
            } else {
                $data['img'] = $img;
                $data['system_time']=time();
                $data['brand_name']= $brand;
                $this->car->isUpdate(false)->save($data);
                return 1;
            }
        }else{
            return ['message'=>'请先登录!'];
        }
    }
    // 库存判断
    public function is_stock($spu_id, $nums){
        //库存验证
        $sku_info = db('goods_sku')->where(array('spu_id' => $spu_id))->column('spu_id, number');
        if(!$sku_info){
            $this->errors = lang('goods_goods_not_exist');
            return false;
        }
        if($sku_info[$spu_id] < $nums){
            $this->errors = lang('goods_stock_insufficient');
            return false;
        }
        return true;
    }


    /**
     * 设置购物车商品数量
     * @param int $spu_id 	商品spu_id
     * @param int $specid  购物车ID
     * @param int $nums 	数量
     * @param int $buyer_id 会员id(游客为0 ,默认0)
     * @return [boolean]
     */
    public function set_nums($spu_id ,$nums ,$buyer_id) {
        $nums = max(0, $nums);
        if ($spu_id < 1) {
            return ['message'=>'商品不存在！'];
        }
        $set_info = $this->car->where("spu_id = {$spu_id}")->find();
        if (!$set_info) {
            return ['message'=>'商品未添加！'];
        }
        if ($nums == 0) {	// 删除该记录
            $result = $this->delpro($spu_id ,$buyer_id);
            if (!$result){
                return ['message'=>'删除失败！'];
            }
        } else {
            $brand_name = $this->spu->where('id',$spu_id)->field('brand_name');
            $data['brand_name']  = $brand_name;
            $data['nums']        = $nums;
            $data['system_time'] = time();
            $result = $this->car->where("spu_id = {$spu_id}")->setField($data);
            if (!$result) {
                return ['message'=>'操作失败！'];
            }
        }
        return 1;


    }


    /**
     * 减除相应购买数量
     * @param  array $params  参数 ：array('spu_id1' => number1 [,'sku_id2' => number2])
     * @param  int   $buyer_id 会员id(游客为0)
     * @return [boolean]
     */
    public function dec_nums($params = [] ,$buyer_id = 0) {
        if (empty($params)) {
            $this->error('商品不存在！');
        }
        foreach($params as $specid => $v) {
            $nums[$specid] = $v['nums'];
        }
        // dump($nums);
        $spec_ids = join(',', array_keys($params));

        $carts = $this->car->where("buyer_id = {$buyer_id} and id in ({$spec_ids})")->field('id,spu_id,nums')->select()->toArray();
        foreach ($carts as $k => $v) {
            $this->set_num($v['spu_id'] ,$v['id'], $v['nums'], $buyer_id);
        }
        return TRUE;
    }

    /**
     * 设置购物车商品数量
     * @param int $sku_id   商品sku_id
     * @param int $specid  购物车ID
     * @param int $nums     数量
     * @param int $buyer_id 会员id(游客为0 ,默认0)
     * @return [boolean]
     */
    public function set_num($spu_id ,$specid,$nums ,$buyer_id = 0) {
        $spu_id = (int) $spu_id;
        $nums = max(0, (int) $nums);
        $buyer_id = (int) $buyer_id;
        if ($spu_id < 1) {
            $this->error('商品不存在');
        }
        //库存验证
        $spu_info=db::table('bbs_goods_spu')->where(['id'=>$spu_id])->column('id,total');

        if(!$spu_info){
            $this->error('商品不存在');
        }

        if($spu_info[$spu_id] < $nums){
           $this->error('库存不足');
        }
        if($buyer_id > 0) {
            $set_info = $this->car->where("spu_id = {$spu_id}")->find();
        if (!$set_info) {
            return ['message'=>'商品未添加！'];
        }
            if ($set_info['nums'] == $nums) {   // 删除该记录
                $result = $this->car->where('id',$specid)->delete();
                if (!$result) return FALSE;
            } else {
                $data['nums']        = $set_info['nums'] - $nums;
                $data['system_time'] = time();
                $result = $this->car->where("id = {$specid}")->setField($data);
                if (!$result) {
                    $this->error('修改失败');
                }
                dump($set_info);
            }
            return $result;
        }

    }



    /**
     * 删除购物车商品
     * @param  int $sku_id 商品sku_id
     * @param  int $buyer_id 会员id(游客为0 ,默认0)
     * @return [boolean]
     */
    public function delpro($spu_id ,$buyer_id) {
        if (!$spu_id) {
            return ['message'=>'商品不存在！'];
        }      
        $sqlmap = [];
        $sqlmap['spu_id'] = $spu_id;
        $result = $this->car->where($sqlmap)->delete();
        if ($result == FALSE) {
            return ['message'=>'商品删除失败！'];
        }
        return 1;
    }

    public function getOne($where) {
        return $this->car->where($where)->find();
    }

}