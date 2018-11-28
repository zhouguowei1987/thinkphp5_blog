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

class BlogBlog extends Base
{
    /*
     * 博文列表
     */
    public function blogIndex(){
        //博文分类
        $categoryModel = new \app\admin\model\BlogCategory();
        $category = $categoryModel->getCategoryMultipleByWhere(['status'=>1]);
        $category = getTree($category,0,'pid','category_id');
        $this->assign('category',$category);
        return $this->fetch('blog_index');
    }
    /*
     * ajax获取博文列表
     */
    public function ajaxGetBlogList(){
        if(Request::instance()->isAjax()){
            $page = Request::instance()->get('page/d',1);
            $limit = Request::instance()->get('limit/d',10);
            $offset = ($page - 1) * $limit;
            $BlogModel = new \app\admin\model\BlogBlog();
            $where = [];
            if(Request::instance()->has('category_id')) {
                $category_id = Request::instance()->get('category_id/d');
                if ($category_id) {
                    $where['category_id'] = $category_id;
                }
            }
            $result = $BlogModel->getBlogList($offset,$limit,$where);
            if(!empty($result['iTotalRecords'])){
                $categoryModel = new \app\admin\model\BlogCategory();
                foreach($result['iTotalRecords'] as $k=>$v){
                    //分类名称
                    $category_info = $categoryModel->getCategoryOneByWhere(['category_id'=>$v['category_id']]);
                    $result['iTotalRecords'][$k]['category_name'] = !empty($category_info) ? $category_info['category_name'] : '';
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
     * ajax隐藏、显示博文
     */
    public function ajaxUpdateBlogStatus(){
        if(Request::instance()->isAjax()){
            if(Request::instance()->has('blog_id') && Request::instance()->has('status')){
                $blog_id = Request::instance()->post('blog_id/d');
                $blogModel = new \app\admin\model\BlogBlog();
                $blog_info = $blogModel->getBlogOneByWhere(['blog_id'=>$blog_id]);
                if(!empty($blog_info)){
                    $where['blog_id'] = $blog_id;
                    $status = Request::instance()->post('status/d');
                    $data = [
                        'status' => $status,
                        'update_time' => time()
                    ];
                    if($blogModel->saveBlog($data,$where)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '';
                        if($status == 0){
                            $log_note = '隐藏博文-'.$blog_info['blog_title'];
                        }else if($status == 1){
                            $log_note = '显示博文-'.$blog_info['blog_title'];
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
                    return json(['status'=>500,'msg'=>'博文不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }
    }
    /*
     * 编辑博文
     */
    public function blogEdit(){
        $blog_id = Request::instance()->param('blog_id/d',0);
        $blogModel = new \app\admin\model\BlogBlog();
        $blog_info = $blogModel->getBlogOneByWhere(['blog_id'=>$blog_id]);
        if(!empty($blog_info)){
            //博文分类
            $categoryModel = new \app\admin\model\BlogCategory();
            $category = $categoryModel->getCategoryMultipleByWhere(['status'=>1]);
            $category = getTree($category,0,'pid','category_id');
            $this->assign('category',$category);
            $this->assign('blog_info',$blog_info);
            return $this->fetch('blog_edit');
        }else{
            return $this->fetch('common/404');
        }
    }
    /*
     * 添加博文
     */
    public function blogAdd(){
        //博文分类
        $categoryModel = new \app\admin\model\BlogCategory();
        $category = $categoryModel->getCategoryMultipleByWhere(['status'=>1]);
        $category = getTree($category,0,'pid','category_id');
        $this->assign('category',$category);
        return $this->fetch('blog_add');
    }
    /*
     * ajax保存博文信息
     */
    public function ajaxSaveBlog(){
        if(Request::instance()->isAjax()){
            $blog_id = Request::instance()->post('blog_id/d',0);
            $blogModel = new \app\admin\model\BlogBlog();
            $blog_info = $blogModel->getBlogOneByWhere(['blog_id'=>$blog_id]);
            $where = [];
            $category_id = Request::instance()->post('category_id/d');
            $blog_title = Request::instance()->post('blog_title/s');
            $blog_keywords = Request::instance()->post('blog_keywords/s','');
            $blog_description = Request::instance()->post('blog_description/s','');
            $blog_thumb = Request::instance()->post('blog_thumb/s','');
            $blog_detail = Request::instance()->post('blog_detail/s','');
            $list_sort = Request::instance()->post('list_sort/d');
            $blog_type = Request::instance()->post('blog_type/s');
            $blog_url = Request::instance()->post('blog_url/s');
            $is_index = Request::instance()->post('is_index/d');
            $index_sort = Request::instance()->post('index_sort/d');
            $is_top = Request::instance()->post('is_top/d');
            $top_sort = Request::instance()->post('top_sort/d');
            $allow_comment = Request::instance()->post('allow_comment/d');
            $data = [
                'category_id' => $category_id,
                'blog_title' => $blog_title,
                'blog_keywords' => $blog_keywords,
                'blog_description' => $blog_description,
                'blog_thumb' => $blog_thumb,
                'blog_detail' => $blog_detail,
                'list_sort' => $list_sort,
                'blog_type' => $blog_type,
                'blog_url' => $blog_url,
                'is_index' => $is_index,
                'index_sort' => $index_sort,
                'is_top' => $is_top,
                'top_sort' => $top_sort,
                'allow_comment' => $allow_comment,
                'update_time' => time()
            ];
            if(!empty($blog_info)){
                $where['blog_id'] = $blog_id;
            }else{
                $data['create_time'] = time();
            }
            if($result_blog_id = $blogModel->saveBlog($data,$where)){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                if(!empty($where)){
                    $log_note = '编辑博文-'.$blog_info['blog_title'];
                    $referr_url = Url::build('blog_blog/blogEdit',['blog_id'=>$blog_id]);
                }else{
                    $log_note = '添加博文-'.$blog_title;
                    $referr_url = Url::build('blog_blog/blogEdit',['blog_id'=>$result_blog_id]);
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