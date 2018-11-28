<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/6
 * Time: 16:48
 */

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Url;

class Base extends Controller
{
    protected $beforeActionList = [
        '_initialize' =>  ['except'=>'check']
    ];

    /*
     * 验证是否登陆，如果没有登陆跳转到登陆页面
     */
    public function _initialize()
    {
        //检测是否已经登陆
        if(!Session::has('admin')){
            exit($this->redirect(Url::build('common/login')));
        }
        //检测是否有权限
        if(!$this->check()){
            if(Request::instance()->isAjax()){
                echo json_encode(['status'=>500,'msg'=>'抱歉，您没有相关的权限，请联系超级管理~']);
                exit;
            }else{
                exit($this->fetch('common/no_auth'));
            }
        }
    }
    /*
     * 检测当前登陆管理员是否具有权限
     */
    public function check(){
        $request = Request::instance();
        $module = $request->module();
        $controller = $request->controller();
        $action = $request->action();
        /*echo $module.'--'.$controller.'--'.$action;
        exit;*/
        $ruleModel = new \app\admin\model\AdminRule();
        $rule_info = $ruleModel->getRuleOneByWhere(['module'=>strtolower($module),'controller'=>strtolower($controller),'action'=>strtolower($action)]);
        if(empty($rule_info)){
            return true;
        }else{
            $role_id = Session::get('admin.role_id');
            $authAccessModel = new \app\admin\model\AdminAuthAccess();
            $auth_access_info = $authAccessModel->getAuthAccessOneByWhere(['role_id'=>$role_id,'rule_id'=>$rule_info['rule_id']]);
            if(!empty($auth_access_info)){
                return true;
            }else{
                return false;
            }
        }
    }
}