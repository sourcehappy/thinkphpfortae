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
// $Id: DbPdo.class.php 2729 2012-02-12 04:13:34Z liu21st $

/**
 +-----------------------------
 * PDO���ݿ�������
 +-----------------------------
 */
class DbPdo extends Db{

    protected $PDOStatement = null;
    private   $table = '';

    /**
     +----------------------------------------------------------
     * �ܹ����� ��ȡ���ݿ�������Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $config ���ݿ���������
     +----------------------------------------------------------
     */
    public function __construct($config=''){
        if ( !class_exists('PDO') ) {
            throw_exception(L('_NOT_SUPPERT_').':PDO');
        }
        if(!empty($config)) {
            $this->config   =   $config;
            if(empty($this->config['params'])) {
                $this->config['params'] =   array();
            }
        }

    }

    /**
     +----------------------------------------------------------
     * �������ݿⷽ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function connect($config='',$linkNum=0) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;
            if($this->pconnect) {
                $config['params'][PDO::ATTR_PERSISTENT] = true;
            }
            try{
				$this->linkID[$linkNum] = new PDO( $config['dsn'], $config['username'], $config['password'],$config['params']);
            }catch (PDOException $e) {
                throw_exception($e->getMessage());
            }
            // ��ΪPDO�������л����ܵ������ݿ����Ͳ�ͬ��������»�ȡ�µ�ǰ�����ݿ�����
            $this->dbType = $this->_getDsnType($config['dsn']);
            $this->linkID[$linkNum]->exec('SET NAMES '.C('DB_CHARSET'));
			$this->linkID[$linkNum]->exec("SET sql_mode=''");
			// ������ӳɹ�
            $this->connected    =   true;
            // ע�����ݿ�����������Ϣ
            if(1 != C('DB_DEPLOY_TYPE')) unset($this->config);
        }
        return $this->linkID[$linkNum];
    }

    /**
     +----------------------------------------------------------
     * �ͷŲ�ѯ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function free() {
        $this->PDOStatement = null;
    }

    /**
     +----------------------------------------------------------
     * ִ�в�ѯ �������ݼ�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  sqlָ��
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function query($str) {
        $this->initConnect(false);
        if ( !$this->_linkID ) return false;
		if (!IS_ONLINE)
			$str=unicode_encode($str,"GBK");

		$this->queryStr = $str;
        //�ͷ�ǰ�εĲ�ѯ���
        if ( !empty($this->PDOStatement) ) $this->free();
        N('db_query',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $this->PDOStatement = $this->_linkID->prepare($str);
        if(false === $this->PDOStatement)
            throw_exception($this->error());
        $result =   $this->PDOStatement->execute();
        $this->debug();
        if ( false === $result ) {
            $this->error();
            return false;
        } else {
            return $this->getAll();
        }
    }

	    /**
     +----------------------------------------------------------
     * ִ�в�ѯ �������ݼ�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  sqlָ��
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function queryStr($str) {
        $this->initConnect(false);
        if ( !$this->_linkID ) return false;
		if (!IS_ONLINE)
			$str=unicode_encode($str,"GBK");

		$this->queryStr = $str;
         N('db_query',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $result =   $this->_linkID->query($str);
        $this->debug();
        if ( false === $result ) {
            $this->error();
            return false;
        } else {
            return $result->fetchAll(constant('PDO::FETCH_ASSOC'));
        }
    }


    /**
     +----------------------------------------------------------
     * ִ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  sqlָ��
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function execute($str) {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
		if (!IS_ONLINE)
			$str=unicode_encode($str,"GBK");

        $this->queryStr = $str;
        $flag = false;
        //�ͷ�ǰ�εĲ�ѯ���
        if ( !empty($this->PDOStatement) ) $this->free();
        N('db_write',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');

        $this->PDOStatement	=	$this->_linkID->prepare( $str );
        if(false === $this->PDOStatement) {
            throw_exception($this->error());
        }
        $result	=	$this->PDOStatement->execute();
        $this->debug();
        if ( false === $result) {
            $this->error();
            return false;
        } else {
            $this->numRows = $result;
            if($flag || preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $str)) {
                $this->lastInsID = $this->getLastInsertId();
            }
            return $this->numRows;
        }
    }

    /**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function startTrans() {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        //����rollback ֧��
        if ($this->transTimes == 0) {
            $this->_linkID->beginTransaction();
        }
        $this->transTimes++;
        return ;
    }

    /**
     +----------------------------------------------------------
     * ���ڷ��Զ��ύ״̬����Ĳ�ѯ�ύ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function commit() {
        if ($this->transTimes > 0) {
            $result = $this->_linkID->commit();
            $this->transTimes = 0;
            if(!$result){
                throw_exception($this->error());
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * ����ع�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function rollback() {
        if ($this->transTimes > 0) {
            $result = $this->_linkID->rollback();
            $this->transTimes = 0;
            if(!$result){
                throw_exception($this->error());
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * ������еĲ�ѯ����
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    private function getAll() {
        //�������ݼ�
        $result =   $this->PDOStatement->fetchAll(constant('PDO::FETCH_ASSOC'));
        $this->numRows = count( $result );
        return $result;
    }

    /**
     +----------------------------------------------------------
     * ȡ�����ݱ���ֶ���Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getFields($tableName) {
        $this->initConnect(true);
        if(C('DB_DESCRIBE_TABLE_SQL')) {
            // ����������ֶβ�ѯSQL
            $sql   = str_replace('%table%',$tableName,C('DB_DESCRIBE_TABLE_SQL'));
        }else{
                    $sql   = 'DESCRIBE '.$tableName;//��ע: �����಻ֻ���mysql�����ܼ�``
         }
        $result = $this->queryStr($sql);
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $val['Name'] = isset($val['name'])?$val['name']:$val['Name'];
                $val['Type'] = isset($val['type'])?$val['type']: $val['Type'];
                $name= strtolower(isset($val['Field'])?$val['Field']:$val['Name']);
                $info[$name] = array(
                    'name'    => $name ,
                    'type'    => $val['Type'],
                    'notnull' => (bool)(((isset($val['Null'])) && ($val['Null'] === '')) || ((isset($val['notnull'])) && ($val['notnull'] === ''))), // not null is empty, null is yes
                    'default' => isset($val['Default'])? $val['Default'] :(isset($val['dflt_value'])?$val['dflt_value']:""),
                    'primary' => isset($val['Key'])?strtolower($val['Key']) == 'pri':(isset($val['pk'])?$val['pk']:false),
                    'autoinc' => isset($val['Extra'])?strtolower($val['Extra']) == 'auto_increment':(isset($val['Key'])?$val['Key']:false),
                );
            }
        }
        return $info;
    }

    /**
     +----------------------------------------------------------
     * ȡ�����ݿ�ı���Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getTables($dbName='') {
        if(C('DB_FETCH_TABLES_SQL')) {
            // ��������ı��ѯSQL
            $sql   = str_replace('%db%',$dnName,C('DB_FETCH_TABLES_SQL'));
        }else{
			if(!empty($dbName)) {
			   $sql    = 'SHOW TABLES FROM '.$dbName;
			}else{
			   $sql    = 'SHOW TABLES ';
			}
        }
        $result = $this->query($sql);
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

    /**
     +----------------------------------------------------------
     * limit����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $lmit
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseLimit($limit) {
        $limitStr    = '';
        if(!empty($limit)) {
			$limitStr .= ' LIMIT '.$limit.' ';
        }
        return $limitStr;
    }

    /**
     +----------------------------------------------------------
     * �ر����ݿ�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function close() {
        $this->_linkID = null;
    }

    /**
     +----------------------------------------------------------
     * ���ݿ������Ϣ
     * ����ʾ��ǰ��SQL���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function error() {
        if($this->PDOStatement) {
            $error = $this->PDOStatement->errorInfo();
            $this->error = $error[2];
        }else{
            $this->error = '';
        }
        if($this->debug && '' != $this->queryStr){
            $this->error .= "\n [ SQL��� ] : ".$this->queryStr;
        }
        return $this->error;
    }

    /**
     +----------------------------------------------------------
     * SQLָ�ȫ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  SQLָ��
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function escapeString($str) {
         return addslashes($str);
    }

    /**
     +----------------------------------------------------------
     * ��ȡ������id
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    public function getLastInsertId() {
         return $this->_linkID->lastInsertId();
    }

}