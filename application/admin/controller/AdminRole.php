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

class adminRole extends Base
{
    /*
     * 角色列表
     */
    public function roleIndex(){
        return $this->fetch('role_index');
    }
    /*
     * ajax获取角色列表
     */
    public function ajaxGetRoleList(){
        if(Request::instance()->isAjax()){
            $page = Request::instance()->get('page/d',1);
            $limit = Request::instance()->get('limit/d',10);
            $offset = ($page - 1) * $limit;
            $roleModel = new \app\admin\model\AdminAuthRole();
            $where = [];
            if(Request::instance()->has('role_name')){
                $role_name = Request::instance()->get('role_name/s');
                if(!empty($role_name)){
                    $where['role_name'] = ["LIKE","%{$role_name}%"];
                }
            }
            $result = $roleModel->getAuthRoleList($offset,$limit,$where);
            if(!empty($result['iTotalRecords'])){
                $AuthAccessModel = new \app\admin\model\AdminAuthAccess();
                $ruleModel = new \app\admin\model\AdminRule();
                foreach($result['iTotalRecords'] as $k=>$v){
                    //角色拥有的权限节点
                    $authAccess = $AuthAccessModel->getAuthAccessMultipleByWhere(['role_id'=>$v['role_id']]);
                    $rule_ids = [];
                    $rule_names = [];
                    if(!empty($authAccess)){
                        foreach($authAccess as $vv){
                            $rule_ids[] = $vv['rule_id'];
                        }
                        //根据权限节点，得到权限节点详情
                        $rule = $ruleModel->getRuleMultipleByWhere(['rule_id'=>['IN',$rule_ids]]);
                        foreach($rule as $vvv){
                            $rule_names[] = $vvv['rule_name'];
                        }
                    }
                    $result['iTotalRecords'][$k]['rule_names'] = !empty($rule_names) ? implode(',',$rule_names) : '';
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
     * ajax启用、停用角色
     */
    public function ajaxUpdateRoleStatus(){
        if(Request::instance()->isAjax()){
            if(Request::instance()->has('role_id') && Request::instance()->has('status')){
                $role_id = Request::instance()->post('role_id/d');
                $authRoleModel = new \app\admin\model\AdminAuthRole();
                $role_info = $authRoleModel->getAuthRoleOneByWhere(['role_id'=>$role_id]);
                if(!empty($role_info)){
                    $where['role_id'] = $role_id;
                    $status = Request::instance()->post('status/d');
                    $data = [
                        'status' => $status,
                        'update_time' => time()
                    ];
                    if($authRoleModel->saveAuthRole($data,$where)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '';
                        if($status == 0){
                            $log_note = '停用角色-'.$role_info['role_name'];
                        }else if($status == 1){
                            $log_note = '启用角色-'.$role_info['role_name'];
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
                        return json(['status'=>200,'msg'=>'操作成功']);
                    }else{
                        return json(['status'=>500,'msg'=>'操作失败']);
                    }
                }else{
                    return json(['status'=>500,'msg'=>'角色信息不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }
    }
    /*
     * 编辑角色
     */
    public function roleEdit(){
        $role_id = Request::instance()->param('role_id/d',0);
        $authRoleModel = new \app\admin\model\AdminAuthRole();
        $role_info = $authRoleModel->getAuthRoleOneByWhere(['role_id'=>$role_id]);
        if(!empty($role_info)){
            $this->assign('role_info',$role_info);
            return $this->fetch('role_edit');
        }else{
            return $this->fetch('common/404');
        }
    }
    /*
     * 添加角色
     */
    public function roleAdd(){
        return $this->fetch('role_add');
    }
    /*
     * ajax保存角色信息
     */
    public function ajaxSaveRole(){
        if(Request::instance()->isAjax()){
            $role_id = Request::instance()->post('role_id/d',0);
            $authRoleModel = new \app\admin\model\AdminAuthRole();
            $role_info = $authRoleModel->getAuthRoleOneByWhere(['role_id'=>$role_id]);
            $where = [];
            $role_name = Request::instance()->post('role_name/s');
            $remark = Request::instance()->post('remark/s');
            $status = Request::instance()->post('status/d',0);
            $data = [
                'role_name' => $role_name,
                'remark' => $remark,
                'status' => $status,
                'update_time' => time()
            ];
            if(!empty($role_info)){
                $where['role_id'] = $role_id;
            }else{
                $data['create_time'] = time();
            }
            if($result_role_id = $authRoleModel->saveAuthRole($data,$where)){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                if(!empty($where)){
                    $log_note = '编辑角色-'.$role_info['role_name'];
                    $referr_url = Url::build('admin_role/roleEdit',['role_id'=>$role_id]);
                }else{
                    $log_note = '添加角色-'.$role_name;
                    $referr_url = Url::build('admin_role/roleEdit',['role_id'=>$result_role_id]);
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
    /*
     * 角色权限
     * @param $role_id
     */
    public function roleAuthAccess(){
        $ruleModel = new \app\admin\model\AdminRule();
        $authRoleModel = new \app\admin\model\AdminAuthRole();
        $authAccessModel = new \app\admin\model\AdminAuthAccess();
        if(Request::instance()->isAjax()){
            //保存角色权限
            if(Request::instance()->has('role_id') && Request::instance()->has('rule_ids')){
                $role_id = Request::instance()->post('role_id/d',0);
                $role_info = $authRoleModel->getAuthRoleOneByWhere(['role_id'=>$role_id]);
                if(!empty($role_info)){
                    $rule_ids = Request::instance()->post('rule_ids/s','');
                    $data = [];
                    if(!empty($rule_ids)){
                        foreach(explode(',',$rule_ids) as $v){
                            $data[] = [
                                'role_id' => $role_id,
                                'rule_id' => $v
                            ];
                        }
                    }
                    if($authAccessModel->saveAuthAccess($role_id,$data)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '角色分配权限-'.$role_info['role_name'];
                        $referr_url = Url::build('admin_role/roleAuthAccess',['role_id'=>$role_id]);
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
                }else{
                    return json(['status'=>500,'msg'=>'角色不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }else{
            //角色权限预览
            $role_id = Request::instance()->param('role_id/d',0);
            $role_info = $authRoleModel->getAuthRoleOneByWhere(['role_id'=>$role_id]);
            if(!empty($role_info)){
                //所有权限节点
                $rule = $ruleModel->getRuleMultipleByWhere([],'list_order ASC,rule_id ASC');
                $rule = getTree($rule,0,'pid','rule_id');
                $this->assign('rule',$rule);
                //当前角色的权限
                $authAccess = $authAccessModel->getAuthAccessMultipleByWhere(['role_id'=>$role_id]);
                $role_rule_ids = [];
                if(!empty($authAccess)){
                    foreach($authAccess as $v){
                        $role_rule_ids[] = $v['rule_id'];
                    }
                }
                $this->assign('role_rule_ids',$role_rule_ids);
                $this->assign('role_info',$role_info);
                return $this->fetch('role_auth_access');
            }else{
                return $this->fetch('common/404');
            }
        }
    }
}