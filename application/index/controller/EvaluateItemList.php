<?php
/**
func:评分卡指标维护
 */
namespace app\index\controller;

use think\Db;
class EvaluateItemList extends Common
{
	public function index(){
		$list=Db('Creditevaluate_evaluateitem')
			->field('*')
			->order('EvaluateItemID','desc')
			->paginate(10,false,['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);

		$data=$list->toArray()['data'];
		$page=$list->render();
		$this->assign('data',$data);
		$this->assign('page',$page);
		return $this->fetch();
	}

	//查询
	public function search(){
		if($this->request->isPost()){
			$where="1=1";
			if(!empty(input('post.EvaluateItemName'))){
				$where.="and EvaluateItemName like'%".trim(input('post.EvaluateItemName'))."%'";
			}
			if(!empty(input('post.DataType'))){
				$where.="and DataType='".trim(input('post.DataType'))."'";
			}
			$list=Db('Creditevaluate_evaluateitem')
				->field('*')
				->where($where)
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

	//新增
	public function add(){
		return $this->fetch();
	}

	//编辑
	public function edit(){
		$id=input('get.id');
		$result= Db('Creditevaluate_evaluateitem')
				->field('*')
				->where('EvaluateItemID',$id)
				->find();
		$this->assign('data',$result);
		return $this->fetch();
	}

	//删除
	public function del(){
		$id=input('post.id');
		$sql="exec S_SP_CreditEvaluate_EvaluateItem_DeleteEvaluateItem $id";
		$number=  Db::execute($sql);
		echo $number;
	}

	//保存
	public function save(){
		$option=input('get.option');
		if($option=='add'){
			$EvaluateItemName=input('post.EvaluateItemName');
			$DataType=input('post.DataType');
			$DataFunction=input('post.DataFunction');
			$ItemKey=input('post.ItemKey');
			$sql="exec S_SP_CreditEvaluate_EvaluateItem_AddEvaluateItem $EvaluateItemName,$DataType,$DataFunction,$ItemKey";
			$number=  Db::execute($sql);
			if ($number !== false) {
				return return_array_result(1, lang('保存成功'));
			} else {
				return return_array_result(0, lang('保存失败'));
			}
		}
		else if($option=='edit'){
			$EvaluateItemID=input('post.id');
			$EvaluateItemName=input('post.EvaluateItemName');
			$DataType=input('post.DataType');
			$DataFunction=input('post.DataFunction');
			$ItemKey=input('post.ItemKey');
			$sql="exec S_SP_CreditEvaluate_EvaluateItem_SaveEvaluateItem $EvaluateItemID,$EvaluateItemName,$DataType,$DataFunction,$ItemKey";
			$number=  Db::execute($sql);
			if ($number !== false) {
				return return_array_result(1, lang('保存成功'));
			} else {
				return return_array_result(0, lang('保存失败'));
			}
		}

	}

	//编辑明细
	public function detail(){
		$type=input('get.type');
		$id=input('get.id');
		$list= Db('Creditevaluate_evaluateitemdetail')
				->field('*')
				->where('EvaluateItemID',$id)
				->order('EvaluateItemDetailIndex','asc')
				->select();
		$this->assign('data',$list);
		$this->assign('pid',$id);
		$this->assign('type',$type);
		return $this->fetch();
	}

	//明细新增
	public function detail_add(){
		$type=input('get.type');
		$pid=input('get.pid');
		$this->assign('pid',$pid);
		$this->assign('type',$type);
		return $this->fetch();
	}

	//明细编辑
	public function detail_edit(){
		$type=input('get.type');
		$id=input('get.id');
		$data=Db('Creditevaluate_evaluateitemdetail')
				->field('*')
				->where('EvaluateItemDetailID',$id)
				->find();
		$this->assign('data',$data);
		$this->assign('type',$type);
		return $this->fetch();
	}

	//明细保存
	public function detail_save(){
		$option=input('get.option');
		$EvaluateItemDetailIndex=input('post.EvaluateItemDetailIndex');
		$StartValue=input('post.StartValue');
		$EndValue=empty(!input('post.EndValue'))?input('post.EndValue'):'';
		$Ratio=input('post.Ratio');
		if($option=='add'){
			$pid=input('get.pid');
			if($EndValue==''){
				$sql="exec S_SP_CreditEvaluate_EvaluateItem_AddEvaluateItemDetail $pid,$StartValue,'',$Ratio,$EvaluateItemDetailIndex";
			}
			else{
				$sql="exec S_SP_CreditEvaluate_EvaluateItem_AddEvaluateItemDetail $pid,$StartValue,$EndValue,$Ratio,$EvaluateItemDetailIndex";
			}
			$number=Db::execute($sql);
			if ($number !== false) {
				return return_array_result(1, lang('保存成功'));
			} else {
				return return_array_result(0, lang('保存失败'));
			}
		}
		else if($option=='edit'){
			$id=input('post.id');
			$pid=input('post.pid');
			if($EndValue==''){
				$sql="exec S_SP_CreditEvaluate_EvaluateItem_SaveEvaluateItemDetail  $id,$pid,$StartValue,'',$Ratio,$EvaluateItemDetailIndex";
			}
			else{
				$sql="exec S_SP_CreditEvaluate_EvaluateItem_SaveEvaluateItemDetail  $id,$pid,$StartValue,$EndValue,$Ratio,$EvaluateItemDetailIndex";
			}
			$number=Db::execute($sql);
			if ($number !== false) {
				return return_array_result(1, lang('保存成功'));
			} else {
				return return_array_result(0, lang('保存失败'));
			}
		}
	}

	//明细删除
	public function detail_del(){
		$id=input('post.id');
		$sql="exec S_SP_CreditEvaluate_EvaluateItem_DeleteEvaluateItemDetail $id";
		$number=Db::execute($sql);
		echo $number;
	}
}