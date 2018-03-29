<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/11/24
 * Func: 资信报告确认下单（企业版）
 */
namespace app\index\controller;

use think\Db;
use think\facade\Request;

class OrderConfirm extends Common
{
    /*
     * 流程申请
     * */
    public function add(){
        $org = $this->listOrganizations(session('auth_id'));
        $this->assign('org', $org);
        return $this->fetch();
    }
    /*
     * 主表数据
     * */
    public function index(){
        if($this->request->isPost()){
            $map = input('post.');
            $param['reportbuyerNo'] = $map['sinosureBuyerNo'];
            $id = Db::table('Z_TB_Sinosure_EntrustInput')->insertGetId($param);
            if($id){
                $resultData = Db::table('Z_TB_Sinosure_EntrustInput')->where('id=' . $id)->find();
                $this->assign('data', $resultData);
            }else{
                $this->error("客户申请资信报告确认下单失败");
            }
        }else{

        }
        return $this->fetch();
    }
    /*
     *  保存数据
     * */
    public function update(){
        if($this->request->isPost()){
            $param=request()->except(['ID']);
            $resultStatus = Db::table('Z_TB_Sinosure_EntrustInput')->where('ID' , input('post.ID'))->update($param);
            if($resultStatus!==false){
                $this->success("保存成功!",url('OrderConfirm/add'),1);
            }else{
                $this->error("保存失败!");
            }
        }
    }
}