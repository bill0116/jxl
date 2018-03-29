<?php
/**
func:报告类型确认
*/
namespace app\index\controller;

use think\Db;

class ConfirmBg extends Common
{
	/*
	 * @ 初始化initialize
	 * */
	public function initialize()
	{
		parent::initialize();
		$this->model = model('flow');
	}

	public function index()
	{
		$map = input('param.');
		$resultData = Db::table('Z_TB_WTDApply')->where('id', $map['applicationid'])->find();
		$activeid=$map['activeid'];
		$this->assign('activeid', $activeid);
		$this->assign('data', $resultData);
		return $this->fetch();
	}

	//保存
	public function save(){
		$userid=session('auth_id');
		$applicationid=input('request.id');
		$activeid=input('request.activeid');
		$resultData = Db::table('Z_TB_WTDApply')->where('id', $applicationid)->find();

		$map['speedType1']=input('request.speedType1');//确认报告
		$map['speedMemo']=input('request.speedMemo');//备注
		if($map['speedType1']!=$resultData['speedType']){
			if($map['speedType1']=='1'){
				if($resultData['normalDay']){
					$finishDate=$this->getFinish($resultData['entrustDate'],$resultData['normalDay']);
				}else{
					$finishDate=$this->getFinish($resultData['entrustDate'],6);
				}

			}
			else if($map['speedType1']=='2'){
				$finishDate=$this->getFinish($resultData['entrustDate'],5);
			}
			else{
				$finishDate=$this->getFinish($resultData['entrustDate'],3);
			}

			$map['finishDate']=$finishDate;//预计完成日期
		}

		$status = Db::table('Z_TB_WTDApply')
				->where('ID',$applicationid)
				->update($map);
		if ($status !== false) {
			$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate() where activeid={$activeid}");
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

			$map['speedType1']=input('request.speedType1');//确认报告
			$map['speedMemo']=input('request.speedMemo');//备注
			if($map['speedType1']!=$resultData['speedType']){
				if($map['speedType1']=='1'){
					if($resultData['normalDay']){
						$finishDate=$this->getFinish($resultData['entrustDate'],$resultData['normalDay']);
					}else{
						$finishDate=$this->getFinish($resultData['entrustDate'],6);
					}

				}
				else if($map['speedType1']=='2'){
					$finishDate=$this->getFinish($resultData['entrustDate'],5);
				}
				else{
					$finishDate=$this->getFinish($resultData['entrustDate'],3);
				}

				$map['finishDate']=$finishDate;//预计完成日期
			}

			$status = Db::table('Z_TB_WTDApply')
					->where('ID',$applicationid)
					->update($map);
			if ($status !== false) {
				$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(),  senduserid={$userid} ,activeuserid={$userid},statusIndex=3,statusname='确认有无旧报' where activeid={$activeid}");
				$this->success('提交成功', url('Confirm/get_list'), 1);
			} else {
				$this->error('提交失败');
			}
		}

	}

}