<?php
/**
func:经营状况
 */
namespace app\index\controller;

use think\Db;
class RunInformation2 extends Common
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
		$this->table=Db::table('Z_TB_CompanyInformation_Run1');
	}

	public function index(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);

		//运营信息
		$data=$this->table->where('applicationid',$applicationid)->find();
		$this->assign('data',$data);

		//颜色的变化
		$this->color_baogao($applicationid,$option);

		$this->assign('type',7);
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

		//主要产品
		$map['mainProduct']=input('request.mainProduct');
		$map['productBrand']=input('request.productBrand');
		$map['salesVolume']=input('request.salesVolume');

		//采购情况
		$map['purchase_internal']=input('param.purchase_internal');
		$map['mainRegion']=input('param.mainRegion');
		$map['mainPay']=input('param.mainPay');
		$map['purchase_abroad']=input('param.purchase_abroad');
		$map['mainRegion_abroad']=input('param.mainRegion_abroad');
		$map['mainPay_abroad']=input('param.mainPay_abroad');
		$map['supplier']=input('param.supplier');
		$map['supplierPaySitua']=input('param.supplierPaySitua');

		if($map['purchase_internal']){
			if(($map['purchase_internal']+	$map['purchase_abroad'])!=100){
				$this->error('国内外采购占比相加一定为100%!');
			}
		}


		//销售情况
		$map['sales_internal']=input('param.sales_internal');
		$map['mainRegion1']=input('param.mainRegion1');
		$map['mainPay1']=input('param.mainPay1');
		$map['sales_abroad']=input('param.sales_abroad');
		$map['mainRegion_abroad1']=input('param.mainRegion_abroad1');
		$map['mainPay_abroad1']=input('param.mainPay_abroad1');
		$map['salesObject']=input('param.salesObject');
		$map['salesChannel']=input('param.salesChannel');
		$map['customer']=input('param.customer');
		$map['customerPaySitua']=input('param.customerPaySitua');

		if($map['sales_internal']){
			if(($map['sales_internal']+$map['sales_abroad'])!=100){
				$this->error('国内外销售占比相加一定为100%!');
			}
		}


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

		//主要产品
		$map['mainProductEng']=input('request.mainProductEng');
		$map['productBrandEng']=input('request.productBrandEng');
		$map['salesVolumeEng']=input('request.salesVolumeEng');

		//采购情况
		$map['purchase_internalEng']=input('param.purchase_internalEng');
		$map['mainRegionEng']=input('param.mainRegionEng');
		$map['mainPayEng']=input('param.mainPayEng');
		$map['purchase_abroadEng']=input('param.purchase_abroadEng');
		$map['mainRegion_abroadEng']=input('param.mainRegion_abroadEng');
		$map['mainPay_abroadEng']=input('param.mainPay_abroadEng');
		$map['supplierEng']=input('param.supplierEng');
		$map['supplierPaySituaEng']=input('param.supplierPaySituaEng');

		//销售情况
		$map['sales_internalEng']=input('param.sales_internalEng');
		$map['mainRegion1Eng']=input('param.mainRegion1Eng');
		$map['mainPay1Eng']=input('param.mainPay1Eng');
		$map['sales_abroadEng']=input('param.sales_abroadEng');
		$map['mainRegion_abroad1Eng']=input('param.mainRegion_abroad1Eng');
		$map['mainPay_abroad1Eng']=input('param.mainPay_abroad1Eng');
		$map['salesObjectEng']=input('param.salesObjectEng');
		$map['salesChannelEng']=input('param.salesChannelEng');
		$map['customerEng']=input('param.customerEng');
		$map['customerPaySituaEng']=input('param.customerPaySituaEng');

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
