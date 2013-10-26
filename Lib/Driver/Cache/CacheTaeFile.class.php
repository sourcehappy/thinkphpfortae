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
// $Id: CacheFile.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * �ļ����ͻ�����
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: CacheFile.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class CacheTaeFile extends Cache {

    /**
     +----------------------------------------------------------
     * ����洢ǰ׺
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $prefix='~@';

    /**
     +----------------------------------------------------------
     * �ܹ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($options='') {
        if(!empty($options)) {
            $this->options =  $options;
        }
        $this->options['temp'] = !empty($options['temp'])?$options['temp']:C('DATA_CACHE_PATH');
        $this->options['expire'] = isset($options['expire'])?$options['expire']:C('DATA_CACHE_TIME');
        $this->options['length']  =  isset($options['length'])?$options['length']:0;
        if(substr($this->options['temp'], -1) != "/")    $this->options['temp'] .= "/";
        $this->connected = true;
        $this->init();
    }

    /**
     +----------------------------------------------------------
     * ��ʼ�����
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    private function init() {
            if ( !$fileStoreService->createDir($this->options['temp']) )
                return false;
    }

    /**
     +----------------------------------------------------------
     * �Ƿ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    private function isConnected() {
        return $this->connected;
    }

    /**
     +----------------------------------------------------------
     * ȡ�ñ����Ĵ洢�ļ���
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @param string $name ���������
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function filename($name) {
        $name	=	md5($name);
        if(C('DATA_CACHE_SUBDIR')) {
            // ʹ����Ŀ¼
            $dir   ='';
            for($i=0;$i<C('DATA_PATH_LEVEL');$i++) {
                $dir	.=	$name{$i}.'/';
            }
            $fileStoreService->createDir($this->options['temp'].$dir);
            $filename	=	$dir.$this->prefix.$name.'.php';
        }else{
            $filename	=	$this->prefix.$name.'.php';
        }
        return $this->options['temp'].$filename;
    }

    /**
     +----------------------------------------------------------
     * ��ȡ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ���������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function get($name) {
        $filename   =   $this->filename($name);
        if (!$this->isConnected() || !$fileStoreService->isFileExist($filename)) {
           return false;
        }
        N('cache_read',1);
        $content    =   $fileStoreService->getFileText($filename);
        if( false !== $content) {
            $expire  =  (int)substr($content,8, 12);
            if($expire != 0 && time() > $expire) {
                //�������ɾ�������ļ�
                $fileStoreService->deleteFile($filename);
                return false;
            }
            if(C('DATA_CACHE_CHECK')) {//��������У��
                $check  =  substr($content,20, 32);
                $content   =  substr($content,52, -3);
                if($check != md5($content)) {//У�����
                    return false;
                }
            }else {
            	$content   =  substr($content,20, -3);
            }
            if(C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
                //��������ѹ��
                $content   =   gzuncompress($content);
            }
            $content    =   unserialize( unserialize_fix( $content )  );
            return $content;
        }
        else {
            return false;
        }
    }

    /**
     +----------------------------------------------------------
     * д�뻺��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ���������
     * @param mixed $value  �洢����
     * @param int $expire  ��Чʱ�� 0Ϊ����
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function set($name,$value,$expire=null) {
       N('cache_write',1);
        if(is_null($expire)) {
            $expire =  $this->options['expire'];
        }

		if ($expire)
			$expire=time()+$expire;

        $filename   =   $this->filename($name);
        $data   =   serialize( $value );
        if( C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
            //����ѹ��
            $data   =   gzcompress($data,3);
        }
        if(C('DATA_CACHE_CHECK')) {//��������У��
            $check  =  md5($data);
        }else {
            $check  =  '';
        }
        $data    = "<?php\n//".sprintf('%012d',$expire).$check.$data."\n?>";
        $result  =   $fileStoreService->saveTextFile($data,$filename);

        if($result) {
            if($this->options['length']>0) {
                // ��¼�������
                $this->queue($name);
            }
            return true;
        }else {
            return false;
        }
    }

    /**
     +----------------------------------------------------------
     * ɾ������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ���������
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function rm($name) {
        return $fileStoreService->deleteFile($this->filename($name));
    }

    /**
     +----------------------------------------------------------
     * �������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ���������
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function clear() {
        $path   =  $this->options['temp'];
		return $fileStoreService->deleteDir($path);
    }

}