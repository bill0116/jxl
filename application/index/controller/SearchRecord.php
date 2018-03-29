<?php
/**
 * func:调档记录
 */
namespace app\index\controller;

use think\Db;

class SearchRecord extends Common
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
        $this->table = Db::table('Z_TB_DiaoDang_Detail');
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

        //颜色的变化
        $this->color_baogao($applicationid, $option);

        //调档明细
        $list = $this->table
            ->alias('a')
            ->field('a.*,b.name')
            ->join('Z_TB_Channel_DiaoDang b', 'a.company=b.code', 'left')
            ->where('applicationid=' . $applicationid)
            ->order('id', 'desc')
            ->select();
        $this->assign('list', $list);

        $this->assign('type', 2);
        return $this->fetch();
    }


	//深度报告
	public function index1(){
		$option=input('get.option');
		$applicationid=input('get.applicationid');
		$activeid=input('get.activeid');
		$this->assign('option',$option);
		$this->assign('activeid',$activeid);
		$this->assign('applicationid',$applicationid);

        //颜色的变化
        $this->color_baogao1($applicationid,$option);

        //调档明细
        $list = $this->table
            ->alias('a')
            ->field('a.*,b.name')
            ->join('Z_TB_Channel_DiaoDang b', 'a.company=b.code', 'left')
            ->where('applicationid=' . $applicationid)
            ->order('id', 'desc')
            ->select();
        $this->assign('list', $list);

        $this->assign('type', 2);
        return $this->fetch();
    }

}