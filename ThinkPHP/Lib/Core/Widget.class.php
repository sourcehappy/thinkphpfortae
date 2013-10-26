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
 * ThinkPHP Widget类 抽象类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author liu21st <liu21st@gmail.com>
 * @version  $Id: Widget.class.php 2783 2012-02-25 06:49:45Z liu21st $
 +------------------------------------------------------------------------------
 */
abstract class Widget {

    // 使用的模板引擎 每个Widget可以单独配置不受系统影响
    protected $template =  '';

    /**
     +----------------------------------------------------------
     * 渲染输出 render方法是Widget唯一的接口
     * 使用字符串返回 不能有任何输出
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data  要渲染的数据
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    abstract public function render($data);

    /**
     +----------------------------------------------------------
     * 渲染模板输出 供render方法内部调用
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile  模板文件
     * @param mixed $var  模板变量
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function renderFile($templateFile='',$var='') {


        if(!file_exists_case($templateFile)){
            // 自动定位模板文件
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