<?php
namespace app\order\controller;
use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request;
use app\order\service\Cart as car;
use app\order\common\model\Cart as ca;

class Cart extends Base{
    protected function initialize()
    {   
       parent::initialize();
       $this->isLogin();
    }

    public function index(){
        $car = new car;
        $buyer_id = Request::session('user_id');
        $cart_recharge = $car->get_cart_lists($buyer_id);
        $this->assign('title','购物车');
        $this->assign('cart_recharge',$cart_recharge);
        return $this->fetch();
    }

    /*
        添加加购物车
     */
    public function cart_add(){
        $car = new car;
        $member = Request::Session('user_id');
            $post = input('post.');
            $result = $car->cart_add($post['spu_id'], $post['nums'], $member);
            if ($result != 1)
            {
                return ['message'=>'添加失败！'.$result['message']];
            }else{
                return ['message'=>'添加成功！'];
            }
    }


    /*
        设置购物车商品数量
        @param int   $spu_id 商品spu_id
        @param int   $nums   要设置的数量
        @return [boolean]
     */
    public function set_nums() {
        $car = new car;
        $buyer_id = Request::Session('user_id');
        $result = $car->set_nums(input('spu_id/d') ,input('nums/d') , $buyer_id);
        if ($result != 1)
            {
                return ['message'=>$result['message']];
            }else{
                return ['message'=>'操作成功！'];
        }

    }


    /**
     * 删除购物车商品
     * @param  int 	$sku_id 商品sku_id
     * @return [boolean]
     */
    public function delpro() {
        $spec_id = input('post.spec_id');
        $type = input('post.type');
        if(strstr($spec_id, ',')) {
            $sku_arr = explode(',', $spec_id);
            foreach($sku_arr as $k => $v) {
                $result = $this->service->delpro($v, (int) $this->member['id'], $type);
            }
        }else{
            $result = $this->service->delpro($spec_id, (int) $this->member['id'], $type);
        }
        if (!$result) showmessage($this->service->errors);
        showmessage(lang('_operation_success_'),'',1,'json');
    }

}