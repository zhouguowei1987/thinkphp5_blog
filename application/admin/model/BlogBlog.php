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

class BlogBlog extends Model
{
    /*
     * 获取博文列表
     * @param $offset
     * @param $limit
     * @param $where
     */
    public function getBlogList($offset,$limit,$where){
        $iTotalCount = Db::name('blog')->where($where)->count();
        $iTotalRecords = Db::name('blog')->where($where)->limit($offset, $limit)->order('blog_id ASC')->select();
        $result = [];
        $result['iTotalCount'] = $iTotalCount;
        $result['iTotalRecords'] = $iTotalRecords;
        return $result;
    }
    /*
     * 根据条件获取一条博客博文信息
     */
    public function getBlogOneByWhere($where){
        return Db::name('blog')->where($where)->find();
    }
    /*
     * 根据条件，得到博客博文
     * @param $where
     */
    public function getBlogMultipleByWhere($where,$order = ''){
        return Db::name('blog')->where($where)->order($order)->select();
    }
    /*
     * 保存博客博文信息
     * @param $data
     * @param @where
     */
    public function saveBlog($data,$where = []){
        DB::startTrans();
        try{
            if(isset($where) && !empty($where)){
                if(DB::name('blog')->data($data)->where($where)->update() === false){
                    throw new Exception('更新失败');
                }
            }else{
                if(!Db::name('blog')->data($data)->insert()){
                    throw new Exception('添加失败');
                }
                $blog_id = Db::name('blog')->getLastInsID();
            }
            Db::commit();
            return isset($blog_id) ? $blog_id : true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
    /*
     * 删除博客博文
     * @param $where
     */
    public function deleteBlog($where = []){
        DB::startTrans();
        if(empty($where)){
            return false;
        }
        try{
            if(Db::name('blog')->where($where)->delete() === false){
                throw new Exception('删除博客博文失败');
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}