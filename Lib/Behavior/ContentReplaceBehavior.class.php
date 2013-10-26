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
// $Id: ContentReplaceBehavior.class.php 2777 2012-02-23 13:07:50Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ ģ����������滻
 +------------------------------------------------------------------------------
 */
class ContentReplaceBehavior extends Behavior {
    // ��Ϊ��������
    protected $options   =  array(
        'TMPL_PARSE_STRING'=>array(),
    );

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$content){
        $content = $this->templateContentReplace($content);
    }

    /**
     +----------------------------------------------------------
     * ģ�������滻
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $content ģ������
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function templateContentReplace($content) {
        // ϵͳĬ�ϵ���������滻
        $replace =  array(
            '__TMPL__'      => APP_TMPL_PATH,  // ��Ŀģ��Ŀ¼
            '__ROOT__'      => __ROOT__,       // ��ǰ��վ��ַ
            '__APP__'       => __APP__,        // ��ǰ��Ŀ��ַ
            '__GROUP__'   =>   defined('GROUP_NAME')?__GROUP__:__APP__,
            '__ACTION__'    => __ACTION__,     // ��ǰ������ַ
            '__SELF__'      => __SELF__,       // ��ǰҳ���ַ
            '__URL__'       => __URL__,
            '../Public'   => APP_TMPL_PATH.'Public',// ��Ŀ����ģ��Ŀ¼
            '__PUBLIC__'  => __ROOT__.'/Public',// վ�㹫��Ŀ¼
        );
        // �����û��Զ���ģ����ַ����滻
        if(is_array(C('TMPL_PARSE_STRING')) )
            $replace =  array_merge($replace,C('TMPL_PARSE_STRING'));
        $content = str_replace(array_keys($replace),array_values($replace),$content);
        return $content;
    }

}