<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/13
 * Time: 16:33
 */

namespace app\admin\model;

use think\Model;
use think\Db;

class AdminAuthRole extends Model
{
    /*
     * 获取角色列表
     * @param $offset
     * @param $limit
     * @param $where
     */
    public function getAuthRoleList($offset,$limit,$where){
        $iTotalCount = Db::name('admin_auth_role')->where($where)->count();
        $iTotalRecords = Db::name('admin_auth_role')->where($where)->limit($offset, $limit)->order('role_id ASC')->select();
        $result = [];
        $result['iTotalCount'] = $iTotalCount;
        $result['iTotalRecords'] = $iTotalRecords;
        return $result;
    }
    /*
     * 根据条件获取一条角色信息
     */
    public function getAuthRoleOneByWhere($where){
        return Db::name('admin_auth_role')->where($where)->find();
    }
    /*
     * 根据条件获取多条角色信息
     */
    public function getAuthRoleMultipleByWhere($where){
        return Db::name('admin_auth_role')->where($where)->select();
    }
    /*
     * 保存角色信息
     * @param $data
     * @param @where
     */
    public function saveAuthRole($data,$where = []){
        DB::startTrans();
        try{
            if(isset($where) && !empty($where)){
                if(DB::name('admin_auth_role')->data($data)->where($where)->update() === false){
                    throw new Exception('更新失败');
                }
            }else{
                if(!Db::name('admin_auth_role')->data($data)->insert()){
                    throw new Exception('添加失败');
                }
                $role_id = Db::name('admin_auth_role')->getLastInsID();
            }
            Db::commit();
            return isset($role_id) ? $role_id : true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}