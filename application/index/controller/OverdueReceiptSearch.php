<?php
/**
 func:逾期未收汇查询
 */
namespace app\index\controller;

use think\Db;
class OverdueReceiptSearch extends Common
{
	public function index(){
		if($this->request->isPost()){
			$where="1=1 ";
			if(!empty(input('post.inviceNo'))){
				$where.="and a.inviceNo='".trim(input('post.inviceNo'))."'";
			}
			if(!empty(input('post.sinosureBuyerNo'))){
				$where.="and a.sinosureBuyerNo='".trim(input('post.sinosureBuyerNo'))."'";
			}
			if(!empty(input('post.buyerEngName'))){
				$where.="and b.buyerEngName='".trim(input('post.buyerEngName'))."'";
			}
			if(!empty(input('post.transportDate'))){
				$where.="and a.transportDate='".trim(input('post.transportDate'))."'";
			}
			$list=Db::table('Z_TB_Sinosure_OverdueReceipt')
				->alias('a')
				->field('a.*')
				->join('Z_TB_Sinosure_Buyer b', 'a.sinosureBuyerNo=b.buyerno','left')
				->where($where)
				->paginate(10,false,['type'=>'bootstrap','var_page'=>'page','path'=>'javascript:doSearch([PAGE]);']);
			if($list){
				$data=$list->toArray()['data'];
				$page=$list->render();
				return return_array_result(1,lang('查询成功'),'',['list'=>$data,'page'=>$page]);
			}else{
				return return_array_result(0,lang('查询失败'));
			}
		}else{
			return $this->fetch();
		}
	}
}
