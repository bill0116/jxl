<?php
/**
 * Func: 限额预撤销提醒查询
 */
namespace app\index\controller;

use think\Db;

class QuotaPreRevocationSearch extends Common{
	public function index(){
		if($this->request->isPost()){
			$where = "1=1 ";
			if (!empty(input('post.payMode')))
				$where .= " and a.payMode='" . input('post.payMode') . "'";
			if (!empty(input('post.bizbuyerNo')))
				$where .= " and a.bizbuyerNo like '%" . trim(input('post.bizbuyerNo')) . "%'";
			if (!empty(input('post.buyerEngName')))
				$where .= " and b.buyerEngName like '%" . trim(input('post.buyerEngName')) . "%'";
			if (!empty(input('post.effectdate')))
				$where .= " and a.effectdate >='" . trim(input('post.effectdate')) . "'";
			if (!empty(input('post.lapsedate')))
				$where .= " and a.lapsedate <='" . trim(input('post.lapsedate')) . "'";
			$list = Db::table('Z_TB_Sinosure_QuotaPreRevocationInfo')
				->alias('a')
				->field('dbo.S_F_GetGentalCodeText(219,a.paymode)  as  paymodetext,a.*')
				->join('Z_TB_Sinosure_Buyer b', 'a.bizbuyerNo=b.buyerno','left')
				->where($where)
				->paginate(10, false, ['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
			if($list){
				$data=$list->toArray()['data'];
				$page= $list->render();
				return return_array_result(1,lang('查询成功'),'',['list'=>$data,'page'=> $page]);
			}else{
				return return_array_result(0,lang('查询失败'));
			}
		}else{
			return $this->fetch();
		}
	}
}