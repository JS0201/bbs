<?php
// 应用公共文件
use think\Db;
if(!function_exists('getUserName'))
{
    function getUserName($id)
    {
        return Db::table('user')->where(['id'=>$id])->value('name');
    }
}
if(!function_exists('getCateName'))
{
    function getCateName($cateId)
    {
        return Db::table('article_category')->where(['id'=>$cateId])->value('name');
    }
}

/**
 * 通用提示页
 * @param  string  $msg 提示消息（支持语言包变量）
 * @param  integer $status 状态（0：失败；1：成功）
 * @param  string  $extra 附加数据
 * @param  string  $format 返回类型
 * @return mixed
 */
function showmessage($message, $jumpUrl = '-1', $status = 0, $extra = '', $format = '')
{
    if(empty($format)) {
        $format = is_ajax() ? 'json' : 'html';
    }
    switch($format) {
        case 'html':

            if(!defined('IN_ADMIN')) {
                if($jumpUrl == '-1' || $jumpUrl == '') {
                    echo "<script>history.go(-1);</script>";
                } else {
                    redirect($jumpUrl);
                }

            } else {


                $view = new \think\View();


                if(stripos($jumpUrl, 'formhash') === false) {

                    //$jumpUrl = $jumpUrl . config('pathinfo_depr') . 'formhash' . config('pathinfo_depr') . FORMHASH;

                    if(stripos($jumpUrl, '?') === false){
                        $pathinfo_depr='?';
                    }else{
                        $pathinfo_depr='&';
                    }

                    $jumpUrl = $jumpUrl . $pathinfo_depr.'formhash='  . FORMHASH;
                }

                echo $view->fetch(ROOT_PATH . 'public/template/showmessage.tpl', ['message' => $message, 'url' => $jumpUrl]);

            }

            break;
        case 'json':
            $result = array('status' => $status, 'referer' => $jumpUrl, 'message' => $message, 'result' => $extra);
            echo json_encode($result);
            exit;
            break;
        default:
            # code...
            break;
    }
    exit;
}


