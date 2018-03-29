<?php
/**
 * func:生产经营情况
 */
namespace app\index\controller;

use think\Db;

class ProductBusiness extends Common
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
        $this->table = Db::table('Z_TB_ProductBusiness_Detail');
    }

    public function index()
    {
        $option = input('get.option');
        $applicationid = input('get.applicationid');
        $activeid = input('get.activeid');
        $this->assign('option', $option);
        $this->assign('activeid', $activeid);
        $this->assign('applicationid', $applicationid);

        //经营场所
        $data = $this->table->where('applicationid', $applicationid)->find();
        $this->assign('data', $data);

        //生产设备
        $equipment_list = DB::table('Z_TB_ProduceDetail')->where("applicationid=$applicationid and [key]='equipment'")->order('id', 'desc')->select();
        $this->assign('equipment_list', $equipment_list);

        //生产规模
        $scale_list = DB::table('Z_TB_ProduceDetail')->where("applicationid=$applicationid and [key]='scale'")->order('id', 'desc')->select();
        $this->assign('scale_list', $scale_list);

        //内销明细
        $domestic_list = DB::table('Z_TB_SalesDetail')->where("applicationid=$applicationid and [key]='domestic'")->order('id', 'desc')->select();
        $total_volume = 0;//销量
        $total_sales = 0;//销售额
        foreach ($domestic_list as $v) {
            $total_volume += $v['key1'];
            $total_sales += $v['key2'];
        }
        $this->assign('total_volume1', $total_volume);
        $this->assign('total_sales1', $total_sales);
        $this->assign('domestic_list', $domestic_list);

        //外销明细
        $export_list = DB::table('Z_TB_SalesDetail')->where("applicationid=$applicationid and [key]='export'")->order('id', 'desc')->select();
        $total_volume = 0;//销量
        $total_sales = 0;//销售额
        foreach ($export_list as $v) {
            $total_volume += $v['key1'];
            $total_sales += $v['key2'];
        }
        $this->assign('total_volume2', $total_volume);
        $this->assign('total_sales2', $total_sales);
        $this->assign('export_list', $export_list);

        //内外销比较
        $compare_list = DB::table('Z_TB_SalesDetail')->where("applicationid=$applicationid and [key]='compare'")->order('id', 'desc')->select();
        $this->assign('compare_list', $compare_list);

        //销售方式比重
        $way_list = DB::table('Z_TB_SalesWaysRate')->where("applicationid", $applicationid)->order('id', 'asc')->select();
        $this->assign('way_list', $way_list);

        //竞争对手
        $Opponent_list = DB::table('Z_TB_SalesOpponent_Information')->where("applicationid", $applicationid)->order('id', 'desc')->select();
        $this->assign('Opponent_list', $Opponent_list);


        //颜色的变化
        $this->color_baogao1($applicationid,$option);

        $this->assign('type', 9);
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

        //经营场所
        $map['address'] = input('param.address');
        $map['use'] = input('param.use');
        $map['coverArea'] = input('param.coverArea');
        $map['useProperty'] = input('param.useProperty');
        $map['buildArea'] = input('param.buildArea');
        $map['ownership'] = input('param.ownership');
        $map['place_detail'] = input('param.place_detail');
        $map['sales_detail'] = input('param.sales_detail');

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

    //增加(生产设备/生产规模)
    public function add_produce()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['key'] = input('post.key');
        $info['key1'] = '';
        $info['key2'] = '';
        $info['key3'] = '';
        $data = Db::table('Z_TB_ProduceDetail')->where('applicationid', $info['applicationid'])->where('key', $info['key'])->order('id', 'desc')->select();
        if ($data) {
            foreach ($data as $v) {
                if (!$v['senddate']) {
                    return return_array_result(0, lang('新增失败'));
                    break;
                } else {
                    $tableName = Db::table('Z_TB_ProduceDetail');
                    $res = $tableName->insertGetId($info);
                    if ($res) {
                        $result = $tableName->where('id', $res)->find();
                        return return_array_result(1, lang('新增成功'), '', $result);
                    }
                }
            }
        } else {
            $tableName = Db::table('Z_TB_ProduceDetail');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'), '', $result);
            }
        }

    }

    //删除
    public function produce_del()
    {
        $id = input('post.id');
        $key = input('post.key');
        $res = Db::table('Z_TB_ProduceDetail')->where("id={$id} and [key]='{$key}'")->delete();
        if ($res !== false) {
            return return_array_result(1, lang('删除成功'));
        }
    }

    //保存
    public function produce_update()
    {
        $key = $_POST['key'];
        $info['key1'] = $_POST['xs1'];
        $info['key2'] = $_POST['xs2'];
        $info['key3'] = $_POST['xs3'];
        $info['senddate'] = date('Y-m-d H:i:s', time());//保存时间
        $id = $_POST['id'];
        $re = Db::table('Z_TB_ProduceDetail')->where("id={$id}")->where("[key]='{$key}'")->update($info);
        if ($re) {
            return return_array_result(1, lang('保存成功'));
        }
    }

    //增加销量
    public function add_sales()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['key'] = input('request.key');
        $info['product'] = '';
        $info['key1'] = '';
        $info['key2'] = '';
        $data = Db::table('Z_TB_SalesDetail')->where('applicationid', $info['applicationid'])->where('key', $info['key'])->order('id', 'desc')->select();
        if ($data) {
            foreach ($data as $v) {
                if (!$v['senddate']) {
                    return return_array_result(0, lang('新增失败'));
                    break;
                } else {
                    $tableName = Db::table('Z_TB_SalesDetail');
                    $res = $tableName->insertGetId($info);
                    if ($res) {
                        $result = $tableName->where('id', $res)->find();
                        return return_array_result(1, lang('新增成功'), '', $result);
                    }
                }
            }
        } else {
            $tableName = Db::table('Z_TB_SalesDetail');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'), '', $result);
            }
        }


    }

    //保存
    public function sales_update()
    {
        $key = $_POST['key'];
        $info['product'] = $_POST['xs1'];
        $info['key1'] = $_POST['xs2'];
        $info['key2'] = $_POST['xs3'];
        $info['senddate'] = date('Y-m-d H:i:s', time());//保存时间
        $id = $_POST['id'];
        $applicationid = $_POST['applicationid'];
        $re = Db::table('Z_TB_SalesDetail')->where("id={$id}")->update($info);
        if ($re) {
            $total_volume = 0;//销量
            $total_sales = 0;//销售额
            $result = Db::table('Z_TB_SalesDetail')->where('applicationid', $applicationid)->where('key', $key)->select();
            foreach ($result as $v) {
                $total_volume += $v['key1'];
                $total_sales += $v['key2'];
            }
            return return_array_result(1, lang('保存成功'), '', ['total_volume' => $total_volume, 'total_sales' => $total_sales]);
        }
    }

    //删除
    public function sales_del()
    {
        $id = input('post.id');
        $key = input('post.key');
        $applicationid = input('post.applicationid');
        $res = Db::table('Z_TB_SalesDetail')->where('id=' . $id)->delete();
        if ($res !== false) {
            $total_volume = 0;//销量
            $total_sales = 0;//销售额
            $result = Db::table('Z_TB_SalesDetail')->where('applicationid', $applicationid)->where('key', $key)->select();
            foreach ($result as $v) {
                $total_volume += $v['key1'];
                $total_sales += $v['key2'];
            }
            return return_array_result(1, lang('删除成功'), '', ['total_volume' => $total_volume, 'total_sales' => $total_sales]);
        }

    }

    //增加产品的销售比重
    public function add_way()
    {
        $info['creator'] = session('auth_id');//创建人
        $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
        $info['applicationid'] = input('post.applicationid');
        $info['product'] = '';
        $info['rate1'] = '';
        $info['rate2'] = '';
        $info['rate3'] = '';
        $info['rate4'] = '';
        $info['rate5'] = '';
        $info['rate6'] = '';
        $data = Db::table('Z_TB_SalesWaysRate')->where('applicationid', $info['applicationid'])->order('id', 'desc')->select();
        if ($data) {
            foreach ($data as $v) {
                if (!$v['senddate']) {
                    return return_array_result(0, lang('新增失败'));
                    break;
                } else {
                    $tableName = Db::table('Z_TB_SalesWaysRate');
                    $res = $tableName->insertGetId($info);
                    if ($res) {
                        $result = $tableName->where('id', $res)->find();
                        return return_array_result(1, lang('新增成功'), '', $result);
                    }
                }
            }
        } else {
            $tableName = Db::table('Z_TB_SalesWaysRate');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'), '', $result);
            }
        }


    }

    //保存
    public function way_update()
    {
        $info['product'] = $_POST['xs1'];
        $info['rate1'] = $_POST['xs2'];
        $info['rate2'] = $_POST['xs3'];
        $info['rate3'] = $_POST['xs4'];
        $info['rate4'] = $_POST['xs5'];
        $info['rate5'] = $_POST['xs6'];
        $info['rate6'] = $_POST['xs7'];
        $info['senddate'] = date('Y-m-d H:i:s', time());//保存时间
        $id = $_POST['id'];
        $applicationid = $_POST['applicationid'];
        $re = Db::table('Z_TB_SalesWaysRate')->where("id={$id}")->update($info);
        if ($re) {
            return return_array_result(1, lang('保存成功'));
        }
    }

    //删除
    public function way_del()
    {
        $id = input('post.id');
        $applicationid = input('post.applicationid');
        $res = Db::table('Z_TB_SalesWaysRate')->where('id=' . $id)->delete();
        if ($res !== false) {
            return return_array_result(1, lang('删除成功'));
        }
    }


    //保存竞争对手
    public function saveOpponent()
    {
        $id = input('param.OpponentID');
        if (!$id) {
            $info['creator'] = session('auth_id');//创建人
            $info['createdate'] = date('Y-m-d H:i:s', time());//创建时间
            $info['applicationid'] = input('post.applicationid');
            $info['name'] = input('param.companyName');
            $info['address'] = input('param.address');
            $info['companyType'] = input('param.companyType');
            $info['businessScope'] = input('param.businessScope');
            $info['salesScope'] = input('param.salesScope');
            $info['brand'] = input('param.brand');
            $info['staff'] = input('param.staff');
            $info['plan'] = input('param.plan');
            $info['introduce'] = input('param.introduce');
            $tableName = Db::table('Z_TB_SalesOpponent_Information');
            $res = $tableName->insertGetId($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('新增成功'));
            }
        } else {
            $info['name'] = input('param.companyName');
            $info['address'] = input('param.address');
            $info['companyType'] = input('param.companyType');
            $info['businessScope'] = input('param.businessScope');
            $info['salesScope'] = input('param.salesScope');
            $info['brand'] = input('param.brand');
            $info['staff'] = input('param.staff');
            $info['plan'] = input('param.plan');
            $info['introduce'] = input('param.introduce');
            $tableName = Db::table('Z_TB_SalesOpponent_Information');
            $res = $tableName->where('id', $id)->update($info);
            if ($res) {
                $result = $tableName->where('id', $res)->find();
                return return_array_result(1, lang('修改成功'));
            }
        }

    }


    //删除竞争对手
    public function Opponent_del()
    {
        $id = $_POST['id'];
        $applicationid = input('post.applicationid');
        $res = Db::table('Z_TB_SalesOpponent_Information')->where('id=' . $id)->delete();
        if ($res !== false) {
            return return_array_result(1, lang('删除成功'));
        }
    }


	//保存(翻译)
	public function saveEng(){
		$applicationid=input('request.id');

		//经营场所
		$map['addressEng']=input('param.addressEng');
		$map['useEng']=input('param.useEng');
		$map['coverAreaEng']=input('param.coverAreaEng');
		$map['usePropertyEng']=input('param.usePropertyEng');
		$map['buildAreaEng']=input('param.buildAreaEng');
		$map['ownershipEng']=input('param.ownershipEng');
		$map['place_detailEng']=input('param.place_detailEng');
		$map['sales_detailEng']=input('param.sales_detailEng');

		$ret=$this->table->where('applicationid',$applicationid)->update($map);
		if ($ret) {
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