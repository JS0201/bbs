<?php
namespace app\order\service;

use app\common\controller\Base;
use app\order\common\model\Track as tra;
use think\Db;

class Track extends Base
{
    public function initialize()
    {
        $this->track = new tra;
    }


    /**
     * 添加订单跟踪
     * @param  string 	$order_sn  	主订单号 (必传)
     * @param  string 	$sub_sn  	子订单号 (必传)
     * @param  string 	$msg  		跟踪内容
     * @param  int 		$time  		时间戳 (默认当前时间戳)
     * @param  int 		$delivery_id 订单物流关联id (默认0)
     * @return [boolean]
     */
    public function add($order_sn ,$sub_sn,$msg = '' ,$time = 0 ,$delivery_id = 0) {
        if (!$order_sn) {
            $this->error('订单信息不存在！');
        }
        $data = array();
        $data['order_sn'] = $order_sn;
        $data['sub_sn'] = $sub_sn;
        $data['msg']      = $msg;
        $data['time']     = ((int) $time == 0) ? time() : $time;
        $data['add_time'] = time();
        if ($delivery_id > 0) {
            $data['delivery_id'] = $delivery_id;
        }else{
            $data['delivery_id'] = 0;
        }
        $result = $this->track->isUpdate(false)->data($data)->save();
        if (!$this->track->id) {
             $this->error('订单跟踪失败！');
        }
        return true;
    }


}
