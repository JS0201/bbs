<?php
namespace app\index\service;
use app\common\controller\Base;
use app\common\model\Address as addre;
use app\common\model\Region;
use think\facade\Request; 

class Address extends Base
{
    public function initialize()
    {
        $this->addre = new addre;
        $this->Region = new Region;
    }

    public function getAll($where)
    {
       return $this->addre->where($where)->order('id asc')->select();
       
    }

    public function getOne($where)
    {
        return $this->Region->where($where)->select();
    }

    //地址列表
    public function getList()
    {

        $list = $this->model->getList("mid = {$this->userid}", "*");
        $result = array();
        if($list) {
            foreach($list as $k => $v) {
                $address = $this->model->getCompleteAddressAttr('', $v);
                $result[$k]['address'] = $address;
                $result[$k] = $v;
            }
        }
        return $result;
    }
    //添加编辑地址
    public function add($uid, $request)
    {
        if(!$request) {
            return ['参数错误'];
        }
        $result=$data=array();
        $data['mid'] = $uid;
        $data['name'] = $request['realname'];
        $data['mobile'] = $request['mobile'];
        $data['isdefault'] = 0;    
        $data['province'] = $request['province'];
        $data['city'] = $request['city'];
        $data['county'] = $request['area'];
        $data['address'] = $request['address'];
        $data['local'] = '';
        $result['local'] = $this->Region
        ->field('region_name')
        ->where('region_id',$data['province'])
        ->whereor('region_id',$data['city'])
        ->whereor('region_id',$data['county'])
        ->select();
        foreach ($result['local'] as $key => $value) {
        	$result[]=$value['region_name'];
        }
        $data['address'] = $request['address'];
        $data['province'] = $result[0];
        $data['city'] = $result[1];
        $data['county'] = $result[2];
        $data['local'] = $data['province'].$data['city'].$data['county'].$data['address'];
        if(!$this->addre->isupdate(false)->save($data)) {
            return ['添加失败！'];
        }else{
        	return 1;
        }
        
    }
    public function edit($id, $uid, $request)
    {
        if(!$request) {
            $this->errors = lang('submit_parameters_error');
            return false;
        }
        unset($request['id']);
        unset($request['uid']);
        $region = explode(' ',$request['region']);
        $request['province'] = $region[0];
        $request['city'] = $region[1];
        $request['county'] = $region[2];
        unset($request['region']);
        $re = $this->model->updated("id = {$id}", $request);
        if($re !== false) {
            return true;
        }
        $this->errors = lang('_operation_fail_');
        return false;
    }

    public function getDef($uid)
    {
        $result = array();
        $arr = $this->addre->where(['mid' => $uid, 'isdefault' => 1])->find();
        $result['name'] = $arr['name'];
        $result['mobile'] = $arr['mobile'];
        $result['id'] = $arr['id'];
        $result['local'] = $arr['local'];
        return $result;
    }

    public function isCheck($aid,$ischeck,$uid)
    {
        if($ischeck==1)
        {  
            $data = ['isdefault'=>0];
            $result = $this->addre->save($data,['mid'=>$uid]);
            $adata = ['isdefault'=>1];
            $result = addre::update($adata,['id'=>$aid]);
           return true;
        }else{
            $adata = ['isdefault'=>0];
            $result = addre::update($adata,['id'=>$aid]);
           return true;
        }

    }
    
}