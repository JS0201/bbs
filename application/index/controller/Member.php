<?php 
namespace app\index\controller;

use app\common\controller\Base;
use app\common\model\User as UserModel;
use app\common\model\Article; 
use app\common\model\Comment;
use app\admin\common\model\Finance;
use think\facade\Request; 
use think\facade\Session;
use app\index\service\Member as Mem;
use think\Db;

class Member extends Base
{
	//个人空间
	public function show()
	{
		$param = Request::param('user_id');
		$where = ['id','=',$param];
		$data = UserModel::get($where);
		$userId = Session::get('user_id');
    	$artList = Article::where('user_id', $userId)->paginate(5);
    	$comList = Comment::where('session_id', $userId)->paginate(5);
    	$fin = Finance::get(['uid'=>$param]);
		
		$this->assign('fin',$fin['money']);
		$this->assign('userId',$userId);
		$this->assign('data',$data);
		$this->assign('artList',$artList);
		$this->assign('comList',$comList);
		$this->assign('empty','<h3>没有文章</h3>');
		$this->assign('em','<h3>没有评论</h3>');
		$this->assign('title','个人空间');
		return $this->fetch('member/memshow');
	}

	//删除评论
	public function doDelete()
     {
     	$artId = Request::param('id');
     	if(Comment::destroy($artId)){
     		$this->success('删除成功');
     	} 
     	$this->error('删除失败');
     }

     //个人信息修改页面
	public function memEdite()
	{
		$userId = Session::get('user_id');
		$data = UserModel::get($userId);
		$this->assign('data',$data);
		$this->assign('title','修改信息');
		return $this->fetch();
	}

	//处理个人信息修改
	public function  memEditeCheck()
	{	
		if($data = Request::post()){
			$rule = 'app\common\validate\MemEdite'; 
			$res=$this->validate($data,$rule);
		  	if (true !== $res){
		  		$this->error($res,'memEdite');
		  	}
		  	$mem = new Mem;
		  	if($mem->mem_edite($data)){
				$this->success('修改成功！','memEdite');
			} else {
				$this->error('修改失败！','memEdite');			
			}			 
		}else{
			$this->error('请求类型错误','memEdite');
		}
	}

	/*
		充值界面
	 */
	public function memMoney()
		{

		return $this->fetch();
	}

	/*
		提交充值
	 */
	public function memMoneyCharge()
		{
			if($data = Request::post()){
			
		  	$mem = new Mem;
		  	if($mem->mem_Charge($data))
		  	{
		  		$this->success('提交成功！等待审核。','memMoney');
		  	}else{
		  		$this->error('充值失败！请稍后再试！','memMoney');
		  	}
		}
	}

	/*
		充值明细
	 */
	public function memMoneyShow()
	{	
		$uid = Session ::get('user_id');
		$param = [
            'money'   =>     '50',
    ];
		$mem = DB::table('bbs_rechange_check')
				->where(2)->find();
		$this->assign('mem',$mem);
		$this->assign('title','充值明细');
		return $this->fetch();
	}




}
