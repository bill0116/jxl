<?php
/**
 * func:资产表(深度报告)
 */
namespace app\index\controller;

use think\Db;

class AssetDetail1 extends Common
{
    protected $beforeActionList = ['getAttachList'];

    protected function getAttachList()
    {
        $applicationid = input('get.applicationid');
        $this->assign('attachList', $this->getAttachFile($this->getAttachID($applicationid)));
    }

    public function initialize()
    {
        parent::initialize();
        $this->model = model('flow');
        $this->table = Db::table('Z_TB_financial_assets');
    }

    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);


        //资产情况
        $data1 = Db::table('Z_TB_financial_assets')->where('applicationid', $applicationid)->find();
        if (!$data1) {
            $this->assign('data', $data1);
        } else {
            $data = Db::table('Z_TB_financial_assets')->where('applicationid', $applicationid)->order('year', 'desc')->select();
            $this->assign('data', $data);
        }

        //颜色的变化
        $this->color_baogao1($applicationid,$option);

        $this->assign('type',10);
        return $this->fetch();
    }

    //保存
    public function save()
    {
        $userid = session('auth_id');
        $applicationid = input('request.id');
        $activeid = input('request.activeid');
        $year = date('Y', time());//当前年份

        $this->table->where('applicationid', $applicationid)->delete();

        for ($i = 0; $i < 5; $i++) {
            $map['creator'] = $userid;//创建人
            $map['createdate'] = date('Y-m-d H:i:s', time());//创建时间
            $map['applicationid'] = input('request.id');//委托单id
            $map['activeid'] = input('request.activeid');//activeid

            //资产表
            $map['year'] = array($year - 1, $year - 2, $year - 3, $year - 4, $year - 5)[$i];//年份
            $map['currencyFunds'] = input('request.currencyFunds/a')[$i];//货币资金
            $map['receivableBill'] = input('request.receivableBill/a')[$i];//应收票据
            $map['receivableAccount'] = input('request.receivableAccount/a')[$i];//应收账款
            $map['prepayAccount'] = input('request.prepayAccount/a')[$i];//预付账款
            $map['otherReceivableAccount'] = input('request.otherReceivableAccount/a')[$i];//其他应收账款
            $map['stock'] = input('request.stock/a')[$i];//存货
            $map['apportionCost'] = input('request.apportionCost/a')[$i];//待摊费用
            $map['otherCurrentAssets'] = input('request.otherCurrentAssets/a')[$i];//其他流动资产
            $map['totalCurrentAssets'] = input('request.totalCurrentAssets/a')[$i];//流动资产合计
            $map['long_termInvestment'] = input('request.long_termInvestment/a')[$i];//长期投资
            $map['totalLong_termInvestment'] = input('request.totalLong_termInvestment/a')[$i];//长期投资合计
            $map['fixedAssetsOriginial'] = input('request.fixedAssetsOriginial/a')[$i];//固定资产原值
            $map['cumulativeDiscounts'] = input('request.cumulativeDiscounts/a')[$i];//累积折扣
            $map['fixedAssetsNet'] = input('request.fixedAssetsNet/a')[$i];//固定资产净值
            $map['constructionProject'] = input('request.constructionProject/a')[$i];//在建工程
            $map['totalFixedAssets'] = input('request.totalFixedAssets/a')[$i];//固定资产合计
            $map['deferAssets'] = input('request.deferAssets/a')[$i];//递延资产
            $map['intangibleAssets'] = input('request.intangibleAssets/a')[$i];//无形资产
            $map['totalDeferIntangible'] = input('request.totalDeferIntangible/a')[$i];//合计递延无形资产
            $map['totalAssets'] = input('request.totalAssets/a')[$i];//资产总计

            $map['short_termInvestment'] = input('request.short_termInvestment/a')[$i];//短期投资
            $map['debtAccount'] = input('request.debtAccount/a')[$i];//坏账准备
            $map['receivableAccountNet'] = input('request.receivableAccountNet/a')[$i];//应收账款净额
            $map['transferExpense'] = input('request.transferExpense/a')[$i];//待转其他业务支出
            $map['flowAssetsLoss'] = input('request.flowAssetsLoss/a')[$i];//待处理流动资产净损失
            $map['bondInvest'] = input('request.bondInvest/a')[$i];//一年内到期的长期债券投资
            $map['fixedAssetsClear'] = input('request.fixedAssetsClear/a')[$i];//固定资产处理
            $map['fixedAssetsLoss'] = input('request.fixedAssetsLoss/a')[$i];//待处理固定资产净损失
            $map['otherLongAssets'] = input('request.otherLongAssets/a')[$i];//其他长期投资

            $id = Db::table('Z_TB_financial_assets')->insertGetId($map);
        }

        if ($id) {
            return return_array_result(1, lang('保存成功'));
        } else {
            return return_array_result(0, lang('保存失败'));
        }
    }

    //确认
    public function recheck()
    {
        $applicationid = input('request.applicationid');;
        return $this->check($this->table, $applicationid);
    }

    //备注
    public function save_memo()
    {
        $applicationid = input('request.applicationid');
        $memo = input('request.confirmMemo');
        return $this->memo($this->table, $applicationid, $memo);
    }


    //导入数据
    public function import()
    {
        $userid = session('auth_id');
        $applicationid = input('request.id');
        $activeid = input('request.activeid');

        //删除数据
        Db::table('Z_TB_Financial_Sunyi')->where('applicationid', $applicationid)->delete();//损益表
        Db::table('Z_TB_financial_assets')->where('applicationid', $applicationid)->delete();//资产表
        Db::table('Z_TB_financial_Debt')->where('applicationid', $applicationid)->delete();//负债表

        //文件上传
        $files = request()->file('file');
        $info = $files->move('public/excel');
        if (!$info) {
            // 上传错误提示错误信息
            $this->error($files->getError());
        } else {
            // 上传成功 获取上传文件信息
            $filename = str_replace('\\', '/', $info->getPathname());
            $this->importExecl($filename, $applicationid, $activeid);
        }
    }

    //excel导入
    public function importExecl($filename, $applicationid, $activeid)
    {
        $userid = session('auth_id');
        $file = dirname(__FILE__);
        if (!file_exists($file)) {
            return array("error" => 0, 'message' => 'file not found!');
        }
        include_once './vendor/Excel/PHPExcel/IOFactory.php';
        $fileType = \PHPExcel_IOFactory::identify($filename);//自动获取文件的类型提供给phpexcel用
        $objReader = \PHPExcel_IOFactory::createReader($fileType);//读取文件 读取操作对象
        $objPHPexcel = $objReader->load($filename, $encode = 'utf-8');//加载文件
        $sheetCount = $objPHPexcel->getSheetCount();//获取excel文件里有多少个sheet
        $data1 = array();
        $data2 = array();
        $data3 = array();


        for ($i = 0; $i < $sheetCount; $i++) {
            $sheet = $objPHPexcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumn = $sheet->getHighestColumn(); // 取得总列数
            //for($i=1;$i<=$highestRow;$i++){
            //损益表
            $year = date('Y', time());//当前年份
            $year_arr = array($year - 1, $year - 2, $year - 3, $year - 4, $year - 5);

            $year1 = substr($objPHPexcel->getActiveSheet()->getCell("B1")->getValue(), 0, 4);
            if (in_array($year1, $year_arr)) {
                $sunyi1['creator'] = $userid;//创建人
                $sunyi1['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $sunyi1['applicationid'] = $applicationid;
                $sunyi1['activeid'] = $activeid;
                $sunyi1['year'] = substr($objPHPexcel->getActiveSheet()->getCell("B1")->getValue(), 0, 4);
                $sunyi1['mainIncome'] = $objPHPexcel->getActiveSheet()->getCell("B2")->getValue();
                $sunyi1['mainCost'] = $objPHPexcel->getActiveSheet()->getCell("B3")->getValue();
                $sunyi1['tax_addition'] = $objPHPexcel->getActiveSheet()->getCell("B4")->getValue();

                $sunyi1['mainPrpfit_YW'] = $objPHPexcel->getActiveSheet()->getCell("B6")->getValue();
                $sunyi1['otherPrpfit_YW'] = $objPHPexcel->getActiveSheet()->getCell("B7")->getValue();
                $sunyi1['business'] = $objPHPexcel->getActiveSheet()->getCell("B8")->getValue();
                $sunyi1['manage'] = $objPHPexcel->getActiveSheet()->getCell("B9")->getValue();
                $sunyi1['financial'] = $objPHPexcel->getActiveSheet()->getCell("B10")->getValue();
                // $sunyi1['sumCost'] = $objPHPexcel->getActiveSheet()->getCell("B11")->getValue();
                $sunyi1['interestExpense'] = $objPHPexcel->getActiveSheet()->getCell("B12")->getValue();
                $sunyi1['exchange'] = $objPHPexcel->getActiveSheet()->getCell("B13")->getValue();

                $sunyi1['profit_YY'] = $objPHPexcel->getActiveSheet()->getCell("B15")->getValue();
                $sunyi1['investIncome'] = $objPHPexcel->getActiveSheet()->getCell("B16")->getValue();
                $sunyi1['subsidyIncome'] = $objPHPexcel->getActiveSheet()->getCell("B17")->getValue();
                $sunyi1['otherBusinessIn'] = $objPHPexcel->getActiveSheet()->getCell("B18")->getValue();
                $sunyi1['otherBusinessOut'] = $objPHPexcel->getActiveSheet()->getCell("B19")->getValue();


                $sunyi1['totalProfit'] = $objPHPexcel->getActiveSheet()->getCell("B21")->getValue();
                $sunyi1['incomeTax'] = $objPHPexcel->getActiveSheet()->getCell("B22")->getValue();
                $sunyi1['shareholderSunyi'] = $objPHPexcel->getActiveSheet()->getCell("B23")->getValue();
                $sunyi1['investSunyi'] = $objPHPexcel->getActiveSheet()->getCell("B24")->getValue();
                $sunyi1['other'] = $objPHPexcel->getActiveSheet()->getCell("B25")->getValue();
                $sunyi1['profit_SH'] = $objPHPexcel->getActiveSheet()->getCell("B27")->getValue();
                $data1[] = $sunyi1;

                //资产表
                $asset1['creator'] = $userid;//创建人
                $asset1['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $asset1['applicationid'] = $applicationid;
                $asset1['activeid'] = $activeid;
                $asset1['year'] = substr($objPHPexcel->getActiveSheet()->getCell("B1")->getValue(), 0, 4);
                $asset1['currencyFunds'] = $objPHPexcel->getActiveSheet()->getCell("B29")->getValue();
                $asset1['short_termInvestment'] = $objPHPexcel->getActiveSheet()->getCell("B30")->getValue();
                $asset1['receivableBill'] = $objPHPexcel->getActiveSheet()->getCell("B31")->getValue();
                $asset1['receivableDividend'] = $objPHPexcel->getActiveSheet()->getCell("B32")->getValue();
                $asset1['receivableInterest'] = $objPHPexcel->getActiveSheet()->getCell("B33")->getValue();
                $asset1['receivableAccount'] = $objPHPexcel->getActiveSheet()->getCell("B34")->getValue();
                $asset1['otherReceivableAccount'] = $objPHPexcel->getActiveSheet()->getCell("B35")->getValue();
                $asset1['prepayAccount'] = $objPHPexcel->getActiveSheet()->getCell("B36")->getValue();
                $asset1['receivableSubsidy'] = $objPHPexcel->getActiveSheet()->getCell("B37")->getValue();
                $asset1['stock'] = $objPHPexcel->getActiveSheet()->getCell("B38")->getValue();
                $asset1['apportionCost'] = $objPHPexcel->getActiveSheet()->getCell("B39")->getValue();
                $asset1['bondInvest'] = $objPHPexcel->getActiveSheet()->getCell("B40")->getValue();
                $asset1['flowAssetsLoss'] = $objPHPexcel->getActiveSheet()->getCell("B41")->getValue();
                $asset1['otherCurrentAssets'] = $objPHPexcel->getActiveSheet()->getCell("B42")->getValue();
                $asset1['totalCurrentAssets'] = $objPHPexcel->getActiveSheet()->getCell("B44")->getValue();

                $asset1['long_termInvestment1'] = $objPHPexcel->getActiveSheet()->getCell("B45")->getValue();
                $asset1['long_termInvestment2'] = $objPHPexcel->getActiveSheet()->getCell("B46")->getValue();
                $asset1['long_termInvestment3'] = $objPHPexcel->getActiveSheet()->getCell("B47")->getValue();
                $asset1['totalLong_termInvestment'] = $objPHPexcel->getActiveSheet()->getCell("B49")->getValue();

                $asset1['fixedAssetsOriginial'] = $objPHPexcel->getActiveSheet()->getCell("B50")->getValue();
                $asset1['cumulativeDiscounts'] = $objPHPexcel->getActiveSheet()->getCell("B51")->getValue();
                $asset1['fixedAssetsNet'] = $asset1['fixedAssetsOriginial'] - $asset1['cumulativeDiscounts'];
                $asset1['fixedAssetsDecrease'] = $objPHPexcel->getActiveSheet()->getCell("B53")->getValue();

                $asset1['fixedAssetsNet1'] = $asset1['fixedAssetsNet'] - $asset1['fixedAssetsDecrease'];
                $asset1['projectMaterial'] = $objPHPexcel->getActiveSheet()->getCell("B55")->getValue();
                $asset1['constructionProject'] = $objPHPexcel->getActiveSheet()->getCell("B56")->getValue();
                $asset1['fixedAssetsClear'] = $objPHPexcel->getActiveSheet()->getCell("B57")->getValue();
                $asset1['fixedAssetsLoss'] = $objPHPexcel->getActiveSheet()->getCell("B58")->getValue();
                $asset1['totalFixedAssets'] = $objPHPexcel->getActiveSheet()->getCell("B60")->getValue();


                $asset1['intangibleAssets'] = $objPHPexcel->getActiveSheet()->getCell("B61")->getValue();
                $asset1['deferAssets'] = $objPHPexcel->getActiveSheet()->getCell("B62")->getValue();
                $asset1['startCost'] = $objPHPexcel->getActiveSheet()->getCell("B63")->getValue();
                $asset1['longApportionCost'] = $objPHPexcel->getActiveSheet()->getCell("B64")->getValue();
                $asset1['otherLongAssets'] = $objPHPexcel->getActiveSheet()->getCell("B65")->getValue();
                $asset1['totalDeferIntangible'] = $objPHPexcel->getActiveSheet()->getCell("B67")->getValue();

                $asset1['deferLoan'] = $objPHPexcel->getActiveSheet()->getCell("B68")->getValue();
                $asset1['totalAssets'] = $objPHPexcel->getActiveSheet()->getCell("B70")->getValue();
                $data2[] = $asset1;

                //负债表
                $debt1['creator'] = $userid;//创建人
                $debt1['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $debt1['applicationid'] = $applicationid;
                $debt1['activeid'] = $activeid;
                $debt1['year'] = substr($objPHPexcel->getActiveSheet()->getCell("B1")->getValue(), 0, 4);
                $debt1['short_termBorrowing'] = $objPHPexcel->getActiveSheet()->getCell("B72")->getValue();
                $debt1['payableBill'] = $objPHPexcel->getActiveSheet()->getCell("B73")->getValue();
                $debt1['payableAccount'] = $objPHPexcel->getActiveSheet()->getCell("B74")->getValue();
                $debt1['advanceAccount'] = $objPHPexcel->getActiveSheet()->getCell("B75")->getValue();
                $debt1['consignGoods'] = $objPHPexcel->getActiveSheet()->getCell("B76")->getValue();
                $debt1['payableWages'] = $objPHPexcel->getActiveSheet()->getCell("B77")->getValue();
                $debt1['payableWelfare'] = $objPHPexcel->getActiveSheet()->getCell("B78")->getValue();
                $debt1['payableEquity'] = $objPHPexcel->getActiveSheet()->getCell("B79")->getValue();
                $debt1['payableTax'] = $objPHPexcel->getActiveSheet()->getCell("B80")->getValue();
                $debt1['payableProfit'] = $objPHPexcel->getActiveSheet()->getCell("B81")->getValue();
                $debt1['otherPayable'] = $objPHPexcel->getActiveSheet()->getCell("B82")->getValue();
                $debt1['advanceExpense'] = $objPHPexcel->getActiveSheet()->getCell("B83")->getValue();
                $debt1['preDebt'] = $objPHPexcel->getActiveSheet()->getCell("B84")->getValue();
                $debt1['longdebt'] = $objPHPexcel->getActiveSheet()->getCell("B85")->getValue();
                $debt1['otherDebt'] = $objPHPexcel->getActiveSheet()->getCell("B86")->getValue();
                $debt1['totalDebtFlow'] = $objPHPexcel->getActiveSheet()->getCell("B88")->getValue();

                $debt1['long_termBorrowing'] = $objPHPexcel->getActiveSheet()->getCell("B89")->getValue();
                $debt1['payableDebt'] = $objPHPexcel->getActiveSheet()->getCell("B90")->getValue();
                $debt1['longPayableDebt'] = $objPHPexcel->getActiveSheet()->getCell("B91")->getValue();
                $debt1['specialPayable'] = $objPHPexcel->getActiveSheet()->getCell("B92")->getValue();
                $debt1['houseTurnover'] = $objPHPexcel->getActiveSheet()->getCell("B93")->getValue();
                $debt1['otherLongDebt'] = $objPHPexcel->getActiveSheet()->getCell("B94")->getValue();
                $debt1['totalLong_termDebt'] = $objPHPexcel->getActiveSheet()->getCell("B96")->getValue();

                $debt1['dydksx'] = $objPHPexcel->getActiveSheet()->getCell("B97")->getValue();
                $debt1['totalDebt'] = $objPHPexcel->getActiveSheet()->getCell("B99")->getValue();

                $debt1['capitalCollect'] = $objPHPexcel->getActiveSheet()->getCell("B100")->getValue();
                $debt1['capitalReserve'] = $objPHPexcel->getActiveSheet()->getCell("B101")->getValue();
                $debt1['surplusReserve'] = $objPHPexcel->getActiveSheet()->getCell("B102")->getValue();
                $debt1['welfare'] = $objPHPexcel->getActiveSheet()->getCell("B103")->getValue();
                $debt1['unconfirmInvestLoss'] = $objPHPexcel->getActiveSheet()->getCell("B104")->getValue();
                $debt1['undistributedprofit'] = $objPHPexcel->getActiveSheet()->getCell("B105")->getValue();
                $debt1['foreignDiscount'] = $objPHPexcel->getActiveSheet()->getCell("B106")->getValue();
                $debt1['totalOwnerEquity'] = $objPHPexcel->getActiveSheet()->getCell("B108")->getValue();
                $debt1['total'] = $objPHPexcel->getActiveSheet()->getCell("B109")->getValue();
                $data3[] = $debt1;
            }

            $year2 = substr($objPHPexcel->getActiveSheet()->getCell("C1")->getValue(), 0, 4);
            if (in_array($year2, $year_arr)) {
                //损益表
                $sunyi2['creator'] = $userid;//创建人
                $sunyi2['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $sunyi2['applicationid'] = $applicationid;
                $sunyi2['activeid'] = $activeid;
                $sunyi2['year'] = substr($objPHPexcel->getActiveSheet()->getCell("C1")->getValue(), 0, 4);
                $sunyi2['mainIncome'] = $objPHPexcel->getActiveSheet()->getCell("C2")->getValue();
                $sunyi2['mainCost'] = $objPHPexcel->getActiveSheet()->getCell("C3")->getValue();
                $sunyi2['tax_addition'] = $objPHPexcel->getActiveSheet()->getCell("C4")->getValue();

                $sunyi2['mainPrpfit_YW'] = $objPHPexcel->getActiveSheet()->getCell("C6")->getValue();
                $sunyi2['otherPrpfit_YW'] = $objPHPexcel->getActiveSheet()->getCell("C7")->getValue();
                $sunyi2['business'] = $objPHPexcel->getActiveSheet()->getCell("C8")->getValue();
                $sunyi2['manage'] = $objPHPexcel->getActiveSheet()->getCell("C9")->getValue();
                $sunyi2['financial'] = $objPHPexcel->getActiveSheet()->getCell("C10")->getValue();
                // $sunyi2['sumCost'] = $objPHPexcel->getActiveSheet()->getCell("C11")->getValue();
                $sunyi2['interestExpense'] = $objPHPexcel->getActiveSheet()->getCell("C12")->getValue();
                $sunyi2['exchange'] = $objPHPexcel->getActiveSheet()->getCell("C13")->getValue();

                $sunyi2['profit_YY'] = $objPHPexcel->getActiveSheet()->getCell("C15")->getValue();
                $sunyi2['investIncome'] = $objPHPexcel->getActiveSheet()->getCell("C16")->getValue();
                $sunyi2['subsidyIncome'] = $objPHPexcel->getActiveSheet()->getCell("C17")->getValue();
                $sunyi2['otherBusinessIn'] = $objPHPexcel->getActiveSheet()->getCell("C18")->getValue();
                $sunyi2['otherBusinessOut'] = $objPHPexcel->getActiveSheet()->getCell("C19")->getValue();


                $sunyi2['totalProfit'] = $objPHPexcel->getActiveSheet()->getCell("C21")->getValue();
                $sunyi2['incomeTax'] = $objPHPexcel->getActiveSheet()->getCell("C22")->getValue();
                $sunyi2['shareholderSunyi'] = $objPHPexcel->getActiveSheet()->getCell("C23")->getValue();
                $sunyi2['investSunyi'] = $objPHPexcel->getActiveSheet()->getCell("C24")->getValue();
                $sunyi2['other'] = $objPHPexcel->getActiveSheet()->getCell("C25")->getValue();
                $sunyi2['profit_SH'] = $objPHPexcel->getActiveSheet()->getCell("C27")->getValue();
                $data1[] = $sunyi2;

                //资产表
                $asset2['creator'] = $userid;//创建人
                $asset2['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $asset2['applicationid'] = $applicationid;
                $asset2['activeid'] = $activeid;
                $asset2['year'] = substr($objPHPexcel->getActiveSheet()->getCell("C1")->getValue(), 0, 4);
                $asset2['currencyFunds'] = $objPHPexcel->getActiveSheet()->getCell("C29")->getValue();
                $asset2['short_termInvestment'] = $objPHPexcel->getActiveSheet()->getCell("C30")->getValue();
                $asset2['receivableBill'] = $objPHPexcel->getActiveSheet()->getCell("C31")->getValue();
                $asset2['receivableDividend'] = $objPHPexcel->getActiveSheet()->getCell("C32")->getValue();
                $asset2['receivableInterest'] = $objPHPexcel->getActiveSheet()->getCell("C33")->getValue();
                $asset2['receivableAccount'] = $objPHPexcel->getActiveSheet()->getCell("C34")->getValue();
                $asset2['otherReceivableAccount'] = $objPHPexcel->getActiveSheet()->getCell("C35")->getValue();
                $asset2['prepayAccount'] = $objPHPexcel->getActiveSheet()->getCell("C36")->getValue();
                $asset2['receivableSubsidy'] = $objPHPexcel->getActiveSheet()->getCell("C37")->getValue();
                $asset2['stock'] = $objPHPexcel->getActiveSheet()->getCell("C38")->getValue();
                $asset2['apportionCost'] = $objPHPexcel->getActiveSheet()->getCell("C39")->getValue();
                $asset2['bondInvest'] = $objPHPexcel->getActiveSheet()->getCell("C40")->getValue();
                $asset2['flowAssetsLoss'] = $objPHPexcel->getActiveSheet()->getCell("C41")->getValue();
                $asset2['otherCurrentAssets'] = $objPHPexcel->getActiveSheet()->getCell("C42")->getValue();
                $asset2['totalCurrentAssets'] = $objPHPexcel->getActiveSheet()->getCell("C44")->getValue();

                $asset2['long_termInvestment1'] = $objPHPexcel->getActiveSheet()->getCell("C45")->getValue();
                $asset2['long_termInvestment2'] = $objPHPexcel->getActiveSheet()->getCell("C46")->getValue();
                $asset2['long_termInvestment3'] = $objPHPexcel->getActiveSheet()->getCell("C47")->getValue();
                $asset2['totalLong_termInvestment'] = $objPHPexcel->getActiveSheet()->getCell("C49")->getValue();

                $asset2['fixedAssetsOriginial'] = $objPHPexcel->getActiveSheet()->getCell("C50")->getValue();
                $asset2['fixedAssetsOriginial'] = $objPHPexcel->getActiveSheet()->getCell("C51")->getValue();
                $asset2['fixedAssetsNet'] = $asset2['fixedAssetsOriginial'] - $asset2['fixedAssetsOriginial'];
                $asset2['fixedAssetsDecrease'] = $objPHPexcel->getActiveSheet()->getCell("C53")->getValue();

                $asset2['fixedAssetsNet1'] = $asset2['fixedAssetsNet'] - $asset2['fixedAssetsDecrease'];
                $asset2['projectMaterial'] = $objPHPexcel->getActiveSheet()->getCell("C55")->getValue();
                $asset2['constructionProject'] = $objPHPexcel->getActiveSheet()->getCell("C56")->getValue();
                $asset2['fixedAssetsClear'] = $objPHPexcel->getActiveSheet()->getCell("C57")->getValue();
                $asset2['fixedAssetsLoss'] = $objPHPexcel->getActiveSheet()->getCell("C58")->getValue();
                $asset2['totalFixedAssets'] = $objPHPexcel->getActiveSheet()->getCell("C60")->getValue();


                $asset2['intangibleAssets'] = $objPHPexcel->getActiveSheet()->getCell("C61")->getValue();
                $asset2['deferAssets'] = $objPHPexcel->getActiveSheet()->getCell("C62")->getValue();
                $asset2['startCost'] = $objPHPexcel->getActiveSheet()->getCell("C63")->getValue();
                $asset2['longApportionCost'] = $objPHPexcel->getActiveSheet()->getCell("C64")->getValue();
                $asset2['otherLongAssets'] = $objPHPexcel->getActiveSheet()->getCell("C65")->getValue();
                $asset2['totalDeferIntangible'] = $objPHPexcel->getActiveSheet()->getCell("C67")->getValue();

                $asset2['deferLoan'] = $objPHPexcel->getActiveSheet()->getCell("C68")->getValue();
                $asset2['totalAssets'] = $objPHPexcel->getActiveSheet()->getCell("C70")->getValue();
                $data2[] = $asset2;

                //负债表
                $debt2['creator'] = $userid;//创建人
                $debt2['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $debt2['applicationid'] = $applicationid;
                $debt2['activeid'] = $activeid;
                $debt2['year'] = substr($objPHPexcel->getActiveSheet()->getCell("C1")->getValue(), 0, 4);
                $debt2['short_termBorrowing'] = $objPHPexcel->getActiveSheet()->getCell("C72")->getValue();
                $debt2['payableBill'] = $objPHPexcel->getActiveSheet()->getCell("C73")->getValue();
                $debt2['payableAccount'] = $objPHPexcel->getActiveSheet()->getCell("C74")->getValue();
                $debt2['advanceAccount'] = $objPHPexcel->getActiveSheet()->getCell("C75")->getValue();
                $debt2['consignGoods'] = $objPHPexcel->getActiveSheet()->getCell("C76")->getValue();
                $debt2['payableWages'] = $objPHPexcel->getActiveSheet()->getCell("C77")->getValue();
                $debt2['payableWelfare'] = $objPHPexcel->getActiveSheet()->getCell("C78")->getValue();
                $debt2['payableEquity'] = $objPHPexcel->getActiveSheet()->getCell("C79")->getValue();
                $debt2['payableTax'] = $objPHPexcel->getActiveSheet()->getCell("C80")->getValue();
                $debt2['payableProfit'] = $objPHPexcel->getActiveSheet()->getCell("C81")->getValue();
                $debt2['otherPayable'] = $objPHPexcel->getActiveSheet()->getCell("C82")->getValue();
                $debt2['advanceExpense'] = $objPHPexcel->getActiveSheet()->getCell("C83")->getValue();
                $debt2['preDebt'] = $objPHPexcel->getActiveSheet()->getCell("C84")->getValue();
                $debt2['longdebt'] = $objPHPexcel->getActiveSheet()->getCell("C85")->getValue();
                $debt2['otherDebt'] = $objPHPexcel->getActiveSheet()->getCell("C86")->getValue();
                $debt2['totalDebtFlow'] = $objPHPexcel->getActiveSheet()->getCell("C88")->getValue();

                $debt2['long_termBorrowing'] = $objPHPexcel->getActiveSheet()->getCell("C89")->getValue();
                $debt2['payableDebt'] = $objPHPexcel->getActiveSheet()->getCell("C90")->getValue();
                $debt2['longPayableDebt'] = $objPHPexcel->getActiveSheet()->getCell("C91")->getValue();
                $debt2['specialPayable'] = $objPHPexcel->getActiveSheet()->getCell("C92")->getValue();
                $debt2['houseTurnover'] = $objPHPexcel->getActiveSheet()->getCell("C93")->getValue();
                $debt2['otherLongDebt'] = $objPHPexcel->getActiveSheet()->getCell("C94")->getValue();
                $debt2['totalLong_termDebt'] = $objPHPexcel->getActiveSheet()->getCell("C96")->getValue();

                $debt2['dydksx'] = $objPHPexcel->getActiveSheet()->getCell("C97")->getValue();
                $debt2['totalDebt'] = $objPHPexcel->getActiveSheet()->getCell("C99")->getValue();

                $debt2['capitalCollect'] = $objPHPexcel->getActiveSheet()->getCell("C100")->getValue();
                $debt2['capitalReserve'] = $objPHPexcel->getActiveSheet()->getCell("C101")->getValue();
                $debt2['surplusReserve'] = $objPHPexcel->getActiveSheet()->getCell("C102")->getValue();
                $debt2['welfare'] = $objPHPexcel->getActiveSheet()->getCell("C103")->getValue();
                $debt2['unconfirmInvestLoss'] = $objPHPexcel->getActiveSheet()->getCell("C104")->getValue();
                $debt2['undistributedprofit'] = $objPHPexcel->getActiveSheet()->getCell("C105")->getValue();
                $debt2['foreignDiscount'] = $objPHPexcel->getActiveSheet()->getCell("C106")->getValue();
                $debt2['totalOwnerEquity'] = $objPHPexcel->getActiveSheet()->getCell("C108")->getValue();
                $debt2['total'] = $objPHPexcel->getActiveSheet()->getCell("C109")->getValue();
                $data3[] = $debt2;
            }

            $year3 = substr($objPHPexcel->getActiveSheet()->getCell("D1")->getValue(), 0, 4);
            if (in_array($year3, $year_arr)) {

                //损益表
                $sunyi3['creator'] = $userid;//创建人
                $sunyi3['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $sunyi3['applicationid'] = $applicationid;
                $sunyi3['activeid'] = $activeid;
                $sunyi3['year'] = substr($objPHPexcel->getActiveSheet()->getCell("D1")->getValue(), 0, 4);
                $sunyi3['mainIncome'] = $objPHPexcel->getActiveSheet()->getCell("D2")->getValue();
                $sunyi3['mainCost'] = $objPHPexcel->getActiveSheet()->getCell("D3")->getValue();
                $sunyi3['tax_addition'] = $objPHPexcel->getActiveSheet()->getCell("D4")->getValue();

                $sunyi3['mainPrpfit_YW'] = $objPHPexcel->getActiveSheet()->getCell("D6")->getValue();
                $sunyi3['otherPrpfit_YW'] = $objPHPexcel->getActiveSheet()->getCell("D7")->getValue();
                $sunyi3['business'] = $objPHPexcel->getActiveSheet()->getCell("D8")->getValue();
                $sunyi3['manage'] = $objPHPexcel->getActiveSheet()->getCell("D9")->getValue();
                $sunyi3['financial'] = $objPHPexcel->getActiveSheet()->getCell("D10")->getValue();
                // $sunyi3['sumCost'] = $objPHPexcel->getActiveSheet()->getCell("D11")->getValue();
                $sunyi3['interestExpense'] = $objPHPexcel->getActiveSheet()->getCell("D12")->getValue();
                $sunyi3['exchange'] = $objPHPexcel->getActiveSheet()->getCell("D13")->getValue();

                $sunyi3['profit_YY'] = $objPHPexcel->getActiveSheet()->getCell("D15")->getValue();
                $sunyi3['investIncome'] = $objPHPexcel->getActiveSheet()->getCell("D16")->getValue();
                $sunyi3['subsidyIncome'] = $objPHPexcel->getActiveSheet()->getCell("D17")->getValue();
                $sunyi3['otherBusinessIn'] = $objPHPexcel->getActiveSheet()->getCell("D18")->getValue();
                $sunyi3['otherBusinessOut'] = $objPHPexcel->getActiveSheet()->getCell("D19")->getValue();


                $sunyi3['totalProfit'] = $objPHPexcel->getActiveSheet()->getCell("D21")->getValue();
                $sunyi3['incomeTax'] = $objPHPexcel->getActiveSheet()->getCell("D22")->getValue();
                $sunyi3['shareholderSunyi'] = $objPHPexcel->getActiveSheet()->getCell("D23")->getValue();
                $sunyi3['investSunyi'] = $objPHPexcel->getActiveSheet()->getCell("D24")->getValue();
                $sunyi3['other'] = $objPHPexcel->getActiveSheet()->getCell("D25")->getValue();
                $sunyi3['profit_SH'] = $objPHPexcel->getActiveSheet()->getCell("D27")->getValue();
                $data1[] = $sunyi3;

                //资产表
                $asset3['creator'] = $userid;//创建人
                $asset3['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $asset3['applicationid'] = $applicationid;
                $asset3['activeid'] = $activeid;
                $asset3['year'] = substr($objPHPexcel->getActiveSheet()->getCell("D1")->getValue(), 0, 4);
                $asset3['currencyFunds'] = $objPHPexcel->getActiveSheet()->getCell("D29")->getValue();
                $asset3['short_termInvestment'] = $objPHPexcel->getActiveSheet()->getCell("D30")->getValue();
                $asset3['receivableBill'] = $objPHPexcel->getActiveSheet()->getCell("D31")->getValue();
                $asset3['receivableDividend'] = $objPHPexcel->getActiveSheet()->getCell("D32")->getValue();
                $asset3['receivableInterest'] = $objPHPexcel->getActiveSheet()->getCell("D33")->getValue();
                $asset3['receivableAccount'] = $objPHPexcel->getActiveSheet()->getCell("D34")->getValue();
                $asset3['otherReceivableAccount'] = $objPHPexcel->getActiveSheet()->getCell("D35")->getValue();
                $asset3['prepayAccount'] = $objPHPexcel->getActiveSheet()->getCell("D36")->getValue();
                $asset3['receivableSubsidy'] = $objPHPexcel->getActiveSheet()->getCell("D37")->getValue();
                $asset3['stock'] = $objPHPexcel->getActiveSheet()->getCell("D38")->getValue();
                $asset3['apportionCost'] = $objPHPexcel->getActiveSheet()->getCell("D39")->getValue();
                $asset3['bondInvest'] = $objPHPexcel->getActiveSheet()->getCell("D40")->getValue();
                $asset3['flowAssetsLoss'] = $objPHPexcel->getActiveSheet()->getCell("D41")->getValue();
                $asset3['otherCurrentAssets'] = $objPHPexcel->getActiveSheet()->getCell("D42")->getValue();
                $asset3['totalCurrentAssets'] = $objPHPexcel->getActiveSheet()->getCell("D44")->getValue();

                $asset3['long_termInvestment1'] = $objPHPexcel->getActiveSheet()->getCell("D45")->getValue();
                $asset3['long_termInvestment2'] = $objPHPexcel->getActiveSheet()->getCell("D46")->getValue();
                $asset3['long_termInvestment3'] = $objPHPexcel->getActiveSheet()->getCell("D47")->getValue();
                $asset3['totalLong_termInvestment'] = $objPHPexcel->getActiveSheet()->getCell("D49")->getValue();

                $asset3['fixedAssetsOriginial'] = $objPHPexcel->getActiveSheet()->getCell("D50")->getValue();
                $asset3['cumulativeDiscounts'] = $objPHPexcel->getActiveSheet()->getCell("D51")->getValue();
                $asset3['fixedAssetsNet'] = $asset3['fixedAssetsOriginial'] - $asset3['cumulativeDiscounts'];
                $asset3['fixedAssetsDecrease'] = $objPHPexcel->getActiveSheet()->getCell("D53")->getValue();

                $asset3['fixedAssetsNet1'] = $asset3['fixedAssetsNet'] - $asset3['fixedAssetsDecrease'];
                $asset3['projectMaterial'] = $objPHPexcel->getActiveSheet()->getCell("D55")->getValue();
                $asset3['constructionProject'] = $objPHPexcel->getActiveSheet()->getCell("D56")->getValue();
                $asset3['fixedAssetsClear'] = $objPHPexcel->getActiveSheet()->getCell("D57")->getValue();
                $asset3['fixedAssetsLoss'] = $objPHPexcel->getActiveSheet()->getCell("D58")->getValue();
                $asset3['totalFixedAssets'] = $objPHPexcel->getActiveSheet()->getCell("D60")->getValue();


                $asset3['intangibleAssets'] = $objPHPexcel->getActiveSheet()->getCell("D61")->getValue();
                $asset3['deferAssets'] = $objPHPexcel->getActiveSheet()->getCell("D62")->getValue();
                $asset3['startCost'] = $objPHPexcel->getActiveSheet()->getCell("D63")->getValue();
                $asset3['longApportionCost'] = $objPHPexcel->getActiveSheet()->getCell("D64")->getValue();
                $asset3['otherLongAssets'] = $objPHPexcel->getActiveSheet()->getCell("D65")->getValue();
                $asset3['totalDeferIntangible'] = $objPHPexcel->getActiveSheet()->getCell("D67")->getValue();

                $asset3['deferLoan'] = $objPHPexcel->getActiveSheet()->getCell("D68")->getValue();
                $asset3['totalAssets'] = $objPHPexcel->getActiveSheet()->getCell("D70")->getValue();
                $data2[] = $asset3;

                //负债表
                $debt3['creator'] = $userid;//创建人
                $debt3['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $debt3['applicationid'] = $applicationid;
                $debt3['activeid'] = $activeid;
                $debt3['year'] = substr($objPHPexcel->getActiveSheet()->getCell("D1")->getValue(), 0, 4);
                $debt3['short_termBorrowing'] = $objPHPexcel->getActiveSheet()->getCell("D72")->getValue();
                $debt3['payableBill'] = $objPHPexcel->getActiveSheet()->getCell("D73")->getValue();
                $debt3['payableAccount'] = $objPHPexcel->getActiveSheet()->getCell("D74")->getValue();
                $debt3['advanceAccount'] = $objPHPexcel->getActiveSheet()->getCell("D75")->getValue();
                $debt3['consignGoods'] = $objPHPexcel->getActiveSheet()->getCell("D76")->getValue();
                $debt3['payableWages'] = $objPHPexcel->getActiveSheet()->getCell("D77")->getValue();
                $debt3['payableWelfare'] = $objPHPexcel->getActiveSheet()->getCell("D78")->getValue();
                $debt3['payableEquity'] = $objPHPexcel->getActiveSheet()->getCell("D79")->getValue();
                $debt3['payableTax'] = $objPHPexcel->getActiveSheet()->getCell("D80")->getValue();
                $debt3['payableProfit'] = $objPHPexcel->getActiveSheet()->getCell("D81")->getValue();
                $debt3['otherPayable'] = $objPHPexcel->getActiveSheet()->getCell("D82")->getValue();
                $debt3['advanceExpense'] = $objPHPexcel->getActiveSheet()->getCell("D83")->getValue();
                $debt3['preDebt'] = $objPHPexcel->getActiveSheet()->getCell("D84")->getValue();
                $debt3['longdebt'] = $objPHPexcel->getActiveSheet()->getCell("D85")->getValue();
                $debt3['otherDebt'] = $objPHPexcel->getActiveSheet()->getCell("D86")->getValue();
                $debt3['totalDebtFlow'] = $objPHPexcel->getActiveSheet()->getCell("D88")->getValue();

                $debt3['long_termBorrowing'] = $objPHPexcel->getActiveSheet()->getCell("D89")->getValue();
                $debt3['payableDebt'] = $objPHPexcel->getActiveSheet()->getCell("D90")->getValue();
                $debt3['longPayableDebt'] = $objPHPexcel->getActiveSheet()->getCell("D91")->getValue();
                $debt3['specialPayable'] = $objPHPexcel->getActiveSheet()->getCell("D92")->getValue();
                $debt3['houseTurnover'] = $objPHPexcel->getActiveSheet()->getCell("D93")->getValue();
                $debt3['otherLongDebt'] = $objPHPexcel->getActiveSheet()->getCell("D94")->getValue();
                $debt3['totalLong_termDebt'] = $objPHPexcel->getActiveSheet()->getCell("D96")->getValue();

                $debt3['dydksx'] = $objPHPexcel->getActiveSheet()->getCell("D97")->getValue();
                $debt3['totalDebt'] = $objPHPexcel->getActiveSheet()->getCell("D99")->getValue();

                $debt3['capitalCollect'] = $objPHPexcel->getActiveSheet()->getCell("D100")->getValue();
                $debt3['capitalReserve'] = $objPHPexcel->getActiveSheet()->getCell("D101")->getValue();
                $debt3['surplusReserve'] = $objPHPexcel->getActiveSheet()->getCell("D102")->getValue();
                $debt3['welfare'] = $objPHPexcel->getActiveSheet()->getCell("D103")->getValue();
                $debt3['unconfirmInvestLoss'] = $objPHPexcel->getActiveSheet()->getCell("D104")->getValue();
                $debt3['undistributedprofit'] = $objPHPexcel->getActiveSheet()->getCell("D105")->getValue();
                $debt3['foreignDiscount'] = $objPHPexcel->getActiveSheet()->getCell("D106")->getValue();
                $debt3['totalOwnerEquity'] = $objPHPexcel->getActiveSheet()->getCell("D108")->getValue();
                $debt3['total'] = $objPHPexcel->getActiveSheet()->getCell("D109")->getValue();
                $data3[] = $debt3;
            }

            $year4 = substr($objPHPexcel->getActiveSheet()->getCell("E1")->getValue(), 0, 4);
            if (in_array($year4, $year_arr)) {


                //损益表
                $sunyi4['creator'] = $userid;//创建人
                $sunyi4['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $sunyi4['applicationid'] = $applicationid;
                $sunyi4['activeid'] = $activeid;
                $sunyi4['year'] = substr($objPHPexcel->getActiveSheet()->getCell("E1")->getValue(), 0, 4);
                $sunyi4['mainIncome'] = $objPHPexcel->getActiveSheet()->getCell("E2")->getValue();
                $sunyi4['mainCost'] = $objPHPexcel->getActiveSheet()->getCell("E3")->getValue();
                $sunyi4['tax_addition'] = $objPHPexcel->getActiveSheet()->getCell("E4")->getValue();

                $sunyi4['mainPrpfit_YW'] = $objPHPexcel->getActiveSheet()->getCell("E6")->getValue();
                $sunyi4['otherPrpfit_YW'] = $objPHPexcel->getActiveSheet()->getCell("E7")->getValue();
                $sunyi4['business'] = $objPHPexcel->getActiveSheet()->getCell("E8")->getValue();
                $sunyi4['manage'] = $objPHPexcel->getActiveSheet()->getCell("E9")->getValue();
                $sunyi4['financial'] = $objPHPexcel->getActiveSheet()->getCell("E10")->getValue();
                // $sunyi4['sumCost'] = $objPHPexcel->getActiveSheet()->getCell("E11")->getValue();
                $sunyi4['interestExpense'] = $objPHPexcel->getActiveSheet()->getCell("E12")->getValue();
                $sunyi4['exchange'] = $objPHPexcel->getActiveSheet()->getCell("E13")->getValue();

                $sunyi4['profit_YY'] = $objPHPexcel->getActiveSheet()->getCell("E15")->getValue();
                $sunyi4['investIncome'] = $objPHPexcel->getActiveSheet()->getCell("E16")->getValue();
                $sunyi4['subsidyIncome'] = $objPHPexcel->getActiveSheet()->getCell("E17")->getValue();
                $sunyi4['otherBusinessIn'] = $objPHPexcel->getActiveSheet()->getCell("E18")->getValue();
                $sunyi4['otherBusinessOut'] = $objPHPexcel->getActiveSheet()->getCell("E19")->getValue();


                $sunyi4['totalProfit'] = $objPHPexcel->getActiveSheet()->getCell("E21")->getValue();
                $sunyi4['incomeTax'] = $objPHPexcel->getActiveSheet()->getCell("E22")->getValue();
                $sunyi4['shareholderSunyi'] = $objPHPexcel->getActiveSheet()->getCell("E23")->getValue();
                $sunyi4['investSunyi'] = $objPHPexcel->getActiveSheet()->getCell("E24")->getValue();
                $sunyi4['other'] = $objPHPexcel->getActiveSheet()->getCell("E25")->getValue();
                $sunyi4['profit_SH'] = $objPHPexcel->getActiveSheet()->getCell("E27")->getValue();
                $data1[] = $sunyi4;

                //资产表
                $asset4['creator'] = $userid;//创建人
                $asset4['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $asset4['applicationid'] = $applicationid;
                $asset4['activeid'] = $activeid;
                $asset4['year'] = substr($objPHPexcel->getActiveSheet()->getCell("E1")->getValue(), 0, 4);
                $asset4['currencyFunds'] = $objPHPexcel->getActiveSheet()->getCell("E29")->getValue();
                $asset4['short_termInvestment'] = $objPHPexcel->getActiveSheet()->getCell("E30")->getValue();
                $asset4['receivableBill'] = $objPHPexcel->getActiveSheet()->getCell("E31")->getValue();
                $asset4['receivableDividend'] = $objPHPexcel->getActiveSheet()->getCell("E32")->getValue();
                $asset4['receivableInterest'] = $objPHPexcel->getActiveSheet()->getCell("E33")->getValue();
                $asset4['receivableAccount'] = $objPHPexcel->getActiveSheet()->getCell("E34")->getValue();
                $asset4['otherReceivableAccount'] = $objPHPexcel->getActiveSheet()->getCell("E35")->getValue();
                $asset4['prepayAccount'] = $objPHPexcel->getActiveSheet()->getCell("E36")->getValue();
                $asset4['receivableSubsidy'] = $objPHPexcel->getActiveSheet()->getCell("E37")->getValue();
                $asset4['stock'] = $objPHPexcel->getActiveSheet()->getCell("E38")->getValue();
                $asset4['apportionCost'] = $objPHPexcel->getActiveSheet()->getCell("E39")->getValue();
                $asset4['bondInvest'] = $objPHPexcel->getActiveSheet()->getCell("E40")->getValue();
                $asset4['flowAssetsLoss'] = $objPHPexcel->getActiveSheet()->getCell("E41")->getValue();
                $asset4['otherCurrentAssets'] = $objPHPexcel->getActiveSheet()->getCell("E42")->getValue();
                $asset4['totalCurrentAssets'] = $objPHPexcel->getActiveSheet()->getCell("E44")->getValue();

                $asset4['long_termInvestment1'] = $objPHPexcel->getActiveSheet()->getCell("E45")->getValue();
                $asset4['long_termInvestment2'] = $objPHPexcel->getActiveSheet()->getCell("E46")->getValue();
                $asset4['long_termInvestment3'] = $objPHPexcel->getActiveSheet()->getCell("E47")->getValue();
                $asset4['totalLong_termInvestment'] = $objPHPexcel->getActiveSheet()->getCell("E49")->getValue();

                $asset4['fixedAssetsOriginial'] = $objPHPexcel->getActiveSheet()->getCell("E50")->getValue();
                $asset4['cumulativeDiscounts'] = $objPHPexcel->getActiveSheet()->getCell("E51")->getValue();
                $asset4['fixedAssetsNet'] = $asset4['fixedAssetsOriginial'] - $asset4['cumulativeDiscounts'];
                $asset4['fixedAssetsDecrease'] = $objPHPexcel->getActiveSheet()->getCell("E53")->getValue();

                $asset4['fixedAssetsNet1'] = $asset4['fixedAssetsNet'] - $asset4['fixedAssetsDecrease'];
                $asset4['projectMaterial'] = $objPHPexcel->getActiveSheet()->getCell("E55")->getValue();
                $asset4['constructionProject'] = $objPHPexcel->getActiveSheet()->getCell("E56")->getValue();
                $asset4['fixedAssetsClear'] = $objPHPexcel->getActiveSheet()->getCell("E57")->getValue();
                $asset4['fixedAssetsLoss'] = $objPHPexcel->getActiveSheet()->getCell("E58")->getValue();
                $asset4['totalFixedAssets'] = $objPHPexcel->getActiveSheet()->getCell("E60")->getValue();


                $asset4['intangibleAssets'] = $objPHPexcel->getActiveSheet()->getCell("E61")->getValue();
                $asset4['deferAssets'] = $objPHPexcel->getActiveSheet()->getCell("E62")->getValue();
                $asset4['startCost'] = $objPHPexcel->getActiveSheet()->getCell("E63")->getValue();
                $asset4['longApportionCost'] = $objPHPexcel->getActiveSheet()->getCell("E64")->getValue();
                $asset4['otherLongAssets'] = $objPHPexcel->getActiveSheet()->getCell("E65")->getValue();
                $asset4['totalDeferIntangible'] = $objPHPexcel->getActiveSheet()->getCell("E67")->getValue();

                $asset4['deferLoan'] = $objPHPexcel->getActiveSheet()->getCell("E68")->getValue();
                $asset4['totalAssets'] = $objPHPexcel->getActiveSheet()->getCell("E70")->getValue();
                $data2[] = $asset4;

                //负债表
                $debt4['creator'] = $userid;//创建人
                $debt4['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $debt4['applicationid'] = $applicationid;
                $debt4['activeid'] = $activeid;
                $debt4['year'] = substr($objPHPexcel->getActiveSheet()->getCell("E1")->getValue(), 0, 4);
                $debt4['short_termBorrowing'] = $objPHPexcel->getActiveSheet()->getCell("E72")->getValue();
                $debt4['payableBill'] = $objPHPexcel->getActiveSheet()->getCell("E73")->getValue();
                $debt4['payableAccount'] = $objPHPexcel->getActiveSheet()->getCell("E74")->getValue();
                $debt4['advanceAccount'] = $objPHPexcel->getActiveSheet()->getCell("E75")->getValue();
                $debt4['consignGoods'] = $objPHPexcel->getActiveSheet()->getCell("E76")->getValue();
                $debt4['payableWages'] = $objPHPexcel->getActiveSheet()->getCell("E77")->getValue();
                $debt4['payableWelfare'] = $objPHPexcel->getActiveSheet()->getCell("E78")->getValue();
                $debt4['payableEquity'] = $objPHPexcel->getActiveSheet()->getCell("E79")->getValue();
                $debt4['payableTax'] = $objPHPexcel->getActiveSheet()->getCell("E80")->getValue();
                $debt4['payableProfit'] = $objPHPexcel->getActiveSheet()->getCell("E81")->getValue();
                $debt4['otherPayable'] = $objPHPexcel->getActiveSheet()->getCell("E82")->getValue();
                $debt4['advanceExpense'] = $objPHPexcel->getActiveSheet()->getCell("E83")->getValue();
                $debt4['preDebt'] = $objPHPexcel->getActiveSheet()->getCell("E84")->getValue();
                $debt4['longdebt'] = $objPHPexcel->getActiveSheet()->getCell("E85")->getValue();
                $debt4['otherDebt'] = $objPHPexcel->getActiveSheet()->getCell("E86")->getValue();
                $debt4['totalDebtFlow'] = $objPHPexcel->getActiveSheet()->getCell("E88")->getValue();

                $debt4['long_termBorrowing'] = $objPHPexcel->getActiveSheet()->getCell("E89")->getValue();
                $debt4['payableDebt'] = $objPHPexcel->getActiveSheet()->getCell("E90")->getValue();
                $debt4['longPayableDebt'] = $objPHPexcel->getActiveSheet()->getCell("E91")->getValue();
                $debt4['specialPayable'] = $objPHPexcel->getActiveSheet()->getCell("E92")->getValue();
                $debt4['houseTurnover'] = $objPHPexcel->getActiveSheet()->getCell("E93")->getValue();
                $debt4['otherLongDebt'] = $objPHPexcel->getActiveSheet()->getCell("E94")->getValue();
                $debt4['totalLong_termDebt'] = $objPHPexcel->getActiveSheet()->getCell("E96")->getValue();

                $debt4['dydksx'] = $objPHPexcel->getActiveSheet()->getCell("E97")->getValue();
                $debt4['totalDebt'] = $objPHPexcel->getActiveSheet()->getCell("E99")->getValue();

                $debt4['capitalCollect'] = $objPHPexcel->getActiveSheet()->getCell("E100")->getValue();
                $debt4['capitalReserve'] = $objPHPexcel->getActiveSheet()->getCell("E101")->getValue();
                $debt4['surplusReserve'] = $objPHPexcel->getActiveSheet()->getCell("E102")->getValue();
                $debt4['welfare'] = $objPHPexcel->getActiveSheet()->getCell("E103")->getValue();
                $debt4['unconfirmInvestLoss'] = $objPHPexcel->getActiveSheet()->getCell("E104")->getValue();
                $debt4['undistributedprofit'] = $objPHPexcel->getActiveSheet()->getCell("E105")->getValue();
                $debt4['foreignDiscount'] = $objPHPexcel->getActiveSheet()->getCell("E106")->getValue();
                $debt4['totalOwnerEquity'] = $objPHPexcel->getActiveSheet()->getCell("E108")->getValue();
                $debt4['total'] = $objPHPexcel->getActiveSheet()->getCell("E109")->getValue();
                $data3[] = $debt4;
            }

            $year5 = substr($objPHPexcel->getActiveSheet()->getCell("F1")->getValue(), 0, 4);
            if (in_array($year5, $year_arr)) {

                //损益表
                $sunyi5['creator'] = $userid;//创建人
                $sunyi5['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $sunyi5['applicationid'] = $applicationid;
                $sunyi5['activeid'] = $activeid;
                $sunyi5['year'] = substr($objPHPexcel->getActiveSheet()->getCell("F1")->getValue(), 0, 4);
                $sunyi5['mainIncome'] = $objPHPexcel->getActiveSheet()->getCell("F2")->getValue();
                $sunyi5['mainCost'] = $objPHPexcel->getActiveSheet()->getCell("F3")->getValue();
                $sunyi5['tax_addition'] = $objPHPexcel->getActiveSheet()->getCell("F4")->getValue();

                $sunyi5['mainPrpfit_YW'] = $objPHPexcel->getActiveSheet()->getCell("F6")->getValue();
                $sunyi5['otherPrpfit_YW'] = $objPHPexcel->getActiveSheet()->getCell("F7")->getValue();
                $sunyi5['business'] = $objPHPexcel->getActiveSheet()->getCell("F8")->getValue();
                $sunyi5['manage'] = $objPHPexcel->getActiveSheet()->getCell("F9")->getValue();
                $sunyi5['financial'] = $objPHPexcel->getActiveSheet()->getCell("F10")->getValue();
                // $sunyi5['sumCost'] = $objPHPexcel->getActiveSheet()->getCell("F11")->getValue();
                $sunyi5['interestExpense'] = $objPHPexcel->getActiveSheet()->getCell("F12")->getValue();
                $sunyi5['exchange'] = $objPHPexcel->getActiveSheet()->getCell("F13")->getValue();

                $sunyi5['profit_YY'] = $objPHPexcel->getActiveSheet()->getCell("F15")->getValue();
                $sunyi5['investIncome'] = $objPHPexcel->getActiveSheet()->getCell("F16")->getValue();
                $sunyi5['subsidyIncome'] = $objPHPexcel->getActiveSheet()->getCell("F17")->getValue();
                $sunyi5['otherBusinessIn'] = $objPHPexcel->getActiveSheet()->getCell("F18")->getValue();
                $sunyi5['otherBusinessOut'] = $objPHPexcel->getActiveSheet()->getCell("F19")->getValue();


                $sunyi5['totalProfit'] = $objPHPexcel->getActiveSheet()->getCell("F21")->getValue();
                $sunyi5['incomeTax'] = $objPHPexcel->getActiveSheet()->getCell("F22")->getValue();
                $sunyi5['shareholderSunyi'] = $objPHPexcel->getActiveSheet()->getCell("F23")->getValue();
                $sunyi5['investSunyi'] = $objPHPexcel->getActiveSheet()->getCell("F24")->getValue();
                $sunyi5['other'] = $objPHPexcel->getActiveSheet()->getCell("F25")->getValue();
                $sunyi5['profit_SH'] = $objPHPexcel->getActiveSheet()->getCell("F27")->getValue();
                $data1[] = $sunyi5;

                //资产表
                $asset5['creator'] = $userid;//创建人
                $asset5['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $asset5['applicationid'] = $applicationid;
                $asset5['activeid'] = $activeid;
                $asset5['year'] = substr($objPHPexcel->getActiveSheet()->getCell("F1")->getValue(), 0, 4);
                $asset5['currencyFunds'] = $objPHPexcel->getActiveSheet()->getCell("F29")->getValue();
                $asset5['short_termInvestment'] = $objPHPexcel->getActiveSheet()->getCell("F30")->getValue();
                $asset5['receivableBill'] = $objPHPexcel->getActiveSheet()->getCell("F31")->getValue();
                $asset5['receivableDividend'] = $objPHPexcel->getActiveSheet()->getCell("F32")->getValue();
                $asset5['receivableInterest'] = $objPHPexcel->getActiveSheet()->getCell("F33")->getValue();
                $asset5['receivableAccount'] = $objPHPexcel->getActiveSheet()->getCell("F34")->getValue();
                $asset5['otherReceivableAccount'] = $objPHPexcel->getActiveSheet()->getCell("F35")->getValue();
                $asset5['prepayAccount'] = $objPHPexcel->getActiveSheet()->getCell("F36")->getValue();
                $asset5['receivableSubsidy'] = $objPHPexcel->getActiveSheet()->getCell("F37")->getValue();
                $asset5['stock'] = $objPHPexcel->getActiveSheet()->getCell("F38")->getValue();
                $asset5['apportionCost'] = $objPHPexcel->getActiveSheet()->getCell("F39")->getValue();
                $asset5['bondInvest'] = $objPHPexcel->getActiveSheet()->getCell("F40")->getValue();
                $asset5['flowAssetsLoss'] = $objPHPexcel->getActiveSheet()->getCell("F41")->getValue();
                $asset5['otherCurrentAssets'] = $objPHPexcel->getActiveSheet()->getCell("F42")->getValue();
                $asset5['totalCurrentAssets'] = $objPHPexcel->getActiveSheet()->getCell("F44")->getValue();

                $asset5['long_termInvestment1'] = $objPHPexcel->getActiveSheet()->getCell("F45")->getValue();
                $asset5['long_termInvestment2'] = $objPHPexcel->getActiveSheet()->getCell("F46")->getValue();
                $asset5['long_termInvestment3'] = $objPHPexcel->getActiveSheet()->getCell("F47")->getValue();
                $asset5['totalLong_termInvestment'] = $objPHPexcel->getActiveSheet()->getCell("F49")->getValue();

                $asset5['fixedAssetsOriginial'] = $objPHPexcel->getActiveSheet()->getCell("F50")->getValue();
                $asset5['fixedAssetsOriginial'] = $objPHPexcel->getActiveSheet()->getCell("F51")->getValue();
                $asset5['fixedAssetsNet'] = $asset5['fixedAssetsOriginial'] - $asset5['fixedAssetsOriginial'];
                $asset5['fixedAssetsDecrease'] = $objPHPexcel->getActiveSheet()->getCell("F53")->getValue();

                $asset5['fixedAssetsNet1'] = $asset5['fixedAssetsNet'] - $asset5['fixedAssetsDecrease'];
                $asset5['projectMaterial'] = $objPHPexcel->getActiveSheet()->getCell("F55")->getValue();
                $asset5['constructionProject'] = $objPHPexcel->getActiveSheet()->getCell("F56")->getValue();
                $asset5['fixedAssetsClear'] = $objPHPexcel->getActiveSheet()->getCell("F57")->getValue();
                $asset5['fixedAssetsLoss'] = $objPHPexcel->getActiveSheet()->getCell("F58")->getValue();
                $asset5['totalFixedAssets'] = $objPHPexcel->getActiveSheet()->getCell("F60")->getValue();


                $asset5['intangibleAssets'] = $objPHPexcel->getActiveSheet()->getCell("F61")->getValue();
                $asset5['deferAssets'] = $objPHPexcel->getActiveSheet()->getCell("F62")->getValue();
                $asset5['startCost'] = $objPHPexcel->getActiveSheet()->getCell("F63")->getValue();
                $asset5['longApportionCost'] = $objPHPexcel->getActiveSheet()->getCell("F64")->getValue();
                $asset5['otherLongAssets'] = $objPHPexcel->getActiveSheet()->getCell("F65")->getValue();
                $asset5['totalDeferIntangible'] = $objPHPexcel->getActiveSheet()->getCell("F67")->getValue();

                $asset5['deferLoan'] = $objPHPexcel->getActiveSheet()->getCell("F68")->getValue();
                $asset5['totalAssets'] = $objPHPexcel->getActiveSheet()->getCell("F70")->getValue();
                $data2[] = $asset5;

                //负债表
                $debt5['creator'] = $userid;//创建人
                $debt5['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $debt5['applicationid'] = $applicationid;
                $debt5['activeid'] = $activeid;
                $debt5['year'] = substr($objPHPexcel->getActiveSheet()->getCell("F1")->getValue(), 0, 4);
                $debt5['short_termBorrowing'] = $objPHPexcel->getActiveSheet()->getCell("F72")->getValue();
                $debt5['payableBill'] = $objPHPexcel->getActiveSheet()->getCell("F73")->getValue();
                $debt5['payableAccount'] = $objPHPexcel->getActiveSheet()->getCell("F74")->getValue();
                $debt5['advanceAccount'] = $objPHPexcel->getActiveSheet()->getCell("F75")->getValue();
                $debt5['consignGoods'] = $objPHPexcel->getActiveSheet()->getCell("F76")->getValue();
                $debt5['payableWages'] = $objPHPexcel->getActiveSheet()->getCell("F77")->getValue();
                $debt5['payableWelfare'] = $objPHPexcel->getActiveSheet()->getCell("F78")->getValue();
                $debt5['payableEquity'] = $objPHPexcel->getActiveSheet()->getCell("F79")->getValue();
                $debt5['payableTax'] = $objPHPexcel->getActiveSheet()->getCell("F80")->getValue();
                $debt5['payableProfit'] = $objPHPexcel->getActiveSheet()->getCell("F81")->getValue();
                $debt5['otherPayable'] = $objPHPexcel->getActiveSheet()->getCell("F82")->getValue();
                $debt5['advanceExpense'] = $objPHPexcel->getActiveSheet()->getCell("F83")->getValue();
                $debt5['preDebt'] = $objPHPexcel->getActiveSheet()->getCell("F84")->getValue();
                $debt5['longdebt'] = $objPHPexcel->getActiveSheet()->getCell("F85")->getValue();
                $debt5['otherDebt'] = $objPHPexcel->getActiveSheet()->getCell("F86")->getValue();
                $debt5['totalDebtFlow'] = $objPHPexcel->getActiveSheet()->getCell("F88")->getValue();

                $debt5['long_termBorrowing'] = $objPHPexcel->getActiveSheet()->getCell("F89")->getValue();
                $debt5['payableDebt'] = $objPHPexcel->getActiveSheet()->getCell("F90")->getValue();
                $debt5['longPayableDebt'] = $objPHPexcel->getActiveSheet()->getCell("F91")->getValue();
                $debt5['specialPayable'] = $objPHPexcel->getActiveSheet()->getCell("F92")->getValue();
                $debt5['houseTurnover'] = $objPHPexcel->getActiveSheet()->getCell("F93")->getValue();
                $debt5['otherLongDebt'] = $objPHPexcel->getActiveSheet()->getCell("F94")->getValue();
                $debt5['totalLong_termDebt'] = $objPHPexcel->getActiveSheet()->getCell("F96")->getValue();

                $debt5['dydksx'] = $objPHPexcel->getActiveSheet()->getCell("F97")->getValue();
                $debt5['totalDebt'] = $objPHPexcel->getActiveSheet()->getCell("F99")->getValue();

                $debt5['capitalCollect'] = $objPHPexcel->getActiveSheet()->getCell("F100")->getValue();
                $debt5['capitalReserve'] = $objPHPexcel->getActiveSheet()->getCell("F101")->getValue();
                $debt5['surplusReserve'] = $objPHPexcel->getActiveSheet()->getCell("F102")->getValue();
                $debt5['welfare'] = $objPHPexcel->getActiveSheet()->getCell("F103")->getValue();
                $debt5['unconfirmInvestLoss'] = $objPHPexcel->getActiveSheet()->getCell("F104")->getValue();
                $debt5['undistributedprofit'] = $objPHPexcel->getActiveSheet()->getCell("F105")->getValue();
                $debt5['foreignDiscount'] = $objPHPexcel->getActiveSheet()->getCell("F106")->getValue();
                $debt5['totalOwnerEquity'] = $objPHPexcel->getActiveSheet()->getCell("F108")->getValue();
                $debt5['total'] = $objPHPexcel->getActiveSheet()->getCell("F109")->getValue();
                $data3[] = $debt5;
            }

            $year_arr1 = array($year1, $year2, $year3, $year4, $year5);

            $year_arr2 = array_filter($year_arr1);//去除数组中的null值

            $year_diff = array_diff($year_arr, $year_arr2);//数组的差
            foreach ($year_diff as $value) {
                //损益表
                $sunyi6['creator'] = $userid;//创建人
                $sunyi6['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $sunyi6['applicationid'] = $applicationid;
                $sunyi6['activeid'] = $activeid;
                $sunyi6['year'] = $value;
                $data1[] = $sunyi6;

                //资产表
                $asset6['creator'] = $userid;//创建人
                $asset6['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $asset6['applicationid'] = $applicationid;
                $asset6['activeid'] = $activeid;
                $asset6['year'] = $value;
                $data2[] = $asset6;

                //负债表
                $debt6['creator'] = $userid;//创建人
                $debt6['createdate'] = date('Y-m-d H:i:s', time());//创建时间
                $debt6['applicationid'] = $applicationid;
                $debt6['activeid'] = $activeid;
                $debt6['year'] = $value;
                $data3[] = $debt6;
            }

            //}
        }
        foreach ($data1 as $v) {
            Db::table('Z_TB_Financial_Sunyi')->insert($v);
        }

        foreach ($data2 as $v) {
            Db::table('Z_TB_financial_assets')->insert($v);
        }

        foreach ($data3 as $v) {
            Db::table('Z_TB_financial_Debt')->insert($v);
        }
    }
}