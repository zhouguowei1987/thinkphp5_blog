<?php
/**
 * Created by PhpStorm.
 * User: 周国伟
 * Date: 2018/11/23
 * Time: 16:55
 */

namespace app\admin\controller;

use think\Request;
use think\Session;
use think\Url;

class BlogNav extends Base
{
    /*
     * 博客导航列表
     */
    public function navIndex(){
        $navModel = new \app\admin\model\BlogNav();
        $nav = $navModel->getNavMultipleByWhere([]);
        $nav = get_tree($nav,0,'pid','nav_id');
        $this->assign('nav',$nav);
        return $this->fetch('nav_index');
    }
    /*
     * ajax博客导航菜单显示
     */
    public function ajaxUpdateNavStatus(){
        if(Request::instance()->isAjax()){
            if(Request::instance()->has('nav_id') && Request::instance()->has('status')){
                $nav_id = Request::instance()->post('nav_id/d');
                $navModel = new \app\admin\model\BlogNav();
                $nav_info = $navModel->getNavOneByWhere(['nav_id'=>$nav_id]);
                if(!empty($nav_info)){
                    $where['nav_id'] = $nav_id;
                    $status = Request::instance()->post('status/d');
                    $data = [
                        'status' => $status,
                        'update_time' => time()
                    ];
                    if($navModel->saveNav($data,$where)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '';
                        if($status == 0){
                            $log_note = '博客导航隐藏-'.$nav_info['nav_name'];
                        }else if($status == 1){
                            $log_note = '博客导航显示-'.$nav_info['nav_name'];
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
                    return json(['status'=>500,'msg'=>'博客导航不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }
    }
    /*
     * 删除博客导航
     */
    public function ajaxDeleteNav(){
        $nav_id = Request::instance()->param('nav_id/d',0);
        $navModel = new \app\admin\model\BlogNav();
        $nav_info = $navModel->getNavOneByWhere(['nav_id'=>$nav_id]);
        if(!empty($nav_info)){
            //查看是否有下级博客导航
            $child_nav = $navModel->getNavMultipleByWhere(['pid'=>$nav_info['nav_id']]);
            if(!empty($child_nav)){
                //上级节点信息
                return json(['status'=>500,'msg'=>'有子级节点不可删除']);
            }
            if($navModel->deleteNav(['nav_id'=>$nav_id])){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                $log_note = '删除博客导航-'.$nav_info['nav_name'];
                $actionLogData = [
                    'admin_id' => Session::get('admin.admin_id'),
                    'log_note' => $log_note,
                    'log_url' => Request::instance()->url(),
                    'log_data' => serialize(['nav_id'=>$nav_id]),
                    'log_action_ip' => ip2long(Request::instance()->ip()),
                    'log_create_time' => time()
                ];
                $actionLogModel->saveActionLog($actionLogData);
                return json(['status'=>200,'msg'=>'操作成功']);
            }else{
                return json(['status'=>500,'msg'=>'操作失败']);
            }
        }else{
            return json(['status'=>500,'msg'=>'博客导航不存在']);
        }
    }
    /*
     * 编辑博客导航
     */
    public function navEdit(){
        $nav_id = Request::instance()->param('nav_id/d',0);
        $navModel = new \app\admin\model\BlogNav();
        $nav_info = $navModel->getNavOneByWhere(['nav_id'=>$nav_id]);
        if(!empty($nav_info)){
            if($nav_info['pid'] != 0){
                //上级节点信息
                $parent_nav_info = $navModel->getNavOneByWhere(['nav_id'=>$nav_info['pid']]);
                $this->assign('parent_nav_info',$parent_nav_info);
            }
            $this->assign('nav_info',$nav_info);
            return $this->fetch('nav_edit');
        }else{
            return $this->fetch('common/404');
        }
    }
    /*
     * 添加博客导航
     */
    public function navAdd(){
        $pid = Request::instance()->param('pid/d',0);
        $navModel = new \app\admin\model\BlogNav();
        if($pid){
            $parent_nav_info = $navModel->getNavOneByWhere(['nav_id'=>$pid]);
            $this->assign('parent_nav_info',$parent_nav_info);
        }
        return $this->fetch('nav_add');
    }
    /*
     * ajax保存博客导航信息
     */
    public function ajaxSaveNav(){
        if(Request::instance()->isAjax()){
            $nav_id = Request::instance()->post('nav_id/d',0);
            $navModel = new \app\admin\model\BlogNav();
            $nav_info = $navModel->getNavOneByWhere(['nav_id'=>$nav_id]);
            $where = [];
            $level = Request::instance()->post('level/d',1);
            $nav_name = Request::instance()->post('nav_name/s','');
            $pid = Request::instance()->post('pid/d',0);
            $nav_url = Request::instance()->post('nav_url/s','');
            $status = Request::instance()->post('status/d',0);
            $list_order = Request::instance()->post('list_order/d',0);
            $data = [
                'level' => $level,
                'nav_name' => $nav_name,
                'pid' => $pid,
                'nav_url' => $nav_url,
                'status' => $status,
                'list_order' => $list_order,
                'update_time' => time()
            ];
            if(!empty($nav_info)){
                $where['nav_id'] = $nav_id;
            }else{
                $data['create_time'] = time();
            }
            if($result_nav_id = $navModel->saveNav($data,$where)){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                if(!empty($where)){
                    $log_note = '编辑博客导航-'.$nav_info['nav_name'];
                    $referr_url = Url::build('blog_nav/navEdit',['nav_id'=>$nav_id]);
                }else{
                    $log_note = '添加博客导航-'.$nav_name;
                    $referr_url = Url::build('blog_nav/navEdit',['nav_id'=>$result_nav_id]);
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