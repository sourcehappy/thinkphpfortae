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
// $Id: common.php 2799 2012-03-05 07:18:06Z liu21st $

/**
  +------------------------------------------------------------------------------
 * Think ����������
  +------------------------------------------------------------------------------
 * @category   Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id: common.php 2799 2012-03-05 07:18:06Z liu21st $
  +------------------------------------------------------------------------------
 */

// ��¼��ͳ��ʱ�䣨΢�룩
function G($start,$end='',$dec=4) {
    static $_info = array();
    if(is_float($end)) { // ��¼ʱ��
        $_info[$start]  =  $end;
    }elseif(!empty($end)){ // ͳ��ʱ��
        if(!isset($_info[$end])) $_info[$end]   =  microtime(TRUE);
        return number_format(($_info[$end]-$_info[$start]),$dec);
    }else{ // ��¼ʱ��
        $_info[$start]  =  microtime(TRUE);
    }
}

// ���úͻ�ȡͳ������
function N($key, $step=0) {
    static $_num = array();
    if (!isset($_num[$key])) {
        $_num[$key] = 0;
    }
    if (empty($step))
        return $_num[$key];
    else
        $_num[$key] = $_num[$key] + (int) $step;
}

/**
  +----------------------------------------------------------
 * �ַ����������ת��
 * type
 * =0 ��Java���ת��ΪC�ķ��
 * =1 ��C���ת��ΪJava�ķ��
  +----------------------------------------------------------
 * @access protected
  +----------------------------------------------------------
 * @param string $name �ַ���
 * @param integer $type ת������
  +----------------------------------------------------------
 * @return string
  +----------------------------------------------------------
 */
function parse_name($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

// �Ż���require_once
function require_cache($filename) {
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists_case($filename)) {
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}

// ���ִ�Сд���ļ������ж�
function file_exists_case($filename) {
    if (is_file($filename)) {
        if (IS_WIN && C('APP_FILE_CASE')) {
            if (basename(realpath($filename)) != basename($filename))
                return false;
        }
        return true;
    }
    return false;
}

/**
  +----------------------------------------------------------
 * ������������ ͬjava��Import
 * �������л��湦��
  +----------------------------------------------------------
 * @param string $class ��������ռ��ַ���
 * @param string $baseUrl ��ʼ·��
 * @param string $ext ������ļ���չ��
  +----------------------------------------------------------
 * @return boolen
  +----------------------------------------------------------
 */
function import($class, $baseUrl = '', $ext='.class.php') {
    static $_file = array();
    $class = str_replace(array('.', '#'), array('/', '.'), $class);
    if ('' === $baseUrl && false === strpos($class, '/')) {
        // ����������
        return alias_import($class);
    }
    if (isset($_file[$class . $baseUrl]))
        return true;
    else
        $_file[$class . $baseUrl] = true;
    $class_strut = explode('/', $class);
    if (empty($baseUrl)) {
        if ('@' == $class_strut[0] || APP_NAME == $class_strut[0]) {
            //���ص�ǰ��ĿӦ�����
            $baseUrl = dirname(LIB_PATH);
            $class = substr_replace($class, basename(LIB_PATH).'/', 0, strlen($class_strut[0]) + 1);
        }elseif ('think' == strtolower($class_strut[0])){ // think �ٷ������
            $baseUrl = CORE_PATH;
            $class = substr($class,6);
        }elseif (in_array(strtolower($class_strut[0]), array('org', 'com'))) {
            // org ������������� com ��ҵ�������
            $baseUrl = LIBRARY_PATH;
        }else { // ����������ĿӦ�����
            $class = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
            $baseUrl = APP_PATH . '../' . $class_strut[0] . '/'.basename(LIB_PATH).'/';
        }
    }
    if (substr($baseUrl, -1) != '/')
        $baseUrl .= '/';
    $classfile = $baseUrl . $class . $ext;
    if (!class_exists(basename($class),false)) {
        // ����಻���� ��������ļ�
        return require_cache($classfile);
    }
}

/**
  +----------------------------------------------------------
 * ���������ռ䷽ʽ���뺯����
 * load('@.Util.Array')
  +----------------------------------------------------------
 * @param string $name �����������ռ��ַ���
 * @param string $baseUrl ��ʼ·��
 * @param string $ext ������ļ���չ��
  +----------------------------------------------------------
 * @return void
  +----------------------------------------------------------
 */
function load($name, $baseUrl='', $ext='.php') {
    $name = str_replace(array('.', '#'), array('/', '.'), $name);
    if (empty($baseUrl)) {
        if (0 === strpos($name, '@/')) {
            //���ص�ǰ��Ŀ������
            $baseUrl = COMMON_PATH;
            $name = substr($name, 2);
        } else {
            //����ThinkPHP ϵͳ������
            $baseUrl = EXTEND_PATH . 'Function/';
        }
    }
    if (substr($baseUrl, -1) != '/')
        $baseUrl .= '/';
    require_cache($baseUrl . $name . $ext);
}

// ���ٵ��������������
// ���е�������ܵ�����ļ�ͳһ�ŵ� ϵͳ��VendorĿ¼����
// ����Ĭ�϶�����.php��׺����
function vendor($class, $baseUrl = '', $ext='.php') {
    if (empty($baseUrl))
        $baseUrl = VENDOR_PATH;
    return import($class, $baseUrl, $ext);
}

// ���ٶ���͵������
function alias_import($alias, $classfile='') {
    static $_alias = array();
    if (is_string($alias)) {
        if(isset($_alias[$alias])) {
            return require_cache($_alias[$alias]);
        }elseif ('' !== $classfile) {
            // �����������
            $_alias[$alias] = $classfile;
            return;
        }
    }elseif (is_array($alias)) {
        $_alias   =  array_merge($_alias,$alias);
        return;
    }
    return false;
}

/**
  +----------------------------------------------------------
 * D��������ʵ����Model ��ʽ ��Ŀ://����/ģ��
 +----------------------------------------------------------
 * @param string name Model��Դ��ַ
  +----------------------------------------------------------
 * @return Model
  +----------------------------------------------------------
 */
function D($name='') {
    if(empty($name)) return new Model;
    static $_model = array();
    if(isset($_model[$name]))
        return $_model[$name];
    if(strpos($name,'://')) {// ָ����Ŀ
        $name   =  str_replace('://','/Model/',$name);
    }else{
        $name   =  C('DEFAULT_APP').'/Model/'.$name;
    }
    import($name.'Model');
    $class   =   basename($name.'Model');
    if(class_exists($class)) {
        $model = new $class();
    }else {
        $model  = new Model(basename($name));
    }
    $_model[$name]  =  $model;
    return $model;
}

/**
  +----------------------------------------------------------
 * M��������ʵ����һ��û��ģ���ļ���Model
  +----------------------------------------------------------
 * @param string name Model���� ֧��ָ������ģ�� ���� MongoModel:User
 * @param string tablePrefix ��ǰ׺
 * @param mixed $connection ���ݿ�������Ϣ
  +----------------------------------------------------------
 * @return Model
  +----------------------------------------------------------
 */
function M($name='', $tablePrefix='',$connection='') {
    static $_model = array();
    if(strpos($name,':')) {
        list($class,$name)    =  explode(':',$name);
    }else{
        $class   =   'Model';
    }
    if (!isset($_model[$name . '_' . $class]))
        $_model[$name . '_' . $class] = new $class($name,$tablePrefix,$connection);
    return $_model[$name . '_' . $class];
}

/**
  +----------------------------------------------------------
 * A��������ʵ����Action ��ʽ��[��Ŀ://][����/]ģ��
  +----------------------------------------------------------
 * @param string name Action��Դ��ַ
  +----------------------------------------------------------
 * @return Action
  +----------------------------------------------------------
 */
function A($name) {
    static $_action = array();
    if(isset($_action[$name]))
        return $_action[$name];
    if(strpos($name,'://')) {// ָ����Ŀ
        $name   =  str_replace('://','/Action/',$name);
    }else{
        $name   =  '@/Action/'.$name;
    }
    import($name.'Action');
    $class   =   basename($name.'Action');
    if(class_exists($class,false)) {
        $action = new $class();
        $_action[$name]  =  $action;
        return $action;
    }else {
        return false;
    }
}

// Զ�̵���ģ��Ĳ�������
// URL ������ʽ [��Ŀ://][����/]ģ��/���� 
function R($url,$vars=array()) {
    $info =  pathinfo($url);
    $action  =  $info['basename'];
    $module =  $info['dirname'];
    $class = A($module);
    if($class)
        return call_user_func_array(array(&$class,$action),$vars);
    else
        return false;
}

// ��ȡ���������Զ���(�����ִ�Сд)
function L($name=null, $value=null) {
    static $_lang = array();
    // �ղ����������ж���
    if (empty($name))
        return $_lang;
    // �ж����Ի�ȡ(������)
    // ��������,ֱ�ӷ���ȫ��д$name
    if (is_string($name)) {
        $name = strtoupper($name);
        if (is_null($value))
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        $_lang[$name] = $value; // ���Զ���
        return;
    }
    // ��������
    if (is_array($name))
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
    return;
}

// ��ȡ����ֵ
function C($name=null, $value=null) {
    static $_config = array();
    // �޲���ʱ��ȡ����
    if (empty($name))   return $_config;
    // ����ִ�����û�ȡ��ֵ
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : null;
            $_config[$name] = $value;
            return;
        }
        // ��ά�������úͻ�ȡ֧��
        $name = explode('.', $name);
        $name[0]   =  strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // ��������
    if (is_array($name)){
        return $_config = array_merge($_config, array_change_key_case($name));
    }
    return null; // ����Ƿ�����
}

// �����ǩ��չ
function tag($tag, &$params=NULL) {
    // ϵͳ��ǩ��չ
    $extends = C('extends.' . $tag);
    // Ӧ�ñ�ǩ��չ
    $tags = C('tags.' . $tag);
    if (!empty($tags)) {
        if(empty($tags['_overlay']) && !empty($extends)) { // �ϲ���չ
            $tags = array_unique(array_merge($extends,$tags));
        }elseif(isset($tags['_overlay'])){ // ͨ������ '_overlay'=>1 ����ϵͳ��ǩ
            unset($tags['_overlay']);
        }
    }elseif(!empty($extends)) {
        $tags = $extends;
    }
    if($tags) {
        if(APP_DEBUG) {
            G($tag.'Start');
            Log::record('Tag[ '.$tag.' ] --START--',Log::INFO);
        }
        // ִ����չ
        foreach ($tags as $key=>$name) {
            if(!is_int($key)) { // ָ����Ϊ�������·�� ����ģʽ��չ
                $name   = $key;
            }
            B($name, $params);
        }
        if(APP_DEBUG) { // ��¼��Ϊ��ִ����־
            Log::record('Tag[ '.$tag.' ] --END-- [ RunTime:'.G($tag.'Start',$tag.'End',6).'s ]',Log::INFO);
        }
    }else{ // δִ���κ���Ϊ ����false
        return false;
    }
}

// ��̬�����Ϊ��չ��ĳ����ǩ
function add_tag_behavior($tag,$behavior,$path='') {
    $array   =  C('tags.'.$tag);
    if(!$array) {
        $array   =  array();
    }
    if($path) {
        $array[$behavior] = $path;
    }else{
        $array[] =  $behavior;
    }
    C('tags.'.$tag,$array);
}

// ����������
function filter($name, &$content) {
    $class = $name . 'Filter';
    require_cache(LIB_PATH . 'Filter/' . $class . '.class.php');
    $filter = new $class();
    $content = $filter->run($content);
}

// ִ����Ϊ
function B($name, &$params=NULL) {
    $class = $name.'Behavior';
    G('behaviorStart');
    $behavior = new $class();
    $behavior->run($params);
    if(APP_DEBUG) { // ��¼��Ϊ��ִ����־
        G('behaviorEnd');
        Log::record('Run '.$name.' Behavior [ RunTime:'.G('behaviorStart','behaviorEnd',6).'s ]',Log::INFO);
    }
}

// ��Ⱦ���Widget
function W($name, $data=array(), $return=false) {
    $class = $name . 'Widget';
    require_cache(LIB_PATH . 'Widget/' . $class . '.class.php');
    if (!class_exists($class))
        throw_exception(L('_CLASS_NOT_EXIST_') . ':' . $class);
    $widget = Think::instance($class);
    $content = $widget->render($data);
    if ($return)
        return $content;
    else
        echo $content;
}


// ��Ⱦ���Widget
function W($params) {
	$class = $params[0] . 'Widget';
    require_cache(LIB_PATH . 'Widget/' . $class . '.class.php');
    if (!class_exists($class))
        throw_exception(L('_CLASS_NOT_EXIST_') . ':' . $class);
    $widget = Think::instance($class);
    $content = $widget->render($params[1]);
    return $content;
}


// ȥ�������еĿհ׺�ע��
function strip_whitespace($content) {
    $stripStr = '';
    //����phpԴ��
    $tokens = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                //���˸���PHPע��
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //���˿ո�
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<THINK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "THINK;\n";
                    for($k = $i+1; $k < $j; $k++) {
                        if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else if($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

// ѭ������Ŀ¼
function mk_dir($dir, $mode = 0777) {
    if (is_dir($dir) || @mkdir($dir, $mode))
        return true;
    if (!mk_dir(dirname($dir), $mode))
        return false;
    return @mkdir($dir, $mode);
}

//[RUNTIME]
// �����ļ�
function compile($filename) {
    $content = file_get_contents($filename);
    // �滻Ԥ����ָ��
    $content = preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s', '', $content);
    $content = substr(trim($content), 5);
    if ('?>' == substr($content, -2))
        $content = substr($content, 0, -2);
    return $content;
}

// �����������ɳ�������
function array_define($array,$check=true) {
    $content = "\n";
    foreach ($array as $key => $val) {
        $key = strtoupper($key);
        if($check)   $content .= 'defined(\'' . $key . '\') or ';
        if (is_int($val) || is_float($val)) {
            $content .= "define('" . $key . "'," . $val . ');';
        } elseif (is_bool($val)) {
            $val = ($val) ? 'true' : 'false';
            $content .= "define('" . $key . "'," . $val . ');';
        } elseif (is_string($val)) {
            $content .= "define('" . $key . "','" . addslashes($val) . "');";
        }
        $content    .= "\n";
    }
    return $content;
}

if (!function_exists('basename'))
{
	function basename($filepath, $suffix){
	  $filepath=str_replace("\\","/",$filepath);
	  $split = explode('/',$filepath);
	  $count = count($split);
	  $basename='';
	  for($i=$count-1;$i>0;$i--)
	  {
		if ($split[$i] ){
			$basename=$split[$i];
			break;
		}
	  }
	  return str_replace($suffix,'',$basename);
	}
}

if (!function_exists('dirname'))
{
	function dirname($filepath = null) {
	  $filepath=str_replace("\\","/",$filepath);
	  $split = explode('/',$filepath);
	  $count = count($split);
	  for($i=$count-1;$i>0;$i--)
	  {
		if ($split[$i] ){
			$count=$i;
			break;
		}
	  }
	  if ($count==1 && $split[0]=='')
		  return "\\";
	  if ($count==1)
		  return ".";

	  $dirname='';
	  for($i=0;$i<$count;$i++)
		$dirname.=$split[$i]."/";
	  return substr($dirname,0,-1);
	}
}

if (!function_exists('is_file'))
{
	function is_file($file){
		return (bool)md5_file($file);
	}
}

/**
 +----------------------------------------------------------
 * ϵͳ�Զ��������
 * ����֧�������Զ�����·��
 +----------------------------------------------------------
 * @param string $class ��������
 +----------------------------------------------------------
 * @return void
 +----------------------------------------------------------
 */
function __autoload($class) {
	// ����Ƿ���ڱ�������
	if(alias_import($class)) return ;

	if(substr($class,-8)=='Behavior') { // ������Ϊ
		if(require_cache(CORE_PATH.'Behavior/'.$class.'.class.php') 
			|| require_cache(EXTEND_PATH.'Behavior/'.$class.'.class.php') 
			|| require_cache(LIB_PATH.'Behavior/'.$class.'.class.php')
			|| (defined('MODE_NAME') && require_cache(MODE_PATH.ucwords(MODE_NAME).'/Behavior/'.$class.'.class.php'))) {
			return ;
		}
	}elseif(substr($class,-5)=='Model'){ // ����ģ��
		if(require_cache(LIB_PATH.'Model/'.$class.'.class.php')
			|| require_cache(EXTEND_PATH.'Model/'.$class.'.class.php') ) {
			return ;
		}
	}elseif(substr($class,-6)=='Action'){ // ���ؿ�����
		if((defined('GROUP_NAME') && require_cache(LIB_PATH.'Action/'.GROUP_NAME.'/'.$class.'.class.php'))
			|| require_cache(LIB_PATH.'Action/'.$class.'.class.php')
			|| require_cache(EXTEND_PATH.'Action/'.$class.'.class.php') ) {
			return ;
		}
	}

	// �����Զ�����·�����ý��г�������
	$paths  =   explode(',',C('APP_AUTOLOAD_PATH'));
	foreach ($paths as $path){
		if(import($path.'.'.$class))
			// ���������ɹ��򷵻�
			return ;
	}
}

function my_unserialize($serialize_str) {
	return unserialize(str_replace('U:', 's:', $serialize_str ));
} 
//[/RUNTIME]