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

class BlogTag extends Model
{
    /*
     * 根据条件获取一条博客标签信息
     */
    public function getTagOneByWhere($where){
        return Db::name('blog_tag')->where($where)->find();
    }
    /*
     * 根据条件，得到博客标签
     * @param $where
     */
    public function getTagMultipleByWhere($where,$order = ''){
        return Db::name('blog_tag')->where($where)->order($order)->select();
    }
}