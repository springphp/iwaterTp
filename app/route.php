<?php

use think\Route;

//使用get访问模式，正式环境应修改为post请求
Route::get('api/:version/gateway','api/Index/index',['ext'=>'htm']);