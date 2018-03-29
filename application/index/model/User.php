<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Function:用户模型
 */
namespace app\index\model;

use think\Model;

class User extends Common
{
    protected $table = 'S_TB_User';

    protected $pk = 'UserID';

    function checkLogin($param)
    {
        $param['language'] = empty($param['language']) ? "zh" : $param['language'];
        if ($param['language'] == "zh") {
            cookie('think_var', 'zh-cn');
        } else if ($param['language'] == "en") {
            cookie('think_var', 'en-us');
        }
        if ($param['LoginName'] == "") {
            return return_array_result(0, lang("登陆名不为空!"));
        }
        if ($param['PassWord'] == "") {
            return return_array_result(0, lang("密码不为空!"));
        }
        $map = array();
        $model = model("User");
        // 支持使用绑定帐号登录
        $map['LoginName'] = $param['LoginName'];
        if ($param['PassWord'] != ",..,") {
            $map['PassWord'] = array('eq', $param['PassWord']);
//            $ldapHost = "172.16.41.229";//LDAP 服务器地址
//            $ldapPort = "389";//LDAP 服务器端口号
//            $ldapUser = $param['LoginName'];//设定登录DN
//            $ldapPwd = $param['PassWord'];//设定密码
//            $ldapConn = ldap_connect($ldapHost, $ldapPort);//建立与 LDAP 服务器的连接
//            if (!$ldapConn) {
//                return return_array_result(0, lang("不能绑定到LDAP服务器!"));
//            }
//            ldap_bind($ldapConn, $ldapUser, $ldapPwd);//与服务器绑定
//            if (ldap_errno($ldapConn) != 0) {
//                $userData = $model->where($map)->find();
//            } else {
                $where['LoginName'] = $param['LoginName'];
                $userData = $model->where($where)->find();
//            }
        } else {
            $where['LoginName'] = $param['LoginName'];
            $userData = $model->where($where)->find();
        }
        if ($userData) {
            session(config('user_login_key'), $userData['UserID']);
            session('user_name', $userData['UserName']);
            session('user', $userData);
            return return_array_result(1, lang("登陆成功!"), url('index/index'));
        } else {
            return return_array_result(0, lang("登陆失败!"));
        }
    }

    public function getUserName()
    {

    }
}