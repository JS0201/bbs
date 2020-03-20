<?php
namespace app\goods\service;

use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request; 
use app\goods\common\model\Brand as bra;
use think\facade\Db;

class Brand extends Base
{
	public function initialize()
	{
		$this->bra = new bra;
	}
    
    public function get_column($sqlmap=[],$field='id,name') {
       return  $this->bra->where($sqlmap)->field($field)->order('name','asc')->select();
    }

    public function category_lists()
    {
        $category = $this->bra->order('sort asc,id asc')->select()->toArray();
        return $category;
    }

	public function edit($data, $isupdate = false, $valid = true, $msg = [])
    {
        $result = $this->bra->isupdate($isupdate)->save($data);
        if ($result === false) {
            return 0;
        }
        return 1;
    }

    public function get_by_id($sqlmap=[],$flag=true){

        if(!$result=$this->bra->where($sqlmap)->find()->toArray()){
            $this->error('参数错误！');
        }

        return $result;

    }


    public function del($sqlmap){


        if(empty($sqlmap)){
        	$this->error('请选择您要操作的数据！');
        }

        $this->bra->destroy($sqlmap);
        return 1;
    }

}
