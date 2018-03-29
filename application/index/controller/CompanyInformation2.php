<?php
/**
 * func:企业概况2
 */
namespace app\index\controller;

use think\Db;

class CompanyInformation2 extends Common
{
    protected $beforeActionList = ['getAttachList'];

    protected function getAttachList()
    {
        $applicationid = input('get.applicationid');
        $this->assign('attachList', $this->getAttachFile($this->getAttachID($applicationid)));
    }

    /*
* @ 初始化initialize
* */
    public function initialize()
    {
        parent::initialize();
        $this->model = model('flow');
        $this->table = Db::table('Z_TB_Branch');
    }

    public function index()
    {
        $option = input('get.option');
        $this->assign('option', $option);
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);

        //变更
        $capital_list = Db::table('Z_TB_companyInformation_Change')->where('applicationid', $applicationid)->order('item', 'desc')->order('id', 'asc')->select();
        $this->assign('capital_list', $capital_list);

        //股东情况
        $share_list = DB::table('Z_TB_ShareholderDetail')->where('applicationid=' . $applicationid)->order('rate', 'desc')->select();
        $total_money = 0;//出资额总计
        $total_rate = 0;//股权总计
        foreach ($share_list as $v) {
            $total_money += $v['money'];
            $total_rate += $v['rate'];
        }
        $this->assign('share_list', $share_list);
        $this->assign('total_money', $total_money);
        $this->assign('total_rate', $total_rate);

        //董事会情况
        $board_list = DB::table('Z_TB_BoardDetail')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('board_list', $board_list);

        //代表人对外投资
        $invest_list = DB::table('Z_TB_RepresentInvest')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('invest_list', $invest_list);

        //代表人在其他公司任职
        $position_list = DB::table('Z_TB_RepresentPosition')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('position_list', $position_list);

        //企业对外投资
        $companyinvest_list = DB::table('Z_TB_CompanyInvest')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('companyinvest_list', $companyinvest_list);

        //分支机构(主表)
        $data = $this->table->where('applicationid', $applicationid)->find();
        $this->assign('data', $data);

        //颜色的变化
        $this->color_baogao($applicationid,$option);


        $this->assign('type', 5);
        $language = '英文';
        $this->assign('language', $language);
        return $this->fetch();
    }

    public function save()
    {
        $userid = session('auth_id');
        $applicationid = input('request.id');

        $map['creator'] = $userid;//创建人
        $map['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $map['applicationid'] = input('request.id');//委托单id
        $map['branch'] = input('request.branch');//分支


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

    //增加变更
    public function add_capital()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['time'] = '';
        $info['before'] = '';
        $info['after'] = '';
        $info['item'] = '';
        $data = Db::table('Z_TB_companyInformation_Change')->where('applicationid', $info['applicationid'])->order('id', 'desc')->select();
        if ($data) {
            foreach ($data as $v) {
                if (!$v['senddate']) {

                    return return_array_result(0, lang('新增失败'));
                    break;
                } else {
                    $tableName = Db::table('Z_TB_companyInformation_Change');
                    $res = $tableName->insertGetId($info);
                    if ($res) {
                        $result = Db::table('Z_TB_companyInformation_Change')->where('id', $res)->find();
                        return return_array_result(1, lang('新增成功'), '', $result);
                    }
                }
            }
        } else {
            $tableName = Db::table('Z_TB_companyInformation_Change');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = Db::table('Z_TB_companyInformation_Change')->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'), '', $result);
            }
        }


    }

    //删除
    public function capital_del()
    {
        $id = input('post.id');
        $tableName = Db::table('Z_TB_companyInformation_Change');
        $res = $tableName->where('id=' . $id)->delete();
        if ($res !== false) {
            $result = 1;
            return return_array_result(1, lang('删除成功'), '', $result);
        }

    }

    //保存
    public function capital_update1()
    {
        $time = $_POST['xs1'];
        $res = strtotime($time);
        $date = date('Y-m-d', $res);
        if ($time != $date) {
            return return_array_result(1, lang('日期不对'));
        }
        $info['time'] = $_POST['xs1'];
        $info['before'] = $_POST['xs2'];
        $info['after'] = $_POST['xs3'];
        $info['item'] = $_POST['xs4'];
        $info['senddate'] = date('Y-m-d H:i:s', time());//保存时间
        $applicationid = $_POST['applicationid'];
        $id = $_POST['id'];
        $tableName = Db::table('Z_TB_companyInformation_Change');

        $re = $tableName->where("id={$id}")->update($info);
        if ($re !== false) {
            $result = $tableName->where('applicationid', $applicationid)->select();
            return return_array_result(1, lang('保存成功'), '', $result);
        }
    }


    //增加股东情况
    public function add_share()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['member'] = '';
        $info['money'] = '';
        $info['rate'] = '';
        $info['investType'] = '';
        $data = Db::table('Z_TB_ShareholderDetail')->where('applicationid', $info['applicationid'])->order('id', 'desc')->select();
        if ($data) {
            foreach ($data as $v) {
                if (!$v['senddate']) {
                    return return_array_result(0, lang('新增失败'));
                    break;
                } else {
                    $tableName = Db::table('Z_TB_ShareholderDetail');
                    $res = $tableName->insertGetId($info);
                    if ($res) {
                        $result = $tableName->where('id', $res)->find();
                        return return_array_result(1, lang('新增成功'), '', $result);
                    }
                }
            }
        } else {
            $tableName = Db::table('Z_TB_ShareholderDetail');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'), '', $result);
            }
        }


    }

    //保存
    public function share_update()
    {
        $info['member'] = $_POST['xs1'];
        $info['money'] = $_POST['xs2'];
        $info['rate'] = $_POST['xs3'];
        $info['investType'] = $_POST['xs4'];
        $info['senddate'] = date('Y-m-d H:i:s', time());//保存时间
        $id = $_POST['id'];
        $applicationid = $_POST['applicationid'];
        $re = Db::table('Z_TB_ShareholderDetail')->where("id={$id}")->update($info);
        if ($re) {
            $result = Db::table('Z_TB_ShareholderDetail')->where('applicationid', $applicationid)->select();
            $total_money = 0;//出资额总计
            $total_rate = 0;//股权总计
            foreach ($result as $v) {
                $total_money += $v['money'];
                $total_rate += $v['rate'];
            }
            return return_array_result(1, lang('保存成功'), '', ['total_money' => $total_money, 'total_rate' => $total_rate]);
        }
    }

    //删除
    public function share_del()
    {
        $id = input('post.id');
        $applicationid = input('post.applicationid');
        $res = Db::table('Z_TB_ShareholderDetail')->where('id=' . $id)->delete();
        if ($res !== false) {
            $total_money = 0;//出资额总计
            $total_rate = 0;//股权总计
            $result = Db::table('Z_TB_ShareholderDetail')->where('applicationid', $applicationid)->select();
            foreach ($result as $v) {
                $total_money += $v['money'];
                $total_rate += $v['rate'];
            }
            return return_array_result(1, lang('删除成功'), '', ['total_money' => $total_money, 'total_rate' => $total_rate]);
        }

    }


    //增加董事
    public function add_board()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['name'] = '';
        $info['position'] = '';
        $info['sex'] = '';
        $info['age'] = '';
        $info['certificate'] = '';
        $info['house'] = '';
        $data = Db::table('Z_TB_BoardDetail')->where('applicationid', $info['applicationid'])->order('id', 'desc')->select();
        if ($data) {
            foreach ($data as $v) {
                if (!$v['senddate']) {
                    return return_array_result(0, lang('新增失败'));
                    break;
                } else {
                    $tableName = Db::table('Z_TB_BoardDetail');
                    $res = $tableName->insertGetId($info);
                    if ($res) {
                        $result = $tableName->where('id', $res)->find();
                        return return_array_result(1, lang('新增成功'), '', $result);
                    }
                }
            }
        } else {
            $tableName = Db::table('Z_TB_BoardDetail');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'), '', $result);
            }
        }

    }

    //删除
    public function board_del()
    {
        $id = input('post.id');
        $res = Db::table('Z_TB_BoardDetail')->where('id=' . $id)->delete();
        if ($res !== false) {
            return return_array_result(1, lang('删除成功'));
        }

    }

    //保存
    public function board_update()
    {
        $info['name'] = $_POST['xs1'];
        $info['position'] = $_POST['xs2'];
        $info['sex'] = $_POST['xs3'];
        $info['age'] = $_POST['xs4'];
        $info['certificate'] = $_POST['xs5'];
        $info['house'] = $_POST['xs6'];
        $info['senddate'] = date('Y-m-d H:i:s', time());//保存时间
        $id = $_POST['id'];
        $re = Db::table('Z_TB_BoardDetail')->where("id={$id}")->update($info);
        if ($re) {
            return return_array_result(1, lang('保存成功'));
        }
    }


    //增加对外投资/其他公司任职/企业对外投资
    public function add_invest()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['companyName'] = '';
        $info['registerCode'] = '';
        $info['registerCapital'] = '';
        $info['registerDate'] = '';
        $info['status'] = '';

        $info['tableKey'] = input('post.key');
        $key = input('post.key');
        if ($key == 'represent') {
            $info['rate'] = '';
            $tableName = Db::table('Z_TB_RepresentInvest');
        } else if ($key == 'position') {
            $info['key1'] = '';
            $info['key2'] = '';
            $tableName = Db::table('Z_TB_RepresentPosition');
        } else {
            $info['key1'] = '';
            $info['key2'] = '';
            $tableName = Db::table('Z_TB_CompanyInvest');
        }
        $res = $tableName->insertGetId($info);
        if ($res) {
            $result = $tableName->where('id', $res)->find();
            return return_array_result(1, lang('新增成功'), '', $result);
        }
    }

    //删除
    public function invest_del()
    {
        $id = input('post.id');
        $key = input('post.key');
        if ($key == 'represent') {
            $tableName = Db::table('Z_TB_RepresentInvest');
        } else if ($key == 'position') {
            $tableName = Db::table('Z_TB_RepresentPosition');
        } else {
            $tableName = Db::table('Z_TB_CompanyInvest');
        }
        $res = $tableName->where('id=' . $id)->delete();
        if ($res !== false) {
            $result = 1;
            return return_array_result(1, lang('删除成功'), '', $result);
        }

    }


	//保存(翻译)
	public function saveEng(){
		$applicationid=input('request.id');

		$map['branchEng']=input('request.branchEng');//分支
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


    //保存
    public function invest_update()
    {
        $info['companyName'] = $_POST['xs1'];
        $info['registerCode'] = $_POST['xs2'];
        $info['registerCapital'] = $_POST['xs3'];
        $info['registerDate'] = $_POST['xs4'];
        $info['status'] = $_POST['xs5'];
        $id = $_POST['id'];
        $key = input('post.key');
        if ($key == 'represent') {
            $info['rate'] = $_POST['xs6'];
            $tableName = Db::table('Z_TB_RepresentInvest');
        } else if ($key == 'position') {
            $info['key1'] = $_POST['xs6'];
            $info['key2'] = $_POST['xs7'];
            $tableName = Db::table('Z_TB_RepresentPosition');
        } else {
            $info['key1'] = $_POST['xs6'];
            $info['key2'] = $_POST['xs7'];
            $tableName = Db::table('Z_TB_CompanyInvest');
        }
        $re = $tableName->where("id={$id}")->update($info);
        if ($re) {
            $result = $tableName->where('id', $id)->select();
            return return_array_result(1, lang('保存成功'), '', $result);
        }
    }



}