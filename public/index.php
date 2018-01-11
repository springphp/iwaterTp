<?php
// [ 应用入口文件 ]

//定义项目根目录
define('ROOT_PATH', dirname(__DIR__).'/');
// 定义应用目录
define('APP_PATH', ROOT_PATH . 'app/');
// 定义缓存目录
define('RUNTIME_PATH',ROOT_PATH . 'runtime/');

// 加载框架引导文件
require ROOT_PATH . '/thinkphp/start.php';
