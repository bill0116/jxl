<?php
/**
func:委托方信息管理
 */
namespace app\index\controller;

use think\Db;
class EntrustorManage extends Common
{
	public function search()
	{
		if ($this->request->isPost()) {
			$where = "1=1";
			if (!empty(input('post.buyerno')))
				$where .= "and buyerno='" . trim(input('post.buyerno')) . "'";
			if (!empty(input('post.buyerName')))
				$where .= "and buyerName like'%" . trim(input('post.buyerName')) . "%'";
			$list = Db::table('Z_TB_Sinosure_Buyer')
					->field('*')
					->where($where)
					->paginate(10, false, ['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
			if ($list) {
				$data = $list->toArray()['data'];
				$page = $list->render();
				return return_array_result(1, lang('查询成功'), '', ['list' => $data, 'page' => $page]);
			} else {
				return return_array_result(0, lang('查询失败'));
			}
		} else {
			return $this->fetch();
		}
	}

	//新增
	public function add()
	{
		if($this->request->isPost()){
			$map = input('param.');
			$status = Db::table('Z_TB_Sinosure_Buyer')->insert($map);
			if ($status !== false) {
				$this->success('新增成功', url('EntrustorManage/search'),'', 3);
			} else {
				$this->error('新增失败');
			}
		}else{
			return $this->view->fetch();
		}
	}

	//编辑
	public function edit()
	{
		$buyerno = input('get.buyerno');
		$result = DB::table('Z_TB_Sinosure_Buyer')
				->field('*')
				->where('buyerno', $buyerno)
				->find();
		$this->assign('data', $result);
		return $this->fetch();
	}

	//保存修改后的
	public function save()
	{
		if ($this->request->isAjax()) {
			$map = request()->except(['buyerno'], 'post');
			$status = Db::table('Z_TB_Sinosure_Buyer')
					->where('buyerno', input('post.buyerno'))
					->update($map);
			if ($status !== false) {
				return return_array_result(1, lang('保存成功'));
			} else {
				return return_array_result(0, lang('保存失败'));
			}
		}
	}

	//删除
	public function del(){
		if ($this->request->isAjax()) {
			$map =input('post.id');
			$status = Db::table('Z_TB_Sinosure_Buyer')
					->where('buyerno','in',$map)
					->delete();
			if ($status) {
				return return_array_result(1, lang('删除成功'));
			} else {
				return return_array_result(0, lang('删除失败'));
			}
		}
	}
}
