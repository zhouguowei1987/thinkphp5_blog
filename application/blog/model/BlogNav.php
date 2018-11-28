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

class BlogNav extends Model
{
    /*
     * 根据条件获取一条博客导航信息
     */
    public function getNavOneByWhere($where){
        return Db::name('blog_nav')->where($where)->find();
    }
    /*
     * 根据条件，得到博客导航
     * @param $where
     */
    public function getNavMultipleByWhere($where,$order = ''){
        return Db::name('blog_nav')->where($where)->order($order)->select();
    }
}