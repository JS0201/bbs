<?php
namespace app\order\controller;
use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request;
use app\order\service\Cart as car;
use app\index\service\Address;
use app\goods\service\Spu;
use app\order\service\Order as ord;
use think\Db;

class Order extends Base{
    protected function initialize()
    {
       parent::initialize();
       $this->isLogin();
    }

    //我的订单
    public function allorder()
    {
        $this->assign('title','我的订单');
        return $this->fetch();
    }

    //创建订单
    public function create() {
        // 会员收货地区id，便于加载配送物流
        $request = input('post.');
        $uid = Request::session('user_id');
        $order = new ord;
        $result =  $order->temp_creates($uid, $request['params'], $request['addressid'], $request['remark'], 1,[], true, $request['isbuy']);
       if (!$result) {
           return ['status'=>0,'message'=>'创建订单失败！'];
       }
       return['status'=>1,'message'=>'创建订单成功！'];
    }


    //确认订单
    public function confirm()
    {

        $this->assign('title', '确认订单');
        $money = input('get.money');
        $gid = explode(',', input('get.obj'));
        if (!$gid && $money) {
            $this->error('参数错误！');
        }
        //默认地址
        $addressService = new Address();
        $uid = Request::session('user_id');
        $address = $addressService->getDef($uid);

        $good = $list = array();
        $spuService = new Spu();
        $car = new car;
        if ($gid) { //购物车确认订单
            foreach ($gid as $k => $v) {
                $cartArr = $car->getOne(array('id' => $v));
                if (!$cartArr) {
                    $this->redirect("/order/cart/index");
                }
                $spuArr = $spuService->getGood(array("id" => $cartArr['spu_id']));
                $spuArr['price'] = $spuArr['min_price']*$cartArr['nums'];
                $spuArr['sum'] = $cartArr['nums'];
                $spuArr['cart_id'] = $v;
                $spuArr['spu_id'] = $spuArr['id'];
                $spuArr['thumb'] = $cartArr['img'];
                $spuArr['money'] = $money;
                $list[$k] = $spuArr;
                if (isset($good['express'])) {
                    $good['express'] += $spuArr['express'];   //运费待整改
                } else {
                    $good['express'] = $spuArr['express'];
                }
                if (isset($good['price'])) {
                    $good['price'] += $spuArr['price'];
                } else {
                    $good['price'] = $spuArr['price'];
                }
            }
         }
            $this->assign('address', $address);
            $this->assign('list', $list);
            $this->assign('good', $good);
            return $this->fetch();
        }

    public function detailConfirm()
    {
        $addressService = new Address();
        $uid = Request::session('user_id');
        $address = $addressService->getDef($uid);
        $data = Request::param();
        $spu = Db::table('bbs_goods_spu')->where(array('id'=>$data['spu_id']))->find();
        $good['thumb'] = $spu['thumb'];
        $good['name'] = $spu['name'];
        $good['sum'] = $data['sum'];
        $good['min_price'] = $spu['min_price'];
        $good['price'] = $data['sum'] * $spu['min_price'];
        $good['spu_id'] = $spu['id'];
        $good['express'] = $spu['express'];
        $this->assign('good', $good);
        $this->assign('address', $address);
        $this->assign('title', '确认订单');
        return $this->fetch('detail_confirm');
    }

    public function deleted()
    {
        $sn = input('post.sn');
        $action = input('post.action');
        $status = input('post.status');
        $re = $this->service->set_order($sn, $action, $status);
        if(!$re) {
            showmessage($this->errors);
        }
        showmessage(lang('_operation_success_'),'',1,'json');
    }

    public function pay()
    {
        $sn = input('post.sn');
        $action = input('post.action');
        $status = input('post.status');
        $re = $this->service->set_order($sn, $action, $status);
        if(!$re) {
            showmessage($this->errors);
        }
        showmessage(lang('_operation_success_'),'',1,'json');
    }
    public function sureget()
    {
        $sn = input('post.sn');
        $action = input('post.action');
        $status = input('post.status');
        $re = $this->service->set_order($sn, $action, $status);
        if(!$re) {
            showmessage($this->errors);
        }
        showmessage(lang('_operation_success_'),'',1,'json');
    }

}