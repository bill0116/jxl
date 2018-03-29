<?php
/**
func:组织结构
 */
namespace app\index\controller;

use think\Db;
class Organization extends Common
{
	protected $beforeActionList = ['getAttachList'];

	protected function getAttachList(){
		$applicationid=input('get.applicationid');
		$this->assign('attachList',$this->getAttachFile($this->getAttachID($applicationid)));
	}

	public function initialize()
	{
		parent::initialize();
		$this->model = model('flow');
		$this->table=Db::table('Z_TB_Organization');
	}

	public function index(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);


		$data=$this->table->where('applicationid',$applicationid)->find();
		$this->assign('data',$data);

		//职能部门
		$department_list=DB::table('Z_TB_Department')->where('applicationid='.$applicationid)->order('id','desc')->select();
		$this->assign('department_list',$department_list);

		//分支机构
		$branch_list=DB::table('Z_TB_BranchDepartment')->where('applicationid='.$applicationid)->order('id','desc')->select();
		$this->assign('branch_list',$branch_list);

		//全资和控股子公司
		$hold_list=DB::table('Z_TB_Subsidiary')->where('applicationid='.$applicationid)->order('id','desc')->select();
		$this->assign('hold_list',$hold_list);

		//参股企业
		$share_list=DB::table('Z_TB_Shares')->where('applicationid='.$applicationid)->order('id','desc')->select();
		$this->assign('share_list',$share_list);

		//颜色的变化
		$this->color_baogao1($applicationid,$option);

		$this->assign('type',7);
		$language='英文';
		$this->assign('language',$language);
		return $this->fetch();
	}

	//保存
	public function save(){
		$userid=session('auth_id');
		$applicationid=input('request.applicationid');
		$activeid=input('request.activeid');

		$map['creator']=$userid;//创建人
		$map['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$map['applicationid']=input('request.applicationid');//委托单id
		$map['activeid']=input('request.activeid');//activeid


		$map['attach1']=input('request.attach1');
		$map['attach2']=input('request.attach2');
		$map['attach3']=input('request.attach3');

		$this->table->where('applicationid',$applicationid)->delete();

		$id = $this->table->insertGetId($map);
		if ($id) {
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
		}
	}

	//上传组织机构
	public function upload1()
	{
		if (request()->isAjax()) {
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

				$id = Db::table('S_TB_Attach')->insertGetId($fileInfo);

				if ($id) {
					//获取附件
					$attachList = $this->getAttachFile($id);
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

	//上传生产组织机构图
	public function upload2()
	{
		if (request()->isAjax()) {
			$files = request()->file('Attaches2');
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

				$id = Db::table('S_TB_Attach')->insertGetId($fileInfo);

				if ($id) {
					//获取附件
					$attachList = $this->getAttachFile($id);
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

	//上传销售组织机构图
	public function upload3()
	{
		if (request()->isAjax()) {
			$files = request()->file('Attaches3');
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

				$id = Db::table('S_TB_Attach')->insertGetId($fileInfo);

				if ($id) {
					//获取附件
					$attachList = $this->getAttachFile($id);
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

	//增加职能部门
	public function add_adviser()
	{
		$info['creator'] = session('auth_id');//创建人
		$info['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$info['applicationid'] = input('post.applicationid');
		$info['name'] = '';
		$info['lower'] ='';
		$info['number'] = '';
		$info['detail'] ='';
		$data = Db::table('Z_TB_Department')->where('applicationid',$info['applicationid'])->order('id','desc')->select();
		if($data){
			foreach($data as $v){
				if(!$v['senddate']){
					return return_array_result(0,lang('新增失败'));
					break;
				}
				else{
					$tableName=Db::table('Z_TB_Department');
					$res = $tableName->insertGetId($info);
					if ($res) {
						$result = $tableName->where('id',$res)->find();
						return return_array_result(1,lang('新增成功'),'',$result);
					}
				}
			}
		}
		else{
			$tableName=Db::table('Z_TB_Department');
			$res = $tableName->insertGetId($info);
			if ($res) {
				$result = $tableName->where('id',$res)->find();
				return return_array_result(1,lang('新增成功'),'',$result);
			}
		}

	}
	//删除
	public function adviser_del()
	{
		$id = input('post.id');
		$res =Db::table('Z_TB_Department')->where('id='.$id)->delete();
		if ($res !== false) {
			return return_array_result(1,lang('删除成功'));
		}

	}
	//保存
	public function adviser_update()
	{
		$info['name'] = $_POST['xs1'];
		$info['lower'] =$_POST['xs2'];
		$info['number'] = $_POST['xs3'];
		$info['detail'] =$_POST['xs4'];
		$info['senddate']=date('Y-m-d H:i:s',time());//保存时间
		$id = $_POST['id'];
		$re =  Db::table('Z_TB_Department')->where("id={$id}")->update($info);
		if ($re) {
			return return_array_result(1,lang('保存成功'));
		}
	}

	//增加分支机构
	public function add_board()
	{
		$info['creator'] = session('auth_id');//创建人
		$info['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$info['applicationid'] = input('post.applicationid');
		$info['name'] ='';
		$info['address'] = '';
		$info['content'] = '';
		$info['leader'] ='';
		$info['tel'] = '';
		$data = Db::table('Z_TB_BranchDepartment')->where('applicationid',$info['applicationid'])->order('id','desc')->select();
		if($data){
			foreach($data as $v){
				if(!$v['senddate']){
					return return_array_result(0,lang('新增失败'));
					break;
				}
				else{
					$tableName=Db::table('Z_TB_BranchDepartment');
					$res = $tableName->insertGetId($info);
					if ($res) {
						$result = $tableName->where('id',$res)->find();
						return return_array_result(1,lang('新增成功'),'',$result);
					}
				}
			}
		}
		else{
			$tableName=Db::table('Z_TB_BranchDepartment');
			$res = $tableName->insertGetId($info);
			if ($res) {
				$result = $tableName->where('id',$res)->find();
				return return_array_result(1,lang('新增成功'),'',$result);
			}
		}

	}
	//删除
	public function board_del()
	{
		$id = input('post.id');
		$res =Db::table('Z_TB_BranchDepartment')->where('id='.$id)->delete();
		if ($res !== false) {
			return return_array_result(1,lang('删除成功'));
		}

	}
	//保存
	public function board_update()
	{
		$info['name'] = $_POST['xs1'];
		$info['address'] = $_POST['xs2'];
		$info['content'] = $_POST['xs3'];
		$info['leader'] = $_POST['xs4'];
		$info['tel'] = $_POST['xs5'];
		$info['senddate']=date('Y-m-d H:i:s',time());//保存时间
		$id = $_POST['id'];
		$re =  Db::table('Z_TB_BranchDepartment')->where("id={$id}")->update($info);
		if ($re) {
			return return_array_result(1,lang('保存成功'));
		}
	}

	//增加全资和控股子公司
	public function add_hold()
	{
		$info['creator'] = session('auth_id');//创建人
		$info['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$info['applicationid'] = input('post.applicationid');
		$info['name'] ='';
		$info['rate'] = '';
		$info['business'] = '';
		$info['scale'] ='';
		$info['leader'] = '';
		$info['memo'] = '';
		$data = Db::table('Z_TB_Subsidiary')->where('applicationid',$info['applicationid'])->order('id','desc')->select();
		if($data){
			foreach($data as $v){
				if(!$v['senddate']){
					return return_array_result(0,lang('新增失败'));
					break;
				}
				else{
					$tableName=Db::table('Z_TB_Subsidiary');
					$res = $tableName->insertGetId($info);
					if ($res) {
						$result = $tableName->where('id',$res)->find();
						return return_array_result(1,lang('新增成功'),'',$result);
					}
				}
			}
		}
		else{
			$tableName=Db::table('Z_TB_Subsidiary');
			$res = $tableName->insertGetId($info);
			if ($res) {
				$result = $tableName->where('id',$res)->find();
				return return_array_result(1,lang('新增成功'),'',$result);
			}
		}

	}
	//删除
	public function hold_del()
	{
		$id = input('post.id');
		$res =Db::table('Z_TB_Subsidiary')->where('id='.$id)->delete();
		if ($res !== false) {
			return return_array_result(1,lang('删除成功'));
		}

	}
	//保存
	public function hold_update()
	{
		$info['name'] = $_POST['xs1'];
		$info['rate'] = $_POST['xs2'];
		$info['business'] = $_POST['xs3'];
		$info['scale'] = $_POST['xs4'];
		$info['leader'] = $_POST['xs5'];
		$info['memo'] = $_POST['xs6'];
		$info['senddate']=date('Y-m-d H:i:s',time());//保存时间
		$id = $_POST['id'];
		$re =  Db::table('Z_TB_Subsidiary')->where("id={$id}")->update($info);
		if ($re) {
			return return_array_result(1,lang('保存成功'));
		}
	}

	//增加参股企业
	public function add_share()
	{
		$info['creator'] = session('auth_id');//创建人
		$info['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$info['applicationid'] = input('post.applicationid');
		$info['name'] = '';
		$info['rate'] = '';
		$info['summany'] ='';
		$data = Db::table('Z_TB_Shares')->where('applicationid',$info['applicationid'])->order('id','desc')->select();
		if($data){
			foreach($data as $v){
				if(!$v['senddate']){
					return return_array_result(0,lang('新增失败'));
					break;
				}
				else{
					$tableName=Db::table('Z_TB_Shares');
					$res = $tableName->insertGetId($info);
					if ($res) {
						$result = $tableName->where('id',$res)->find();
						return return_array_result(1,lang('新增成功'),'',$result);
					}
				}
			}
		}
		else{
			$tableName=Db::table('Z_TB_Shares');
			$res = $tableName->insertGetId($info);
			if ($res) {
				$result = $tableName->where('id',$res)->find();
				return return_array_result(1,lang('新增成功'),'',$result);
			}
		}



	}
	//保存
	public function share_update()
	{
		$info['name'] = $_POST['xs1'];
		$info['rate'] = $_POST['xs2'];
		$info['summany'] = $_POST['xs3'];
		$info['senddate']=date('Y-m-d H:i:s',time());//保存时间
		$id = $_POST['id'];
		$applicationid = $_POST['applicationid'];
		$re =  Db::table('Z_TB_Shares')->where("id={$id}")->update($info);
		if ($re) {
			$result =Db::table('Z_TB_Shares')->where('applicationid', $applicationid)->select();
			return return_array_result(1,lang('保存成功'));
		}
	}
	//删除
	public function share_del()
	{
		$id = input('post.id');
		$applicationid=input('post.applicationid');
		$res =Db::table('Z_TB_Shares')->where('id='.$id)->delete();
		if ($res !== false) {
			$result =Db::table('Z_TB_Shares')->where('applicationid', $applicationid)->select();
			return return_array_result(1,lang('删除成功'));
		}

	}

	//确认
	public function recheck(){
		$applicationid=input('request.applicationid');;
		return  $this->check($this->table,$applicationid);
	}

	//备注
	public function save_memo(){
		$applicationid=input('request.applicationid');
		$memo=input('request.confirmMemo');
		return  $this->memo($this->table,$applicationid,$memo);
	}
}