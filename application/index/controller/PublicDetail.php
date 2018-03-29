<?php
/**
 * func:公共记录
 */
namespace app\index\controller;

use think\Db;

class PublicDetail extends Common
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
        $this->table = Db::table('Z_TB_PublicReport');
    }

    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);


        //执行信息
        $exec_list = DB::table('Z_TB_ReportExecute')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('exec_list', $exec_list);

        //失信信息
        $dishonesty_list = DB::table('Z_TB_ReportDishonesty')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('dishonesty_list', $dishonesty_list);

        //法院公告
        $notice_list = DB::table('Z_TB_ReportCourtNotice')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('notice_list', $notice_list);

        //裁判文书
        $referee_list = DB::table('Z_TB_ReportRefereeDocument')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('referee_list', $referee_list);


        //公共记录
        $data = $this->table->where('applicationid', $applicationid)->find();
        $this->assign('data', $data);

        //颜色的变化
        $this->color_baogao($applicationid, $option);

        $this->assign('type', 9);
        $language = '英文';
        $this->assign('language', $language);
        return $this->fetch();
    }


	//保存
    public function save()
    {
        $userid = session('auth_id');
        $applicationid = input('request.id');

        $map['creator'] = $userid;//创建人
        $map['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $map['applicationid'] = input('request.id');//委托单id
        $map['certificateCode'] = input('request.certificateCode');
        $map['grade'] = input('request.grade');
        $map['qualification'] = input('request.qualification');
        $map['badReport'] = input('request.badReport');
        $map['website'] = input('request.website');
        $map['organization'] = input('request.organization');
        $map['name_authen'] = input('request.name_authen');
        $map['safetyGrade'] = input('request.safetyGrade');


        $this->table->where('applicationid', $applicationid)->delete();

        $id = $this->table->insertGetId($map);
        if ($id) {
            return return_array_result(1, lang('保存成功'));
        } else {
            return return_array_result(0, lang('保存失败'));
        }

    }

    //确认
    public function recheck()
    {
        $applicationid = input('request.applicationid');;
        return $this->check($this->table, $applicationid);
    }

    //备注
    public function save_memo()
    {
        $applicationid = input('request.applicationid');
        $memo = input('request.confirmMemo');
        return $this->memo($this->table, $applicationid, $memo);
    }

    //增加执行信息/失信信息/法院公告/裁判文书
    public function add_report()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['information'] = '';
        $info['tableKey'] = input('post.key');
        $key = input('post.key');
        if ($key == 'exec') {
            $info['key1'] = '';
            $info['key2'] = '';
            $info['key3'] = '';
            $info['key4'] = '';
            $info['key5'] = '';
            $tableName = Db::table('Z_TB_ReportExecute');
        } else if ($key == 'dishonesty') {
            $tableName = Db::table('Z_TB_ReportDishonesty');
        } else if ($key == 'notice') {
            $info['key1'] = '';
            $info['key2'] = '';
            $info['key3'] = '';
            $info['key4'] = '';
            $info['key5'] = '';
            $tableName = Db::table('Z_TB_ReportCourtNotice');
        } else {
            $tableName = Db::table('Z_TB_ReportRefereeDocument');
        }
        $res = $tableName->insertGetId($info);
        if ($res) {
            $result = $tableName->where('id', $res)->find();
            return return_array_result(1, lang('新增成功'), '', $result);
        }
    }

    //删除
    public function report_del()
    {
        $id = input('post.id');
        $key = input('post.key');
        if ($key == 'exec') {
            $tableName = Db::table('Z_TB_ReportExecute');
        } else if ($key == 'dishonesty') {
            $tableName = Db::table('Z_TB_ReportDishonesty');
        } else if ($key == 'notice') {
            $tableName = Db::table('Z_TB_ReportCourtNotice');
        } else {
            $tableName = Db::table('Z_TB_ReportRefereeDocument');
        }
        $res = $tableName->where('id=' . $id)->delete();
        if ($res !== false) {
            $result = 1;
            return return_array_result(1, lang('删除成功'), '', $result);
        }

    }

    //保存
    public function report_update()
    {
        $key = input('post.key');
        if ($key == 'exec') {
            $info['key1'] = $_POST['xs1'];
            $info['key2'] = $_POST['xs2'];
            $info['key3'] = $_POST['xs3'];
            $info['key4'] = $_POST['xs4'];
            $info['key5'] = $_POST['xs5'];
            $tableName = Db::table('Z_TB_ReportExecute');
        } else if ($key == 'dishonesty') {
            $info['information'] = $_POST['xs1'];
            $tableName = Db::table('Z_TB_ReportDishonesty');
        } else if ($key == 'notice') {
            $info['key1'] = $_POST['xs1'];
            $info['key2'] = $_POST['xs2'];
            $info['key3'] = $_POST['xs3'];
            $info['key4'] = $_POST['xs4'];
            $info['key5'] = $_POST['xs5'];
            $tableName = Db::table('Z_TB_ReportCourtNotice');
        } else {
            $info['information'] = $_POST['xs1'];
            $tableName = Db::table('Z_TB_ReportRefereeDocument');
        }
        $id = $_POST['id'];
        $re = $tableName->where("id={$id}")->update($info);
        if ($re) {
            $result = $tableName->where('id', $id)->select();
            return return_array_result(1, lang('保存成功'), '', $result);
        }
    }


	//保存(翻译)
	public function saveEng(){
		$applicationid=input('request.id');

		$map['certificateCodeEng']=input('request.certificateCodeEng');
		$map['gradeEng']=input('request.gradeEng');
		$map['qualificationEng']=input('request.qualificationEng');
		$map['badReportEng']=input('request.badReportEng');
		$map['websiteEng']=input('request.websiteEng');
		$map['organizationEng']=input('request.organizationEng');
		$map['name_authenEng']=input('request.name_authenEng');
		$map['safetyGradeEng']=input('request.safetyGradeEng');

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