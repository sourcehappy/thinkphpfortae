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
 * ThinkPHP ����ʱ�ļ� ������ټ���
 +------------------------------------------------------------------------------
 */
if (!defined('THINK_PATH')) exit();


//  �汾��Ϣ
define('THINK_VERSION', '3.0');
define('THINK_RELEASE', '20120313');

define('IS_CLI',(PHP_SAPI=='cli' && !isset($context) )? 1   :   0);
define('IS_CGI',IS_CLI ? 0 : 1 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_ONLINE',stristr($_taeServer,'taobao.com') ?1 :0 );

 
// ·������ ��������ļ������¶��� ����·��������������/ ��β
defined('CORE_PATH') or define('CORE_PATH',THINK_PATH.'Lib/'); // ϵͳ�������Ŀ¼
defined('EXTEND_PATH') or define('EXTEND_PATH',THINK_PATH.'Extend/'); // ϵͳ��չĿ¼
defined('MODE_PATH') or define('MODE_PATH',EXTEND_PATH.'Mode/'); // ģʽ��չĿ¼
defined('ENGINE_PATH') or define('ENGINE_PATH',EXTEND_PATH.'Engine/'); // ������չĿ¼
defined('VENDOR_PATH') or define('VENDOR_PATH',EXTEND_PATH.'Vendor/'); // ���������Ŀ¼
defined('LIBRARY_PATH') or define('LIBRARY_PATH',EXTEND_PATH.'Library/'); // ��չ���Ŀ¼
defined('COMMON_PATH') or define('COMMON_PATH',    APP_PATH.'Common/'); // ��Ŀ����Ŀ¼
defined('LIB_PATH') or define('LIB_PATH',    APP_PATH.'Lib/'); // ��Ŀ���Ŀ¼
defined('CONF_PATH') or define('CONF_PATH',  APP_PATH.'Conf/'); // ��Ŀ����Ŀ¼
defined('LANG_PATH') or define('LANG_PATH', APP_PATH.'Lang/'); // ��Ŀ���԰�Ŀ¼
defined('TMPL_PATH') or define('TMPL_PATH',APP_PATH.'Tpl/'); // ��Ŀģ��Ŀ¼
defined('HTML_PATH') or define('HTML_PATH',APP_PATH.'Html/'); // ��Ŀ��̬Ŀ¼
defined('LOG_PATH') or define('LOG_PATH',  RUNTIME_PATH.'Logs/'); // ��Ŀ��־Ŀ¼
defined('TEMP_PATH') or define('TEMP_PATH', RUNTIME_PATH.'Temp/'); // ��Ŀ����Ŀ¼
defined('DATA_PATH') or define('DATA_PATH', RUNTIME_PATH.'Data/'); // ��Ŀ����Ŀ¼
defined('CACHE_PATH') or define('CACHE_PATH',   RUNTIME_PATH.'Cache/'); // ��Ŀģ�建��Ŀ¼


if (IS_CLI){
	if(!is_dir(LIB_PATH)) {
        // ������ĿĿ¼�ṹ
        build_app_dir();
    }
    exit('��ĿĿ¼�Ѿ����ɹ�����ʼ�����~');
}


// ��������ʱ����Ҫ���ļ� �������Զ�Ŀ¼����
function load_runtime_file() {
    // ��ȡ���ı����ļ��б�
    $list = array(
        CORE_PATH.'Core/Think.class.php',
        CORE_PATH.'Core/Behavior.class.php',
    );
    // ����ģʽ�ļ��б�
    foreach ($list as $key=>$file){
        if(is_file($file))  require_cache($file);
    }
    // ����ϵͳ����������
    alias_import(include THINK_PATH.'Conf/alias.php');
}

// ������ĿĿ¼�ṹ
function build_app_dir() {
    // û�д�����ĿĿ¼�Ļ��Զ�����
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
        // Ŀ¼��ȫд��
        if(!defined('BUILD_DIR_SECURE')) define('BUILD_DIR_SECURE',false);
        if(BUILD_DIR_SECURE) {
            if(!defined('DIR_SECURE_FILENAME')) define('DIR_SECURE_FILENAME','index.html');
            if(!defined('DIR_SECURE_CONTENT')) define('DIR_SECURE_CONTENT',' ');
            // �Զ�д��Ŀ¼��ȫ�ļ�
            $content = DIR_SECURE_CONTENT;
            $a = explode(',', DIR_SECURE_FILENAME);
            foreach ($a as $filename){
                foreach ($dirs as $dir)
                    file_put_contents($dir.$filename,$content);
            }
        }
        // д�������ļ�
        if(!is_file(CONF_PATH.'config.php'))
            file_put_contents(CONF_PATH.'config.php',"<?php\nreturn array(\n\t//'������'=>'����ֵ'\n);\n?>");
        // д�����Action
        if(!is_file(LIB_PATH.'Action/IndexAction.class.php'))
            build_first_action();
    }else{
        exit('��ĿĿ¼����д��Ŀ¼�޷��Զ����ɣ���ʹ����Ŀ�����������ֶ�������ĿĿ¼~');
    }
}

// ��������Action
function build_first_action() {
    $content = file_get_contents(THINK_PATH.'Tpl/default_index.tpl');
    file_put_contents(LIB_PATH.'Action/IndexAction.class.php',$content);
}

// ��������ʱ�����ļ�
load_runtime_file();
// ��¼�����ļ�ʱ��
G('loadTime');
// ִ�����
Think::Start();