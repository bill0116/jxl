<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/11/28
 * Func: 付款指数统计报表
 */
namespace app\index\controller;

use think\Db;

class PaymentCount extends Common{
    /*
     * @ 报表查询
     * */
    public function index(){
        if($this->request->isPost()){
            $where = " buyerno is not null ";
            if (!empty(input('post.GJAHR')))
                $where .= " and GJAHR ='" . input('post.GJAHR')."'";
            if (!empty(input('post.MONAT')))
                $where .= " and MONAT='" . input('post.MONAT') . "'";
            if (!empty(input('post.buyerNO')))
                $where .= " and buyerNO like '%" . trim(input('post.buyerNO')) . "%'";
            if (!empty(input('post.buyerEngName')))
                $where .= " and buyerEngName like '%" . trim(input('post.buyerEngName')) . "%'";
            if (!empty(input('post.bukrs')))
                $where .= " and bukrs ='" . trim(input('post.bukrs')) . "'";
            $list = Db::table('z_tb_report_fkzs')
                ->where($where)
                ->paginate(10, false, ['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
            if($list){
                $data=$list->toArray()['data'];
                $page= $list->render();
                return return_array_result(1,lang('查询成功'),'',['list'=>$data,'page'=> $page]);
            }else{
                return return_array_result(0,lang('查询失败'));
            }
        }else{
            return $this->fetch();
        }
    }
}