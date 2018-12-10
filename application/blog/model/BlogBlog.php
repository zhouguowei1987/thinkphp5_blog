<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/23
 * Time: 15:59
 */

namespace app\blog\model;

use think\Model;
use think\Db;
use think\Url;

class BlogBlog extends Model
{
    /*
     * 博文分页
     */
    public function getBlogListPage($where,$filed = '*',$order = 'blog_id DESC'){
        $list = Db::name('blog')->where($where)->field($filed)->order($order)->paginate()->each(function($item, $key){
            if($item['blog_type'] == 1){
                $item['blog_url'] = Url::build('index/infos',['blog_id'=>$item['blog_id']]);
            }
            $item['create_time'] = date("Y-m-d",$item['create_time']);
            $category_info = Db::name('blog_category')->where(['category_id'=>$item['category_id']])->find();
            $item['category_name'] = !empty($category_info) ? $category_info['category_name'] : '';
            $item['blog_category_url'] = Url::build('Index/lists',['category_id'=>$item['category_id']]);
            return $item;
        });
        $page = $list->render();
        $result = [];
        $result['list'] = $list;
        $result['page'] = $page;
        return $result;
    }
    /*
     * 根据博文条件，得到博文详情
     */
    public function getBlogInfoOneByWhere($where){
        return Db::name('blog')->where($where)->find();
    }
    /*
     * 根据条件，得到博客配置
     * @param $where
     */
    public function getSystemMultipleByWhere($where,$order = ''){
        return Db::name('blog_system')->where($where)->order($order)->select();
    }
}