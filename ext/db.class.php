<?php 
/**
 * ���ݿ���
 * �ṩ�����ݵ�д�룬��ѯ�ȹ��ܡ�
 */
class db {
    /**
     * ���ݿ��ַ
     * ����: 201.203.30.40:3306
     * @var string ���ݿ��ַ
     */
    public $db_host = 'localhost';
    /**
     * ���ݿ���
     * ���磺 zvcore
     * @var string ���ݿ���
     */
    public $db_name = 'zvcore';
    /**
     * ���ݿ��û���
     * ���磺root
     * @var string ���ݿ��û���
     */
    public $db_user = 'root';
    /**
     * ���ݿ�����
     * ���磺****bb99**
     * @var string ���ݿ�����
     */
    public $db_pwd = '';
    /**
     * ���ݿ����
     * ���磺utf8
     * @var string ���ݿ����
     */
    public $db_charset = 'utf8';
    /**
     * ���ݿ��ǰ׺
     * ���磺zv_
     * @var string ���ݿ��ǰ׺
     */
    public $db_prefix = 'zv_';
    /**
     * ���ݿ������
     * ���磺$this->db(����$db)
     * @var object ���ݿ�����
     */
    public $db_link = null;
    /**
     * ���������ļ�
     * �����ļ�λ��./config/db.php
     * @param object $config ������Ϣ����
     */
    public function __construct($config=array()) {
    	
        $this->db_host = $config['db_host'];
        $this->db_name = $config['db_name'];
        $this->db_user = $config['db_user'];
        $this->db_pwd = $config['db_pwd'];
        $this->db_charset = $config['db_charset'];
        $this->db_prefix = $config['db_prefix'];		
        $this->connect();		
    }
    /**
     * ��ѯ������
     * @param object $sql
     * @param object $lifetime [optional]
     * @return 
     */
    public function queryAndCache($sql, $lifetime = 5) {
        $cacheData = zcache::get($sql);
        if (!$cacheData) {
            $rs = $this->query($sql);
            $cacheData = $this->got_array($rs);
            zcache::set($sql, $cacheData, $lifetime);
        }
        return $cacheData;
    }
    /**
     * �������ݿ�
     * ��Ҫ�������ļ��ﶨ������ݵ�������Ϣ
     */
    public function connect() {
        $this->db_link = @mysql_connect($this->db_host, $this->db_user, $this->db_pwd);
		
		if (!$this->db_link) {
			throw new Exception("���ݿ�����ʧ��");
            
        }
        $this->query("set names ".$this->db_charset);
        $this->select_db($this->db_name);
    }
    /**
     * ��ѯ
     * ��Ҫ�ṩ��ѯ���
     * @return object ���ز�ѯ ��Դ
     * @param object $sql ��ѯ���
     */
    public function query($sql) {
        if (null == $this->db_link) {
            $this->connect();
        }
        $query = mysql_query($sql, $this->db_link);
        return $query;
    }
    /**
     * ѡ�����ݿ�
     *  ���ݿⶨ���������ļ���
     * @return ѡ�����ݿ���
     * @param object $dbname ���ݿ���
     */
    public function select_db($dbname) {
        $sr = mysql_select_db($dbname, $this->db_link);
        if (!$sr) {
           throw new Exception("ѡ�����ݿ�ʧ�ܣ�");
        } else {
            return true;
        }
    }
	/**
	 * ������ʵ����
	 * @param object $table
	 * @return 
	 */
	public function tableName($table){
		$dbConfig=registry::getRegistry('db');
		$db_prefix=$dbConfig['db_prefix'];
		return $db_prefix.$table;
	}
    /**
     * ����һ������
     * һ����ͷһ��
     * @return ����һ������
     * @param object $query ��ѯ��Դ
     */
    public function fetch_array($query) {
        if (!$query) {
           return false;
        }
        return mysql_fetch_assoc($query);
    }
	/**
	 * ���ض������͵Ĳ�ѯ���
	 * @param object $query
	 * @return 
	 */
	public function fetch_object($query){
		if(!$query){
			return false;
		}
		return mysql_fetch_object($query);
	}
	/**
	 * ���ز�ѯ�ĵ�һ����һ�еĽ��
	 * @param object $result
	 * @param object $row [optional]
	 * @return 
	 */
	public function result($result,$row=0){
		$rows=mysql_fetch_row($result);
		return $rows[$row];
	}
    
    /**
     * ���ز�ѯ
     * �����н������
     * @return ���в�ѯ���������
     * @param object $query ��ѯ��Դ
     */
    public function got_array($query) {
        if (!$query || !$this->num_rows($query) ){
           return false;
        }
        while ($rt = $this->fetch_array($query)) {
            $ga[] = $rt;
        }
        return $ga;
    }
    /**
     * ����һ������
     * ���ز����id
     * @return  �������ݵ�id
     * @param object $sql ��ѯ����
     */
    public function insert($sql) {
        if (!$this->query($sql)) {
            return false;
        }
        return mysql_insert_id($this->db_link);
    }
    /**
     * Ӱ�����
     * ���ز�ѯ������Ӱ�����
     * @return mixed Ӱ�����
     */
    public function affected_rows() {
    
        return mysql_affected_rows($this->db_link);
    }
    
    /**
     * ���ݽ������
     * ���ز�ѯ��������
     * @return integer ������
     * @param object $query ��ѯ������Դ
     */
    public function num_rows($query) {
        if (!$query) {
            return false;
        }
        return mysql_num_rows($query);
    }
    /**
     *��ȫ�����������
     * @param <type> $str
     * @return <type>
     */
    public function escape_string($str){
        return mysql_real_escape_string($str);
    }
    /**
     * ��ȡ������Ϣ
     * �����Ҫ��������mysql��ѯ�쳣��ʱ����ô˷����������ٴ�����Ϣ
     */
    public function get_mysql_error() {
        $errorMsg = mysql_errno();
        $errorMsg .= ":";
        $errorMsg .= mysql_error();
        $errorMsg .= "\n";
        return $errorMsg;
    }
    
}
