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
// $Id: WriteHtmlCacheBehavior.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ ��̬����д��
 * �������ò������£�
 +------------------------------------------------------------------------------
 */
class WriteHtmlCacheBehavior extends Behavior {

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$content){
         if(C('HTML_CACHE_ON') && defined('HTML_FILE_NAME') && $content!='')  {
            $file['timestamp']=time();
			$file['content']=$content;
			//��̬�ļ�д��
            // �������HTML���� ��鲢��дHTML�ļ�
            // û��ģ��Ĳ��������ɾ�̬�ļ�
            if( false === F(HTML_FILE_NAME,$file) )
                throw_exception(L('_CACHE_WRITE_ERROR_').':'.HTML_FILE_NAME);
        }
    }
}