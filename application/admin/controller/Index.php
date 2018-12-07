<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/6
 * Time: 16:47
 */
namespace app\admin\controller;

use think\Request;
use think\Session;

class Index extends Base
{
    /*
     * 控制面板首页
     */
    public function index(){
        //左侧菜单
        $menu = $this->getMenu();
        //p($menu);
        $this->assign('menu',$menu);
        return $this->fetch('index');
    }

    /*
     * 左侧菜单
     */
    public function getMenu(){
        //当前登录角色的权限节点
        $AuthAccessModel = new \app\admin\model\AdminAuthAccess();
        $authAccess = $AuthAccessModel->getAuthAccessMultipleByWhere(['role_id'=>Session::get('admin.role_id')]);
        $rule_ids = [];
        $result = [];
        if(!empty($authAccess)){
            $ruleModel = new \app\admin\model\AdminRule();
            foreach($authAccess as $v){
                $rule_ids[] = $v['rule_id'];
            }
            //根据权限节点，得到权限节点详情
            $rule = $ruleModel->getRuleMultipleByWhere(['rule_id'=>['IN',$rule_ids],'status'=>1],'list_order ASC');
            $result = getTree($rule,0,'pid','rule_id');
        }
        return $result;
    }

    /*
     * 欢迎页面
     */
    public function welcome(){
        return $this->fetch('welcome');
    }
}