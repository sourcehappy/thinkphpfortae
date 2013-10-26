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
// $Id: ParseTemplateBehavior.class.php 2740 2012-02-17 08:16:42Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ ģ�����
 +------------------------------------------------------------------------------
 */

class ParseTemplateBehavior extends Behavior {
    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$_data){
        $tpl   =  new Smarty();

		if ($_data['var'])
			$tpl->assign($_data['var']);
        $_data['content']=$tpl->fetch($_data['file']);

        $_data['content'] = preg_replace('/\{:(\S+?)\((\S.+?)\)\}/eis',"\$this->parseTag('\\1','\\2')",$_data['content'] );

    }

	public function parseTag($function,$tagStr){
		$paraStr=json_decode(str_replace('\"','"',$tagStr),true);
		if ($paraStr!==false && function_exists($function) )
			$content=$function( $paraStr );
		return $content;
	}
}