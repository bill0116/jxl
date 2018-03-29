<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/11/21
 * Func: 信保限额复评审请 flowid=2
 */
namespace app\index\controller;

use think\Db;

class CreditRepply extends Common
{
    function initialize()
    {
        parent::initialize();
        $this->model = model('flow');
    }
    /*
     * @ 流程审批
     * */
    public function update()
    {
        if (request()->isPost()) {
            $map = input('post.');
            $db = Db::table('Z_TB_FLowApp_CreditApplication');
            if ($map['tasktype'] == 0) {
                $param = request()->except(['id', 'activeid', 'tasktype', 'routeid', 'userid', 'IsApproved'], 'post');
                $result = $db->where('id', $map['id'])->update($param);
                if ($result !== false) {
                    $route = $this->model->getSubmitActive($map['activeid'], session('auth_id'), '', '', '', '');
                    if (isset($route[0]["msg"]) && $route[0]["msg"] == '流程已流向退回人')
                        $this->success($route[0]["msg"], url('index/index'), 1);
                    array_shift($route);
                    $this->assign('route', $route);
                    if (count($route) == 1) {
                        if (!empty($route[0]['routeid']) || $route[0]['routeid'] == 0) {  //  获得审批人信息
                            $userinfo = $this->model->getSubmitActive($map['activeid'], session('auth_id'), '', '', $route[0]['routeid'], '');
                            if (isset($userinfo[0]['result']) && $userinfo[0]['result'] == 's')
                                $this->success($userinfo[0]['msg'], url('index/index'), 1);
                            $this->assign('usermsg', $userinfo[0]['msg']);
                            array_shift($userinfo);  //  删除忽略第一条数据信息
                            if (count($userinfo) == 1) {
                                $nextflow = $this->model->getSubmitActive($map['activeid'], session('auth_id'), '', '', $route[0]['routeid'], $userinfo[0]['userid']);
                                if ($nextflow)
                                    $this->success('申请成功，请等待审批', url('index/index'), 1);
                            } else {
                                $this->assign('routeid', $route[0]['routeid']);
                                $this->assign('user', $userinfo);
                            }
                        }
                        if (isset($map['routeid']) && (!empty($map['routeid'] || $map['routeid'] == 0) && isset($map['userid']))) {  // 根据审批人id
                            $nextflow = $this->model->getSubmitActive($map['activeid'], session('auth_id'), '', '', $map['routeid'], $map['userid']);
                            if ($nextflow)
                                $this->success('申请成功，请等待审批', url('index/index'), 1);
                        }
                    }
                    else if (count($route) > 1) {
                        if (isset($map['routeid']) && (!empty($map['routeid'] || $map['routeid'] == 0)) && empty($map['userid'])) {  //  获得审批人信息
                            $userinfo = $this->model->getSubmitActive($map['activeid'], session('auth_id'), '', '', $map['routeid'], '');
                            if ($userinfo[0]['result'] == 's')
                                $this->success($userinfo[0]['msg'], url('index/index'), 1);
                            $this->assign('usermsg', $userinfo[0]['msg']);
                            array_shift($userinfo);
                            if (count($userinfo) == 1) {
                                $nextflow = $this->model->getSubmitActive($map['activeid'], session('auth_id'), '', '', $map['routeid'], $userinfo[0]['userid']);
                                if ($nextflow)
                                    $this->success('申请成功，请等待审批', url('index/index'), 1);
                            } else {
                                $this->assign('user', $userinfo);
                                $this->assign('routeid', $map['routeid']);
                            }
                        }
                        if (isset($map['routeid']) && (!empty($map['routeid'] || $map['routeid'] == 0)) && !empty($map['userid'])) {  // 根据审批人id
                            $nextflow = $this->model->getSubmitActive($map['activeid'], session('auth_id'), '', '', $map['routeid'], $map['userid']);
                            if ($nextflow)
                                $this->success('申请成功，请等待审批', url('index/index'), 1);
                        }
                    }
                } else {
                    $this->error($this->getError(),3);
                }
                $myActive = $this->model->getFlowMyactive($map['activeid'], session('auth_id'));
                $resultData = $db->where('id', $map['id'])->find();
                $this->assign('myActive', $myActive);
                $this->assign('data', $resultData);
            } else if ($map['tasktype'] == 1) {
                $xml = xml_approve_link($map['ApproveDate'], session('auth_id'), session('user.UserName'), $map['IsApproved'], isset($map['IsApprovedText']) ? $map['IsApprovedText'] : '', $map['IsApprovedMemo']);
                $myActive = $this->model->getFlowMyactive($map['activeid'], session('auth_id'));
                $resultData = $db->where('id', $map['id'])->find();
                $this->assign('myActive', $myActive);
                $this->assign('data', $resultData);
                if ($resultData) {
                    $this->assign('IsApproved', input('post.IsApproved'));
                    $this->assign('IsApprovedMemo', $map['IsApprovedMemo']);
                    if (!empty($map['activeid']) && !empty(session('auth_id')) && !empty($map['IsApproved']) && !empty($xml) && empty($map['routeid']) && empty($map['userid'])) {  //  获得下一步路线route
                        $route = $this->model->getSubmitActive($map['activeid'], session('auth_id'), $map['IsApproved'], $xml, '', '');
                        if (isset($route[0]['msg']) && $route[0]['msg'] == '流程已流向申请人')
                            $this->success('流程已流向申请人', url('index/index'), 1);
                        array_shift($route);  //  删除忽略第一条数据信息
                        if (count($route) == 1) {    //审批环节为1条
                            $userinfo = $this->model->getSubmitActive($map['activeid'], session('auth_id'), $map['IsApproved'], $xml, $route[0]['routeid'], '');
                            if (isset($userinfo[0]['result']) && $userinfo[0]['result'] == 's')
                                $this->success($userinfo[0]['msg'], url('index/index'), 1);
                            $this->assign('usermsg', $userinfo[0]['msg']);
                            array_shift($userinfo);  //  删除忽略第一条数据信息
                            $this->assign('user', $userinfo);
                            $this->assign('routeid', $route[0]['routeid']);
                        } else {    //审批环节为多条
                            $this->assign('route', $route);
                        }
                    } else if (!empty($map['activeid']) && !empty(session('auth_id')) && !empty($map['IsApproved']) && !empty($xml) && !empty($map['routeid']) && empty($map['userid'])) {
                        $userinfo = $this->model->getSubmitActive($map['activeid'], session('auth_id'), $map['IsApproved'], $xml, $map['routeid'], '');
                        if (isset($userinfo[0]['result']) && $userinfo[0]['result'] == 's')
                            $this->success($userinfo[0]['msg'], url('index/index'), 1);
                        $this->assign('usermsg', $userinfo[0]['msg']);
                        if (isset($userinfo[0]['msg']) && $userinfo[0]['msg'] == "流程已结束")
                            $this->success('审批成功，流程已结束', url('index/index'), 1);
                        array_shift($userinfo);  //  删除忽略第一条数据信息
                        if (empty($userinfo))
                            $this->success('该流程已结束', url('index/index'), 1);
                        if (count($userinfo) == 1) {//审批人为1个
                            $nextflow = $this->model->getSubmitActive($map['activeid'], session('auth_id'), $map['IsApproved'], $xml, $map['routeid'], $userinfo[0]['userid']);
                            if ($nextflow)
                                $this->success('审批成功,流程已转向' . $userinfo[0]['username'], url('index/index'), 1);
                        } else {//审批人为多个
                            $this->assign('user', $userinfo);
                            $this->assign('routeid', $map['routeid']);
                        }
                    }
                    if (!empty($map['activeid']) && !empty(session('auth_id')) && !empty($map['IsApproved']) && !empty($xml) && !empty($map['routeid']) && !empty($map['userid'])) {
                        //  当前审批结束，转给下一个审批
                        $nextflow = $this->model->getSubmitActive($map['activeid'], session('auth_id'), $map['IsApproved'], $xml, $map['routeid'], $map['userid']);
                        $this->assign('nextflow', $nextflow);
                        if ($nextflow) {
                            $this->success('审批成功,流程已转向下一步审批人', url('index/index'), 1);
                        } else {
                            $this->error('审批失败');
                        }
                    }
                }
            }
            $list= $this->model->getUserApprove($map['activeid']);
            $this->assign('approveList',$list);
            $attachList=$this->getAttachFile($resultData['Attaches']);
            $this->assign('attachList',$attachList);
            $formData=$this->getModelForm($map['activeid']);
            $this->assign('formData', $formData);
            return $this->fetch('index');
        }
    }

    /*
     * @ 流程查看
     * */
    public function index()
    {
        $map = input('param.');
        $myActive = $this->model->getFlowMyactive($map['activeid'], session('auth_id'));
        $this->assign('myActive', $myActive);
        $resultData = Db::table('Z_TB_FLowApp_CreditApplication')->where('id', $map['id'])->find();
        $this->assign('data', $resultData);
        $list = $this->model->getUserApprove($map['activeid']);
        $this->assign('approveList', $list);
        $attachList = $this->getAttachFile($resultData['Attaches']);
        $this->assign('attachList', $attachList);
        $formData=$this->getModelForm($map['activeid']);
        $this->assign('formData', $formData);
        return $this->fetch();
    }
    /*
     * @ 模型评估数据
     * */
    public function getModelForm($activeid){
        $result=DB::table('Z_TB_Buyer_Credit_EvaluateDetail')
            ->field('*')
            ->where('ActiveID',$activeid)
            ->select();
        return $result;
    }

}