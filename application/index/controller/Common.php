<?php
/**
 * 后台公共控制器
 * 为什么要继承CommonController？
 * 因为CommonController初始化函数验证了登陆以及读取了导航菜单
 * 以及封装了许多公用功能函数，所以综合考虑继承比较好。
 */
namespace app\index\controller;

use think\Db;
use think\Controller;

class Common extends Controller
{
    /*
     * @ 继承控制器基类Controller
     * @ 初始化initialize方法无返回值
     * */
    protected function initialize()
    {
        $authID = session(config('user_login_key'));
        if (!isset($authID)) {
            $this->redirect(config('user_login_way'));
        } else {
            $this->assign('left_mune', tree_nav($this->assignMenu(), $this->assignBadgecount()));
        }
    }

    /*
     * @ 根据权限获得按钮
     * */
    protected function getMenu($userID)
    {
        $lisMenu = Db::query("select distinct c.* from  (select distinct userid,roleid from [S_TB_User_Role]) a
        left join [S_TB_Role_Permission] b on a.RoleID=b.roleid
        left  join S_tb_menu c on b.PermissionKey=c.PermissionKey and b.PermissionLevel=c.PermissionLevel
        where a.userid=" . $userID . "  and c.IsUsed=0 and c.PermissionKey!=''
        union
        select * from S_TB_Menu where PermissionKey='' and PermissionLevel=''  and IsUsed=0  order by MenuIndex");
        return $lisMenu;
    }

    /*
     * @ 数组转化成树
     * */
    protected function assignMenu()
    {
        $leftMenu = list_to_tree(self::getMenu(session(config('user_login_key'))), 0);
        return $leftMenu;
    }

    /*
     * @ 辅助数组转化树
     * */
    protected function assignBadgecount()
    {
        $badgeCount = 100;
        return $badgeCount;
    }

    /*
     * @权限获取组织机构
     * @OrganizationTitle
     * @去除停用的组织机构
     * */
    public function listOrganizations($user_id)
    {
        $result = Db::query("select distinct a.* from S_TB_Organization a inner join dbo.S_TB_User_Role_Detail b on
        a.ID=b.OrganizationID inner join dbo.S_TB_User_Role c on
        b.UserRoleID=c.UserRoleID where c.UserID=$user_id and OrganizationTitle not like '%停%'
        union
        select *from S_TB_Organization where id=1");
        return self::organizationTree($result);
    }

    /*
     *@ 权限获取组织机构
     * */
    public static function organizationTree(&$list, $pid = 0, $level = 0, $html = ' |----')
    {
        static $tree = array();
        foreach ($list as $v) {
            if ($v['PID'] == $pid) {
                $v['sort'] = $level;
                $v['OrganizationTitle'] = str_repeat($html, $level) . $v['OrganizationTitle'];
                $tree[] = $v;
                self::organizationTree($list, $v['ID'], $level + 1);
            }
        }
        return $tree;
    }

    /*
     * @ 客户代码
     * */
    public function getSinosureBuyer()
    {
        if (request()->isPost()) {
            $buyerKunnr = input('param.buyerNo');
            $where = empty($buyerKunnr) ? "" : "and buyerno like '%$buyerKunnr%'";
            $result = Db::query("select top 20 buyerno, buyerName, corpBuyerNo,buyerTel,buyerPerson,entrustor,specicalRequire,finishDay from Z_TB_Sinosure_Buyer where 1=1 $where");
            if ($result) {
                return return_array_result(1, lang('查询成功!'), '', $result[0]);
            } else {
                return return_array_result(0, lang('未查到此客户!'));
            }
        } else {
            $buyerKunnr = input('param.buyerKunnr');
            $where = empty($buyerKunnr) ? "" : "and buyerno like '%$buyerKunnr%'";
            $buyerName = input('param.buyerName');
            $where .= empty($buyerName) ? "" : "and buyerName like '%$buyerName%'";
            $result = Db::query("select top 20 buyerno, buyerName, corpBuyerNo,buyerTel,buyerPerson,entrustor,specicalRequire,finishDay from Z_TB_Sinosure_Buyer where 1=1 $where");
            if ($result) {
                $html = "";
                foreach ($result as $v) {
                    $html .= "<tr role='row'><td></td><td>" . $v['buyerno'] . "</td><td>" . $v['buyerName'] . "</td><td>";
                    $html .= "<input type='button'
                     onclick=\"sureCheckBuyerNo('" .htmlspecialchars($v['buyerno']) . "','" . htmlspecialchars($v['buyerName']) . "','" . htmlspecialchars($v['buyerTel']) . "','" .  htmlspecialchars($v['buyerPerson']) . "','" .  htmlspecialchars($v['entrustor']) . "','" .  htmlspecialchars($v['specicalRequire']) . "','".htmlspecialchars($v['finishDay']) . "')\"
                      value='选择'></td></tr>";
                }
                return return_array_result(1, lang('查询成功!'), '', $html);
            } else {
                return return_array_result(0, lang('查询成功!'));
            }

        }
    }

    /*
     * @ 国家代码
     * @ code:CountryKunnr
     * @ return array().
     * */
    public function getCountryName()
    {
        if (request()->isPost()) {
            $CountryCode = input('param.CountryCode');
            $where = empty($CountryCode) ? "" : "and CountryCode like '%$CountryCode%'";
            $result = Db::query("select top 1 CountryCode,CountryName  from  Z_TB_countryEDI  where 1=1  $where");
            if ($result) {
                return return_array_result(1, lang('查询成功!'), '', $result[0]);
            } else {
                return return_array_result(0, lang('未查到此客户!'));
            }
        } else {
            $key = input('param.key');
            $CountryKunnr = input('param.CountryKunnr');
            $where = empty($CountryKunnr) ? "" : "and CountryCode like '%$CountryKunnr%'";
            $CountryName = input('param.CountryName');
            $where .= empty($CountryName) ? "" : "and CountryName like '%$CountryName%'";
            $result = Db::query("select top 10 CountryCode,CountryName  from  Z_TB_countryEDI  where 1=1  $where");
            if ($result) {
                $html = "";
                foreach ($result as $v) {
                    $html .= "<tr role='row'><td></td><td>" . $v['CountryCode'] . "</td><td>" . $v['CountryName'] . "</td><td>";
                    $html .= "<input type='button' onclick=\"sureCheckCountryName('" . htmlspecialchars($v['CountryCode']) . "','" . htmlspecialchars($v['CountryName']) . "','".$key."')\"
                      value='选择' style='width:auto;'></td></tr>";
                }
                return return_array_result(1, lang('查询成功!'), '', $html);
            } else {
                return return_array_result(0, lang('查询失败!'));
            }

        }

    }

    /*
     * @ 获取附件ID
     * */
    public function getAttachID($applicationid)
    {
        $attach = Db::table('Z_TB_WTDApply')
            ->where('id', $applicationid)
            ->value('Attaches');
        return $attach;
    }

    /*
     * @ 获取流程附件
     * */
    public function getAttachFile($attach)
    {
        $attachFile = array();
        if (!empty($attach)) {
            $attachFile = Db::name('Attach')
                ->where('id', 'in', $attach)
                ->order('ID', 'desc')
                ->select();
        }
        return $attachFile;
    }

    /*
     * @ 附件上传
     * @ string主表Attaches字段
     * @ 关联S_TB_Attach表
     * */
    public function uploadAttachFile()
    {
        if (request()->isAjax()) {
            $files = request()->file('file');
            $info = $files->move('public/uploads');
            if ($info) {
                $fileInfo = [
                    'AttachUrl' => str_replace('\\', '/', $info->getPathname()),
                    'AttachName' => $files->getInfo()['name'],
                    'CreateDate' => date('Y-m-d H:i:s', time()),
                    'CreatorName' => session('user.UserName'),
                    'Creator' => session('auth_id'),
                    'Size' => $files->getSize(),
                    'AttachNewName' => $info->getFilename(),
                ];
                $attach = Db::table('Z_TB_WTDApply')
                    ->where('id', input('applicationid'))
                    ->value('Attaches');
                $id = Db::name('Attach')->insertGetId($fileInfo);
                $attachString = empty($attach) ? $id : $attach . "," . $id;
                $attachStatus = Db::table('Z_TB_WTDApply')
                    ->where('id', input('applicationid'))
                    ->setField('Attaches', $attachString);
                if ($id && $attachStatus !== false) {
                    $attachList = $this->getAttachFile($attachString);
                    return return_array_result(1, lang('上传成功!'), '', $attachList);
                } else {
                    return return_array_result(0, lang('上传失败'));
                }
            } else {
                return return_array_result(0, lang('上传失败'));
            }
        } else {
            return return_array_result(0, lang('请求不合法'));
        }
    }

    /*
    * @ 附件删除
    * @ string主表Attaches字段
    * @ 关联S_TB_Attach表
    * */
    public function delAttachFile()
    {
        if ($this->request->isAjax()) {
            $attach = Db::table('Z_TB_WTDApply')
                ->where('id', input('applicationid'))
                ->value('Attaches');
            $attachString = implode(',', array_merge(array_diff(explode(',', $attach), array(input('post.id')))));
            $attachStatus = Db::table('Z_TB_WTDApply')
                ->where('id', input('applicationid'))
                ->setField('Attaches', $attachString);
            $res = Db::name('Attach')
                ->where('ID', input('post.id'))
                ->delete();
            if ($attachStatus !== false && $res !== false) {
                return return_array_result(1, lang('删除成功'));
            } else {
                return return_array_result(0, lang('删除失败'));
            }
        } else {
            return return_array_result(0, lang('请求不合法'));
        }
    }

    /*
     * @ 流程保存
     * @ $activeid流程id
     * */
    public function saveActiveFlow()
    {
        if ($this->request->isAjax()) {
            $map = request()->except(['id','ID', 'activeid', 'tasktype', 'routeid', 'userid', 'IsApproved'], 'post');
            if (isset($map['declaration'])) {
                $map['declaration'] =1;
            }
            if (isset($map['trafficCode']) && !empty($map['trafficCode'])) {
                $map['trafficCode'] = implode(',', $map['trafficCode']);
            }
            $param = Db::name('FcActive')
                ->field('ApplicationTable,ApplicationID')
                ->where('ActiveID', input('post.activeid'))
                ->find();
            $status = Db::table($param['ApplicationTable'])
                ->where('ID', $param['ApplicationID'])
                ->update($map);
            if ($status !== false) {
                return return_array_result(1, lang('保存成功'));
            } else {
                return return_array_result(0, lang('保存失败'));
            }
        } else {
            return return_array_result(0, lang('请求不合法'));
        }
    }

    //  特殊字符转换
    public  function HtmlEncode($fString)
    {
        if($fString!="")
        {
            $fString = str_replace( '>', '&gt;',$fString);
            $fString = str_replace( '<', '&lt;',$fString);
            $fString = str_replace( '/', '-',$fString);
            $fString = str_replace( chr(32), '&nbsp;',$fString);
            $fString = str_replace( chr(13), ' ',$fString);
            $fString = str_replace( chr(10) & chr(10), '<br>',$fString);
            $fString = str_replace( chr(10), '<BR>',$fString);
        }
        return $fString;
    }

    //获取委托单
    public function getWTD_list($userid,$status){
        if($status=='1'){
            $WTD_list = Db::query("select *,[dbo].[S_F_GetUserNameByUserID](creator) as creatorname from S_TB_FC_active
                                where flowid=37 and  creator={$userid} and status={$status}
                                order by senddate  desc");
        }else{
            $WTD_list = Db::query("select * ,[dbo].[S_F_GetUserNameByUserID](senduserid) as sendusername ,[dbo].[S_F_GetUserNameByUserID](activeuserid) as activeusername from S_TB_FC_active
                                where flowid=37 and  activeuserid={$userid} and status={$status}
                                order by senddate desc");
        }

        return $WTD_list;
    }

    //报表确认
    public function check($table,$applicationid){
        $map['isConfirm']='1';
        $ret=$table->where('applicationid',$applicationid)->update($map);
        if ($ret) {
            return return_array_result(1, lang('确认成功'));
        } else {
            return return_array_result(0, lang('确认失败'));
        }
    }

    //报表备注
    public function memo($table,$applicationid,$memo){
        $map['confirmMemo']=$memo;
        $ret=$table->where('applicationid',$applicationid)->update($map);
        if ($ret) {
            return return_array_result(1, lang('保存成功'));
        } else {
            return return_array_result(0, lang('保存失败'));
        }
    }

    //报表确认(翻译)
    public function checkEng($table,$applicationid){
        $map['isConfirmEng']='1';
        $ret=$table->where('applicationid',$applicationid)->update($map);
        if ($ret) {
            return return_array_result(1, lang('确认成功'));
        } else {
            return return_array_result(0, lang('确认失败'));
        }
    }

    //报表备注（翻译）
    public function memoEng($table,$applicationid,$memo){
        $map['confirmMemoEng']=$memo;
        $ret=$table->where('applicationid',$applicationid)->update($map);
        if ($ret) {
            return return_array_result(1, lang('保存成功'));
        } else {
            return return_array_result(0, lang('保存失败'));
        }
    }

    //获取返回的$color(标准报告)
    public function get_color($applicationid){
        //摘要信息
        $data3=Db::table('Z_TB_Summany_information')->where('applicationid',$applicationid)->find();
        $color3=$this->get_confirm($data3);
        $this->assign('color3',$color3);
        //企业概况1
        $data4=Db::table('Z_TB_company_information1')->where('applicationid',$applicationid)->find();
        $color4=$this->get_confirm($data4);
        $this->assign('color4',$color4);
        //企业概况2
        $data5=DB::table('Z_TB_Branch')->where('applicationid',$applicationid)->find();
        $color5=$this->get_confirm($data5);
        $this->assign('color5',$color5);
        //经营状况信息1
        $data6=Db::table('Z_TB_CompanyInformation_Run')->where('applicationid',$applicationid)->find();
        $color6=$this->get_confirm($data6);
        $this->assign('color6',$color6);
        //运营信息2
        $data7=Db::table('Z_TB_CompanyInformation_Run1')->where('applicationid',$applicationid)->find();
        $color7=$this->get_confirm($data7);
        $this->assign('color7',$color7);
        //往来银行信息
        $data8=Db::table('Z_TB_BankContact')->where('applicationid',$applicationid)->find();
        $color8=$this->get_confirm($data8);
        $this->assign('color8',$color8);
        //公共记录
        $data9=DB::table('Z_TB_PublicReport')->where('applicationid',$applicationid)->find();
        $color9=$this->get_confirm($data9);
        $this->assign('color9',$color9);
        //资产情况
        $data10=Db::table('Z_TB_financial_assets')->where('applicationid',$applicationid)->find();
        $color10=$this->get_confirm($data10);
        $this->assign('color10',$color10);
        //负债信息
        $data11=Db::table('Z_TB_financial_Debt')->where('applicationid',$applicationid)->find();
        $color11=$this->get_confirm($data11);
        $this->assign('color11',$color11);
        //损益信息
        $data12=Db::table('Z_TB_Financial_Sunyi')->where('applicationid',$applicationid)->find();
        $color12=$this->get_confirm($data12);
        $this->assign('color12',$color12);
        //比率信息
        $data13=Db::table('Z_TB_Financial_Rate')->where('applicationid',$applicationid)->find();
        $color13=$this->get_confirm($data13);
        $this->assign('color13',$color13);
      /*  //行业对比参照
        $data14=Db::table('Z_TB_Financial_Comparison')->where('applicationid',$applicationid)->find();
        $color14=$this->get_confirm($data14);
        $this->assign('color14',$color14);*/
        //财务分析
        $data15=Db::table('Z_TB_Financial_Analyse')->where('applicationid',$applicationid)->find();
        $color15=$this->get_confirm($data15);
        $this->assign('color15',$color15);
        //核查信息
        $data16=Db::table('Z_TB_InformationCheck')->where('applicationid',$applicationid)->find();
        $color16=$this->get_confirm($data16);
        $this->assign('color16',$color16);
        //信用分析
        $data17=Db::table('Z_TB_CreditAnalyse')->where('applicationid',$applicationid)->find();
        $color17=$this->get_confirm($data17);
        $this->assign('color17',$color17);
        //信用评级
        $data18=Db::table('Z_TB_CreditEvaluate')->where('applicationid',$applicationid)->find();
        $color18=$this->get_confirm($data18);
        $this->assign('color18',$color18);
    }

    //获取返回的$color(标准报告)翻译
    public function get_colorEng($applicationid){
        //摘要信息
        $data3=Db::table('Z_TB_Summany_information')->where('applicationid',$applicationid)->find();
        $color3=$this->get_confirmEng($data3);
        $this->assign('color3',$color3);
        //企业概况1
        $data4=Db::table('Z_TB_company_information1')->where('applicationid',$applicationid)->find();
        $color4=$this->get_confirmEng($data4);
        $this->assign('color4',$color4);
      //企业概况2
       $data5=DB::table('Z_TB_Branch')->where('applicationid',$applicationid)->find();
        $color5=$this->get_confirmEng($data5);
        $this->assign('color5',$color5);
        //经营状况信息1
        $data6=Db::table('Z_TB_CompanyInformation_Run')->where('applicationid',$applicationid)->find();
        $color6=$this->get_confirmEng($data6);
        $this->assign('color6',$color6);
        //运营信息2
        $data7=Db::table('Z_TB_CompanyInformation_Run1')->where('applicationid',$applicationid)->find();
        $color7=$this->get_confirmEng($data7);
        $this->assign('color7',$color7);
        //往来银行信息
        $data8=Db::table('Z_TB_BankContact')->where('applicationid',$applicationid)->find();
        $color8=$this->get_confirmEng($data8);
        $this->assign('color8',$color8);
        //公共记录
        $data9=DB::table('Z_TB_PublicReport')->where('applicationid',$applicationid)->find();
        $color9=$this->get_confirmEng($data9);
        $this->assign('color9',$color9);
      //资产情况
       // $data10=Db::table('Z_TB_financial_assets')->where('applicationid',$applicationid)->find();
        //$color10=$this->get_confirmEng($data10);
        $color10='0';
        $this->assign('color10',$color10);
        //负债信息
       // $data11=Db::table('Z_TB_financial_Debt')->where('applicationid',$applicationid)->find();
       // $color11=$this->get_confirmEng($data11);
        $color11='0';
        $this->assign('color11',$color11);
        //损益信息
       // $data12=Db::table('Z_TB_Financial_Sunyi')->where('applicationid',$applicationid)->find();
       // $color12=$this->get_confirmEng($data12);
        $color12='0';
        $this->assign('color12',$color12);
        //比率信息
       // $data13=Db::table('Z_TB_Financial_Rate')->where('applicationid',$applicationid)->find();
       // $color13=$this->get_confirmEng($data13);
        $color13='0';
        $this->assign('color13',$color13);
        /*  //行业对比参照
		  $data14=Db::table('Z_TB_Financial_Comparison')->where('applicationid',$applicationid)->find();
		  $color14=$this->get_confirm($data14);
		  $this->assign('color14',$color14);*/
        //财务分析
        $data15=Db::table('Z_TB_Financial_Analyse')->where('applicationid',$applicationid)->find();
        $color15=$this->get_confirmEng($data15);
        $this->assign('color15',$color15);
        //核查信息
        $data16=Db::table('Z_TB_InformationCheck')->where('applicationid',$applicationid)->find();
        $color16=$this->get_confirmEng($data16);
        $this->assign('color16',$color16);
        //信用分析
        $data17=Db::table('Z_TB_CreditAnalyse')->where('applicationid',$applicationid)->find();
        $color17=$this->get_confirmEng($data17);
        $this->assign('color17',$color17);
      //信用评级
       // $data18=Db::table('Z_TB_CreditEvaluate')->where('applicationid',$applicationid)->find();
       // $color18=$this->get_confirmEng($data18);
        $color18='0';
        $this->assign('color18',$color18);
    }

    //获取返回的$color(深度报告)
    public function get_color1($applicationid){
        //公司概要
        $data3=Db::table('Z_TB_Summany2_information')->where('applicationid',$applicationid)->find();
        $color3=$this->get_confirm($data3);
        $this->assign('color3',$color3);
        //注册信息
        $data4=Db::table('Z_TB_company_information1')->where('applicationid',$applicationid)->find();
        $color4=$this->get_confirm($data4);
        $this->assign('color4',$color4);
        //注册信息2
        $data5=DB::table('Z_TB_Investigate_Information')->where('applicationid',$applicationid)->find();
        $color5=$this->get_confirm($data5);
        $this->assign('color5',$color5);
        //股权机构
        $data6=Db::table('Z_TB_Shareholder')->where('applicationid',$applicationid)->find();
        $color6=$this->get_confirm($data6);
        $this->assign('color6',$color6);
        //组织机构
        $data7=Db::table('Z_TB_Organization')->where('applicationid',$applicationid)->find();
        $color7=$this->get_confirm($data7);
        $this->assign('color7',$color7);
        //人员状况
        $data8=Db::table('Z_TB_Staff_Information')->where('applicationid',$applicationid)->find();
        $color8=$this->get_confirm($data8);
        $this->assign('color8',$color8);
        //生产经营情况
        $data9=DB::table('Z_TB_ProductBusiness_Detail')->where('applicationid',$applicationid)->find();
        $color9=$this->get_confirm($data9);
        $this->assign('color9',$color9);
        //资产情况
        $data10=Db::table('Z_TB_financial_assets')->where('applicationid',$applicationid)->find();
        $color10=$this->get_confirm($data10);
        $this->assign('color10',$color10);
        //负债信息
        $data11=Db::table('Z_TB_financial_Debt')->where('applicationid',$applicationid)->find();
        $color11=$this->get_confirm($data11);
        $this->assign('color11',$color11);
        //损益信息
        $data12=Db::table('Z_TB_Financial_Sunyi')->where('applicationid',$applicationid)->find();
        $color12=$this->get_confirm($data12);
        $this->assign('color12',$color12);
        //比率信息
        $data13=Db::table('Z_TB_Financial_Rate')->where('applicationid',$applicationid)->find();
        $color13=$this->get_confirm($data13);
        $this->assign('color13',$color13);

        //财务分析
        $data15=Db::table('Z_TB_Financial_Analyse')->where('applicationid',$applicationid)->find();
        $color15=$this->get_confirm($data15);
        $this->assign('color15',$color15);
        //金融机构往来
        $data16=Db::table('Z_TB_BankContact')->where('applicationid',$applicationid)->find();
        $color16=$this->get_confirm($data16);
        $this->assign('color16',$color16);
        //法律纠纷
        $data17=Db::table('Z_TB_LegalDispute_Affect')->where('applicationid',$applicationid)->find();
        $color17=$this->get_confirm($data17);
        $this->assign('color17',$color17);
        //综合评价
        $data18=Db::table('Z_TB_CreditAnalyse')->where('applicationid',$applicationid)->find();
        $color18=$this->get_confirm($data18);
        $this->assign('color18',$color18);
        //风险评定
        $data19=Db::table('Z_TB_CreditEvaluate')->where('applicationid',$applicationid)->find();
        $color19=$this->get_confirm($data19);
        $this->assign('color19',$color19);

        //业务结算情况
        $data20=Db::table('Z_TB_PurchaseProduct_Detail')->where('applicationid',$applicationid)->find();
        $color20=$this->get_confirm($data20);
        $this->assign('color20',$color20);
    }

    //获取返回的$color(深度报告)翻译
    public function get_color1Eng($applicationid){
        //公司概要
        $data3=Db::table('Z_TB_Summany2_information')->where('applicationid',$applicationid)->find();
        $color3=$this->get_confirmEng($data3);
        $this->assign('color3',$color3);
        //注册信息
       $data4=Db::table('Z_TB_company_information1')->where('applicationid',$applicationid)->find();
        $color4=$this->get_confirmEng($data4);
        $this->assign('color4',$color4);
        //注册信息2
       $data5=DB::table('Z_TB_Investigate_Information')->where('applicationid',$applicationid)->find();
        $color5=$this->get_confirmEng($data5);
        $this->assign('color5',$color5);
        //股权机构
       // $data6=Db::table('Z_TB_Shareholder')->where('applicationid',$applicationid)->find();
        //$color6=$this->get_confirmEng($data6);
        $color6='0';
        $this->assign('color6',$color6);
        //组织机构
       // $data7=Db::table('Z_TB_Organization')->where('applicationid',$applicationid)->find();
       // $color7=$this->get_confirmEng($data7);
        $color7='0';
        $this->assign('color7',$color7);
        //人员状况
       $data8=Db::table('Z_TB_Staff_Information')->where('applicationid',$applicationid)->find();
        $color8=$this->get_confirmEng($data8);
        $this->assign('color8',$color8);
        //生产经营情况
        $data9=DB::table('Z_TB_ProductBusiness_Detail')->where('applicationid',$applicationid)->find();
        $color9=$this->get_confirmEng($data9);
        $this->assign('color9',$color9);
        //资产情况
       // $data10=Db::table('Z_TB_financial_assets')->where('applicationid',$applicationid)->find();
       // $color10=$this->get_confirmEng($data10);
        $color10='0';
        $this->assign('color10',$color10);
        //负债信息
       // $data11=Db::table('Z_TB_financial_Debt')->where('applicationid',$applicationid)->find();
       // $color11=$this->get_confirmEng($data11);
        $color11='0';
        $this->assign('color11',$color11);
        //损益信息
       // $data12=Db::table('Z_TB_Financial_Sunyi')->where('applicationid',$applicationid)->find();
       // $color12=$this->get_confirmEng($data12);
        $color12='0';
        $this->assign('color12',$color12);
        //比率信息
       // $data13=Db::table('Z_TB_Financial_Rate')->where('applicationid',$applicationid)->find();
       // $color13=$this->get_confirmEng($data13);
        $color13='0';
        $this->assign('color13',$color13);

        //财务分析
       $data15=Db::table('Z_TB_Financial_Analyse')->where('applicationid',$applicationid)->find();
        $color15=$this->get_confirmEng($data15);
        $this->assign('color15',$color15);
        //金融机构往来
        $data16=Db::table('Z_TB_BankContact')->where('applicationid',$applicationid)->find();
        $color16=$this->get_confirmEng($data16);
        $this->assign('color16',$color16);
        //法律纠纷
       $data17=Db::table('Z_TB_LegalDispute_Affect')->where('applicationid',$applicationid)->find();
        $color17=$this->get_confirmEng($data17);
        $this->assign('color17',$color17);
        //综合评价
       $data18=Db::table('Z_TB_CreditAnalyse')->where('applicationid',$applicationid)->find();
        $color18=$this->get_confirmEng($data18);
        $this->assign('color18',$color18);
        //风险评定
       // $data19=Db::table('Z_TB_CreditEvaluate')->where('applicationid',$applicationid)->find();
       // $color19=$this->get_confirmEng($data19);
        $color19='0';
        $this->assign('color19',$color19);

        //业务结算情况
      $data20=Db::table('Z_TB_PurchaseProduct_Detail')->where('applicationid',$applicationid)->find();
        $color20=$this->get_confirmEng($data20);
        $this->assign('color20',$color20);
    }

    //获取是否确认
    public function get_confirm($res){
        if($res['isConfirm']=='1'){
            return $color='1';
        }else{
            return $color='0';
        }
    }

    //获取是否确认(翻译)
    public function get_confirmEng($res){
        if($res['isConfirmEng']=='1'){
            return $color='3';
        }else{
            return $color='0';
        }
    }

    //获取是否有备注
    public function get_memo($res){
        if($res['confirmMemo']){
            return $color='2';
        }else{
            return $color='0';
        }
    }

    //获取是否有备注(翻译)
    public function get_memoEng($res){
        if($res['confirmMemoEng']){
            return $color='4';
        }else{
            return $color='0';
        }
    }

    //获取返回的$color(标准报告)
    public function get_confirm_color($applicationid){
        //摘要信息
        $data3=Db::table('Z_TB_Summany_information')->where('applicationid',$applicationid)->find();
        $color3=$this->get_memo($data3);
        $this->assign('color3',$color3);
        //企业概况1
        $data4=Db::table('Z_TB_company_information1')->where('applicationid',$applicationid)->find();
        $color4=$this->get_memo($data4);
        $this->assign('color4',$color4);
        //企业概况2
        $data5=DB::table('Z_TB_Branch')->where('applicationid',$applicationid)->find();
        $color5=$this->get_memo($data5);
        $this->assign('color5',$color5);
        //经营状况信息1
        $data6=Db::table('Z_TB_CompanyInformation_Run')->where('applicationid',$applicationid)->find();
        $color6=$this->get_memo($data6);
        $this->assign('color6',$color6);
        //运营信息2
        $data7=Db::table('Z_TB_CompanyInformation_Run1')->where('applicationid',$applicationid)->find();
        $color7=$this->get_memo($data7);
        $this->assign('color7',$color7);
        //往来银行信息
        $data8=Db::table('Z_TB_BankContact')->where('applicationid',$applicationid)->find();
        $color8=$this->get_memo($data8);
        $this->assign('color8',$color8);
        //公共记录
        $data9=DB::table('Z_TB_PublicReport')->where('applicationid',$applicationid)->find();
        $color9=$this->get_memo($data9);
        $this->assign('color9',$color9);
        //资产情况
        $data10=Db::table('Z_TB_financial_assets')->where('applicationid',$applicationid)->find();
        $color10=$this->get_memo($data10);
        $this->assign('color10',$color10);
        //负债信息
        $data11=Db::table('Z_TB_financial_Debt')->where('applicationid',$applicationid)->find();
        $color11=$this->get_memo($data11);
        $this->assign('color11',$color11);
        //损益信息
        $data12=Db::table('Z_TB_Financial_Sunyi')->where('applicationid',$applicationid)->find();
        $color12=$this->get_memo($data12);
        $this->assign('color12',$color12);
        //比率信息
        $data13=Db::table('Z_TB_Financial_Rate')->where('applicationid',$applicationid)->find();
        $color13=$this->get_memo($data13);
        $this->assign('color13',$color13);
    /*    //行业对比参照
        $data14=Db::table('Z_TB_Financial_Comparison')->where('applicationid',$applicationid)->find();
        $color14=$this->get_memo($data14);
        $this->assign('color14',$color14);*/
        //财务分析
        $data15=Db::table('Z_TB_Financial_Analyse')->where('applicationid',$applicationid)->find();
        $color15=$this->get_memo($data15);
        $this->assign('color15',$color15);
        //核查信息
        $data16=Db::table('Z_TB_InformationCheck')->where('applicationid',$applicationid)->find();
        $color16=$this->get_memo($data16);
        $this->assign('color16',$color16);
        //信用分析
        $data17=Db::table('Z_TB_CreditAnalyse')->where('applicationid',$applicationid)->find();
        $color17=$this->get_memo($data17);
        $this->assign('color17',$color17);
        //信用评级
        $data18=Db::table('Z_TB_CreditEvaluate')->where('applicationid',$applicationid)->find();
        $color18=$this->get_memo($data18);
        $this->assign('color18',$color18);
    }

    //获取返回的$color(标准报告)翻译
    public function get_confirm_colorEng($applicationid){
        //摘要信息
        $data3=Db::table('Z_TB_Summany_information')->where('applicationid',$applicationid)->find();
        $color3=$this->get_memoEng($data3);
        $this->assign('color3',$color3);
        //企业概况1
        $data4=Db::table('Z_TB_company_information1')->where('applicationid',$applicationid)->find();
        $color4=$this->get_memoEng($data4);
        $this->assign('color4',$color4);
        //企业概况2
        $data5=DB::table('Z_TB_Branch')->where('applicationid',$applicationid)->find();
        $color5=$this->get_memoEng($data5);
        $this->assign('color5',$color5);
        //经营状况信息1
        $data6=Db::table('Z_TB_CompanyInformation_Run')->where('applicationid',$applicationid)->find();
        $color6=$this->get_memoEng($data6);
        $this->assign('color6',$color6);
        //运营信息2
        $data7=Db::table('Z_TB_CompanyInformation_Run1')->where('applicationid',$applicationid)->find();
        $color7=$this->get_memoEng($data7);
        $this->assign('color7',$color7);
        //往来银行信息
        $data8=Db::table('Z_TB_BankContact')->where('applicationid',$applicationid)->find();
        $color8=$this->get_memoEng($data8);
        $this->assign('color8',$color8);
        //公共记录
        $data9=DB::table('Z_TB_PublicReport')->where('applicationid',$applicationid)->find();
        $color9=$this->get_memoEng($data9);
        $this->assign('color9',$color9);
        //资产情况
       // $data10=Db::table('Z_TB_financial_assets')->where('applicationid',$applicationid)->find();
       // $color10=$this->get_memoEng($data10);
        $color10='0';
        $this->assign('color10',$color10);
        //负债信息
       // $data11=Db::table('Z_TB_financial_Debt')->where('applicationid',$applicationid)->find();
        //$color11=$this->get_memoEng($data11);
        $color11='0';
        $this->assign('color11',$color11);
        //损益信息
        //$data12=Db::table('Z_TB_Financial_Sunyi')->where('applicationid',$applicationid)->find();
       // $color12=$this->get_memoEng($data12);
        $color12='0';
        $this->assign('color12',$color12);
        //比率信息
       //$data13=Db::table('Z_TB_Financial_Rate')->where('applicationid',$applicationid)->find();
       // $color13=$this->get_memoEng($data13);
        $color13='0';
        $this->assign('color13',$color13);
        /*    //行业对比参照
			$data14=Db::table('Z_TB_Financial_Comparison')->where('applicationid',$applicationid)->find();
			$color14=$this->get_memo($data14);
			$this->assign('color14',$color14);*/
        //财务分析
        $data15=Db::table('Z_TB_Financial_Analyse')->where('applicationid',$applicationid)->find();
        $color15=$this->get_memoEng($data15);
        $this->assign('color15',$color15);
        //核查信息
        $data16=Db::table('Z_TB_InformationCheck')->where('applicationid',$applicationid)->find();
        $color16=$this->get_memoEng($data16);
        $this->assign('color16',$color16);
        //信用分析
        $data17=Db::table('Z_TB_CreditAnalyse')->where('applicationid',$applicationid)->find();
        $color17=$this->get_memoEng($data17);
        $this->assign('color17',$color17);
        //信用评级
       // $data18=Db::table('Z_TB_CreditEvaluate')->where('applicationid',$applicationid)->find();
       // $color18=$this->get_memoEng($data18);
        $color18='0';
        $this->assign('color18',$color18);
    }

    //获取返回的$color(深度报告)
    public function get_confirm_color1($applicationid){
        //公司概要
        $data3=Db::table('Z_TB_Summany2_information')->where('applicationid',$applicationid)->find();
        $color3=$this->get_memo($data3);
        $this->assign('color3',$color3);
        //注册信息
        $data4=Db::table('Z_TB_company_information1')->where('applicationid',$applicationid)->find();
        $color4=$this->get_memo($data4);
        $this->assign('color4',$color4);
        //注册信息2
        $data5=DB::table('Z_TB_Investigate_Information')->where('applicationid',$applicationid)->find();
        $color5=$this->get_memo($data5);
        $this->assign('color5',$color5);
        //股权机构
        $data6=Db::table('Z_TB_Shareholder')->where('applicationid',$applicationid)->find();
        $color6=$this->get_memo($data6);
        $this->assign('color6',$color6);
        //组织机构
        $data7=Db::table('Z_TB_Organization')->where('applicationid',$applicationid)->find();
        $color7=$this->get_memo($data7);
        $this->assign('color7',$color7);
        //人员状况
        $data8=Db::table('Z_TB_Staff_Information')->where('applicationid',$applicationid)->find();
        $color8=$this->get_memo($data8);
        $this->assign('color8',$color8);
        //生产经营情况
        $data9=DB::table('Z_TB_ProductBusiness_Detail')->where('applicationid',$applicationid)->find();
        $color9=$this->get_memo($data9);
        $this->assign('color9',$color9);
        //资产情况
        $data10=Db::table('Z_TB_financial_assets')->where('applicationid',$applicationid)->find();
        $color10=$this->get_memo($data10);
        $this->assign('color10',$color10);
        //负债信息
        $data11=Db::table('Z_TB_financial_Debt')->where('applicationid',$applicationid)->find();
        $color11=$this->get_memo($data11);
        $this->assign('color11',$color11);
        //损益信息
        $data12=Db::table('Z_TB_Financial_Sunyi')->where('applicationid',$applicationid)->find();
        $color12=$this->get_memo($data12);
        $this->assign('color12',$color12);
        //比率信息
        $data13=Db::table('Z_TB_Financial_Rate')->where('applicationid',$applicationid)->find();
        $color13=$this->get_memo($data13);
        $this->assign('color13',$color13);

        //财务分析
        $data15=Db::table('Z_TB_Financial_Analyse')->where('applicationid',$applicationid)->find();
        $color15=$this->get_memo($data15);
        $this->assign('color15',$color15);
        //金融机构往来
        $data16=Db::table('Z_TB_BankContact')->where('applicationid',$applicationid)->find();
        $color16=$this->get_memo($data16);
        $this->assign('color16',$color16);
        //法律纠纷
        $data17=Db::table('Z_TB_LegalDispute_Affect')->where('applicationid',$applicationid)->find();
        $color17=$this->get_memo($data17);
        $this->assign('color17',$color17);
        //综合评价
        $data18=Db::table('Z_TB_CreditAnalyse')->where('applicationid',$applicationid)->find();
        $color18=$this->get_memo($data18);
        $this->assign('color18',$color18);
        //风险评定
        $data19=Db::table('Z_TB_CreditEvaluate')->where('applicationid',$applicationid)->find();
        $color19=$this->get_memo($data19);
        $this->assign('color19',$color19);

        //业务结算情况
        $data20=Db::table('Z_TB_PurchaseProduct_Detail')->where('applicationid',$applicationid)->find();
        $color20=$this->get_memo($data20);
        $this->assign('color20',$color20);
    }

    //获取返回的$color(深度报告)翻译
    public function get_confirm_color1Eng($applicationid){
        //公司概要
        $data3=Db::table('Z_TB_Summany2_information')->where('applicationid',$applicationid)->find();
        $color3=$this->get_memoEng($data3);
        $this->assign('color3',$color3);
        //注册信息
        $data4=Db::table('Z_TB_company_information1')->where('applicationid',$applicationid)->find();
        $color4=$this->get_memoEng($data4);
        $this->assign('color4',$color4);
        //注册信息2
        $data5=DB::table('Z_TB_Investigate_Information')->where('applicationid',$applicationid)->find();
        $color5=$this->get_memoEng($data5);
        $this->assign('color5',$color5);
        //股权机构
       // $data6=Db::table('Z_TB_Shareholder')->where('applicationid',$applicationid)->find();
        //$color6=$this->get_memoEng($data6);
        $color6='0';
        $this->assign('color6',$color6);
        //组织机构
       // $data7=Db::table('Z_TB_Organization')->where('applicationid',$applicationid)->find();
        //$color7=$this->get_memoEng($data7);
        $color7='0';
        $this->assign('color7',$color7);
        //人员状况
        $data8=Db::table('Z_TB_Staff_Information')->where('applicationid',$applicationid)->find();
        $color8=$this->get_memoEng($data8);
        $this->assign('color8',$color8);
        //生产经营情况
        $data9=DB::table('Z_TB_ProductBusiness_Detail')->where('applicationid',$applicationid)->find();
        $color9=$this->get_memoEng($data9);
        $this->assign('color9',$color9);
        //资产情况
       // $data10=Db::table('Z_TB_financial_assets')->where('applicationid',$applicationid)->find();
       // $color10=$this->get_memoEng($data10);
        $color10='0';
        $this->assign('color10',$color10);
        //负债信息
       // $data11=Db::table('Z_TB_financial_Debt')->where('applicationid',$applicationid)->find();
        //$color11=$this->get_memoEng($data11);
        $color11='0';
        $this->assign('color11',$color11);
        //损益信息
      //  $data12=Db::table('Z_TB_Financial_Sunyi')->where('applicationid',$applicationid)->find();
       // $color12=$this->get_memoEng($data12);
        $color12='0';
        $this->assign('color12',$color12);
        //比率信息
       // $data13=Db::table('Z_TB_Financial_Rate')->where('applicationid',$applicationid)->find();
       // $color13=$this->get_memoEng($data13);
        $color13='0';
        $this->assign('color13',$color13);

        //财务分析
        $data15=Db::table('Z_TB_Financial_Analyse')->where('applicationid',$applicationid)->find();
        $color15=$this->get_memoEng($data15);
        $this->assign('color15',$color15);
        //金融机构往来
        $data16=Db::table('Z_TB_BankContact')->where('applicationid',$applicationid)->find();
        $color16=$this->get_memoEng($data16);
        $this->assign('color16',$color16);
        //法律纠纷
        $data17=Db::table('Z_TB_LegalDispute_Affect')->where('applicationid',$applicationid)->find();
        $color17=$this->get_memoEng($data17);
        $this->assign('color17',$color17);
        //综合评价
        $data18=Db::table('Z_TB_CreditAnalyse')->where('applicationid',$applicationid)->find();
        $color18=$this->get_memoEng($data18);
        $this->assign('color18',$color18);
        //风险评定
       // $data19=Db::table('Z_TB_CreditEvaluate')->where('applicationid',$applicationid)->find();
        //$color19=$this->get_memoEng($data19);
        $color19='0';
        $this->assign('color19',$color19);

        //业务结算情况
        $data20=Db::table('Z_TB_PurchaseProduct_Detail')->where('applicationid',$applicationid)->find();
        $color20=$this->get_memoEng($data20);
        $this->assign('color20',$color20);
    }



    //获取完成日期的函数
    public function getFinish($date,$day){
        $holiday_arr= Db::table('Z_TB_Holiday')->field('date')->select();
        $holiday_arr1=array();
        foreach($holiday_arr as $v){
            array_push($holiday_arr1,$v['date']);
        }
        $date_start=$date;
        for($i=$day;$i>0;$i--){
            $date1=date('Y-m-d',strtotime('+1 day',strtotime($date_start)));
            if(in_array($date1,$holiday_arr1)){
                $i++;
            }
            $date_start=date('Y-m-d',strtotime('+1 day',strtotime($date_start)));
        }
        return $date1;

    }

    //报告颜色的变化(标准报告)
    public function color_baogao($applicationid,$option){
        if($option=='2'){//制作报告
            $this->get_color($applicationid);
        }
        else if($option=='3'){//报告审核
            $this->get_confirm_color($applicationid);
        }
        else if($option=='4'){//报告翻译
            $this->get_colorEng($applicationid);
        }
        else if($option=='5'){//报告翻译审核
            $this->get_confirm_colorEng($applicationid);
        }
        else if($option=='6'){//报告完成
            $this->get_confirm_color($applicationid);
        }else{
            $this->get_confirm_color($applicationid);
        }
    }

    //报告颜色的变化（深度报告）
    public function color_baogao1($applicationid,$option){
        //颜色的变化
        if($option=='2'){//制作报告
            $this->get_color1($applicationid);
        }
        else if($option=='3'){//报告审核
            $this->get_confirm_color1($applicationid);
        }
        else if($option=='4'){//报告翻译
            $this->get_color1Eng($applicationid);
        }
        else if($option=='5'){//报告翻译审核
            $this->get_confirm_color1Eng($applicationid);
        }
        else if($option=='6'){//报告完成
            $this->get_confirm_color1($applicationid);
        }
        else{
            $this->get_confirm_color1($applicationid);
        }
    }
}