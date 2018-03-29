<?php
namespace app\index\controller;
use think\Controller;
class Login extends Controller{
    public function initialize(){
        $this->userModel = model('User', 'model');
    }
    /*
     * @ Admin系统管理员
     * @ KAO登陆名LoginName
     * @ 系统用户登录验证
     * */
    public function login(){
        if (request()->isPost()) {
            $res = $this->userModel->checkLogin(input('post.'));
            if ($res['code']) {
                $this->redirect('Index/index');
//                $this->success(lang($res['msg']), url('Index/index'), '', 1);
            } else {
                $this->error(lang($res['msg']));
            }
        }else{
            $auth_id = session(config('user_login_key'));
            if (!isset($auth_id)) {
                return $this->fetch();
            } else {
                header('Location:' );
            }
        }
    }
    public function logout(){
        session(config('user_auto_key'),null);
        $this->success(lang('您已经退出登录！'));
    }
}