<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2018/3/5
 * Time: 14:49
 */
namespace app\index\controller;

use think\Db;

class Report extends Common
{

    public function  index()
    {
        $applicationid = input('get.applicationid');
        $resultData = Db::table('Z_TB_WTDApply')->where('id', $applicationid)->find();
        $this->assign('data', $resultData);
        $summanyData = Db::table('Z_TB_Summany_information')->where('applicationid', $applicationid)->find();
        $this->assign('summanyData', $summanyData);
        $companyDate = Db::table('Z_TB_company_information1')->where('applicationid', $applicationid)->find();
        $this->assign('companyDate', $companyDate);
        //变更
        $capital_list=Db::table('Z_TB_companyInformation_Change')->where('applicationid',$applicationid)->order('id','desc')->select();
        $this->assign('capital_list',$capital_list);
        //股东情况
        $share_list=DB::table('Z_TB_ShareholderDetail')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $total_money=0;//出资额总计
        $total_rate=0;//股权总计
        foreach($share_list as $v){
            $total_money+=$v['money'];
            $total_rate+=$v['rate'];
        }
        $this->assign('share_list',$share_list);
        $this->assign('total_money',$total_money);
        $this->assign('total_rate',$total_rate);

        //分支机构(主表)
        $branOrg=Db::table("Z_TB_Branch")->where('applicationid',$applicationid)->value('branch');
        $this->assign('branOrg',$branOrg);

        //代表人对外投资
        $invest_list=DB::table('Z_TB_RepresentInvest')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('invest_list',$invest_list);

        //代表人在其他公司任职
        $position_list=DB::table('Z_TB_RepresentPosition')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('position_list',$position_list);

        //企业对外投资
        $companyinvest_list=DB::table('Z_TB_CompanyInvest')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('companyinvest_list',$companyinvest_list);

        //董事成员情况
        $board_list=DB::table('Z_TB_BoardDetail')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('board_list',$board_list);

        //经营者信息
        $runData=Db::table('Z_TB_CompanyInformation_Run')->where('applicationid',$applicationid)->find();
        $this->assign('runData',$runData);

        $run1Data=Db::table('Z_TB_CompanyInformation_Run1')->where('applicationid',$applicationid)->find();
        $this->assign('run1Data',$run1Data);

        //往来银行
        $bankData=Db::table('Z_TB_BankContact')->where('applicationid',$applicationid)->find();
        $this->assign('bankData',$bankData);

        //公共记录主数据
        $pubData=Db::table('Z_TB_PublicReport')->where('applicationid',$applicationid)->find();
        $this->assign('pubData',$pubData);
        //执行信息
        $exec_list=DB::table('Z_TB_ReportExecute')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('exec_list',$exec_list);
        //失信信息
        $dishonesty_list=DB::table('Z_TB_ReportDishonesty')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('dishonesty_list',$dishonesty_list);
        //法院公告
        $notice_list=DB::table('Z_TB_ReportCourtNotice')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('notice_list',$notice_list);
        //裁判文书
        $referee_list=DB::table('Z_TB_ReportRefereeDocument')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('referee_list',$referee_list);

        //资产表
        $asetData=Db::table('Z_TB_financial_assets')->where('applicationid',$applicationid)->find();
        if(!$asetData){
            $this->assign('asetData',$asetData);
        }else{
            $data=Db::table('Z_TB_financial_assets')->where('applicationid',$applicationid)->order('year','desc')->select();
            $this->assign('asetData',$data);
        }

        //负债表
        $debtData=Db::table('Z_TB_financial_Debt')->where('applicationid',$applicationid)->find();
        if(!$debtData){
            $this->assign('debtData',$debtData);
        } else{
            $data=Db::table('Z_TB_financial_Debt')->where('applicationid',$applicationid)->order('year','desc')->select();
            $this->assign('debtData',$data);
        }

        // 损益表
        $sunyiData=Db::table('Z_TB_Financial_Sunyi')->where('applicationid',$applicationid)->find();
        if(!$sunyiData){
            $this->assign('sunyiData',$sunyiData);
        } else{
            $data=Db::table('Z_TB_Financial_Sunyi')->where('applicationid',$applicationid)->order('year','desc')->select();
            $this->assign('sunyiData',$data);
        }

        //比率信息
        $rateData=Db::table('Z_TB_Financial_Rate')->where('applicationid',$applicationid)->find();
        $this->assign('rateData',$rateData);

        //行业对比参照
        $compData=Db::table('Z_TB_Financial_Comparison')->where('applicationid',$applicationid)->find();
        $this->assign('compData',$compData);

        //财务分析
        $analyseData=Db::table('Z_TB_Financial_Analyse')->where('applicationid',$applicationid)->value('analyse');
        $this->assign('analyseData',$analyseData);

        //核查信息
        $checkData=Db::table('Z_TB_InformationCheck')->where('applicationid',$applicationid)->find();
        $this->assign('checkData',$checkData);

        //综合信用评价
        $creData=Db::table('Z_TB_CreditAnalyse')->where('applicationid',$applicationid)->find();
        $this->assign('creData',$creData);

        //信用评级
        $evaData=Db::table('Z_TB_CreditEvaluate')->where('applicationid',$applicationid)->find();
        $this->assign('evaData',$evaData);

        echo $this->view->fetch('public/standardReports');

        $this->downWord($resultData['name']);
    }

    public function  depth()
    {
        $applicationid = input('get.applicationid');
        $resultData = Db::table('Z_TB_WTDApply')->where('id', $applicationid)->find();
        $this->assign('data', $resultData);

        //公司概要
        $summanyData = Db::table('Z_TB_Summany2_information')->where('applicationid', $applicationid)->find();
        $this->assign('summanyData', $summanyData);

        //注册信息
        $companyDate = Db::table('Z_TB_company_information1')->where('applicationid', $applicationid)->find();
        $this->assign('companyDate', $companyDate);

        //访问信息
        $invData=Db::table('Z_TB_Investigate_Information')->where('applicationid',$applicationid)->find();
        $this->assign('invData',$invData);

        //变更
        $capital_list=Db::table('Z_TB_companyInformation_Change')->where('applicationid',$applicationid)->order('id','desc')->select();
        $this->assign('capital_list',$capital_list);

        //资质情况
        $quali_list=DB::table('Z_TB_Qualification')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('quali_list',$quali_list);

        //顾问机构
        $adviser_list=DB::table('Z_TB_Adviser')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('adviser_list',$adviser_list);

        //股权情况
        $shareData=Db::table('Z_TB_Shareholder')->where('applicationid',$applicationid)->find();
        $this->assign('shareData',$shareData);

        //股东出资情况
        $holder_list=DB::table('Z_TB_ShareholderDetail')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $total_money=0;//出资额总计
        $total_rate=0;//股权总计
        foreach($holder_list as $v){
            $total_money+=$v['money'];
            $total_rate+=$v['rate'];
        }
        $this->assign('holder_list',$holder_list);
        $this->assign('total_money',$total_money);
        $this->assign('total_rate',$total_rate);

        //主要股东概况
        $sharedetail_list=DB::table('Z_TB_ShareholderDetail_Information')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('sharedetail_list',$sharedetail_list);

        //组织机构
        $orgData=Db::table('Z_TB_Organization')->where('applicationid',$applicationid)->find();
        $this->assign('orgData',$orgData);

        //职能部门
        $department_list=DB::table('Z_TB_Department')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('department_list',$department_list);

        //分支机构
        $branch_list=DB::table('Z_TB_BranchDepartment')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('branch_list',$branch_list);

        //全资和控股子公司
        $hold_list=DB::table('Z_TB_Subsidiary')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('hold_list',$hold_list);

        //参股企业
        $share_list=DB::table('Z_TB_Shares')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('share_list',$share_list);

        //经营场所
        $busData=Db::table('Z_TB_ProductBusiness_Detail')->where('applicationid',$applicationid)->find();
        $this->assign('busData',$busData);

        //生产设备
        $equipment_list=DB::table('Z_TB_ProduceDetail')->where("applicationid=$applicationid and [key]='equipment'")->order('id','desc')->select();
        $this->assign('equipment_list',$equipment_list);

        //生产规模
        $scale_list=DB::table('Z_TB_ProduceDetail')->where("applicationid=$applicationid and [key]='scale'")->order('id','desc')->select();
        $this->assign('scale_list',$scale_list);

        //内销明细
        $domestic_list=DB::table('Z_TB_SalesDetail')->where("applicationid=$applicationid and [key]='domestic'")->order('id','desc')->select();
        $total_volume=0;//销量
        $total_sales=0;//销售额
        foreach($domestic_list as $v){
            $total_volume+=$v['key1'];
            $total_sales+=$v['key2'];
        }
        $this->assign('total_volume1',$total_volume);
        $this->assign('total_sales1',$total_sales);
        $this->assign('domestic_list',$domestic_list);

        //外销明细
        $export_list=DB::table('Z_TB_SalesDetail')->where("applicationid=$applicationid and [key]='export'")->order('id','desc')->select();
        $total_volume=0;//销量
        $total_sales=0;//销售额
        foreach($export_list as $v){
            $total_volume+=$v['key1'];
            $total_sales+=$v['key2'];
        }
        $this->assign('total_volume2',$total_volume);
        $this->assign('total_sales2',$total_sales);
        $this->assign('export_list',$export_list);

        //内外销比较
        $compare_list=DB::table('Z_TB_SalesDetail')->where("applicationid=$applicationid and [key]='compare'")->order('id','desc')->select();
        $this->assign('compare_list',$compare_list);

        //销售方式比重
        $way_list=DB::table('Z_TB_SalesWaysRate')->where("applicationid",$applicationid)->order('id','asc')->select();
        $this->assign('way_list',$way_list);

        //竞争对手
        $Opponent_list=DB::table('Z_TB_SalesOpponent_Information')->where("applicationid",$applicationid)->order('id','desc')->select();
        $this->assign('Opponent_list',$Opponent_list);

        //员工信息
        $staffData=Db::table('Z_TB_Staff_Information')->where('applicationid',$applicationid)->find();
        $this->assign('staffData',$staffData);

        //核心管理层
        $board_list=DB::table('Z_TB_BoardDetail')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('board_list',$board_list);

        //董事长的简历
        $chairman_list=DB::table('Z_TB_ChairmanDetail')->where("applicationid=$applicationid and [key]='chairman'")->order('id','desc')->select();
        $this->assign('chairman_list',$chairman_list);

        //总经理的简历
        $manager_list=DB::table('Z_TB_ChairmanDetail')->where("applicationid=$applicationid and [key]='manager'")->order('id','desc')->select();
        $this->assign('manager_list',$manager_list);

        //采购结算
        $purData=Db::table('Z_TB_PurchaseProduct_Detail')->where('applicationid',$applicationid)->value('purchase');
        $this->assign('purData',$purData);
        //采购商品
        $purProchase_list=DB::table('Z_TB_PurchaseProduct')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('purProchase_list',$purProchase_list);
        //主要供应商
        $supplier_list=DB::table('Z_TB_SupplierCustomer_Information')->where("applicationid",$applicationid)->where("key",'supplier')->order('id','desc')->select();
        $this->assign('supplier_list',$supplier_list);
        //销售
        $sales_list=DB::table('Z_TB_SalesProduct')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('sales_list',$sales_list);
        //主要客户
        $customer_list=DB::table('Z_TB_SupplierCustomer_Information')->where("applicationid",$applicationid)->where("key",'customer')->order('id','desc')->select();
        $this->assign('customer_list',$customer_list);

        //资产情况
        $assetsData = Db::table('Z_TB_financial_assets')->where('applicationid', $applicationid)->find();
        if (!$assetsData) {
            $this->assign('assetsData', $assetsData);
        } else {
            $data = Db::table('Z_TB_financial_assets')->where('applicationid', $applicationid)->order('year', 'desc')->select();
            $this->assign('assetsData', $data);
        }

        //负债信息
        $debtData = Db::table('Z_TB_financial_Debt')->where('applicationid', $applicationid)->find();
        if (!$debtData) {
            $this->assign('debtData', $debtData);
        } else {
            $data = Db::table('Z_TB_financial_Debt')->where('applicationid', $applicationid)->order('year', 'desc')->select();
            $this->assign('debtData', $data);
        }

        //损益信息
        $sunyiData = Db::table('Z_TB_Financial_Sunyi')->where('applicationid', $applicationid)->find();
        if (!$sunyiData) {
            $this->assign('sunyiData', $sunyiData);
        } else {
            $data = Db::table('Z_TB_Financial_Sunyi')->where('applicationid', $applicationid)->order('year', 'desc')->select();
            $this->assign('sunyiData', $data);
        }

        //比率信息
        $rateData=Db::table('Z_TB_Financial_Rate')->where('applicationid', $applicationid)->find();
        if($rateData){
            $year=substr($rateData['createdate'],0,4);
        } else{
            $year=null;
        }
        $this->assign('year',$year);$this->assign('year',$year);
        $this->assign('rateData',$rateData);


        //财务分析
        $anaData=Db::table('Z_TB_Financial_Analyse')->where('applicationid', $applicationid)->value('analyse');
        $this->assign('anaData',$anaData);

        //往来银行信息
        $bankData=Db::table('Z_TB_BankContact')->where('applicationid',$applicationid)->find();
        $this->assign('bankData',$bankData);

        //诉讼影响
        $affectData=Db::table('Z_TB_LegalDispute_Affect')->where('applicationid',$applicationid)->value('affect');
        $this->assign('affectData',$affectData);

        //法律纠纷
        $dispute_list=DB::table('Z_TB_LegalDispute')->where('applicationid='.$applicationid)->order('id','desc')->select();
        $this->assign('dispute_list',$dispute_list);

        //企业发展历史
        $history_list=DB::table('Z_TB_Company_History')->where('applicationid='.$applicationid)->order('id','asc')->select();
        $this->assign('history_list',$history_list);

        //综合信用评价
        $creData=Db::table('Z_TB_CreditAnalyse')->where('applicationid',$applicationid)->find();
        $this->assign('creData',$creData);

        //信用评级
        $evaData=Db::table('Z_TB_CreditEvaluate')->where('applicationid',$applicationid)->find();
        $this->assign('evaData',$evaData);

        echo $this->view->fetch('public/depthReports');

        $this->downWord($resultData['name']);
    }

    public function  downWord($companyName)
    {
        ob_start(); //打开缓冲区
        Header("Cache-Control: public");
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        if (strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE')) {
            Header("Content-Disposition: attachment; filename=\"".$companyName."\".doc",true);
        } else if (strpos($_SERVER["HTTP_USER_AGENT"], 'Firefox')) {
            Header("Content-Disposition: attachment; filename=\"".$companyName."\".doc",true);
        } else {
            Header("Content-Disposition: attachment; filename=\"".$companyName."\".doc",true);
        }
        Header("Pragma:no-cache");
        Header("Expires:0");
        ob_end_flush();
    }
}