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

        $system_blog_name = $systemModel->getSystemOneByWhere(['system_code'=>'blog_name','status'=>1]);
        $seo['blog_name'] = !empty($system_blog_name) ? $system_blog_name['system_value'] : '';

        $system_blog_keywords = $systemModel->getSystemOneByWhere(['system_code'=>'blog_keywords','status'=>1]);
        $seo['blog_keywords'] = !empty($system_blog_keywords) ? $system_blog_keywords['system_value'] : '';

        $system_blog_description = $systemModel->getSystemOneByWhere(['system_code'=>'blog_description','status'=>1]);
        $seo['blog_description'] = !empty($system_blog_description) ? $system_blog_description['system_value'] : '';
        $this->assign('seo',$seo);
    }
    /*
     * 博客导航信息
     */
    public function nav(){
        $navModel = new \app\blog\model\BlogNav();
        $nav = $navModel->getNavMultipleByWhere([],'list_order ASC');
        $nav = get_tree($nav,0,'pid','nav_id');
        $this->assign('nav',$nav);
    }
    /*
     * 首页
     */
    public function index(){
        //幻灯片广告
        $blog_index_banner = get_blog_ad_list('blog_index_banner',1);
        if($blog_index_banner){
            $this->assign('blog_index_banner',$blog_index_banner);
        }
        $blogModel = new \app\blog\model\BlogBlog();
        $index_blog = $blogModel->getBlogListPage(['is_index'=>1,'status'=>1],'blog_id,category_id,blog_title,blog_description,blog_thumb,blog_type,blog_url,create_time','index_sort DESC,blog_id DESC');
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
     * @param $category_id
     * @param $page
     */
    public function lists(){
        if(Request::instance()->has('category_id')){
            $category_id = Request::instance()->param('category_id/d',0);
            $categoryModel = new \app\blog\model\BlogCategory();
            $category_info = $categoryModel->getCategoryOneByWhere(['category_id'=>$category_id]);
            if(!empty($category_info)){
                $blogModel = new \app\blog\model\BlogBlog();
                $blog_list = $blogModel->getBlogListPage(['category_id'=>$category_id,'status'=>1],'blog_id,category_id,blog_title,blog_description,blog_thumb,blog_type,blog_url,create_time','blog_id DESC');
                $this->assign('blog_list',$blog_list);
                return $this->fetch('lists');
            }
        }
        return $this->fetch('common/404');
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
            $seo['blog_name'] = !empty($blog_info['blog_title']) ? $blog_info['blog_title'] : '';
            $seo['blog_keywords'] = !empty($blog_info['blog_keywords']) ? $blog_info['blog_keywords'] : '';
            $seo['blog_description'] = !empty($blog_info['blog_description']) ? $blog_info['blog_description'] : '';
            $this->assign('seo',$seo);

            $categoryModel = new \app\blog\model\BlogCategory();
            $category_info = $categoryModel->getCategoryOneByWhere(['category_id'=>$blog_info['category_id']]);
            $blog_info['category_name'] = !empty($category_info) ? $category_info['category_name'] : '';
            if(!empty($blog_info['blog_tags'])){
                $blog_tags = explode(',',$blog_info['blog_tags']);
                $blog_info['blog_tags'] = [];
                $tagModel = new \app\blog\model\BlogTag();
                foreach($blog_tags as $v){
                    $tag_info = $tagModel->getTagOneByWhere(['tag_id'=>$v]);
                    if(!empty($tag_info)){
                        $blog_info['blog_tags'][] = [
                            'tag_name' => $tag_info['tag_name'],
                            'tag_url' => Url::build('index/tags',['tag_id'=>$tag_info['tag_id']])
                        ];
                    }
                }
            }
            $blog_info['create_time'] = date('Y-m-d',$blog_info['create_time']);
            $this->assign('blog_info',$blog_info);
            return $this->fetch('infos');
        }
        return $this->fetch('common/404');
    }
    /*
     * tag标签列表
     * @param tag_id
     */
    public function tags(){
        return $this->fetch('tags');
    }
}