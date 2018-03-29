<?php
/**
 * func:信用评级
 */
namespace app\index\controller;

use think\Db;

class CreditEvaluate extends Common
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
        $this->table = Db::table('Z_TB_CreditEvaluate');
    }




    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);


        //信用评级
        $data = $this->table->where('applicationid', $applicationid)->find();
        $this->assign('data', $data);

        //颜色的变化
        $this->color_baogao($applicationid,$option);

        $this->assign('type', 18);
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

        //信用评级
        $map['manage_factor1'] = input('request.manage_factor1');
        $map['manage_factor2'] = input('request.manage_factor2');
        $map['business_factor1'] = input('request.business_factor1');
        $map['business_factor2'] = input('request.business_factor2');
        $map['credit_factor1'] = input('request.credit_factor1');
        $map['credit_factor2'] = input('request.credit_factor2');
        $map['credit_factor3'] = input('request.credit_factor3');
        $map['financial_factor1'] = input('request.financial_factor1');
        $map['financial_factor2'] = input('request.financial_factor2');
        $map['financial_factor3'] = input('request.financial_factor3');
        $map['other_factor1'] = input('request.other_factor1');
        $map['other_factor2'] = input('request.other_factor2');
        $map['other_factor3'] = input('request.other_factor3');
        $map['other_factor4'] = input('request.other_factor4');
        $total=$map['manage_factor1']+$map['manage_factor2']+ $map['business_factor1']+$map['business_factor2']+
            $map['credit_factor1']+ $map['credit_factor2']+$map['credit_factor3']+
            $map['financial_factor1']+ $map['financial_factor2']+$map['financial_factor3']+
            $map['other_factor1']+ $map['other_factor2']+ $map['other_factor3']+ $map['other_factor4'];
        $map['total'] = $total;

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