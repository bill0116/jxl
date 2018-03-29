<?php
/**
func:报告翻译确认
 */
namespace app\index\controller;

use think\Db;
class ConfirmTranslate extends Common
{
	public function get_list(){
		$userid=session('auth_id');
		$status='8';
		$data=$this->getWTD_list($userid,$status);
		$this->assign('data',$data);
		return $this->fetch();
	}
}
