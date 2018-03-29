<?php
/**
 * func:综合信用评价
 */
namespace app\index\controller;


use think\Db;

class CreditAnalyse1 extends Common
{
    protected $beforeActionList = ['getAttachList'];

    protected function getAttachList()
    {
        $applicationid = input('get.applicationid');
        $this->assign('attachList', $this->getAttachFile($this->getAttachID($applicationid)));
    }

    public function initialize()
    {
        parent::initialize();
        $this->model = model('flow');
        $this->table = Db::table('Z_TB_CreditAnalyse');
    }

    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);

        //信用分析
        $data = $this->table->where('applicationid', $applicationid)->find();
        $this->assign('data', $data);


        //企业发展历史
        $history_list = DB::table('Z_TB_Company_History')->where('applicationid=' . $applicationid)->order('id', 'asc')->select();
        $this->assign('history_list', $history_list);

        //颜色的变化
        $this->color_baogao1($applicationid,$option);

        $this->assign('type', 18);
        $language = '英文';
        $this->assign('language', $language);
        return $this->fetch();
    }

    //保存
    public function save()
    {
        $userid = session('auth_id');
        $applicationid = input('request.id');
        $activeid = input('request.activeid');

        $map['creator'] = $userid;//创建人
        $map['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $map['applicationid'] = input('request.id');//委托单id
        $map['activeid'] = input('request.activeid');//activeid

        $map['businessManage'] = input('request.businessManage');
        $map['summany'] = input('request.summany');
        $map['compreAnalyse'] = input('request.compreAnalyse');

        $this->table->where('applicationid', $applicationid)->delete();


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

	//保存(翻译)
	public function saveEng(){
		$applicationid=input('request.id');

		$map['businessManageEng']=input('request.businessManageEng');
		$map['summanyEng']=input('request.summanyEng');
		$map['compreAnalyseEng']=input('request.compreAnalyseEng');

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

    //增加发展史
    public function add_history()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['information'] = '';
        $info['time'] = '';
        $tableName = Db::table('Z_TB_Company_History');
        $res = $tableName->insertGetId($info);
        if ($res) {
            $result = $tableName->where('id', $res)->find();
            return return_array_result(1, lang('新增成功'), '', $result);
        }
    }

    public function history_del()
    {
        $id = input('post.id');
        $tableName = Db::table('Z_TB_Company_History');
        $res = $tableName->where('id=' . $id)->delete();
        if ($res !== false) {
            $result = 1;
            return return_array_result(1, lang('删除成功'), '', $result);
        }

    }

    //保存
    public function history_update()
    {
        $info['information'] = $_POST['xs1'];
        $id = $_POST['id'];
        $info['time'] = $_POST['xs2'];
        $tableName = Db::table('Z_TB_Company_History');
        $re = $tableName->where("id={$id}")->update($info);
        if ($re) {
            $result = $tableName->where('id', $id)->select();
            return return_array_result(1, lang('保存成功'), '', $result);
        }
    }

}