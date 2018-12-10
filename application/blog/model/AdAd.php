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

class AdAd extends Model
{
    /*
     * 根据条件获取一条广告信息
     */
    public function getAdOneByWhere($where){
        return Db::name('ad')->where($where)->find();
    }
    /*
     * 根据条件，得到广告
     * @param $where
     */
    public function getAdMultipleByWhere($where,$order = ''){
        return Db::name('ad')->where($where)->order($order)->select();
    }
}