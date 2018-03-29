<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/10/25 9:35
 * Function:流程控制器
 */
namespace app\index\controller;

use think\Db;

class Flow extends Common
{
    function initialize()
    {
        parent::initialize();
        $this->model=model('flow');
    }

    /*
     * @ 流程查询
     * */
    public function index()
    {
        if($this->request->isPost()){
            $flowData = $this->model->getFlowByID(input('post.flowID'));
            $formData = $this->model->getFormByKey($flowData[0]['ApplicationFormKey']);
            $where = "a.senduserid is not null and a.flowid=".$flowData[0]['FlowID']."";
            if (input('post.flowStatus') == 0)
                $where .= " and a.ActiveTaskID!=0 ";
             else if (input('post.flowStatus') == 1)
                $where .= " and a.ActiveTaskID=0 ";
            if (!empty(input('post.approveResult')))
                $where .= " and a.ApproveResult='" . input('post.approveResult') . "'";
            if (!empty(input('post.creator')))
                $where .= " and a.Creator=" . input('post.creator');
            if(!empty(input('post.createDateFrom')))
                $where .=" and a.CreateDate>='".input('post.createDateFrom')."'";
            if(!empty(input('post.createDateTo')))
                $where .=" and a.CreateDate<='".input('post.createDateTo')." 23:59'";
            if (!empty(input('post.Organization')) && stripos(input('post.Organization'), ',') == false)
                $where .= " and _Organization in (select id from  dbo.Z_F_GetOrganizationsSubID('".input('post.Organization')."'))";
            if (!empty(input('post.Organization')) && stripos(input('post.Organization'), ',') != false)
                $where .= " and _Organization in (" .input('post.Organization').")";
            if(!empty(input('post.key')))
                $where .=" and _CustomerNumber like '%".trim(input('post.key'))."%'";
            if (!empty(input('post.name')))
                $where .= " and _CustomerName like '%".trim(input('post.name'))."%'";
            $list = Db::name('FcActive')
                ->alias('a')
                ->field('a.*,dbo.S_F_GetUserNameByUserID(a.creator) as CreatorName,
                dbo.S_F_GetUserNameByUserID(a.SendUserID) as SendUserName,c.FlowUrl,
                dbo.S_F_GetActiveUserName(a.activeid) as ActiveUserName ')
                ->join("{$formData[0][0]['TableName']}" . " b", 'a.applicationId=b.id')
                ->join('S_TB_FC_FLOW c', 'a.FlowID=c.FlowID')
                ->where($where)
                ->order('SendDate', 'desc')
                ->paginate(10, false, ['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
            if($list){
                $data=$list->toArray()['data'];
                $page= $list->render();
                foreach ($data as $key => $value) {
                    $data[$key]['FlowTitle'] = $flowData[0]['FlowTitle'];
                    $data[$key]['CreateDate']=date('Y-m-d',strtotime($value['CreateDate']));
                    $data[$key]['SendDate']=date('Y-m-d',strtotime($value['SendDate']));
                    $data[$key]['approveResultText'] = $value['ApproveResult'] == 1 ? "同意" : "不同意";
                    !empty($value['FlowUrl']) && $data[$key]['FlowUrl'] = url($value['FlowUrl'], ['id' => $value['ApplicationID'], 'activeid' => $value['ActiveID']]);
                }
                return return_array_result(1,lang('查询成功'),'',['list'=>$data,'page'=> $page]);
            }else{
                return return_array_result(0,lang('查询失败'));
            }
        }else{
            return $this->fetch();
        }
    }
    /*
     * @ 保存流程
     * */
    public function saveFlowProcess()
    {
        if (request()->isPost()) {
            $activeID = input('param.activeid');
            $param = Db::name('FcActive')->where('Activeid=' . $activeID)->find();
            $status = Db::table($param['ApplicationTable'])
                ->where('id', $param['ApplicationID'])
                ->update(input('post.'));
            if ($status) {
                return return_array_result(1, lang('保存成功!'));
            } else {
                return return_array_result(0, lang('保存失败!'));
            }
        } else {
            return return_array_result(0, lang("请求方式不合法!"));
        }
    }

    /*
     * @ 删除流程
     * */
    public function  delFlowProcess()
    {
        if (request()->isPost()) {
            $activeID = input('post.id');
            if (input('post.key') == 'schedule') {
                $status = Db::execute("exec Z_SP_Function_DeleteActive $activeID");
            } else if (input('post.key') == 'todo') {
                $status = Db::execute("exec Z_SP_Function_EndQuotaToApprove $activeID");
            }
            if ($status) {
                return return_array_result(1, lang('删除成功!'));
            } else {
                return return_array_result(0, lang('删除失败!'));
            }
        } else {
            return return_array_result(0, lang("请求方式不合法!"));
        }
    }

    /*
     * 删除待评估
     * */
    public function  delFlowAssessmen()
    {
        if (request()->isPost()) {
            $activeID = input('param.id');
            $status = Db::execute("exec Z_SP_Function_EndQuotaToApprove $activeID");
            if ($status) {
                return return_array_result(1, lang('删除成功!'));
            } else {
                return return_array_result(0, lang('删除失败!'));
            }
        } else {
            return return_array_result(0, lang("请求方式不合法!"));
        }
    }
}