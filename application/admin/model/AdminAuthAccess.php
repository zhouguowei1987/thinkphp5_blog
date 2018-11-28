<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/12
 * Time: 13:31
 */

namespace app\admin\model;

use think\Model;
use think\Db;

class AdminAuthAccess extends Model
{
    /*
     * 根据条件，得到一条角色权限信息
     * @param $where
     */
    public function getAuthAccessOneByWhere($where){
        return Db::name('admin_auth_access')->where($where)->find();
    }
    /*
     * 根据条件，得到角色的权限节点
     * @param $where;
     */
    public function getAuthAccessMultipleByWhere($where){
        return Db::name('admin_auth_access')->where($where)->select();
    }
    /*
     * 保存角色权限
     * @param $role_id
     * @param $data
     */
    public function saveAuthAccess($role_id,$data){
        DB::startTrans();
        try{
            //删除已有权限
            if(DB::name('admin_auth_access')->where(['role_id'=>$role_id])->delete() === false){
                throw new Exception('删除原有权限失败');
            }
            //批量插入权限
            if(!empty($data)){
                if(!Db::name('admin_auth_access')->insertAll($data)){
                    throw new Exception('批量添加权限失败');
                }
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}