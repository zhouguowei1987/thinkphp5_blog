<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/12
 * Time: 13:59
 */

namespace app\admin\model;

use think\Model;
use think\Db;
use think\Exception;

class AdminActionLog extends Model
{
    /*
     * 添加后台行为操作日志
     */
    public function saveActionLog($data){
        DB::startTrans();
        try{
            if(!Db::name('admin_action_log')->data($data)->insert()){
                throw new Exception('添加行为操作日志失败');
            }
            DB::commit();
            return true;
        }catch (Exception $e){
            Db::rollback();
            return false;
        }
    }
}