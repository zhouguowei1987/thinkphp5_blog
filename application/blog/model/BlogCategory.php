<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/23
 * Time: 16:56
 */

namespace app\blog\model;

use think\Model;
use think\Db;

class BlogCategory extends Model
{
    /*
     * 根据条件获取一条博客分类信息
     */
    public function getCategoryOneByWhere($where){
        return Db::name('blog_category')->where($where)->find();
    }
    /*
     * 根据条件，得到博客分类
     * @param $where
     */
    public function getCategoryMultipleByWhere($where,$order = ''){
        return Db::name('blog_category')->where($where)->order($order)->select();
    }
}