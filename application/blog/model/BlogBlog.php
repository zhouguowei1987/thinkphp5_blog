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

class BlogBlog extends Model
{
    /*
     * 根据条件，获取博文列表
     */
    public function getBlogListByWhere($where,$filed = '*',$offset = 0,$length = 10,$order){
        return Db::name('blog')->field($filed)->where($where)->order($order)->limit($offset,$length)->select();
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