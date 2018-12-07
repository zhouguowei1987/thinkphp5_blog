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

class BlogTag extends Model
{
    /*
     * 获取博客标签列表
     * @param $offset
     * @param $limit
     * @param $where
     */
    public function getBlogTagList($offset,$limit,$where){
        $iTotalCount = Db::name('blog_tag')->where($where)->count();
        $iTotalRecords = Db::name('blog_tag')->where($where)->limit($offset, $limit)->order('tag_id DESC')->select();
        $result = [];
        $result['iTotalCount'] = $iTotalCount;
        $result['iTotalRecords'] = $iTotalRecords;
        return $result;
    }
    /*
     * 根据条件获取一条博客标签信息
     */
    public function getTagOneByWhere($where){
        return Db::name('blog_tag')->where($where)->find();
    }
    /*
     * 根据条件，得到博客标签
     * @param $where
     */
    public function getTagMultipleByWhere($where,$order = ''){
        return Db::name('blog_tag')->where($where)->order($order)->select();
    }
    /*
     * 保存博客标签信息
     * @param $data
     * @param @where
     */
    public function saveTag($data,$where = []){
        DB::startTrans();
        try{
            if(isset($where) && !empty($where)){
                if(DB::name('blog_tag')->data($data)->where($where)->update() === false){
                    throw new Exception('更新失败');
                }
            }else{
                if(!Db::name('blog_tag')->data($data)->insert()){
                    throw new Exception('添加失败');
                }
                $tag_id = Db::name('blog_tag')->getLastInsID();
            }
            Db::commit();
            return isset($tag_id) ? $tag_id : true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
    /*
     * 删除博客标签
     * @param $where
     */
    public function deleteBlog($where = []){
        DB::startTrans();
        if(empty($where)){
            return false;
        }
        try{
            if(Db::name('blog')->where($where)->delete() === false){
                throw new Exception('删除博客标签失败');
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            DB::rollback();
            return false;
        }
    }
}