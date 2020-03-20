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

    public function getall(){
        $result=$this->cate->select();
        return $result;
    }

    public function get_by_id($sqlmap=[],$show_self=true,$flag=true,$edite){
        $result=$this->cate->where($sqlmap)->find();
        if(!$result){
            $this->error('参数错误');
        }
        $info=$result->toArray();
        $info['parent_name']=$this->create_cat_format($info['path_id'],$show_self,$flag,$edite);
        return $info;
    }

    public function getone($sqlmap){
        $result=$this->cate->where('id',$sqlmap)->find();
        if(!$result){
            $this->error('参数错误');
        }
        $info=$result->toArray();
        return $info;
    }


    public function category_lists()
    {
        $category = $this->cate->order('sort asc,id asc')->select()->toArray();
        return $category;
    }

    public function add($data,$show_self=true,$flag=true)
    {
        $id = mcate::create($data)->getLastInsID();
        $result=$this->cate->where('id',$id)->find();
        $info=$result->toArray();
        $info['path']=$this->create_cat_format($info['path_id'],$show_self=true,$flag=true);
        $info['path_id'] = implode("",[$data['path_id'],$id]);
        $result=$this->cate->isupdate(true,['id'=>$id])->save(['path'=>$info['path'],'path_id'=>$info['path_id']]);
        return 1;
    }

    public function edit($data)
    {
        $result = $this->cate->where('id',$data['id'])->update($data);
        if ($result === false) {
            return 0;
        }
        return 1;
    }


    /**
     * [create_cat_format 组合父子级分类关系]
     * @param string $path_id 层级
     * @param string $show_self 是否显示自己
     * @param bool $edite 是否为编辑操作
     * @param bool   $extra 是否显示顶级分类文字
     * @return string
     */
    public function create_cat_format($path_id='',$show_self = true,$extra = FALSE, $edite=FALSE)
    {
        $names = [];
        $cat_str='';
        $path_id=trim($path_id,',');
        if(!$show_self){
            $path_id=substr($path_id,0,strrpos($path_id,','));
        }
        if($path_id){
            $names=[];
            $category_list=$this->cate->query('select name from bbs_goods_category where id in ('.$path_id.') order by field(id,'.$path_id.')');
            foreach($category_list as $k=>$v){

                $names[]=$v['name'];
            }

        }
            if($extra == TRUE)
            {
                array_unshift($names,'顶级分类'); //将元素插入到数组开头
            }
            if($edite==TRUE)
            {
                foreach ($names as $key => $value) {
                    if($key == count($names)-1){
                        unset($value);
                    }elseif($key == count($names)-2){
                        $cat_str .= $value;
                    }else{
                        $cat_str .= $value.' > ';
                    }
                }
            }else{
                foreach ($names as $key => $value) {
                    if($key == count($names)-1){
                        $cat_str .= $value;
                    }else{
                        $cat_str .= $value.' > ';
                    }
                }
            }
            return $cat_str;
        }

        public function del($sqlmap){


        if(empty($sqlmap)){
            $this->error('请选择您要操作的数据！');
        }

        mcate::destroy($sqlmap);
        return 1;
    }
    }
