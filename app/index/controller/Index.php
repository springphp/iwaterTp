<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return '后台框架<a href="'.url('admin/index/index').'">点击跳转</a>';
    }
}
