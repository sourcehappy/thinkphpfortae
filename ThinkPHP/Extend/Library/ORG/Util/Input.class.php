<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: Input.class.php 2528 2012-01-03 14:58:50Z liu21st $

/** �������ݹ�����
 * ʹ�÷���
 *  $Input = Input::getInstance();
 *  $Input->get('name','md5','0');
 *  $Input->session('memberId','','0');
 *
 * �����ܽ���һЩ���õ����ݴ����������·������迼��magic_quotes_gpc�����á�
 *
 * ��ȡ���ݣ�
 *    �����$_POST����$_GET�л�ȡ��ʹ��Input::getVar($_POST['field']);�������ݿ�����ļ��Ͳ���Ҫ�ˡ�
 *    ����ֱ��ʹ�� Input::magicQuotes���������е�magic_quotes_gpcת�塣
 *
 * �洢���̣�
 *    ����Input::getVar($_POST['field'])��õ����ݣ����Ǹɾ������ݣ�����ֱ�ӱ��档
 *    ���Ҫ����Σ�յ�html������ʹ�� $html = Input::safeHtml($data);
 *
 * ҳ����ʾ��
 *    ���ı���ʾ����ҳ�У������±���<title>$data</title>�� $data = Input::forShow($field);
 *    HTML ����ҳ����ʾ�����������ݣ����账��
 *    ����ҳ����Դ���뷽ʽ��ʾhtml��$vo = Input::forShow($html);
 *    ���ı�����HTML��textarea�н��б༭: $vo = Input::forTarea($value);
 *    html�ڱ�ǩ��ʹ�ã���<input value="����" /> ��ʹ�� $vo = Input::forTag($value); ���� $vo = Input::hsc($value);
 *
 * ����ʹ�������
 *    �ַ���Ҫ�����ݿ���������� $data = Input::forSearch($field);
 */
class Input {

    private $filter =   null;   // �������
    private static $_input  =   array('get','post','request','env','server','cookie','session','globals','config','lang','call');
    //html��ǩ����
    public static $htmlTags = array(
        'allow' => 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a',
        'ban' => 'html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml',
    );

    static public function getInstance() {
        return get_instance_of(__CLASS__);
    }

    /**
     +----------------------------------------------------------
     * ħ������ �в����ڵĲ�����ʱ��ִ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $type ������������
     * @param array $args ���� array(key,filter,default)
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($type,$args=array()) {
        $type    =   strtolower(trim($type));
        if(in_array($type,self::$_input,true)) {
            switch($type) {
                case 'get':      $input      =& $_GET;break;
                case 'post':     $input      =& $_POST;break;
                case 'request': $input      =& $_REQUEST;break;
                case 'env':      $input      =& $_ENV;break;
                case 'server':   $input      =& $_SERVER;break;
                case 'cookie':   $input      =& $_COOKIE;break;
                case 'session':  $input      =& $_SESSION;break;
                case 'globals':   $input      =& $GLOBALS;break;
                case 'files':      $input      =& $_FILES;break;
                case 'call':       $input      =   'call';break;
                case 'config':    $input      =   C();break;
                case 'lang':      $input      =   L();break;
                default:return NULL;
            }
            if('call' === $input) {
                // ����������ʽ����������
                $callback    =   array_shift($args);
                $params  =   array_shift($args);
                $data    =   call_user_func_array($callback,$params);
                if(count($args)===0) {
                    return $data;
                }
                $filter =   isset($args[0])?$args[0]:$this->filter;
                if(!empty($filter)) {
                    $data    =   call_user_func_array($filter,$data);
                }
            }else{
                if(0==count($args) || empty($args[0]) ) {
                    return $input;
                }elseif(array_key_exists($args[0],$input)) {
                    // ϵͳ����
                    $data	 =	 $input[$args[0]];
                    $filter	=	isset($args[1])?$args[1]:$this->filter;
                    if(!empty($filter)) {
                        $data	 =	 call_user_func_array($filter,$data);
                    }
                }else{
                    // ������ָ������
                    $data	 =	 isset($args[2])?$args[2]:NULL;
                }
            }
            return $data;
        }
    }

    /**
     +----------------------------------------------------------
     * �������ݹ��˷���
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @param mixed $filter ���˷���
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function filter($filter) {
        $this->filter   =   $filter;
        return $this;
    }

    /**
     +----------------------------------------------------------
     * �ַ�MagicQuoteת�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function noGPC() {
        if ( get_magic_quotes_gpc() ) {
           $_POST = stripslashes_deep($_POST);
           $_GET = stripslashes_deep($_GET);
           $_COOKIE = stripslashes_deep($_COOKIE);
           $_REQUEST= stripslashes_deep($_REQUEST);
        }
    }

    /**
     +----------------------------------------------------------
     * �����ַ������Ա����������������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function forSearch($string) {
        return str_replace( array('%','_'), array('\%','\_'), $string );
    }

    /**
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function forShow($string) {
        return self::nl2Br( self::hsc($string) );
    }

    /**
     +----------------------------------------------------------
     * �����ı����ݣ��Ա���textarea��ǩ����ʾ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function forTarea($string) {
        return str_ireplace(array('<textarea>','</textarea>'), array('&lt;textarea>','&lt;/textarea>'), $string);
    }

    /**
     +----------------------------------------------------------
     * �������еĵ����ź�˫���Ž���ת��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $text Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function forTag($string) {
        return str_replace(array('"',"'"), array('&quot;','&#039;'), $string);
    }

    /**
     +----------------------------------------------------------
     * ת�������еĳ�����Ϊ�ɵ������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function makeLink($string) {
        $validChars = "a-z0-9\/\-_+=.~!%@?#&;:$\|";
        $patterns = array(
                        "/(^|[^]_a-z0-9-=\"'\/])([a-z]+?):\/\/([{$validChars}]+)/ei",
                        "/(^|[^]_a-z0-9-=\"'\/])www\.([a-z0-9\-]+)\.([{$validChars}]+)/ei",
                        "/(^|[^]_a-z0-9-=\"'\/])ftp\.([a-z0-9\-]+)\.([{$validChars}]+)/ei",
                        "/(^|[^]_a-z0-9-=\"'\/:\.])([a-z0-9\-_\.]+?)@([{$validChars}]+)/ei");
        $replacements = array(
                        "'\\1<a href=\"\\2://\\3\" title=\"\\2://\\3\" rel=\"external\">\\2://'.Input::truncate( '\\3' ).'</a>'",
                        "'\\1<a href=\"http://www.\\2.\\3\" title=\"www.\\2.\\3\" rel=\"external\">'.Input::truncate( 'www.\\2.\\3' ).'</a>'",
                        "'\\1<a href=\"ftp://ftp.\\2.\\3\" title=\"ftp.\\2.\\3\" rel=\"external\">'.Input::truncate( 'ftp.\\2.\\3' ).'</a>'",
                        "'\\1<a href=\"mailto:\\2@\\3\" title=\"\\2@\\3\">'.Input::truncate( '\\2@\\3' ).'</a>'");
        return preg_replace($patterns, $replacements, $string);
    }

    /**
     +----------------------------------------------------------
     * ������ʾ�ַ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     * @param int $length ����֮��ĳ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function truncate($string, $length = '50') {
        if ( empty($string) || empty($length) || strlen($string) < $length ) return $string;
        $len = floor( $length / 2 );
        $ret = substr($string, 0, $len) . " ... ". substr($string, 5 - $len);
        return $ret;
    }

    /**
     +----------------------------------------------------------
     * �ѻ���ת��Ϊ<br />��ǩ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function nl2Br($string) {
        return preg_replace("/(\015\012)|(\015)|(\012)/", "<br />", $string);
    }

    /**
     +----------------------------------------------------------
     * ��� magic_quotes_gpc Ϊ�ر�״̬�������������ת���ַ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function addSlashes($string) {
        if (!get_magic_quotes_gpc()) {
            $string = addslashes($string);
        }
        return $string;
    }

    /**
     +----------------------------------------------------------
     * ��$_POST��$_GET��$_COOKIE��$_REQUEST�������л������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function getVar($string) {
        return Input::stripSlashes($string);
    }

    /**
     +----------------------------------------------------------
     * ��� magic_quotes_gpc Ϊ����״̬������������Է�ת���ַ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function stripSlashes($string) {
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }
        return $string;
    }

    /**
     +----------------------------------------------------------
     * ������textbox������ʾhtml����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static function hsc($string) {
        return preg_replace(array("/&amp;/i", "/&nbsp;/i"), array('&', '&amp;nbsp;'), htmlspecialchars($string, ENT_QUOTES));
    }

    /**
     +----------------------------------------------------------
     * ��hsc()�����������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $text Ҫ������ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static function undoHsc($text) {
        return preg_replace(array("/&gt;/i", "/&lt;/i", "/&quot;/i", "/&#039;/i", '/&amp;nbsp;/i'), array(">", "<", "\"", "'", "&nbsp;"), $text);
    }

    /**
     +----------------------------------------------------------
     * �����ȫ��html�����ڹ���Σ�մ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $text Ҫ������ַ���
     * @param mixed $allowTags ����ı�ǩ�б��� table|td|th|td
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function safeHtml($text, $allowTags = null) {
        $text =  trim($text);
        //��ȫ����ע��
        $text = preg_replace('/<!--?.*-->/','',$text);
        //��ȫ���˶�̬����
        $text =  preg_replace('/<\?|\?'.'>/','',$text);
        //��ȫ����js
        $text = preg_replace('/<script?.*\/script>/','',$text);

        $text =  str_replace('[','&#091;',$text);
        $text = str_replace(']','&#093;',$text);
        $text =  str_replace('|','&#124;',$text);
        //���˻��з�
        $text = preg_replace('/\r?\n/','',$text);
        //br
        $text =  preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
        $text = preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
        //����Σ�յ����ԣ��磺����on�¼�lang js
        while(preg_match('/(<[^><]+)(lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1],$text);
        }
        while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1].$mat[3],$text);
        }
        if( empty($allowTags) ) { $allowTags = self::$htmlTags['allow']; }
        //�����HTML��ǩ
        $text =  preg_replace('/<('.$allowTags.')( [^><\[\]]*)>/i','[\1\2]',$text);
        //���˶���html
        if ( empty($banTag) ) { $banTag = self::$htmlTags['ban']; }
        $text =  preg_replace('/<\/?('.$banTag.')[^><]*>/i','',$text);
        //���˺Ϸ���html��ǩ
        while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
            $text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
        }
        //ת������
        while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1].'|'.$mat[3].'|'.$mat[4],$text);
        }
        //������ת��
        $text =  str_replace('\'\'','||',$text);
        $text = str_replace('""','||',$text);
        //���˴���ĵ�������
        while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
            $text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
        }
        //ת���������в��Ϸ��� < >
        $text =  str_replace('<','&lt;',$text);
        $text = str_replace('>','&gt;',$text);
        $text = str_replace('"','&quot;',$text);
        //��ת��
        $text =  str_replace('[','<',$text);
        $text =  str_replace(']','>',$text);
        $text =  str_replace('|','"',$text);
        //���˶���ո�
        $text =  str_replace('  ',' ',$text);
        return $text;
    }

    /**
     +----------------------------------------------------------
     * ɾ��html��ǩ���õ����ı������Դ���Ƕ�׵ı�ǩ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ�����html
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function deleteHtmlTags($string) {
        while(strstr($string, '>')) {
            $currentBeg = strpos($string, '<');
            $currentEnd = strpos($string, '>');
            $tmpStringBeg = @substr($string, 0, $currentBeg);
            $tmpStringEnd = @substr($string, $currentEnd + 1, strlen($string));
            $string = $tmpStringBeg.$tmpStringEnd;
        }
        return $string;
    }

    /**
     +----------------------------------------------------------
     * �����ı��еĻ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string Ҫ������ַ���
     * @param mixed $br �Ի��еĴ���
     *        false��ȥ�����У�true������ԭ����string���滻��string
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function nl2($string, $br = '<br />') {
        if ($br == false) {
            $string = preg_replace("/(\015\012)|(\015)|(\012)/", '', $string);
        } elseif ($br != true){
            $string = preg_replace("/(\015\012)|(\015)|(\012)/", $br, $string);
        }
        return $string;
    }
}