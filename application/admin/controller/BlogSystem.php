<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/13
 * Time: 11:02
 */

namespace app\admin\controller;

use think\Request;
use think\Session;
use think\Url;

class BlogSystem extends Base
{
    /*
     * 博客配置列表
     */
    public function systemIndex(){
        return $this->fetch('system_index');
    }
    /*
     * ajax获取博客配置列表
     */
    public function ajaxGetSystemList(){
        if(Request::instance()->isAjax()){
            $page = Request::instance()->get('page/d',1);
            $limit = Request::instance()->get('limit/d',10);
            $offset = ($page - 1) * $limit;
            $systemModel = new \app\admin\model\BlogSystem();
            $where = [];
            if(Request::instance()->has('system_name')) {
                $system_name = Request::instance()->get('system_name/s');
                if ($system_name) {
                    $where['system_name'] = ['LIKE',"%{$system_name}%"];
                }
            }
            if(Request::instance()->has('system_code')) {
                $system_code = Request::instance()->get('system_code/s');
                if ($system_code) {
                    $where['system_code'] = $system_code;
                }
            }
            if(Request::instance()->has('status')) {
                $status = Request::instance()->get('status/d');
                if($status != 2){
                    $where['status'] = $status;
                }
            }
            $result = $systemModel->getBlogSystemList($offset,$limit,$where);
            if(!empty($result['iTotalRecords'])){
                foreach($result['iTotalRecords'] as $k=>$v){
                    //创建时间
                    $result['iTotalRecords'][$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
                }
            }
            $result = array(
                'code' => 0,
                'msg' => '获取成功',
                'count' => $result['iTotalCount'],
                'data' => $result['iTotalRecords']
            );
            return json($result);
        }
    }
    /*
     * ajax隐藏、显示博客配置
     */
    public function ajaxUpdateSystemStatus(){
        if(Request::instance()->isAjax()){
            if(Request::instance()->has('system_id') && Request::instance()->has('status')){
                $system_id = Request::instance()->post('system_id/d');
                $systemModel = new \app\admin\model\BlogSystem();
                $system_info = $systemModel->getSystemOneByWhere(['system_id'=>$system_id]);
                if(!empty($system_info)){
                    $where['system_id'] = $system_id;
                    $status = Request::instance()->post('status/d');
                    $data = [
                        'status' => $status,
                        'update_time' => time()
                    ];
                    if($systemModel->saveSystem($data,$where)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '';
                        if($status == 0){
                            $log_note = '隐藏博客配置-'.$system_info['system_name'];
                        }else if($status == 1){
                            $log_note = '显示博客配置-'.$system_info['system_name'];
                        }
                        $actionLogData = [
                            'blog_id' => Session::get('admin.admin_id'),
                            'log_note' => $log_note,
                            'log_url' => Request::instance()->url(),
                            'log_data' => serialize($data),
                            'log_action_ip' => ip2long(Request::instance()->ip()),
                            'log_create_time' => time()
                        ];
                        $actionLogModel->saveActionLog($actionLogData);
                        return json(['status'=>200,'msg'=>'操作成功']);
                    }else{
                        return json(['status'=>500,'msg'=>'操作失败']);
                    }
                }else{
                    return json(['status'=>500,'msg'=>'配置项不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }
    }
    /*
     * 编辑博客配置
     */
    public function systemEdit(){
        $system_id = Request::instance()->param('system_id/d',0);
        $systemModel = new \app\admin\model\BlogSystem();
        $system_info = $systemModel->getSystemOneByWhere(['system_id'=>$system_id]);
        if(!empty($system_info)){
            $this->assign('system_info',$system_info);
            return $this->fetch('system_edit');
        }else{
            return $this->fetch('common/404');
        }
    }
    /*
     * 添加博客配置
     */
    public function systemAdd(){
        return $this->fetch('system_add');
    }
    /*
     * ajax保存博客配置信息
     */
    public function ajaxSaveSystem(){
        if(Request::instance()->isAjax()){
            $system_id = Request::instance()->post('system_id/d',0);
            $systemModel = new \app\admin\model\BlogSystem();
            $system_info = $systemModel->getSystemOneByWhere(['system_id'=>$system_id]);
            $where = [];
            $system_name = str_replace([' '],'',Request::instance()->post('system_name/s'));
            $system_code = str_replace([' '],'',Request::instance()->post('system_code/s'));
            $status = Request::instance()->post('status/d');
            $data = [
                'system_name' => $system_name,
                'system_code' => $system_code,
                'status' => $status,
                'update_time' => time()
            ];
            //查看配置项code是否可用
            $code_system_info = $systemModel->getSystemOneByWhere(['system_code'=>$system_code]);
            if(!empty($system_info)){
                if(!empty($code_system_info) && $code_system_info['system_id'] != $system_id){
                    return json(['status'=>500,'msg'=>'配置项code已存在，不可使用']);
                }
                $where['system_id'] = $system_id;
            }else{
                if(!empty($code_system_info)){
                    return json(['status'=>500,'msg'=>'配置项code已存在无需重复添加']);
                }
                $data['create_time'] = time();
            }
            if($result_system_id = $systemModel->saveSystem($data,$where)){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                if(!empty($where)){
                    $log_note = '编辑博客配置-'.$system_info['system_name'];
                    $referr_url = Url::build('blog_system/systemEdit',['system_id'=>$system_id]);
                }else{
                    $log_note = '添加博客配置-'.$system_name;
                    $referr_url = Url::build('blog_system/systemEdit',['system_id'=>$result_system_id]);
                }
                $actionLogData = [
                    'admin_id' => Session::get('admin.admin_id'),
                    'log_note' => $log_note,
                    'log_url' => Request::instance()->url(),
                    'log_data' => serialize($data),
                    'log_action_ip' => ip2long(Request::instance()->ip()),
                    'log_create_time' => time()
                ];
                $actionLogModel->saveActionLog($actionLogData);
                return json(['status'=>200,'msg'=>'操作成功','referr_url'=>$referr_url]);
            }else{
                return json(['status'=>500,'msg'=>'操作失败']);
            }
        }
    }
}