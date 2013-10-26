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
// $Id: Debug.class.php 2702 2012-02-02 12:35:01Z liu21st $

 /**
 +------------------------------------------------------------------------------
 * ϵͳ������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Debug.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class Debug {

    static private $marker =  array();
    /**
     +----------------------------------------------------------
     * ��ǵ���λ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name  Ҫ��ǵ�λ������
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function mark($name) {
        self::$marker['time'][$name]  =  microtime(TRUE);
        if(MEMORY_LIMIT_ON) {
            self::$marker['mem'][$name] = memory_get_usage();
            self::$marker['peak'][$name] = function_exists('memory_get_peak_usage')?memory_get_peak_usage(): self::$marker['mem'][$name];
        }
    }

    /**
     +----------------------------------------------------------
     * ����ʹ��ʱ��鿴
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $start  ��ʼ��ǵ�����
     * @param string $end  ������ǵ�����
     * @param integer $decimals  ʱ���С��λ
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    static public function useTime($start,$end,$decimals = 6) {
        if ( ! isset(self::$marker['time'][$start]))
            return '';
        if ( ! isset(self::$marker['time'][$end]))
            self::$marker['time'][$end] = microtime(TRUE);
        return number_format(self::$marker['time'][$end] - self::$marker['time'][$start], $decimals);
    }

    /**
     +----------------------------------------------------------
     * ����ʹ���ڴ�鿴
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $start  ��ʼ��ǵ�����
     * @param string $end  ������ǵ�����
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    static public function useMemory($start,$end) {
        if(!MEMORY_LIMIT_ON)
            return '';
        if ( ! isset(self::$marker['mem'][$start]))
            return '';
        if ( ! isset(self::$marker['mem'][$end]))
            self::$marker['mem'][$end] = memory_get_usage();
        return number_format((self::$marker['mem'][$end] - self::$marker['mem'][$start])/1024);
    }

    /**
     +----------------------------------------------------------
     * ����ʹ���ڴ��ֵ�鿴
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $start  ��ʼ��ǵ�����
     * @param string $end  ������ǵ�����
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    static function getMemPeak($start,$end) {
        if(!MEMORY_LIMIT_ON)
            return '';
        if ( ! isset(self::$marker['peak'][$start]))
            return '';
        if ( ! isset(self::$marker['peak'][$end]))
            self::$marker['peak'][$end] = function_exists('memory_get_peak_usage')?memory_get_peak_usage(): memory_get_usage();
        return number_format(max(self::$marker['peak'][$start],self::$marker['peak'][$end])/1024);
    }
}