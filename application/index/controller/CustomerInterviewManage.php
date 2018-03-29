<?php
/**
func:客户走访管理
 */
namespace app\index\controller;

use think\Db;
class CustomerInterviewManage extends Common
{
	public function search(){
		if($this->request->isPost()){
			$where="1=1";
			if(!empty(input('post.Organization')))
				$where.="and Organization='".trim(input('post.Organization'))."'";
			if(!empty(input('post.CustomerNumber')))
				$where.="and CustomerNumber like '%".trim(input('post.CustomerNumber'))."%'";
			if(!empty(input('post.CustomerName')))
				$where.="and CustomerName like '%".trim(input('post.CustomerName'))."%'";
			$list=Db::table('z_TB_Customer_Interview_List')
					->field('*,dbo.Z_F_GetCustomerNameByBuerNo(CustomerNumber,0) as CustomerName1,
                          dbo.S_F_GetOrganizationName(Organization) as OrganizationName')
					->where($where)
					->paginate(10,false,['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
			if($list){
				$data=$list->toArray()['data'];
				$page=$list->render();
				return return_array_result(1,lang('查询成功'),'',['list'=>$data,'page'=>$page]);
			}else{
				return return_array_result(0,lang('查询失败'));
			}
		}else{
			$org = $this->listOrganizations(session('auth_id'));
			$this->assign('org', $org);
			return $this->fetch();
		}

	}

	public function add(){

		$param['Creator'] = session('auth_id');
		$param['CreateName'] = session('user_name');
		$param['CreateDate'] = date('Y-m-d H:i:s', time());
		$id = Db::table('Z_TB_Customer_Interview_List')->insertGetId($param);
		if($id){
			$org = $this->listOrganizations(session('auth_id'));
			$this->assign('org', $org);
			$result= Db::table('Z_TB_Customer_Interview_List')
					->field('*')
					->where('id',$id)
					->find();
			$this->assign('data',$result);
		}
		return $this->fetch();

	}

	public function save(){
		if ($this->request->isAjax()) {
			$map = request()->except(['id'], 'post');
			$status = Db::table('Z_TB_Customer_Interview_List')
					->where('ID', input('post.id'))
					->update($map);
			if ($status !== false) {
				return return_array_result(1, lang('保存成功'));
			} else {
				return return_array_result(0, lang('保存失败'));
			}
		}
	}

}