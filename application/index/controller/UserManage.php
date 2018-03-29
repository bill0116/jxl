<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2018/3/27
 * Time: 10:18
 * Func:用户管理
 */
namespace app\index\controller;

use think\Db;

class UserManage extends Common
{
    /*
     * 首页查询
     * */
    public function index()
    {
        if ($this->request->isPost()) {
            $where = " 1=1";
            $param = $this->request->param();
            if (!empty($param['Organization']))
                $where .= " and b.organization=" . $param['Organization'];
            if (!empty($param['RoleID']))
                $where .= " and b.roleid=" . $param['RoleID'];
            if (!empty($param['Name']))
                $where .= " and (a.username like '%" . ltrim($param['Name']) . "%' or a.loginname like  '%" . ltrim($param['Name']) . "%')";
            $sqlCount = "select count(*) as count from s_tb_user a inner join s_tb_user_role b on a.userid=b.userid left join s_tb_role c on b.roleid = c.roleid and c.roleid<>0 where $where";
            $sqlData = "select distinct row_number() OVER (order by username) as rn, a.userid,a.username,a.loginname,a.email,a.isactive, b.organization,isnull(rolename,'') as rolename2,b.roleid
                      from s_tb_user a inner join s_tb_user_role b on a.userid=b.userid left join s_tb_role c on b.roleid = c.roleid and c.roleid<>0 where $where";
            $countScore = Db::query($sqlCount);
            $page = empty(input('post.page/d')) ? 1 : input('post.page/d');
            $prePage = ($page - 1) * 10 + 1;
            $nextPage = $page * 10;
            $resultList = Db::query("SELECT  T1.* FROM (SELECT thinkphp.*, ROW_NUMBER() OVER ( ORDER BY username) AS ROW_NUMBER FROM
                          (" . $sqlData . ")AS thinkphp) AS T1 WHERE (T1.ROW_NUMBER BETWEEN $prePage  AND  $nextPage)");
            $list = new \think\paginator\driver\Bootstrap($resultList, 10, $page, $countScore[0]['count'], false, ['path' => 'javascript:doSearch([PAGE]);']);
            if ($list) {
                $data = $list->toArray()['data'];
                foreach ($data as $k => $v) {
                    !empty($v['isactive']) && $data[$k]['status'] = $v['isactive'] == 1 ? "启用" : "禁用";
                }
                $page = $list->render();
                return return_array_result(1, lang('查询成功'), '', ['list' => $data, 'page' => $page]);
            } else {
                return return_array_result(0, lang('查询失败'));
            }
        } else {
            $this->assign("orgData", $this->getOrganization(session('auth_id')));
            $this->assign("roleInfo", $this->getUserRole());
            return $this->view->fetch();
        }
    }

    /*
     * 用户角色
     * */
    public function getUserRole($id = null)
    {
        $map = [];
        !empty($v['IsActive']) && $map['RoleID'] = $id;
        $userRole = Db::name('Role')->field('RoleID,RoleName')->where($map)->select();
        return $userRole;
    }

    /*
     * 经营单元
     * */
    public function getOrganization($userid)
    {
        $orgData = Db::query("S_SP_SystemManager_Organization_GetOrganizationsByUserID '$userid' ");
        return $orgData;
    }

    /*
     * 用户新增
     * */
    public function add()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (empty($param['LoginName'])) return return_array_result(0, "登录名不为空!");
            if (empty($param['UserName'])) return return_array_result(0, "用户名不为空!");
            if (empty($param['EMail'])) return return_array_result(0, "Email不为空!");
            $param['PassWord'] = 'PassWord';
            $isExist = Db::name('User')->where('LoginName', ltrim($param['LoginName']))->select();
            if (!empty($isExist)) return return_array_result(0, "用户已存在!");
            $status = Db::name('User')->insert($param);
            if ($status) {
                return return_array_result(1, "新增成功!");
            } else {
                return return_array_result(0, "新增失败!");
            }
        }
    }

    /*
     * 用户编辑
     * */
    public function edit()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $status = Db::name('User')
                ->where('UserID', $param['UserID'])
                ->update([
                    'UserName' => $param['UserName'], 'LoginName' => $param['LoginName'],
                    'EMail' => $param['EMail'], 'IsActive' => $param['IsActive'], 'PassWord' => $param['PassWord'],
                ]);
            //查看在该销售组织是否存在该用户
            $sqlCount = Db::name('UserRole')->where("userid = '" . $param['UserID'] . "' and organization = '" . $param['organization'] . "'")->count();
            if ($sqlCount == 0) {
                $inStatus = Db::name('UserRole')->insert(['UserID' => $param['UserID'], 'Organization' => $param['organization'], 'RoleID' => '0']);
                if (empty($inStatus)) {
                    return return_array_result(0, "更新失败!");
                }
            } else {
                if ($status !== false) {
                    return return_array_result(1, "更新成功!");
                } else {
                    return return_array_result(0, "更新失败!");
                }
            }
        } else {
            $param = input('param.');
            $userData = Db::name('User')
                ->distinct(true)
                ->alias('a')
                ->field('a.userid,a.username,a.loginname,loginname + \'-\'+username as describeName,a.[PassWord],a.email,a.isactive, b.organization,b.roleid')
                ->join('S_TB_User_Role b', 'a.userid=b.userid')
                ->where("a.userid = '" . $param['userid'] . "' and b.organization = '" . $param['organization'] . "'")
                ->find();
            $this->assign("userData", $userData);
            $listData = Db::name('User')
                ->alias('a')
                ->field('c.userroleid,d.rolename,f.organizationtitle')
                ->join('S_TB_User_Role b', 'a.userid=b.userid')
                ->join('S_TB_User_Role_Detail c', 'b.UserRoleID=c.UserRoleID')
                ->join('S_TB_Role d', 'b.roleid=d.roleid', 'left')
                ->join('S_TB_Organization f', 'c.organizationID=f.ID', 'left')
                ->where("a.userid = '" . $param['userid'] . "' and b.organization = '" . $param['organization'] . "'")
                ->select();
            $this->assign("listData", $listData);
            return $this->view->fetch();
        }
    }

    /*
     * 用户删除
     * */
    public function del()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $status = Db::table('S_TB_User_Role')->where("userid='" . $param['userid'] . "' and organization!='" . $param['organization'] . "'")->count();
            if ($status > 0) {
                $laStatus = Db::table('S_TB_User_Role')->where("userid='" . $param['userid'] . "' and organization!='" . $param['organization'] . "'")->delete();
            } else {
                $laStatus = Db::table('S_TB_User')->where('UserID', $param['userid'])->delete();
            }
            if (empty($laStatus)) {
                return return_array_result(0, "删除失败!");
            } else {
                return return_array_result(1, "删除成功!");
            }
        }
    }

    /*
     * 角色添加/修改
     * */
    public function role()
    {
        $param = $this->request->param();
        $this->assign("param", $param);
        $userData = Db::name('User')
            ->distinct(true)
            ->alias('a')
            ->field('a.userid,a.username,b.roleid')
            ->join('S_TB_User_Role b', 'a.userid=b.userid', 'inner')
            ->where("a.userid = '" . $param['userid'] . "' and b.organization = '" . $param['organization'] . "'")
            ->select();
        $this->assign("userData", $userData);
        //角色查询
        $roleData = Db::name('Role')->order('roleindex')->select();
        $this->assign('roleData', $roleData);
        //左列树
        $orgData = Db::name('User')
            ->distinct(true)
            ->alias('a')
            ->field('c.id,c.OrganizationTitle')
            ->join('S_TB_User_Role b', 'a.userid=b.userid', 'inner')
            ->join('S_TB_Organization c', 'b.Organization=c.id')
            ->select();
        $this->assign('vkorglist', $orgData);
        $oldData = "";
        if ($param['option'] == "edit") {
            //查询已被选中
            $oldData = Db::query(" select distinct * from s_tb_user_role a inner join s_tb_user_role_detail b on a.userroleid = b.userroleid
                    where a.userroleid='" . $param['userroleid'] . "' and a.userid = '" . $param['userid'] . "' and a.organization='" . $param['organization'] . "'");
        }
        $this->assign('old', $oldData);
        return $this->view->fetch();
    }

    /*
     * 列表中的编辑中的编辑
     * */
    public function roleedit()
    {
        $param = $this->request->request();

        //列表中的编辑
        if ($param['option'] == 'edit') {
            Db::table('S_TB_User_Role')->where("organization='" . $param['vkorg'] . "' and userid='" . $param['userid'] . "' and roleid='0'")->delete();
            if (isset($param['Role']) && is_array($param['Role'])) {
                foreach ($param['Role'] as $key => $val) {
                    $up = Db::table('S_TB_User_Role')->where('UserID', $param['userroleid'])->setField('RoleID', $val);
                }
            }
            $del = Db::table('S_TB_User_Role_Detail')->where("UserRoleId", $param['userroleid'])->delete();
            if (empty($up) && empty($del)) {
                if (isset($param['vtweg']) && is_array($param['vtweg'])) {
                    foreach ($param['vtweg'] as $key => $val) {
                        $vt = Db::execute("insert into s_tb_user_role_detail(UserRoleID,organizationid) values('" . $param['userroleid'] . "','" . $val . "')");
                    }
                }
                if (!empty($vt)) {
                    echo "<script language=javascript>alert('编辑成功');opener.document.location.href='./edit?organization=" . $param['vkorg'] . "&userid=" . $param['userid'] . "';window.close();CollectGarbage();</script>";
                }
            }
        }
    }

    /*
     * 列表中的编辑的角色删除
     * */
    public function editdel()
    {
        if ($this->request->isPost()) {
            $userroleid = input('post.userroleid');
            $status = Db::execute("delete from s_tb_user_role where userroleid='$userroleid';
                    delete from s_tb_user_role_detail where UserRoleId='$userroleid';");
            if (empty($status)) {
                return return_array_result(1, "删除成功!");
            } else {
                return return_array_result(0, "删除失败!");
            }
        }
    }


    /*
     * 增加角色
     * */
    public function addrole()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (!empty($param['vkorg'])) {
                if (!empty($param['Role'])) {
                    foreach ($param['Role'] as $key => $val) {
                        $arr['RoleID'] = $val;
                        $arr['UserID'] = $param['userid'];
                        $arr['Organization'] = $param['vkorg'];
                        $userroleid = Db::name('UserRole')->insert($arr);
                        $ree = Db::name('UserRole')->where("organization2='" . $param['vkorg'] . "' and userid='" . $param['userid'] . "' and roleid='0'");
                        foreach ($param['vtweg'] as $key => $val) {
                            $vt = Db::execute("insert into s_tb_user_role_detail(UserRoleID,organizationid) values('" . $userroleid . "','" . $val . "')");
                        }
                        //渠道/办事处
                        if (!empty($vt)) {
                            echo "<script language=javascript>alert('添加成功');opener.document.location.href='./edit.html?organization=" . $param['vkorg'] . "&userid=" . $param['userid'] . "';window.close();CollectGarbage();</script>";
                        } elseif ($userroleid) {
                            return $this->success("角色添加成功");
                        }
                    }
                } else {
                    $this->success("请选择角色");
                }
            } else {
                $this->success("请选择销售部");
            }
        } else {
            $param = $this->request->param();
            $this->assign("param", $param);
            $userData = Db::name('User')
                ->distinct(true)
                ->alias('a')
                ->field('a.userid,a.username,b.roleid')
                ->join('S_TB_User_Role b', 'a.userid=b.userid', 'inner')
                ->where("a.userid = '" . $param['userid'] . "' and b.organization = '" . $param['organization'] . "'")
                ->select();
            $this->assign("userData", $userData);
            //角色查询
            $roleData = Db::name('Role')->order('roleindex')->select();
            $this->assign('roleData', $roleData);
            //左列树
            $orgData = Db::name('User')
                ->distinct(true)
                ->alias('a')
                ->field('c.id,c.OrganizationTitle')
                ->join('S_TB_User_Role b', 'a.userid=b.userid', 'inner')
                ->join('S_TB_Organization c', 'b.Organization=c.id')
                ->select();
            $this->assign('vkorglist', $orgData);
            return $this->view->fetch();
        }
    }
}