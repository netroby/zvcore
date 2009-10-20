<?php 
class Model extends db {
    protected $_dbConfig;
    protected $lastSql;
    protected $table;
    public function __construct($tableName='') {
    	//���û�����ñ��������Զ�����Model��ȡ����
        if(null!=$tableName && ""==$this->table){
        	$this->table=$tableName;
        }    
        //���û���������ݿ������ļ�����ȡĬ���ļ�
        if (null == $this->_dbConfig) {
            $this->_dbConfig = registry::getRegistry('db');
        }
        parent::__construct($this->_dbConfig);
        
    }
    /**
     * ȡ����
     * @return
     */
    private function _getTable() {
    	
        $tableName = $this->tableName($this->table);
        return $tableName;
    }
    /**
     * ����id����
     * @param object $id [optional]
     * @param object $rows [optional]
     * @return
     */
    public function findById($id = 0, $rows = "") {
        if ("" == $rows) {
            $rows = " * ";
        }
        $sql = "select ".$rows." from ".$this->_getTable()." where id = '".$id."';";
        $this->lastSql = $sql;
        $result = $this->query($sql);
        if (!$result) {
            return false;
        }
        return $this->fetch_object($result);
    }
    /**
     * �����������Ҽ�¼
     * @param object $byWhat [optional] ָ������
     * @param object $value [optional] ��������
     * @param object $rows [optional]  ��Ҫ��ѯ���ֶ�
     * @return ��ѯ���
     */
    public function findBy($byWhat = 'id', $value = 0, $rows = "") {
        if ("" == $rows) {
            $rows = " * ";
        }
        $sql = "select ".$rows." from ".$this->_getTable()." where ".$byWhat." = '".$this->escape_string($value)."';";
        $this->lastSql = $sql;
        $result = $this->query($sql);
        if (!$result) {
            return FALSE;
        }
        return $this->fetch_object($result);
    }
    /**
     * ����idɾ����¼
     * @param object $id [optional] id��ֵ
     * @return ɾ���Ľ��
     */
    public function deleteById($id = 0) {
        if ($id == 0) {
            return false;
        }
        $sql = "delete from ".$this->_getTable()." where id = '".$id."';";
        $this->lastSql = $sql;
        return $this->query($sql);
        
    }
    /**
     * �����ض�����ɾ��
     * @param object $byWhat [optional] ָ������
     * @param object $value [optional]  ��������
     * @return ɾ���Ľ��
     */
    public function deleteBy($byWhat, $value) {
        if(!isset($value) || !isset($byWhat)){
        	return false;
        }
		$sql = "delete from ".$this->_getTable()." where ".$byWhat." = '".$value."';";
        $this->lastSql = $sql;
		
        return $this->query($sql);
		
    }
    /**
     * ������¼
     * @param object $data [optional] ������������
     * @return �����Ľ��
     */
    public function insertTable($data = array()) {
        $dc = count($data);
        if ($dc == 0) {
            return false;
        }
        $key_handle = array_keys($data);
        $val_handle = array_values($data);
        for ($i = 0; $i < $dc; $i++) {
            $keys[$i] = trim($key_handle[$i]);
            $vals[$i] = "'".$this->escape_string($val_handle[$i])."'";
        }
        $sql = "insert into ".$this->_getTable()." (".implode(", ", $keys).") values (".implode(", ", $vals).");";
        $this->lastSql = $sql;
        return $this->insert($sql);

        
    }
    /**
     * ���±��
     * @param object $id [optional] ������ֵ
     * @param object $data [optional] Ҫ���µ�����
     * @return ���½��
     */
    public function updateTable($id = '', $data = array()) {
        if ($id == "") {
            return false;
        }
        $dc = count($data);
        if ($dc == 0) {
            return false;
        }
        $key_handle = array_keys($data);
        $val_handle = array_values($data);
        
        $qm = "  ";
        $k = $dc - 1;
        for ($i = 0; $i < $dc; $i++) {
        
            if ($i == $k) {
                $qm .= trim($key_handle[$i])." = '".$this->escape_string($val_handle[$i])."' ";
            } else {
                $qm .= trim($key_handle[$i])." = '".$this->escape_string($val_handle[$i])."', ";
            }
        }
        $sql = " update ".$this->_getTable()." set ".$qm." where id = '".$id."';";
        //����sql
        $this->lastSql = $sql;
        return $this->query($sql);
    }
    /**
     * ��ȡ����sql���
     * @return ����sql���
     */
    public function getLastSql() {
        return $this->lastSql;
    }
    /**
     * ����
     * @param object $name
     * @param object $args
     * @return
     */
    public function __call($name, $args) {
    
        if (strstr($name, 'findBy')) {
            $byWhat = str_replace("findBy", '', $name);
           return  $this->findBy($byWhat, $args['0']);
        }
        if (strstr($name, 'deleteBy')) {
            $byWhat = str_replace("deleteBy", '', $name);
            
            return $this->deleteBy($byWhat, $args['0']);
        }
        
    }
}
