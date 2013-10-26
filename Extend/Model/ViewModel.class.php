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
// $Id: ViewModel.class.php 2795 2012-03-02 15:34:18Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP ��ͼģ������չ
 +------------------------------------------------------------------------------
 */
class ViewModel extends Model {

    protected $viewFields = array();

    /**
     +----------------------------------------------------------
     * �Զ�������ݱ���Ϣ
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function _checkTableInfo() {}

    /**
     +----------------------------------------------------------
     * �õ����������ݱ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getTableName() {
        if(empty($this->trueTableName)) {
            $tableName = '';
            foreach ($this->viewFields as $key=>$view){
                // ��ȡ���ݱ�����
                if(isset($view['_table'])) { // 2011/10/17 ���ʵ�ʱ�������֧�� ����ʵ��ͬһ�������ͼ
                    $tableName .= $view['_table'];
                }else{
                    $class  =   $key.'Model';
                    $Model  =  class_exists($class)?new $class():M($key);
                    $tableName .= $Model->getTableName();
                }
                // ���������
                $tableName .= !empty($view['_as'])?' '.$view['_as']:' '.$key;
                // ֧��ON ��������
                $tableName .= !empty($view['_on'])?' ON '.$view['_on']:'';
                // ָ��JOIN���� ���� RIGHT INNER LEFT ��һ������Ч
                $type = !empty($view['_type'])?$view['_type']:'';
                $tableName   .= ' '.strtoupper($type).' JOIN ';
                $len  =  strlen($type.'_JOIN ');
            }
            $tableName = substr($tableName,0,-$len);
            $this->trueTableName    =   $tableName;
        }
        return $this->trueTableName;
    }

    /**
     +----------------------------------------------------------
     * ���ʽ���˷���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $options ���ʽ
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function _options_filter(&$options) {
        if(isset($options['field']))
            $options['field'] = $this->checkFields($options['field']);
        else
            $options['field'] = $this->checkFields();
        if(isset($options['group']))
            $options['group']  =  $this->checkGroup($options['group']);
        if(isset($options['where']))
            $options['where']  =  $this->checkCondition($options['where']);
        if(isset($options['order']))
            $options['order']  =  $this->checkOrder($options['order']);
    }

    /**
     +----------------------------------------------------------
     * ����Ƿ����������ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $name ģ������
     * @param array $fields �ֶ�����
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    private function _checkFields($name,$fields) {
        if(false !== $pos = array_search('*',$fields)) {// ���������ֶ�
            $fields  =  array_merge($fields,M($name)->getDbFields());
            unset($fields[$pos]);
        }
        return $fields;
    }

    /**
     +----------------------------------------------------------
     * ��������е���ͼ�ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data �������ʽ
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function checkCondition($where) {
        if(is_array($where)) {
            $view   =   array();
            // �����ͼ�ֶ�
            foreach ($this->viewFields as $key=>$val){
                $k = isset($val['_as'])?$val['_as']:$key;
                $val  =  $this->_checkFields($key,$val);
                foreach ($where as $name=>$value){
                    if(false !== $field = array_search($name,$val,true)) {
                        // ������ͼ�ֶ�
                        $_key   =   is_numeric($field)?    $k.'.'.$name   :   $k.'.'.$field;
                        $view[$_key]    =   $value;
                        unset($where[$name]);
                    }
                }
            }
            $where    =   array_merge($where,$view);
         }
        return $where;
    }

    /**
     +----------------------------------------------------------
     * ���Order���ʽ�е���ͼ�ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $order �ֶ�
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function checkOrder($order='') {
         if(is_string($order) && !empty($order)) {
            $orders = explode(',',$order);
            $_order = array();
            foreach ($orders as $order){
                $array = explode(' ',$order);
                $field   =   $array[0];
                $sort   =   isset($array[1])?$array[1]:'ASC';
                // ��������ͼ�ֶ�
                foreach ($this->viewFields as $name=>$val){
                    $k = isset($val['_as'])?$val['_as']:$name;
                    $val  =  $this->_checkFields($name,$val);
                    if(false !== $_field = array_search($field,$val,true)) {
                        // ������ͼ�ֶ�
                        $field     =  is_numeric($_field)?$k.'.'.$field:$k.'.'.$_field;
                        break;
                    }
                }
                $_order[] = $field.' '.$sort;
            }
            $order = implode(',',$_order);
         }
        return $order;
    }

    /**
     +----------------------------------------------------------
     * ���Group���ʽ�е���ͼ�ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $group �ֶ�
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function checkGroup($group='') {
         if(!empty($group)) {
            $groups = explode(',',$group);
            $_group = array();
            foreach ($groups as $field){
                // ��������ͼ�ֶ�
                foreach ($this->viewFields as $name=>$val){
                    $k = isset($val['_as'])?$val['_as']:$name;
                    $val  =  $this->_checkFields($name,$val);
                    if(false !== $_field = array_search($field,$val,true)) {
                        // ������ͼ�ֶ�
                        $field     =  is_numeric($_field)?$k.'.'.$field:$k.'.'.$_field;
                        break;
                    }
                }
                $_group[] = $field;
            }
            $group  =   implode(',',$_group);
         }
        return $group;
    }

    /**
     +----------------------------------------------------------
     * ���fields���ʽ�е���ͼ�ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $fields �ֶ�
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function checkFields($fields='') {
        if(empty($fields) || '*'==$fields ) {
            // ��ȡȫ����ͼ�ֶ�
            $fields =   array();
            foreach ($this->viewFields as $name=>$val){
                $k = isset($val['_as'])?$val['_as']:$name;
                $val  =  $this->_checkFields($name,$val);
                foreach ($val as $key=>$field){
                    if(is_numeric($key)) {
                        $fields[]    =   $k.'.'.$field.' AS '.$field;
                    }elseif('_' != substr($key,0,1)) {
                        // ��_��ͷ��Ϊ���ⶨ��
                        if( false !== strpos($key,'*') ||  false !== strpos($key,'(') || false !== strpos($key,'.')) {
                            //�������* ���� ʹ����sql���� �������ǰ��ı���
                            $fields[]    =   $key.' AS '.$field;
                        }else{
                            $fields[]    =   $k.'.'.$key.' AS '.$field;
                        }
                    }
                }
            }
            $fields = implode(',',$fields);
        }else{
            if(!is_array($fields))
                $fields =   explode(',',$fields);
            // ��������ͼ�ֶ�
            $array =  array();
            foreach ($fields as $key=>$field){
                if(strpos($field,'(') || strpos(strtolower($field),' as ')){
                    // ʹ���˺������߱���
                    $array[] =  $field;
                    unset($fields[$key]);
                }
            }
            foreach ($this->viewFields as $name=>$val){
                $k = isset($val['_as'])?$val['_as']:$name;
                $val  =  $this->_checkFields($name,$val);
                foreach ($fields as $key=>$field){
                    if(false !== $_field = array_search($field,$val,true)) {
                        // ������ͼ�ֶ�
                        if(is_numeric($_field)) {
                            $array[]    =   $k.'.'.$field.' AS '.$field;
                        }elseif('_' != substr($_field,0,1)){
                            if( false !== strpos($_field,'*') ||  false !== strpos($_field,'(') || false !== strpos($_field,'.'))
                                //�������* ���� ʹ����sql���� �������ǰ��ı���
                                $array[]    =   $_field.' AS '.$field;
                            else
                                $array[]    =   $k.'.'.$_field.' AS '.$field;
                        }
                    }
                }
            }
            $fields = implode(',',$array);
        }
        return $fields;
    }
}