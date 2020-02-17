<?php
namespace app\admin\service;

use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request; 
use app\admin\common\model\User; 
use app\admin\common\model\Finance; 
use app\admin\common\model\RechangeCheck;
use app\admin\common\model\MemberLog;
use app\admin\service\MoneyFinance;

class Member extends Base
{
	public function initialize()
	{
		$this->us = new User;
		$this->re = new RechangeCheck;
		$this->log = new MemberLog;
		$this->fin = new Finance;
	}
	public function CheckShow()
	{
		$param = [];
		$param['type'] = 1;
		$rechange = new RechangeCheck; 
		$lists = $rechange->alias('c')
					  	  ->join('bbs_user m','m.id = c.uid')
					  	  ->field('m.name,m.status as mstatus,m.id,c.*,c.id as idd')
					  	  ->where($param)
					  	  ->order('c.id desc')
					  	  ->paginate(10);
		foreach ($lists as $key => $value) {
			$lists[$key]['checktime'] = $value['checktime'] ? date('Y-m-d H:i:s',$value['checktime']) : '等待审核';
		}
		$num = count($lists);
		$lists = ['num'=>$num,'lists'=>$lists];
		return $lists;
	}

	public function getMessage($id)
	{
		$rechange = $this->RechangeCheckGetOne("id = {$id}");
        if(!$rechange) {
            $this->error('参数错误！','moneyCheckShow');
        }
        $status = [
        	'0'=>'已删除',
        	'1'=>'审核中',
        	'2'=>'审核通过',
        	'3'=>'审核未通过',
        ];
        $user = $this->UserGetOne("id = {$rechange['uid']}");
        $rechange['name'] = $user['name'];
        $rechange['realname'] = $user['realname'];
        $rechange['status_text'] = $status[$rechange['status']];
        $rechange['create_time'] = date('Y-m-d H:i:s',$rechange['create_time']);
        $rechange['checktime'] = $rechange['checktime'] ? date('Y-m-d H:i:s',$rechange['checktime']) : '等待审核';
        return $rechange;
	}

	public function edit($admin_id, $data)
	{
		if(!$info=$this->RechangeCheckGetOne(['id'=>$data['id']])){
            $this->error('参数错误！');
        }
        $check_re = $this->re->updated("id = {$data['id']}", array('status' => $data['status'], 'remark' => $data['remark'],'checktime' => time()));
        if($data['status'] == 2) {
            $id = $this->log->getValue("mid = {$info['uid']} and signid = {$data['id']}",'id');
            if($check_re !== false) {
                if(!$id) {
                	$FinanceService = new MoneyFinance;
                    return $FinanceService->setFinance($info['uid'], $info['money'], 'money', '充值成功',$admin_id, true, 7, '', $data['id']);
                }
                return 1;
            }
        }
        return 1;
	}

	public function RechangeCheckGetOne($where)
	{
		
		// return RechangeCheck::find(function($query) use ($where){
		// 	$query->where('id','=',$where);
		// });
		return $this->re->getOne($where);
	}

	public function UserGetOne($where)
	{
		return $this->us->getOne($where);
	}

}
