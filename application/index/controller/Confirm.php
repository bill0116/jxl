<?php
/**
func:资信委托单确认
 */
namespace app\index\controller;

use think\Db;

class Confirm extends Common
{
	/*
	 * @ 初始化initialize
	 * */
	public function initialize()
	{
		parent::initialize();
		$this->model = model('flow');
	}

	//获取委托单
	public function get_list(){
		$userid=session('auth_id');
		$status='2';
		$data=$this->getWTD_list($userid,$status);
		$this->assign('data',$data);
		return $this->fetch();
	}

	public function index()
	{
		$map = input('param.');
		$resultData = Db::table('Z_TB_WTDApply')->where('id', $map['applicationid'])->find();
		$activeid=$map['activeid'];
		$this->assign('activeid', $activeid);
		$this->assign('data', $resultData);

		//确认是否有误的备注列表
		$trueApproveList= Db::table('S_TB_FC_ActiveComfirm_Detail')->where('activeid', $activeid)->select();
		$this->assign('trueApproveList', $trueApproveList);
		return $this->fetch();
	}

	//保存
	public function save(){
		$userid=session('auth_id');
		$applicationid=input('request.id');
		$activeid=input('request.activeid');

		$map['isTrue']=input('request.isTrue');//确认是否有误
		$map['trueMemo']=input('request.trueMemo');//备注

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
			$username=session('user_name');
			$applicationid=input('request.id');
			$activeid=input('request.activeid');

			$isTrue=input('request.isTrue');//确认是否有误
			$trueMemo=input('request.trueMemo');//备注

			if($isTrue==1){
				$info['ActiveID']=$activeid;
				$info['UserID']=$userid;
				$info['UserName']=$username;
				$info['SignDate']= date('Y-m-d H:i:s', time());
				$info['trueMemo']=input('request.trueMemo');
				$ret1 = Db::table('S_TB_FC_ActiveComfirm_Detail')->insert($info);
				$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(),  senduserid={$userid} ,activeuserid={$userid},status=1,statusIndex=0,statusname='报告有误' where activeid={$activeid}");
				$this->success('提交成功', url('Confirm/get_list'), 1);
			}
			else{
				$map['isTrue']=$isTrue;
				$map['trueMemo']=$trueMemo;

				$status = Db::table('Z_TB_WTDApply')
						->where('ID',$applicationid)
						->update($map);
				if ($status !== false) {
					$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(),  senduserid={$userid} ,activeuserid={$userid},statusIndex=2,statusname='确认报告类型' where activeid={$activeid}");
					$this->success('提交成功', url('Confirm/get_list'), 1);
				} else {
					$this->error('提交失败');
				}
			}

		}

	}

	//调查历史
	public function history(){
		$companyName=input('param.companyName');
		$status='10';
		$where="flowid=37  and status={$status} and keycol8='{$companyName}'";
		$list = Db::table('S_TB_FC_active')
				->field('* ,[dbo].[S_F_GetUserNameByUserID](senduserid) as sendusername ,[dbo].[S_F_GetUserNameByUserID](activeuserid) as activeusername')
				->where($where)
				->order('senddate','desc')
				->paginate(10,false,['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
		$data=$list->toArray()['data'];
		$page = $list->render();
		$this->assign('data',$data);
		$this->assign('page',$page);
		$this->assign('companyName',$companyName);
		return $this->fetch();
	}

	//查询
	public function search(){
		if($this->request->isPost()){
			$status='10';
			$where="flowid=37  and status={$status}";
			if(!empty(input('post.company'))){

				$where.="and keycol8 like'%".trim(input('post.company'))."%'";
			}
			$list = Db::table('S_TB_FC_active')
					->field('* ,[dbo].[S_F_GetUserNameByUserID](senduserid) as sendusername ,[dbo].[S_F_GetUserNameByUserID](activeuserid) as activeusername')
					->where($where)
					->order('senddate','desc')
					->paginate(10,false,['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
			if($list){
				$data=$list->toArray()['data'];
				$page=$list->render();
				return return_array_result(1,lang('查询成功'),'',['list'=>$data,'page'=> $page]);
			}
			else{
				return return_array_result(0,lang('查询失败'));
			}
		}
	}
}