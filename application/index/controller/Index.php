<?php
namespace app\index\controller;

use think\Db;

class Index extends Common
{
    /*
     * @ 初始化initialize
     * */
    public function initialize()
    {
        parent::initialize();
        $this->model = model('flow');
    }

    /*
     * @ 系统首页
     * @ 今日任务getTodoTask
     * @ 在办任务getDoingTask
     * @ 风险信息riskInfo
     * */
    public function index()
    {
      $this->getTodoTask();
        /*   $this->getTodoAssessment();
		 $this->getRiskInfo();*/
        return $this->fetch();
    }

    /*
     * @ 今日任务
     * */
    public function  getTodoTask()
    {
        $userID = session('auth_id');
        if($this->request->isPost()){
            $where="flowid=37 and activeuserid={$userID} and status!='10'";
            $list = Db::table('S_TB_FC_active')
                ->field('* ,[dbo].[S_F_GetUserNameByUserID](senduserid) as sendusername ,[dbo].[S_F_GetUserNameByUserID](activeuserid) as activeusername')
                ->where($where)
                ->order('senddate','desc')
                ->paginate(5,false,['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:getTodoTask([PAGE]);']);
            $data=$list->toArray()['data'];
            $page = $list->render();
            $this->assign('data',$data);
            $this->assign('page',$page);
            if($list){
                $data=$list->toArray()['data'];
                $page=$list->render();
                return return_array_result(1,lang('查询成功'),'',['list'=>$data,'page'=> $page]);
            }
            else{
                return return_array_result(0,lang('查询失败'));
            }
        }
        else{
            $where="flowid=37 and activeuserid={$userID} and status!='10'";
            $list = Db::table('S_TB_FC_active')
                ->field('* ,[dbo].[S_F_GetUserNameByUserID](senduserid) as sendusername ,[dbo].[S_F_GetUserNameByUserID](activeuserid) as activeusername')
                ->where($where)
                ->order('senddate','desc')
                ->paginate(5,false,['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:getTodoTask([PAGE]);']);
            $data=$list->toArray()['data'];
            $page = $list->render();
            $this->assign('data',$data);
            $this->assign('page',$page);
        }
    }

    /*
     * @ 待信用评估非LC限额
     * */
    public function  getTodoAssessment()
    {
        $userID = session('auth_id');
        $countScore = Db::query("select count(*) as  count from Z_VE_NoLCQuotaApppy where (OrganizationFlag='Q'
        and exists(select * from S_TB_User_Role where UserID=$userID and RoleID in (132,152)) or OrganizationFlag='S'
        and exists(select * from S_TB_User_Role where UserID=$userID and RoleID=146) ) and approveflag=1 and
        IfSubmitToApprove='否' and ifend='否'");
        $limitRows = 5; // 设置每页记录数
        if (request()->isAjax()) {
            $page = empty(input('post.page')) ? 1 : input('post.page');
            $prePage = ($page - 1) * $limitRows + 1;
            $nextPage = $page * $limitRows;
            $resultList = Db::query(" with A as (select *,row_number() over(order by activeid desc) as rowunmber from Z_VE_NoLCQuotaApppy
            where (OrganizationFlag='Q' and exists(select * from S_TB_User_Role where UserID=$userID and RoleID in (132,152)) or
            OrganizationFlag='S'and exists(select * from S_TB_User_Role where UserID=$userID and RoleID=146) ) and approveflag=1 and
            IfSubmitToApprove='否' and ifend='否')
            select isnull(auditDate,'') as auditDate,* from A  where rowunmber between $prePage and $nextPage");
            foreach ($resultList as $k => $v) {
                if (!empty($v['ActiveID']) && $resultList[$k]['ActiveID'])
                    $resultList[$k]['FlowUrl'] = url('NolcTrace/detail', ['activeid' => $v['ActiveID']]);
                !empty($v['auditDate']) && $resultList[$k]['auditDate'] = date('Y-m-d', strtotime($v['auditDate']));
            }
            $Page = new \think\paginator\driver\Bootstrap($resultList, $limitRows, $page, $countScore[0]['count'], false, ['path' => 'javascript:getTodoAssessment([PAGE]);']);
            return ['todoAssessmenPage' => $Page->render(), 'todoAssessmenList' => $resultList];

        } else {
            $page = empty(input('get.page')) ? 1 : input('get.page');
            $prePage = ($page - 1) * $limitRows + 1;
            $nextPage = $page * $limitRows;
            $resultList = Db::query(" with A as (select *,row_number() over(order by activeid desc) as rowunmber from Z_VE_NoLCQuotaApppy
            where (OrganizationFlag='Q' and exists(select * from S_TB_User_Role where UserID=$userID and RoleID in (132,152)) or
            OrganizationFlag='S'and exists(select * from S_TB_User_Role where UserID=$userID and RoleID=146) ) and approveflag=1 and
            IfSubmitToApprove='否' and ifend='否')
            select isnull(auditDate,'') as auditDate,* from A  where rowunmber between $prePage and $nextPage");
            foreach ($resultList as $k => $v) {
                if (!empty($v['ActiveID']) && $resultList[$k]['ActiveID'])
                    $resultList[$k]['FlowUrl'] = url('NolcTrace/detail', ['activeid' => $v['ActiveID']]);
                !empty($v['auditDate']) && $resultList[$k]['auditDate'] = date('Y-m-d', strtotime($v['auditDate']));
            }
            $Page = new \think\paginator\driver\Bootstrap($resultList, $limitRows, $page, $countScore[0]['count'], false, ['path' => 'javascript:getTodoAssessment([PAGE]);']);
            $this->assign('todoAssessmenPage', $Page->render());// 赋值分页输出
            $this->assign('todoAssessmenList', $resultList);
        }
    }

    /*
     * @ 中信保修改
     * */
    public function  getSinosureEdit()
    {
        if($this->request->isPost()){
            $id = input('post.id');
            $param = request()->except(['TBGS','id']);
            $list = Db::table('Z_TB_SinoSure_QuotaApproveInfo')->where('ID', $id)->update($param);
            if ($list !== false) {
                return return_array_result(1, lang('保存成功'));
            } else {
                return return_array_result(0, lang('保存失败'));
            }
        }else if($this->request->isGet()){
            $result=Db::table('Z_TB_SinoSure_QuotaApproveInfo')
                ->where('ID',input('param.id'))
                ->find();
            if(!empty($result)){
                return return_array_result(1,lang("查询成功"),'',$result);
            }else{
                return return_array_result(0,lang("查询失败"));
            }
        }

    }

    /*
     * @ 太保录入
     * */
    public function getTurboInsert()
    {
        if($this->request->isPost()){
            $id = input('post.id');
            $param = request()->except(['id']);
            if(empty($id)){
                $status = Db::table('Z_TB_SinoSure_QuotaApproveInfo')
                    ->where(['noticeSerialNo'=>input('param.noticeSerialNo')])
                    ->find();
                if($status){
                    return return_array_result(0, lang('第一个流水号重复，请更换一个!'));
                }
                $list = Db::table('Z_TB_SinoSure_QuotaApproveInfo')
                    ->insertGetId($param);
                if($list){
                    return return_array_result(1, lang('录入成功'));
                }else{
                    return return_array_result(0, lang('录入失败'));
                }
            }else{
                $list = Db::table('Z_TB_SinoSure_QuotaApproveInfo')
                    ->where(['corpSerialNo'=>input('param.id'),'TBGS'=>'太保'])
                    ->update($param);
                if ($list !== false) {
                    return return_array_result(1, lang('保存成功'));
                } else {
                    return return_array_result(0, lang('保存失败'));
                }
            }
        }else if($this->request->isGet()){
            $result=Db::table('Z_TB_SinoSure_QuotaApproveInfo')
                ->where(['corpSerialNo'=>input('param.id'),'TBGS'=>'太保'])
                ->find();
            if(!empty($result)){
                return return_array_result(1,lang("查询成功"),'',$result);
            }else{
                return return_array_result(0,lang("查询失败"));
            }
        }
    }
    /*
     * @ 风险信息查询
     * */
    public function  getRiskInfo(){
        $riskInfo = Db::table('Z_TB_Sinosure_InfoRetrieve')
            ->order('PublishTime', 'desc')
            ->paginate(5, false, ['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:getRiskInfo([PAGE]);']);
        if ($this->request->isPost()) {
            if ($riskInfo) {
                $data = $riskInfo->toArray()['data'];
                $page = $riskInfo->render();
                return return_array_result(1, lang('查询成功'), '', ['list' => $data, 'page' => $page]);
            } else {
                return return_array_result(0, lang('查询失败'));
            }
        } else {
            $this->assign('riskPage', $riskInfo->render());// 赋值分页输出
            $this->assign('riskList', $riskInfo->toArray()['data']);
        }
    }

}
