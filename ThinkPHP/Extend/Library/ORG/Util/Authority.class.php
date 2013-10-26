<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: luofei614 <www.3g4k.com>��
// +----------------------------------------------------------------------
// $Id: Authority.class.php 2504 2011-12-28 07:35:29Z liu21st $
/**
 * Ȩ����֤��
 * �������ԣ�
 * 1���ǶԹ��������֤�����ǶԽڵ������֤���û����԰ѽڵ㵱����������ʵ�ֶԽڵ������֤��
 *      $auth=new Authority();  $auth->getAuth('��������','�û�id')
 * 2������ͬʱ�Զ������������֤�������ö�������Ĺ�ϵ��or����and��
 *      $auth=new Authority();  $auth->getAuth('����1,����2','�û�id','and') 
 *      ����������Ϊandʱ��ʾ���û���Ҫͬʱ���й���1�͹���2��Ȩ�ޡ� ������������Ϊorʱ����ʾ�û�ֵ��Ҫ�߱�����һ���������ɡ�Ĭ��Ϊor
 * 3��һ���û��������ڶ���û���(think_auth_group_access�� �������û������û���)��������Ҫ����ÿ���û���ӵ����Щ����(think_auth_group �������û���Ȩ��)
 * 
 * 4��֧�ֹ�����ʽ��
 *      ��think_auth_rule ���ж���һ������ʱ�����typeΪ1�� condition�ֶξͿ��Զ��������ʽ�� �綨��{score}>5  and {score}<100  ��ʾ�û��ķ�����5-100֮��ʱ��������Ż�ͨ����
 * @category ORG
 * @package ORG
 * @subpackage Util
 * @author luofei614<www.3g4k.com>
 */

//���ݿ�
/*
-- ----------------------------
-- think_auth_rule�������
-- id:������name������Ψһ��ʶ, title�������������� type:���ͣ�0���ڹ����ͨ����1��������ʱ������֤����condition��������ʽ
-- ----------------------------
 DROP TABLE IF EXISTS `think_auth_rule`;
CREATE TABLE `think_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(10) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `condition` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8; 
-- ----------------------------
-- think_auth_group �û���� 
-- id�������� title:�û����������ƣ� rules���û���ӵ�еĹ���id�� ��������á�,������
-- ----------------------------
 DROP TABLE IF EXISTS `think_auth_group`;
CREATE TABLE `think_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `rules` char(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
-- ----------------------------
-- think_auth_group_access �û�����ϸ��
-- uid:�û�id��group_id���û���id
-- ----------------------------
DROP TABLE IF EXISTS `think_auth_group_access`;
CREATE TABLE `think_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_2` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

 */

class Authority {

    //Ĭ������
    protected $_config = array(
        'AUTH_ON' => true, //��֤����
        'AUTH_TYPE' => 1, // ��֤��ʽ��1Ϊʱʱ��֤��2Ϊ��¼��֤��
        'AUTH_GROUP' => 'think_auth_group', //�û������ݱ���
        'AUTH_GROUP_ACCESS' => 'think_auth_group_access', //�û�����ϸ��
        'AUTH_RULE' => 'think_auth_rule', //Ȩ�޹����
        'AUTH_USER' => 'think_members'//�û���Ϣ��
    );

    public function __construct() {
        if (C('AUTH_CONFIG')) {
            //������������ AUTH_CONFIG, ��������Ϊ���顣
            $this->_config = array_merge($this->_config, C('AUTH_CONFIG'));
        }
    }

    //���Ȩ��$name �������ַ���������򶺺ŷָ uidΪ ��֤���û�id�� $or �Ƿ�Ϊor��ϵ��Ϊtrue�ǣ� nameΪ���飬ֻҪ��������һ������ͨ����ͨ�������Ϊfalse��Ҫȫ������ͨ����
    public function getAuth($name, $uid, $relation='or') {
        if (!$this->_config['AUTH_ON'])
            return true;
        $authList = $this->getAuthList($uid);
        if (is_string($name)) {
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }
        $list = array(); //��Ȩ�޵�name
        foreach ($authList as $val) {
            if (in_array($val, $name))
                $list[] = $val;
        }
        if ($relation=='or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation=='and' and empty($diff)) {
            return true;
        }
        return false;
    }

    //����û��飬�ⲿҲ���Ե���
    public function getGroups($uid) {
        static $groups = array();
        if (!empty($groups[$uid]))
            return $groups[$uid];
        $groups[$uid] = M()->table($this->_config['AUTH_GROUP_ACCESS'] . ' a')->where("a.uid='$uid'")->join($this->_config['AUTH_GROUP']." g on a.group_id=g.id")->select();
        return $groups[$uid];
    }

    //���Ȩ���б�
    protected function getAuthList($uid) {
        static $_authList = array();
        if (isset($_authList[$uid])) {
            return $_authList[$uid];
        }
        if(isset($_SESSION['_AUTH_LIST_'.$uid])){
            return $_SESSION['_AUTH_LIST_'.$uid];
        }
        //��ȡ�û������û���
        $groups = $this->getGroups($uid);
        $ids = array();
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid] = array();
            return array();
        }
        //��ȡ�û�������Ȩ�޹���(in)
        $map['id'] = array('in', $ids);
        $rules = M()->table($this->_config['AUTH_RULE'])->where($map)->select();
        //ѭ�������жϽ����
        $authList = array();
        foreach ($rules as $r) {
            if ($r['type'] == 1) {
                //������֤
                $user = $this->getUserInfo($uid);
                $command = preg_replace('/\{(\w*?)\}/e', '$user[\'\\1\']', $r['condition']);
                //dump($command);//debug
                @(eval('$condition=(' . $command . ');'));
                if ($condition) {
                    $authList[] = $r['name'];
                }
            } else {
                //���ھ�ͨ��
                $authList[] = $r['name'];
            }
        }
        $_authList[$uid] = $authList;
        if($this->_config['AUTH_TYPE']==2){
            //session���
            $_SESSION['_AUTH_LIST_'.$uid]=$authList;
        }
        return $authList;
    }
    //����û�����,�����Լ��������ȡ���ݿ�
    protected function getUserInfo($uid) {
        return M()->table($this->_config['AUTH_USER'])->find($uid);
    }

}