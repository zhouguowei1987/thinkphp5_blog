<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/23
 * Time: 11:05
 */

namespace app\admin\controller;

use think\Request;
use think\Url;
use think\Session;

class BlogSystem extends Base
{
    /*
     * 博客SEO设置
     */
    public function info(){
        $systemModel = new \app\admin\model\BlogSystem();
        if(Request::instance()->isAjax()){
            $blog_name = Request::instance()->post('blog_name/s','');
            $blog_keywords = Request::instance()->post('blog_keywords/s','');
            $blog_description = Request::instance()->post('blog_description/s','');
            $data = [
                'blog_name' => $blog_name,
                'blog_keywords' => $blog_keywords,
                'blog_description' => $blog_description
            ];
            foreach($data as $k=>$v){
                $system_info = $systemModel->getSystemOneByWhere(['system_name'=>$k]);
                $systemData = [
                    'system_name' => $k,
                    'system_value' => $v,
                    'update_time' => time()
                ];
                if(empty($system_info)){
                    $systemData['create_time'] = time();
                    $systemWhere = [];
                }else{
                    $systemWhere['system_id'] = $system_info['system_id'];
                }
                if(!$systemModel->saveSystem($systemData,$systemWhere)){
                    break;
                    return json(['status' => 500, 'msg' => '操作失败']);
                }
            }
            //添加后台行为操作日志
            $actionLogModel = new \app\admin\model\AdminActionLog();
            $log_note = '编辑博客配置';
            $referr_url = Url::build('blog_system/info');
            $actionLogData = [
                'admin_id' => Session::get('admin.admin_id'),
                'log_note' => $log_note,
                'log_url' => Request::instance()->url(),
                'log_data' => serialize($data),
                'log_action_ip' => ip2long(Request::instance()->ip()),
                'log_create_time' => time()
            ];
            $actionLogModel->saveActionLog($actionLogData);
            return json(['status' => 200, 'msg' => '操作成功', 'referr_url' => $referr_url]);
        }else{
            $system = $systemModel->getSystemMultipleByWhere([]);
            $blog_system = [];
            if(!empty($system)){
                foreach($system as $k=>$v){
                    $blog_system[$v['system_name']] = $v['system_value'];
                }
            }
            $this->assign('blog_system',$blog_system);
            return $this->fetch('info');
        }
    }
}