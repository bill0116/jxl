<?php
/**
func:委托单申请
 */
namespace app\index\controller;

use think\Db;
class WtdApply extends Common
{
	function initialize()
	{
		parent::initialize();
		$this->model = model('flow');

	}

	//新建委托单
	public function add(){
		return $this->fetch();
	}

    //获取委托单
	public function get_list(){
		$userid=session('auth_id');
		$status='1';
        $data=$this->getWTD_list($userid,$status);
		$this->assign('data',$data);
		return $this->fetch();
	}

	//委托单详细页
	public function index()
	{
		$userid=session('auth_id');
		$userName=session('user_name');
		if(request()->isPost()){
			$map=input('post.');
			$param['kunnr']=$map['buyerNo'];//公司代码
			$param['name']=$map['buyerName'];//公司名称
			$param['LXR']=$map['buyerPerson'];//联系人
			$param['LXRPhone']=$map['buyerTel'];//电话
			$param['entrustor']=$map['entrustor'];//委托人
			$param['type']=$map['type'];//报告种类
			$param['specicalRequire']=$map['specicalRequire'];//特殊需求
			$param['normalDay']=$map['finishDay'];//普通报告完成天数
			if($param['type']=='1'){
				$param['typeName']='标准报告';
			}
			else if($param['type']=='2'){
				$param['typeName']='深度报告';
			}
			$param['creator'] = $userid;
			$param['creatorName'] =$userName;
			$param['createDate'] = date('Y-m-d H:i:s', time());
			$hour=date('H', time());
			if($hour>12){
				$param['entrustDate'] = date('Y-m-d',strtotime('+1 day'));
			}else{
				$param['entrustDate'] = date('Y-m-d', time());
			}

			$id = Db::table('Z_TB_WTDApply')->insertGetId($param);
			if($id){
				$activeid = $this->model->newActive(37, $id, $param['creator'],$param['type']);
				$resultData = Db::table('Z_TB_WTDApply')->where('id=' . $id)->find();
				$this->assign('data', $resultData);
				$this->assign('activeid', $activeid);
			}
		}
		else {
			$map = input('param.');
			$resultData = Db::table('Z_TB_WTDApply')->where('id', $map['applicationid'])->find();
			$activeid=$map['activeid'];
			$this->assign('activeid', $activeid);
			$this->assign('data', $resultData);

			//确认是否有误的备注列表
			$trueApproveList= Db::table('S_TB_FC_ActiveComfirm_Detail')->where('activeid', $activeid)->select();
			$this->assign('trueApproveList', $trueApproveList);
		}
		return $this->fetch();
	}

   //保存
	public function save(){
		$userid=session('auth_id');
		$applicationid=input('request.id');
		$activeid=input('request.activeid');
		$resultData = Db::table('Z_TB_WTDApply')->where('id', $applicationid)->find();

		$map['companyName']=input('request.companyName');//目标公司名称
		$map['engName']=input('request.engName');//英文名称
		$map['address']=input('request.address');//地址
		$map['tel']=input('request.tel');//电话
		$map['fax']=input('request.fax');//传真
		$map['FZR']=input('request.FZR');//负责人
		$map['companyLXR']=input('request.companyLXR');//联系人
		$map['demand']=input('request.demand');//调查目的
		$map['area']=input('request.area');//地区
		$map['speedType']=input('request.speedType');//报告情况
		$map['payType']=input('request.payType');//付款方式
		$map['payMemo']=input('request.payMemo');//说明
		$map['isTranslate']=input('request.isTranslate');//是否翻译
		$map['translateType']=input('request.translateType');//法医类型
		$map['translateMemo']=input('request.translateMemo');//说明
		$map['entrustDate']=empty(!input('request.entrustDate'))?input('request.entrustDate'):null;//委托日期
		if($map['entrustDate'] && $map['speedType']){
			if($map['speedType']=='1'){
				if($resultData['normalDay']){
					$finishDate=$this->getFinish($map['entrustDate'],$resultData['normalDay']);
				}else{
					$finishDate=$this->getFinish($map['entrustDate'],6);
				}

			}
			else if($map['speedType']=='2'){
				$finishDate=$this->getFinish($map['entrustDate'],5);
			}
			else{
				$finishDate=$this->getFinish($map['entrustDate'],3);
			}

			$map['finishDate']=$finishDate;//预计完成日期
		}
		$map['price']=input('request.price');//单价
		$map['department']=input('request.department');//接单部门

		//委托单编号
		if(input('post.wtdCode')!=''){
			$map['wtdCode']=input('post.wtdCode');
		}else{
			$yearmonth=date('Ymd');
			//$var=sprintf('%04d',$applicationid);
			$var=$applicationid;
			$map['wtdCode']='WTD'."$yearmonth$var";
		}
		$status = Db::table('Z_TB_WTDApply')
				->where('ID',$applicationid)
				->update($map);
		if ($status !== false) {
			$resultData = Db::table('Z_TB_WTDApply')->where('id=' . $applicationid)->find();
			$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), keycol4='{$resultData['kunnr']}',keycol5='{$resultData['name']}',keycol6='{$map['wtdCode']}',keycol8='{$map['companyName']}', activeuserid={$userid} where activeid={$activeid}");
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
		}

	}

		//提交
	public function update()
	{
		if(request()->isPost()){
			$userid=session('auth_id');
			$applicationid=input('request.id');
			$activeid=input('request.activeid');
			$resultData = Db::table('Z_TB_WTDApply')->where('id', $applicationid)->find();

			$map['companyName']=input('request.companyName');//目标公司名称
			$map['engName']=input('request.engName');//英文名称
			$map['address']=input('request.address');//地址
			$map['tel']=input('request.tel');//电话
			$map['fax']=input('request.fax');//传真
			$map['FZR']=input('request.FZR');//负责人
			$map['companyLXR']=input('request.companyLXR');//联系人
			$map['demand']=input('request.demand');//调查目的
			$map['area']=input('request.area');//地区
			$map['speedType']=input('request.speedType');//报告类型
			$map['payType']=input('request.payType');//付款方式
			$map['payMemo']=input('request.payMemo');//说明
			$map['isTranslate']=input('request.isTranslate');//是否翻译
			$map['translateType']=input('request.translateType');//法医类型
			$map['translateMemo']=input('request.translateMemo');//说明
			$map['entrustDate']=empty(!input('request.entrustDate'))?input('request.entrustDate'):null;//委托日期
			if($map['entrustDate'] && $map['speedType']){
				if($map['speedType']=='1'){
					if($resultData['normalDay']){
						$finishDate=$this->getFinish($map['entrustDate'],$resultData['normalDay']);
					}else{
						$finishDate=$this->getFinish($map['entrustDate'],6);
					}

				}
				else if($map['speedType']=='2'){
					$finishDate=$this->getFinish($map['entrustDate'],5);
				}
				else{
					$finishDate=$this->getFinish($map['entrustDate'],3);
				}

				$map['finishDate']=$finishDate;//预计完成日期
			}
			//$map['finishDate']=empty(!input('request.finishDate'))?input('request.finishDate'):null;//预计完成日期
			$map['price']=input('request.price');//单价
			$map['department']=input('request.department');//接单部门

			$map['status']=2;//阶段
			$map['statusName']='确认';//阶段

			//委托单编号
			if(input('post.wtdCode')!=''){
				$map['wtdCode']=input('post.wtdCode');
			}else{
				$yearmonth=date('Ymd');
				//$var=sprintf('%04d',$applicationid);
				$var=$applicationid;
				$map['wtdCode']='WTD'."$yearmonth$var";
			}
			$status = Db::table('Z_TB_WTDApply')
					->where('ID',$applicationid)
					->update($map);
			if ($status !== false) {
				$resultData = Db::table('Z_TB_WTDApply')->where('id=' . $applicationid)->find();
				$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), keycol4='{$resultData['kunnr']}',keycol5='{$resultData['name']}',keycol6='{$map['wtdCode']}',keycol7='{$map['translateType']}',keycol8='{$map['companyName']}',senduserid={$userid} ,activeuserid={$userid},status=2,statusIndex=1,statusname='确认是否有误' where activeid={$activeid}");
				$this->success('申请成功', url('WtdApply/get_list'), 1);
			} else {
				$this->error('申请失败');
			}
		}

	}



}