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
// $Id: convention.php 2756 2012-02-19 10:38:32Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP���������ļ�
 * ���ļ��벻Ҫ�޸ģ����Ҫ���ǹ������õ�ֵ��������Ŀ�����ļ����趨�͹���������������
 * �������ƴ�Сд���⣬ϵͳ��ͳһת����Сд
 * �������ò�������������Чǰ��̬�ı�
 +------------------------------------------------------------------------------
 * @category Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id: convention.php 2756 2012-02-19 10:38:32Z liu21st $
 +------------------------------------------------------------------------------
 */
if (!defined('THINK_PATH')) exit();
return  array(
    /* ��Ŀ�趨 */
    'APP_STATUS'            => 'debug',  // Ӧ�õ���ģʽ״̬ ����ģʽ��������Ч Ĭ��Ϊdebug ����չ ���Զ����ض�Ӧ�������ļ�
    'APP_FILE_CASE'         => false,   // �Ƿ����ļ��Ĵ�Сд ��Windowsƽ̨��Ч
    'APP_AUTOLOAD_PATH'     => '',// �Զ����ػ��Ƶ��Զ�����·��,ע������˳��
    'APP_TAGS_ON'           => true, // ϵͳ��ǩ��չ����
    'APP_SUB_DOMAIN_DEPLOY' => false,   // �Ƿ�������������
    'APP_SUB_DOMAIN_RULES'  => array(), // �������������
    'APP_SUB_DOMAIN_DENY'   => array(), //  �����������б�
    'APP_GROUP_LIST'        => '',      // ��Ŀ�����趨,�����֮���ö��ŷָ�,����'Home,Admin'

    /* Cookie���� */
    'COOKIE_EXPIRE'         => 3600,    // Coodie��Ч��
    'COOKIE_DOMAIN'         => '',      // Cookie��Ч����
    'COOKIE_PATH'           => '/',     // Cookie·��
    'COOKIE_PREFIX'         => '',      // Cookieǰ׺ �����ͻ

    /* Ĭ���趨 */
    'DEFAULT_APP'           => '@',     // Ĭ����Ŀ���ƣ�@��ʾ��ǰ��Ŀ
    'DEFAULT_LANG'          => 'zh-cn', // Ĭ������
    'DEFAULT_THEME'    => '',	// Ĭ��ģ����������
    'DEFAULT_GROUP'         => 'Home',  // Ĭ�Ϸ���
    'DEFAULT_MODULE'        => 'Index', // Ĭ��ģ������
    'DEFAULT_ACTION'        => 'index', // Ĭ�ϲ�������
    'DEFAULT_CHARSET'       => 'GBK', // Ĭ���������
    'DEFAULT_TIMEZONE'      => 'PRC',	// Ĭ��ʱ��
    'DEFAULT_AJAX_RETURN'   => 'JSON',  // Ĭ��AJAX ���ݷ��ظ�ʽ,��ѡJSON XML ...
    'DEFAULT_FILTER'        => 'htmlspecialchars', // Ĭ�ϲ������˷��� ���� $this->_get('������');$this->_post('������')...

    /* ���ݿ����� */
    'DB_TYPE'               => 'mysql',     // ���ݿ�����
	'DB_HOST'               => 'localhost', // ��������ַ
	'DB_NAME'               => '',          // ���ݿ���
	'DB_USER'               => 'root',      // �û���
	'DB_PWD'                => '',          // ����
	'DB_PORT'               => '',        // �˿�
	'DB_PREFIX'             => 'think_',    // ���ݿ��ǰ׺
    'DB_FIELDTYPE_CHECK'    => false,       // �Ƿ�����ֶ����ͼ��
    'DB_FIELDS_CACHE'       => true,        // �����ֶλ���
    'DB_CHARSET'            => 'gbk',      // ���ݿ����Ĭ�ϲ���utf8
    'DB_DEPLOY_TYPE'        => 0, // ���ݿⲿ��ʽ:0 ����ʽ(��һ������),1 �ֲ�ʽ(���ӷ�����)
    'DB_RW_SEPARATE'        => false,       // ���ݿ��д�Ƿ���� ����ʽ��Ч
    'DB_MASTER_NUM'         => 1, // ��д����� ������������
    'DB_SQL_BUILD_CACHE'    => false, // ���ݿ��ѯ��SQL��������
    'DB_SQL_BUILD_QUEUE'    => 'Tae',   // SQL������еĻ��淽ʽ ֧�� file xcache��apc
    'DB_SQL_BUILD_LENGTH'   => 20, // SQL����Ķ��г���

    /* ���ݻ������� */
    'DATA_CACHE_TIME'		=> 0,      // ���ݻ�����Ч�� 0��ʾ���û���
    'DATA_CACHE_COMPRESS'   => false,   // ���ݻ����Ƿ�ѹ������
    'DATA_CACHE_CHECK'		=> false,   // ���ݻ����Ƿ�У�黺��
    'DATA_CACHE_TYPE'		=> 'Tae',  // ���ݻ�������,֧��:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_PATH'       => TEMP_PATH,// ����·������ (����File��ʽ������Ч)
    'DATA_CACHE_SUBDIR'		=> false,    // ʹ����Ŀ¼���� (�Զ����ݻ����ʶ�Ĺ�ϣ������Ŀ¼)
    'DATA_PATH_LEVEL'       => 1,        // ��Ŀ¼���漶��

    /* �������� */
    'ERROR_MESSAGE'         => '�������ҳ����ʱ�����˴������Ժ����ԡ�',//������ʾ��Ϣ,�ǵ���ģʽ��Ч
    'ERROR_PAGE'            => '',	// ������ҳ��
    'SHOW_ERROR_MSG'        => false,    // ��ʾ������Ϣ

    /* ��־���� */
    'LOG_RECORD'            => false,   // Ĭ�ϲ���¼��־
    'LOG_TYPE'                 => 3, // ��־��¼���� 0 ϵͳ 1 �ʼ� 3 �ļ� 4 SAPI Ĭ��Ϊ�ļ���ʽ
    'LOG_DEST'                 => '', // ��־��¼Ŀ��
    'LOG_EXTRA'               => '', // ��־��¼������Ϣ
    'LOG_LEVEL'                => 'EMERG,ALERT,CRIT,ERR',// �����¼����־����
    'LOG_FILE_SIZE'         => 2097152,	// ��־�ļ���С����
    'LOG_EXCEPTION_RECORD'  => false,    // �Ƿ��¼�쳣��Ϣ��־

    /* SESSION���� */
    'SESSION_AUTO_START'    => false,    // �Ƿ��Զ�����Session
    'SESSION_OPTIONS'           => array(), // session �������� ֧��type name id path expire domian �Ȳ���
    'SESSION_TYPE'              => '', // session hander���� Ĭ���������� ������չ��session hander����
    'SESSION_PREFIX'            => '', // session ǰ׺
    'VAR_SESSION_ID'        => 'session_id',     //sessionID���ύ����

    /* ģ���������� */
    'TMPL_CONTENT_TYPE'     => 'text/html', // Ĭ��ģ���������
    'TMPL_ACTION_ERROR'     => THINK_PATH.'Tpl/dispatch_jump.tpl', // Ĭ�ϴ�����ת��Ӧ��ģ���ļ�
    'TMPL_ACTION_SUCCESS'   => THINK_PATH.'Tpl/dispatch_jump.tpl', // Ĭ�ϳɹ���ת��Ӧ��ģ���ļ�
    'TMPL_EXCEPTION_FILE'   => THINK_PATH.'Tpl/think_exception.tpl',// �쳣ҳ���ģ���ļ�
    'TMPL_DETECT_THEME'     => false,       // �Զ����ģ������
    'TMPL_TEMPLATE_SUFFIX'  => '.html',     // Ĭ��ģ���ļ���׺
    'TMPL_FILE_DEPR'=>'/', //ģ���ļ�MODULE_NAME��ACTION_NAME֮��ķָ����ֻ����Ŀ���鲿����Ч

    /* URL���� */
	'URL_CASE_INSENSITIVE'  => false,   // Ĭ��false ��ʾURL���ִ�Сд true���ʾ�����ִ�Сд
    'URL_MODEL'             => 0,       // URL����ģʽ,��ѡ����0��1��2��3,������������ģʽ��
    // 0 (��ͨģʽ); 1 (PATHINFO ģʽ); 2 (REWRITE  ģʽ); 3 (����ģʽ)  Ĭ��ΪPATHINFO ģʽ���ṩ��õ��û������SEO֧��
    'URL_PATHINFO_DEPR'     => '/',	// PATHINFOģʽ�£�������֮��ķָ����
    'URL_PATHINFO_FETCH'     =>   'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL', // ���ڼ����ж�PATH_INFO ������SERVER��������б�
    'URL_HTML_SUFFIX'       => '',  // URLα��̬��׺����


    /* ϵͳ������������ */
    'VAR_GROUP'             => 'g',     // Ĭ�Ϸ����ȡ����
    'VAR_MODULE'            => 'm',		// Ĭ��ģ���ȡ����
    'VAR_ACTION'            => 'a',		// Ĭ�ϲ�����ȡ����
    'VAR_AJAX_SUBMIT'       => 'ajax',  // Ĭ�ϵ�AJAX�ύ����
    'VAR_PATHINFO'          => 's',	// PATHINFO ����ģʽ��ȡ�������� ?s=/module/action/id/1 ����Ĳ���ȡ����URL_PATHINFO_DEPR
    'VAR_URL_PARAMS'      => '_URL_', // PATHINFO URL��������
    'VAR_TEMPLATE'          => 't',		// Ĭ��ģ���л�����

);