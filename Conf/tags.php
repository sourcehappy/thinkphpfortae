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
// $Id: tags.php 2726 2012-02-11 13:34:24Z liu21st $

// ϵͳĬ�ϵĺ�����Ϊ��չ�б��ļ�
return array(
    'app_init'=>array(
    ),
    'app_begin'=>array(
        'CheckTemplate', // ģ����
        'ReadHtmlCache', // ��ȡ��̬����
    ),
    'route_check'=>array(
    ), 
    'app_end'=>array(),
    'path_info'=>array(),
    'action_begin'=>array(),
    'action_end'=>array(),
    'view_begin'=>array(),
    'view_template'=>array(
        'LocationTemplate', // �Զ���λģ���ļ�
    ),
    'view_parse'=>array(
        'ParseTemplate', // ģ����� ֧��PHP������ģ������͵�����ģ������
    ),
    'view_filter'=>array(
        'ContentReplace', // ģ������滻
        'WriteHtmlCache', // д�뾲̬����
        'ShowRuntime', // ����ʱ����ʾ
    ),
    'view_end'=>array(
        'ShowPageTrace', // ҳ��Trace��ʾ
    ),
);