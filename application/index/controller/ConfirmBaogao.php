<?php
/**
func:报告确认
 */
namespace app\index\controller;

use think\Db;
class ConfirmBaogao extends Common
{
	public function get_list(){
		$userid=session('auth_id');
		$status='6';
		$data=$this->getWTD_list($userid,$status);
		$this->assign('data',$data);
		return $this->fetch();
	}
}