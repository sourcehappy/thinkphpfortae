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
// $Id: ShowRuntimeBehavior.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ ����ʱ����Ϣ��ʾ
 +------------------------------------------------------------------------------
 */
class ShowRuntimeBehavior extends Behavior {
    // ��Ϊ��������
    protected $options   =  array(
        'SHOW_RUN_TIME'			=> false,   // ����ʱ����ʾ
        'SHOW_ADV_TIME'			=> false,   // ��ʾ��ϸ������ʱ��
        'SHOW_DB_TIMES'			=> false,   // ��ʾ���ݿ��ѯ��д�����
        'SHOW_CACHE_TIMES'		=> false,   // ��ʾ�����������
        'SHOW_USE_MEM'			=> false,   // ��ʾ�ڴ濪��
        'SHOW_FUN_TIMES'         => false ,  // ��ʾ�������ô���
    );

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$content){
        if(C('SHOW_RUN_TIME')){
            if(false !== strpos($content,'{__NORUNTIME__}')) {
                $content   =  str_replace('{__NORUNTIME__}','',$content);
            }else{
                $runtime = $this->showTime();
                 if(strpos($content,'{__RUNTIME__}'))
                     $content   =  str_replace('{__RUNTIME__}',$runtime,$content);
                 else
                     $content   .=  $runtime;
            }
        }else{
            $content   =  str_replace(array('{__NORUNTIME__}','{__RUNTIME__}'),'',$content);
        }
    }

    /**
     +----------------------------------------------------------
     * ��ʾ����ʱ�䡢���ݿ����������������ڴ�ʹ����Ϣ
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function showTime() {
        // ��ʾ����ʱ��
        G('beginTime',$GLOBALS['_beginTime']);
        G('viewEndTime');
        $showTime   =   'Process: '.G('beginTime','viewEndTime').'s ';
        if(C('SHOW_ADV_TIME')) {
            // ��ʾ��ϸ����ʱ��
            $showTime .= '( Load:'.G('beginTime','loadTime').'s Init:'.G('loadTime','initTime').'s Exec:'.G('initTime','viewStartTime').'s Template:'.G('viewStartTime','viewEndTime').'s )';
        }
        if(C('SHOW_DB_TIMES') && class_exists('Db',false) ) {
            // ��ʾ���ݿ��������
            $showTime .= ' | DB :'.N('db_query').' queries '.N('db_write').' writes ';
        }
        if(C('SHOW_CACHE_TIMES') && class_exists('Cache',false)) {
            // ��ʾ�����д����
            $showTime .= ' | Cache :'.N('cache_read').' gets '.N('cache_write').' writes ';
        }
        if(MEMORY_LIMIT_ON && C('SHOW_USE_MEM')) {
            // ��ʾ�ڴ濪��
            $showTime .= ' | UseMem:'. number_format((memory_get_usage() - $GLOBALS['_startUseMems'])/1024).' kb';
        }

        if(C('SHOW_FUN_TIMES')) {
            $fun  =  get_defined_functions();
            $showTime .= ' | CallFun:'.count($fun['user']).','.count($fun['internal']);
        }
        return $showTime;
    }
}