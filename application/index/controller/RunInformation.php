<?php
/**
func:经营状况
 */
namespace app\index\controller;

use think\Db;
class RunInformation extends Common
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
		$this->table=Db::table('Z_TB_CompanyInformation_Run');
	}

	public function index(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);

		//经营状况信息
		$data=$this->table->where('applicationid',$applicationid)->find();
		$this->assign('data',$data);

		//颜色的变化
		$this->color_baogao($applicationid,$option);

		$this->assign('type',6);
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

		//经营者简介
		$map['operator']=input('request.operator');
		$map['sex']=input('request.sex');
		$map['age']=input('request.age');
		$map['certificate']=input('request.certificate');
		$map['education']=input('request.education');
		$map['position']=input('request.position');
		$map['house']=input('request.house');
		$map['duty']=input('request.duty');
		$map['industryExperience']=input('request.industryExperience');
		$map['manageExperience']=input('request.manageExperience');
		$map['promotion']=input('request.promotion');
		$map['badRecord']=input('request.badRecord');
		$map['experience']=input('request.experience');

		//人员状况
		$map['totalNum']=input('param.totalNum');
			if(!is_numeric($map['totalNum']) && $map['totalNum']){
				$this->error('员工总数必须是数字');
			}

		$map['manageNum']=input('param.manageNum');
		if(!is_numeric($map['manageNum'])  && $map['manageNum']){
			$this->error('管理人员必须是数字');
		}

		$map['technologyNum']=input('param.technologyNum');
		if(!is_numeric($map['technologyNum'])  && $map['technologyNum']){
			$this->error('技术人员必须是数字');
		}

		$map['otherNum']=input('param.otherNum');
		if(!is_numeric($map['otherNum'])  && $map['otherNum']){
			$this->error('其他人员必须是数字');
		}
		if($map['totalNum']!=($map['manageNum']+$map['technologyNum']+$map['otherNum'])){
			$this->error('总人数不对');
		}

		//经营场所
		$map['address']=input('param.address');
		$map['location']=input('param.location');
		$map['area']=input('param.area');
		$map['property']=input('param.property');
		$map['condition']=input('param.condition');

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

		//经营者简介
		$map['operatorEng']=input('request.operatorEng');
		$map['sexEng']=input('request.sexEng');
		$map['ageEng']=input('request.ageEng');
		$map['certificateEng']=input('request.certificateEng');
		$map['educationEng']=input('request.educationEng');
		$map['positionEng']=input('request.positionEng');
		$map['houseEng']=input('request.houseEng');
		$map['dutyEng']=input('request.dutyEng');
		$map['industryExperienceEng']=input('request.industryExperienceEng');
		$map['manageExperienceEng']=input('request.manageExperienceEng');
		$map['promotionEng']=input('request.promotionEng');
		$map['badRecordEng']=input('request.badRecordEng');
		$map['experienceEng']=input('request.experienceEng');

		//人员状况
		$map['totalNumEng']=input('param.totalNumEng');
		$map['manageNumEng']=input('param.manageNumEng');
		$map['technologyNumEng']=input('param.technologyNumEng');
		$map['otherNumEng']=input('param.otherNumEng');

		//经营场所
		$map['addressEng']=input('param.addressEng');
		$map['locationEng']=input('param.locationEng');
		$map['areaEng']=input('param.areaEng');
		$map['propertyEng']=input('param.propertyEng');
		$map['conditionEng']=input('param.conditionEng');

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