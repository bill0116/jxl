<?php
/**
func:客户档案查询
 */
namespace app\index\controller;

use think\Db;
class CustomerList extends Common
{
	public function index(){
		if($this->request->isPost()){
			$where="1=1";
			if(!empty(input('post.kunnr'))){
				$where.="and kunnr='".trim(input('post.kunnr'))."'";
			}
			if(!empty(input('post.buyerCountryCode'))){
				$where.="and buyerCountryCode='".trim(input('post.buyerCountryCode'))."'";
			}
			if(!empty(input('post.buyerno'))){
				$where.="and buyerno='".trim(input('post.buyerno'))."'";
			}
			if(!empty(input('post.buyerEngName'))){
				$where.="and buyerEngName like'%".trim(input('post.buyerEngName'))."%'";
			}
			if(!empty(input('post.Black'))){
				$where.="and Black='".trim(input('post.Black'))."'";
			}
			$list=Db::table('Z_VE_Customer_All')
				->field('kunnr,buyerCountryCode,buyerno,buyerEngName,Black,buyerEngAddress,land1,name')
				->where($where)
				->paginate(10,false,['type' => 'bootstrap', 'var_page' => 'page', 'path' => 'javascript:doSearch([PAGE]);']);
			if($list){
				$data=$list->toArray()['data'];
				foreach ($data as $k => $v) {
					$data[$k]['FlowUrl'] = url('CustomerList/detail', ['buyerno' => $this->HtmlEncode($v['buyerno'])]);
				}
				$page=$list->render();
				return return_array_result(1,lang('查询成功'),'',['list'=>$data,'page'=> $page]);
			}
			else{
				return return_array_result(0,lang('查询失败'));
			}
		}else{
			return $this->fetch();
		}
	}

	public function detail(){
		$buyerno1=input('param.buyerno');
		$buyerno=str_replace('-','/',$buyerno1);
		//基本信息
		$resultData1 = Db::table('Z_TB_Sinosure_Buyer')
				->field('buyerno,buyerEngName,Kunnr,buyerCountryCode,buyerEngAddress,buyerRegNo,buyerTel,buyerFax,buyerCity')
				->where('buyerno', $buyerno)
				->find();
		$this->assign('data1', $resultData1);

		//信保限额信息
		$resultData2 = Db::table('Z_TB_QuotaActual')
				->field('* ,dbo.S_F_GetGentalCodeText(219,paymode) as paymodetext ,(callimit-dbo.Z_F_GetQuotaLimitUsed(kunnr,OrganizationFlag,IfLC,bankswift)) as GSSYED ')
				->where('buyerno', $buyerno)
				->select();
		$this->assign('data2', $resultData2);

		//客户超期数据
		$resultData3 = Db::table('Z_TB_REPORT_OverAmount')
				->field('*')
				->where('buyerno', $buyerno)
				->select();
		$this->assign('data3', $resultData3);

		//信用复评历史
		$resultData4=Db::query("select ActiveID,buyerno,buyerEngName,Score,Grade,MScore,CalLimit,CalTerm,ContractPayModeText,quotaactiveid,
								CreateDate,RefuseRate2,OtherRate2
								from (select a.ActiveID,c.sinosurebuyerno as buyerno,c.buyerEngName,c.Score,c.Grade,c.MScore,c.CalLimit,c.CalTerm,
										c.ContractPayModeText,b.ActiveID as quotaactiveid,c.CreateDate,b.RefuseRate2,b.OtherRate2
										from S_TB_FC_Active a inner join Z_VE_NoLCQuotaApppy b on a.ActiveID=b.CreditActiveid
										left join  Z_TB_FLowApp_CreditApplication c on a.ApplicationID=c.id where c.sinosurebuyerno='{$buyerno}')  a
								");
		$this->assign('data4',$resultData4);

		//客户评估历史
		$resultData5=Db::query("select * from (
								select  b.[sinosurebuyerno] CustomerNumber ,b.[sinosurebuyerno] as buyerno,[buyerEngName] as CustomerName,
								 b.id,b.creatorname as UserName,b.score,b.grade,MScore as SystemSuggest, a.activeid as EvaluateresultID,a.ActiveID,
								 a.createdate as EvaluateDate
								from S_tb_fc_active a left join Z_TB_FLowApp_CreditApplication b on a.applicationid=b.id
								where flowid=2 and b.[sinosurebuyerno]='{$buyerno}') a
								");
		$this->assign('data5',$resultData5);

		//资信摘要
		//信用信息
		$resultData6=Db::table("Z_TB_SinoSure_ForeignCustomerCreditInfo")
				->field('*')
				->where('sinosurebuyerno', $buyerno)
				->select();
		$this->assign('data6',$resultData6);
		//经营信息
		$resultData7=Db::table("Z_TB_SinoSure_ForeignCustomerBusinessInfo")
				->field('*,dbo.S_F_GetGentalCodeText(353,businessType) as businessTxt')
				->where('sinosurebuyerno', $buyerno)
				->select();
		$this->assign('data7',$resultData7);
		//公告信息
		$resultData8=Db::table("Z_TB_SinoSure_ForeignCustomerPublicInfo")
				->field('*')
				->where('sinosurebuyerno', $buyerno)
				->select();
		$this->assign('data8',$resultData8);
		//股东信息
		$resultData9=Db::table("Z_TB_SinoSure_ForeignCustomerSharesInfo")
				->field('*')
				->where('sinosurebuyerno', $buyerno)
				->select();
		$this->assign('data9',$resultData9);
		return $this->fetch();
	}
}