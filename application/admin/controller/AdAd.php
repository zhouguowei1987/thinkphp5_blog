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

class AdAd extends Base
{
    /*
     * 广告列表
     */
    public function adIndex(){
        $positionModel = new \app\admin\model\AdPosition();
        $position = $positionModel->getPositionMultipleByWhere(['status'=>1]);
        $this->assign('position',$position);
        return $this->fetch('ad_index');
    }
    /*
     * ajax获取广告列表
     */
    public function ajaxGetAdList(){
        if(Request::instance()->isAjax()){
            $page = Request::instance()->get('page/d',1);
            $limit = Request::instance()->get('limit/d',10);
            $offset = ($page - 1) * $limit;
            $adModel = new \app\admin\model\AdAd();
            $where = [];
            if(Request::instance()->has('ad_name')) {
                $ad_name = Request::instance()->get('ad_name/s');
                if ($ad_name) {
                    $where['ad_name'] = ['LIKE',"%{$ad_name}%"];
                }
            }
            if(Request::instance()->has('ad_code')) {
                $ad_code = Request::instance()->get('ad_code/s');
                if ($ad_code) {
                    $where['ad_code'] = $ad_code;
                }
            }
            if(Request::instance()->has('status')) {
                $status = Request::instance()->get('status/d');
                if($status != 2){
                    $where['status'] = $status;
                }
            }
            $result = $adModel->getAdList($offset,$limit,$where);
            if(!empty($result['iTotalRecords'])){
                $positionModel = new \app\admin\model\AdPosition();
                foreach($result['iTotalRecords'] as $k=>$v){
                    switch ($v['ad_type']){
                        case 1:
                            $result['iTotalRecords'][$k]['ad_type_name'] = '图片';
                            break;
                        case 2:
                            $result['iTotalRecords'][$k]['ad_type_name'] = '文字';
                            break;
                    }
                    $position_info = $positionModel->getPositionOneByWhere(['position_id'=>$v['position_id']]);
                    $result['iTotalRecords'][$k]['ad_position_name'] = !empty($position_info) ? $position_info['position_name'] : '';
                    $result['iTotalRecords'][$k]['ad_position_code'] = !empty($position_info) ? $position_info['position_code'] : '';
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
     * ajax隐藏、显示广告
     */
    public function ajaxUpdateAdStatus(){
        if(Request::instance()->isAjax()){
            if(Request::instance()->has('ad_id') && Request::instance()->has('status')){
                $ad_id = Request::instance()->post('ad_id/d');
                $adModel = new \app\admin\model\AdAd();
                $ad_info = $adModel->getAdOneByWhere(['ad_id'=>$ad_id]);
                if(!empty($ad_info)){
                    $where['ad_id'] = $ad_id;
                    $status = Request::instance()->post('status/d');
                    $data = [
                        'status' => $status,
                        'update_time' => time()
                    ];
                    if($adModel->saveAd($data,$where)){
                        //添加后台行为操作日志
                        $actionLogModel = new \app\admin\model\AdminActionLog();
                        $log_note = '';
                        if($status == 0){
                            $log_note = '隐藏广告-'.$ad_info['ad_name'];
                        }else if($status == 1){
                            $log_note = '显示广告-'.$ad_info['ad_name'];
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
                    return json(['status'=>500,'msg'=>'广告不存在']);
                }
            }else{
                return json(['status'=>500,'msg'=>'缺少参数']);
            }
        }
    }
    /*
     * 编辑广告
     */
    public function adEdit(){
        $ad_id = Request::instance()->param('ad_id/d',0);
        $adModel = new \app\admin\model\AdAd();
        $ad_info = $adModel->getAdOneByWhere(['ad_id'=>$ad_id]);
        if(!empty($ad_info)){
            $positionModel = new \app\admin\model\AdPosition();
            $position = $positionModel->getPositionMultipleByWhere(['status'=>1]);
            $this->assign('position',$position);

            $this->assign('ad_info',$ad_info);
            return $this->fetch('ad_edit');
        }else{
            return $this->fetch('common/404');
        }
    }
    /*
     * 添加广告
     */
    public function adAdd(){
        $positionModel = new \app\admin\model\AdPosition();
        $position = $positionModel->getPositionMultipleByWhere(['status'=>1]);
        $this->assign('position',$position);
        return $this->fetch('ad_add');
    }
    /*
     * ajax保存广告信息
     */
    public function ajaxSaveAd(){
        if(Request::instance()->isAjax()){
            $ad_id = Request::instance()->post('ad_id/d',0);
            $adModel = new \app\admin\model\AdAd();
            $ad_info = $adModel->getAdOneByWhere(['ad_id'=>$ad_id]);
            $where = [];
            $ad_name = str_replace([' '],'',Request::instance()->post('ad_name/s'));
            $ad_code = str_replace([' '],'',Request::instance()->post('ad_code/s'));
            $status = Request::instance()->post('status/d');
            $data = [
                'ad_name' => $ad_name,
                'ad_code' => $ad_code,
                'status' => $status,
                'update_time' => time()
            ];
            //查看广告code是否可用
            $code_ad_info = $adModel->getAdOneByWhere(['ad_code'=>$ad_code]);
            if(!empty($ad_info)){
                if(!empty($code_ad_info) && $code_ad_info['ad_id'] != $ad_id){
                    return json(['status'=>500,'msg'=>'广告code已存在，不可使用']);
                }
                $where['ad_id'] = $ad_id;
            }else{
                if(!empty($code_ad_info)){
                    return json(['status'=>500,'msg'=>'广告code已存在无需重复添加']);
                }
                $data['create_time'] = time();
            }
            if($result_ad_id = $adModel->saveAd($data,$where)){
                //添加后台行为操作日志
                $actionLogModel = new \app\admin\model\AdminActionLog();
                if(!empty($where)){
                    $log_note = '编辑广告-'.$ad_info['ad_name'];
                    $referr_url = Url::build('ad_ad/adEdit',['ad_id'=>$ad_id]);
                }else{
                    $log_note = '添加广告-'.$ad_name;
                    $referr_url = Url::build('ad_ad/adEdit',['ad_id'=>$result_ad_id]);
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