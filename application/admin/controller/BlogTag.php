<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/13
 * Time: 11:02
 */

namespace app\admin\controller;

use think\Request;
use think\Session;
use think\Url;

class BlogTag extends Base
{
    /*
     * 博客标签列表
     */
    public function tagIndex(){
        return $this->fetch('tag_index');
    }
    /*
     * ajax获取博客标签列表
     */
    public function ajaxGetTagList(){
        if(Request::instance()->isAjax()){
            $page = Request::instance()->get('page/d',1);
            $limit = Request::instance()->get('limit/d',10);
            $offset = ($page - 1) * $limit;
            $tagModel = new \app\admin\model\BlogTag();
            $where = [];
            if(Request::instance()->has('tag_name')) {
                $tag_name = Request::instance()->get('tag_name/s');
                if ($tag_name) {
                    $where['tag_name'] = ['LIKE',"%{$tag_name}%"];
                }
            }
            if(Request::instance()->has('status')) {
                $status = Request::instance()->get('status/d');
                if($status != 2){
                    $where['status'] = $status;
                }
            }
            $result = $tagModel->getBlogTagList($offset,$limit,$where);
            if(!empty($result['iTotalRecords'])){
                foreach($result['iTotalRecords'] as $k=>$v){
                    //创建时间
                    $result['iTotalRecords'][$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
                }
            }
            $result = array(
                'code' => 0,
                'msg' => '获取成功',
                'count' => $result['iTotalCount'],
                'data' => $result['iTotalRecords']
            );
            return json($result);
        }
    }
    /*
     * ajax隐藏、显示博客标签
     */
    public function ajaxUpdateTagStatus(){
        if(Request::instance()->isAjax()){
            if(Request::instance()->has('tag_id') && Request::instance()->has('status')){
                $tag_id = Request::instance()->post('tag_id/d');
                $tagModel = new \app\admin\model\BlogTag();
                $tag_info = $tagModel->getTagOneByWhere(['tag_id'=>$tag_id]);
                if(!empty($tag_info)){
                    $where['tag_id'] = $tag_id;
                    $status = Request::instance()->post('status/d');
                    $data = [
                        'status' => $status,
                        'update_time' => time()
                    ];
                    if($tagModel->saveTag($data,$where)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '';
                        if($status == 0){
                            $log_note = '隐藏博客标签-'.$tag_info['tag_name'];
                        }else if($status == 1){
                            $log_note = '显示博客标签-'.$tag_info['tag_name'];
                        }
                        $actionLogData = [
                            'blog_id' => Session::get('admin.admin_id'),
                            'log_note' => $log_note,
                            'log_url' => Request::instance()->url(),
                            'log_data' => serialize($data),
                            'log_action_ip' => ip2long(Request::instance()->ip()),
                            'log_create_time' => time()
                        ];
                        $actionLogModel->saveActionLog($actionLogData);
                        return json(['status'=>200,'msg'=>'操作成功']);
                    }else{
                        return json(['status'=>500,'msg'=>'操作失败']);
                    }
                }else{
                    return json(['status'=>500,'msg'=>'标签不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }
    }
    /*
     * 编辑博客标签
     */
    public function tagEdit(){
        $tag_id = Request::instance()->param('tag_id/d',0);
        $tagModel = new \app\admin\model\BlogTag();
        $tag_info = $tagModel->getTagOneByWhere(['tag_id'=>$tag_id]);
        if(!empty($tag_info)){
            $this->assign('tag_info',$tag_info);
            return $this->fetch('tag_edit');
        }else{
            return $this->fetch('common/404');
        }
    }
    /*
     * 添加博客标签
     */
    public function tagAdd(){
        return $this->fetch('tag_add');
    }
    /*
     * ajax保存博客标签信息
     */
    public function ajaxSaveTag(){
        if(Request::instance()->isAjax()){
            $tag_id = Request::instance()->post('tag_id/d',0);
            $tagModel = new \app\admin\model\BlogTag();
            $tag_info = $tagModel->getTagOneByWhere(['tag_id'=>$tag_id]);
            $where = [];
            $tag_name = str_replace([' '],'',Request::instance()->post('tag_name/s'));
            $status = Request::instance()->post('status/d');
            $data = [
                'tag_name' => $tag_name,
                'status' => $status,
                'update_time' => time()
            ];
            //查看标签名称是否可用
            $name_tag_info = $tagModel->getTagOneByWhere(['tag_name'=>$tag_name]);
            if(!empty($tag_info)){
                if(!empty($name_tag_info) && $name_tag_info['tag_id'] != $tag_id){
                    return json(['status'=>500,'msg'=>'标签名称已存在，不可使用']);
                }
                $where['tag_id'] = $tag_id;
            }else{
                if(!empty($name_tag_info)){
                    return json(['status'=>500,'msg'=>'标签已存在无需重复添加']);
                }
                $data['create_time'] = time();
            }
            if($result_tag_id = $tagModel->saveTag($data,$where)){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                if(!empty($where)){
                    $log_note = '编辑博客标签-'.$tag_info['tag_name'];
                    $referr_url = Url::build('blog_tag/tagEdit',['tag_id'=>$tag_id]);
                }else{
                    $log_note = '添加博客标签-'.$tag_name;
                    $referr_url = Url::build('blog_tag/tagEdit',['tag_id'=>$result_tag_id]);
                }
                $actionLogData = [
                    'admin_id' => Session::get('admin.admin_id'),
                    'log_note' => $log_note,
                    'log_url' => Request::instance()->url(),
                    'log_data' => serialize($data),
                    'log_action_ip' => ip2long(Request::instance()->ip()),
                    'log_create_time' => time()
                ];
                $actionLogModel->saveActionLog($actionLogData);
                return json(['status'=>200,'msg'=>'操作成功','referr_url'=>$referr_url]);
            }else{
                return json(['status'=>500,'msg'=>'操作失败']);
            }
        }
    }
}