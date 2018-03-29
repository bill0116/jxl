<?php
/**
func:调档管理
 */
namespace app\index\controller;

use think\Db;
class DiaodangManage extends Common
{
	public function initialize()
	{
		parent::initialize();
		$this->model = model('flow');
		$this->table=Db::table('Z_TB_DiaoDang_Detail');
	}

	public function get_list(){
		$userid=session('auth_id');
		$status='3';
		$data=$this->getWTD_list($userid,$status);
		$this->assign('data',$data);
		return  $this->fetch();
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
			$map['status']=4;//阶段
			$map['statusName']='分配';//阶段

			$status = Db::table('Z_TB_WTDApply')
					->where('ID',$applicationid)
					->update($map);
			if ($status !== false) {
				$ret=Db::execute("update S_TB_FC_Active set  SendDate=getdate(), senduserid={$userid} ,activeuserid={$userid},status=4,statusname='分配' where activeid={$activeid}");
				$this->success('提交成功', url('DiaodangManage/get_list'), 1);
			} else {
				$this->error('提交失败');
			}
		}

	}

	//调档明细新增
	public function detail_add(){
		if(request()->isPost()){
			$map = input('param.');
			$info['applicationid']=$map['applicationid'];
			$info['memo']=$map['memo'];
			$info['company']=$map['company'];
			$info['date']=date('Y-m-d H:i:s',time());
			$id = $this->table->insertGetId($info);
			if ($id) {
				return return_array_result(1, lang('保存成功'));
			} else {
				return return_array_result(0, lang('保存失败'));
			}
		}
		else{
			$map = input('param.');
			$applicationid=$map['applicationid'];
			$this->assign('applicationid',$applicationid);
			return $this->fetch();
		}

	}

	//调档明细编辑/查看
	public function detail_edit(){
		$map = input('param.');
		$type=$map['type'];
		$id=$map['id'];
		$resultData = Db::table('Z_TB_DiaoDang_Detail')->where('id=' . $id)->find();
		$this->assign('data', $resultData);
		//附件
		$attachList = $this->getAttachFile($resultData['Attaches']);
		$this->assign('attachList',$attachList);
		$this->assign('type', $type);
		return $this->fetch();
	}

	//调档明细保存
	public function detail_save(){
		$map = input('param.');
		$id=$map['id'];
		$param['company']=$map['company'];
		$param['memo']=$map['memo'];
		$param['cost']=$map['cost'];
		$param['date']=empty(!$map['date'])?$map['date']:null;

		$status = Db::table('Z_TB_DiaoDang_Detail')->where('id', $id)->update($param);
		if ($status !== false) {
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
		}

	}

	//调档明细删除
	public function detail_del(){
		$id=input('request.id');
		$number = Db::table('Z_TB_DiaoDang_Detail')->where('id', $id)->delete();
		echo $number;

	}

	/*
 * @ 附件上传
 * @ string主表Attaches字段
 * @ 关联S_TB_Attach表
 * */
	public function uploadFile_diaodang()
	{
		if (request()->isAjax()) {
			//$Attaches1=input('request.Attaches1');
			$files = request()->file('Attaches1');
			$info = $files->move('public/uploads');
			if ($info) {
				$fileInfo = [
						'AttachUrl' => str_replace('\\', '/', $info->getPathname()),
						'AttachName' => $files->getInfo()['name'],
						'CreateDate' => date('Y-m-d H:i:s', time()),
						'CreatorName' => session('user_name'),
						'Creator' => session('auth_id'),
						'Size' => $files->getSize(),
						'AttachNewName' => $info->getFilename(),
				];
				$attach = Db::table('Z_TB_DiaoDang_Detail')
						->where('id', input('post.id'))
						->value('Attaches');
				$id = Db::table('S_TB_Attach')->insertGetId($fileInfo);
				$attachString = empty($attach) ? $id : $attach . "," . $id;
				$attachStatus = Db::table('Z_TB_DiaoDang_Detail')
						->where('id', input('post.id'))
						->setField('Attaches', $attachString);
				if ($id && $attachStatus !== false) {
					//获取附件
					$attachList = $this->getAttachFile($attachString);
					return return_array_result(1, lang('上传成功!'), '', $attachList);
				} else {
					return return_array_result(0, lang('上传失败'));
				}
			} else {
				return return_array_result(0, lang('上传失败'));
			}
		} else {
			return return_array_result(0, lang('请求不合法'));
		}
	}

	/*
	* @ 附件删除
	* @ string主表Attaches字段
	* @ 关联S_TB_Attach表
	* */
	public function delFile_diaodang()
	{
		if ($this->request->isAjax()) {
			$attach = Db::table('Z_TB_DiaoDang_Detail')
					->where('id', input('post.id'))
					->value('Attaches');
			$attachString = implode(',', array_merge(array_diff(explode(',', $attach), array(input('post.file_id')))));
			$attachStatus = Db::table('Z_TB_DiaoDang_Detail')
					->where('id', input('post.id'))
					->setField('Attaches', $attachString);
			$res = Db::table('S_TB_Attach')
					->where('ID', input('post.file_id'))
					->delete();
			if ($attachStatus !== false && $res !== false) {
				return return_array_result(1, lang('删除成功'));
			} else {
				return return_array_result(0, lang('删除失败'));
			}
		} else {
			return return_array_result(0, lang('请求不合法'));
		}
	}

}