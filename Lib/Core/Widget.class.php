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
// $Id: Widget.class.php 2783 2012-02-25 06:49:45Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP Widget�� ������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author liu21st <liu21st@gmail.com>
 * @version  $Id: Widget.class.php 2783 2012-02-25 06:49:45Z liu21st $
 +------------------------------------------------------------------------------
 */
abstract class Widget {

    // ʹ�õ�ģ������ ÿ��Widget���Ե������ò���ϵͳӰ��
    protected $template =  '';

    /**
     +----------------------------------------------------------
     * ��Ⱦ��� render������WidgetΨһ�Ľӿ�
     * ʹ���ַ������� �������κ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data  Ҫ��Ⱦ������
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    abstract public function render($data);

    /**
     +----------------------------------------------------------
     * ��Ⱦģ����� ��render�����ڲ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile  ģ���ļ�
     * @param mixed $var  ģ�����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function renderFile($templateFile='',$var='') {


        if(!file_exists_case($templateFile)){
            // �Զ���λģ���ļ�
            $name   = substr(get_class($this),0,-6);
            $filename   =  empty($templateFile)?$name:$templateFile;
            $templateFile = THEME_PATH.'Widget/'.$filename.C('TMPL_TEMPLATE_SUFFIX');
            if(!file_exists_case($templateFile))
                throw_exception(L('_TEMPLATE_NOT_EXIST_').'['.$templateFile.']');
        }
        $template   =  strtolower($this->template?$this->template:(C('TMPL_ENGINE_TYPE')?C('TMPL_ENGINE_TYPE'):'php'));
        $tpl   =  new Smarty();

		$Think['ACTION_NAME']=ACTION_NAME;
		$Think['Request']=$_REQUEST;
		$Think['Post']=$_POST;
		$Think['Get']=$_GET;
		$Think['Config']=C();
		$tpl->assign('Think', $Think);


		$tpl->assign($var);
		$content=$tpl->fetch($templateFile);
        return $content;
    }
}