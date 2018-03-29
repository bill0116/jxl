<?php
/**
 * func:法律纠纷
 */
namespace app\index\controller;

use think\Db;

class LegalDispute extends Common
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
        $this->table = Db::table('Z_TB_LegalDispute_Affect');
    }

    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);

        //影响
        $data = $this->table->where('applicationid', $applicationid)->find();
        $this->assign('data', $data);


        //法律纠纷
        $dispute_list = DB::table('Z_TB_LegalDispute')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('dispute_list', $dispute_list);

        //颜色的变化
        $this->color_baogao1($applicationid,$option);


        $this->assign('type', 17);
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

        //影响
        $map['affect'] = input('request.affect');
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

    //增加法律纠纷
    public function add_dispute()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['name'] = '';
        $info['summary'] = '';
        $info['request'] = '';
        $info['date'] = '';
        $data = Db::table('Z_TB_LegalDispute')->where('applicationid', $info['applicationid'])->order('id', 'desc')->select();
        if ($data) {
            foreach ($data as $v) {
                if (!$v['senddate']) {
                    return return_array_result(0, lang('新增失败'));
                    break;
                } else {
                    $tableName = Db::table('Z_TB_LegalDispute');
                    $res = $tableName->insertGetId($info);
                    if ($res) {
                        $result = $tableName->where('id', $res)->find();
                        return return_array_result(1, lang('新增成功'), '', $result);
                    }
                }
            }
        } else {
            $tableName = Db::table('Z_TB_LegalDispute');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'), '', $result);
            }
        }

    }

    //删除
    public function dispute_del()
    {
        $id = input('post.id');
        $res = Db::table('Z_TB_LegalDispute')->where('id=' . $id)->delete();
        if ($res !== false) {
            return return_array_result(1, lang('删除成功'));
        }

    }

    //保存
    public function dispute_update()
    {
        $info['name'] = $_POST['xs1'];
        $info['summary'] = $_POST['xs2'];
        $info['request'] = $_POST['xs3'];
        $info['date'] = $_POST['xs4'];
        $info['senddate'] = date('Y-m-d H:i:s', time());//保存时间
        $id = $_POST['id'];
        $re = Db::table('Z_TB_LegalDispute')->where("id={$id}")->update($info);
        if ($re) {
            return return_array_result(1, lang('保存成功'));
        }
    }

    //保存(翻译)
    public function saveEng(){
        $applicationid=input('request.id');

        //影响
        $map['affectEng']=input('request.affectEng');

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