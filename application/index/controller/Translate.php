<?php
/**
func:翻译资信报告
 */
namespace app\index\controller;

use think\Db;
class Translate extends Common
{
	public function get_list(){
		$userid=session('auth_id');
		$status='7';
		$data=$this->getWTD_list($userid,$status);
		$this->assign('data',$data);
		return $this->fetch();
	}

}
