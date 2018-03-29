<?php
namespace app\index\model;

use think\Model;
use think\Db;

class Flow extends Model
{
    /*
 * @ $flowid :流程id
 * @ $applicationid :流程主表id
 * @ $userId :当前用户UserID
 * @ function:启动流程
 * */
    public function newActive($flowId, $applicationId, $userId,$type)
    {
        $map = [
            'FlowID' => $flowId,
            'Creator' => $userId,
            'CreateDate' => date('Y-m-d H:i:s', time()),
            'ApplicationID' => $applicationId,
            'status'=>'1',
            'statusName'=>'申请',
            'KeyCol10'=>$type
        ];
        Db::startTrans();
        try {
            $newActiveID = Db::table('S_TB_FC_Active')->insertGetId($map);
            $where = [
                'ActiveID' => $newActiveID,
                'UserID' => $userId,
                'IsFinished' => 0
            ];
            Db::table('S_TB_FC_ActiveUser')->insert($where);
            Db::commit();
            return $newActiveID;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /*
     * @param  $flowid流程id
     * @return 流程数组
     * */
    public function getFlowByID($flowid)
    {
        $result = Db::name('FcFlow')
            ->alias('a')
            ->field('a.*,b.FormTitle as SelectFormTitle,c.FormTitle as ApplicationFormTitle')
            ->join('S_TB_Form b', 'a.selectformkey=b.formkey', 'left')
            ->join('S_TB_Form c', 'a.applicationformkey=c.formkey', 'left')
            ->where('flowid', $flowid)
            ->order('FlowIndex','desc')
            ->select();
        return $result;
    }

    /*
     * @param  $formKey流程所对应主表
     * @return 流程数组
     * */
    public function getFormByKey($formKey)
    {
        $result = Db::query("exec S_SP_UserDefined_Form_GetForms '$formKey'");
        return $result;
    }

    /*
     * @int $activeid :流程id
     * @array return $result
     * */
    public function getActiveData($activeid)
    {
        $result = Db::table('S_TB_FC_Active')->where('ActiveID', $activeid)->find();
        if ($result) {
            return $result;
        } else {
            return false;
        }

    }

    /*
     * @ $flowid :流程id
     * @ $applicationid :流程主表id
     * @ $userId :当前用户UserID
     * @ function:启动流程
     * */
    public function newStartActive($flowId, $applicationId, $userId)
    {
        $dtFlow = Db::table('S_TB_FC_Flow')->where('flowid', $flowId)->find();
        $dtForm = Db::query("select * from S_TB_Form where FormKey=(select ApplicationFormKey from S_TB_FC_Flow where flowid=$flowId)");
        if (!$dtForm)
            return 0;
        $dtApplication = Db::query("select * from " . $dtForm[0]['TableName'] . " where " . $dtForm[0]['KeyFieldName'] . "=$applicationId");
        $flowMapData = Db::query("select isnull(max(flowmapid),0) as flowMapID from S_TB_Flow_Map where flowid=" . $flowId . " and organization='" . $dtApplication[0]['Organization'] . "'");
        $flowMapID = $flowMapData[0]['flowMapID'];
        if ($flowMapData[0]['flowMapID'] == 0 && $dtApplication [0]['Organization'] != "0") //查找上级经营单位的ID
        {
            $organizationData = Db::query("select isnull(max(pid),0) as organization from S_TB_Organization where id={$dtApplication [0]['Organization']}");
            if ($organizationData[0]['organization'] == "0")
                return 0;
            $paramFlowMap = Db::query("select isnull(max(flowmapid),0) as flowMapID  from S_TB_Flow_Map where flowid=" . $flowId . " and organization='" . $organizationData[0]['organization'] . "'");
            $flowMapID = $paramFlowMap[0]['flowMapID'];
        }
        $dtTask = $this->getStartTask($flowId, $flowMapID);
        if (isset($dtApplication[0]['CustomerNumber']) && $dtApplication[0]['CustomerNumber'] != null)
            $CustomerNumber = $dtApplication[0]['CustomerNumber'];
        else
            if ($dtFlow && $dtFlow['CustomerNumberField'] != "")
                $CustomerNumber = $dtApplication[0][$dtFlow['CustomerNumberField']];
        if (isset($dtApplication[0]['CustomerName']) && $dtApplication[0]['CustomerName'] != null)
            $CustomerName = $dtApplication[0]['CustomerName'];
        else
            if ($dtFlow['CustomerNameField'] != null && $dtFlow['CustomerNameField'] != "")
                $CustomerName = $dtApplication[0][$dtFlow['CustomerNameField']];
        if ($dtApplication[0]['Organization'] != null)
            $Organization = $dtApplication[0]['Organization'];
        $map = [
            'FlowID' => $flowId,
            'Creator' => $userId,
            'CreateDate' => date('Y-m-d H:i:s', time()),
            'ActiveTaskID' => $dtTask[0]['TaskID'],
            'ActiveMessage' => $dtTask[0]['TaskTitle'],
            'ApplicationID' => $applicationId,
            'FlowMapID' => $flowMapID,
            'ApplicationTable' => $dtForm[0]['TableName'],
            '_CustomerNumber' => $CustomerNumber,
            '_CustomerName' => $CustomerName,
            '_Organization' => $Organization
        ];
        Db::startTrans();
        try {
            $newActiveID = Db::table('S_TB_FC_Active')->insertGetId($map);
            $where = [
                'ActiveID' => $newActiveID,
                'UserID' => $userId,
                'TaskID' => $dtTask[0]['TaskID'],
                'IsFinished' => 0
            ];
            Db::table('S_TB_FC_ActiveUser')->insert($where);
            Db::commit();
            return $newActiveID;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /*
     * @$activeid  :流程id
     * @$curuserid :当前userid
     * func :权限判断 $taskType -1/0/1 查看申请审批
     * */
    public function getFlowMyactive($activeid, $curuserid)
    {
        $myactive = Db::query("select c.tasktype,c.taskid,b.flowid,c.TaskTitle  as sectionname,b.FlowMapID as flowmapid,
        b.flowid,b._CustomerNumber as kunnr,b._CustomerName as name,b._Organization as organization,d.FlowUrl,
        b.activeid,b.applicationid,b.creator,b.sendbacktaskid,d.FlowTitle,e.username as creatorname,
        e.loginname as creatorno  from S_TB_FC_ActiveUser a inner join  S_TB_FC_Active b
        on a.ActiveID=b.ActiveID
        inner join S_TB_FC_Task c on  b.FlowID=c.FlowID and a.TaskID=c.TaskID and b.FlowMapID=c.FlowMapID
        inner join S_TB_FC_Flow d on b.flowid=d.flowid  left join S_TB_User e on b.creator=e.userid
        where  a.ActiveID=$activeid and a.userid=$curuserid");
        if ($myactive) {
            if ($myactive[0]['tasktype'] == 0) {
                $myactive[0]['tasktype'] = 0;
            } else {
                $myactive[0]['tasktype'] = 1;
            }
        } else {
            $myactive = Db::query("select c.tasktype,c.taskid,a.flowid,c.TaskTitle  as sectionname,a.FlowMapID as flowmapid,
            a._CustomerNumber as kunnr,a._CustomerName as name,a._Organization as organization,d.FlowUrl,
            a.activeid,a.applicationid,a.creator,a.sendbacktaskid,d.FlowTitle,e.username as creatorname,
            e.loginname as creatorno  from S_TB_FC_Active  a left join   S_TB_FC_ActiveUser b on a.ActiveID=b.ActiveID
            left join S_TB_FC_Task c on  a.FlowID=c.FlowID and b.TaskID=c.TaskID and a.FlowMapID=c.FlowMapID
            left join S_TB_FC_Flow d on a.flowid=d.flowid  left join S_TB_User e on a.creator=e.userid
            where  a.ActiveID=$activeid");
            $myactive[0]['tasktype'] = -1;
        }
        return $myactive[0];
    }

    /*
     * func:获取开始节点
     * @$FlowID :流程id
     * @$FlowMapId：根据组织结构判断流程
     * */
    public function  getStartTask($FlowID, $FlowMapId)
    {
        $result = Db::query("select * from S_TB_FC_Task where flowid=" . $FlowID . " and FlowMapId=" . $FlowMapId . " and TaskType=0");
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /*
     * @ $activeid 流程id
     * @ $curuserid 当前用户id
     * @ $IsApproved 审批意见
     * @ $ApproveProcess 审批信息xml
     * @ $nextrouteid 下一步审批环节
     * @ $nextuserid   下一步审批人
     * @ 提交流程获取下一流程以及审批人
     * */
    public function getSubmitActive($activeid, $curuserid, $IsApproved, $ApproveProcess, $nextrouteid, $nextuserid)
    {
        $myactive = $this->getFlowMyactive($activeid, $curuserid);
        if ($myactive['tasktype'] == -1) {
            $retarray[0] = ["result" => "e", "msg" => "此流程无须当前用户提交"];
            return $retarray;
        }
        $flowid = $myactive["flowid"];
        $flowmapid = $myactive["flowmapid"];
        $taskid = $myactive["taskid"];
        $applicationid = $myactive["applicationid"];
        $creator = $myactive["creator"];
//        $sendbackuserid = $myactive["sendbackuserid"];
        $sendbacktaskid = $myactive["sendbacktaskid"];
        $flowname = $myactive["FlowTitle"];
        $creatorname = $myactive["creatorname"];
        $creatorno = $myactive["creatorno"];
        $kunnrname = empty($myactive['kunnr']) ?: $myactive['kunnr'] . "(" . $myactive['name'] . ")";
        $str = $creatorno;

        $entity = $str;
        $url = "localhost/hisense/" . $myactive['FlowUrl'] . "/index/activeid/" . $activeid . "/applicationid/" . $applicationid . "/cl/1?loginname=";
        //返回下个审批路线
        if ($myactive && ($myactive["tasktype"] == 0 || $myactive["tasktype"] != 0 && $IsApproved != "") && $nextrouteid == "") {
            Db::name('FcActive')->where('activeid', $activeid)->update(['ApproveResult' => $IsApproved]);
            return $this->getNextRoute($activeid, $flowid, $flowmapid, $taskid, $curuserid, $IsApproved);
        }
        //返回下个审批人
        if ($myactive && ($myactive["tasktype"] == 0 || $myactive["tasktype"] != 0 && $IsApproved != "") && $nextrouteid != "" && $nextuserid == "") {
            $task = Db::query("select tasktype,a.tasktitle,a.taskid from S_TB_FC_Task a inner join S_TB_FC_Route b
                    on a.FlowID=b.FlowID and a.FlowMapID=b.FlowMapID and a.TaskID=b.BackTaskID where b.FlowMapID='$flowmapid'
                    and b.flowid=$flowid and b.routeid=$nextrouteid");
            if ($task[0]["tasktype"] == "2") {
                $sql = "exec S_SP_MoveToNextTask $activeid,$flowid,$flowmapid,$nextrouteid,$curuserid,0,'$IsApproved','$ApproveProcess'";
                Db::execute($sql);
                if ($IsApproved == "1") {
                    $sql = "exec S_SP_EndActive $activeid,$flowid";
                    Db::execute($sql);
                } else if ($IsApproved == "5" || $IsApproved == "6") {
                    //放货申请结束时不建议不同意等同于拒绝
                    $sql = "update S_TB_FC_Active set ApproveResult='2' where activeid=$activeid";
                    Db::execute($sql);
                }
                $sql = "exec S_SP_SendEndMail $activeid,'" . $url . "'";
                Db::execute($sql);
                $retarray[0] = array("result" => "s", "msg" => "流程已结束");
                return $retarray;
            }
            $retarray[0] = array("result" => "u", "msg" => $task[0]["tasktitle"]);
            //$retarray[]=array("userid"=>1,"username"=>管理员);
            //单角色选择改成多角色选择
            $sql = " select distinct(roleid),rolefilter from S_TB_Role where charindex(','+ltrim(roleid)+',',','+
                (select roleid from S_TB_FC_Task a inner join S_TB_FC_Route b on a.FlowID=b.FlowID and a.FlowMapID=b.FlowMapID
                and a.TaskID=b.BackTaskID where b.FlowMapID=$flowmapid and b.flowid=$flowid and b.routeid=$nextrouteid)+',')>0";
            $roles = Db::query($sql);
            foreach ($roles as $role) {
                $roleid = $role["roleid"];

                if (($role["rolefilter"] != "" && (strpos(strtolower($role["rolefilter"]), "vkorg") === 0 || strpos(strtolower($role["rolefilter"]), "vkorg") > 0)) && ($role["rolefilter"] != "" &&
                        (strpos(strtolower($role["rolefilter"]), "vtweg") === 0 || strpos(strtolower($role["rolefilter"]), "vtweg") > 0))
                ) {
                    $sql = "select distinct a.userid,username from S_TB_User a inner join S_TB_User_role b on a.userid=b.userid
                            inner join S_TB_User_Role_Detail c on b.UserRoleID=c.UserRoleID  where b.roleid=$roleid";
                } else
                    $sql = "select distinct a.userid,username from S_TB_User a inner join S_TB_User_role b on a.userid=b.userid
                            inner join S_TB_User_Role_Detail c on b.UserRoleID=c.UserRoleID  where b.roleid=$roleid";
                $users = Db::query($sql);

                foreach ($users as $user) {
                    $retarray[] = array("userid" => $user["userid"], "username" => $user["username"]);
                }
            }
            //下一步人员为空处理
            if (sizeof($retarray) == 1) {
                if ($IsApproved == "")
                    $IsApproved = "1";
                $nextroute1 = $this->getNextRoute($activeid, $flowid, $flowmapid, $task[0]["taskid"], $curuserid, $IsApproved);
                if (sizeof($nextroute1) > 1) {
                    $nextrouteid = $nextroute1[1]["routeid"];
                    return $this->getSubmitActive($activeid, $curuserid, $IsApproved, $ApproveProcess, $nextrouteid, $nextuserid);
                }
            } else if (sizeof($retarray) == 2) {
                $nextuserid = $retarray[1]["userid"];
                if ($curuserid == $nextuserid)    //下一步审批人同当前用户则跳过当前审批人
                {
                    if ($IsApproved == "")
                        $IsApproved = "1";
                    $nextroute1 = $this->getNextRoute($activeid, $flowid, $flowmapid, $task[0]["taskid"], $curuserid, $IsApproved);
                    if (sizeof($nextroute1) > 1) {
                        $nextrouteid = $nextroute1[1]["routeid"];
                        return $this->getSubmitActive($activeid, $curuserid, $IsApproved, $ApproveProcess, $nextrouteid, "");
                    }
                } else
                    return $this->getSubmitActive($activeid, $curuserid, $IsApproved, $ApproveProcess, $nextrouteid, $nextuserid);
            }
            return $retarray;
        }
        //流向下个审批环节
        if ($myactive && ($myactive["tasktype"] == 0 || $myactive["tasktype"] != 0 && $IsApproved != "") && $nextrouteid != "" && $nextuserid != "") {
            Db::execute("exec S_SP_MoveToNextTask $activeid,$flowid,$flowmapid,$nextrouteid,$curuserid,$nextuserid,'$IsApproved','$ApproveProcess'");
            $activeuser = Db::query("select a.userid,b.username from S_TB_FC_ActiveUser a inner join S_TB_User b on a.UserID=b.userid where activeid=$activeid and a.userid=$nextuserid");
            if ($activeuser) {
                $retarray[0] = array("result" => "s", "msg" => "流程已流向下个审批人:" . $activeuser[0]["username"]);
                $user = Db::query("select loginname,email,username from S_TB_User where userid=$nextuserid ");
                $email = $user[0]["email"];
                $emp_no = $user[0]["loginname"];
                $username = $user[0]["username"];
                $subject = "请审批" . $creatorname . "提交的" . $kunnrname . "的" . $flowname;
                $sql = "insert into S_TB_SendMail(Subject,Body,Receiver,ReceiverName) values ('$subject','$url$emp_no','$email','$username')";
                Db::execute($sql);
                //获取授权信息
                $agent = Db::query("select AcceptUserId,b.UserName as givename from S_TB_User_agent a inner join S_TB_User b
                on a.AcceptUserId=b.userid where a.AcceptUserId=$nextuserid and a.AcceptUserId!=0");
                if ($agent) {
                    $AcceptUserId = $agent[0]['AcceptUserId'];
                    $user = Db::query("select loginname,email,username from TB_User where userid=$AcceptUserId");
                    if ($user) {
                        $email = $user[0]["email"];
                        $emp_no = $user[0]["loginname"];
                        $username = $user[0]["username"];
                        $subject = "请代理" . $agent[0]["givename"] . "审批" . $creatorname . "提交的" . $kunnrname . "的" . $flowname;
                        $sql = "insert into S_TB_SendMail(Subject,Body,Receiver,ReceiverName)
						values('$subject','$url$emp_no','$email','$username')";
                        Db::execute($sql);
                    }
                }
            } else {
                $end = Db::query("select activeid,flowid,applicationid from S_TB_FC_Active where activeid=$activeid and ActiveTaskID=0");
                if ($end) {
                    $retarray[0] = array("result" => "s", "msg" => "流程已结束");
                    //流程结束处理
                    if ($IsApproved == "1") {
                        $sql = "exec S_SP_EndActive $activeid,$flowid";
                        Db::execute($sql);
                    }
                    $sql = "exec S_SP_SendEndMail $activeid,'" . $url . "'";
                    Db::execute($sql);
                } else
                    $retarray[0] = array("result" => "e", "msg" => "流程流转失败");
            }
            return $retarray;
        }
        //审批环节必须选择审批意见
        if ($myactive && $myactive[0]["tasktype"] != 0 && $IsApproved == "")
            return array(array("result" => "e", "msg" => "请选择审批意见!"));
        //必须选择下个审批路线
        if ($myactive && ($myactive[0]["tasktype"] == 0 || $myactive[0]["tasktype"] != 0 && $IsApproved != "")
            && $nextrouteid == ""
        )
            return array(array("result" => "e", "msg" => "请选择下个审批环节!"));
        //必须选择下个审批人
        if ($myactive &&
            ($myactive[0]["tasktype"] == 0 || $myactive[0]["tasktype"] != 0 && $IsApproved != "")
            && $nextrouteid != ""
            && $nextuserid == ""
        )
            return array(array("result" => "e", "msg" => "请选择下个审批人!"));
    }

    /*
     * @ $activeid 流程id
     * @ $flowid   当前用户id
     * @ $flowmapid    flowMapID
     * @ $taskid   当前结点
     * @ $curuserid 当前用户id
     * @ $IsApproved 审批意见
     * @ 获得下一环节
     * */
    public function getNextRoute($activeid, $flowid, $flowmapid, $taskid, $curuserid, $IsApproved)
    {
        $routeData = Db::name('FcRoute')
            ->field('RouteID,RouteTitle,RouteFormula')
            ->where(['Flowid' => $flowid, 'FlowMapID' => $flowmapid, 'FrontTaskID' => $taskid])
            ->select();
        $retarray[0] = array("result" => "r");
//        $myactive = $this->getFlowMyactive($activeid, $curuserid);
//        $approvelevel = $myactive["approvelevel"];
//        $vkorg = $myactive["vkorg"];
        foreach ($routeData as $route) {
//            if ($route["controlexpression"] != "") {
//                $exp = $route["controlexpression"];
//                if (strpos($route["controlexpression"], "approveresult") >= 0) {
//                    $exp = str_replace("approveresult", $IsApproved, $exp);
//                }
//                if (strpos($route["controlexpression"], "&&") >= 0) {
//                    $exp = str_replace("&&", "and", $exp);
//                }
//                if (strpos($route["controlexpression"], "||") >= 0) {
//                    $exp = str_replace("||", "or", $exp);
//                }
//                if (strpos($route["controlexpression"], "approvelevel") >= 0) {
//                    $exp = str_replace("approvelevel", $approvelevel, $exp);
//                }
//                if (strpos($route["controlexpression"], "vkorg") >= 0) {
//                    $exp = str_replace("vkorg", $vkorg, $exp);
//                }
//                $sqlexp = "select count(*) as expc from TB_FC_Flow where " . $exp;
//                dump($sqlexp);
//                $retexp = Db::query($sqlexp);
//                if ($retexp[0]["expc"] == 0)
//                    continue;
//            }
            $retarray[] = ["routeid" => $route["RouteID"], "routename" => $route["RouteTitle"]];
        }
        return $retarray;
    }

    /*
     * @  func 获得审批意见
     * @  $activeid 流程id
     * */
    public function getUserApprove($activeid)
    {
        $approveTesult = Db::query("select c.TaskTitle,case when f.username is not null then f.username+'(代理'+d.username+')' else d.username end as name,
        case b.ApproveResult when 1 then '同意' when 2 then '拒绝' when 3 then '撤销' when 4 then '退回' when 5 then '不建议' when 6 then '不同意'
        when 7 then '退回提交' end as ApproveResult,replace(b.ApproveProcess,char(10),'<br/>') as ApproveProcess,convert(varchar,b.SignDate,20) as SignDate
        from S_TB_FC_Active a inner join S_TB_FC_ActiveUserh b on a.activeid=b.activeid inner join S_TB_FC_Task c on a.FlowMapID=c.FlowMapID and b.TaskID=c.TaskID
        and a.FlowID=c.flowid inner join S_TB_User d on b.userid=d.userid left join S_TB_User f on b.AgentUserID=f.userid
        where a.activeid= $activeid and b.ApproveResult<>'' order by SignDate");
        $count = count($approveTesult);
        for ($i = 0; $i < $count; $i++) {
            $array = json_decode(json_encode(simplexml_load_string($approveTesult[$i]["ApproveProcess"])), TRUE);
            $approveTesult[$i]["Memo"] = !empty($array["Approve"]["Memo"]) ? $array["Approve"]["Memo"] : '';
        }
        return $approveTesult;
    }
}