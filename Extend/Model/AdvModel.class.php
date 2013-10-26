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
// $Id: AdvModel.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP �߼�ģ������չ
 +------------------------------------------------------------------------------
 */
class AdvModel extends Model {
    protected $optimLock = 'lock_version';
    protected $returnType  =  'array';
    protected $blobFields     =   array();
    protected $blobValues    = null;
    protected $serializeField   = array();
    protected $readonlyField  = array();
    protected $_filter           = array();

    public function __construct($name='',$tablePrefix='',$connection='') {
        if('' !== $name || is_subclass_of($this,'AdvModel') ){
            // �����AdvModel��������д���ģ���������ȡ�ֶλ���
        }else{
            // �յ�ģ�� �ر��ֶλ���
            $this->autoCheckFields = false;
        }
        parent::__construct($name,$tablePrefix,$connection);
    }

    /**
     +----------------------------------------------------------
     * ����__call�������� ʵ��һЩ�����Model���� ��ħ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method ��������
     * @param mixed $args ���ò���
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {
        if(strtolower(substr($method,0,3))=='top'){
            // ��ȡǰN����¼
            $count = substr($method,3);
            array_unshift($args,$count);
            return call_user_func_array(array(&$this, 'topN'), $args);
        }else{
            return parent::__call($method,$args);
        }
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
        // ������л��ֶ�
        $data = $this->serializeField($data);
        return parent::_facade($data);
     }

    // ��ѯ�ɹ���Ļص�����
    protected function _after_find(&$result,$options='') {
        // ������л��ֶ�
        $this->checkSerializeField($result);
        // ��ȡ�ı��ֶ�
        $this->getBlobFields($result);
        // ����ֶι���
        $result   =  $this->getFilterFields($result);
        // �����ֹ���
        $this->cacheLockVersion($result);
    }

    // ��ѯ���ݼ��ɹ���Ļص�����
    protected function _after_select(&$resultSet,$options='') {
        // ������л��ֶ�
        $resultSet   =  $this->checkListSerializeField($resultSet);
        // ��ȡ�ı��ֶ�
        $resultSet   =  $this->getListBlobFields($resultSet);
        // ����б��ֶι���
        $resultSet   =  $this->getFilterListFields($resultSet);
    }

    // д��ǰ�Ļص�����
    protected function _before_insert(&$data,$options='') {
        // ��¼�ֹ���
        $data = $this->recordLockVersion($data);
        // ����ı��ֶ�
        $data = $this->checkBlobFields($data);
        // ����ֶι���
        $data   =  $this->setFilterFields($data);
    }

    protected function _after_insert($data,$options) {
        // �����ı��ֶ�
        $this->saveBlobFields($data);
    }

    // ����ǰ�Ļص�����
    protected function _before_update(&$data,$options='') {
        // ����ֹ���
        if(!$this->checkLockVersion($data,$options)) {
            return false;
        }
        // ����ı��ֶ�
        $data = $this->checkBlobFields($data);
        // ���ֻ���ֶ�
        $data = $this->checkReadonlyField($data);
        // ����ֶι���
        $data   =  $this->setFilterFields($data);
    }

    protected function _after_update($data,$options) {
        // �����ı��ֶ�
        $this->saveBlobFields($data);
    }

    protected function _after_delete($data,$options) {
        // ɾ��Blob����
        $this->delBlobFields($data);
    }

    /**
     +----------------------------------------------------------
     * ��¼�ֹ���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data ���ݶ���
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function recordLockVersion($data) {
        // ��¼�ֹ���
        if($this->optimLock && !isset($data[$this->optimLock]) ) {
            if(in_array($this->optimLock,$this->fields,true)) {
                $data[$this->optimLock]  =   0;
            }
        }
        return $data;
    }

    /**
     +----------------------------------------------------------
     * �����ֹ���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data ���ݶ���
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function cacheLockVersion($data) {
        if($this->optimLock) {
            if(isset($data[$this->optimLock]) && isset($data[$this->getPk()])) {
                // ֻ�е������ֹ����ֶκ�������ֵ��ʱ��ż�¼�ֹ���
                $_SESSION[$this->name.'_'.$data[$this->getPk()].'_lock_version']    =   $data[$this->optimLock];
            }
        }
    }

    /**
     +----------------------------------------------------------
     * ����ֹ���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data  ��ǰ����
     * @param array $options ��ѯ���ʽ
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    protected function checkLockVersion(&$data,$options) {
        $id = $data[$this->getPk()];
        // ����ֹ���
        $identify   = $this->name.'_'.$id.'_lock_version';
        if($this->optimLock && isset($_SESSION[$identify])) {
            $lock_version = $_SESSION[$identify];
            $vo   =  $this->field($this->optimLock)->find($id);
            $_SESSION[$identify]     =   $lock_version;
            $curr_version = $vo[$this->optimLock];
            if(isset($curr_version)) {
                if($curr_version>0 && $lock_version != $curr_version) {
                    // ��¼�Ѿ�����
                    $this->error = L('_RECORD_HAS_UPDATE_');
                    return false;
                }else{
                    // �����ֹ���
                    $save_version = $data[$this->optimLock];
                    if($save_version != $lock_version+1) {
                        $data[$this->optimLock]  =   $lock_version+1;
                    }
                    $_SESSION[$identify]     =   $lock_version+1;
                }
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * ����ǰN����¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param integer $count ��¼����
     * @param array $options ��ѯ���ʽ
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function topN($count,$options=array()) {
        $options['limit'] =  $count;
        return $this->select($options);
    }

    /**
     +----------------------------------------------------------
     * ��ѯ���������ĵ�N����¼
     * 0 ��ʾ��һ����¼ -1 ��ʾ���һ����¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param integer $position ��¼λ��
     * @param array $options ��ѯ���ʽ
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function getN($position=0,$options=array()) {
        if($position>=0) { // �������
            $options['limit'] = $position.',1';
            $list   =  $this->select($options);
            return $list?$list[0]:false;
        }else{ // �������
            $list   =  $this->select($options);
            return $list?$list[count($list)-abs($position)]:false;
        }
    }

    /**
     +----------------------------------------------------------
     * ��ȡ���������ĵ�һ����¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ��ѯ���ʽ
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function first($options=array()) {
        return $this->getN(0,$options);
    }

    /**
     +----------------------------------------------------------
     * ��ȡ�������������һ����¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ��ѯ���ʽ
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function last($options=array()) {
        return $this->getN(-1,$options);
    }

    /**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $data ����
     * @param string $type �������� Ĭ��Ϊ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function returnResult($data,$type='') {
        if('' === $type)
            $type = $this->returnType;
        switch($type) {
            case 'array' :  return $data;
            case 'object':  return (object)$data;
            default:// �����û��Զ��巵������
                if(class_exists($type))
                    return new $type($data);
                else
                    throw_exception(L('_CLASS_NOT_EXIST_').':'.$type);
        }
    }

    /**
     +----------------------------------------------------------
     * ��ȡ���ݵ�ʱ����������ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $result ��ѯ������
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function getFilterFields(&$result) {
        if(!empty($this->_filter)) {
            foreach ($this->_filter as $field=>$filter){
                if(isset($result[$field])) {
                    $fun  =  $filter[1];
                    if(!empty($fun)) {
                        if(isset($filter[2]) && $filter[2]){
                            // �����������ݶ�����Ϊ����
                            $result[$field]  =  call_user_func($fun,$result);
                        }else{
                            // �����ֶε�ֵ��Ϊ����
                            $result[$field]  =  call_user_func($fun,$result[$field]);
                        }
                    }
                }
            }
        }
        return $result;
    }

    protected function getFilterListFields(&$resultSet) {
        if(!empty($this->_filter)) {
            foreach ($resultSet as $key=>$result)
                $resultSet[$key]  =  $this->getFilterFields($result);
        }
        return $resultSet;
    }

    /**
     +----------------------------------------------------------
     * д�����ݵ�ʱ����������ֶ�
     +----------------------------------------------------------
     * @access pubic
     +----------------------------------------------------------
     * @param mixed $result ��ѯ������
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function setFilterFields($data) {
        if(!empty($this->_filter)) {
            foreach ($this->_filter as $field=>$filter){
                if(isset($data[$field])) {
                    $fun              =  $filter[0];
                    if(!empty($fun)) {
                        if(isset($filter[2]) && $filter[2]) {
                            // �����������ݶ�����Ϊ����
                            $data[$field]   =  call_user_func($fun,$data);
                        }else{
                            // �����ֶε�ֵ��Ϊ����
                            $data[$field]   =  call_user_func($fun,$data[$field]);
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     +----------------------------------------------------------
     * ���������б�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $resultSet ����
     * @param string $type �������� Ĭ��Ϊ����
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function returnResultSet(&$resultSet,$type='') {
        foreach ($resultSet as $key=>$data)
            $resultSet[$key]  =  $this->returnResult($data,$type);
        return $resultSet;
    }

    protected function checkBlobFields(&$data) {
        // ���Blob�ļ������ֶ�
        if(!empty($this->blobFields)) {
            foreach ($this->blobFields as $field){
                if(isset($data[$field])) {
                    if(isset($data[$this->getPk()]))
                        $this->blobValues[$this->name.'/'.$data[$this->getPk()].'_'.$field] =   $data[$field];
                    else
                        $this->blobValues[$this->name.'/@?id@_'.$field] =   $data[$field];
                    unset($data[$field]);
                }
            }
        }
        return $data;
    }

    /**
     +----------------------------------------------------------
     * ��ȡ���ݼ����ı��ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $resultSet ��ѯ������
     * @param string $field ��ѯ���ֶ�
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function getListBlobFields(&$resultSet,$field='') {
        if(!empty($this->blobFields)) {
            foreach ($resultSet as $key=>$result){
                $result =   $this->getBlobFields($result,$field);
                $resultSet[$key]    =   $result;
            }
        }
        return $resultSet;
    }

    /**
     +----------------------------------------------------------
     * ��ȡ���ݵ��ı��ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data ��ѯ������
     * @param string $field ��ѯ���ֶ�
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function getBlobFields(&$data,$field='') {
        if(!empty($this->blobFields)) {
            $pk =   $this->getPk();
            $id =   $data[$pk];
            if(empty($field)) {
                foreach ($this->blobFields as $field){
                    $identify   =   $this->name.'/'.$id.'_'.$field;
                    $data[$field]   =   F($identify);
                }
                return $data;
            }else{
                $identify   =   $this->name.'/'.$id.'_'.$field;
                return F($identify);
            }
        }
    }

    /**
     +----------------------------------------------------------
     * ����File��ʽ���ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data ���������
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function saveBlobFields(&$data) {
        if(!empty($this->blobFields)) {
            foreach ($this->blobValues as $key=>$val){
                if(strpos($key,'@?id@'))
                    $key    =   str_replace('@?id@',$data[$this->getPk()],$key);
                F($key,$val);
            }
        }
    }

    /**
     +----------------------------------------------------------
     * ɾ��File��ʽ���ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data ���������
     * @param string $field ��ѯ���ֶ�
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function delBlobFields(&$data,$field='') {
        if(!empty($this->blobFields)) {
            $pk =   $this->getPk();
            $id =   $data[$pk];
            if(empty($field)) {
                foreach ($this->blobFields as $field){
                    $identify   =   $this->name.'/'.$id.'_'.$field;
                    F($identify,null);
                }
            }else{
                $identify   =   $this->name.'/'.$id.'_'.$field;
                F($identify,null);
            }
        }
    }

    /**
     +----------------------------------------------------------
     * �ֶ�ֵ�ӳ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $field  �ֶ���
     * @param integer $step  ����ֵ
     * @param integer $lazyTime  ��ʱʱ��(s)
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function setLazyInc($field,$step=1,$lazyTime=0) {
        $condition   =  $this->options['where'];
        if(empty($condition)) { // û�����������κθ���
            return false;
        }
        if($lazyTime>0) {// �ӳ�д��
            $guid =  md5($this->name.'_'.$field.'_'.serialize($condition));
            $step = $this->lazyWrite($guid,$step,$lazyTime);
            if(false === $step ) return true; // �ȴ��´�д��
        }
        return $this->setField($field,array('exp',$field.'+'.$step));
    }

    /**
     +----------------------------------------------------------
     * �ֶ�ֵ�ӳټ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $field  �ֶ���
     * @param integer $step  ����ֵ
     * @param integer $lazyTime  ��ʱʱ��(s)
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function setLazyDec($field,$step=1,$lazyTime=0) {
        $condition   =  $this->options['where'];
        if(empty($condition)) { // û�����������κθ���
            return false;
        }
        if($lazyTime>0) {// �ӳ�д��
            $guid =  md5($this->name.'_'.$field.'_'.serialize($condition));
            $step = $this->lazyWrite($guid,$step,$lazyTime);
            if(false === $step ) return true; // �ȴ��´�д��
        }
        return $this->setField($field,array('exp',$field.'-'.$step));
    }

    /**
     +----------------------------------------------------------
     * ��ʱ���¼�� ����false��ʾ��Ҫ��ʱ
     * ���򷵻�ʵ��д�����ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $guid  д���ʶ
     * @param integer $step  д�벽��ֵ
     * @param integer $lazyTime  ��ʱʱ��(s)
     +----------------------------------------------------------
     * @return false|integer
     +----------------------------------------------------------
     */
    protected function lazyWrite($guid,$step,$lazyTime) {
        if(false !== ($value = F($guid))) { // ���ڻ���д������
            if(time()>F($guid.'_time')+$lazyTime) {
                // ��ʱ����ʱ�䵽�ˣ�ɾ���������� ��ʵ��д�����ݿ�
                F($guid,NULL);
                F($guid.'_time',NULL);
                return $value+$step;
            }else{
                // ׷�����ݵ�����
                F($guid,$value+$step);
                return false;
            }
        }else{ // û�л�������
            F($guid,$step);
            // ��ʱ��ʼ
            F($guid.'_time',time());
            return false;
        }
    }

    /**
     +----------------------------------------------------------
     * ������л������ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data ����
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
     protected function serializeField(&$data) {
        // ������л��ֶ�
        if(!empty($this->serializeField)) {
            // ���巽ʽ  $this->serializeField = array('ser'=>array('name','email'));
            foreach ($this->serializeField as $key=>$val){
                if(empty($data[$key])) {
                    $serialize  =   array();
                    foreach ($val as $name){
                        if(isset($data[$name])) {
                            $serialize[$name]   =   $data[$name];
                            unset($data[$name]);
                        }
                    }
                    if(!empty($serialize)) {
                        $data[$key] =   serialize($serialize);
                    }
                }
            }
        }
        return $data;
     }

    // ��鷵�����ݵ����л��ֶ�
    protected function checkSerializeField(&$result) {
        // ������л��ֶ�
        if(!empty($this->serializeField)) {
            foreach ($this->serializeField as $key=>$val){
                if(isset($result[$key])) {
                    $serialize   =   unserialize($result[$key]);
                    foreach ($serialize as $name=>$value)
                        $result[$name]  =   $value;
                    unset($serialize,$result[$key]);
                }
            }
        }
        return $result;
    }

    // ������ݼ������л��ֶ�
    protected function checkListSerializeField(&$resultSet) {
        // ������л��ֶ�
        if(!empty($this->serializeField)) {
            foreach ($this->serializeField as $key=>$val){
                foreach ($resultSet as $k=>$result){
                    if(isset($result[$key])) {
                        $serialize   =   unserialize($result[$key]);
                        foreach ($serialize as $name=>$value)
                            $result[$name]  =   $value;
                        unset($serialize,$result[$key]);
                        $resultSet[$k] =   $result;
                    }
                }
            }
        }
        return $resultSet;
    }

    /**
     +----------------------------------------------------------
     * ���ֻ���ֶ�
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data ����
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function checkReadonlyField(&$data) {
        if(!empty($this->readonlyField)) {
            foreach ($this->readonlyField as $key=>$field){
                if(isset($data[$field]))
                    unset($data[$field]);
            }
        }
        return $data;
    }

    /**
     +----------------------------------------------------------
     * ������ִ��SQL���
     * �������ָ���Ϊ��execute����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $sql  SQL������ָ��
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function patchQuery($sql=array()) {
        if(!is_array($sql)) return false;
        // �Զ���������֧��
        $this->startTrans();
        try{
            foreach ($sql as $_sql){
                $result   =  $this->execute($_sql);
                if(false === $result) {
                    // ���������Զ��ع�����
                    $this->rollback();
                    return false;
                }
            }
            // �ύ����
            $this->commit();
        } catch (ThinkException $e) {
            $this->rollback();
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * �õ��ֱ�ĵ����ݱ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $data ����������
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getPartitionTableName($data=array()) {
        // �����ݱ���з���
        if(isset($data[$this->partition['field']])) {
            $field   =   $data[$this->partition['field']];
            switch($this->partition['type']) {
                case 'id':
                    // ����id��Χ�ֱ�
                    $step    =   $this->partition['expr'];
                    $seq    =   floor($field / $step)+1;
                    break;
                case 'year':
                    // ������ݷֱ�
                    if(!is_numeric($field)) {
                        $field   =   strtotime($field);
                    }
                    $seq    =   date('Y',$field)-$this->partition['expr']+1;
                    break;
                case 'mod':
                    // ����id��ģ���ֱ�
                    $seq    =   ($field % $this->partition['num'])+1;
                    break;
                case 'md5':
                    // ����md5�����зֱ�
                    $seq    =   (ord(substr(md5($field),0,1)) % $this->partition['num'])+1;
                    break;
                default :
                    if(function_exists($this->partition['type'])) {
                        // ֧��ָ��������ϣ
                        $fun    =   $this->partition['type'];
                        $seq    =   (ord(substr($fun($field),0,1)) % $this->partition['num'])+1;
                    }else{
                        // �����ֶε�����ĸ��ֵ�ֱ�
                        $seq    =   (ord($field{0}) % $this->partition['num'])+1;
                    }
            }
            return $this->getTableName().'_'.$seq;
        }else{
            // �����õķֱ��ֶβ��ڲ�ѯ��������������
            // �������ϲ�ѯ�������趨 partition['num']
            $tableName  =   array();
            for($i=0;$i<$this->partition['num'];$i++)
                $tableName[] = 'SELECT * FROM '.$this->getTableName().'_'.($i+1);
            $tableName = '( '.implode(" UNION ",$tableName).') AS '.$this->name;
            return $tableName;
        }
    }
}