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
// $Id: Think.class.php 2791 2012-02-29 10:08:57Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP Portal��
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Think.class.php 2791 2012-02-29 10:08:57Z liu21st $
 +------------------------------------------------------------------------------
 */
class Think {

    private static $_instance = array();

    /**
     +----------------------------------------------------------
     * Ӧ�ó����ʼ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function Start() {
        //[RUNTIME]
        Think::buildApp();         // Ԥ������Ŀ
        //[/RUNTIME]
        // ����Ӧ��
        App::run();
        return ;
    }

    //[RUNTIME]
    /**
     +----------------------------------------------------------
     * ��ȡ������Ϣ ������Ŀ
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static private function buildApp() {
        // ���صײ���������ļ�
        C(include THINK_PATH.'Conf/convention.php');

        // ��ȡ����ģʽ
        if(defined('MODE_NAME')) { // ģʽ�����ò������ģʽ
            $mode   = include MODE_PATH.strtolower(MODE_NAME).'.php';
        }else{
            $mode   =  array();
        }

        // ����ģʽ�����ļ�
        if(isset($mode['config'])) {
            C( is_array($mode['config'])?$mode['config']:include $mode['config'] );
        }

        // ������Ŀ�����ļ�
        if(is_file(CONF_PATH.'config.php'))
            C(include CONF_PATH.'config.php');

        // ���ؿ�ܵײ����԰�
        L(include THINK_PATH.'Lang/'.strtolower(C('DEFAULT_LANG')).'.php');

        // ����ģʽϵͳ��Ϊ����
        if(C('APP_TAGS_ON')) {
            if(isset($mode['extends'])) {
                C('extends',is_array($mode['extends'])?$mode['extends']:include $mode['extends']);
            }else{ // Ĭ�ϼ���ϵͳ��Ϊ��չ����
                C('extends', include THINK_PATH.'Conf/tags.php');
            }
        }

        // ����Ӧ����Ϊ����
        if(isset($mode['tags'])) {
            C('tags', is_array($mode['tags'])?$mode['tags']:include $mode['tags']);
        }elseif(is_file(CONF_PATH.'tags.php')){
            // Ĭ�ϼ�����Ŀ����Ŀ¼��tags�ļ�����
            C('tags', include CONF_PATH.'tags.php');
        }

        $compile   = '';
        // ��ȡ���ı����ļ��б�
        if(isset($mode['core'])) {
            $list   =  $mode['core'];
        }else{
            $list  =  array(
                THINK_PATH.'Common/functions.php', // ��׼ģʽ������
                CORE_PATH.'Core/Log.class.php',    // ��־������
                CORE_PATH.'Core/Dispatcher.class.php', // URL������
                CORE_PATH.'Core/App.class.php',   // Ӧ�ó�����
                CORE_PATH.'Core/Action.class.php', // ��������
                CORE_PATH.'Core/View.class.php',  // ��ͼ��
                CORE_PATH.'Core/Model.class.php',  // ���ݿ���
                CORE_PATH.'Core/Db.class.php',  // ������
            );
        }
        // ��Ŀ׷�Ӻ��ı����б��ļ�
        if(is_file(CONF_PATH.'core.php')) {
            $list  =  array_merge($list,include CONF_PATH.'core.php');
        }
        foreach ($list as $file){
            if(is_file($file))  {
                require_cache($file);
            }
        }

        // ������Ŀ�����ļ�
        if(is_file(COMMON_PATH.'common.php')) {
            include COMMON_PATH.'common.php';
        }

        // ����ģʽ��������
        if(isset($mode['alias'])) {
            $alias = is_array($mode['alias'])?$mode['alias']:include $mode['alias'];
            alias_import($alias);
         }
        // ������Ŀ��������
        if(is_file(CONF_PATH.'alias.php')){ 
            $alias = include CONF_PATH.'alias.php';
            alias_import($alias);
         }

        if(APP_DEBUG) {
		 // ����ģʽ����ϵͳĬ�ϵ������ļ�
		C(include THINK_PATH.'Conf/debug.php');
		// ��ȡ����ģʽ��Ӧ��״̬
		$status  =  C('APP_STATUS');
		// ���ض�Ӧ����Ŀ�����ļ�
		if(is_file(CONF_PATH.$status.'.php'))
			// ������Ŀ���ӿ���ģʽ���ö���
			C(include CONF_PATH.$status.'.php');
		}

        return ;
    }
    //[/RUNTIME]


    /**
     +----------------------------------------------------------
     * ȡ�ö���ʵ�� ֧�ֵ�����ľ�̬����
     +----------------------------------------------------------
     * @param string $class ��������
     * @param string $method ��ľ�̬������
     +----------------------------------------------------------
     * @return object
     +----------------------------------------------------------
     */
    static public function instance($class,$method='') {
        $identify   =   $class.$method;
        if(!isset(self::$_instance[$identify])) {
            if(class_exists($class)){
                $o = new $class();
                if(!empty($method) && method_exists($o,$method))
                    self::$_instance[$identify] = call_user_func_array(array(&$o, $method));
                else
                    self::$_instance[$identify] = $o;
            }
            else
                halt(L('_CLASS_NOT_EXIST_').':'.$class);
        }
        return self::$_instance[$identify];
    }

    /**
     +----------------------------------------------------------
     * �Զ���������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name ��������
     * @param $value  ����ֵ
     +----------------------------------------------------------
     */
    public function __set($name ,$value) {
        if(property_exists($this,$name))
            $this->$name = $value;
    }

    /**
     +----------------------------------------------------------
     * �Զ�������ȡ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name ��������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __get($name) {
        return isset($this->$name)?$this->$name:null;
    }
}