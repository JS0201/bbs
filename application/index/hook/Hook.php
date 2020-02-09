<?php
namespace app\index\hook;

class Hook {
	public function registerInit($params){
        //注册开关
        $reg_allow=1;
        if(empty($reg_allow)){
            $params['reg_status']=0;
            $params['reg_error']='注册暂未开放';
            return $params;
        }
}
}