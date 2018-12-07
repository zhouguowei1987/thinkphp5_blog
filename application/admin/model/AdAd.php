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

class AdAd extends Model
{
    /*
     * 获取广告列表
     * @param $offset
     * @param $limit
     * @param $where
     */
    public function getAdList($offset,$limit,$where){
        $iTotalCount = Db::name('ad')->where($where)->count();
        $iTotalRecords = Db::name('ad')->where($where)->limit($offset, $limit)->order('ad_id ASC')->select();
        $result = [];
        $result['iTotalCount'] = $iTotalCount;
        $result['iTotalRecords'] = $iTotalRecords;
        return $result;
    }
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
    /*
     * 保存广告信息
     * @param $data
     * @param @where
     */
    public function saveAd($data,$where = []){
        DB::startTrans();
        try{
            if(isset($where) && !empty($where)){
                if(DB::name('ad')->data($data)->where($where)->update() === false){
                    throw new Exception('更新失败');
                }
            }else{
                if(!Db::name('ad')->data($data)->insert()){
                    throw new Exception('添加失败');
                }
                $ad_id = Db::name('ad')->getLastInsID();
            }
            Db::commit();
            return isset($ad_id) ? $ad_id : true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}