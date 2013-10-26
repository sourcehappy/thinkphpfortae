<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * ThinkPHP 运行时文件 编译后不再加载
 +------------------------------------------------------------------------------
 */
if (!defined('THINK_PATH')) exit();


//  版本信息
define('THINK_VERSION', '3.0');
define('THINK_RELEASE', '20120313');

define('IS_CLI',(PHP_SAPI=='cli' && !isset($context) )? 1   :   0);
define('IS_CGI',IS_CLI ? 0 : 1 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_ONLINE',stristr($_taeServer,'taobao.com') ?1 :0 );

 
// 路径设置 可在入口文件中重新定义 所有路径常量都必须以/ 结尾
defined('CORE_PATH') or define('CORE_PATH',THINK_PATH.'Lib/'); // 系统核心类库目录
defined('EXTEND_PATH') or define('EXTEND_PATH',THINK_PATH.'Extend/'); // 系统扩展目录
defined('MODE_PATH') or define('MODE_PATH',EXTEND_PATH.'Mode/'); // 模式扩展目录
defined('ENGINE_PATH') or define('ENGINE_PATH',EXTEND_PATH.'Engine/'); // 引擎扩展目录
defined('VENDOR_PATH') or define('VENDOR_PATH',EXTEND_PATH.'Vendor/'); // 第三方类库目录
defined('LIBRARY_PATH') or define('LIBRARY_PATH',EXTEND_PATH.'Library/'); // 扩展类库目录
defined('COMMON_PATH') or define('COMMON_PATH',    APP_PATH.'Common/'); // 项目公共目录
defined('LIB_PATH') or define('LIB_PATH',    APP_PATH.'Lib/'); // 项目类库目录
defined('CONF_PATH') or define('CONF_PATH',  APP_PATH.'Conf/'); // 项目配置目录
defined('LANG_PATH') or define('LANG_PATH', APP_PATH.'Lang/'); // 项目语言包目录
defined('TMPL_PATH') or define('TMPL_PATH',APP_PATH.'Tpl/'); // 项目模板目录
defined('HTML_PATH') or define('HTML_PATH',APP_PATH.'Html/'); // 项目静态目录
defined('LOG_PATH') or define('LOG_PATH',  RUNTIME_PATH.'Logs/'); // 项目日志目录
defined('TEMP_PATH') or define('TEMP_PATH', RUNTIME_PATH.'Temp/'); // 项目缓存目录
defined('DATA_PATH') or define('DATA_PATH', RUNTIME_PATH.'Data/'); // 项目数据目录
defined('CACHE_PATH') or define('CACHE_PATH',   RUNTIME_PATH.'Cache/'); // 项目模板缓存目录


if (IS_CLI){
	if(!is_dir(LIB_PATH)) {
        // 创建项目目录结构
        build_app_dir();
    }
    exit('项目目录已经生成功，开始编码吧~');
}


// 加载运行时所需要的文件 并负责自动目录生成
function load_runtime_file() {
    // 读取核心编译文件列表
    $list = array(
        CORE_PATH.'Core/Think.class.php',
        CORE_PATH.'Core/Behavior.class.php',
    );
    // 加载模式文件列表
    foreach ($list as $key=>$file){
        if(is_file($file))  require_cache($file);
    }
    // 加载系统类库别名定义
    alias_import(include THINK_PATH.'Conf/alias.php');
}

// 创建项目目录结构
function build_app_dir() {
    // 没有创建项目目录的话自动创建
    if(!is_dir(APP_PATH)) mk_dir(APP_PATH,0777);
    if(is_writeable(APP_PATH)) {
        $dirs  = array(
            LIB_PATH,
            RUNTIME_PATH,
            CONF_PATH,
            COMMON_PATH,
            LANG_PATH,
            CACHE_PATH,
            TMPL_PATH,
            TMPL_PATH.C('DEFAULT_THEME').'/',
            LOG_PATH,
            TEMP_PATH,
            DATA_PATH,
            LIB_PATH.'Model/',
            LIB_PATH.'Action/',
            LIB_PATH.'Behavior/',
            LIB_PATH.'Widget/',
            );
        foreach ($dirs as $dir){
            if(!is_dir($dir))  mk_dir($dir,0777);
        }
        // 目录安全写入
        if(!defined('BUILD_DIR_SECURE')) define('BUILD_DIR_SECURE',false);
        if(BUILD_DIR_SECURE) {
            if(!defined('DIR_SECURE_FILENAME')) define('DIR_SECURE_FILENAME','index.html');
            if(!defined('DIR_SECURE_CONTENT')) define('DIR_SECURE_CONTENT',' ');
            // 自动写入目录安全文件
            $content = DIR_SECURE_CONTENT;
            $a = explode(',', DIR_SECURE_FILENAME);
            foreach ($a as $filename){
                foreach ($dirs as $dir)
                    file_put_contents($dir.$filename,$content);
            }
        }
        // 写入配置文件
        if(!is_file(CONF_PATH.'config.php'))
            file_put_contents(CONF_PATH.'config.php',"<?php\nreturn array(\n\t//'配置项'=>'配置值'\n);\n?>");
        // 写入测试Action
        if(!is_file(LIB_PATH.'Action/IndexAction.class.php'))
            build_first_action();
    }else{
        exit('项目目录不可写，目录无法自动生成！请使用项目生成器或者手动生成项目目录~');
    }
}

// 创建测试Action
function build_first_action() {
    $content = file_get_contents(THINK_PATH.'Tpl/default_index.tpl');
    file_put_contents(LIB_PATH.'Action/IndexAction.class.php',$content);
}

// 加载运行时所需文件
load_runtime_file();
// 记录加载文件时间
G('loadTime');
// 执行入口
Think::Start();