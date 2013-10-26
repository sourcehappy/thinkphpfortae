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
// $Id: View.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP ��ͼ���
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author liu21st <liu21st@gmail.com>
 * @version  $Id: View.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class View {
    protected $tVar        =  array(); // ģ���������

    /**
     +----------------------------------------------------------
     * ģ�������ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $name
     * @param mixed $value
     +----------------------------------------------------------
     */
    public function assign($name,$value=''){
        if(is_array($name)) {
            $this->tVar   =  array_merge($this->tVar,$name);
        }elseif(is_object($name)){
            foreach($name as $key =>$val)
                $this->tVar[$key] = $val;
        }else {
            $this->tVar[$name] = $value;
        }
    }

    /**
     +----------------------------------------------------------
     * ȡ��ģ�������ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function get($name){
        if(isset($this->tVar[$name]))
            return $this->tVar[$name];
        else
            return false;
    }

    /* ȡ������ģ����� */
    public function getAllVar(){
        return $this->tVar;
    }

    // ����ҳ�����е�ģ�����
    public function traceVar(){
        foreach ($this->tVar as $name=>$val){
            dump($val,1,'['.$name.']<br/>');
        }
    }

    /**
     +----------------------------------------------------------
     * ����ģ���ҳ����� ���Է����������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile ģ���ļ���
     * @param string $charset ģ������ַ���
     * @param string $contentType �������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function display($templateFile='',$charset='',$contentType='') {
        G('viewStartTime');
        // ��ͼ��ʼ��ǩ
        tag('view_begin',$templateFile);
        // ��������ȡģ������
        $content = $this->fetch($templateFile);
        // ���ģ������
        $this->show($content,$charset,$contentType);
        // ��ͼ������ǩ
        tag('view_end');
    }

    /**
     +----------------------------------------------------------
     * ��������ı����԰���Html
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $content �������
     * @param string $charset ģ������ַ���
     * @param string $contentType �������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function show($content,$charset='',$contentType=''){
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE');
        // ��ҳ�ַ�����
        header('Content-Type:'.$contentType.'; charset='.$charset);
        header('Cache-control: private');  //֧��ҳ�����
        // ���ģ���ļ�
        echo $content;
    }

    /**
     +----------------------------------------------------------
     * �����ͻ�ȡģ������ �������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile ģ���ļ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function fetch($templateFile='') {
        // ģ���ļ�������ǩ
        tag('view_template',$templateFile);
        // ģ���ļ�������ֱ�ӷ���
        if(!is_file($templateFile)) return NULL;
        // ҳ�滺��

        if('php' == strtolower(C('TMPL_ENGINE_TYPE'))) { // ʹ��PHPԭ��ģ��
            // ģ�����б����ֽ��Ϊ��������
            extract($this->tVar, EXTR_OVERWRITE);
            // ֱ������PHPģ��
            include $templateFile;
        }else{
            // ��ͼ������ǩ
            $params = array('var'=>$this->tVar,'file'=>$templateFile);
            tag('view_parse',$params);
        }
        // ��ȡ����ջ���
        $content = $params['content'];
        // ���ݹ��˱�ǩ
        tag('view_filter',$content);
        // ���ģ���ļ�
        return $content;
    }
}