<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/11/27
 * Func: 资信报告订单批复通知
 * Table:Z_TB_Sinosure_CreditReportApprove
 */
namespace app\index\controller;

use think\Db;

class ReplyNotice extends Common{
    /*
     *  客户批复结果查询
     *  关联下单表查询详情数据
     * */
    public function index(){
        if ($this->request->isPost()) {
            $where = " 1=1 ";
            if (!empty(input('post.buyerNO')))
                $where .= " and b.reportbuyerNo like '%" . trim(input('post.buyerNO')) . "%'";
            if (!empty(input('post.buyerEngName')))
                $where .= " and b.reportCorpEngName like '%" . trim(input('post.buyerEngName')) . "%'";
            $list = Db::table('Z_TB_Sinosure_CreditReportApprove')
                ->alias('a')
                ->field('a.*,b.reportbuyerNo,b.reportCorpEngName')
                ->join('Z_TB_Sinosure_EntrustInput b','a.corpserialno=b.ID','left')
                ->where($where)
                ->paginate(10, false, ['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
            if ($list) {
                return return_array_result(1, lang('查询成功'),'', ['list' => $list->toArray()['data'], 'page' => $list->render()]);
            } else {
                return return_array_result(0, lang('查询失败'));
            }
        } else {
            return $this->fetch();
        }
    }
}
