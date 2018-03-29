<?php
/**
func:摘要
 */
namespace app\index\controller;

use think\Db;
class Summany2 extends Common
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
		$this->table=Db::table('Z_TB_Summany2_information');
	}

	public function index(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);

		//摘要信息
		$data=$this->table->where('applicationid',$applicationid)->find();
		$this->assign('data',$data);

		//颜色的变化
		$this->color_baogao1($applicationid,$option);

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

		//公司概要
		$map['creditGrade']=input('request.creditGrade');//信用等级
		$map['establishDate']=input('request.establishDate');
		$map['registerCapital']=input('request.registerCapital');
		$map['businessType']=input('request.businessType');
		$map['taxCode']=input('request.taxCode');
		$map['organization']=input('request.organization');
		$map['mainBusiness']=input('request.mainBusiness');
		$map['scale']=input('request.scale');
		$map['staff']=input('request.staff');
		$map['sales']=input('request.sales');
		$map['assets']=input('request.assets');
		$map['prospect']=input('request.prospect');
		$map['payRecord']=input('request.payRecord');

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

		//公司概要
		$map['creditGradeEng']=input('request.creditGradeEng');//信用等级
		$map['establishDateEng']=input('request.establishDateEng');
		$map['registerCapitalEng']=input('request.registerCapitalEng');
		$map['businessTypeEng']=input('request.businessTypeEng');
		$map['taxCodeEng']=input('request.taxCodeEng');
		$map['organizationEng']=input('request.organizationEng');
		$map['mainBusinessEng']=input('request.mainBusinessEng');
		$map['scaleEng']=input('request.scaleEng');
		$map['staffEng']=input('request.staffEng');
		$map['salesEng']=input('request.salesEng');
		$map['assetsEng']=input('request.assetsEng');
		$map['prospectEng']=input('request.prospectEng');
		$map['payRecordEng']=input('request.payRecordEng');

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