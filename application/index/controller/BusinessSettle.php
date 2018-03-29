<?php
/**
 * func:业务结算情况
 */
namespace app\index\controller;

use think\Db;

class BusinessSettle extends Common
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
        $this->table = Db::table('Z_TB_PurchaseProduct_Detail');
    }

    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);

        //采购结算
        $data = $this->table->where('applicationid', $applicationid)->find();
        $this->assign('data', $data);

        //采购
        $purchase_list = DB::table('Z_TB_PurchaseProduct')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('purchase_list', $purchase_list);

        //主要供应商
        $supplier_list = DB::table('Z_TB_SupplierCustomer_Information')->where("applicationid", $applicationid)->where("key", 'supplier')->order('id', 'desc')->select();
        $this->assign('supplier_list', $supplier_list);

        //销售
        $sales_list = DB::table('Z_TB_SalesProduct')->where('applicationid=' . $applicationid)->order('id', 'desc')->select();
        $this->assign('sales_list', $sales_list);

        //主要客户
        $customer_list = DB::table('Z_TB_SupplierCustomer_Information')->where("applicationid", $applicationid)->where("key", 'customer')->order('id', 'desc')->select();
        $this->assign('customer_list', $customer_list);

		//颜色的变化
		$this->color_baogao1($applicationid,$option);


        $this->assign('type', 20);
        $language = '英文';
        $this->assign('language', $language);
        return $this->fetch();
    }

    //保存
    public function save()
    {
        $userid = session('auth_id');
        $applicationid = input('request.id');
        $activeid = input('request.activeid');

        $map['creator'] = $userid;//创建人
        $map['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $map['applicationid'] = input('request.id');//委托单id
        $map['activeid'] = input('request.activeid');//activeid

        //采购
        $map['purchase'] = input('request.purchase');

        $this->table->where('applicationid', $applicationid)->delete();

        $id = $this->table->insertGetId($map);
        if ($id) {
            return return_array_result(1, lang('保存成功'));
        } else {
            return return_array_result(0, lang('保存失败'));
        }
    }


	//确认
	public function recheck(){
		$applicationid=input('request.applicationid');;
		return  $this->check($this->table,$applicationid);
	}

	//备注
	public function save_memo(){
		$applicationid=input('request.applicationid');
		$memo=input('request.confirmMemo');
		return  $this->memo($this->table,$applicationid,$memo);
	}


    //增加采购
    public function add_purchase()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['name'] = '';
        $info['payType'] = '';
        $info['term'] = '';
        $info['supplierNum'] = '';
        $data = Db::table('Z_TB_PurchaseProduct')->where('applicationid', $info['applicationid'])->order('id', 'desc')->select();
        if ($data) {
            foreach ($data as $v) {
                if (!$v['senddate']) {
                    return return_array_result(0, lang('新增失败'));
                    break;
                } else {
                    $tableName = Db::table('Z_TB_PurchaseProduct');
                    $res = $tableName->insertGetId($info);
                    if ($res) {
                        $result = $tableName->where('id', $res)->find();
                        return return_array_result(1, lang('新增成功'), '', $result);
                    }
                }
            }
        } else {
            $tableName = Db::table('Z_TB_PurchaseProduct');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'), '', $result);
            }
        }


    }

    //删除
    public function purchase_del()
    {
        $id = input('post.id');
        $res = Db::table('Z_TB_PurchaseProduct')->where('id=' . $id)->delete();
        if ($res !== false) {
            return return_array_result(1, lang('删除成功'));
        }

    }

    //保存
    public function purchase_update()
    {
        $info['name'] = $_POST['xs1'];
        $info['payType'] = $_POST['xs2'];
        $info['term'] = $_POST['xs3'];
        $info['supplierNum'] = $_POST['xs4'];
        $info['senddate'] = date('Y-m-d H:i:s', time());//保存时间
        $id = $_POST['id'];
        $re = Db::table('Z_TB_PurchaseProduct')->where("id={$id}")->update($info);
        if ($re) {
            return return_array_result(1, lang('保存成功'));
        }
    }


    //保存供应商/客户
    public function saveSupplier()
    {
        $id = input('param.SupplierID');
        if (!$id) {
            $info['creator'] = session('auth_id');//创建人
            $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
            $info['applicationid'] = input('post.applicationid');
            $info['key'] = input('param.key');
            $info['name'] = input('param.supplierName');
            $info['address'] = input('param.supplierAddress');
            $info['phone'] = input('param.linkPhone');
            $info['linkman'] = input('param.linkman');
            $info['interviewDate'] = input('param.interviewDate');
            $info['start'] = input('param.start');
            $info['mainProduct'] = input('param.mainProduct');
            $info['limit'] = input('param.limit');
            $info['payType'] = input('param.payType');
            $info['term'] = input('param.term');
            $info['evaluate'] = input('param.evaluate');
            $tableName = Db::table('Z_TB_SupplierCustomer_Information');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'));
            }
        } else {
            $info['name'] = input('param.supplierName');
            $info['address'] = input('param.supplierAddress');
            $info['phone'] = input('param.linkPhone');
            $info['linkman'] = input('param.linkman');
            $info['interviewDate'] = input('param.interviewDate');
            $info['start'] = input('param.start');
            $info['mainProduct'] = input('param.mainProduct');
            $info['limit'] = input('param.limit');
            $info['payType'] = input('param.payType');
            $info['term'] = input('param.term');
            $info['evaluate'] = input('param.evaluate');
            $tableName = Db::table('Z_TB_SupplierCustomer_Information');
            $res = $tableName->where('id', $id)->update($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('修改成功'));
            }
        }

    }

    //删除供应商/客户
    public function Supplier_del()
    {
        $id = $_POST['id'];
        $applicationid = input('post.applicationid');
        $res = Db::table('Z_TB_SupplierCustomer_Information')->where('id=' . $id)->delete();
        if ($res !== false) {
            return return_array_result(1, lang('删除成功'));
        }
    }

    //增加销售
    public function add_sales()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['name'] = '';
        $info['scope'] = '';
        $info['customer'] = '';
        $info['condition'] = '';
        $data = Db::table('Z_TB_SalesProduct')->where('applicationid', $info['applicationid'])->order('id', 'desc')->select();
        if ($data) {
            foreach ($data as $v) {
                if (!$v['senddate']) {
                    return return_array_result(0, lang('新增失败'));
                    break;
                } else {
                    $tableName = Db::table('Z_TB_SalesProduct');
                    $res = $tableName->insertGetId($info);
                    if ($res) {
                        $result = $tableName->where('id', $res)->find();
                        return return_array_result(1, lang('新增成功'), '', $result);
                    }
                }
            }
        } else {
            $tableName = Db::table('Z_TB_SalesProduct');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'), '', $result);
            }
        }

    }

    //删除
    public function sales_del()
    {
        $id = input('post.id');
        $res = Db::table('Z_TB_SalesProduct')->where('id=' . $id)->delete();
        if ($res !== false) {
            return return_array_result(1, lang('删除成功'));
        }

    }

    //保存
    public function sales_update()
    {
        $info['name'] = $_POST['xs1'];
        $info['scope'] = $_POST['xs2'];
        $info['customer'] = $_POST['xs3'];
        $info['condition'] = $_POST['xs4'];
        $info['senddate'] = date('Y-m-d H:i:s', time());//保存时间
        $id = $_POST['id'];
        $re = Db::table('Z_TB_SalesProduct')->where("id={$id}")->update($info);
        if ($re) {
            return return_array_result(1, lang('保存成功'));
        }
    }


	//保存(翻译)
	public function saveEng(){
		$applicationid=input('request.id');

		//采购
		$map['purchaseEng']=input('request.purchaseEng');

		$ret=$this->table->where('applicationid',$applicationid)->update($map);

		if ($ret!==false) {
			return return_array_result(1, lang('保存成功'));
		} else {
			return return_array_result(0, lang('保存失败'));
		}
	}

	//确认(翻译)
	public function recheckEng(){
		$applicationid=input('request.applicationid');;
		return  $this->checkEng($this->table,$applicationid);
	}

	//备注(翻译)
	public function save_memoEng(){
		$applicationid=input('request.applicationid');
		$memo=input('request.confirmMemoEng');
		return  $this->memoEng($this->table,$applicationid,$memo);
	}

}