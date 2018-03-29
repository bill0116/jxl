<?php
/**
func:分配报告给制作人
 */
namespace app\index\controller;

use think\Db;
class DivideBaogao extends Common
{
	public function initialize()
	{
		parent::initialize();
		$this->model = model('flow');
		$this->table=Db::table('Z_TB_DiaoDang_Detail');
	}

	public function get_list(){
		$userid=session('auth_id');
		$status='4';
		$data=$this->getWTD_list($userid,$status);
		$this->assign('data',$data);
		return $this->fetch();
	}

	public function detail(){
		$map = input('param.');
		$resultData = Db::table('Z_TB_WTDApply')->where('id', $map['applicationid'])->find();
		$applicationid=$map['applicationid'];
		$activeid=$map['activeid'];

		//调档明细
		$list=$this->table
				->alias('a')
				->field('a.*,b.name')
				->join('Z_TB_Channel_DiaoDang b', 'a.company=b.code', 'left')
				->where('applicationid='.$applicationid)
				->order('id','desc')
				->select();
		$this->assign('list',$list);

		$this->assign('applicationid',$applicationid);
		$this->assign('activeid', $activeid);
		$this->assign('data', $resultData);
		return $this->fetch();
	}

	public function update(){
		if(request()->isPost()){
			$userid=session('auth_id');
			$applicationid=input('request.id');
			$activeid=input('request.activeid');
			$map['status']=5;//阶段
			$map['statusName']='制作';//阶段

			$maker=input('request.maker');//报告制作人

			$status = Db::table('Z_TB_WTDApply')
					->where('ID',$applicationid)
					->update($map);
			if ($status !== false) {
				$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), senduserid={$userid} ,activeuserid={$maker},status=5,statusname='制作' where activeid={$activeid}");
				$this->success('提交成功', url('DivideBaogao/get_list'), 1);
			} else {
				$this->error('提交失败');
			}
		}
	}

}