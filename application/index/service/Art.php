<?php
namespace app\index\service;
use app\common\controller\Base;
use app\common\model\Article;
use think\facade\Request; 

class Art extends Base
{
	public function art_save($data)
	{	
		$res = $this->validate($data, 'app\common\validate\Article');
            if (true !== $res) {
                echo '<script >alert("'.$res.'");</script>';
                $this->error('发布失败，请检查！','index/index/insert');
            } else {
                //第一步：获取表单上传文件
                //第二步：使用验证规则，移动到框架指定目录
                //第三步：成功上传后，获取上传信息
                $file = Request::file('title_img');
                $info = $file -> validate([
                    'size'=>5000000000, 
                    'ext'=>'jpeg,jpg,png,gif' 
                ]) -> move('uploads/');
                if ($info) {
                    $data['title_img'] = $info->getSaveName();

                } else {
                    $this->error($file->getError(),'index/index/insert');
                }
	}
		if(Article::create($data)){
			return 1;
		}
}
}