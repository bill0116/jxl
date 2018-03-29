<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/11/13
 * Time: 13:49
 * Func:青岛免费/非免费合同 顺德免费/非免费可公用 (14,27,28,29)
 */
namespace app\index\controller;

use think\Db;

class ContractQm extends Common
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
            if ($map['tasktype'] == 1) {
                $xml = xml_approve_link($map['ApproveDate'], session('auth_id'), session('user.UserName'), $map['IsApproved'], isset($map['IsApprovedText']) ? $map['IsApprovedText'] : '', $map['IsApprovedMemo']);
                $myActive = $this->model->getFlowMyactive($map['activeid'], session('auth_id'));
                $resultData = Db::table('Z_TB_FlowApp_ContractApplication')->where('ID=' . $map['ID'])->find();
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
            $list = $this->model->getUserApprove($map['activeid']);
            $this->assign('approveList', $list);
            $attachList = $this->getAttachFile($resultData['Attaches']);
            $this->assign('attachList', $attachList);

            $creditGuaranteeList = $this->getCrediGuarantee($resultData['kunnr'], $resultData['OrganizationFlag'], $resultData['IfLC']);
            $this->assign('creditGuaranteeList', $creditGuaranteeList);
            $overLimitList = $this->getOverAmount($resultData['kunnr'], $resultData['OrganizationFlag'], $resultData['IfLC']);
            $this->assign('overLimitList', $overLimitList);
            $invoiceReceivabledList = $this->getInvoiceReceivabled($resultData['kunnr'], $resultData['OrganizationFlag'], $resultData['IfLC']);
            $this->assign('invoiceReceivabledList', $invoiceReceivabledList);
            $shipmentNotInvoicedList = $this->getShipmentNotInvoiced($resultData['kunnr'], $resultData['OrganizationFlag'], $resultData['IfLC']);
            $this->assign('shipmentNotInvoicedList', $shipmentNotInvoicedList);
            $approveNotShipmentList = $this->getApproveNotShipment($resultData['kunnr'], $resultData['OrganizationFlag'], $resultData['IfLC']);
            $this->assign('approveNotShipmentList', $approveNotShipmentList);
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
        $resultData = Db::table('Z_TB_FlowApp_ContractApplication')->where('id', $map['id'])->find();
        $this->assign('data', $resultData);
        $list = $this->model->getUserApprove($map['activeid']);
        $this->assign('approveList', $list);
        $attachList = $this->getAttachFile($resultData['Attaches']);
        $this->assign('attachList', $attachList);
        $childList = $this->getChildData($map['activeid']);
        $this->assign('childList', $childList);
        $creditGuaranteeList = $this->getCrediGuarantee($resultData['kunnr'], $resultData['OrganizationFlag'], $resultData['IfLC']);
        $this->assign('creditGuaranteeList', $creditGuaranteeList);
        $overLimitList = $this->getOverAmount($resultData['kunnr'], $resultData['OrganizationFlag'], $resultData['IfLC']);
        $this->assign('overLimitList', $overLimitList);
        $invoiceReceivabledList = $this->getInvoiceReceivabled($resultData['kunnr'], $resultData['OrganizationFlag'], $resultData['IfLC']);
        $this->assign('invoiceReceivabledList', $invoiceReceivabledList);
        $shipmentNotInvoicedList = $this->getShipmentNotInvoiced($resultData['kunnr'], $resultData['OrganizationFlag'], $resultData['IfLC']);
        $this->assign('shipmentNotInvoicedList', $shipmentNotInvoicedList);
        $approveNotShipmentList = $this->getApproveNotShipment($resultData['kunnr'], $resultData['OrganizationFlag'], $resultData['IfLC']);
        $this->assign('approveNotShipmentList', $approveNotShipmentList);
        return $this->fetch();
    }

    /*
     * @ 子表数据
     * */
    public function  getChildData($activeid)
    {
        $result = Db::table('Z_TB_FlowApp_ContractCommodityInfo')
            ->where('activeid', $activeid)
            ->select();
        return $result;
    }
}