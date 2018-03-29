<?php
/**
 * func:财务指标行业对比参照
 */
namespace app\index\controller;


use think\Db;

class FinancialComparison extends Common
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


        //行业对比参照
        $data = $this->table->where('applicationid', $applicationid)->find();
        $this->assign('data', $data);

        //颜色的变化
        $this->color_baogao($applicationid,$option);

        $this->assign('type', 14);
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

        //财务状况行业对比信息
        $map['debtRate'] = input('request.debtRate');
        $map['stockTurn'] = input('request.stockTurn');
        $map['quickRate'] = input('request.quickRate');
        $map['totalAssetTurnRate'] = input('request.totalAssetTurnRate');
        $map['receiAccountTurnRate'] = input('request.receiAccountTurnRate');
        $map['flowAssetTurnRate'] = input('request.flowAssetTurnRate');
        $map['mainProfitRate'] = input('request.mainProfitRate');
        $map['netProfitRate'] = input('request.netProfitRate');
        $map['costProfitRate'] = input('request.costProfitRate');


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