<?php
/**
 * func:重要财务状况比率表(深度报告)
 */
namespace app\index\controller;

use think\Db;

class FinancialRateDetail1 extends Common
{
    protected $beforeActionList = ['getAttachList'];

    protected function getAttachList()
    {
        $applicationid = input('get.applicationid');
        $this->assign('attachList', $this->getAttachFile($this->getAttachID($applicationid)));
    }

    public function initialize()
    {
        parent::initialize();
        $this->model = model('flow');
        $this->table = Db::table('Z_TB_Financial_Rate');
    }


    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);


        //比率信息
        $data = $this->table->where('applicationid', $applicationid)->find();
        if ($data) {
            $year = substr($data['createdate'], 0, 4);
        } else {
            $year = null;
        }
        $this->assign('data', $data);
        $this->assign('year', $year);

        //颜色的变化
        $this->color_baogao1($applicationid,$option);

        $this->assign('type', 13);
        return $this->fetch();
    }

    //保存
    public function save()
    {
        $userid = session('auth_id');
        $applicationid = input('request.id');
        $activeid = input('request.activeid');

        $map['creator'] = $userid;//创建人
        $map['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $map['applicationid'] = input('request.id');//委托单id
        $map['activeid'] = input('request.activeid');//activeid

        //比率信息
        $map['debtRate'] = input('request.debtRate');//资产负债率
        $map['flowRate'] = input('request.flowRate');//流动比率
        $map['quickRate'] = input('request.quickRate');//速动比率
        $map['totalAssetTurnRate'] = input('request.totalAssetTurnRate');//总资产周转率
        $map['receiAccountTurnRate'] = input('request.receiAccountTurnRate');//应收账款周转率
        $map['flowAssetTurnRate'] = input('request.flowAssetTurnRate');//流动资产周转率
        $map['mainProfitRate'] = input('request.mainProfitRate');//主要业务利润率
        $map['netProfitRate'] = input('request.netProfitRate');//净资产收益率
        $map['costProfitRate'] = input('request.costProfitRate');//成本费用利用率
        $map['assetTurnDay'] = input('request.assetTurnDay');//资金周转天数
        $map['stockTurn'] = input('request.stockTurn');//存货周转率
        $map['DSODay'] = input('request.DSODay');//DSO
        $map['DPODay'] = input('request.DPODay');//DPO

        $map['debtRate1'] = input('request.debtRate1');//资产负债率
        $map['flowRate1'] = input('request.flowRate1');//流动比率
        $map['quickRate1'] = input('request.quickRate1');//速动比率
        $map['totalAssetTurnRate1'] = input('request.totalAssetTurnRate1');//总资产周转率
        $map['receiAccountTurnRate1'] = input('request.receiAccountTurnRate1');//应收账款周转率
        $map['flowAssetTurnRate1'] = input('request.flowAssetTurnRate1');//流动资产周转率
        $map['mainProfitRate1'] = input('request.mainProfitRate1');//主要业务利润率
        $map['netProfitRate1'] = input('request.netProfitRate1');//净资产收益率
        $map['costProfitRate1'] = input('request.costProfitRate1');//成本费用利用率
        $map['assetTurnDay1'] = input('request.assetTurnDay1');//资金周转天数
        $map['stockTurn1'] = input('request.stockTurn1');//存货周转率
        $map['DSODay1'] = input('request.DSODay1');//DSO
        $map['DPODay1'] = input('request.DPODay1');//DPO


        $map['propertyRate'] = input('request.propertyRate');//产权比率
        $map['assetTurnRate'] = input('request.assetTurnRate');//净资产周转率
        $map['salesGrossRate'] = input('request.salesGrossRate');//销售毛利率
        $map['salesNetRate'] = input('request.salesNetRate');//销售净利率
        $map['assetsNetRate'] = input('request.assetsNetRate');//资产净利率
        $map['assetsProfitRate'] = input('request.assetsProfitRate');//净资产收益率

        $map['propertyRate1'] = input('request.propertyRate1');//产权比率
        $map['assetTurnRate1'] = input('request.assetTurnRate1');//净资产周转率
        $map['salesGrossRate1'] = input('request.salesGrossRate1');//销售毛利率
        $map['salesNetRate1'] = input('request.salesNetRate1');//销售净利率
        $map['assetsNetRate1'] = input('request.assetsNetRate1');//资产净利率
        $map['assetsProfitRate1'] = input('request.assetsProfitRate1');//净资产收益率

        $this->table->where('applicationid', $applicationid)->delete();

        $id = $this->table->insertGetId($map);
        if ($id) {
            return return_array_result(1, lang('保存成功'));
        } else {
            return return_array_result(0, lang('保存失败'));
        }
    }

    //确认
    public function recheck()
    {
        $applicationid = input('request.applicationid');
        return $this->check($this->table, $applicationid);
    }

    //备注
    public function save_memo()
    {
        $applicationid = input('request.applicationid');
        $memo = input('request.confirmMemo');
        return $this->memo($this->table, $applicationid, $memo);
    }
}