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
// $Id: CheckTemplateBehavior.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ ģ����
 +------------------------------------------------------------------------------
 */
class CheckTemplateBehavior extends Behavior {
    // ��Ϊ�������壨Ĭ��ֵ�� ������Ŀ�����и���
    protected $options   =  array(
            'VAR_TEMPLATE'          => 't',		// Ĭ��ģ���л�����
            'TMPL_DETECT_THEME'     => false,       // �Զ����ģ������
            'DEFAULT_THEME'    => '',	// Ĭ��ģ����������
            'TMPL_TEMPLATE_SUFFIX'  => '.html',     // Ĭ��ģ���ļ���׺
            'TMPL_FILE_DEPR'=>'/', //ģ���ļ�MODULE_NAME��ACTION_NAME֮��ķָ����ֻ����Ŀ���鲿����Ч
        );

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$params){
        // ������̬����
        $this->checkTemplate();
    }

    /**
     +----------------------------------------------------------
     * ģ���飬���������ʹ��Ĭ��
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function checkTemplate() {
        /* ��ȡģ���������� */
        $templateSet =  C('DEFAULT_THEME');
        if(C('TMPL_DETECT_THEME')) {// �Զ����ģ������
            $t = C('VAR_TEMPLATE');
            if (isset($_GET[$t])){
                $templateSet = $_GET[$t];
            }elseif($themes=S('uz_themes')){
                $templateSet = $themes;
            }
 			// ���ⲻ����ʱ�ԸĻ�ʹ��Ĭ������
            if(!is_file( TMPL_PATH.$templateSet."/".MODULE_NAME."/".ACTION_NAME.C('TMPL_TEMPLATE_SUFFIX') ))
                $templateSet = C('DEFAULT_THEME');
            S('uz_themes',$templateSet,0);
        }

        /* ģ�����Ŀ¼���� */
        define('THEME_NAME',   $templateSet);                  // ��ǰģ����������
        $group   =  defined('GROUP_NAME')?GROUP_NAME.'/':'';
        define('THEME_PATH',   TMPL_PATH.$group.(THEME_NAME?THEME_NAME.'/':''));
        define('APP_TMPL_PATH',__ROOT__.'/'.APP_NAME.(APP_NAME?'/':'').'Tpl/'.$group.(THEME_NAME?THEME_NAME.'/':''));
        C('TEMPLATE_NAME',THEME_PATH.MODULE_NAME.(defined('GROUP_NAME')?C('TMPL_FILE_DEPR'):'/').ACTION_NAME.C('TMPL_TEMPLATE_SUFFIX'));
        C('CACHE_PATH',CACHE_PATH.$group);
        return ;
    }
}