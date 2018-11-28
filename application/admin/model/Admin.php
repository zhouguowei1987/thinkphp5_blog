<?php

/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/12
 * Time: 11:07
 */
namespace  app\admin\model;

use think\Model;
use think\Db;
use think\Exception;

class Admin extends Model
{
    /*
     * 获取管理员列表
     * @param $offset
     * @param $limit
     * @param $where
     */
    public function getAdminList($offset,$limit,$where){
        $iTotalCount = Db::name('admin')->where($where)->count();
        $iTotalRecords = Db::name('admin')->where($where)->limit($offset, $limit)->order('admin_id ASC')->select();
        $result = [];
        $result['iTotalCount'] = $iTotalCount;
        $result['iTotalRecords'] = $iTotalRecords;
        return $result;
    }
    /*
     * 根据条件获取一条管理员信息
     */
    public function getAdminOneByWhere($where){
        return Db::name('admin')->where($where)->find();
    }
    /*
     * 保存管理员信息
     * @param $data
     * @param @where
     */
    public function saveAdmin($data,$where = []){
        DB::startTrans();
        try{
            if(isset($where) && !empty($where)){
                if(Db::name('admin')->data($data)->where($where)->update() === false){
                    throw new Exception('更新失败');
                }
            }else{
                if(!Db::name('admin')->data($data)->insert()){
                    throw new Exception('添加失败');
                }
                $admin_id = Db::name('admin')->getLastInsID();
            }
            Db::commit();
            return isset($admin_id) ? $admin_id : true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}