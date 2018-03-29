<?php
/**
func:人员状况
 */
namespace app\index\controller;

use think\Db;
class StaffDetail extends Common
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
		$this->table=Db::table('Z_TB_Staff_Information');
	}

	public function index(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);

		//员工信息
		$data=$this->table->where('applicationid',$applicationid)->find();
		$this->assign('data',$data);


		//核心管理层
		$board_list=DB::table('Z_TB_BoardDetail')->where('applicationid='.$applicationid)->order('id','desc')->select();
		$this->assign('board_list',$board_list);

		//董事长的简历
		$chairman_list=DB::table('Z_TB_ChairmanDetail')->where("applicationid=$applicationid and [key]='chairman'")->order('id','desc')->select();
		$this->assign('chairman_list',$chairman_list);

		//总经理的简历
		$manager_list=DB::table('Z_TB_ChairmanDetail')->where("applicationid=$applicationid and [key]='manager'")->order('id','desc')->select();
		$this->assign('manager_list',$manager_list);

		//颜色的变化
		$this->color_baogao1($applicationid,$option);

		$this->assign('type',8);
		$language='英文';
		$this->assign('language',$language);
		return $this->fetch();
	}

	//保存
	public function save(){
		$userid=session('auth_id');
		$applicationid=input('request.id');
		$activeid=input('request.activeid');

		$map['creator']=$userid;//创建人
		$map['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$map['applicationid']=input('request.id');//委托单id
		$map['activeid']=input('request.activeid');//activeid


		//人员状况
		$map['totalNum']=input('param.totalNum');
		$map['manageNum']=input('param.manageNum');
		$map['technologyNum']=input('param.technologyNum');
		$map['salesNum']=input('param.salesNum');
		$map['producerNum']=input('param.producerNum');
		$map['temporaryNum']=input('param.temporaryNum');
		$map['retireNum']=input('param.retireNum');
		$map['staff_detail']=input('param.staff_detail');
		$map['chairman_detail']=input('param.chairman_detail');
		$map['manager_detail']=input('param.manager_detail');

		$this->table->where('applicationid',$applicationid)->delete();

		$id = $this->table->insertGetId($map);
		if ($id) {
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
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

	//增加核心管理层
	public function add_board()
	{
		$info['creator'] = session('auth_id');//创建人
		$info['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$info['applicationid'] = input('post.applicationid');
		$info['name'] ='';
		$info['position'] ='';
		$info['sex'] ='';
		$info['age'] ='';
		$info['education'] ='';
		$info['manage'] ='';
		$data = Db::table('Z_TB_BoardDetail')->where('applicationid',$info['applicationid'])->order('id','desc')->select();
		if($data){
			foreach($data as $v){
				if(!$v['senddate']){
					return return_array_result(0,lang('新增失败'));
					break;
				}
				else{
					$tableName=Db::table('Z_TB_BoardDetail');
					$res = $tableName->insertGetId($info);
					if ($res) {
						$result = $tableName->where('id',$res)->find();
						return return_array_result(1,lang('新增成功'),'',$result);
					}
				}
			}
		}
		else{
			$tableName=Db::table('Z_TB_BoardDetail');
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
		$res =Db::table('Z_TB_BoardDetail')->where('id='.$id)->delete();
		if ($res !== false) {
			return return_array_result(1,lang('删除成功'));
		}

	}
	//保存
	public function board_update()
	{
		$info['name'] = $_POST['xs1'];
		$info['position'] = $_POST['xs2'];
		$info['sex'] = $_POST['xs3'];
		$info['age'] = $_POST['xs4'];
		$info['education'] = $_POST['xs5'];
		$info['manage'] = $_POST['xs6'];
		$info['senddate']=date('Y-m-d H:i:s',time());//保存时间
		$id = $_POST['id'];
		$re =  Db::table('Z_TB_BoardDetail')->where("id={$id}")->update($info);
		if ($re) {
			return return_array_result(1,lang('保存成功'));
		}
	}

	//增加主要管理者简历（董事长、总经理）
	public function add_manager()
	{
		$info['creator'] = session('auth_id');//创建人
		$info['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$info['applicationid'] = input('post.applicationid');
		$info['key'] =input('post.key');
		$info['time'] ='';
		$info['company'] ='';
		$info['post'] = '';
		$data = Db::table('Z_TB_ChairmanDetail')->where('applicationid',$info['applicationid'])->where('key',$info['key'])->order('id','desc')->select();
		if($data){
			foreach($data as $v){
				if(!$v['senddate']){
					return return_array_result(0,lang('新增失败'));
					break;
				}
				else{
					$tableName=Db::table('Z_TB_ChairmanDetail');
					$res = $tableName->insertGetId($info);
					if ($res) {
						$result = $tableName->where('id',$res)->find();
						return return_array_result(1,lang('新增成功'),'',$result);
					}
				}
			}
		}
		else{
			$tableName=Db::table('Z_TB_ChairmanDetail');
			$res = $tableName->insertGetId($info);
			if ($res) {
				$result = $tableName->where('id',$res)->find();
				return return_array_result(1,lang('新增成功'),'',$result);
			}
		}

	}
	//删除
	public function manager_del()
	{
		$id = input('post.id');
		$key = input('post.key');
		$res =Db::table('Z_TB_ChairmanDetail')->where("id={$id}")->where("[key]='{$key}'")->delete();
		if ($res !== false) {
			return return_array_result(1,lang('删除成功'));
		}

	}
	//保存
	public function manager_update()
	{
		$key = $_POST['key'];
		$info['time'] = $_POST['xs1'];
		$info['company'] =$_POST['xs2'];
		$info['post'] = $_POST['xs3'];
		$info['senddate']=date('Y-m-d H:i:s',time());//保存时间
		$id = $_POST['id'];
		$re =  Db::table('Z_TB_ChairmanDetail')->where("id={$id}")->where("[key]='{$key}'")->update($info);
		if ($re) {
			return return_array_result(1,lang('保存成功'));
		}
	}

	//保存(翻译)
	public function saveEng(){
		$applicationid=input('request.id');
		//人员状况
		$map['totalNumEng']=input('param.totalNumEng');
		$map['manageNumEng']=input('param.manageNumEng');
		$map['technologyNumEng']=input('param.technologyNumEng');
		$map['salesNumEng']=input('param.salesNumEng');
		$map['producerNumEng']=input('param.producerNumEng');
		$map['temporaryNumEng']=input('param.temporaryNumEng');
		$map['retireNumEng']=input('param.retireNumEng');
		$map['staff_detailEng']=input('param.staff_detailEng');
		$map['chairman_detailEng']=input('param.chairman_detailEng');
		$map['manager_detailEng']=input('param.manager_detailEng');

		$ret=$this->table->where('applicationid',$applicationid)->update($map);
		if ($ret!==false) {
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
		}
	}

	//确认
	public function recheckEng(){
		$applicationid=input('request.applicationid');;
		return  $this->checkEng($this->table,$applicationid);
	}

	//备注
	public function save_memoEng(){
		$applicationid=input('request.applicationid');
		$memo=input('request.confirmMemoEng');
		return  $this->memoEng($this->table,$applicationid,$memo);
	}

}