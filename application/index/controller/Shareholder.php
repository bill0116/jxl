<?php
/**
fun:股权情况
 */
namespace app\index\controller;

use think\Db;
class Shareholder extends Common
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
		$this->table=Db::table('Z_TB_Shareholder');
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

		//股东出资情况
		$share_list=DB::table('Z_TB_ShareholderDetail')->where('applicationid='.$applicationid)->order('id','desc')->select();
		$total_money=0;//出资额总计
		$total_rate=0;//股权总计
		foreach($share_list as $v){
			$total_money+=$v['money'];
			$total_rate+=$v['rate'];
		}
		$this->assign('share_list',$share_list);
		$this->assign('total_money',$total_money);
		$this->assign('total_rate',$total_rate);

		//主要股东概况
		$sharedetail_list=DB::table('Z_TB_ShareholderDetail_Information')->where('applicationid='.$applicationid)->order('id','desc')->select();
		$this->assign('sharedetail_list',$sharedetail_list);

		//颜色的变化
		$this->color_baogao1($applicationid,$option);

		$this->assign('type',6);
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


		$map['attach']=input('request.attach');//往来银行

		$this->table->where('applicationid',$applicationid)->delete();

		$id = $this->table->insertGetId($map);
		if ($id) {
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
		}
	}


	//上传附件
	public function upload()
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


   //新增股东概况
	public function add_report()
	{
		$info['creator'] = session('auth_id');//创建人
		$info['createdate']=date('Y-m-d H:i:s',time());//创建时间
		$info['applicationid'] = input('post.applicationid');
		$info['information'] ='';
		$tableName=Db::table('Z_TB_ShareholderDetail_Information');
		$res = $tableName->insertGetId($info);
		if ($res) {
			$result = $tableName->where('id',$res)->find();
			return return_array_result(1,lang('新增成功'),'',$result);
		}
	}
	//删除
	public function report_del()
	{
		$id = input('post.id');
		$tableName=Db::table('Z_TB_ShareholderDetail_Information');
		$res = $tableName->where('id=' . $id)->delete();
		if ($res !== false) {
			$result = 1;
			return return_array_result(1,lang('删除成功'),'',$result);
		}

	}
	//保存
	public function report_update()
	{
		$info['information'] = $_POST['xs1'];
		$id = $_POST['id'];
		$tableName=Db::table('Z_TB_ShareholderDetail_Information');
		$re =  $tableName->where("id={$id}")->update($info);
		if ($re) {
			$result = $tableName->where('id', $id)->select();
			return return_array_result(1,lang('保存成功'),'',$result);
		}
	}

}