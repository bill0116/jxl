<?php
/**
 * func:制作报告
 */
namespace app\index\controller;

use think\Db;

class MakeBaogao extends Common
{
    protected $beforeActionList = ['getAttachList'];

    protected function getAttachList()
    {
        $applicationid = input('get.applicationid');
        $this->assign('attachList', $this->getAttachFile($this->getAttachID($applicationid)));
    }
	//标准报告
	public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);
        $this->assign('type', 1);


        //委托单信息
        $resultData = Db::table('Z_TB_WTDApply')->where('id', $applicationid)->find();
        $this->assign('data', $resultData);

        //颜色的变化
        $this->color_baogao($applicationid, $option);

        return $this->fetch();
    }





	//深度报告
	public function index1()
	{
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);
		$this->assign('type',1);

        //委托单信息
        $resultData = Db::table('Z_TB_WTDApply')->where('id', $applicationid)->find();
        $this->assign('data', $resultData);

        //颜色的变化
        $this->color_baogao1($applicationid,$option);

        return $this->fetch();
    }


    public function get_list()
    {
        $userid = session('auth_id');
        $status = '5';
        $data = $this->getWTD_list($userid, $status);
        $this->assign('data', $data);
        return $this->fetch();
    }
}