<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/12
 * Time: 13:15
 */

namespace app\admin\model;

use think\Model;
use think\Db;

class AdminRule extends Model
{
    /*
     * 根据条件获取一条权限节点信息
     */
    public function getRuleOneByWhere($where){
        return Db::name('admin_rule')->where($where)->find();
    }
    /*
     * 根据条件，得到权限节点
     * @param $where
     */
    public function getRuleMultipleByWhere($where,$order = ''){
        return Db::name('admin_rule')->where($where)->order($order)->select();
    }
    /*
     * 保存权限节点信息
     * @param $data
     * @param @where
     */
    public function saveRule($data,$where = []){
        DB::startTrans();
        try{
            if(isset($where) && !empty($where)){
                if(DB::name('admin_rule')->data($data)->where($where)->update() === false){
                    throw new Exception('更新失败');
                }
            }else{
                if(!Db::name('admin_rule')->data($data)->insert()){
                    throw new Exception('添加失败');
                }
                $rule_id = Db::name('admin_rule')->getLastInsID();
            }
            Db::commit();
            return isset($rule_id) ? $rule_id : true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
    /*
     * 删除权限节点
     * @param $where
     */
    public function deleteRule($where = []){
        DB::startTrans();
        if(empty($where)){
            return false;
        }
        try{
            if(DB::name('admin_auth_access')->where($where)->delete() === false){
                throw new Exception('删除管理员权限失败');
            }
            if(Db::name('admin_rule')->where($where)->delete() === false){
                throw new Exception('删除权限节点失败');
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}