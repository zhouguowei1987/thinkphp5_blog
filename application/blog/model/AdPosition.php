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

class AdPosition extends Model
{
    /*
     * 根据条件获取一条广告位信息
     */
    public function getPositionOneByWhere($where){
        return Db::name('ad_position')->where($where)->find();
    }
}