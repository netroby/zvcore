<?php

class Model extends db
{
    protected $_dbConfig;
    protected $lastSql;
    protected $table;

    public function __construct($tableName = '')
    {
        //如果没有设置表名，则自动根据Model名取表名
        if (null !== $tableName && '' === $this->table) {
            $this->table = $tableName;
        }
        //如果没有设置数据库配置文件，读取默认文件
        if (null === $this->_dbConfig) {
            $this->_dbConfig = registry::getRegistry('db');
        }
        parent::__construct($this->_dbConfig);

    }

    /**
     * 取表名
     * @return
     */
    private function _getTable()
    {

        $tableName = $this->tableName($this->table);
        return $tableName;
    }

    /**
     * 根据id查找
     * @param object $id [optional]
     * @param object $rows [optional]
     * @return
     */
    public function findById($id = 0, $rows = "")
    {
        if ("" == $rows) {
            $rows = " * ";
        }
        $sql = "select " . $rows . " from " . $this->_getTable() . " where id = '" . $id . "';";
        $this->lastSql = $sql;
        $result = $this->query($sql);
        if (!$result) {
            return false;
        }
        return $this->fetch_object($result);
    }

    /**
     * 根据条件查找记录
     * @param object $byWhat [optional] 指定条件
     * @param object $value [optional] 条件内容
     * @param object $rows [optional]  需要查询的字段
     * @return 查询结果
     */
    public function findBy($byWhat = 'id', $value = 0, $rows = "")
    {
        if ("" == $rows) {
            $rows = " * ";
        }
        $sql = "select " . $rows . " from " . $this->_getTable() . " where " . $byWhat . " = '" . $this->escape_string($value) . "';";
        $this->lastSql = $sql;
        $result = $this->query($sql);
        if (!$result) {
            return FALSE;
        }
        return $this->fetch_object($result);
    }

    /**
     * 根据id删除记录
     * @param object $id [optional] id的值
     * @return 删除的结果
     */
    public function deleteById($id = 0)
    {
        if ($id == 0) {
            return false;
        }
        $sql = "delete from " . $this->_getTable() . " where id = '" . $id . "';";
        $this->lastSql = $sql;
        return $this->query($sql);

    }

    /**
     * 根据特定条件删除
     * @param object $byWhat [optional] 指定条件
     * @param object $value [optional]  条件内容
     * @return 删除的结果
     */
    public function deleteBy($byWhat, $value)
    {
        if (!isset($value) || !isset($byWhat)) {
            return false;
        }
        $sql = "delete from " . $this->_getTable() . " where " . $byWhat . " = '" . $value . "';";
        $this->lastSql = $sql;

        return $this->query($sql);

    }

    /**
     * 新增记录
     * @param object $data [optional] 新增数据数组
     * @return 新增的结果
     */
    public function insertTable($data = array())
    {
        $dc = count($data);
        if ($dc == 0) {
            return false;
        }
        $key_handle = array_keys($data);
        $val_handle = array_values($data);
        for ($i = 0; $i < $dc; $i++) {
            $keys[$i] = trim($key_handle[$i]);
            $vals[$i] = "'" . $this->escape_string($val_handle[$i]) . "'";
        }
        $sql = "insert into " . $this->_getTable() . " (" . implode(", ", $keys) . ") values (" . implode(", ", $vals) . ");";
        $this->lastSql = $sql;
        return $this->insert($sql);


    }

    /**
     * 更新表格
     * @param object $id [optional] 主键的值
     * @param object $data [optional] 要更新的数据
     * @return 更新结果
     */
    public function updateTable($id = '', $data = array())
    {
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
                $qm .= trim($key_handle[$i]) . " = '" . $this->escape_string($val_handle[$i]) . "' ";
            } else {
                $qm .= trim($key_handle[$i]) . " = '" . $this->escape_string($val_handle[$i]) . "', ";
            }
        }
        $sql = " update " . $this->_getTable() . " set " . $qm . " where id = '" . $id . "';";
        //跟踪sql
        $this->lastSql = $sql;
        return $this->query($sql);
    }

    /**
     * 获取最后的sql语句
     * @return 最后的sql语句
     */
    public function getLastSql()
    {
        return $this->lastSql;
    }

    /**
     * 重载
     * @param object $name
     * @param object $args
     * @return
     */
    public function __call($name, $args)
    {

        if (strstr($name, 'findBy')) {
            $byWhat = str_replace("findBy", '', $name);
            return $this->findBy($byWhat, $args['0']);
        }
        if (strstr($name, 'deleteBy')) {
            $byWhat = str_replace("deleteBy", '', $name);

            return $this->deleteBy($byWhat, $args['0']);
        }

    }
}
