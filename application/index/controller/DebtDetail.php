<?php
/**
 * func：负债表
 */
namespace app\index\controller;

use think\Db;

class DebtDetail extends Common
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
        $this->table = Db::table('Z_TB_financial_Debt');
    }




    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);


        //负债信息
        $data1 = Db::table('Z_TB_financial_Debt')->where('applicationid', $applicationid)->find();
        if (!$data1) {
            $this->assign('data', $data1);
        } else {
            $data = Db::table('Z_TB_financial_Debt')->where('applicationid', $applicationid)->order('year', 'desc')->select();
            $this->assign('data', $data);
        }

        //颜色的变化
        $this->color_baogao($applicationid,$option);

        $this->assign('type', 11);
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

            //负债信息
            $map['year'] = array($year - 1, $year - 2, $year - 3, $year - 4, $year - 5)[$i];//年份
            $map['short_termBorrowing'] = input('request.short_termBorrowing/a')[$i];//短期借款
            $map['payableBill'] = input('request.payableBill/a')[$i];//应付票据
            $map['payableAccount'] = input('request.payableAccount/a')[$i];//应收账款
            $map['advanceAccount'] = input('request.advanceAccount/a')[$i];//预收账款
            $map['payableWages'] = input('request.payableWages/a')[$i];//应付工资
            $map['payableWelfare'] = input('request.payableWelfare/a')[$i];//应付福利费
            $map['payableTax'] = input('request.payableTax/a')[$i];//应交税金
            $map['otherPayable'] = input('request.otherPayable/a')[$i];//其他应付款
            $map['advanceExpense'] = input('request.advanceExpense/a')[$i];//预提费用
            $map['otherDebt'] = input('request.otherDebt/a')[$i];//其他流动负债
            $map['totalDebtFlow'] = input('request.totalDebtFlow/a')[$i];//流动负债合计
            $map['long_termBorrowing'] = input('request.long_termBorrowing/a')[$i];//长期借款
            $map['totalLong_termDebt'] = input('request.totalLong_termDebt/a')[$i];//长期负债合计
            $map['totalDebt'] = input('request.totalDebt/a')[$i];//负债合计
            $map['capitalCollect'] = input('request.capitalCollect/a')[$i];//实收资本
            $map['capitalReserve'] = input('request.capitalReserve/a')[$i];//资本公积
            $map['surplusReserve'] = input('request.surplusReserve/a')[$i];//盈余公积
            $map['welfare'] = input('request.welfare/a')[$i];//公益金
            $map['undistributedprofit'] = input('request.undistributedprofit/a')[$i];//未分配利润
            $map['totalOwnerEquity'] = input('request.totalOwnerEquity/a')[$i];//所有者权益合计
            $map['total'] = input('request.total/a')[$i];//总计

            $id = Db::table('Z_TB_financial_Debt')->insertGetId($map);
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