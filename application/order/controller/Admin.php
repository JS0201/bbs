<?php
namespace app\order\controller;
use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request;
use app\order\service\Order;
use app\order\service\Log;
use think\Db;
use app\order\common\model\Cart as ca;

class Admin extends Base{
    protected function initialize()
    {   
       parent::initialize();
       $this->isLogin();
    }


    public function index(){
    	$order_service = new Order();
        $sqlmap = $order_service->build_sqlmap(input('get.'));
        $list=$order_service->get_list($sqlmap);
        $this->assign('list',$list);
        return $this->fetch('admin_index');
    }



    public function detail(){
        $sn=input('sn/s');
        $sqlmap['sn'] = $sn;
        $order_service = new Order();
        $log_service = new Log();
        $order = $order_service->get_find($sqlmap);
        if (!$order) $this->error('订单不存在！');
        //查询商品总价、数量
        $child = db::table('bbs_order_child')->where('order_sn',$sqlmap['sn'])->select();
        
        $order_logs = $log_service->get_by_order_sn($order['sn'],'id DESC');
        //查询商品信息
        $spu = db::table('bbs_goods_spu')->where("id in ({$order['spu_id']})")->order('id asc')->select();
        // dump($spu);
        // foreach ($spu as $k => $v) {
        //     $spec = (array)json_decode($v['sku_spec']);
        //     $sku[$k]['sku_spec'] = $spec;
        // }
        $this->assign('order',$order);
        $this->assign('child',$child);
        $this->assign('order_logs',$order_logs);
        $this->assign('spu_info',$spu);
        return $this->fetch('admin_detail');
    }


    /* 确认付款 */
    public function pay() {
        if (is_post()) {
            $params = [];
            $params['pay_time'] = remove_xss(strtotime(input('pay_time')));
            $params['paid_amount']= sprintf("%0.2f", (float) input('paid_amount'));
            $params['pay_method'] = remove_xss(input('pay_method'));
            $params['pay_sn']     = remove_xss(input('pay_sn'));
            $params['msg']        = remove_xss(input('msg'));
            if ($params['pay_method'] != 'other' && !$params['pay_sn']) {
                showmessage(lang('pay_deal_sn_empty'));
            }
            $result = $this->service->set_order(input('sn') ,'pay' ,'',$params);
            if (!$result) showmessage($this->service->errors);
            showmessage(lang('pay_success'),'',1,'json');
        } else {
            // 获取所有已开启的支付方式
            $pays = model('pay/Payment','service')->get_list();
            foreach ($pays as $k => $pay) {
                $pays[$k] = $pay['pay_name'];
            }
            $pays['other'] = '其它付款方式';
            $order = $this->service->get_find(array('sn' => input('sn')));
            $this->assign('pays',$pays)->assign('order',$order);
            return $this->fetch('alert_pay');

        }
    }

    /* 确认订单 */
    public function confirm() {
        if (is_post()) {
            $result = $this->service->set_order(input('sn') ,'confirm' ,'',['msg' => input('msg')]);
            if (!$result) showmessage($this->service->errors);
            showmessage('确认订单成功','',1,'json');
        } else {
           return  $this->fetch('alert_confirm');
        }
    }

    /* 确认发货 */
    public function delivery() {
        if (Request::post()) {
            $params = array();
            $params['is_choise']     = input('is_choise');
            $params['delivery_id']   = input('delivery_id');
            $params['delivery_sn']   = input('delivery_sn');
            $params['sn']   	     = input('sn');
            $params['msg']           = input('msg');
            $order = new Order();
            $result = $order->set_order(input('sn') ,'delivery' ,'',$params);
            if (!$result) {
                return ['message' => '发货失败！'];
            }else{
                return ['status' => 1,'message' => '发货成功！'];
            }
        } else {
            // 获取已开启的物流
            $sqlmap = $deliverys = [];
            $sqlmap['enabled'] = 1;
            $deliverys = db::table('bbs_delivery')->where('enabled',$sqlmap['enabled'])->field('id,name')->select();
            $this->assign('deliverys',$deliverys);
            return $this->fetch('alert_delivery');
        }
    }



    /* 确认完成 */
    public function finish() {
        if (Request::post()) {
            $order = new Order();
            $result = $order->set_order(input('sn') ,'finish' ,'',['msg'=>input('msg')]);
            if (!$result){
                return ['message'=>'确认失败！'];
            }else{
                 return ['status'=>1,'message'=>'确认完成成功'];
            }
        } else {
            return $this->fetch('alert_finish');
        }
    }


    /* 取消订单 */
    public function cancel() {
        $order = new Order;
        if (Request::post()) {
            $result = $order->set_order(input('sn') ,'order' ,2,['msg'=>input('msg'),'isrefund' => 1]);
            if (!$result){
                return ['message'=>'取消订单失败'];
            }
            return ['status'=>1,'message'=>'取消订单成功'];
        } else {
            $order = $order->get_find(array('sn' => input('sn')));
            $this->assign('order',$order);
            return $this->fetch('alert_cancel');
        }
    }


    /* 作废 */
    public function recycle() {
        if (Request::post()) {
            $order = new Order;
            $result = $order->set_order(input('sn') ,'order' ,3,['msg'=>input('msg')]);
            if (!$result){
                return ['message'=>'废弃订单失败'];
            }
            return ['status'=>1,'message'=>'废弃订单成功'];
        } else {

            return $this->fetch('alert_recycle');
        }
    }



}