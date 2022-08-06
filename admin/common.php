<?php
// 定义根路径
define('NEBULA_ROOT_PATH', dirname(__DIR__) . '/');

// 载入程序配置
if (!@include NEBULA_ROOT_PATH . '/config.php') {
    file_exists(NEBULA_ROOT_PATH . '/install.php') ? header('Location: /install.php') : print('Missing Config File');
    exit;
}

// 加载公共文件
require NEBULA_ROOT_PATH . 'includes/Common.php';

// 初始化
\Nebula\Common::init();

// 缓存组件
\Nebula\Widgets\Cache::alloc()->to($cache);

// 配置组件
\Nebula\Widgets\Option::alloc()->to($option);

// 用户组件
\Nebula\Widgets\User::alloc()->to($user);

// 请求对象
$request = \Nebula\Request::getInstance();

// 响应对象
$response = \Nebula\Response::getInstance();
