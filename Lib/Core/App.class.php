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
// $Id: App.class.php 2792 2012-03-02 03:36:36Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP Ӧ�ó����� ִ��Ӧ�ù��̹���
 * ������ģʽ��չ�����¶��� ���Ǳ������Run�����ӿ�
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: App.class.php 2792 2012-03-02 03:36:36Z liu21st $
 +------------------------------------------------------------------------------
 */
class App {

    /**
     +----------------------------------------------------------
     * Ӧ�ó����ʼ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function init() {

        // ����ϵͳʱ��
        date_default_timezone_set(C('DEFAULT_TIMEZONE'));
        // ���ض�̬��Ŀ�����ļ�������
        load_ext_file();
        // URL����
        Dispatcher::dispatch();

        if(defined('GROUP_NAME')) {
            // ���ط��������ļ�
            if(is_file(CONF_PATH.GROUP_NAME.'/config.php'))
                C(include CONF_PATH.GROUP_NAME.'/config.php');
            // ���ط��麯���ļ�
            if(is_file(COMMON_PATH.GROUP_NAME.'/function.php'))
                include COMMON_PATH.GROUP_NAME.'/function.php';
        }
        return ;
    }

    /**
     +----------------------------------------------------------
     * ִ��Ӧ�ó���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    static public function exec() {
        // ��ȫ���
        if(!preg_match('/^[A-Za-z_0-9]+$/',MODULE_NAME)){
            $module =  false;
        }else{
            //����Action������ʵ��
            $group =  defined('GROUP_NAME') ? GROUP_NAME.'/' : '';
            $module  =  A($group.MODULE_NAME);
        }

        if(!$module) {
            if(function_exists('__hack_module')) {
                // hack ��ʽ������չģ�� ����Action����
                $module = __hack_module();
                if(!is_object($module)) {
                    // ���ټ���ִ�� ֱ�ӷ���
                    return ;
                }
            }else{
                // �Ƿ���Emptyģ��
                $module = A('Empty');
                if(!$module){
                    $msg =  L('_MODULE_NOT_EXIST_').MODULE_NAME;
                    if(APP_DEBUG) {
                        // ģ�鲻���� �׳��쳣
                        throw_exception($msg);
                    }else{
                        if(C('LOG_EXCEPTION_RECORD')) Log::record($msg);
                        send_http_status(404);
                        die($msg);
                    }
                }
            }
        }
        //��ȡ��ǰ������
        $action = ACTION_NAME;
        // ��ȡ������������ǩ
        tag('action_name',$action);
        if (method_exists($module,'_before_'.$action)) {
            // ִ��ǰ�ò���
            call_user_func(array(&$module,'_before_'.$action));
        }
        //ִ�е�ǰ����
        call_user_func(array(&$module,$action));
        if (method_exists($module,'_after_'.$action)) {
            //  ִ�к�׺����
            call_user_func(array(&$module,'_after_'.$action));
        }
        return ;
    }

    /**
     +----------------------------------------------------------
     * ����Ӧ��ʵ�� ����ļ�ʹ�õĿ�ݷ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function run() {
        // ��Ŀ��ʼ����ǩ
        tag('app_init');
        App::init();
        // ��Ŀ��ʼ��ǩ
        tag('app_begin');
        // ��¼Ӧ�ó�ʼ��ʱ��
        G('initTime');
        App::exec();
        // ��Ŀ������ǩ
        tag('app_end');
        // ������־��¼
        return ;
    }

}