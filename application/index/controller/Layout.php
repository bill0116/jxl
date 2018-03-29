<?php
/**
func:报表公共模板
 */
namespace app\index\controller;

use think\Db;

class Layout extends Common
{
	protected $beforeActionList = ['getAttachList'];

	protected function getAttachList(){
		$applicationid=input('get.applicationid');
		$this->assign('attachList',$this->getAttachFile($this->getAttachID($applicationid)));
	}

	//标准报告的模板
	public function common_baogao(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);

		//颜色的变化
		$this->color_baogao($applicationid,$option);

		$this->assign('type',100);
		return $this->fetch();
	}

	//深度报告的模板
	public function common_baogao2(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);


		//颜色的变化
		$this->color_baogao1($applicationid,$option);

		$this->assign('type',100);
		return $this->fetch();
	}

	//制作报告提交
	public function update(){
		$userid=session('auth_id');
		$applicationid=input('request.applicationid');
		$activeid=input('request.activeid');
		$info= Db::table('Z_TB_WTDApply')
				->where('id',$applicationid)
				->find();
		if($info['status']=='7'){
			$map['status']=8;//阶段
			$map['statusName']='翻译审核';//阶段
			$status = Db::table('Z_TB_WTDApply')
					->where('id',$applicationid)
					->update($map);
			if ($status !== false) {
				$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), senduserid={$userid} ,activeuserid={$userid},status={$map['status']},statusname='{$map['statusName']}' where activeid={$activeid}");
				return return_array_result('1', lang('提交成功'),url('Translate/get_list'));
			} else {
				return return_array_result('1', lang('提交失败'));
			}
		}else{
			$map['status']=6;//阶段
			$map['statusName']='审核';//阶段
			$status = Db::table('Z_TB_WTDApply')
					->where('id',$applicationid)
					->update($map);
			if ($status !== false) {
				$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), senduserid={$userid} ,activeuserid={$userid},status={$map['status']},statusname='{$map['statusName']}' where activeid={$activeid}");
				return return_array_result('1', lang('提交成功'),url('MakeBaogao/get_list'));
			} else {
				return return_array_result('1', lang('提交失败'));
			}
		}

	}

	//审核完成提交
	public function update1(){
		$userid=session('auth_id');
		$applicationid=input('request.applicationid');
		$activeid=input('request.activeid');
		$IsApproved=input('request.IsApproved');
		$info= Db::table('Z_TB_WTDApply')
				->where('id',$applicationid)
				->find();
		if($IsApproved=='拒绝'){
			if($info['status']=='6'){
				$map['status']=5;//阶段
				$map['statusName']='制作';//阶段
				$status = Db::table('Z_TB_WTDApply')
						->where('id',$applicationid)
						->update($map);
				if ($status !== false) {
					$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), senduserid={$userid} ,activeuserid={$userid},status={$map['status']},statusname='{$map['statusName']}'where activeid={$activeid}");
					return return_array_result('1', lang('提交成功'),url('ConfirmBaogao/get_list'));
				} else {
					return return_array_result('1', lang('提交失败'));
				}
			}else{
				$map['status']=7;//阶段
				$map['statusName']='翻译';//阶段
				$status = Db::table('Z_TB_WTDApply')
						->where('id',$applicationid)
						->update($map);
				if ($status !== false) {
					$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), senduserid={$userid} ,activeuserid={$userid},status={$map['status']},statusname='{$map['statusName']}' where activeid={$activeid}");
					return return_array_result('1', lang('提交成功'),url('ConfirmBaogao/get_list'));
				} else {
					return return_array_result('1', lang('提交失败'));
				}
			}
		}
		else{
			if($info['isTranslate']=='1'){
				$map['status']=10;//阶段
				$map['statusName']='已完成';//阶段
				$status = Db::table('Z_TB_WTDApply')
						->where('id',$applicationid)
						->update($map);
				if ($status !== false) {
					$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), senduserid={$userid} ,activeuserid={$userid},status={$map['status']},statusname='{$map['statusName']}' ,ActiveMessage='{$IsApproved}'where activeid={$activeid}");
					return return_array_result('1', lang('提交成功'),url('ConfirmBaogao/get_list'));
				} else {
					return return_array_result('1', lang('提交失败'));
				}
			}
			else{
				if($info['status']=='8'){
					$map['status']=10;//阶段
					$map['statusName']='已完成';//阶段
					$status = Db::table('Z_TB_WTDApply')
							->where('id',$applicationid)
							->update($map);
					if ($status !== false) {
						$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), senduserid={$userid} ,activeuserid={$userid},status={$map['status']},statusname='{$map['statusName']}' ,ActiveMessage='{$IsApproved}'where activeid={$activeid}");
						return return_array_result('1', lang('提交成功'),url('ConfirmTranslate/get_list'));
					} else {
						return return_array_result('1', lang('提交失败'));
					}
				}else{
					$map['status']=7;//阶段
					$map['statusName']='翻译';//阶段
					$status = Db::table('Z_TB_WTDApply')
							->where('id',$applicationid)
							->update($map);
					if ($status !== false) {
						$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), senduserid={$userid} ,activeuserid={$userid},status={$map['status']},statusname='{$map['statusName']}' ,ActiveMessage='{$IsApproved}'where activeid={$activeid}");
						return return_array_result('1', lang('提交成功'),url('ConfirmBaogao/get_list'));
					} else {
						return return_array_result('1', lang('提交失败'));
					}
				}

			}
		}


	}


}