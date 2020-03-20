<?php
namespace app\admin\service;
use app\common\controller\Base;
use app\admin\common\model\Finance;
use app\admin\common\model\MemberLog;

class MoneyFinance extends Base
{
    public function initialize()
    {
        $this->Fin = new Finance;
    }

    /**调整用户个人积分
     * @param $uid 用户ID
     * @param $number 更改数值
     * @param $type 积分类型
     * @param $settype 加或减 true为加 false为减
     * @param $admin_id 后台用户ID
     * @param $settype 加钱或减钱
     * @param $transaction_type 交易类型 1支付 2直推收益  3团队收益 4押金退还
     * @param $fromid 收益来源用户
     * @param $signid 收益来源用户
     */
    
    public function setFinance($uid, $number, $type, $msg = '',$admin_id = '', $settype = true, $transaction_type, $fromid, $signid = 0)
    {
        $finance = $this->Fin->getByUid($uid);
        $money = $settype ? $finance[$type] + $number : $finance[$type] - $number;
        $money_re = $this->Fin->updated("uid = {$uid}", array("{$type}" => $money, "time" => time())); //修改金额
        $log_model = new MemberLog();
        $log_re = $log_model->add(array(
            'mid' => $uid,
            'value' => $number,
            'fromid' => $fromid,
            'msg'   => $msg,
            'dateline' => time(),
            'type'     => $type,
            'style'    => $transaction_type,
            'admin_id' => $admin_id,
            'money_detail' => json_encode(array("{$type}" => $money)),
            'signid'    => $signid
        ));                                                    
        return 1;
    }

}