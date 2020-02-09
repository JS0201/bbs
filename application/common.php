<?php
// 应用公共文件
use think\Db;
if(!function_exists('getUserName'))
{
    function getUserName($id)
    {
        return Db::table('zh_user')->where(['id'=>$id])->value('name');
    }
}
if(!function_exists('getCateName'))
{
    function getCateName($cateId)
    {
        return Db::table('zh_article_category')->where(['id'=>$cateId])->value('name');
    }
}




