<?php
namespace app\goods\service;

use app\common\controller\Base;
use think\facade\Session;
use think\facade\Request; 
use app\goods\common\model\Spu as Sp;
use think\Db;

class Detail extends Base
{
    public function initialize()
    {
        $this->spu = new Sp;
    }
    
    public function getAll() {
         $result = Db::name('bbs_goods_spu')->alias('s')
         ->leftjoin('bbs_goods_category c','s.catid=c.id','LEFT')
         ->leftjoin('bbs_goods_brand b','s.brand_id=b.id','LEFT')
         ->field('s.*,c.name cate_name,b.name brand_name')
         ->select();
         foreach ($result as $key => $value) {
            $result[$key]['imgs']=substr($value['imgs'],0,strpos($value['imgs'],'&'));
         }

        return $result;

    }

    public function getOne($cid) {
         $result = Db::name('bbs_goods_spu')->alias('s')
         ->leftjoin('bbs_goods_category c','s.catid=c.id','LEFT')
         ->leftjoin('bbs_goods_brand b','s.brand_id=b.id','LEFT')
         ->where("s.id","$cid")
         ->field('s.*,c.name cate_name,b.name brand_name')
         ->select();

        $img = substr($result[0]['imgs'], 0,-1);
         $imgs['img'] = explode('&', $img);
         $im = array();
         foreach ($imgs['img'] as $key => $value) {
             $im[$key] = ['img'=>$value];
         }
        return [$result,$im];
        return $result;
    }



    public function get_index($where = '', $isindex = true, $start = 0, $limit = 20) {
        $result = array();
        if($isindex) { //积分商城
            $result = $this->model->where('type = 2 and status = 1')->limit(20)->order('max_price asc')->select()->toarray(); //套餐
        }else{ //消费商城
            $result = $this->model->where($where)->limit($start, $limit)->order('max_price asc')->select()->toarray();
        }
        if($result) {
            $config = config('aliyun_oss');
            foreach($result as $k => $v) {
                $result[$k]['thumb'] = $config['Url'].$result[$k]['thumb'];
            }
        }
        return $result;
    }

    public function get_find($sqlmap, $extra='brand,category,sku,type'){

            $info = [];
            $result=$this->model->where($sqlmap)->find();
            if(!$result){
                $this->errors = lang('goods_goods_not_exist');
                return false;
            }

            $spu=$result->toArray();
            $info['spu'] = $spu;

            /* 返回值 */
            if($extra) {
                $extra = explode(",", $extra);
                foreach ($extra AS $val) {
                    $method = "get_extra_".$val;
                    if(method_exists($this->model,$method)) {
                        $info['_'.$val] = $this->model->$method($spu);
                    }
                }
            }
            $info['extra']=$this->model->get_extra($info);
            return $info;

    }

    

    public function add($goods)
    {
     $message=[];
     $data=[];
     $data['imgs']='';
     $files = request()->file('image');
     foreach($files as $file){
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( 'uploads/');
        if($info){
           $message[] = $info->getsavename(); 
       }else{
            // 上传失败获取错误信息
        echo $file->getError();
    }    
}
foreach ($message as $key => $value) {
    $data['imgs'] .= $value.'&';
}

$goods['imgs'] = $data['imgs'];
return Sp::create($goods);
}
    
        
    

    public function edit($goods, $isupdate = false, $valid = true, $msg = []){
        $spu = $goods['spu'];
        $cate = $spu['catid'];
        $cateArr = $this->cat_model->getOne("id = {$cate}");
        $params=[];
        //---start---图片
        foreach($goods['album'][0] as $kk => $vv) {  
            if(strpos($vv,'tesi') !== false) {
                $ii = explode('/', $vv);
                $goods['album'][0][$kk] = $ii[3].'/'.$ii[4].'/'.$ii[5].'/'.$ii[6];
            }
         }
        $spu_imgs = $goods['album'][0];
        $spu['imgs'] = $goods['album'] ? json_encode($spu_imgs,JSON_UNESCAPED_SLASHES) : '';
        $spu['thumb'] = $spu_imgs ? $spu_imgs[0] : '';
        foreach($goods as $key=>$val){
            $params[$key]=$val;
        }
        $skus = $data = array();
        $ss=array_values($goods['album']);
        $ssd=array_keys($goods['album']);
        $len=strlen($ssd[1]);
        $goods_spec = [];
        foreach ($goods['sn'] as $key => $sn) {
            if(!isset($i)){
                $i=1;
            }
            $re = json_decode($goods['specs'][$key], true);
            if($ss[$i]){
                $img=json_encode($ss[$i],true);
            }
            $i=$i+1;
            $skus[$key]['sn'] = $sn;
            $skus[$key]['status_ext'] = $params['status_ext'][$key]?$params['status_ext'][$key]:'';
            $skus[$key]['spec'] = $params['spec'][$key] ? $params['spec'][$key] : '';
            $skus[$key]['barcode'] = $params['barcode'][$key];
            $skus[$key]['shop_price'] = $params['shop_price'][$key];
            $skus[$key]['market_price'] = $params['market_price'][$key];
            $skus[$key]['number'] = $params['number'][$key];
            $skus[$key]['sku_id'] = $params['sku_id'][$key];
            $spu['sku_total'] += $params['number'][$key];
            $skus[$key]['weight'] = $params['weight'][$key];
            $skus[$key]['volume'] = $params['volume'][$key];

            if($re){
                $spec_query = [];
                foreach ($re as $a => $b) {
                    $spec_query[] = $b;
                    if($a == 0){
                        $skus[$key]['spec'] = '{'.'"'.$b['name'].'":"'.$b['value'].'"';
                    }else{
                        $skus[$key]['spec'] = $skus[$key]['spec'].',"'.$b['name'].'":"'.$b['value'].'"';
                    }
                    $goods_spec[] = array('name' => $b['name'],'value'=>$b['value'],'color'=>$b['color'],'img'=>$b['img'],'style'=>$b['style']);//拼接规格数组
                }
                $skus[$key]['spec'] = $skus[$key]['spec'].'}';
                $skus[$key]['spec_query'] = json_encode($spec_query);
            }else{
                $skus[$key]['sku_name'] = $spu['name'];
            }
            if($img&&$len>15){
                $skus[$key]['imgs'] = $img;
            }
        }
        $spu['type'] = $cateArr['type'];
        // 添加商品时更新
        if(!$isupdate){
            $spu['admin_id'] = $cateArr['type'];
        }
        $this->model->startTrans();
        $result = $this->model->except('id')->validate($valid, $msg)->isupdate($isupdate)->save($spu);
        $spuid = $this->model->id;

        if($result===false){
            $this->model->rollback();
            $this->errors=$this->model->getError();
            return false;
        }

        $this->model->commit();

        $spu['id'] = isset($spu['id']) && $spu['id'] > 0 ? $spu['id'] : $this->model->id;
        if((int)$spu['id'] > 0){
            $local_sku_ids = $this->sku_model->where(['spu_id' => $spu['id']])->column('sku_id');
        }

        $sku_key = key($skus);
        $data=[];
        foreach ($skus as $key => $sku) {
            $sku['imgs'] = $spu['imgs'];
            $sku['thumb'] = $spu['thumb'];
            $sku['spu_id'] = $spu['id'];
            $sku['description'] = $spu['content'] ? $spu['content'] : '';
            $sku['status'] = $spu['status'];
            $sku['sort'] = $spu['sort'];
            $sku['type'] = $spu['type'];
            $sku['sku_name'] = $spu['name'];
            //sku编辑版本
            $sku['edition'] =  1;
            $sku_ids[] = $sku['sku_id'];
            //组装sku编辑、新增、删除数组
            if(!$sku['sku_id']){
                $data['new'][] = $sku;
            }else{
                if(in_array($sku['sku_id'],$local_sku_ids)){
                    $data['edit'][] = $sku;
                }
            }

        }

        //拼规格数组。hxd改
        $spec_spu=[];
        foreach ($goods_spec as $c => $d) {
            $is = db("goods_spec")->where(array('name'=>$d['name'],'spu_id'=>$spuid))->find();//查询name和spu_id是否存在
            if($is){
                $s['name'] = $d['name'];
                if($is['value'] == $d['value']){ //如果值存在，不做任何操作
                    continue;
                }else{
                    $vs = explode(',', $is['value']);  //切割成数组
                    if(in_array($d['value'], $vs, TRUE)){  //判断数组里是否存在当前值
                        continue;
                    } else {
                        $s['value'] = $is['value'].','.$d['value'];  //不存在，直接追加值
                        $s['color'] = $is['color'].','.$d['color'];
                        $s['img'] = $is['img'].','.$d['img'];
                    }
                }
                $s['style'] = $d['style'];
                $s['spu_id'] = $spuid;
                db("goods_spec")->where('id',$is['id'])->update($s);

            }else{ //如果数据库不存在规格，直接添加
                $s['name'] = $d['name'];
                $s['value'] = $d['value'];
                $s['color'] = $d['color'];
                $s['img'] = $d['img'];
                $s['spu_id'] = $spuid;
                $s['style'] = $d['style'];
                db("goods_spec")->insert($s);
            }
        }


        $data['del'] = array_diff($local_sku_ids,$sku_ids);
        //sku数据入库操作

        $this->sku_service->startTrans();
        $skuinfo = $this->sku_service->create_sku($data);
        if(!$skuinfo){
            $this->sku_service->rollback();
            $this->errors = $this->sku_service->errors;
            return FALSE;
        }

        $this->sku_service->commit();

        //spu最大最小价格
        $_price = $this->sku_model->field("min(shop_price) AS min_price, max(shop_price) AS max_price")->where(["spu_id" => $spu['id']])->find();
        $this->model->save(['id' => $spu['id'], 'min_price' => $_price['min_price'], 'max_price' => $_price['max_price']]);


        //生成商品索引表
        $this->create_goods_index($spu,$skuinfo);
        // 生成log记录
        $code = $isupdate ? $goods['spu']['id'] : $this->model->id;
        $name = $isupdate ? lang('admin_log_goods_edit') :  lang('admin_log_goods_add');
        $contetn = $goods['spu']['name'] ? $goods['spu']['name'] : '';
        AdminLog(1, $contetn, lang("admin_log_goods"), $name, $code);

        return true;
    }


    /**
     * 生成商品索引表
     * @param $spu
     * @param $skus
     * @return bool
     */
    public function create_goods_index($spu,$skus){
        if(!empty($skus['del'])){
            $this->index_model->where(['sku_id'=>['IN',$skus['del']]])->delete();
        }
        $skuinfo =  array_merge($skus['edit'] ? $skus['edit'] : [],$skus['new'] ? $skus['new'] : []);
        if(!empty($skuinfo)){
            foreach ($skuinfo AS $sku) {
                $item = array();
                $item['sku_id'] = $sku['sku_id'];
                $item['spu_id'] = $spu['id'];
                $item['catid'] = $spu['catid'];
                $item['path_id'] = db('goods_category')->where(['id'=>$spu['catid']])->value('path_id');
                $item['brand_id'] = $spu['brand_id'];
                $item['shop_price'] = $sku['shop_price'];
                $item['show_in_lists'] = $sku['show_in_lists'] ? $sku['show_in_lists'] : 0;
                $item['status'] = $spu['status'];
                $item['status_ext'] = $sku['status_ext'] ? $sku['status_ext'] : 0;
                $item['sort'] = $spu['sort'];
                $skuindex = $this->index_model->get($item['sku_id']);
                if(empty($skuindex)){


                    $this->index_model->save($item);
                }else{
                    $this->index_model->isupdate(true)->save($item);
                }
            }
        }
        return true;
    }









    /**
     * [create_goods_spec_array 生成goods中的spec_array]
     * @param  [type] $data [规格json数组]
     * @return [array]       [商品的规格数组]
     */
    private function create_goods_spec_array($skus){



        if(isset($skus)){
            $goods_spec_array = array();

            foreach($skus as $key => $sku) {

                foreach (json_decode($sku['spec'],true) as $key => $spec) {
                    if(!isset($goods_spec_array[$spec['id']])) {
                        $goods_spec_array[$spec['id']] = ['id' => $spec['id'],'name' => $spec['name'],'value' => [],'style' => [],'img' => [],'color' => []];
                    }
                    $goods_spec_array[$spec['id']]['value'][] = $spec['value'];
                    $goods_spec_array[$spec['id']]['style'][] = $spec['style'];
                    $goods_spec_array[$spec['id']]['img'][] = $spec['img'];
                    $goods_spec_array[$spec['id']]['color'][] = $spec['color'];
                }

            }
            foreach($goods_spec_array as $key => $val) {
                $val['value'] = array_unique($val['value']);
                $val['img'] = array_unique($val['img']);
                $val['color'] = array_unique($val['color']);
                $val['style'] = array_unique($val['style']);
                $goods_spec_array[$key]['value'] = join(',',$val['value']);
                $goods_spec_array[$key]['img'] = join(',',$val['img']);
                $goods_spec_array[$key]['color'] = join(',',$val['color']);
                $goods_spec_array[$key]['style'] = join(',',$val['style']);
            }
        }
        return $goods_spec_array;
    }


    public function get_goods_specs($skus){


        $selectedItem = [];
        foreach ($skus as $key => $specs) {
            foreach ($specs['spec'] as $spec) {
                $item = [];
                $item['id'] = $spec['id'];
                $item['name'] = $spec['name'];
                $item['value'] = $spec['value'];
                $item['style'] =  $spec['style'];
                $item['color'] = $spec['color'];
                $item['img'] = $spec['img'];
                $item['spec_md5'] = md5($spec['id'].$spec['value']);
                $selectedItem[] = $item;
            }
        }

        $selectedItem = more_array_unique($selectedItem);
        return $selectedItem;

    }



    /**
     * [delete 删除商品，在商品列表里删除只改变状态，在回收站里删除直接删除]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function del($params){

        $id = $params['id'];


        $label = $params['label'];
        $data = $params = $map = [];


        if($id){
            if($label == 4){
                $result =$this->delete_goods($id);
            }else{
                $sqlmap['id'] = $map['spu_id'] = ['IN',$id];
                $data['status'] = -1;
                $result = $this->model->save($data,$sqlmap);
                $this->sku_model->save($data,$map);
                $this->index_model->save($data,$map);
                if(!$result){
                    $this->errors = lang('_operation_fail_');
                    return FALSE;
                }
                $result = true;
            }
            // 生成log记录
            $code = AdminLoginFormat($id);
            AdminLog(1, '', lang("admin_log_goods"), lang('admin_log_goods_delete'), $code);
            return $result;
        }else{
            $this->errors = lang('_param_error_');
            return FALSE;
        }

     }


    /**
     * [delete_goods 删除商品,只有在回收站里进行此操作]
     * @param  [array] $id [商品id]
     * @return [type]     [description]
     */
    private function delete_goods($id){
        $id = (array)$id;
        if(empty($id)) {
            $this->errors = lang('_param_error_');
            return FALSE;
        }
        $_goods_del_result = $this->model->where(['id' => ["IN", $id]])->delete();
        $_pro_del_result = $this->sku_model->where(['spu_id' => ['IN', $id]])->delete();
        $this->index_model->where(['spu_id' => ['IN', $id]])->delete();
        return true;
    }



    private function search_map($data){


        $map=[];
        $map['sqlmap']=[];
        $map['order']=[];

        if(isset($data['keywords']) && $data['keywords'] ) $map['sqlmap']['name|sn']=['like','%'.$data['keywords'].'%'];
        if($data['label']=='' || $data['label']==1){
            $map['sqlmap']['status']=['neq',-1];
        }elseif($data['label']==2){
            $map['sqlmap']['status']=0;
        }elseif($data['label']==3){
            $map['sqlmap']['warn_number']=['elt',5];
            $map['sqlmap']['status']=['neq',-1];
        }elseif($data['label']==4){
            $map['sqlmap']['status']=-1;
        }
        if($data['catid']){
            $path_id=db('goods_index')->where(['path_id'=>['like','%,'.$data['catid'].',%']])->column('spu_id');
            $map['sqlmap']['id']=['in',$path_id];
        }
        if(isset($data['order']) && $data['order']) $map['order']=$data['order'].' desc';

        return $map;
    }

}