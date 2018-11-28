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

class AdminAdmin extends Base
{
    /*
     * 管理员列表
     */
    public function adminIndex(){
        return $this->fetch('admin_index');
    }
    /*
     * ajax获取管理员列表
     */
    public function ajaxGetAdminList(){
        if(Request::instance()->isAjax()){
            $page = Request::instance()->get('page/d',1);
            $limit = Request::instance()->get('limit/d',10);
            $offset = ($page - 1) * $limit;
            $AdminModel = new \app\admin\model\Admin();
            $where = [];
            if(Request::instance()->has('admin_account')){
                $admin_account = Request::instance()->get('admin_account/s');
                if(!empty($admin_account)){
                    $where['admin_account'] = ["LIKE","%{$admin_account}%"];
                }
            }
            if(Request::instance()->has('admin_truename')){
                $admin_truename = Request::instance()->get('admin_truename/s');
                if(!empty($admin_truename)){
                    $where['admin_truename'] = ["LIKE","%{$admin_truename}%"];
                }
            }
            if(Request::instance()->has('admin_mobile_phone')){
                $admin_mobile_phone = Request::instance()->get('admin_mobile_phone/s');
                if(!empty($admin_mobile_phone)){
                    $where['admin_mobile_phone'] = ["LIKE","%{$admin_mobile_phone}%"];
                }
            }
            if(Request::instance()->has('admin_mail')){
                $admin_mail = Request::instance()->get('admin_mail/s');
                if(!empty($admin_mail)){
                    $where['admin_mail'] = ["LIKE","%{$admin_mail}%"];
                }
            }
            $result = $AdminModel->getAdminList($offset,$limit,$where);
            if(!empty($result['iTotalRecords'])){
                $authRoleModel = new \app\admin\model\AdminAuthRole();
                foreach($result['iTotalRecords'] as $k=>$v){
                    //所属角色
                    $role_info = $authRoleModel->getAuthRoleOneByWhere(['role_id'=>$v['role_id']]);
                    $result['iTotalRecords'][$k]['role_name'] = !empty($role_info) ? $role_info['role_name'] : '';
                    $result['iTotalRecords'][$k]['login_time'] = date("Y-m-d H:i:s",$v['login_time']);
                    $result['iTotalRecords'][$k]['login_ip'] = long2ip($v['login_ip']);
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
     * ajax启用、停用管理员账号
     */
    public function ajaxUpdateAdminStatus(){
        if(Request::instance()->isAjax()){
            if(Request::instance()->has('admin_id') && Request::instance()->has('status')){
                $admin_id = Request::instance()->post('admin_id/d');
                $adminModel = new \app\admin\model\Admin();
                $admin_info = $adminModel->getAdminOneByWhere(['admin_id'=>$admin_id]);
                if(!empty($admin_info)){
                    $where['admin_id'] = $admin_id;
                    $status = Request::instance()->post('status/d');
                    $data = [
                        'status' => $status,
                        'update_time' => time()
                    ];
                    if($adminModel->saveAdmin($data,$where)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '';
                        if($status == 0){
                            $log_note = '停用管理员-'.$admin_info['admin_account'];
                        }else if($status == 1){
                            $log_note = '启用管理员-'.$admin_info['admin_account'];
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
                    return json(['status'=>500,'msg'=>'管理员信息不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }
    }
    /*
     * 编辑管理员
     */
    public function adminEdit(){
        $admin_id = Request::instance()->param('admin_id/d',0);
        $adminModel = new \app\admin\model\Admin();
        $admin_info = $adminModel->getAdminOneByWhere(['admin_id'=>$admin_id]);
        if(!empty($admin_info)){
            $authRoleModel = new \app\admin\model\AdminAuthRole();
            $role = $authRoleModel->getAuthRoleMultipleByWhere(['status'=>1]);
            $this->assign('role',$role);
            $this->assign('admin_info',$admin_info);
            return $this->fetch('admin_edit');
        }else{
            return $this->fetch('common/404');
        }
    }
    /*
     * 添加管理员
     */
    public function adminAdd(){
        $authRoleModel = new \app\admin\model\AdminAuthRole();
        $role = $authRoleModel->getAuthRoleMultipleByWhere(['status'=>1]);
        $this->assign('role',$role);
        return $this->fetch('admin_add');
    }
    /*
     * ajax保存管理员信息
     */
    public function ajaxSaveAdmin(){
        if(Request::instance()->isAjax()){
            $admin_id = Request::instance()->post('admin_id/d',0);
            $adminModel = new \app\admin\model\Admin();
            $admin_info = $adminModel->getAdminOneByWhere(['admin_id'=>$admin_id]);
            $where = [];
            $admin_account = Request::instance()->post('admin_account/s');
            $admin_truename = Request::instance()->post('admin_truename/s');
            $admin_mobile_phone = Request::instance()->post('admin_mobile_phone/s');
            $admin_mail = Request::instance()->post('admin_mail/s');
            //查看账号是否已经被使用
            $admin_account_info = $adminModel->getAdminOneByWhere(['admin_account'=>$admin_account]);
            if(!empty($admin_account_info) && $admin_account_info['admin_id'] != $admin_info['admin_id']){
                return json(['status'=>500,'msg'=>'登录账号已被使用']);
            }
            //查看手机号码是否已被使用
            $admin_mobile_phone_info = $adminModel->getAdminOneByWhere(['admin_mobile_phone'=>$admin_mobile_phone]);
            if(!empty($admin_mobile_phone_info) && $admin_mobile_phone_info['admin_id'] != $admin_info['admin_id']){
                return json(['status'=>500,'msg'=>'手机号码已被使用']);
            }
            //查看邮箱是否已被使用
            $admin_mail_info = $adminModel->getAdminOneByWhere(['admin_mail'=>$admin_mail]);
            if(!empty($admin_mail_info) && $admin_mail_info['admin_id'] != $admin_info['admin_id']){
                return json(['status'=>500,'msg'=>'邮箱已被使用']);
            }
            $role_id = Request::instance()->post('role_id/d');
            $status = Request::instance()->post('status/d',0);
            $data = [
                'admin_account' => $admin_account,
                'admin_truename' => $admin_truename,
                'admin_mobile_phone' => $admin_mobile_phone,
                'admin_mail' => $admin_mail,
                'role_id' => $role_id,
                'status' => $status,
                'update_time' => time()
            ];
            $password = Request::instance()->post('password/s','');
            if(!empty($password)){
                $salt = random(6,false);
                $data['password'] = md5(md5($password).$salt);
                $data['salt'] = $salt;
            }
            if(!empty($admin_info)){
                $where['admin_id'] = $admin_id;
            }else{
                $data['create_time'] = time();
            }
            if($result_admin_id = $adminModel->saveAdmin($data,$where)){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                if(!empty($where)){
                    $log_note = '编辑管理员-'.$admin_info['admin_account'];
                    $referr_url = Url::build('admin_admin/adminEdit',['admin_id'=>$admin_id]);
                }else{
                    $log_note = '添加管理员-'.$admin_account;
                    $referr_url = Url::build('admin_admin/adminEdit',['admin_id'=>$result_admin_id]);
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