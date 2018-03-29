<?php
/**
func:注册信息2
 */
namespace app\index\controller;

use think\Db;
class Register2 extends Common
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
		$this->table=Db::table('Z_TB_Investigate_Information');
	}

	public function index(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);

		//访问信息
		$data=$this->table->where('applicationid',$applicationid)->find();
		$this->assign('data',$data);

		//变更
		$capital_list=Db::table('Z_TB_companyInformation_Change')->where('applicationid',$applicationid)->order('id','desc')->select();
		$this->assign('capital_list',$capital_list);

		//资质情况
		$board_list=DB::table('Z_TB_Qualification')->where('applicationid='.$applicationid)->order('id','desc')->select();
		$this->assign('board_list',$board_list);

		//顾问机构
		$adviser_list=DB::table('Z_TB_Adviser')->where('applicationid='.$applicationid)->order('id','desc')->select();
		$this->assign('adviser_list',$adviser_list);


		//颜色的变化
		$this->color_baogao1($applicationid,$option);

		$this->assign('type',5);
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

		//访问信息
		$map['date']=input('request.date');
		$map['department']=input('request.department');
		$map['key1']=input('request.key1');
		$map['key2']=input('request.key2');
		$map['key3']=input('request.key3');
		$map['key4']=input('request.key4');
		$map['key5']=input('request.key5');
		$map['key6']=input('request.key6');


		$this->table->where('applicationid',$applicationid)->delete();

		$id =$this->table->insertGetId($map);
		if ($id) {
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
		}
	}

	//增加资质
	public function add_board()
	{
		$info['creator'] = session('auth_id');//创建人
		$info['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$info['applicationid'] = input('post.applicationid');
		$info['certificate'] = '';
		$info['name'] ='';
		$info['productName'] = '';
		$info['date'] ='';
		$info['organization'] = '';
		$data = Db::table('Z_TB_Qualification')->where('applicationid',$info['applicationid'])->order('id','desc')->select();
		if($data){
			foreach($data as $v){
				if(!$v['senddate']){
					return return_array_result(0,lang('新增失败'));
					break;
				}
				else{
					$tableName=Db::table('Z_TB_Qualification');
					$res = $tableName->insertGetId($info);
					if ($res) {
						$result = $tableName->where('id',$res)->find();
						return return_array_result(1,lang('新增成功'),'',$result);
					}
				}
			}
		}
		else{
			$tableName=Db::table('Z_TB_Qualification');
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
		$res =Db::table('Z_TB_Qualification')->where('id='.$id)->delete();
		if ($res !== false) {
			return return_array_result(1,lang('删除成功'));
		}

	}
	//保存
	public function board_update()
	{
		$info['certificate'] = $_POST['xs1'];
		$info['name'] = $_POST['xs2'];
		$info['productName'] = $_POST['xs3'];
		$info['date'] = $_POST['xs4'];
		$info['organization'] = $_POST['xs5'];
		$info['senddate']=date('Y-m-d H:i:s',time());//保存时间
		$id = $_POST['id'];
		$re =  Db::table('Z_TB_Qualification')->where("id={$id}")->update($info);
		if ($re) {
			return return_array_result(1,lang('保存成功'));
		}
	}

	//增加顾问机构
	public function add_adviser()
	{
		$info['creator'] = session('auth_id');//创建人
		$info['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$info['applicationid'] = input('post.applicationid');
		$info['adviser'] = '';
		$info['address'] ='';
		$info['content'] = '';
		$info['start'] ='';
		$data = Db::table('Z_TB_Adviser')->where('applicationid',$info['applicationid'])->order('id','desc')->select();
		if($data){
			foreach($data as $v){
				if(!$v['senddate']){
					return return_array_result(0,lang('新增失败'));
					break;
				}
				else{
					$tableName=Db::table('Z_TB_Adviser');
					$res = $tableName->insertGetId($info);
					if ($res) {
						$result = $tableName->where('id',$res)->find();
						return return_array_result(1,lang('新增成功'),'',$result);
					}
				}
			}
		}
		else{
			$tableName=Db::table('Z_TB_Adviser');
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
		$res =Db::table('Z_TB_Adviser')->where('id='.$id)->delete();
		if ($res !== false) {
			return return_array_result(1,lang('删除成功'));
		}

	}
	//保存
	public function adviser_update()
	{
		$info['adviser'] = $_POST['xs1'];
		$info['address'] =$_POST['xs2'];
		$info['content'] = $_POST['xs3'];
		$info['start'] =$_POST['xs4'];
		$info['senddate']=date('Y-m-d H:i:s',time());//保存时间
		$id = $_POST['id'];
		$re =  Db::table('Z_TB_Adviser')->where("id={$id}")->update($info);
		if ($re) {
			return return_array_result(1,lang('保存成功'));
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

	//保存(翻译)
	public function saveEng(){
		$applicationid=input('request.id');

		//访问信息
		$map['dateEng']=input('request.dateEng');
		$map['departmentEng']=input('request.departmentEng');

		$ret=$this->table->where('applicationid',$applicationid)->update($map);
		if ($ret!==false) {
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
		}
	}

	//确认(翻译)
	public function recheckEng(){
		$applicationid=input('request.applicationid');;
		return  $this->checkEng($this->table,$applicationid);
	}

	//备注(翻译)
	public function save_memoEng(){
		$applicationid=input('request.applicationid');
		$memo=input('request.confirmMemoEng');
		return  $this->memoEng($this->table,$applicationid,$memo);
	}
}