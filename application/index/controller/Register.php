<?php
/**
func:注册信息
 */
namespace app\index\controller;

use think\Db;
class Register extends Common
{
	protected $beforeActionList = ['getAttachList'];

	protected function getAttachList(){
		$applicationid=input('get.applicationid');
		$this->assign('attachList',$this->getAttachFile($this->getAttachID($applicationid)));
	}

	public function initialize()
	{
		parent::initialize();
		$this->model = model('flow');
		$this->table=Db::table('Z_TB_company_information1');
	}

	public function index(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);

		//注册信息
		$data=$this->table->where('applicationid',$applicationid)->find();
		$this->assign('data',$data);

		//颜色的变化
		$this->color_baogao1($applicationid,$option);

		$this->assign('type',4);
		$language='英文';
		$this->assign('language',$language);
		return $this->fetch();
	}

	//保存
	public function save(){
		$userid=session('auth_id');
		$applicationid=input('request.id');
		$activeid=input('request.activeid');

		$map['creator']=$userid;//创建人
		$map['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$map['applicationid']=input('request.id');//委托单id
		$map['activeid']=input('request.activeid');//activeid

		//注册信息
		$map['name']=input('request.name');//名称
		$map['address']=input('request.address');//地址
		$map['registerDate']=input('request.registerDate');//注册登记日期
		$map['registerCapital']=input('request.registerCapital');//注册资本
		$map['registerOffice']=input('request.registerOffice');//登记机关
		$map['companyType']=input('request.companyType');//企业类型
		$map['representative']=input('request.representative');//法定代表人
		$map['business']=input('request.business');//经营范围
		$map['businessTerm']=input('request.businessTerm');//经营期限
		$map['organization']=input('request.organization');//工商注册号

		//进出口经营权
		$map['import_export_power']=input('request.import_export_power');//进出口经营权
		$map['import_export_start']=input('request.import_export_start');//获得时间
		$map['agent']=input('request.agent');//代理机构
		$map['certificateCode']=input('request.certificateCode');//企业海关编码

		//公共记录
		$map['publicDetail']=input('request.publicDetail');

		//商标
		$map['brand']=input('request.brand');

		$this->table->where('applicationid',$applicationid)->delete();

		$id =$this->table->insertGetId($map);
		if ($id) {
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
		}
	}

	//确认
	public function recheck(){
		$applicationid=input('request.applicationid');;
		return  $this->check($this->table,$applicationid);
	}

	//备注
	public function save_memo(){
		$applicationid=input('request.applicationid');
		$memo=input('request.confirmMemo');
		return  $this->memo($this->table,$applicationid,$memo);
	}

	//保存(翻译)
	public function saveEng(){
		$applicationid=input('request.id');

		//注册信息
		$map['nameEng']=input('request.nameEng');//名称
		$map['addressEng']=input('request.addressEng');//地址
		$map['registerDateEng']=input('request.registerDateEng');//注册登记日期
		$map['registerCapitalEng']=input('request.registerCapitalEng');//注册资本
		$map['registerOfficeEng']=input('request.registerOfficeEng');//登记机关
		$map['companyTypeEng']=input('request.companyTypeEng');//企业类型
		$map['representativeEng']=input('request.representativeEng');//法定代表人
		$map['businessEng']=input('request.businessEng');//经营范围
		$map['businessTermEng']=input('request.businessTermEng');//经营期限
		$map['organizationEng']=input('request.organizationEng');//工商注册号

		//进出口经营权
		$map['import_export_powerEng']=input('request.import_export_powerEng');//进出口经营权
		$map['import_export_startEng']=input('request.import_export_startEng');//获得时间
		$map['agentEng']=input('request.agentEng');//代理机构
		$map['certificateCodeEng']=input('request.certificateCodeEng');//企业海关编码

		//公共记录
		$map['publicDetailEng']=input('request.publicDetailEng');

		//商标
		$map['brandEng']=input('request.brandEng');

		$ret=$this->table->where('applicationid',$applicationid)->update($map);
		if ($ret) {
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