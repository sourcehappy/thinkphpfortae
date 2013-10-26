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
// $Id: RBAC.class.php 2504 2011-12-28 07:35:29Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ���ڽ�ɫ�����ݿⷽʽ��֤��
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: RBAC.class.php 2504 2011-12-28 07:35:29Z liu21st $
 +------------------------------------------------------------------------------
 */
// �����ļ���������
// USER_AUTH_ON �Ƿ���Ҫ��֤
// USER_AUTH_TYPE ��֤����
// USER_AUTH_KEY ��֤ʶ���
// REQUIRE_AUTH_MODULE  ��Ҫ��֤ģ��
// NOT_AUTH_MODULE ������֤ģ��
// USER_AUTH_GATEWAY ��֤����
// RBAC_DB_DSN  ���ݿ�����DSN
// RBAC_ROLE_TABLE ��ɫ������
// RBAC_USER_TABLE �û�������
// RBAC_ACCESS_TABLE Ȩ�ޱ�����
// RBAC_NODE_TABLE �ڵ������
/*
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `think_access` (
  `role_id` smallint(6) unsigned NOT NULL,
  `node_id` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `think_node` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `sort` smallint(6) unsigned DEFAULT NULL,
  `pid` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `pid` (`pid`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `think_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `pid` smallint(6) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `think_role_user` (
  `role_id` mediumint(9) unsigned DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/
class RBAC {
    // ��֤����
    static public function authenticate($map,$model='')
    {
        if(empty($model)) $model =  C('USER_AUTH_MODEL');
        //ʹ�ø�����Map������֤
        return M($model)->where($map)->find();
    }

    //���ڼ���û�Ȩ�޵ķ���,�����浽Session��
    static function saveAccessList($authId=null)
    {
        if(null===$authId)   $authId = $_SESSION[C('USER_AUTH_KEY')];
        // ���ʹ����ͨȨ��ģʽ�����浱ǰ�û��ķ���Ȩ���б�
        // �Թ���Ա��������Ȩ��
        if(C('USER_AUTH_TYPE') !=2 && !$_SESSION[C('ADMIN_AUTH_KEY')] )
            $_SESSION['_ACCESS_LIST']	=	RBAC::getAccessList($authId);
        return ;
    }

	// ȡ��ģ���������¼����Ȩ���б� ������Ȩ�޵ļ�¼ID����
	static function getRecordAccessList($authId=null,$module='') {
        if(null===$authId)   $authId = $_SESSION[C('USER_AUTH_KEY')];
        if(empty($module))  $module	=	MODULE_NAME;
        //��ȡȨ�޷����б�
        $accessList = RBAC::getModuleAccessList($authId,$module);
        return $accessList;
	}

    //��鵱ǰ�����Ƿ���Ҫ��֤
    static function checkAccess()
    {
        //�����ĿҪ����֤�����ҵ�ǰģ����Ҫ��֤�������Ȩ����֤
        if( C('USER_AUTH_ON') ){
			$_module	=	array();
			$_action	=	array();
            if("" != C('REQUIRE_AUTH_MODULE')) {
                //��Ҫ��֤��ģ��
                $_module['yes'] = explode(',',strtoupper(C('REQUIRE_AUTH_MODULE')));
            }else {
                //������֤��ģ��
                $_module['no'] = explode(',',strtoupper(C('NOT_AUTH_MODULE')));
            }
            //��鵱ǰģ���Ƿ���Ҫ��֤
            if((!empty($_module['no']) && !in_array(strtoupper(MODULE_NAME),$_module['no'])) || (!empty($_module['yes']) && in_array(strtoupper(MODULE_NAME),$_module['yes']))) {
				if("" != C('REQUIRE_AUTH_ACTION')) {
					//��Ҫ��֤�Ĳ���
					$_action['yes'] = explode(',',strtoupper(C('REQUIRE_AUTH_ACTION')));
				}else {
					//������֤�Ĳ���
					$_action['no'] = explode(',',strtoupper(C('NOT_AUTH_ACTION')));
				}
				//��鵱ǰ�����Ƿ���Ҫ��֤
				if((!empty($_action['no']) && !in_array(strtoupper(ACTION_NAME),$_action['no'])) || (!empty($_action['yes']) && in_array(strtoupper(ACTION_NAME),$_action['yes']))) {
					return true;
				}else {
					return false;
				}
            }else {
                return false;
            }
        }
        return false;
    }

	// ��¼���
	static public function checkLogin() {
        //��鵱ǰ�����Ƿ���Ҫ��֤
        if(RBAC::checkAccess()) {
            //�����֤ʶ���
            if(!$_SESSION[C('USER_AUTH_KEY')]) {
                if(C('GUEST_AUTH_ON')) {
                    // �����ο���Ȩ����
                    if(!isset($_SESSION['_ACCESS_LIST']))
                        // �����ο�Ȩ��
                        RBAC::saveAccessList(C('GUEST_AUTH_ID'));
                }else{
                    // ��ֹ�οͷ�����ת����֤����
                    redirect(PHP_FILE.C('USER_AUTH_GATEWAY'));
                }
            }
        }
        return true;
	}

    //Ȩ����֤�Ĺ���������
    static public function AccessDecision($appName=APP_NAME)
    {
        //����Ƿ���Ҫ��֤
        if(RBAC::checkAccess()) {
            //������֤ʶ��ţ�����н�һ���ķ��ʾ���
            $accessGuid   =   md5($appName.MODULE_NAME.ACTION_NAME);
            if(empty($_SESSION[C('ADMIN_AUTH_KEY')])) {
                if(C('USER_AUTH_TYPE')==2) {
                    //��ǿ��֤�ͼ�ʱ��֤ģʽ ���Ӱ�ȫ ��̨Ȩ���޸Ŀ��Լ�ʱ��Ч
                    //ͨ�����ݿ���з��ʼ��
                    $accessList = RBAC::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
                }else {
                    // ����ǹ���Ա���ߵ�ǰ�����Ѿ���֤���������ٴ���֤
                    if( $_SESSION[$accessGuid]) {
                        return true;
                    }
                    //��¼��֤ģʽ���Ƚϵ�¼�󱣴��Ȩ�޷����б�
                    $accessList = $_SESSION['_ACCESS_LIST'];
                }
                //�ж��Ƿ�Ϊ�����ģʽ������ǣ���֤��ȫģ����
                $module = defined('P_MODULE_NAME')?  P_MODULE_NAME   :   MODULE_NAME;
                if(!isset($accessList[strtoupper($appName)][strtoupper($module)][strtoupper(ACTION_NAME)])) {
                    $_SESSION[$accessGuid]  =   false;
                    return false;
                }
                else {
                    $_SESSION[$accessGuid]	=	true;
                }
            }else{
                //����Ա������֤
				return true;
			}
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * ȡ�õ�ǰ��֤�ŵ�����Ȩ���б�
     +----------------------------------------------------------
     * @param integer $authId �û�ID
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    static public function getAccessList($authId)
    {
        // Db��ʽȨ������
        $db     =   Db::getInstance(C('RBAC_DB_DSN'));
        $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
        $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=1 and node.status=1";
        $apps =   $db->query($sql);
        $access =  array();
        foreach($apps as $key=>$app) {
            $appId	=	$app['id'];
            $appName	 =	 $app['name'];
            // ��ȡ��Ŀ��ģ��Ȩ��
            $access[strtoupper($appName)]   =  array();
            $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=2 and node.pid={$appId} and node.status=1";
            $modules =   $db->query($sql);
            // �ж��Ƿ���ڹ���ģ���Ȩ��
            $publicAction  = array();
            foreach($modules as $key=>$module) {
                $moduleId	 =	 $module['id'];
                $moduleName = $module['name'];
                if('PUBLIC'== strtoupper($moduleName)) {
                $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=3 and node.pid={$moduleId} and node.status=1";
                    $rs =   $db->query($sql);
                    foreach ($rs as $a){
                        $publicAction[$a['name']]	 =	 $a['id'];
                    }
                    unset($modules[$key]);
                    break;
                }
            }
            // ���ζ�ȡģ��Ĳ���Ȩ��
            foreach($modules as $key=>$module) {
                $moduleId	 =	 $module['id'];
                $moduleName = $module['name'];
                $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=3 and node.pid={$moduleId} and node.status=1";
                $rs =   $db->query($sql);
                $action = array();
                foreach ($rs as $a){
                    $action[$a['name']]	 =	 $a['id'];
                }
                // �͹���ģ��Ĳ���Ȩ�޺ϲ�
                $action += $publicAction;
                $access[strtoupper($appName)][strtoupper($moduleName)]   =  array_change_key_case($action,CASE_UPPER);
            }
        }
        return $access;
    }

	// ��ȡģ�������ļ�¼����Ȩ��
	static public function getModuleAccessList($authId,$module) {
        // Db��ʽ
        $db     =   Db::getInstance(C('RBAC_DB_DSN'));
        $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'));
        $sql    =   "select access.node_id from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and  access.module='{$module}' and access.status=1";
        $rs =   $db->query($sql);
        $access	=	array();
        foreach ($rs as $node){
            $access[]	=	$node['node_id'];
        }
		return $access;
	}
}