<?php
/**
 * func:损益表(深度报告)
 */
namespace app\index\controller;

use think\Db;

class SunyiDetail1 extends Common
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
        $this->table = Db::table('Z_TB_Financial_Sunyi');
    }

    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);


		//颜色的变化
		$this->color_baogao1($applicationid,$option);

        //损益信息
        $data1 = $this->table->where('applicationid', $applicationid)->find();
        if (!$data1) {
            $this->assign('data', $data1);
        } else {
            $data = Db::table('Z_TB_Financial_Sunyi')->where('applicationid', $applicationid)->order('year', 'desc')->select();
            $this->assign('data', $data);
        }

        $this->assign('type', 12);
        return $this->fetch();
    }

    //保存
    public function save()
    {
        $userid = session('auth_id');
        $applicationid = input('request.id');
        $activeid = input('request.activeid');
        $year = date('Y', time());//当前年

        $this->table->where('applicationid', $applicationid)->delete();

        for ($i = 0; $i < 5; $i++) {
            $map['creator'] = $userid;//创建人
            $map['createdate'] = date('Y-m-d H:i:s', time());//创建时间
            $map['applicationid'] = input('request.id');//委托单id
            $map['activeid'] = input('request.activeid');//activeid

            //注册信息
            $map['year'] = array($year - 1, $year - 2, $year - 3, $year - 4, $year - 5)[$i];//年份
            //$map['salesIncome']=input('request.salesIncome/a')[$i];//销售收入
            $map['mainIncome'] = input('request.mainIncome/a')[$i];//主营业务收入
            //$map['otherIncome']=input('request.otherIncome/a')[$i];//其他业务收入
            //$map['cost']=input('request.cost/a')[$i];//业务成本
            $map['mainCost'] = input('request.mainCost/a')[$i];//主营业务成本
            //$map['otherCost']=input('request.otherCost/a')[$i];//其他业务成本
            $map['tax_addition'] = input('request.tax_addition/a')[$i];//税金及附加
            //$map['prpfit_YW']=input('request.prpfit_YW/a')[$i];//业务利润
            $map['business'] = input('request.business/a')[$i];//营业费用
            $map['manage'] = input('request.manage/a')[$i];//管理费用
            $map['financial'] = input('request.financial/a')[$i];//财务费用
            $map['profit_YY'] = input('request.profit_YY/a')[$i];//营业利润
            $map['investIncome'] = input('request.investIncome/a')[$i];//投资收益
            $map['otherBusinessIn'] = input('request.otherBusinessIn/a')[$i];//营业外收入
            $map['otherBusinessOut'] = input('request.otherBusinessOut/a')[$i];//营业外支出
            $map['totalProfit'] = input('request.totalProfit/a')[$i];//利润总额
            $map['incomeTax'] = input('request.incomeTax/a')[$i];//所得税
            $map['profit_SH'] = input('request.profit_SH/a')[$i];//税后利润

            $map['mainPrpfit_YW'] = input('request.mainPrpfit_YW/a')[$i];//主营业务利润
            $map['otherPrpfit_YW'] = input('request.otherPrpfit_YW/a')[$i];//其他业务利润
            $map['subsidyIncome'] = input('request.subsidyIncome/a')[$i];//补贴收入

            $id = Db::table('Z_TB_Financial_Sunyi')->insertGetId($map);
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