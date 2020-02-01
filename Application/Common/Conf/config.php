<?php

return array(
	//'配置项'=>'配置值'
	'URL_MODEL'             =>  2,
	'TMPL_FILE_DEPR'        =>  '_', //模板文件CONTROLLER_NAME与ACTION_NAME之间的分割符
	'DEFAULT_MODULE'        =>  'Admin',
	'DEFAULT_CONTROLLER'    =>  'User', // 默认控制器名称
	'DEFAULT_ACTION'        =>  'index', // 默认操作名称
	'TMPL_ACTION_ERROR'     =>  'dispatch_jump', // 默认错误跳转对应的模板文件
	'TMPL_ACTION_SUCCESS'   =>  'dispatch_jump', // 默认成功跳转对应的模板文件
	'TMPL_EXCEPTION_FILE' => APP_PATH.'/Public/exception.tpl',
	//'ERROR_PAGE' => 'http://cnmmm.com',
	/* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '127.0.0.1', // 服务器地址
    'DB_NAME'               =>  'cnmmmcom',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  '2e1cf461e540cfbb',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'wx_',    // 数据库表前缀
);