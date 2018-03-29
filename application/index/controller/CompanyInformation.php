<?php
/**
 * func:企业概况
 */
namespace app\index\controller;

use think\Db;

class CompanyInformation extends Common
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
        $this->table = Db::table('Z_TB_company_information1');
    }

    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);

        //注册信息
        $data = $this->table->where('applicationid', $applicationid)->find();
        $this->assign('data', $data);


        //颜色的变化
        $this->color_baogao($applicationid,$option);

        $this->assign('type', 4);
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
        $map['name'] = input('request.name');//名称
        $map['creditCode'] = input('request.creditCode');//信用代码
        $map['address'] = input('request.address');//地址
        $map['registerDate'] = input('request.registerDate');//注册登记日期
        $map['registerCapital'] = input('request.registerCapital');//注册资本
        $map['registerOffice'] = input('request.registerOffice');//登记机关
        $map['companyType'] = input('request.companyType');//企业类型
        $map['representative'] = input('request.representative');//法定代表人
        $map['business'] = input('request.business');//经营范围
        $map['businessTerm'] = input('request.businessTerm');//经营期限
        $map['registerTel'] = input('request.registerTel');//登记电话
        $map['industryCode'] = input('request.industryCode');//行业代码
        $map['organization'] = input('request.organization');//组织机构证号
        $map['import_export_power'] = input('request.import_export_power');//进出口权
        $map['yearCheck'] = input('request.yearCheck');//年检情况


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
		$map['nameEng']=input('request.nameEng');//名称
		$map['creditCodeEng']=input('request.creditCodeEng');//信用代码
		$map['addressEng']=input('request.addressEng');//地址
		$map['registerDateEng']=input('request.registerDateEng');//注册登记日期
		$map['registerCapitalEng']=input('request.registerCapitalEng');//注册资本
		$map['registerOfficeEng']=input('request.registerOfficeEng');//登记机关
		$map['companyTypeEng']=input('request.companyTypeEng');//企业类型
		$map['representativeEng']=input('request.representativeEng');//法定代表人
		$map['businessEng']=input('request.businessEng');//经营范围
		$map['businessTermEng']=input('request.businessTermEng');//经营期限
		$map['registerTelEng']=input('request.registerTelEng');//登记电话
		$map['industryCodeEng']=input('request.industryCodeEng');//行业代码
		$map['organizationEng']=input('request.organizationEng');//组织机构证号
		$map['import_export_powerEng']=input('request.import_export_powerEng');//进出口权
		$map['yearCheckEng']=input('request.yearCheckEng');//年检情况


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