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

class AdminRule extends Base
{
    /*
     * 权限节点列表
     */
    public function ruleIndex(){
        $ruleModel = new \app\admin\model\AdminRule();
        $rule = $ruleModel->getRuleMultipleByWhere([],'list_order ASC');
        $rule = getTree($rule,0,'pid','rule_id');
        $this->assign('rule',$rule);
        return $this->fetch('rule_index');
    }
    /*
     * ajax权限节点菜单显示
     */
    public function ajaxUpdateRuleStatus(){
        if(Request::instance()->isAjax()){
            if(Request::instance()->has('rule_id') && Request::instance()->has('status')){
                $rule_id = Request::instance()->post('rule_id/d');
                $ruleModel = new \app\admin\model\AdminRule();
                $rule_info = $ruleModel->getRuleOneByWhere(['rule_id'=>$rule_id]);
                if(!empty($rule_info)){
                    $where['rule_id'] = $rule_id;
                    $status = Request::instance()->post('status/d');
                    $data = [
                        'status' => $status,
                        'update_time' => time()
                    ];
                    if($ruleModel->saveRule($data,$where)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '';
                        if($status == 0){
                            $log_note = '权限节点左侧不显示-'.$rule_info['rule_name'];
                        }else if($status == 1){
                            $log_note = '权限节点左侧显示-'.$rule_info['rule_name'];
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
                    return json(['status'=>500,'msg'=>'权限节点不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }
    }
    /*
     * 删除权限节点
     */
    public function ajaxDeleteRule(){
        $rule_id = Request::instance()->param('rule_id/d',0);
        $ruleModel = new \app\admin\model\AdminRule();
        $rule_info = $ruleModel->getRuleOneByWhere(['rule_id'=>$rule_id]);
        if(!empty($rule_info)){
            //查看是否有下级权限节点
            $child_rule = $ruleModel->getRuleMultipleByWhere(['pid'=>$rule_info['rule_id']]);
            if(!empty($child_rule)){
                //上级节点信息
                return json(['status'=>500,'msg'=>'有子级节点不可删除']);
            }
            if($ruleModel->deleteRule(['rule_id'=>$rule_id])){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                $log_note = '删除权限节点-'.$rule_info['rule_name'];
                $actionLogData = [
                    'admin_id' => Session::get('admin.admin_id'),
                    'log_note' => $log_note,
                    'log_url' => Request::instance()->url(),
                    'log_data' => serialize(['rule_id'=>$rule_id]),
                    'log_action_ip' => ip2long(Request::instance()->ip()),
                    'log_create_time' => time()
                ];
                $actionLogModel->saveActionLog($actionLogData);
                return json(['status'=>200,'msg'=>'操作成功']);
            }else{
                return json(['status'=>500,'msg'=>'操作失败']);
            }
        }else{
            return json(['status'=>500,'msg'=>'权限节点不存在']);
        }
    }
    /*
     * 编辑权限节点
     */
    public function ruleEdit(){
        $rule_id = Request::instance()->param('rule_id/d',0);
        $ruleModel = new \app\admin\model\AdminRule();
        $rule_info = $ruleModel->getRuleOneByWhere(['rule_id'=>$rule_id]);
        if(!empty($rule_info)){
            if($rule_info['pid'] != 0){
                //上级节点信息
                $parent_rule_info = $ruleModel->getRuleOneByWhere(['rule_id'=>$rule_info['pid']]);
                $this->assign('parent_rule_info',$parent_rule_info);
            }
            $this->assign('rule_info',$rule_info);
            return $this->fetch('rule_edit');
        }else{
            return $this->fetch('common/404');
        }
    }
    /*
     * 添加权限节点
     */
    public function ruleAdd(){
        $pid = Request::instance()->param('pid/d',0);
        $ruleModel = new \app\admin\model\AdminRule();
        if($pid){
            $parent_rule_info = $ruleModel->getRuleOneByWhere(['rule_id'=>$pid]);
            $this->assign('parent_rule_info',$parent_rule_info);
        }
        return $this->fetch('rule_add');
    }
    /*
     * ajax保存权限节点信息
     */
    public function ajaxSaveRule(){
        if(Request::instance()->isAjax()){
            $rule_id = Request::instance()->post('rule_id/d',0);
            $ruleModel = new \app\admin\model\AdminRule();
            $rule_info = $ruleModel->getRuleOneByWhere(['rule_id'=>$rule_id]);
            $where = [];
            $level = Request::instance()->post('level/d',1);
            $rule_name = Request::instance()->post('rule_name/s','');
            $pid = Request::instance()->post('pid/d',0);
            $module = Request::instance()->post('module/s','');
            $controller = Request::instance()->post('controller/s','');
            $action = Request::instance()->post('action/s','');
            $status = Request::instance()->post('status/d',0);
            $icon = Request::instance()->post('icon/s','');
            $list_order = Request::instance()->post('list_order/d',0);
            $data = [
                'level' => $level,
                'rule_name' => $rule_name,
                'pid' => $pid,
                'module' => strtolower($module),
                'controller' => strtolower($controller),
                'action' => strtolower($action),
                'status' => $status,
                'icon' => $icon,
                'list_order' => $list_order,
                'update_time' => time()
            ];
            if(!empty($rule_info)){
                $where['rule_id'] = $rule_id;
            }else{
                $data['create_time'] = time();
            }
            if($result_rule_id = $ruleModel->saveRule($data,$where)){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                if(!empty($where)){
                    $log_note = '编辑权限节点-'.$rule_info['rule_name'];
                    $referr_url = Url::build('admin_rule/ruleEdit',['rule_id'=>$rule_id]);
                }else{
                    $log_note = '添加权限节点-'.$rule_name;
                    $referr_url = Url::build('admin_rule/ruleEdit',['rule_id'=>$result_rule_id]);
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