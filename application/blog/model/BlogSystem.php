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

class BlogSystem extends Model
{
    /*
     * 根据条件获取一条博客配置信息
     */
    public function getSystemOneByWhere($where){
        return Db::name('blog_system')->where($where)->find();
    }
    /*
     * 根据条件，得到博客配置
     * @param $where
     */
    public function getSystemMultipleByWhere($where,$order = ''){
        return Db::name('blog_system')->where($where)->order($order)->select();
    }
}