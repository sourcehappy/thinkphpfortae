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
// $Id: Action.class.php 2791 2012-02-29 10:08:57Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP Action���������� ������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id: Action.class.php 2791 2012-02-29 10:08:57Z liu21st $
 +------------------------------------------------------------------------------
 */
abstract class Action {

    // ��ͼʵ������
    protected $view   =  null;
    // ��ǰAction����
    private $name =  '';

   /**
     +----------------------------------------------------------
     * �ܹ����� ȡ��ģ�����ʵ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
        tag('action_begin');
        //ʵ������ͼ��
        $this->view       = Think::instance('View');
        //��������ʼ��
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

   /**
     +----------------------------------------------------------
     * ��ȡ��ǰAction����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     */
    protected function getActionName() {
        if(empty($this->name)) {
            // ��ȡAction����
            $this->name     =   substr(get_class($this),0,-6);
        }
        return $this->name;
    }

    /**
     +----------------------------------------------------------
     * �Ƿ�AJAX����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
    protected function isAjax() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
            if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
                return true;
        }
        if(!empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')]))
            // �ж�Ajax��ʽ�ύ
            return true;
        return false;
    }

    /**
     +----------------------------------------------------------
     * ģ����ʾ
     * �������õ�ģ��������ʾ������
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $templateFile ָ��Ҫ���õ�ģ���ļ�
     * Ĭ��Ϊ�� ��ϵͳ�Զ���λģ���ļ�
     * @param string $charset �������
     * @param string $contentType �������
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function display($templateFile='',$charset='',$contentType='') {
        $this->view->display($templateFile,$charset,$contentType);
    }

    /**
     +----------------------------------------------------------
     *  ��ȡ���ҳ������
     * �������õ�ģ������fetch������
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $templateFile ָ��Ҫ���õ�ģ���ļ�
     * Ĭ��Ϊ�� ��ϵͳ�Զ���λģ���ļ�
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function fetch($templateFile='') {
        return $this->view->fetch($templateFile);
    }

    /**
     +----------------------------------------------------------
     *  ������̬ҳ��
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @htmlfile ���ɵľ�̬�ļ�����
     * @htmlpath ���ɵľ�̬�ļ�·��
     * @param string $templateFile ָ��Ҫ���õ�ģ���ļ�
     * Ĭ��Ϊ�� ��ϵͳ�Զ���λģ���ļ�
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function buildHtml($htmlfile='',$htmlpath='',$templateFile='') {
        $content = $this->fetch($templateFile);
        $htmlpath   = !empty($htmlpath)?$htmlpath:HTML_PATH;
        $htmlfile =  $htmlpath.$htmlfile.C('HTML_FILE_SUFFIX');
        if(!is_dir(dirname($htmlfile)))
            // �����̬Ŀ¼������ �򴴽�
            mk_dir(dirname($htmlfile));
        if(false === file_put_contents($htmlfile,$content))
            throw_exception(L('_CACHE_WRITE_ERROR_').':'.$htmlfile);
        return $content;
    }

    /**
     +----------------------------------------------------------
     * ģ�������ֵ
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $name Ҫ��ʾ��ģ�����
     * @param mixed $value ������ֵ
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function assign($name,$value='') {
        $this->view->assign($name,$value);
    }

    public function __set($name,$value) {
        $this->view->assign($name,$value);
    }

    /**
     +----------------------------------------------------------
     * ȡ��ģ����ʾ������ֵ
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $name ģ����ʾ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __get($name) {
        return $this->view->get($name);
    }

    /**
     +----------------------------------------------------------
     * ħ������ �в����ڵĲ�����ʱ��ִ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method ������
     * @param array $args ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME)) {
            if(file_exists_case(C('TEMPLATE_NAME'))){
                // ����Ƿ����Ĭ��ģ�� �����ֱ�����ģ��
                $this->display();
            }elseif(method_exists($this,'_empty')) {
                // ���������_empty���� �����
                $this->_empty($method,$args);
            }elseif(function_exists('__hack_action')) {
                // hack ��ʽ������չ����
                __hack_action();
            }elseif(APP_DEBUG) {
                // �׳��쳣
                throw_exception(L('_ERROR_ACTION_').ACTION_NAME);
            }else{
                if(C('LOG_EXCEPTION_RECORD')) Log::record(L('_ERROR_ACTION_').ACTION_NAME);
                send_http_status(404);
                exit( L('_ERROR_ACTION_').ACTION_NAME );
            }
        }else{
            switch(strtolower($method)) {
                // �ж��ύ��ʽ
                case 'ispost':
                case 'isget':
                case 'ishead':
                case 'isdelete':
                case 'isput':
                    return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method,2));
                // ��ȡ���� ֧�ֹ��˺�Ĭ��ֵ ���÷�ʽ $this->_post($key,$filter,$default);
                case '_get':      $input =& $_GET;break;
                case '_post':$input =& $_POST;break;
                case '_put': parse_str(file_get_contents('php://input'), $input);break;
                case '_request': $input =& $_REQUEST;break;
                case '_session': $input =& $_SESSION;break;
                case '_cookie':  $input =& $_COOKIE;break;
                case '_server':  $input =& $_SERVER;break;
                case '_globals':  $input =& $GLOBALS;break;
                default:
                    throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            }
            if(isset($input[$args[0]])) { // ȡֵ����
                $data	 =	 $input[$args[0]];
                $fun  =  $args[1]?$args[1]:C('DEFAULT_FILTER');
                $data	 =	 $fun($data); // ��������
            }else{ // ����Ĭ��ֵ
                $data	 =	 isset($args[2])?$args[2]:NULL;
            }
            return $data;
        }
    }

    /**
     +----------------------------------------------------------
     * ����������ת�Ŀ�ݷ���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $message ������Ϣ
     * @param string $jumpUrl ҳ����ת��ַ
     * @param Boolean $ajax �Ƿ�ΪAjax��ʽ
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function error($message,$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }

    /**
     +----------------------------------------------------------
     * �����ɹ���ת�Ŀ�ݷ���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $message ��ʾ��Ϣ
     * @param string $jumpUrl ҳ����ת��ַ
     * @param Boolean $ajax �Ƿ�ΪAjax��ʽ
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function success($message,$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }

    /**
     +----------------------------------------------------------
     * Ajax��ʽ�������ݵ��ͻ���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data Ҫ���ص�����
     * @param String $info ��ʾ��Ϣ
     * @param boolean $status ����״̬
     * @param String $status ajax�������� JSON XML
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function ajaxReturn($data,$info='',$status=1,$type='') {
        $result  =  array();
        $result['status']  =  $status;
        $result['info'] =  $info;
        $result['data'] = $data;
        //��չajax��������, ��Action�ж���function ajaxAssign(&$result){} ���� ��չajax�������ݡ�
        if(method_exists($this,'ajaxAssign')) 
            $this->ajaxAssign($result);
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        if(strtoupper($type)=='JSON') {
            // ����JSON���ݸ�ʽ���ͻ��� ����״̬��Ϣ
            header('Content-Type:text/html; charset=gbk');
            echo json_encode($result);
        }elseif(strtoupper($type)=='XML'){
            // ����xml��ʽ����
            header('Content-Type:text/xml; charset=gbk');
            echo xml_encode($result);
        }elseif(strtoupper($type)=='EVAL'){
            // ���ؿ�ִ�е�js�ű�
            header('Content-Type:text/html; charset=gbk');
            echo $data;
        }else{
            // TODO ����������ʽ
        }
    }

    /**
     +----------------------------------------------------------
     * Action��ת(URL�ض��� ֧��ָ��ģ�����ʱ��ת
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $url ��ת��URL���ʽ
     * @param array $params ����URL����
     * @param integer $delay ��ʱ��ת��ʱ�� ��λΪ��
     * @param string $msg ��ת��ʾ��Ϣ
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        redirect($url,$delay,$msg);
    }

    /**
     +----------------------------------------------------------
     * Ĭ����ת���� ֧�ִ��������ȷ��ת
     * ����ģ����ʾ Ĭ��ΪpublicĿ¼�����successҳ��
     * ��ʾҳ��Ϊ������ ֧��ģ���ǩ
     +----------------------------------------------------------
     * @param string $message ��ʾ��Ϣ
     * @param Boolean $status ״̬
     * @param string $jumpUrl ҳ����ת��ַ
     * @param Boolean $ajax �Ƿ�ΪAjax��ʽ
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
        // �ж��Ƿ�ΪAJAX����
        if($ajax || $this->isAjax()) $this->ajaxReturn($ajax,$message,$status);
        if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
        // ��ʾ����
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //��������˹رմ��ڣ�����ʾ��Ϻ��Զ��رմ���
        if($this->view->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // ״̬
        //��֤������ܾ�̬����Ӱ��
        C('HTML_CACHE_ON',false);
        if($status) { //���ͳɹ���Ϣ
            $this->assign('message',$message);// ��ʾ��Ϣ
            // �ɹ�������Ĭ��ͣ��1��
            if(!$this->view->get('waitSecond'))    $this->assign('waitSecond','1');
            // Ĭ�ϲ����ɹ��Զ����ز���ǰҳ��
            if(!$this->view->get('jumpUrl')) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->display(C('TMPL_ACTION_SUCCESS'));
        }else{
            $this->assign('error',$message);// ��ʾ��Ϣ
            //��������ʱ��Ĭ��ͣ��3��
            if(!$this->view->get('waitSecond'))    $this->assign('waitSecond','3');
            // Ĭ�Ϸ�������Ļ��Զ�������ҳ
            if(!$this->view->get('jumpUrl')) $this->assign('jumpUrl',"javascript:history.back(-1);");
            $this->display(C('TMPL_ACTION_ERROR'));
            // ��ִֹ��  �����������ִ��
            //exit ;
        }
    }

   /**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __destruct() {
        // ִ�к�������
        tag('action_end');
    }
}