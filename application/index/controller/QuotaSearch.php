<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/11/23
 * Func: 信保限额查询
 */
namespace app\index\controller;

use think\Db;

class QuotaSearch extends Common{
    public function index(){
        if($this->request->isPost()){
            $where = "1=1 ";
            if (!empty(input('post.OrganizationFlag')))
                $where .= " and OrganizationFlag ='" . input('post.OrganizationFlag')."'";
            if (!empty(input('post.payMode')))
                $where .= " and payMode='" . input('post.payMode') . "'";
            if (!empty(input('post.buyerNO')))
                $where .= " and a.buyerNO like '%" . trim(input('post.buyerNO')) . "%'";
            if (!empty(input('post.buyerEngName')))
                $where .= " and a.buyerEngName like '%" . trim(input('post.buyerEngName')) . "%'";
            if (!empty(input('post.TBGS')))
                $where .= " and a.TBGS ='" . trim(input('post.TBGS')) . "'";
            $list = Db::table('Z_TB_QuotaActual')
                ->alias('a')
                ->field('a.OrganizationFlagText,a.TBGS,a.quotaNo,a.buyerNO,dbo.[S_F_GetOrganizationName](Organization) as orgtext,a.kunnr,
                a.buyerEngName,b.buyerRegNo,a.buyerEngName,dbo.Z_F_GetEDICountryNameByCountryCode(buyerCountryCode,\'0\') as buyercountry,
                dbo.S_F_GetGentalCodeText(219,paymode)  as  paymodetext ,a.quotaSum,a.payTerm,a.CalLimit,a.CalTerm,a.quotaBalance,
                a.PolicyNO, dbo.Z_F_GetQuotaLimitUsed(a.kunnr,OrganizationFlag,IfLC,bankswift) as GSYYED,a.bankEngName,a.bankSwift,b.buyerEngAddress')
                ->join('Z_TB_Sinosure_Buyer b', 'a.buyerno=b.buyerno','left')
                ->where($where)
                ->paginate(10, false, ['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
            if($list){
                $data=$list->toArray()['data'];
                $page= $list->render();
                foreach ($data as $k => $value) {
                    $data[$k]['GSSYED'] = $value['CalLimit'] - $value['GSYYED'];
                }
                return return_array_result(1,lang('查询成功'),'',['list'=>$data,'page'=> $page]);
            }else{
                return return_array_result(0,lang('查询失败'));
            }
        }else{
            return $this->fetch();
        }
    }
}