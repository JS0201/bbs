<?php 
namespace app\admin\controller;

use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request; 
use app\admin\service\Member as Mem;

class Member extends Base 
{
	/*
		充值审核界面
	 */
	public function moneyCheckShow()
	{
		$mem = new Mem;
		$lists = $mem->CheckShow();
		$this->assign('num',$lists['num']);
	    $this->assign('lists',$lists['lists']);
		return $this->fetch('moneyCheckShow');
	}

/*
	充值审核
 */
	public function moneyCheckEdite()
	{
		if(Request::post()){
			$admin_id = Session::get('user_id');
			$mem = new Mem;
            if($mem->edit($admin_id, input('post.'))){
                $this->success('操作成功！');
            }
        }else{
        	$mem = new Mem;
			$info=$mem->getMessage(input('id/d'));
        	$this->assign('info',$info);
        	return $this->fetch();
        }
		
	}


}