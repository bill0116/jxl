<?php
/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2017/7/4
 * Time: 13:56
 */
namespace app\index\widget;
use think\Controller;
class PageHeader extends Controller{
    public function simple($name) {
        $this -> assign('name', $name);
        return $this->fetch('widget:pageheader/simple');
    }
    public function load($name){
        $this -> assign('name', $name);
        return $this->fetch('widget:pageheader/load');
    }
}