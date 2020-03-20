<?php
namespace app\order\service;

use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request;
use app\goods\service\Spu as sp;
use app\order\service\Cart;
use app\order\service\Log;
use app\order\service\Track;
use app\admin\service\MoneyFinance;
use app\index\service\Member;
use app\order\common\model\Cart as car;
use app\goods\common\model\Spu;
use app\common\model\Address;
use app\goods\common\model\Order as ord;
use app\order\common\model\Child;
use app\common\model\user;
use think\Db;

class Order extends Base
{
    public function initialize()
    {
        $this->order = new ord;
        $this->child_order = new Child;
        $this->spumodel = new Spu;
        $this->address = new Address;
        $this->user = new user;
    }

    public function get_list($sqlmap=[],$page=[]){

        $lists = $this->order->where($sqlmap)->order('id desc')->select();
        return $lists;

    }


    public function get_find($sqlmap,$field=""){

        return $this->order->where($sqlmap)->field($field)->find();

    }

    /**
     * 生成查询条件
     * @param  $options['type'] (1:待付款|2:待确认|3:待发货|4:待收货|5:已完成|6:已取消|7:已回收|8:已删除)
     * @param  $options['keyword'] 	关键词(订单号|收货人姓名|收货人手机)
     * @return [$sqlmap]
     */
    public function build_sqlmap($options) {;
        $sqlmap = array();
        if(isset($options['type'])){
            $type = $options['type'];
            if (isset($type) && $type > 0) {
            switch ($type) {
                // 待付款
                case 1:
                    $sqlmap['pay_type']   = 1;
                    $sqlmap['status']     = 1;
                    $sqlmap['pay_status'] = 0;
                    break;
                // 待确认
                case 2:

                    $sqlmap = 'status=1 and (pay_type=2 or pay_type=1 and pay_status=1) and confirm_status<>2';
                    break;
                // 待发货
                case 3:
                $sqlmap = 'status=1 and (confirm_status in (1,2) and delivery_status in(0,1))';
                    break;
                // 待收货
                case 4:
                    $sqlmap['status'] = 1;
                    $sqlmap['delivery_status'] = 2;
                    $sqlmap['finish_status'] = 0;

                    break;
                // 已完成
                case 5:
                    $sqlmap['status'] = 1;
                    $sqlmap['finish_status'] = 2;
                    break;
                // 已取消
                case 6:
                    $sqlmap['status'] = 2;
                    break;
                // 已作废
                case 7:
                    $sqlmap['status'] = 3;
                    break;
                // 前台已删除
                case 8:
                    $sqlmap['status'] = 4;
            }
        } 
        }else{
            $sqlmap = '';
        }
        return $sqlmap; 
    }

    /**
     * @param int $buyer_id 会员ID
     * @param $params 商品id 数量 规格
     * @param $address_id 地址ID
     * @param int $pay_type 支付方式
     * @param bool $submit 是否创建 (为false时 获取订单结算信息，为true时 创建订单)
     * @param int $type  类型 直接购买 或 购物车结算
     */
    public function temp_creates($user, $params, $address_id, $remark='', $pay_type = 1,$invoices = array(), $submit = false, $type = 0){
        if(empty($params)) {
            return $this->error('该订单商品不存在！');
        }
        //获取商品数据
        $order_list = array();
        foreach($params as $k => $v) {
            // 查询主商品信息
            $goods_spu = $this->spumodel->where(array('id' => $v['spu_id']))->find();
            if($goods_spu) $goods_spu = $goods_spu->toArray();
            if(isset($order_list[$goods_spu['admin_id']]['spu_total'])) {
                $order_list[$goods_spu['admin_id']]['spu_total'] += $v['sum'] * $goods_spu['min_price']; //商品总价
            }else{
                $order_list[$goods_spu['admin_id']]['spu_total'] = $v['sum'] * $goods_spu['min_price'];
            }
            if(!isset($order_type)) {
                $order_type = $type;
            }
            $order_list['spu'] = $goods_spu; 
            $sku_arr['thumb'] = $v['thumb'];
            $order_list[$goods_spu['admin_id']]['spus'][0]['spu_list'][$k]['admin_id'] = $goods_spu['admin_id'];
            $order_list[$goods_spu['admin_id']]['spus'][0]['spu_list'][$k]['spu_id'] = $v['spu_id'];
            $order_list[$goods_spu['admin_id']]['spus'][0]['spu_list'][$k]['cart_id'] = $v['cart_id'];
            $order_list[$goods_spu['admin_id']]['spus'][0]['spu_list'][$k]['number'] = $v['sum'];
            $order_list[$goods_spu['admin_id']]['spus'][0]['spu_list'][$k]['thumb'] = $v['thumb'];
            $order_list[$goods_spu['admin_id']]['spus'][0]['spu_list'][$k]['spu_name'] = $goods_spu['name'];
            $order_list[$goods_spu['admin_id']]['spus'][0]['spu_list'][$k]['min_price'] = $goods_spu['min_price'];
            $pu_id[] = $v['spu_id'];
        }

            $spuid = implode(',',$pu_id);
// 写入订单表
        if($submit === true) { 
            $real_amount = 0;
            $this->order->startTrans();
            $order_list[$goods_spu['admin_id']]['deliverys_total'] = 0;
            // 循环拆分订单
            foreach ($order_list as $key => $data) {
                // 订单总运费
                $num = $goods_spu['express'];
                $data['deliverys_total'] += sprintf("%.2f", $num);
                // 订单应付总额
                $data['real_amount'] = sprintf("%.2f", max(0, $data['spu_total'] + $data['deliverys_total']));
                $real_amount += $data['real_amount'];
                // 读取收货人信息,-1表示不需要收货地址
                if ($address_id !== -1) {
                    $member_address = $this->address->where(array('id' => $address_id, 'mid' => $user))->find();
                    if (!$member_address) {
                        $this->error('收货地址不存在');
                    }
                }
                // 组装主订单信息
                $order_sn = $this->_build_order_sn();
                $source = defined('MOBILE') ? (defined('IS_WECHAT') ? 3 : 2) : 1;
                $name = Db::table('bbs_user')->where(['id' => $user])->field('name')->find();
                $username = $name['name'];
                $order_data = array(
                    'sn' => $order_sn,
                    'buyer_id' => $user,
                    'admin_id' => $key,
                    'spu_id' => $spuid,
                    'user' => $username,
                    'seller_ids' => 0,
                    'source' => $source,    // 订单来源(1：标准，2：移动端)
                    'pay_type' => $pay_type,        // 支付类型(1:在线支付 , 2:货到付款)
                    'sku_amount' => $data['spu_total'], // 商品总额
                    'real_amount' => $data['real_amount'],        // 应付总额
                    'delivery_amount' => $data['deliverys_total'],    // 总运费
                    'promot_amount' => 0,    // 所有优惠总额
                    'remark' => $remark,
                    'address_name' => $member_address['name'] ? $member_address['name'] : '',
                    'address_mobile' => $member_address['mobile'] ? $member_address['mobile'] : '',
                    'address_detail' => $member_address['local'] ? $member_address['local'] : '',
                    'address_district_ids' => 0,
                    'confirm_status' => 1, //订单直接确认状态
                    'system_time' => time()
                );
                $this->order->isUpdate(false)->data($order_data)->save();
                $oid = $this->order->id;
                if (!$oid) {
                    $this->order->rollback();
                    $this->error('订单提交失败！');
                }
                //减少商品库存
            foreach ($data['spus'] as $k => $val) { 
            foreach ($val['spu_list'] as $cart) {
                    $sp = new sp;
                    $no_stock = $sp->set_dec_number($cart['spu_id'], $cart['number']);
                    if(!$no_stock){
                        $this->error('库存减少失败！');
                    }
                    $load_decs[$cart['cart_id']]['nums'] = $cart['number'];
                    $load_decs[$cart['cart_id']]['cart_id'] = $cart['cart_id'];
                }
            //创建子订单     
            foreach ($val['spu_list'] as $v) {
            $child_order_data = array(
                'order_sn'    => $order_sn,
                'sub_sn'      => $this->_build_order_sn(true),
                'buyer_id'    => $user,
                'admin_id'    => $user,
                'seller_id'   => $user,
                'spu_name'    => $v['spu_name'],
                'spu_id'      => $v['spu_id'],
                'sku_thumb'   => $v['thumb'],
                'spu_price'   => $v['min_price'],
                'real_price'  => $v['min_price'] * $v ['number'],
                'buy_nums'    => $v['number'],
                'delivery_template_id'    => 0,
                );
            $this->child_order->isUpdate(false)->data($child_order_data)->save();
        }

         
            // 清除购物车已购买数据
            if($type != 1){
                if($load_decs) {
                $cart = new Cart;
                $cart->dec_nums($load_decs ,$user);
            }
            }
            $operator = array();
            $identity = Request::session('is_admin');
            if($identity == 1){
                $adm = $this->user->where('id',$user)->find();
                $operator['id'] = $adm['id'];
                $operator['username'] = $adm['name'];
                $operator['operator_type'] = $adm['is_admin'];
            }else{
                $adm = $this->user->where('id',$user)->find();
                $operator['id'] = $adm['id'];
                $operator['username'] = $adm['name'];
                $operator['operator_type'] = $adm['is_admin'];
            }
            //  //订单日志
            $da = array();
            $da['order_sn']      = $order_sn;
            $da['sub_sn']        = $order_sn;
            $da['action']        = '创建订单';
            $da['operator_id']   = $operator['id'];
            $da['operator_name'] = $operator['username'];
            $da['operator_type'] = $operator['operator_type'];
            $da['msg']           = '提交购买商品并生成订单';
            $log = new Log();
            $log->add($da);
            $track =  new Track();
            // 订单跟踪
            $track_msg = $pay_type == 1 ? '系统正在等待付款': '请等待系统确认';
            $tr =  $track->add($order_sn ,$da['sub_sn'], '您提交了订单，'.$track_msg);
            }
            if ($tr == FALSE) {
                    // 回滚删除之前的订单信息
                    $this->order->rollback();
                    return FALSE;
                }
            //立即购买
            if (isset($type)) { 
                    //支付
                    $finance_service = new MoneyFinance();
                    $finance_re = $finance_service->setFinance($user, $data['real_amount'], 'money', '订单付款，订单号：' . $order_sn, '', false, 1, 0);
                    // 判断是否金额不足
                    if (!$finance_re) { // 不足
                        $this->error('余额不足，请充值！');
                    }
                    else{
                        //支付成功。修改订单状态
                        $order_re = $this->order->where('sn',$order_sn)->update( array('pay_status' => '1','pay_time' => time()));
                        $member_re = true;
                    }
                        $this->order->commit();
                         return true;
                }

            }
        }
        
    }

/**
     * 根据日期生成唯一订单号
     * @param boolean $refresh  是否刷新再生成
     * @return string
     */
    private function _build_order_sn($refresh = FALSE) {
        if ($refresh == TRUE) {
            return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 12);
        }
        return date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
    }

/**
     * 设置订单
     * @param  string   $sn         订单号(确认支付时传主订单号)
     * @param  string   $action     操作类型
     *         (order:订单 || pay:支付 || confirm:确认 || delivery:发货 || finish:完成)
     * @param  int      $status     状态(只有$action = 'order'时必填)
     * @param  array    $options    附加参数
     * @return [boolean]
     */
    public function set_order($sn = '',$action = '',$status = 0 ,$options = []) {
        $sn     = (string) trim($sn);
        $action = (string) trim($action);
        $status = (int) $status;
        $msg    = (string) trim($options['msg']);
        unset($options['msg']);
        if (empty($sn)) {
            return FALSE;
        }
        if (empty($action)) {
            return FALSE;
        }
        if (!in_array($action, array('order','pay','confirm','delivery','finish'))) {
            return FALSE;
        }
        // 检测订单是否存在
        $this->order = $this->order->where(['sn' => $sn])->find();
        if (!$this->order) {
            return FALSE;
        }

        // 获取订单状态
        $this->order['_status'] = $this->get_status($this->order);

        switch ($action) {
            case 'order':   // (2：已取消，3：已回收，4：已删除)
                $result = $this->_order($status ,$options);
                // 后台删除订单直接返回
                if ($status == 4 && $result !== FALSE) {
                    return TRUE;
                }
                break;
            case 'pay': // 针对所有子订单操作
                $result = $this->_pay($sn);
                break;
            case 'confirm':
                $result = $this->_confirm();
                break;
            case 'delivery':
                $result = $this->_delivery($options);
                break;
            case 'finish':
                $result = $this->_finish($options);
                break;
        }
        if ($result === FALSE) return FALSE;
        // 订单日志
        $operator = Request::session('user_name'); // 获取操作者信息
        $data = [];

        $data['order_sn'] = $this->order['sn'];
        $data['sub_sn']   = $this->order['sn'];
        $data['action']        = $result;
        $data['operator_id']   = Request::session('user_id');
        $data['operator_name'] = Request::session('user_name');
        $data['operator_type'] = Request::session('is_admin');
        $data['msg']           = $msg;
        $log = new Log();
        $log->add($data);
        return TRUE;
    }

protected function get_status($order) {

        $arr = [];

        switch ($order['status']) {
            case 2: // 已取消
                $arr['now']  = 'cancel';    // 已取消
                $arr['wait'] = 'recycle';   // 已回收
                break;
            case 3: // 已回收
                $arr['now']  = 'recycle';
                $arr['wait'] = 'delete';
                break;
            case 4: // 前台用户已删除
                $arr['now']  = 'delete';
                $arr['wait'] = '';
                break;
            default:    // 正常状态


                if ($order['pay_type'] == 1 && $order['pay_status'] == 0) {
                    $arr['now']  = 'create';
                    $arr['wait'] = 'pay';
                } else if ($order['finish_status'] == 2){
                    $arr['now']  = 'all_finish';
                    $arr['wait'] = 'aftermarket';
                } else if (($order['pay_type'] == 1 && $order['pay_status'] == 1 && $order['confirm_status'] == 0) || ($order['pay_type'] == 2 && $order['confirm_status'] == 0)) {
                    $arr['now']  = ($order['pay_type'] == 1) ? 'pay' :'create';
                    $arr['wait'] = 'load_confirm';  // 待确认
                }else if ($order['confirm_status'] == 1) {
                    $arr['now']  = 'part_confirm';  // 部分确认
                    $arr['wait'] = 'all_confirm';   // 已确认
                }else if ($order['finish_status'] == 0 && $order['confirm_status'] == 2) {
                    if ($order['delivery_status'] == 1) {
                        $arr['now']  = 'part_delivery'; // 部分发货
                        $arr['wait'] = 'all_delivery';  // 已发货
                    } else if ($order['delivery_status'] == 2) {
                        $arr['now']  = 'all_delivery';  // 已发货
                        $arr['wait'] = 'load_finish';        // 确认完成
                    } else {
                        $arr['now']  = 'all_confirm';
                        $arr['wait'] = 'load_delivery';
                    }
                } else if ($order['finish_status'] == 1) {
                    $arr['now']  = 'part_finish';   // 部分完成
                    $arr['wait'] = 'all_finish';    //  已完成
                }
                break;
        }



        return $arr;
    }

    /**
     * 发货操作
     * @param  array  $options
     *                      is_choise ：是否选择物流
     *                      delivery_id ：物流主键
     *                      delivery_sn ：快递单号
     *                      o_sku_ids ：要发货的订单商品ids(多个以 ，分割)
     * @return [string]
     */
    private function _delivery($options = []) {
        $order = $this->order;
        $is_choise     = (int) $options['is_choise'];   // 是否选择物流
        $delivery_id   = (int) $options['delivery_id'];
        $delivery_sn   = (string) trim($options['delivery_sn']);
        $sn        = (string) trim($options['sn']);


        if ($is_choise === 1) {
            if ($delivery_id < 1) {
                return FALSE;
            }
            if (empty($delivery_sn)) {
                return FALSE;
            }
            $delivery_name = db::table('bbs_delivery')->where(['id'=>$delivery_id])->value('name');
        } else {
            $delivery_id   = 0;
            $delivery_name = '无需物流运输';
            $delivery_sn   = '';
        }



        //全部发货，获取id
        $o_sku_ids=$this->child_order->where(['order_sn'=>$sn])->column('id');
        $data = $sqlmap = array();
        // 创建订单物流信息
        $data['o_sku_ids']     = implode(',',$o_sku_ids);
        $data['sn']        = $order['sn'];
        $data['sub_sn']        = $order['sn'];
        $data['delivery_id']   = $delivery_id;
        $data['delivery_name'] = $delivery_name;
        $data['delivery_sn']   = $delivery_sn;
        $data['delivery_time'] = time();

        db::table('bbs_order_delivery')->insert($data);

        $addid=db::table('bbs_order_delivery')->getLastInsID();

        if (!$addid) {
            return FALSE;
        }
        /* 标记订单商品为已发货状态，并关联订单物流id */
        $da = [];
        $da['delivery_id'] = $addid;
        $da['delivery_status'] = 1;
        $ids = $data['o_sku_ids'];
        // $this->child_order->where("id in ($ids)")->setField($da);
        $data=[];
        $data['delivery_status'] = 2;
        $data['delivery_time'] = time();
        $result = $this->order->isUpdate(true)->save($data,['sn' => $order['sn']]);
        if (!$result) {
            return FALSE;
        }


        // // 如果后台设置发货减库存 => 减库存
        // $stock_change = config('cache.stock_change');
        // if ($stock_change != NULL && $stock_change == 2) {
        //     foreach ($o_sku_ids as $k => $id) {

        //         $o_sku = model('order/Sku')->where(['id' => $id])->field('sku_id,buy_nums')->find();
        //         model('goods/Sku','service')->set_dec_number($o_sku['sku_id'],$o_sku['buy_nums']);
        //     }
        // }
        
        // 物流跟踪
        $string = '';
        if($is_choise==1){
            $string = '快递单号：'.$delivery_sn;
        }
        $order_track_service = new Track;
        $order_track_service->add($order['sn'] ,$order['sn'] , '您的订单配货完毕，已经发货。'.$string,0,$addid);
        return '订单发货';
    }


/**
     * 确认收货(完成)
     * @param  array  $options
     *                  o_delivery_id ：订单发货主键id
     * @return [string]
     */
    private function _finish($options = array()) {
        $site_name = '网上书店';
        $order = $this->order;
        if ($order['finish_status'] == 2 || $order['delivery_status'] == 0) {
            return FALSE;
        }

        // 标记子订单为已完成
        $data = [];
        $data['finish_status'] = 2;
        $data['finish_time'] = time();
        $string = '感谢您在'.$site_name.'购物，欢迎您的再次光临';
        $result = $this->order->isUpdate(true)->save($data,['sn' => $order['sn']]);
        // 标记所有订单物流为已收货 (order_delivery & order_sku)


        $sqlmap = $data = [];
        $sqlmap['sub_sn'] = $order['sn'];
        $data['isreceive'] = 1;
        $data['receive_time'] = time();
        $result = db('bbs_order_delivery')->where($sqlmap)->data($data)->update();
        $this->child_order->where($sqlmap)->setField('delivery_status' ,2);
        $delivery_id = $this->child_order->where($sqlmap)->field('delivery_id')->find();

        // 订单跟踪
    
                if($order['delivery_status'] == 2 ){
                    $track = new Track;
                    $track->add($order['sn'] ,$order['sn'] , $string,0,$delivery_id);
                }

        return '完成订单';
    }

/* 订单操作 */
    private function _order($status ,$options) {
        $order = $this->order;
        $data = $sqlmap =[];
        switch ($status) {
            case 2: // 取消订单
                $string = '您的订单已取消';
                if ($order['status'] != 1 || $order['delivery_status'] != 0) {
                    return FALSE;
                }
                /* 在线支付：取消整个订单，货到付款：取消当前子订单 */
                $data['status'] = 2;
                $data['cancel_time'] = time();
                if ($order['pay_type'] == 1) {
                    // 标记所有子订单为已取消
                    $this->order->save($data,['sn' => $order['sn']]);
                    // 主订单信息
                    $order_main = $this->order->where(['sn' =>$order['sn']])->find();
                    /* 未发货&已付款的&是否退款到账户余额 ==> 退款到账户余额 */
                    if ($order['delivery_status'] == 0 && $order['pay_status'] == 1 && $options['isrefund'] == 1) {
                        $member = new Member();
                        $member->change_account($order['buyer_id'],'money',$order['real_amount'],'取消订单退款,订单号:'.$order['sn']);

                        $string = '您的订单已取消，已退款到您的账户余额，请查收';
                    }

                } else {
                   //货到付款==>直接取消订单
                    $this->order->where(array('sn' => $order['sn']))->save($data);
                }

                /* 后台设置为下单减库存 ==> goods_sku加上库存 */
                // $stock_change = input('cache.stock_change');
                // if (isset($stock_change) && $stock_change == 0) {

                //     $skuids = model('order/Sku')->where(['order_sn' => $order['sn'],'buyer_id' => $order['buyer_id']])->column('sku_id,buy_nums');
                //     if ($skuids) {
                //         foreach ($skuids as $skuid => $num) {
                //             model('goods/Sku','service')->set_inc_number($skuid ,$num);
                //         }
                //     }
                // }
                return $string;
                break;

            case 3: // 订单回收站
                if ($order['status'] != 2) {
                    return FALSE;
                }
                // 标记当前子订单为已回收
                $data['status'] = 3;
                $data['cancel_time'] = time();
                $result = $this->order->save($data,['sn' => $order['sn']]);
                if (!$result) {
                    return FALSE;
                }
                return '您的订单已放入回收站';
                break;

            /* 订单删除 */
            case 4:
                if ($order['status'] != 3) {
                    $this->errors = lang('_valid_access_');
                    return FALSE;
                }
                // 前台用户删除的只更改状态，管理员删除的需删除所有订单相关的信息
                if (defined('IN_ADMIN')) {
                    // 删除子订单
                    $sqlmap['sn'] = $order['sn'];
                    $sqlmap['status'] = 3;
                    $result = $this->model->where($sqlmap)->delete();
                    if (!$result) {
                        $this->errors = lang('delete_order_error');
                        return FALSE;
                    }
                    // 删除订单商品
                    db('order_sku')->where(array('sub_sn' => $order['sn']))->delete();
                    // 删除订单日志
                    db('order_log')->where(array('sub_sn' => $order['sn']))->delete();
                    // 删除订单跟踪
                    db('order_track')->where(array('sub_sn' => $order['sn']))->delete();
                    //删除物流
                    db('order_delivery')->where(array('sub_sn' => $order['sn']))->delete();

                    return '订单删除成功';
                } else {
                    // 标记当前订单为删除
                    $data['status'] = 4;
                    $data['cancel_time'] = time();
                    $result = $this->model->save($data,['sn' => $order['sn']]);
                    if (!$result) {
                        $this->errors = lang('_operation_fail_');
                        return FALSE;
                    }

                    return '您的订单已从回收站删除';
                }
                break;
        }
    }


}