<?php

/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/22
 * Time: 17:59
 */
namespace app\blog\controller;

use think\Controller;

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
     * 博客日志
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
     */
    public function infos(){
        return $this->fetch('infos');
    }
}