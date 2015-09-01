<?php
namespace netroby\zvcore;

/**
 * 数据库类
 * 提供了数据的写入，查询等功能。
 */
class DB
{
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
     * @param array $config 配置信息数组
     * @throws \RuntimeException
     */
    public function __construct(array $config = array())
    {

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
     * @param string $sql
     * @param integer $lifetime [optional]
     * @return mixed
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function queryAndCache($sql, $lifetime = 0)
    {
        if ($lifetime > 0) {
            $cacheData = zcache::get($sql);
            if (!$cacheData) {
                $rs = $this->query($sql);
                $cacheData = $this->getArray($rs);
                zcache::set($sql, $cacheData, $lifetime);
            }
            return $cacheData;
        } else {
            $rs = $this->query($sql);
            return $this->getArray($rs);
        }
    }

    /**
     * 连接数据库
     * 需要在配置文件里定义好数据的链接信息
     * @throws \RuntimeException
     */
    public function connect()
    {
        $this->db_link = @mysql_connect($this->db_host, $this->db_user, $this->db_pwd);

        if (!$this->db_link) {
            throw new \RuntimeException('数据库连接失败');

        }
        $this->query('set names ' . $this->db_charset);
        $this->selectDB($this->db_name);
    }

    /**
     * 查询
     * 需要提供查询语句
     * @param string $sql 查询语句
     * @return mixed 返回查询 资源
     * @throws \RuntimeException
     */
    public function query($sql)
    {
        if (null === $this->db_link) {
            $this->connect();
        }
        $query = mysql_query($sql, $this->db_link);
        return $query;
    }

    /**
     * 选择数据库
     *  数据库定义在配置文件里
     * @return mixed 选择数据库结果
     * @param string $dbname 数据库名
     * @throws \RuntimeException
     */
    public function selectDB($dbname)
    {
        $sr = mysql_select_db($dbname, $this->db_link);
        if (!$sr) {
            throw new \RuntimeException('选择数据库失败！');
        } else {
            return true;
        }
    }

    /**
     * 返回一行数据
     * 一般是头一行
     * @param resource $query 查询资源
     * @return mixed 返回一行数据
     */
    public function fetchArray($query)
    {
        if (!$query) {
            return false;
        }
        return mysql_fetch_assoc($query);
    }

    /**
     * 返回对象类型的查询结果
     * @param resource $query
     * @return mixed
     */
    public function fetchObject($query)
    {
        if (!$query) {
            return false;
        }
        return mysql_fetch_object($query);
    }

    /**
     * 返回查询的第一条第一列的结果
     * @param resource $result
     * @param string|integer $row [optional]
     * @return
     */
    public function result($result, $row = 0)
    {
        $rows = mysql_fetch_row($result);
        return $rows[$row];
    }

    /**
     * 返回查询
     * 的所有结果数组
     * @param resource $query 查询资源
     * @return mixed 所有查询结果的数组
     */
    public function getArray($query)
    {
        $ga = [];
        if (!$query || !$this->numRows($query)) {
            return false;
        }
        while ($rt = $this->fetchArray($query)) {
            $ga[] = $rt;
        }
        return $ga;
    }

    /**
     * 插入一条数据
     * 返回插入的id
     * @param object $sql 查询数据
     * @return  mixed 插入数据的id
     * @throws \RuntimeException
     */
    public function insert($sql)
    {
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
    public function affectedRows()
    {

        return mysql_affected_rows($this->db_link);
    }

    /**
     * 数据结果行数
     * 返回查询的数据列
     * @param resource $query 查询数据资源
     * @return integer 数据行
     */
    public function numRows($query)
    {
        if (!$query) {
            return false;
        }
        return mysql_num_rows($query);
    }

    /**
     *安全处理变量数据
     * @param string $str
     * @return string
     */
    public function escapeString($str)
    {
        return mysql_real_escape_string($str);
    }

    /**
     * 获取出错信息
     * 如果需要，可以在mysql查询异常的时候调用此方法，来跟踪错误信息
     */
    public function getMysqlError()
    {
        $errorMsg = mysql_errno();
        $errorMsg .= ':';
        $errorMsg .= mysql_error();
        $errorMsg .= PHP_EOL;
        return $errorMsg;
    }

}
