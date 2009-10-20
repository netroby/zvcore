<?php 
/**
 * 数据库类
 * 提供了数据的写入，查询等功能。
 */
class db {
    /**
     * 数据库地址
     * 例如: 201.203.30.40:3306
     * @var string 数据库地址
     */
    public $db_host = 'localhost';
    /**
     * 数据库名
     * 例如： zvcore
     * @var string 数据库名
     */
    public $db_name = 'zvcore';
    /**
     * 数据库用户名
     * 例如：root
     * @var string 数据库用户名
     */
    public $db_user = 'root';
    /**
     * 数据库密码
     * 例如：****bb99**
     * @var string 数据库密码
     */
    public $db_pwd = '';
    /**
     * 数据库编码
     * 例如：utf8
     * @var string 数据库编码
     */
    public $db_charset = 'utf8';
    /**
     * 数据库表前缀
     * 例如：zv_
     * @var string 数据库表前缀
     */
    public $db_prefix = 'zv_';
    /**
     * 数据库表链接
     * 例如：$this->db(或者$db)
     * @var object 数据库链接
     */
    public $db_link = null;
    /**
     * 加载配置文件
     * 配置文件位于./config/db.php
     * @param object $config 配置信息数组
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
     * 查询并缓存
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
     * 连接数据库
     * 需要在配置文件里定义好数据的链接信息
     */
    public function connect() {
        $this->db_link = @mysql_connect($this->db_host, $this->db_user, $this->db_pwd);
		
		if (!$this->db_link) {
			throw new Exception("数据库连接失败");
            
        }
        $this->query("set names ".$this->db_charset);
        $this->select_db($this->db_name);
    }
    /**
     * 查询
     * 需要提供查询语句
     * @return object 返回查询 资源
     * @param object $sql 查询语句
     */
    public function query($sql) {
        if (null == $this->db_link) {
            $this->connect();
        }
        $query = mysql_query($sql, $this->db_link);
        return $query;
    }
    /**
     * 选择数据库
     *  数据库定义在配置文件里
     * @return 选择数据库结果
     * @param object $dbname 数据库名
     */
    public function select_db($dbname) {
        $sr = mysql_select_db($dbname, $this->db_link);
        if (!$sr) {
           throw new Exception("选择数据库失败！");
        } else {
            return true;
        }
    }
	/**
	 * 构建真实表名
	 * @param object $table
	 * @return 
	 */
	public function tableName($table){
		$dbConfig=registry::getRegistry('db');
		$db_prefix=$dbConfig['db_prefix'];
		return $db_prefix.$table;
	}
    /**
     * 返回一行数据
     * 一般是头一行
     * @return 返回一行数据
     * @param object $query 查询资源
     */
    public function fetch_array($query) {
        if (!$query) {
           return false;
        }
        return mysql_fetch_assoc($query);
    }
	/**
	 * 返回对象类型的查询结果
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
	 * 返回查询的第一条第一列的结果
	 * @param object $result
	 * @param object $row [optional]
	 * @return 
	 */
	public function result($result,$row=0){
		$rows=mysql_fetch_row($result);
		return $rows[$row];
	}
    
    /**
     * 返回查询
     * 的所有结果数组
     * @return 所有查询结果的数组
     * @param object $query 查询资源
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
     * 插入一条数据
     * 返回插入的id
     * @return  插入数据的id
     * @param object $sql 查询数据
     */
    public function insert($sql) {
        if (!$this->query($sql)) {
            return false;
        }
        return mysql_insert_id($this->db_link);
    }
    /**
     * 影响的列
     * 返回查询操作所影响的列
     * @return mixed 影响的列
     */
    public function affected_rows() {
    
        return mysql_affected_rows($this->db_link);
    }
    
    /**
     * 数据结果行数
     * 返回查询的数据列
     * @return integer 数据行
     * @param object $query 查询数据资源
     */
    public function num_rows($query) {
        if (!$query) {
            return false;
        }
        return mysql_num_rows($query);
    }
    /**
     *安全处理变量数据
     * @param <type> $str
     * @return <type>
     */
    public function escape_string($str){
        return mysql_real_escape_string($str);
    }
    /**
     * 获取出错信息
     * 如果需要，可以在mysql查询异常的时候调用此方法，来跟踪错误信息
     */
    public function get_mysql_error() {
        $errorMsg = mysql_errno();
        $errorMsg .= ":";
        $errorMsg .= mysql_error();
        $errorMsg .= "\n";
        return $errorMsg;
    }
    
}
