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
// $Id: RelationModel.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP ����ģ������չ
 +------------------------------------------------------------------------------
 */
define('HAS_ONE',1);
define('BELONGS_TO',2);
define('HAS_MANY',3);
define('MANY_TO_MANY',4);

class RelationModel extends Model {
    // ��������
    protected    $_link = array();

    /**
     +----------------------------------------------------------
     * ��̬����ʵ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method ��������
     * @param array $args ���ò���
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {
        if(strtolower(substr($method,0,8))=='relation'){
            $type    =   strtoupper(substr($method,8));
            if(in_array($type,array('ADD','SAVE','DEL'),true)) {
                array_unshift($args,$type);
                return call_user_func_array(array(&$this, 'opRelation'), $args);
            }
        }else{
            return parent::__call($method,$args);
        }
    }

    /**
     +----------------------------------------------------------
     * �õ����������ݱ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getRelationTableName($relation) {
        $relationTable  = !empty($this->tablePrefix) ? $this->tablePrefix : '';
        $relationTable .= $this->tableName?$this->tableName:$this->name;
        $relationTable .= '_'.$relation->getModelName();
        return strtolower($relationTable);
    }

    // ��ѯ�ɹ���Ļص�����
    protected function _after_find(&$result,$options) {
        // ��ȡ�������� �����ӵ������
        if(!empty($options['link']))
            $this->getRelation($result,$options['link']);
    }

    // ��ѯ���ݼ��ɹ���Ļص�����
    protected function _after_select(&$result,$options) {
        // ��ȡ�������� �����ӵ������
        if(!empty($options['link']))
            $this->getRelations($result,$options['link']);
    }

    // д��ɹ���Ļص�����
    protected function _after_insert($data,$options) {
        // ����д��
        if(!empty($options['link']))
            $this->opRelation('ADD',$data,$options['link']);
    }

    // ���³ɹ���Ļص�����
    protected function _after_update($data,$options) {
        // ��������
        if(!empty($options['link']))
            $this->opRelation('SAVE',$data,$options['link']);
    }

    // ɾ���ɹ���Ļص�����
    protected function _after_delete($data,$options) {
        // ����ɾ��
        if(!empty($options['link']))
            $this->opRelation('DEL',$data,$options['link']);
    }

    /**
     +----------------------------------------------------------
     * �Ա��浽���ݿ�����ݽ��д���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data Ҫ����������
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
     protected function _facade($data) {
        $this->_before_write($data);
        return $data;
     }

    /**
     +----------------------------------------------------------
     * ��ȡ�������ݼ��Ĺ�����¼
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $resultSet  ��������
     * @param string|array $name  ��������
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function getRelations(&$resultSet,$name='') {
        // ��ȡ��¼���������б�
        foreach($resultSet as $key=>$val) {
            $val  = $this->getRelation($val,$name);
            $resultSet[$key]    =   $val;
        }
        return $resultSet;
    }

    /**
     +----------------------------------------------------------
     * ��ȡ�������ݵĹ�����¼
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $result  ��������
     * @param string|array $name  ��������
     * @param boolean $return �Ƿ񷵻ع������ݱ���
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function getRelation(&$result,$name='',$return=false) {
        if(!empty($this->_link)) {
            foreach($this->_link as $key=>$val) {
                    $mappingName =  !empty($val['mapping_name'])?$val['mapping_name']:$key; // ӳ������
                    if(empty($name) || true === $name || $mappingName == $name || (is_array($name) && in_array($mappingName,$name))) {
                        $mappingType = !empty($val['mapping_type'])?$val['mapping_type']:$val;  //  ��������
                        $mappingClass  = !empty($val['class_name'])?$val['class_name']:$key;            //  ��������
                        $mappingFields = !empty($val['mapping_fields'])?$val['mapping_fields']:'*';     // ӳ���ֶ�
                        $mappingCondition = !empty($val['condition'])?$val['condition']:'1=1';          // ��������
                        if(strtoupper($mappingClass)==strtoupper($this->name)) {
                            // �����ù��� ��ȡ������
                            $mappingFk   =   !empty($val['parent_key'])? $val['parent_key'] : 'parent_id';
                        }else{
                            $mappingFk   =   !empty($val['foreign_key'])?$val['foreign_key']:strtolower($this->name).'_id';     //  �������
                        }
                        // ��ȡ����ģ�Ͷ���
                        $model = D($mappingClass);
                        switch($mappingType) {
                            case HAS_ONE:
                                $pk   =  $result[$this->getPk()];
                                $mappingCondition .= " AND {$mappingFk}='{$pk}'";
                                $relationData   =  $model->where($mappingCondition)->field($mappingFields)->find();
                                break;
                            case BELONGS_TO:
                                if(strtoupper($mappingClass)==strtoupper($this->name)) {
                                    // �����ù��� ��ȡ������
                                    $mappingFk   =   !empty($val['parent_key'])? $val['parent_key'] : 'parent_id';
                                }else{
                                    $mappingFk   =   !empty($val['foreign_key'])?$val['foreign_key']:strtolower($model->getModelName()).'_id';     //  �������
                                }
                                $fk   =  $result[$mappingFk];
                                $mappingCondition .= " AND {$model->getPk()}='{$fk}'";
                                $relationData   =  $model->where($mappingCondition)->field($mappingFields)->find();
                                break;
                            case HAS_MANY:
                                $pk   =  $result[$this->getPk()];
                                $mappingCondition .= " AND {$mappingFk}='{$pk}'";
                                $mappingOrder =  !empty($val['mapping_order'])?$val['mapping_order']:'';
                                $mappingLimit =  !empty($val['mapping_limit'])?$val['mapping_limit']:'';
                                // ��ʱ��ȡ������¼
                                $relationData   =  $model->where($mappingCondition)->field($mappingFields)->order($mappingOrder)->limit($mappingLimit)->select();
                                break;
                            case MANY_TO_MANY:
                                $pk   =  $result[$this->getPk()];
                                $mappingCondition = " {$mappingFk}='{$pk}'";
                                $mappingOrder =  $val['mapping_order'];
                                $mappingLimit =  $val['mapping_limit'];
                                $mappingRelationFk = $val['relation_foreign_key']?$val['relation_foreign_key']:$model->getModelName().'_id';
                                $mappingRelationTable  =  $val['relation_table']?$val['relation_table']:$this->getRelationTableName($model);
                                $sql = "SELECT b.{$mappingFields} FROM {$mappingRelationTable} AS a, ".$model->getTableName()." AS b WHERE a.{$mappingRelationFk} = b.{$model->getPk()} AND a.{$mappingCondition}";
                                if(!empty($val['condition'])) {
                                    $sql   .= ' AND '.$val['condition'];
                                }
                                if(!empty($mappingOrder)) {
                                    $sql .= ' ORDER BY '.$mappingOrder;
                                }
                                if(!empty($mappingLimit)) {
                                    $sql .= ' LIMIT '.$mappingLimit;
                                }
                                $relationData   =   $this->query($sql);
                                break;
                        }
                        if(!$return){
                            if(isset($val['as_fields']) && in_array($mappingType,array(HAS_ONE,BELONGS_TO)) ) {
                                // ֧��ֱ�Ӱѹ������ֶ�ֵӳ������ݶ����е�ĳ���ֶ�
                                // ����֧��HAS_ONE BELONGS_TO
                                $fields =   explode(',',$val['as_fields']);
                                foreach ($fields as $field){
                                    if(strpos($field,':')) {
                                        list($name,$nick) = explode(':',$field);
                                        $result[$nick]  =  $relationData[$name];
                                    }else{
                                        $result[$field]  =  $relationData[$field];
                                    }
                                }
                            }else{
                                $result[$mappingName] = $relationData;
                            }
                            unset($relationData);
                        }else{
                            return $relationData;
                        }
                    }
            }
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * ������������
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $opType  ������ʽ ADD SAVE DEL
     * @param mixed $data  ���ݶ���
     * @param string $name ��������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    protected function opRelation($opType,$data='',$name='') {
        $result =   false;
        if(empty($data) && !empty($this->data)){
            $data = $this->data;
        }elseif(!is_array($data)){
            // ������Ч����
            return false;
        }
        if(!empty($this->_link)) {
            // ������������
            foreach($this->_link as $key=>$val) {
                    // �����ƶ���������
                    $mappingName =  $val['mapping_name']?$val['mapping_name']:$key; // ӳ������
                    if(empty($name) || true === $name || $mappingName == $name || (is_array($name) && in_array($mappingName,$name)) ) {
                        // �����ƶ��Ĺ���
                        $mappingType = !empty($val['mapping_type'])?$val['mapping_type']:$val;  //  ��������
                        $mappingClass  = !empty($val['class_name'])?$val['class_name']:$key;            //  ��������
                        // ��ǰ���ݶ�������ֵ
                        $pk =   $data[$this->getPk()];
                        if(strtoupper($mappingClass)==strtoupper($this->name)) {
                            // �����ù��� ��ȡ������
                            $mappingFk   =   !empty($val['parent_key'])? $val['parent_key'] : 'parent_id';
                        }else{
                            $mappingFk   =   !empty($val['foreign_key'])?$val['foreign_key']:strtolower($this->name).'_id';     //  �������
                        }
                        $mappingCondition = !empty($val['condition'])?  $val['condition'] :  "{$mappingFk}='{$pk}'";
                        // ��ȡ����model����
                        $model = D($mappingClass);
                        $mappingData    =   isset($data[$mappingName])?$data[$mappingName]:false;
                        if(!empty($mappingData) || $opType == 'DEL') {
                            switch($mappingType) {
                                case HAS_ONE:
                                    switch (strtoupper($opType)){
                                        case 'ADD': // ���ӹ�������
                                        $mappingData[$mappingFk]    =   $pk;
                                        $result   =  $model->add($mappingData);
                                        break;
                                        case 'SAVE':    // ���¹�������
                                        $result   =  $model->where($mappingCondition)->save($mappingData);
                                        break;
                                        case 'DEL': // �������ɾ����������
                                        $result   =  $model->where($mappingCondition)->delete();
                                        break;
                                    }
                                    break;
                                case BELONGS_TO:
                                    break;
                                case HAS_MANY:
                                    switch (strtoupper($opType)){
                                        case 'ADD'   :  // ���ӹ�������
                                        $model->startTrans();
                                        foreach ($mappingData as $val){
                                            $val[$mappingFk]    =   $pk;
                                            $result   =  $model->add($val);
                                        }
                                        $model->commit();
                                        break;
                                        case 'SAVE' :   // ���¹�������
                                        $model->startTrans();
                                        $pk   =  $model->getPk();
                                        foreach ($mappingData as $vo){
                                            if(isset($vo[$pk])) {// ��������
                                                $mappingCondition   =  "$pk ={$vo[$pk]}";
                                                $result   =  $model->where($mappingCondition)->save($vo);
                                            }else{ // ��������
                                                $vo[$mappingFk] =  $data[$this->getPk()];
                                                $result   =  $model->add($vo);
                                            }
                                        }
                                        $model->commit();
                                        break;
                                        case 'DEL' :    // ɾ����������
                                        $result   =  $model->where($mappingCondition)->delete();
                                        break;
                                    }
                                    break;
                                case MANY_TO_MANY:
                                    $mappingRelationFk = $val['relation_foreign_key']?$val['relation_foreign_key']:$model->getModelName().'_id';// ����
                                    $mappingRelationTable  =  $val['relation_table']?$val['relation_table']:$this->getRelationTableName($model);
                                    if(is_array($mappingData)) {
                                        $ids   = array();
                                        foreach ($mappingData as $vo)
                                            $ids[]   =   $vo[$model->getPk()];
                                        $relationId =   implode(',',$ids);
                                    }
                                    switch (strtoupper($opType)){
                                        case 'ADD': // ���ӹ�������
                                        case 'SAVE':    // ���¹�������
                                        if(isset($relationId)) {
                                            $this->startTrans();
                                            // ɾ������������
                                            $this->table($mappingRelationTable)->where($mappingCondition)->delete();
                                            // �������������
                                            $sql  = 'INSERT INTO '.$mappingRelationTable.' ('.$mappingFk.','.$mappingRelationFk.') SELECT a.'.$this->getPk().',b.'.$model->getPk().' FROM '.$this->getTableName().' AS a ,'.$model->getTableName()." AS b where a.".$this->getPk().' ='. $pk.' AND  b.'.$model->getPk().' IN ('.$relationId.") ";
                                            $result =   $model->execute($sql);
                                            if(false !== $result)
                                                // �ύ����
                                                $this->commit();
                                            else
                                                // ����ع�
                                                $this->rollback();
                                        }
                                        break;
                                        case 'DEL': // �������ɾ���м���������
                                        $result =   $this->table($mappingRelationTable)->where($mappingCondition)->delete();
                                        break;
                                    }
                                    break;
                            }
                    }
                }
            }
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * ���й�����ѯ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $name ��������
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function relation($name) {
        $this->options['link']  =   $name;
        return $this;
    }

    /**
     +----------------------------------------------------------
     * �������ݻ�ȡ �����ڲ�ѯ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ��������
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function relationGet($name) {
        if(empty($this->data))
            return false;
        return $this->getRelation($this->data,$name,true);
    }
}