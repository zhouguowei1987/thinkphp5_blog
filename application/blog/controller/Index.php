<?php

/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/22
 * Time: 17:59
 */
namespace app\blog\controller;

use think\Controller;
use think\Url;
use think\Request;

class Index extends Controller
{
    protected $beforeActionList = [
        'seo',
        'nav'
    ];
    /*
     * 博客seo信息
     */
    public function seo(){
        $systemModel = new \app\blog\model\BlogSystem();

        $system_blog_name = $systemModel->getSystemOneByWhere(['system_name'=>'blog_name']);
        $seo['blog_name'] = !empty($system_blog_name) ? $system_blog_name['system_value'] : '';

        $system_blog_keywords = $systemModel->getSystemOneByWhere(['system_name'=>'blog_keywords']);
        $seo['blog_keywords'] = !empty($system_blog_keywords) ? $system_blog_keywords['system_value'] : '';

        $system_blog_description = $systemModel->getSystemOneByWhere(['system_name'=>'blog_description']);
        $seo['blog_description'] = !empty($system_blog_description) ? $system_blog_description['system_value'] : '';
        $this->assign('seo',$seo);
    }
    /*
     * 博客导航信息
     */
    public function nav(){
        $navModel = new \app\blog\model\BlogNav();
        $nav = $navModel->getNavMultipleByWhere([],'list_order ASC');
        $nav = getTree($nav,0,'pid','nav_id');
        $this->assign('nav',$nav);
    }
    /*
     * 首页
     */
    public function index(){
        $blogModel = new \app\blog\model\BlogBlog();
        $index_blog = $blogModel->getBlogListByWhere(['is_index'=>1,'status'=>1],'blog_id,category_id,blog_title,blog_description,blog_thumb,blog_type,blog_url,create_time',0,15,'index_sort DESC,blog_id DESC');
        $categoryModel = new \app\blog\model\BlogCategory();
        foreach($index_blog as $k=>$v){
            //系统博文
            if($v['blog_type'] == 1){
                $index_blog[$k]['blog_url'] = Url::build('Index/infos',['blog_id'=>$v['blog_id']]);
            }
            $index_blog[$k]['create_time'] = date("Y-m-d",$v['create_time']);
            $category_info = $categoryModel->getCategoryOneByWhere(['category_id'=>$v['category_id']]);
            $index_blog[$k]['category_name'] = !empty($category_info) ? $category_info['category_name'] : '';
            //分类-博文列表url
            $index_blog[$k]['blog_category_url'] = Url::build('Index/lists',['category_id'=>$v['category_id']]);
        }
        $this->assign('index_blog',$index_blog);
        return $this->fetch('index');
    }
    /*
     * 关于我
     */
    public function about(){
        return $this->fetch('about');
    }
    /*
     * 模板分享
     */
    public function share(){
        return $this->fetch('share');
    }
    /*
     * 博客列表
     */
    public function lists(){
        return $this->fetch('lists');
    }
    /*
     * 学无止境
     */
    public function knowledge(){
        return $this->fetch('knowledge');
    }
    /*
     * 时间轴
     */
    public function times(){
        return $this->fetch('times');
    }
    /*
     * 博客详情
     * @param $blog_id
     */
    public function infos(){
        $blog_id = Request::instance()->param('blog_id/d',0);
        $blogModel = new \app\blog\model\BlogBlog();
        $blog_info = $blogModel->getBlogInfoOneByWhere(['blog_id'=>$blog_id]);
        if(!empty($blog_info)){
            $blog_info['create_time'] = date('Y-m-d',$blog_info['create_time']);
        }
        $this->assign('blog_info',$blog_info);
        return $this->fetch('infos');
    }
}