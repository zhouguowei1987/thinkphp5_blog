<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/6
 * Time: 16:55
 */

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Url;

class Common extends Controller
{
    /*
     * 登陆页面
     * @param $admin_account
     * @param $password
     */
    public function login()
    {
        //登录操作请求
        if(Request::instance()->isAjax()){
            $admin_account = Request::instance()->post('admin_account/s');
            $password = Request::instance()->post('password/s');
            $adminModel = new \app\admin\model\Admin();
            $admin_info = $adminModel->getAdminOneByWhere(['admin_account'=>$admin_account]);
            if(!empty($admin_info)){
                if($admin_info['password'] === md5(md5($password).$admin_info['salt'])){
                    if($admin_info['status'] == 0){
                        return json(['status'=>500,'msg'=>'管理员账号已被禁用']);
                    }else{
                        //更新管理员登陆信息
                        $adminData = [
                            'login_ip'=> ip2long(Request::instance()->ip()),
                            'login_time' => time(),
                            'login_count' => $admin_info['login_count'] + 1
                        ];
                        if(!$adminModel->saveAdmin($adminData,['admin_id'=>$admin_info['admin_id']])){
                            return json(['status'=>500,'msg'=>'更新管理员登录信息失败']);
                        }
                        $admin_session = [
                            'admin_id'=> $admin_info['admin_id'],
                            'admin_account'=> $admin_info['admin_account'],
                            'admin_truename'=> $admin_info['admin_truename'],
                            'role_id'=> $admin_info['role_id']
                        ];
                        if(Session::set('admin',$admin_session) !== false){
                            //添加后台行为操作日志
                            $actionLogModel = new \app\admin\model\AdminActionLog();
                            $actionLogData = [
                                'admin_id' => Session::get('admin.admin_id'),
                                'log_note' => '登录管理后台',
                                'log_url' => Request::instance()->url(),
                                'log_action_ip' => ip2long(Request::instance()->ip()),
                                'log_create_time' => time()
                            ];
                            $actionLogModel->saveActionLog($actionLogData);
                            return json(['status'=>200,'msg'=>'登录成功','referr_url'=>Url::build('index/index')]);
                        }
                    }
                }else{
                    return json(['status'=>500,'msg'=>'账号或密码错误']);
                }
            }else{
                return json(['status'=>500,'msg'=>'管理员账号不存在']);
            }
        }else{
            //判断是否已经登录过
            if(Session::has('admin')){
                return $this->redirect(Url::build('index/index'));
            }else{
                return $this->fetch('login');
            }
        }
    }
    /*
     * 查看个人资料
     */
    public function personalProfile(){
        $adminModel = new \app\admin\model\Admin();
        $admin_info = $adminModel->getAdminOneByWhere(['admin_id'=>Session::get('admin.admin_id')]);
        if(Request::instance()->isAjax()){
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
            $adminData = [
                'admin_account' => $admin_account,
                'admin_truename' => $admin_truename,
                'admin_mobile_phone'=> $admin_mobile_phone,
                'admin_mail' => $admin_mail,
                'update_time' => time()
            ];
            if(!$adminModel->saveAdmin($adminData,['admin_id'=>$admin_info['admin_id']])){
                return json(['status'=>500,'msg'=>'个人信息更改失败']);
            }else{
                //更改session中的真实姓名
                Session::set('admin.admin_truename',$admin_truename);
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                $actionLogData = [
                    'admin_id' => Session::get('admin.admin_id'),
                    'log_note' => '更改个人信息',
                    'log_url' => Request::instance()->url(),
                    'log_data' => serialize($adminData),
                    'log_action_ip' => ip2long(Request::instance()->ip()),
                    'log_create_time' => time()
                ];
                $actionLogModel->saveActionLog($actionLogData);
                return json(['status'=>200,'msg'=>'更新个人信息成功']);
            }
        }else{
            if(empty($admin_info)){
                return $this->error('管理员信息不存在');
            }else{
                $this->assign('admin_info',$admin_info);
                return $this->fetch('personal_profile');
            }
        }
    }
    /*
     * 更改密码
     */
    public function changePassword(){
        $adminModel = new \app\admin\model\Admin();
        $admin_info = $adminModel->getAdminOneByWhere(['admin_id'=>Session::get('admin.admin_id')]);
        if(Request::instance()->isAjax()){
            $old_password = Request::instance()->post('old_password/s');
            $new_password = Request::instance()->post('new_password/s');
            //查看旧密码是否正确
            if($admin_info['password'] !== md5(md5($old_password).$admin_info['salt'])){
                return json(['status'=>500,'msg'=>'原密码错误']);
            }
            //更新管理员登陆信息
            $salt = random(6,false);
            $adminData = [
                'password' => md5(md5($new_password).$salt),
                'salt'=> $salt,
                'update_time' => time()
            ];
            if(!$adminModel->saveAdmin($adminData,['admin_id'=>$admin_info['admin_id']])){
                return json(['status'=>500,'msg'=>'更改密码失败']);
            }else{
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                $actionLogData = [
                    'admin_id' => Session::get('admin.admin_id'),
                    'log_note' => '更改密码',
                    'log_url' => Request::instance()->url(),
                    'log_data' => serialize($adminData),
                    'log_action_ip' => ip2long(Request::instance()->ip()),
                    'log_create_time' => time()
                ];
                $actionLogModel->saveActionLog($actionLogData);
                //清除登录session
                Session::clear();
                return json(['status'=>200,'msg'=>'更改成功，请重新登录','referr_url'=>Url::build('common/login')]);
            }
        }else{
            if(empty($admin_info)){
                return $this->error('管理员信息不存在');
            }else{
                $this->assign('admin_info',$admin_info);
                return $this->fetch('change_password');
            }
        }
    }

    /*
     * 退出登录
     */
    public function logout(){
        $actionLogData = [
            'admin_id' => Session::get('admin.admin_id'),
            'log_note' => '退出管理后台',
            'log_url' => Request::instance()->url(),
            'log_action_ip' => ip2long(Request::instance()->ip()),
            'log_create_time' => time()
        ];
        if(Session::clear() !== false){
            //添加后台行为操作日志
            $actionLogModel = new \app\admin\model\AdminActionLog();
            $actionLogModel->saveActionLog($actionLogData);
            $this->redirect(Url::build('common/login'));
        }
    }
    /*
     * 后台公用ajax上传图片
     */
    public function ajaxCommonUploadImages(){
        $upload_file = Request::instance()->post('upload_file/s');
        $save_path = Request::instance()->post('save_path/s');
        $create_rule = 'date';
        if(Request::instance()->has('rule')){
            $rule = Request::instance()->post('rule/s');
            if(!empty($rule) && in_array($rule,['date','md5','sha1','uniqid'])){
                $create_rule = $rule;
            }
        }
        $file = Request::instance()->file($upload_file);
        $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->rule($create_rule)->move(ROOT_PATH .DS. 'uploads'.DS . $save_path);
        if($info){
            $save_path = str_replace("\\",'/',DS .'uploads'.DS. $save_path . DS . $info->getSaveName());
            return json(["status" => 200,'msg'=>'上传成功','save_path'=> $save_path]);
        }else{
            return json(["status" => 500,'msg'=>$file->getError()]);
        }
    }
}