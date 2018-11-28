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

class BlogCategory extends Model
{
    /*
     * 根据条件获取一条博客分类信息
     */
    public function getCategoryOneByWhere($where){
        return Db::name('blog_category')->where($where)->find();
    }
    /*
     * 根据条件，得到博客分类
     * @param $where
     */
    public function getCategoryMultipleByWhere($where,$order = ''){
        return Db::name('blog_category')->where($where)->order($order)->select();
    }
    /*
     * 保存博客分类信息
     * @param $data
     * @param @where
     */
    public function saveCategory($data,$where = []){
        DB::startTrans();
        try{
            if(isset($where) && !empty($where)){
                if(DB::name('blog_category')->data($data)->where($where)->update() === false){
                    throw new Exception('更新失败');
                }
            }else{
                if(!Db::name('blog_category')->data($data)->insert()){
                    throw new Exception('添加失败');
                }
                $category_id = Db::name('blog_category')->getLastInsID();
            }
            Db::commit();
            return isset($category_id) ? $category_id : true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
    /*
     * 删除博客分类
     * @param $where
     */
    public function deleteCategory($where = []){
        DB::startTrans();
        if(empty($where)){
            return false;
        }
        try{
            if(Db::name('blog_category')->where($where)->delete() === false){
                throw new Exception('删除博客分类失败');
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}