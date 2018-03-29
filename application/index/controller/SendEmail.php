<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2018/3/23
 * Time: 14:42
 */
namespace app\index\controller;

class SendEmail extends Common
{
    public function index()
    {
        if ($this->request->isPost()) {
            $toemail = $this->request->param("email");//接受者邮箱
            $fileID = $this->request->param("id");   //接受者附件id
            $fileAttach = !empty($fileID) ? array_column($this->getAttachFile($fileID), 'AttachUrl') : '';
            $subject = '这里写邮件标题';
            $content = "这里写邮件主题内容";//这里可以写a标签之类的HTML代码,具体哪些能写要自己试
            $status = send_mail($toemail, session('user_name'), $subject, $content, $fileAttach);
            return $status ? return_array_result(1,"发送成功") : return_array_result(0,"发送失败");
        }else{
            return return_array_result(0,"请求方式不正确");
        }
    }
}