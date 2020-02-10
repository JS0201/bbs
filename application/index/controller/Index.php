<?php
namespace app\index\controller;

use app\common\controller\Base;
use app\common\model\Article;
use app\common\model\ArtCate;
use app\common\model\Comment;
use think\facade\Request; 
use think\Db;
use think\facade\Session;
use app\index\service\Art;

class Index extends Base
{
    //首页
    public function index()
    {
        $map = [];  
        $map[] = ['status','=',1];

        //搜索
        $keywords = Request::POST('keywords');
        if (!empty($keywords)){
            $map[] = ['title' , 'like','%'.$keywords.'%'];
        }

        $cateId = Request::param('cate_id');
        if (isset($cateId)){
            $map[] = ['cate_id','=', $cateId];
            $res = ArtCate::get($cateId);
            $artList = Db::table('article')
                    ->where($map)
                    ->order('create_time','desc')->paginate(4); 
            $page = $artList->render();
          $this->view->assign('cateName',$res->name);
          
          
        } else {
          $this->view->assign('cateName','全部文章');
          $artList = Db::table('article')
                    ->where($map)
                    ->order('create_time','desc')->paginate(4); 
          $page = $artList->render();
        }

        $this->view->assign('empty','<h3>没有文章</h3>'); 
        $this->view->assign('artList', $artList);
        $this->view->assign('page', $page);
        return $this->fetch('index',['title'=>'社区问答']) ;
    }

    //添加文章
    public function insert()
    {
    	$this->isLogin();
    	$this->view->assign('title','发布文章');
        $cateList = ArtCate::all();
        if (count($cateList)>0) {            
            $this->assign('cateList', $cateList);
        } else {
            $this->error('请先添加栏目','index/index');
        }
    	return $this->view->fetch('insert');
     }

     //保存文章
     public function save()
     {
        if (Request::isPost()){
            $data = Request::post();
            $art = new Art;
                if($art->art_save($data)){
                    $this->success('文章发布成功','index/index');
            } else {
            $this->error('文章发布失败','index/index/insert');
            }             
        }
         else {
            $this->error('请求类型错误');
        }
     }

    //详情页
    public function detail()
    {
        $artId = Request::param('id');
        $art = Article::get(function($query) use ($artId){
            $query->where('id','=',$artId)->setInc('pv',1);
        });
        if (!is_null($art)){

            $this->view->assign('art',$art);
        }
        //评论信息
        $where = function($query)use ($artId){
            $query->where('status',1)
            ->where('article_id',$artId)
            ->order('create_time','desc');        
        };
        $commentList = Comment::all($where);
        $this->view->assign('commentList',$commentList);
        //收藏点赞默认颜色
        $session_id = Session::get('user_id');
        if(empty($session_id))
        {
            $session_id = '';
        }
        $map[] = ['user_id','=', $art['user_id']];
        $map[] = ['article_id','=', $art['id']];
        $map[] = ['session_id','=', $session_id];
        $result = Db::table('user_fav')->where($map)->find();
        if(empty($result))
        {
            $result = ['session_id'=>'',];
        }
        $like = Db::table('user_like')->where($map)->find();
        if(empty($like))
        {
            $like = ['session_id'=>'',];
        }
        $this->view->assign('result',$result);
        $this->view->assign('like',$like);
        $this->view->assign('title','文章详情');
        return $this->view->fetch('detail');
    }



    // 用户收藏
    public function fav()
    {  

        if (!Request::isAjax()){
            return ['status'=>-1, 'message'=>'请求类型错误'];
        }         

        $data = Request::param();
        if (empty($data['session_id'])){
            return  ['status'=>-2, 'message'=>'请先登录'];
        }
        $map[] = ['user_id','=', $data['user_id']];
        $map[] = ['article_id','=', $data['article_id']];
        $map[] = ['session_id','=', $data['session_id']];

        $fav=Db::table('user_fav')->where($map)->find();
        if (is_null($fav)) {

            Db::table('user_fav')
            ->data([
                'user_id'=>$data['user_id'],
                'article_id'=>$data['article_id'],
                'session_id'=>$data['session_id'],
            ])->insert();
            return ['status'=>1, 'message'=>'取消收藏'];
                  
        }else {
            Db::table('user_fav')->where($map)->delete();
            return ['status'=>0, 'message'=>'收藏'];      
        }
    }

    // 用户点赞
    public function like()
    {  
        if (!Request::isAjax()){
            return ['status'=>-1, 'message'=>'请求类型错误'];
        }         

        $data = Request::param();
        if (empty($data['session_id'])){
            return  ['status'=>-2, 'message'=>'请先登录'];
        }
        $map[] = ['user_id','=', $data['user_id']];
        $map[] = ['article_id','=', $data['article_id']];
        $map[] = ['session_id','=', $data['session_id']];


        $like=Db::table('_user_like')->where($map)->find();
        if (is_null($like)) {
            Db::table('user_like')
            ->data([
                'user_id'=>$data['user_id'],
                'article_id'=>$data['article_id'],
                'session_id'=>$data['session_id'],

            ])->insert();
            return ['status'=>1, 'message'=>'取消赞'];
                  
        }else {
            Db::table('user_like')->where($map)->delete();
            return ['status'=>0, 'message'=>'赞'];      
        }
    }



   //用户评论
   public function insertComment()
   {
   
    $data = Request::param();
    if (empty($data['session_id'])){
            return  ['status'=>2, 'message'=>'请先登录'];
        }
    elseif (Comment::create($data,true)){
        return['status'=>1,'message'=>'评论发表成功！'];
    }
    else{
        return['status'=>0,'message'=>'评论发表失败！'];
    }

}
}









