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

class AdPosition extends Base
{
    /*
     * 广告位列表
     */
    public function positionIndex(){
        return $this->fetch('position_index');
    }
    /*
     * ajax获取广告位列表
     */
    public function ajaxGetPositionList(){
        if(Request::instance()->isAjax()){
            $page = Request::instance()->get('page/d',1);
            $limit = Request::instance()->get('limit/d',10);
            $offset = ($page - 1) * $limit;
            $positionModel = new \app\admin\model\AdPosition();
            $where = [];
            if(Request::instance()->has('position_name')) {
                $position_name = Request::instance()->get('position_name/s');
                if ($position_name) {
                    $where['position_name'] = ['LIKE',"%{$position_name}%"];
                }
            }
            if(Request::instance()->has('position_code')) {
                $position_code = Request::instance()->get('position_code/s');
                if ($position_code) {
                    $where['position_code'] = $position_code;
                }
            }
            if(Request::instance()->has('status')) {
                $status = Request::instance()->get('status/d');
                if($status != 2){
                    $where['status'] = $status;
                }
            }
            $result = $positionModel->getPositionList($offset,$limit,$where);
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
     * ajax隐藏、显示广告位
     */
    public function ajaxUpdatePositionStatus(){
        if(Request::instance()->isAjax()){
            if(Request::instance()->has('position_id') && Request::instance()->has('status')){
                $position_id = Request::instance()->post('position_id/d');
                $positionModel = new \app\admin\model\AdPosition();
                $position_info = $positionModel->getPositionOneByWhere(['position_id'=>$position_id]);
                if(!empty($position_info)){
                    $where['position_id'] = $position_id;
                    $status = Request::instance()->post('status/d');
                    $data = [
                        'status' => $status,
                        'update_time' => time()
                    ];
                    if($positionModel->savePosition($data,$where)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '';
                        if($status == 0){
                            $log_note = '隐藏广告位-'.$position_info['position_name'];
                        }else if($status == 1){
                            $log_note = '显示广告位-'.$position_info['position_name'];
                        }
                        $actionLogData = [
                            'ad_id' => Session::get('admin.admin_id'),
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
                    return json(['status'=>500,'msg'=>'广告位不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }
    }
    /*
     * 编辑广告位
     */
    public function positionEdit(){
        $position_id = Request::instance()->param('position_id/d',0);
        $positionModel = new \app\admin\model\AdPosition();
        $position_info = $positionModel->getPositionOneByWhere(['position_id'=>$position_id]);
        if(!empty($position_info)){
            $this->assign('position_info',$position_info);
            return $this->fetch('position_edit');
        }else{
            return $this->fetch('common/404');
        }
    }
    /*
     * 添加广告位
     */
    public function positionAdd(){
        return $this->fetch('position_add');
    }
    /*
     * ajax保存广告位信息
     */
    public function ajaxSavePosition(){
        if(Request::instance()->isAjax()){
            $position_id = Request::instance()->post('position_id/d',0);
            $positionModel = new \app\admin\model\AdPosition();
            $position_info = $positionModel->getPositionOneByWhere(['position_id'=>$position_id]);
            $where = [];
            $position_name = str_replace([' '],'',Request::instance()->post('position_name/s'));
            $position_code = str_replace([' '],'',Request::instance()->post('position_code/s'));
            $status = Request::instance()->post('status/d');
            $data = [
                'position_name' => $position_name,
                'position_code' => $position_code,
                'status' => $status,
                'update_time' => time()
            ];
            //查看广告位code是否可用
            $code_position_info = $positionModel->getPositionOneByWhere(['position_code'=>$position_code]);
            if(!empty($position_info)){
                if(!empty($code_position_info) && $code_position_info['position_id'] != $position_id){
                    return json(['status'=>500,'msg'=>'广告位code已存在，不可使用']);
                }
                $where['position_id'] = $position_id;
            }else{
                if(!empty($code_position_info)){
                    return json(['status'=>500,'msg'=>'广告位code已存在无需重复添加']);
                }
                $data['create_time'] = time();
            }
            if($result_position_id = $positionModel->savePosition($data,$where)){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                if(!empty($where)){
                    $log_note = '编辑广告位-'.$position_info['position_name'];
                    $referr_url = Url::build('ad_position/positionEdit',['position_id'=>$position_id]);
                }else{
                    $log_note = '添加广告位-'.$position_name;
                    $referr_url = Url::build('ad_position/positionEdit',['position_id'=>$result_position_id]);
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