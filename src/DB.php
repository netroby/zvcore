<?php
namespace netroby\zvcore;

/**
 * ���ݿ���
 * �ṩ�����ݵ�д�룬��ѯ�ȹ��ܡ�
 */
class db
{
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
     * @param array $config ������Ϣ����
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
     * ��ѯ������
     * @param string $sql
     * @param integer $lifetime [optional]
     * @return mixed
     * @throws \RuntimeException
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
     * �������ݿ�
     * ��Ҫ�������ļ��ﶨ������ݵ�������Ϣ
     * @throws \RuntimeException
     */
    public function connect()
    {
        $this->db_link = @mysql_connect($this->db_host, $this->db_user, $this->db_pwd);

        if (!$this->db_link) {
            throw new \RuntimeException('���ݿ�����ʧ��');

        }
        $this->query("set names " . $this->db_charset);
        $this->select_db($this->db_name);
    }

    /**
     * ��ѯ
     * ��Ҫ�ṩ��ѯ���
     * @param object $sql ��ѯ���
     * @return object ���ز�ѯ ��Դ
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
     * ѡ�����ݿ�
     *  ���ݿⶨ���������ļ���
     * @return mixed ѡ�����ݿ���
     * @param string $dbname ���ݿ���
     * @throws \RuntimeException
     */
    public function select_db($dbname)
    {
        $sr = mysql_select_db($dbname, $this->db_link);
        if (!$sr) {
            throw new \RuntimeException('ѡ�����ݿ�ʧ�ܣ�');
        } else {
            return true;
        }
    }

    /**
     * ������ʵ����
     * @param object $table
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function tableName($table)
    {
        $dbConfig = registry::getRegistry('db');
        if (is_array($dbConfig)) {
            if (!array_key_exists('db_prefix', $dbConfig)) {
                throw new \InvalidArgumentException('db_prefix not configured');
            }
            $db_prefix = $dbConfig['db_prefix'];
            return $db_prefix . $table;
        }
        return false;
    }

    /**
     * ����һ������
     * һ����ͷһ��
     * @param resource $query ��ѯ��Դ
     * @return mixed ����һ������
     */
    public function fetch_array($query)
    {
        if (!$query) {
            return false;
        }
        return mysql_fetch_assoc($query);
    }

    /**
     * ���ض������͵Ĳ�ѯ���
     * @param resource $query
     * @return mixed
     */
    public function fetch_object($query)
    {
        if (!$query) {
            return false;
        }
        return mysql_fetch_object($query);
    }

    /**
     * ���ز�ѯ�ĵ�һ����һ�еĽ��
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
     * ���ز�ѯ
     * �����н������
     * @param resource $query ��ѯ��Դ
     * @return mixed ���в�ѯ���������
     */
    public function getArray($query)
    {
        $ga = false;
        if (!$query || !$this->num_rows($query)) {
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
     * @param object $sql ��ѯ����
     * @return  mixed �������ݵ�id
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
     * Ӱ�����
     * ���ز�ѯ������Ӱ�����
     * @return mixed Ӱ�����
     */
    public function affected_rows()
    {

        return mysql_affected_rows($this->db_link);
    }

    /**
     * ���ݽ������
     * ���ز�ѯ��������
     * @param resource $query ��ѯ������Դ
     * @return integer ������
     */
    public function num_rows($query)
    {
        if (!$query) {
            return false;
        }
        return mysql_num_rows($query);
    }

    /**
     *��ȫ�����������
     * @param string $str
     * @return string
     */
    public function escape_string($str)
    {
        return mysql_real_escape_string($str);
    }

    /**
     * ��ȡ������Ϣ
     * �����Ҫ��������mysql��ѯ�쳣��ʱ����ô˷����������ٴ�����Ϣ
     */
    public function get_mysql_error()
    {
        $errorMsg = mysql_errno();
        $errorMsg .= ':';
        $errorMsg .= mysql_error();
        $errorMsg .= PHP_EOL;
        return $errorMsg;
    }

}
