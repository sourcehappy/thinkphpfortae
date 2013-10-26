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
// $Id: ThinkPHP.php 2791 2012-02-29 10:08:57Z liu21st $

// ThinkPHP ����ļ�

// ����ϵͳ����������
require THINK_PATH.'Common/common.php';

//��¼��ʼ����ʱ��
$GLOBALS['_beginTime'] = microtime(TRUE);
// ��¼�ڴ��ʼʹ��
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();
defined('APP_PATH') or die('APP_PATH is Need Define !');
defined('APP_NAME') or die('APP_NAME is Need Define !');
defined('RUNTIME_PATH') or define('RUNTIME_PATH',APP_PATH.'Runtime/');
defined('APP_DEBUG') or define('APP_DEBUG',false); // �Ƿ����ģʽ
$runtime = defined('MODE_NAME')?'~'.strtolower(MODE_NAME).'_runtime.php':'~runtime.php';
defined('RUNTIME_FILE') or define('RUNTIME_FILE',RUNTIME_PATH.$runtime);
if(!APP_DEBUG && is_file(RUNTIME_FILE)) {
    // ����ģʽֱ���������л���
    require RUNTIME_FILE;
}else{
    // ϵͳĿ¼����
    defined('THINK_PATH') or define('THINK_PATH', dirname(__FILE__).'/');
    // ��������ʱ�ļ�
    require THINK_PATH.'Common/runtime.php';
}