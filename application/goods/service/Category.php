<?php
namespace app\goods\service;

use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request; 
use app\goods\common\model\Category as mcate;
use think\facade\Db;

class Category extends Base
{
	public function initialize()
	{
		$this->cate = new mcate;
	}

    public function category_lists()
    {
        $category = $this->cate->order('sort asc,id asc')->select()->toArray();
        return $category;
    }

	public function edit($data, $isupdate = false, $valid = true, $msg = [])
    {
        $result = $this->cate->isupdate($isupdate)->save($data);
        if ($result === false) {
            return 0;
        }
        return 1;
    }
}
