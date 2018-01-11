<?php
namespace app\common\validate;
use think\Validate;

class Config extends Validate{
    protected $rule = [
        ['config_mark', 'require|unique:Config|regex:^[a-zA-z]+\w+','标识不能为空|标识已存在|标识只能以字母开头'],
        ['config_name', 'require', '配置名不能为空'],
        ['config_des',  'require', '配置描述不能为空'],

    ];
    protected $scene = [
        'add'   => ['config_mark','config_name','config_des'],
        'edit'	=>	['config_name','config_des'],
    ];    
}