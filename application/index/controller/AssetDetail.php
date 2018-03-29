<?php
/**
 * func:资产表
 */
namespace app\index\controller;

use think\Db;

class AssetDetail extends Common
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
        $this->table = Db::table('Z_TB_financial_assets');
    }

    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);

        //资产情况
        $data1 = Db::table('Z_TB_financial_assets')->where('applicationid', $applicationid)->find();
        if (!$data1) {
            $this->assign('data', $data1);
        } else {
            $data = Db::table('Z_TB_financial_assets')->where('applicationid', $applicationid)->order('year', 'desc')->select();
            $this->assign('data', $data);
        }

        //颜色的变化
        $this->color_baogao($applicationid,$option);

        $this->assign('type', 10);
        return $this->fetch();
    }

    //保存
    public function save()
    {
        $userid = session('auth_id');
        $applicationid = input('request.id');
        $activeid = input('request.activeid');
        $year = date('Y', time());//当前年份

        $this->table->where('applicationid', $applicationid)->delete();

        for ($i = 0; $i < 5; $i++) {
            $map['creator'] = $userid;//创建人
            $map['createdate'] = date('Y-m-d H:i:s', time());//创建时间
            $map['applicationid'] = input('request.id');//委托单id
            $map['activeid'] = input('request.activeid');//activeid

            //资产表
            $map['year'] = array($year - 1, $year - 2, $year - 3, $year - 4, $year - 5)[$i];//年份
            $map['currencyFunds'] = input('request.currencyFunds/a')[$i];//货币资金
            $map['receivableBill'] = input('request.receivableBill/a')[$i];//应收票据
            $map['receivableAccount'] = input('request.receivableAccount/a')[$i];//应收账款
            $map['prepayAccount'] = input('request.prepayAccount/a')[$i];//预付账款
            $map['otherReceivableAccount'] = input('request.otherReceivableAccount/a')[$i];//其他应收账款
            $map['stock'] = input('request.stock/a')[$i];//存货
            $map['apportionCost'] = input('request.apportionCost/a')[$i];//待摊费用
            $map['otherCurrentAssets'] = input('request.otherCurrentAssets/a')[$i];//其他流动资产
            $map['totalCurrentAssets'] = input('request.totalCurrentAssets/a')[$i];//流动资产合计
            $map['long_termInvestment'] = input('request.long_termInvestment/a')[$i];//长期投资
            $map['totalLong_termInvestment'] = input('request.totalLong_termInvestment/a')[$i];//长期投资合计
            $map['fixedAssetsOriginial'] = input('request.fixedAssetsOriginial/a')[$i];//固定资产原值
            $map['cumulativeDiscounts'] = input('request.cumulativeDiscounts/a')[$i];//累积折扣
            $map['fixedAssetsNet'] = input('request.fixedAssetsNet/a')[$i];//固定资产净值
            $map['constructionProject'] = input('request.constructionProject/a')[$i];//在建工程
            $map['totalFixedAssets'] = input('request.totalFixedAssets/a')[$i];//固定资产合计
            $map['deferAssets'] = input('request.deferAssets/a')[$i];//递延资产
            $map['intangibleAssets'] = input('request.intangibleAssets/a')[$i];//无形资产
            $map['totalDeferIntangible'] = input('request.totalDeferIntangible/a')[$i];//合计递延无形资产
            $map['totalAssets'] = input('request.totalAssets/a')[$i];//资产总计

            $id = Db::table('Z_TB_financial_assets')->insertGetId($map);
        }

        if ($id) {
            return return_array_result(1, lang('保存成功'));
        } else {
            return return_array_result(0, lang('保存失败'));
        }
    }

    //确认
    public function recheck()
    {
        $applicationid = input('request.applicationid');;
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