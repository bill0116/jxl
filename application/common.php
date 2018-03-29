<?php
/*函数的命名使用小写字母和下划线（小写字母开头）的方式，例如 get_client_ip；*/

/*
 * @param code:关键字查询
 * @ 获取系统公共配置
 * */
function get_system_config($code)
{
    $model = db("SystemConfig");
    $count = $model->where([['code', '=', $code]])->count();
    if ($count > 1) {
        return $model->where([['code', '=', $code]])->column("val,name");
    } else {
        return $model->where([['code', '=', $code]])->value("val");
    }
}

/*
 * @param $code:状态
 * @param $message:状态信息
 * @param $url：返回Url
 * @return array:返回数据
 */
function return_array_result($code = null, $message = null, $url = null, $data = null)
{
    $result = [
        'code' => $code,
        'msg' => $message,
        'url' => $url,
        'data' => $data
    ];
    return $result;
}

/*
 * @function 数组转化成树
 * @array  $list数组
 * @int    $root根节点
 * @string $pk主键
 * $string $pid父节点
 * @string $child子节点
 * */
function list_to_tree($list, $root = 0, $pk = 'ID', $pid = 'PID', $child = '_child')
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = 0;
            if (isset($data[$pid])) {
                $parentId = $data[$pid];
            }
            if ((string)$root == $parentId) {
                $tree[] = &$list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }
    }
    return $tree;
}

/*
 * @function 树转化为左导航
 * @array $tree数组
 * @array $badge_count
 * @array $level根节点
 * */
function tree_nav($tree, $badge_count, $level = 0)
{
    $level++;
    $html = "";
    if (is_array($tree)) {
        if ($level > 1) {
            $html = "<ul class='submenu collapse'>\r\n";
        } else {
            $html = "<ul id='side-menu' class='nav nav-list'>\r\n";
        }
        foreach ($tree as $val) {
            if (isset($val["MenuTitle"])) {
                $title = $val["MenuTitle"];
                if (!empty($val["FlowUrl"])) {
                    if (strpos($val['FlowUrl'], "##") !== false) {
                        $url = "#";
                    } else if (strpos($val['FlowUrl'], 'http') !== false) {
                        $url = $val['FlowUrl'];
                    } else {
                        $url = url($val['FlowUrl']);
                    }
                } else {
                    $url = "#";
                }
                if (empty($val["ID"])) {
                    $id = $val["MenuTitle"];
                } else {
                    $id = $val["ID"];
                }
                $icon = "fa fa-angle-right";
                if (isset($val['_child'])) {
                    $html .= "<li>\r\n";
                    $html .= "<a node=\"$id\" href=\"" . "$url\">";
                    $html .= "<i class=\"$icon\"></i>";
                    $html .= "<span class=\"menu-text\">$title</span>";
                    $html .= "<span class=\"fa arrow\"></span>";
                    if (!empty($badge_count[$val['ID']])) {
                        $html .= "<span class=\"pull-right label label-primary\">" . $badge_count[$val['ID']] . "</span>";
                    }
                    $html .= "</a>\r\n";
                    $html .= tree_nav($val['_child'], $badge_count, $level);
                    $html = $html . "</li>\r\n";
                } else {
                    $html .= "<li>\r\n";
                    $html .= "<a  node=\"$id\" href=\"" . "$url\">\r\n";
                    $html .= "<i class=\"$icon\"></i>";
                    $html .= "<span class=\"menu-text\">$title</span>";
                    if (!empty($badge_count[$val['ID']])) {
                        $html .= "<span class=\"pull-right label label-primary\">" . $badge_count[$val['ID']] . "</span>";
                    }
                    $html .= "</a>\r\n</li>\r\n";
                }
            }
        }
        $html = $html . "</ul>\r\n";
    }
    return $html;
}

/*
 * function 字符串特殊字符编码
 * @string $fString 审批意见
 * */
function html_encode($fString)
{
    if ($fString != "") {
        $fString = str_replace('>', '&gt;', $fString);
        $fString = str_replace('<', '&lt;', $fString);
        $fString = str_replace(chr(32), '&nbsp;', $fString);
        $fString = str_replace(chr(13), ' ', $fString);
        $fString = str_replace(chr(10) & chr(10), '<br>', $fString);
        $fString = str_replace(chr(10), '<BR>', $fString);
    }
    return $fString;
}

/*
 * function 数组转xml
 * @array   $arr数组
 * */
function array_to_xml($arr)
{
    $xml = "";
    foreach ($arr as $key => $val) {
        if ($val) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        } else {
            $xml .= "<" . $key . "></" . $key . ">";
        }
    }
    $xml .= "";
    return $xml;
}

/*
 * function xml转数组
 * @string  $string字符串
 * */
function xml_to_array($string)
{
    $xml = "<xml>";
    $xml .= $string;
    $xml .= "</xml>";
    $xml = simplexml_load_string($xml);
    $data = json_decode(json_encode($xml), TRUE);
    return $data;
}

/*
 * function 驼峰命名转下划线命名
 * @string  $str字符串
 * */
function to_under_score($str)
{
    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
        return '_' . strtolower($matchs[0]);
    }, $str);
    return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
}

/*
 * function 审批意见字符串拼接xml
 * @string  $str字符串
 * */
function xml_approve_link($ApproveDate, $ApproverID, $ApproverName, $IsApproved, $IsApprovedText, $Memo)
{
    $xml = "<ApproveProcess><Approve>\n";
    $xml .= "<ApproveDate>" . $ApproveDate . "</ApproveDate>\n";
    $xml .= "<ApproverID>" . $ApproverID . "</ApproverID>\n";
    $xml .= "<ApproverName>" . $ApproverName . "</ApproverName>\n";
    $xml .= "<IsApproved>" . $IsApproved . "</IsApproved>\n";
    $xml .= "<IsApprovedText>" . $IsApprovedText . "</IsApprovedText>\n";
    $xml .= "<Memo>" . $Memo . "</Memo>\n";
    $xml .= "</Approve></ApproveProcess>";
    return $xml;
}
/*
 * @ html字符纯过滤
 * @ return string
 * */
function html_encode_filter($fString)
{
    if($fString!="")
    {
        $fString = str_replace( '>', '&gt;',$fString);
        $fString = str_replace( '<', '&lt;',$fString);
        $fString = str_replace( chr(32), '&nbsp;',$fString);
        $fString = str_replace( chr(13), ' ',$fString);
        $fString = str_replace( chr(10) & chr(10), '<br>',$fString);
        $fString = str_replace( chr(10), '<BR>',$fString);
    }
    return $fString;
}
/**
 * 发送邮件方法
 * @param string $to：接收者邮箱地址
 * @param string $title：邮件的标题
 * @param string $content：邮件内容
 * @return boolean  true:发送成功 false:发送失败
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null) {
    $mail = new PHPMailer();                      //实例化PHPMailer对象
    $mail->CharSet = 'UTF-8';                   //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                              // 设定使用SMTP服务
    $mail->SMTPDebug = 0;                        // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;                      // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'TSL';                   // 使用安全协议
    $mail->Host = "smtp.helmcredit.com";        // SMTP 服务器
    $mail->Port = 25;                            // SMTP服务器的端口号
    $mail->Username = "robin@HelmCredit.com";  // SMTP服务器用户名
    $mail->Password = "Helm1234";                // SMTP服务器密码
    $mail->SetFrom('robin@HelmCredit.com', 'Helm1234'); // 发件人邮箱以及名称
    $replyEmail = '';                             //留空则为发件人EMAIL
    $replyName = '';                              //回复名称（留空则为发件人名称）
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($tomail, $name);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}

