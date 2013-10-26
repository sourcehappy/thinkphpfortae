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
// $Id: Dispatcher.class.php 2840 2012-03-23 05:56:20Z liu21st@gmail.com $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP���õ�Dispatcher��
 * ���URL������·�ɺ͵���
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Dispatcher.class.php 2840 2012-03-23 05:56:20Z liu21st@gmail.com $
 +------------------------------------------------------------------------------
 */
class Dispatcher {

    /**
     +----------------------------------------------------------
     * URLӳ�䵽������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function dispatch() {
        // ��ȡ���� ģ��Ͳ�������
        if (C('APP_GROUP_LIST')) {
            define('GROUP_NAME', self::getGroup(C('VAR_GROUP')));
        }
        define('MODULE_NAME',self::getModule(C('VAR_MODULE')));
        define('ACTION_NAME',self::getAction(C('VAR_ACTION')));
        // URL����
        define('__SELF__',strip_tags($_SERVER['REQUEST_URI']));
        // ��ǰ��Ŀ��ַ
        define('__APP__',$_taeServer."/");
        // ��ǰģ��ͷ����ַ
        $module = defined('P_MODULE_NAME')?P_MODULE_NAME:MODULE_NAME;
        if(defined('GROUP_NAME')) {
            define('__GROUP__',(!empty($domainGroup) || strtolower(GROUP_NAME) == strtolower(C('DEFAULT_GROUP')) )?__APP__ : __APP__.'/'.GROUP_NAME);
        }else{
            define('__URL__',!empty($domainModule)?__APP__.'/' : __APP__.'/'.$module);
        }
        // ��ǰ������ַ
        define('__ACTION__',__URL__.$depr.ACTION_NAME);
        //��֤$_REQUEST����ȡֵ
        $_REQUEST = array_merge($_POST,$_GET);
    }

    /**
     +----------------------------------------------------------
     * ·�ɼ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function routerCheck() {
        $return   =  false;
        // ·�ɼ���ǩ
        tag('route_check',$return);
        return $return;
    }

    /**
     +----------------------------------------------------------
     * ���ʵ�ʵ�ģ������
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static private function getModule($var) {
        $module = (!empty($_GET[$var])? $_GET[$var]:C('DEFAULT_MODULE'));
        unset($_GET[$var]);
        if(C('URL_CASE_INSENSITIVE')) {
            // URL��ַ�����ִ�Сд
            define('P_MODULE_NAME',strtolower($module));
            // ����ʶ��ʽ index.php/user_type/index/ ʶ�� UserTypeAction ģ��
            $module = ucfirst(parse_name(P_MODULE_NAME,1));
        }
        return strip_tags($module);
    }

    /**
     +----------------------------------------------------------
     * ���ʵ�ʵĲ�������
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static private function getAction($var) {
        $action   = !empty($_POST[$var]) ?
            $_POST[$var] :
            (!empty($_GET[$var])?$_GET[$var]:C('DEFAULT_ACTION'));
        unset($_POST[$var],$_GET[$var]);
        define('P_ACTION_NAME',$action);
        return strip_tags(C('URL_CASE_INSENSITIVE')?strtolower($action):$action);
    }

    /**
     +----------------------------------------------------------
     * ���ʵ�ʵķ�������
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static private function getGroup($var) {
        $group   = (!empty($_GET[$var])?$_GET[$var]:C('DEFAULT_GROUP'));
        unset($_GET[$var]);
        return strip_tags(C('URL_CASE_INSENSITIVE') ?ucfirst(strtolower($group)):$group);
    }

}