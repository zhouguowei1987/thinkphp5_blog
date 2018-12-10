<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/*
 * 打印测试函数
 */
function p($param){
    if(is_array($param)){
        echo '<pre>';
        print_r($param);
    }else{
        echo $param;
    }
    die();
}
/*
 * 生成随机字符串
 * @param $length 生成字符串长度
 * @param $numberonly 是否要生成纯数字字符串
 * @param $chars 随机字符串种子库
 */
function random($length, $numberonly = false, $chars = '') {
    $hash = '';
    if ($numberonly) {
        $chars = !empty($chars) ? $chars : '0123456789';
    } else {
        $chars = !empty($chars) ? $chars : '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i ++) {
        $hash .= $chars[rand(0, $max)];
    }
    return $hash;
}
/*
 * 验证手机号码
 * @param $phone 手机号码
 */
function check_phone($phone) {
    if (preg_match("/^1[3456789]{1}\d{9}$/", $phone)) {
        return true;
    } else {
        return false;
    }
}
/*
 * 验证邮箱
 * @pram $mailbox 邮箱
 */
function check_mailbox($mailbox) {
    if (preg_match("/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$", $mailbox)) {
        return true;
    } else {
        return false;
    }
}
/*
 * 递归无限级分类树
 * @param $data
 * @param $pid
 * @param $parent_id
 * @param $id
 */
function get_tree($data, $pid = 0,$parent_id = 'parent_id',$id = 'id'){
    $tree = [];
    foreach($data as $k => $v) {
        if($v[$parent_id] == $pid) {
            $v['son'] = get_tree($data, $v[$id],$parent_id,$id);
            if(empty($v['son'])) unset($v['son']);
            $tree[] = $v;
            unset($data[$k]);
        }
    }
    return $tree;
}
/*
 * 获取blog广告
 * @param $position_code
 * @param $ad_type 1、图片 2、文字
 */
function get_blog_ad_list($position_code,$ad_type){
    $positionModel = new \app\blog\model\AdPosition();
    $position_info = $positionModel->getPositionOneByWhere(['position_code'=>$position_code,'status'=>1]);
    if(empty($position_info)){
        return false;
    }
    $adModel = new \app\blog\model\AdAd();
    $ad = $adModel->getAdMultipleByWhere(['position_id'=>$position_info['position_id'],'status'=>1,'ad_type'=>$ad_type]);
    if(empty($ad)){
        return false;
    }
    return $ad;
}
