<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/11/28
 * Func: 超期应收款日报
 */
namespace app\index\controller;

use think\Db;

class OverdueReceive extends Common
{
    /*
     * @ 超期应收款日报（原币种）报表查询
     * */
    public function index()
    {
        if ($this->request->isPost()) {
            $where = "where 1=1 ";
            if(!empty(input('post.reportDate')))
                $where .= " and reportDate='".trim(input('post.reportDate'))."'";
            if(!empty(input('post.bukrsname')))
                $where .= " and bukrsname='".trim(input('post.bukrsname'))."'";
            $countScore = Db::query("SELECT COUNT(*) AS count FROM (SELECT a.bukrsname, a.KUNNR, a.BuyerNo, a.Name, a.fph
                                , CASE iflc WHEN '0' THEN '非证' ELSE '信用证' END AS jsfs, WAERS, cqje, dbo.Z_F_GetCurrencyRates(waers, 'USD') * cqje AS usd, convert(varchar(10), CAST(yskr AS datetime), 120) AS yskr
                                , datediff(day, yskr, reportdate) AS overday, cpdl, xsjl, xsqy, CASE WHEN b.status IS NULL THEN '超期' WHEN b.Status = '报损' THEN '风险' WHEN b.Status = '索赔' THEN '呆死' END AS fxdj
                                , a.vtext, a.keyvalue, cqyy, jjsm, yjjjrq
                            FROM Z_TB_REPORT_OverAmount_Daily a LEFT JOIN dbo.Z_TB_REPORT_OverAmount_Status b ON a.bukrs = b.bukrs
                            AND a.FPH = b.FPH LEFT JOIN (SELECT keyvalue, cqyy, jjsm, yjjjrq
                                FROM Z_TB_REPORT_OverAmount_All
                                ) c ON a.keyvalue = c.keyvalue $where
                            UNION
                            SELECT bukrsname + '合计' AS bukrsname, NULL AS KUNNR, NULL AS BuyerNo, NULL AS Name, NULL AS fph
                                , NULL AS jsfs, NULL AS WAERS, NULL AS cqje, SUM(dbo.Z_F_GetCurrencyRates(waers, 'USD') * cqje) AS usd, NULL AS yskr
                                , NULL AS overday, NULL AS cpdl, NULL AS xsjl, NULL AS xsqy, NULL AS fxdj
                                , NULL AS vtext, NULL AS keyvalue, NULL AS cqyy, NULL AS jjsm, NULL AS yjjjrq
                            FROM Z_TB_REPORT_OverAmount_Daily  $where
                            GROUP BY bukrsname
                            UNION
                            SELECT '总计' AS bukrsname, NULL AS KUNNR, NULL AS BuyerNo, NULL AS Name, NULL AS fph
                                , NULL AS jsfs, NULL AS WAERS, NULL AS cqje, SUM(dbo.Z_F_GetCurrencyRates(waers, 'USD') * cqje) AS usd, NULL AS yskr
                                , NULL AS overday, NULL AS cpdl, NULL AS xsjl, NULL AS xsqy, NULL AS fxdj
                                , NULL AS vtext, NULL AS keyvalue, NULL AS cqyy, NULL AS jjsm, NULL AS yjjjrq
                            FROM Z_TB_REPORT_OverAmount_Daily   $where)  D");
            $page = empty(input('post.page/d')) ? 1 : input('post.page/d');
            $prePage = ($page - 1) * 10 + 1;
            $nextPage = $page * 10;
            $resultList = Db::query("
                        SELECT  T1.* FROM (
                          SELECT thinkphp.*, ROW_NUMBER() OVER ( ORDER BY bukrsname) AS ROW_NUMBER FROM
                          (SELECT a.bukrsname, a.KUNNR, a.BuyerNo, a.Name, a.fph
                                , CASE iflc WHEN '0' THEN '非证' ELSE '信用证' END AS jsfs, WAERS, cqje, dbo.Z_F_GetCurrencyRates(waers, 'USD') * cqje AS usd, convert(varchar(10), CAST(yskr AS datetime), 120) AS yskr
                                , datediff(day, yskr, reportdate) AS overday, cpdl, xsjl, xsqy, CASE WHEN b.status IS NULL THEN '超期' WHEN b.Status = '报损' THEN '风险' WHEN b.Status = '索赔' THEN '呆死' END AS fxdj
                                , a.vtext, a.keyvalue, cqyy, jjsm, yjjjrq
                            FROM Z_TB_REPORT_OverAmount_Daily a LEFT JOIN dbo.Z_TB_REPORT_OverAmount_Status b ON a.bukrs = b.bukrs
                            AND a.FPH = b.FPH LEFT JOIN (SELECT keyvalue, cqyy, jjsm, yjjjrq
                                FROM Z_TB_REPORT_OverAmount_All
                                ) c ON a.keyvalue = c.keyvalue   $where
                            UNION
                            SELECT bukrsname + '合计' AS bukrsname, NULL AS KUNNR, NULL AS BuyerNo, NULL AS Name, NULL AS fph
                                , NULL AS jsfs, NULL AS WAERS, NULL AS cqje, SUM(dbo.Z_F_GetCurrencyRates(waers, 'USD') * cqje) AS usd, NULL AS yskr
                                , NULL AS overday, NULL AS cpdl, NULL AS xsjl, NULL AS xsqy, NULL AS fxdj
                                , NULL AS vtext, NULL AS keyvalue, NULL AS cqyy, NULL AS jjsm, NULL AS yjjjrq
                            FROM Z_TB_REPORT_OverAmount_Daily    $where
                            GROUP BY bukrsname
                            UNION
                            SELECT '总计' AS bukrsname, NULL AS KUNNR, NULL AS BuyerNo, NULL AS Name, NULL AS fph
                                , NULL AS jsfs, NULL AS WAERS, NULL AS cqje, SUM(dbo.Z_F_GetCurrencyRates(waers, 'USD') * cqje) AS usd, NULL AS yskr
                                , NULL AS overday, NULL AS cpdl, NULL AS xsjl, NULL AS xsqy, NULL AS fxdj
                                , NULL AS vtext, NULL AS keyvalue, NULL AS cqyy, NULL AS jjsm, NULL AS yjjjrq
                            FROM Z_TB_REPORT_OverAmount_Daily    $where  )
                           AS thinkphp
                        )  AS T1 WHERE (T1.ROW_NUMBER BETWEEN $prePage  AND  $nextPage)");
            $list = new \think\paginator\driver\Bootstrap($resultList, 10, $page, $countScore[0]['count'], false, ['path' => 'javascript:doSearch([PAGE]);']);
            if ($list) {
                $data = $list->toArray()['data'];
                $page = $list->render();
                return return_array_result(1, lang('查询成功'), '', ['list' => $data, 'page' => $page]);
            } else {
                return return_array_result(0, lang('查询失败'));
            }
        } else {
            return $this->fetch();
        }
    }

    public function index1()
    {
        if ($this->request->isPost()) {
            $where = "where 1=1 ";
            $subQuery = Db::table('Z_TB_REPORT_OverAmount_All')
                ->field('keyvalue, cqyy, jjsm, yjjjrq')
                ->buildSql();
            $list = Db::field('a.bukrsname, a.KUNNR, a.BuyerNo, a.Name, a.fph
                , CASE iflc WHEN \'0\' THEN \'非证\' ELSE \'信用证\' END AS jsfs, WAERS, cqje, dbo.Z_F_GetCurrencyRates(waers, \'USD\') * cqje AS usd, convert(varchar(10), CAST(yskr AS datetime), 120) AS yskr
                , datediff(day, yskr, reportdate) AS overday, cpdl, xsjl, xsqy, CASE WHEN b.status IS NULL THEN \'超期\' WHEN b.Status = \'报损\' THEN \'风险\' WHEN b.Status = \'索赔\' THEN \'呆死\' END AS fxdj
                , a.vtext, a.keyvalue, cqyy, jjsm, yjjjrq')
                ->table('Z_TB_REPORT_OverAmount_Daily')
                ->alias('a')
                ->join('Z_TB_REPORT_OverAmount_Status b', 'a.bukrs = b.bukrs and a.FPH = b.FPH', 'left')
                ->join($subQuery . 'c', 'a.keyvalue = c.keyvalue', 'left')
                ->union(function ($query) {
                    $query->field('bukrsname + \'合计\' AS bukrsname, NULL AS KUNNR, NULL AS BuyerNo, NULL AS Name, NULL AS fph
                , NULL AS jsfs, NULL AS WAERS, NULL AS cqje, SUM(dbo.Z_F_GetCurrencyRates(waers, \'USD\') * cqje) AS usd, NULL AS yskr
                , NULL AS overday, NULL AS cpdl, NULL AS xsjl, NULL AS xsqy, NULL AS fxdj
                , NULL AS vtext, NULL AS keyvalue, NULL AS cqyy, NULL AS jjsm, NULL AS yjjjrq')->group('bukrsname')->table('Z_TB_REPORT_OverAmount_Daily');
                })
                ->union(function ($query) {
                    $query->field('\'总计\' AS bukrsname, NULL AS KUNNR, NULL AS BuyerNo, NULL AS Name, NULL AS fph
                , NULL AS jsfs, NULL AS WAERS, NULL AS cqje, SUM(dbo.Z_F_GetCurrencyRates(waers, \'USD\') * cqje) AS usd, NULL AS yskr
                , NULL AS overday, NULL AS cpdl, NULL AS xsjl, NULL AS xsqy, NULL AS fxdj
                , NULL AS vtext, NULL AS keyvalue, NULL AS cqyy, NULL AS jjsm, NULL AS yjjjrq')->table('Z_TB_REPORT_OverAmount_Daily');
                })
                ->order('bukrsname')
                ->paginate(10, false, ['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
            if ($list) {
                $data = $list->toArray()['data'];
                $page = $list->render();
                return return_array_result(1, lang('查询成功'), '', ['list' => $data, 'page' => $page]);
            } else {
                return return_array_result(0, lang('查询失败'));
            }
        } else {
            return $this->fetch();
        }
    }
}