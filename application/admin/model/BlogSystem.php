<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/23
 * Time: 15:59
 */

namespace app\admin\model;

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
    /*
     * 保存博客配置信息
     * @param $data
     * @param @where
     */
    public function saveSystem($data,$where = []){
        DB::startTrans();
        try{
            if(isset($where) && !empty($where)){
                if(DB::name('blog_system')->data($data)->where($where)->update() === false){
                    throw new Exception('更新失败');
                }
            }else{
                if(!Db::name('blog_system')->data($data)->insert()){
                    throw new Exception('添加失败');
                }
                $system_id = Db::name('blog_system')->getLastInsID();
            }
            Db::commit();
            return isset($system_id) ? $system_id : true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}