<?php
namespace app\order\service;

use app\common\controller\Base;
use app\order\common\model\Log as lo;
use think\Db;

class Log extends Base
{
    public function initialize()
    {
        $this->log = new lo;
    }


    /**
     * 写入订单日志
     * @param $params 日志相关参数
     * @return [boolean]
     */
    public function add($params = array(),$extra = FALSE) {
        if (empty($params)) {
            return FALSE;
        }
        $params['system_time']        = time();
        $result = $this->log->isUpdate(false)->data($params)->save();
        if (!$this->log->id) {
            $this->error('添加失败');
        }
        return $result;
    }


    /**
     * 根据子订单号获取日志
     * @param $sn : 订单号(默认空)
     * @param $order  : 排序(默认主键升序)
     * @return [result]
     */
    public function get_by_order_sn($sn = '' , $order = 'id ASC') {
        $sqlmap = array();
        $sqlmap['order_sn'] = $sn;
        return $this->log->where($sqlmap)->order($order)->select();
    }

}