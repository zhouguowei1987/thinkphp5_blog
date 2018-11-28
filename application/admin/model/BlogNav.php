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

class BlogNav extends Model
{
    /*
     * 根据条件获取一条博客导航信息
     */
    public function getNavOneByWhere($where){
        return Db::name('blog_nav')->where($where)->find();
    }
    /*
     * 根据条件，得到博客导航
     * @param $where
     */
    public function getNavMultipleByWhere($where,$order = ''){
        return Db::name('blog_nav')->where($where)->order($order)->select();
    }
    /*
     * 保存博客导航信息
     * @param $data
     * @param @where
     */
    public function saveNav($data,$where = []){
        DB::startTrans();
        try{
            if(isset($where) && !empty($where)){
                if(DB::name('blog_nav')->data($data)->where($where)->update() === false){
                    throw new Exception('更新失败');
                }
            }else{
                if(!Db::name('blog_nav')->data($data)->insert()){
                    throw new Exception('添加失败');
                }
                $nav_id = Db::name('blog_nav')->getLastInsID();
            }
            Db::commit();
            return isset($nav_id) ? $nav_id : true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
    /*
     * 删除博客导航
     * @param $where
     */
    public function deleteNav($where = []){
        DB::startTrans();
        if(empty($where)){
            return false;
        }
        try{
            if(Db::name('blog_nav')->where($where)->delete() === false){
                throw new Exception('删除博客导航失败');
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}