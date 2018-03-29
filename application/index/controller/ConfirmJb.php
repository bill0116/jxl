<?php
/**
func:确认有无旧报
 */
namespace app\index\controller;

use think\Db;

class ConfirmJb extends Common
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

		//旧报
		$companyName=$resultData['companyName'];
		$status='10';
		$hitory_list = Db::query("select * ,[dbo].[S_F_GetUserNameByUserID](senduserid) as sendusername ,[dbo].[S_F_GetUserNameByUserID](activeuserid) as activeusername from S_TB_FC_active
                                where flowid=37  and status={$status} and keycol8='{$companyName}'
                                order by senddate desc");
		$this->assign('hitory_list',$hitory_list);

		return $this->fetch();
	}

	//保存
	public function save(){
		$userid=session('auth_id');
		$applicationid=input('request.id');
		$activeid=input('request.activeid');


		$map['isOldData']=input('request.isOldData');//确认有误旧资料
		$map['isOldReport']=input('request.isOldReport');//确认有无旧报
		$map['isSameCompany']=input('request.isSameCompany');//是否同意客户
		$map['oldReportDate']=input('request.oldReportDate');//旧报时间
		$map['isSameRequire']=input('request.isSameRequire');//需求是否一致

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

			$map['isOldData']=input('request.isOldData');//确认有误旧资料
			$map['isOldReport']=input('request.isOldReport');//确认有无旧报
			$map['isSameCompany']=input('request.isSameCompany');//是否同意客户
			$map['oldReportDate']=input('request.oldReportDate');//旧报时间
			$map['isSameRequire']=input('request.isSameRequire');//需求是否一致

			$map['status']=3;//阶段
			$map['statusName']='调档';//阶段

			$status = Db::table('Z_TB_WTDApply')
					->where('ID',$applicationid)
					->update($map);
			if ($status !== false) {
				$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(),  senduserid={$userid} ,activeuserid={$userid},status=3,statusname='调档' where activeid={$activeid}");
				$this->success('提交成功', url('Confirm/get_list'), 1);
			} else {
				$this->error('提交失败');
			}
		}

	}


}