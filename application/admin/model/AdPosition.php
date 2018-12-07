<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/23
 * Time: 16:56
 */

namespace app\admin\model;

use think\Model;
use think\Db;

class AdPosition extends Model
{
    /*
     * 获取广告位列表
     * @param $offset
     * @param $limit
     * @param $where
     */
    public function getPositionList($offset,$limit,$where){
        $iTotalCount = Db::name('ad_position')->where($where)->count();
        $iTotalRecords = Db::name('ad_position')->where($where)->limit($offset, $limit)->order('position_id ASC')->select();
        $result = [];
        $result['iTotalCount'] = $iTotalCount;
        $result['iTotalRecords'] = $iTotalRecords;
        return $result;
    }
    /*
     * 根据条件获取一条广告位信息
     */
    public function getPositionOneByWhere($where){
        return Db::name('ad_position')->where($where)->find();
    }
    /*
     * 根据条件，得到广告位
     * @param $where
     */
    public function getPositionMultipleByWhere($where,$order = ''){
        return Db::name('ad_position')->where($where)->order($order)->select();
    }
    /*
     * 保存广告位信息
     * @param $data
     * @param @where
     */
    public function savePosition($data,$where = []){
        DB::startTrans();
        try{
            if(isset($where) && !empty($where)){
                if(DB::name('ad_position')->data($data)->where($where)->update() === false){
                    throw new Exception('更新失败');
                }
            }else{
                if(!Db::name('ad_position')->data($data)->insert()){
                    throw new Exception('添加失败');
                }
                $position_id = Db::name('ad_position')->getLastInsID();
            }
            Db::commit();
            return isset($position_id) ? $position_id : true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}