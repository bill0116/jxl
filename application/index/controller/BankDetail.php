<?php
/**
 * func:往来银行明细
 */
namespace app\index\controller;

use think\Db;

class BankDetail extends Common
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
        $this->table = Db::table('Z_TB_BankContact');
    }




    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);


        //往来银行信息
        $data = $this->table->where('applicationid', $applicationid)->find();
        $this->assign('data', $data);

        //颜色的变化
        $this->color_baogao($applicationid,$option);

        $this->assign('type', 8);
        $language = '英文';
        $this->assign('language', $language);
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

        //注册信息
        $map['bank'] = input('request.bank');
        $map['account'] = input('request.account');
        $map['currency'] = input('request.currency');
        $map['address'] = input('request.address');
        $map['tel'] = input('request.tel');
        $map['evaluate'] = input('request.evaluate');

        $map['mortgageDetail'] = input('request.mortgageDetail');//抵押担保情况

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

	//保存(翻译)
	public function saveEng(){
		$applicationid=input('request.id');

		//注册信息
		$map['bankEng']=input('request.bankEng');
		$map['accountEng']=input('request.accountEng');
		$map['currencyEng']=input('request.currencyEng');
		$map['addressEng']=input('request.addressEng');
		$map['telEng']=input('request.telEng');
		$map['evaluateEng']=input('request.evaluateEng');

		$map['mortgageDetailEng']=input('request.mortgageDetailEng');//抵押担保情况

		$ret=$this->table->where('applicationid',$applicationid)->update($map);
		if ($ret!==false) {
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
		}
	}

	//确认(翻译)
	public function recheckEng(){
		$applicationid=input('request.applicationid');;
		return  $this->checkEng($this->table,$applicationid);
	}

	//备注(翻译)
	public function save_memoEng(){
		$applicationid=input('request.applicationid');
		$memo=input('request.confirmMemoEng');
		return  $this->memoEng($this->table,$applicationid,$memo);
	}
}