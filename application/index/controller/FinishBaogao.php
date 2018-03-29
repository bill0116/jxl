<?php
/**
func:已完成报告
 */
namespace app\index\controller;

use think\Db;
class FinishBaogao extends Common
{
	public function get_list(){
		$userid=session('auth_id');
		$status='10';
		$data=$this->getWTD_list($userid,$status);
		$this->assign('data',$data);
		return $this->fetch();
	}


	public function search()
	{
		if ($this->request->isPost()) {
			$where = "flowid=37  and status=10";
			if (!empty(input('post.companyName')))
				$where .= " and KeyCol8 ='" . trim(input('post.companyName')) . "'";
			if (!empty(input('post.wtdCode')))
				$where .= " and KeyCol6 ='" . trim(input('post.wtdCode')) . "'";
			if (!empty(input('post.kunnr')))
				$where .= " and KeyCol4 like '%" . trim(input('post.kunnr')) . "%'";
			if (!empty(input('post.name')))
				$where .= " and KeyCol5 like '%" . trim(input('post.name')) . "%'";
			if (!empty(input('post.ActiveMessage')))
				$where .= " and ActiveMessage ='" . input('post.ActiveMessage') . "'";
			if (!empty(input('post.creator')))
				$where .= " and Creator ='" . input('post.creator') . "'";
			$list = Db::table('S_TB_FC_Active')
					->field('* ,[dbo].[S_F_GetUserNameByUserID](senduserid) as sendusername ,[dbo].[S_F_GetUserNameByUserID](activeuserid) as activeusername')
					->where($where)
					->order('senddate',' desc')
					->paginate(10, false, ['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
			if ($list) {
				return return_array_result(1, lang('查询成功'), '', ['list' => $list->toArray()['data'], 'page' => $list->render()]);
			} else {
				return return_array_result(0, lang('查询失败'));
			}
		}
	}

}