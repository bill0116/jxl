<?php
/**
func:摘要
 */
namespace app\index\controller;

use think\Db;
class Summany extends Common
{
	protected $beforeActionList = ['getAttachList'];

	protected function getAttachList(){
		$applicationid=input('get.applicationid');
		$this->assign('attachList',$this->getAttachFile($this->getAttachID($applicationid)));
	}
	/*
    * @ 初始化initialize
    * */
	public function initialize()
	{
		parent::initialize();
		$this->model = model('flow');
		$this->table=Db::table('Z_TB_Summany_information');
	}

	public function index(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);

		//委托单信息
		$wtd=Db::table('Z_TB_WTDApply')->where('id',$applicationid)->find();
		$this->assign('wtd',$wtd);

		//摘要信息
		$data=$this->table->where('applicationid',$applicationid)->find();
		$this->assign('data',$data);

		//颜色的变化
		$this->color_baogao($applicationid,$option);

		$this->assign('type',3);
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
		//委托信息
		$map['WTFName']=input('request.WTFName');//联系人
		$map['WTFAddress']=input('request.WTFAddress');//调查目的
		$map['surveyKey']=input('request.surveyKey');//地区

		//联络信息
		$map['name']=input('request.name');//名称
		$map['engName']=input('request.engName');//英文名称
		$map['address']=input('request.address');//地址
		$map['tel']=input('request.tel');//电话
		$map['fax']=input('request.fax');//传真
		$map['email']=input('request.email');//邮箱
		$map['website']=input('request.website');//联系人

		//报告摘要
		$map['creditGrade']=input('request.creditGrade');//信用等级
		$map['limit']=input('request.limit');//基准信用额度
		$map['DSO']=input('request.DSO');//DSO
		$map['DPO']=input('request.DPO');//DPO
		$map['establishDate']=input('request.establishDate');//成立日期
		$map['mainBusiness']=input('request.mainBusiness');//主营业务
		$map['salesRevenue']=input('request.salesRevenue');//销售收入
		$map['totalAssets']=input('request.totalAssets');//资产总计
		$map['netProfit']=input('request.netProfit');//净陆润

		$this->table->where('applicationid',$applicationid)->delete();

		$id = $this->table->insertGetId($map);
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

		//委托信息
		$map['WTFNameEng']=input('request.WTFNameEng');//联系人
		$map['WTFAddressEng']=input('request.WTFAddressEng');//调查目的
		$map['surveyKeyEng']=input('request.surveyKeyEng');//地区

		//联络信息
		$map['nameEng']=input('request.nameEng');//名称
		$map['engNameEng']=input('request.engNameEng');//英文名称
		$map['addressEng']=input('request.addressEng');//地址
		$map['telEng']=input('request.telEng');//电话
		$map['faxEng']=input('request.faxEng');//传真
		$map['emailEng']=input('request.emailEng');//邮箱
		$map['websiteEng']=input('request.websiteEng');//联系人

		//报告摘要
		$map['creditGradeEng']=input('request.creditGradeEng');//信用等级
		$map['limitEng']=input('request.limitEng');//基准信用额度
		$map['DSOEng']=input('request.DSOEng');//DSO
		$map['DPOEng']=input('request.DPOEng');//DPO
		$map['establishDateEng']=input('request.establishDateEng');//成立日期
		$map['mainBusinessEng']=input('request.mainBusinessEng');//主营业务
		$map['salesRevenueEng']=input('request.salesRevenueEng');//销售收入
		$map['totalAssetsEng']=input('request.totalAssetsEng');//资产总计
		$map['netProfitEng']=input('request.netProfitEng');//净陆润


		$ret = $this->table->where('applicationid',$applicationid)->update($map);
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