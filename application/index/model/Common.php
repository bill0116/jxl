<?php
namespace app\index\model;

use think\Model;
use think\Db;

class Common extends Model
{
    /*
     * @ 获得流程名称
     * */
    public function getFlowName(){
        $result=Db::name('FcFlow')
            ->field('*')
            ->order('flowIndex')
            ->select();
        return $result;
    }
    /*
     * @ 通用方法(通过代码字段)
     * */
    public function getCommon($pid)
    {
        $result = Db::table('S_TB_generalcode')
            ->field('GeneralCode,CodeText,CodeIndex')
            ->where('PID', $pid)
            ->cache(true)
            ->order('CodeIndex', 'asc')
            ->select();
        return $result;
    }

    /*
     * @ 商品类别
     * */
    public function getGoodsCode()
    {
        $result = Db::table('Z_TB_GOODCODES')
            ->cache(true)
            ->select();
        return $result;
    }

    /*
     * @ 海关编码
     * */
    public function getCustomsCode()
    {
        $result = Db::table('Z_TB_CustomCode')
            ->field('Code,concat(Code,' - ',Text) as Text')
            ->cache(true)
            ->select();
        return $result;
    }

    /*
     * @ 贸易结算方式
     * */
    public function getTradeCode()
    {
        $result = Db::table('S_TB_GeneralCode')
            ->field('GeneralCode,CodeText,CodeIndex')
            ->where('PID', 336)
            ->cache(true)
            ->order('CodeIndex', 'asc')
            ->select();
        return $result;
    }

    /*
    * @ 国家名称
    * */
    public function getCountryName($code)
    {
        if ($code) {
            $result = Db::table('Z_TB_countryEDI')
                ->where('countryCode', $code)
                ->value('countryName');
        } else {
            $result = '';
        }
        return $result;
    }
    /*
     * @ 运输方式
     * */
    public function getTrafficCode(){

    }
    /*
     * @ 评估模型
     * */
    public function getModel(){
        $result=Db::name('CreditevaluateModel')
            ->field('ModelID,ModelName')
            ->select();
        return $result;
    }
    /*
     * @ 超期应收款日报-公司
     * */
    public function getBurksName(){
        $result = Db::table('Z_TB_REPORT_Sales_Month')
            ->field('distinct dbo.Z_F_GetBukrsName(bukrs) as bukrscode,
            dbo.Z_F_GetBukrsName(bukrs) as bukrsname')
            ->order('bukrsname')
            ->select();
        return $result;
    }
    /*
     * @ 超期应收款日报-海外分公司代码
     * */
    public function getHwBurksName(){
        $where=" b.UserID=".session('auth_id')." and isnull(c.bukrs,'')<>''  ";
        $result = Db::table('S_TB_User_Role_Detail')
            ->alias('a')
            ->field('distinct isnull(c.bukrs,\'\') as Code,
            isnull(c.bukrs,\'\') as Name')
            ->join('S_TB_User_Role b','a.userroleid=b.userroleid')
            ->join('S_TB_Organization c','a.OrganizationID=c.id')
            ->where($where)
            ->order('Name')
            ->select();
        return $result;
    }

    /*
  * @ 获取调档渠道
  * */
    public function getChannel()
    {
        $result = Db::table('Z_TB_Channel_DiaoDang')
            ->field('*')
            ->cache(true)
            ->order('id', 'asc')
            ->select();
        return $result;
    }

    /*
* @ 获取调档内容
* */
    public function getContent()
    {
        $result = Db::table('Z_TB_Content_DiaoDang')
            ->field('*')
            ->cache(true)
            ->order('id', 'asc')
            ->select();
        return $result;
    }

    /*
* @ 获取报告制单人
* */
    public function getMakePerson()
    {
        $result = Db::table('S_TB_User_Make')
            ->field('*')
            ->order('UserID', 'asc')
            ->select();
        return $result;
    }

    /*
* @ 获取申请人
* */
    public function getPerson()
    {
        $result = Db::table('S_TB_User')
            ->field('*')
            ->order('UserID', 'asc')
            ->select();
        return $result;
    }
}