<?php
namespace app\goods\controller;
use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request;

class Admin extends Base{

    public function _initialize()
    {
        // parent::_initialize();
        // $this->config = config('aliyun_oss');
        // $this->sku_service = model('goods/Sku','service');
        // $this->spu_service = model('goods/Spu','service');
        // $this->category_service = model('goods/Category','service');
        // $this->brand_service = model('goods/Brand','service');
        // $this->spec_service = model('goods/spec','service');
    }

    // public function init(){

    //     return $this->fetch();
    // }

    // public function index(){
    //     $catid=input('catid','0');
    //     $category=$this->category_service->get_by_id(['id'=>$catid]);


    //     if(!$category) showmessage(lang('_param_error_'),url('goods/admin/init'));

    //     $sqlmap=[];

    //     //$sqlmap['path_id']=['like','%,'.$catid.',%'];
    //     // 根据商户ID查询数据
    //     if(ADMIN_ID != 1){
    //         $sqlmap['admin_id'] = ADMIN_ID;
    //     }
    //     $list=$this->spu_service->get_list($sqlmap);
    //     $this->assign('imgUrl', $this->config['Url']);
    //     $this->assign('list',$list);
    //     return $this->fetch();
    // }


    public function add(){

        // $catid=input('catid/d');
        // if(!$category=$this->category_service->get_by_id(['id'=>$catid],true,false)){
        //     showmessage($this->category_service->errors);
        // }
        // if(Request::param()){
        //     if (!$this->spu_service->edit(input('post.'))) {
        //         showmessage($this->service->errors);
        //     }
        //     showmessage(Lang('_operation_success_'), url('index',['catid'=>$catid]),1);

        // }else{

            // $brands=$this->brand_service->get_column();
            // $brands[0]='请选择品牌';
            // ksort($brands);
            // $this->assign('category', $category);
            // $this->assign('brands', $brands);
            $a = "admin@public:book_base";
            $this->assign('a',$a);
            return $this->fetch('edit');
        // }

    }


    // public function edit(){

    //     $goods_id=input('id/d');
    //     $config = config('aliyun_oss');
    //     if(!$goods=$this->spu_service->get_find(['id'=>$goods_id])){
    //         showmessage($this->spu_service->errors);
    //     }
    //     if(!$category=$this->category_service->get_by_id(['id'=>$goods['spu']['catid']],true,false)){
    //         showmessage($this->category_service->errors);
    //     }
    //     if($goods['spu']['imgs']) {
    //         foreach($goods['spu']['imgs'] as $k => $v) {
    //             if(strpos($v,'tesi') === false) {
    //                 $goods['spu']['imgs'][$k] = $config['Url'].$v;
    //             }
    //         }
    //     }
    //     if(is_post()){
    //         $id = input('get.id');
    //         if (!$this->spu_service->edit(input('post.'),true)) {
    //             showmessage($this->spu_service->errors);
    //         }
    //         showmessage(Lang('_operation_success_'), url('index',['catid'=>$category['id']]),1);
    //     }else{
    //         $brands=$this->brand_service->get_column();
    //         $brands[0]='请选择品牌';
    //         ksort($brands);
    //         $this->assign('category', $category)->assign('goods',$goods)->assign('brands', $brands);
    //         return $this->fetch();
    //     }
    // }


    // public function goods_spec_pop(){

    //     $specs=$this->spec_service->get_spec_name();
    //     $this->assign('specs',$specs);
    //     return $this->fetch('spec_pop');
    // }



    // public function delete(){
    //     $result = $this->spu_service->del(['id'=>input('param.id/a'),'label'=>input('param.label/s')]);
    //     if(!$result){
    //         showmessage($this->spu_service->errors);
    //     }else{
    //         showmessage(lang('_operation_success_'),$_SERVER['HTTP_REFERER'],1);
    //     }
    // }
    // /*
    //  * 左侧导航
    //  */
    // public function public_categorys(){
    //     $data=$this->category_service->node_list();
    //     $this->assign('data',$data);
    //     return $this->fetch();
    // }

}