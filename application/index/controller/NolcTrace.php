<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/11/20
 * Func: 非LC限额跟踪
 */
namespace app\index\controller;

use think\Db;

class NolcTrace extends Common
{
    function initialize()
    {
        parent::initialize();
        $this->model = model('flow');
    }

    /*
     * @ 数据查询
     * */
    public function index()
    {
        if ($this->request->isPost()) {
            $where = "1=1";
            if (!empty(input('post.Organization')) && input('post.Organization') != 1)
                $where .= " and Organization=" . input('post.Organization');
            if (!empty(input('post.OrganizationFlag')))
                $where .= " and OrganizationFlag='" . input('post.OrganizationFlag') . "'";
            if (!empty(input('post.sinosureBuyerNo')))
                $where .= " and sinosureBuyerNo like '%" . trim(input('post.OrganizationFlag')) . "%'";
            if (!empty(input('post.corpBuyerNo')))
                $where .= " and corpBuyerNo like '%" . trim(input('post.corpBuyerNo')) . "%'";
            if (!empty(input('post.buyerEngName')))
                $where .= " and buyerEngName like '%" . trim(input('post.buyerEngName')) . "%'";
            $list = Db::table('Z_VE_NoLCQuotaApppy')
                ->field(' *,dbo.S_F_GetNOLCStatus(activeid) as status')
                ->where($where)
                ->paginate(10, false, ['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
            if ($list) {
                $data = $list->toArray()['data'];
                foreach ($data as $k => $v) {
                    $data[$k]['FlowUrl'] = url('NolcTrace/detail', ['activeid' => $v['ActiveID']]);
                }
                return return_array_result(1, lang('查询成功'), '', ['list' => $data, 'page' => $list->render()]);
            } else {
                return return_array_result(0, lang('查询失败'));
            }
        } else {
            return $this->fetch();
        }
    }

    /*
     * @ 客户评估详情
     * */
    public function detail()
    {
        $activeid = input('param.activeid');
        $resultData = Db::table('Z_VE_NoLCQuotaApppy')
            ->field(' *,dbo.S_F_GetNOLCStatus(activeid) as status')
            ->where('Activeid', $activeid)
            ->find();
        $this->assign('data', $resultData);
        $modelid = input("param.modelid");
        $formData = [];
        if (!empty($modelid)) {
            $formData = Db::query("select *,dbo.Z_F_GetEvaluateItemValue(EvaluateItemName,'" . $resultData['sinosurebuyerno'] . "') as SysValue
           from [dbo].[S_TB_CreditEvaluate_Model_Detail] a left join [dbo].[S_TB_CreditEvaluate_EvaluateItem] b on
           a.EvaluateItemID=b.EvaluateItemID where a.ModelID=$modelid");
            $itemValue = Db::table('S_TB_CreditEvaluate_EvaluateItemDetail')->order('EvaluateItemDetailIndex')->select();
            foreach ($formData as $k => $v) {
                foreach ($itemValue as $key => $value) {
                    if ($v['DataType'] == 'str' && ($value['EvaluateItemID'] == $v['EvaluateItemID']))
                        $param[] = $value;
                }
                $formData[$k]['ItemValue'] = !empty($param) ? $param : "";
                unset($param);
            }
        }
        $this->assign('modelid', $modelid);
        $this->assign('formData', $formData);
        return $this->fetch();
    }

    /*
     *@ 模型计算
     * */
    public function calCulate()
    {
        if ($this->request->isPost()) {
            $param = input('post.');
            $resultData = Db::table('Z_VE_NoLCQuotaApppy')
                ->field(' *,dbo.S_F_GetNOLCStatus(activeid) as status')
                ->where('Activeid', $param['activeid'])
                ->find();
            $gradeScore = Db::name('CreditevaluateModel')
                ->field('*')
                ->where('ModelID', $param['modelid'])
                ->find();
            $formData = Db::query("select *,dbo.Z_F_GetEvaluateItemValue(EvaluateItemName,'" . $resultData['sinosurebuyerno'] . "') as SysValue
           from [dbo].[S_TB_CreditEvaluate_Model_Detail] a left join [dbo].[S_TB_CreditEvaluate_EvaluateItem] b on
           a.EvaluateItemID=b.EvaluateItemID where a.ModelID={$param['modelid']}");
            $dWeight = 0;
            $dScore = 0;
            $dMScore = 0;
            $modelData = [];
            foreach ($param as $key => $value) {
                if ($value != '' && in_array($key, array_column($formData, 'ItemKey'))) {
                    foreach ($formData as $k => $v) {
                        if ($v['DataType'] == 'str' && $key == $v['ItemKey']) {
                            $modelData[$v['ItemKey']] = $value * $v['Weight'];
                            $dWeight += $v['Weight'];
                            $dScore += $modelData[$v['ItemKey']];
                            $dMScore += $modelData[$v['ItemKey']];
                        } else if ($v['DataType'] == 'num' && $key == $v['ItemKey']) {
                            $itemValue = Db::table('S_TB_CreditEvaluate_EvaluateItemDetail')
                                ->where('EvaluateItemID', $v['EvaluateItemID'])
                                ->order('EvaluateItemDetailIndex')
                                ->select();
                            foreach ($itemValue as $key1 => $value1) {
                                if (($value1['StartValue'] == "" && $value <= $value1['EndValue']) ||
                                    ($value > $value1['StartValue'] && $value <= $value1['EndValue']) ||
                                    ($value > $value1['StartValue'] && $value1['EndValue'] == "")
                                ) {
                                    $modelData[$v['ItemKey']] = $v['Weight'] * $value1['Ratio'];
                                    $dWeight += $v['Weight'];
                                    $dScore += $modelData[$v['ItemKey']];
                                    $dMScore += $modelData[$v['ItemKey']];
                                }
                            }
                        }
                    }
                } else if ($value == '' && in_array($key, array_column($formData, 'ItemKey'))) {
                    $modelData[$key] = 0;
                } else {
                    $modelData[$key] = $value;
                }
            }
            $dScore = $dWeight == 0 ? 0 : round($dScore * 100 / $dWeight, 2);
            $modelData['Score'] = $dScore;
            $modelData['MScore'] = $dMScore;
            $c = 0;
            if ($resultData['FlowID'] == 21)
                $c = $resultData['payTerm'] - 40;///若是海外的账期，则再减去40天。若是非海外的账期，则不需再减去40天。
            else if ($resultData['FlowID'] == 35 || $resultData['FlowID'] == "") {
                if ($resultData['payTerm'] == "")
                    $modelData['payTerm'] = 0;
                $c = $resultData['payTerm'];
            }
            $modelData['CalTerm'] = $c < 0 ? 0 : $c;
            if ($dScore >= $gradeScore['AScore']) {
                $modelData['Grade'] = "A";
                $modelData['CalLimit'] = $resultData['quotaSum'];
            } else if ($dScore >= $gradeScore['BScore']) {
                $modelData['Grade'] = "B";
                $modelData['CalLimit'] = $resultData['quotaSum'];
            } else if ($dScore >= $gradeScore['CScore']) {
                $modelData['Grade'] = "C";
                $modelData['CalLimit'] = $resultData['quotaSum'] * 0.8;
            } else {
                $modelData['Grade'] = "D";
                $modelData['CalLimit'] = 0;
                $modelData['CalTerm'] = 0;
            }
            return $modelData;
        }
    }

    /*
     *@ 产生复评申请流程
     * */
    public function createRepply()
    {
        if ($this->request->isPost()) {
            $param = input('post.');
            $resultData = Db::table('Z_VE_NoLCQuotaApppy')
                ->field(' *,dbo.S_F_GetNOLCStatus(activeid) as status')
                ->where('Activeid', $param['activeid'])
                ->find();
            // S_TB_Customer_Credit_Evaluate（多次保存一人一日保存一条数据）
            $array = Db::table('S_TB_Customer_Credit_Evaluate')
                ->where([
                    'CustomerNumber' => $resultData['sinosurebuyerno'],
                    'EvaluateBy' => session('auth_id'),
                    'EvaluateDate' => date('Y-m-d', time())
                ])->find();
            // 存在就更新,不存在就新增
            if ($array) {
                Db::table('S_TB_Customer_Credit_Evaluate')
                    ->where('ID', $array['ID'])->update([
                        'EvaluateBy' => session('auth_id'),
                        'EvaluateDate' => date('Y-m-d', time()),
                        'ModelId' => $param['modelid'],
                        'Score' => $param['Score'],
                        'Grade' => $param['Grade'],
                        'SystemSuggest' => $param['MScore'],
                    ]);
            } else {
                Db::table('S_TB_Customer_Credit_Evaluate')->insertGetId([
                    'CustomerNumber' => $param['sinosurebuyerno'],
                    'CustomerName' => $param['buyerEngName'],
                    'EvaluateBy' => session('auth_id'),
                    'EvaluateDate' => date('Y-m-d', time()),
                    'ModelId' => $param['modelid'],
                    'ScoreCard' => '',
                    'Score' => $param['Score'],
                    'Grade' => $param['Grade'],
                    'SystemSuggest' => $param['MScore'],
                ]);
            }
            // 信保额度复评流程主表创建数组
            $data=[
                'Organization' => $resultData['Organization'],
                'sinosurebuyerno' => $resultData['sinosurebuyerno'],
                'corpbuyerno' => $resultData['corpBuyerNo'],
                'buyerEngName' => $resultData['buyerEngName'],
                'refuseRate' => $resultData['RefuseRate2'],
                'otherRate' => $resultData['OtherRate2'],
                'Grade' => $param['Grade'],
                'Score' => $param['Score'],
                'MScore' => $param['MScore'],
                'quotaSum' =>  $resultData['quotaSum'],
                'GrantedLimit' => $resultData['quotaSum'],
                'SuggestLimit' =>  $param['CalLimit'],
                'CalTerm' => $param['CalTerm'],
                'ContractPayModeText' => $resultData['contractPayModeText'],
                'GoodName' =>  $resultData['goodsName'],
                'TradeSum' => $resultData['thisYearTradeSum'],
                'TransPlan' => $resultData['otherExplain'],
                'Creator' =>  session('auth_id'),
                'CreatorName' => session('user.UserName'),
                'CreateDate' => date('Y-m-d',time()),
            ];
            // 触发器返回activeid
            $activeid = Db::table('Z_TB_FLowApp_CreditApplication')->insertGetId($data);
            $id = Db::table('Z_TB_FLowApp_CreditApplication')->where('activeid', $activeid)->value('id');
            //  Z_TB_Buyer_Credit_EvaluateDetail]（多条评估表数据）
            $buyerMap=[];
            $formData = Db::query("select *,dbo.Z_F_GetEvaluateItemValue(EvaluateItemName,'" . $resultData['sinosurebuyerno'] . "') as SysValue
           from [dbo].[S_TB_CreditEvaluate_Model_Detail] a left join [dbo].[S_TB_CreditEvaluate_EvaluateItem] b on
           a.EvaluateItemID=b.EvaluateItemID where a.ModelID={$param['modelid']}");
            foreach ($formData as $k => $v) {
                $buyerMap[$k]['ActiveID'] = $activeid;
                $buyerMap[$k]['ItemName'] = $v['EvaluateItemName'];
                $buyerMap[$k]['ItemWeight'] = $v['Weight'];
                foreach ($param as $key => $value) {
                    if ($v['DataType'] == 'str' && $key == $v['ItemKey']) {
                        $itemValue = Db::table('S_TB_CreditEvaluate_EvaluateItemDetail')
                            ->where(['EvaluateItemID' => $v['EvaluateItemID'], 'Ratio' => $param[$key]])
                            ->value('StartValue');
                        $buyerMap[$k]['ItemValue'] = $itemValue; //取值
                        $buyerMap[$k]['ItermGrade'] = $param[$key."Value"];// 得分
                        $buyerMap[$k]['memo'] = $param[$key."Memo"];  // 备份
                    } else if ($v['DataType'] == 'num' && $key == $v['ItemKey']) {
                        $buyerMap[$k]['ItemValue'] = $value;
                        $buyerMap[$k]['ItermGrade'] = $param[$key."Value"];
                        $buyerMap[$k]['memo'] = $param[$key."Memo"];
                    }
                }
                $buyerMap[$k]['EvaluateResultID'] = $id;
            }
            $buyerCreditResult=Db::table('Z_TB_Buyer_Credit_EvaluateDetail')->insertAll($buyerMap);

            // 存在就更新,不存在就新增
            $arrayLast = Db::table('S_TB_Customer_Credit_Evaluate_Latest')
                 ->where('CustomerNumber',$resultData['sinosurebuyerno'])->find();
            if ($arrayLast) {
                Db::table('S_TB_Customer_Credit_Evaluate_Latest')
                    ->where('CustomerNumber',$resultData['sinosurebuyerno'])
                    ->update([
                        'LastUpdateBy' => session('auth_id'),
                        'LastUpdateDate' => date('Y-m-d', time()),
                        'ModelId' => $param['modelid'],
                        'Score' => $param['Score'],
                        'Grade' => $param['Grade'],
                        'SystemSuggest' => $param['MScore'],
                    ]);
            } else {
                Db::table('S_TB_Customer_Credit_Evaluate_Latest')->insertGetId([
                    'CustomerNumber' => $param['sinosurebuyerno'],
                    'CustomerName' => $param['buyerEngName'],
                    'LastUpdateBy' => session('auth_id'),
                    'LastUpdateDate' => date('Y-m-d', time()),
                    'ModelId' => $param['modelid'],
                    'ScoreCard' => '',
                    'Score' => $param['Score'],
                    'Grade' => $param['Grade'],
                    'SystemSuggest' => $param['MScore'],
                ]);
            }
            // 修改信保额度状态
            Db::table('Z_TB_QuotaApproveDetail')->where('ActiveID',$param['activeid'])->update([
                'Score' => $param['Score'],
                'Grade' => $param['Grade'],
                'CalLimit' => $param['CalLimit'],
                'CalTerm' => $param['CalTerm'],
                'IfSubmitToApprove' => '是',
                'CreditActiveid' => $activeid,
            ]);
            return return_array_result(1,lang(''),url('CreditRepply/index',['id'=>$id,'activeid'=>$activeid]));
        }
    }
}