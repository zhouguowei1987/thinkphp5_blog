<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/23
 * Time: 16:55
 */

namespace app\admin\controller;

use think\Request;
use think\Session;
use think\Url;

class BlogCategory extends Base
{
    /*
     * 博客分类列表
     */
    public function categoryIndex(){
        $categoryModel = new \app\admin\model\BlogCategory();
        $category = $categoryModel->getCategoryMultipleByWhere([]);
        $category = get_tree($category,0,'pid','category_id');
        $this->assign('category',$category);
        return $this->fetch('category_index');
    }
    /*
     * ajax博客分类菜单显示
     */
    public function ajaxUpdateCategoryStatus(){
        if(Request::instance()->isAjax()){
            if(Request::instance()->has('category_id') && Request::instance()->has('status')){
                $category_id = Request::instance()->post('category_id/d');
                $categoryModel = new \app\admin\model\BlogCategory();
                $category_info = $categoryModel->getCategoryOneByWhere(['category_id'=>$category_id]);
                if(!empty($category_info)){
                    $where['category_id'] = $category_id;
                    $status = Request::instance()->post('status/d');
                    $data = [
                        'status' => $status,
                        'update_time' => time()
                    ];
                    if($categoryModel->saveCategory($data,$where)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '';
                        if($status == 0){
                            $log_note = '博客分类隐藏-'.$category_info['category_name'];
                        }else if($status == 1){
                            $log_note = '博客分类显示-'.$category_info['category_name'];
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
                        return json(['status'=>200,'msg'=>'操作成功']);
                    }else{
                        return json(['status'=>500,'msg'=>'操作失败']);
                    }
                }else{
                    return json(['status'=>500,'msg'=>'博客分类不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }
    }
    /*
     * 删除博客分类
     */
    public function ajaxDeleteCategory(){
        $category_id = Request::instance()->param('category_id/d',0);
        $categoryModel = new \app\admin\model\BlogCategory();
        $category_info = $categoryModel->getCategoryOneByWhere(['category_id'=>$category_id]);
        if(!empty($category_info)){
            //查看是否有下级博客分类
            $child_category = $categoryModel->getCategoryMultipleByWhere(['pid'=>$category_info['category_id']]);
            if(!empty($child_category)){
                //上级节点信息
                return json(['status'=>500,'msg'=>'有子级节点不可删除']);
            }
            //查看分类下是否有文章
            $blogModel = new \app\admin\model\BlogBlog();
            $blogs = $blogModel->getBlogMultipleByWhere(['category_id'=>$category_id]);
            if(!empty($blogs)){
                return json(['status'=>500,'msg'=>'分类下有博文不可删除']);
            }
            if($categoryModel->deleteCategory(['category_id'=>$category_id])){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                $log_note = '删除博客分类-'.$category_info['category_name'];
                $actionLogData = [
                    'admin_id' => Session::get('admin.admin_id'),
                    'log_note' => $log_note,
                    'log_url' => Request::instance()->url(),
                    'log_data' => serialize(['category_id'=>$category_id]),
                    'log_action_ip' => ip2long(Request::instance()->ip()),
                    'log_create_time' => time()
                ];
                $actionLogModel->saveActionLog($actionLogData);
                return json(['status'=>200,'msg'=>'操作成功']);
            }else{
                return json(['status'=>500,'msg'=>'操作失败']);
            }
        }else{
            return json(['status'=>500,'msg'=>'博客分类不存在']);
        }
    }
    /*
     * 编辑博客分类
     */
    public function categoryEdit(){
        $category_id = Request::instance()->param('category_id/d',0);
        $categoryModel = new \app\admin\model\BlogCategory();
        $category_info = $categoryModel->getCategoryOneByWhere(['category_id'=>$category_id]);
        if(!empty($category_info)){
            if($category_info['pid'] != 0){
                //上级节点信息
                $parent_category_info = $categoryModel->getCategoryOneByWhere(['category_id'=>$category_info['pid']]);
                $this->assign('parent_category_info',$parent_category_info);
            }
            $this->assign('category_info',$category_info);
            return $this->fetch('category_edit');
        }else{
            return $this->fetch('common/404');
        }
    }
    /*
     * 添加博客分类
     */
    public function categoryAdd(){
        $pid = Request::instance()->param('pid/d',0);
        $categoryModel = new \app\admin\model\BlogCategory();
        if($pid){
            $parent_category_info = $categoryModel->getCategoryOneByWhere(['category_id'=>$pid]);
            $this->assign('parent_category_info',$parent_category_info);
        }
        return $this->fetch('category_add');
    }
    /*
     * ajax保存博客分类信息
     */
    public function ajaxSaveCategory(){
        if(Request::instance()->isAjax()){
            $category_id = Request::instance()->post('category_id/d',0);
            $categoryModel = new \app\admin\model\BlogCategory();
            $category_info = $categoryModel->getCategoryOneByWhere(['category_id'=>$category_id]);
            $where = [];
            $level = Request::instance()->post('level/d',1);
            $category_name = Request::instance()->post('category_name/s','');
            $pid = Request::instance()->post('pid/d',0);
            $status = Request::instance()->post('status/d',0);
            $list_order = Request::instance()->post('list_order/d',0);
            $data = [
                'level' => $level,
                'category_name' => $category_name,
                'pid' => $pid,
                'status' => $status,
                'list_order' => $list_order,
                'update_time' => time()
            ];
            if(!empty($category_info)){
                $where['category_id'] = $category_id;
            }else{
                $data['create_time'] = time();
            }
            if($result_category_id = $categoryModel->saveCategory($data,$where)){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                if(!empty($where)){
                    $log_note = '编辑博客分类-'.$category_info['category_name'];
                    $referr_url = Url::build('blog_category/categoryEdit',['category_id'=>$category_id]);
                }else{
                    $log_note = '添加博客分类-'.$category_name;
                    $referr_url = Url::build('blog_category/categoryEdit',['category_id'=>$result_category_id]);
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