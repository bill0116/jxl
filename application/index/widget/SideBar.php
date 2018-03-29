<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/6/23
 * Time: 10:19
 */
namespace app\index\widget;
use think\Controller;
class SideBar extends Controller{
    public function left($tree_menu, $badge_count) {
        echo $this -> tree_nav($tree_menu, $badge_count);
    }

    public function render($data) {
        $tree = $data['tree'];
        $badge_count = $data['new_count'];
        return $this -> tree_nav($tree, $badge_count);
    }

    function tree_nav($tree, $badge_count, $level = 0) {
        $level++;
        $html = "";
        if (is_array($tree)) {
            if ($level > 1) {
                $html = "<ul class='submenu collapse'>\r\n";
            } else {
                $html = "<ul id='side-menu' class='nav nav-list'>\r\n";
            }
            foreach ($tree as $val) {
                if (isset($val["Title"])) {
                    $title = $val["Title"];
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
                        $id = $val["Title"];
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
                        $html .= $this -> tree_nav($val['_child'], $badge_count, $level);
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
}