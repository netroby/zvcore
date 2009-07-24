<?php 
class zcache {
	//缓存类句柄
    private static $_zcache = null;
	//数据库连接句柄
    private $db;
	//数据文件地址
    private $sqlite_db = "./cache/cache.sqlite";
	//禁止外部访问
    private function __construct() {
        if (!file_exists($this->sqlite_db)) {
            $db = $this->getConnection();
            //删除旧的表
            $sql = "drop table if exists cache;";
            $db->query($sql);
            //创建新表
            $sql = "CREATE TABLE `cache` (
	                `key` VARCHAR PRIMARY KEY  NOT NULL , 
	                `val` TEXT NOT NULL ,
	                `lifetime` INTEGER NOT NULL )";
	                
            $db->query($sql);
            if ("00000" != $db->errorCode()) {
                throw new Exception("建立数据表出错了啊".$db->errorInfo[2]);
            }
        }
    }
	//取连接信息
    private function getConnection() {
        if (null == $this->db) {
            $this->db = new PDO("sqlite:".$this->sqlite_db);
        }
        return $this->db;
    }
	//取实例
    private static function getInstant() {
        if (null == self::$_zcache) {
            self::$_zcache = new zcache();
        }
        return self::$_zcache;
    }
	//设置缓存
    public static function set($key = null, $val = null, $lifetime = 0) {
        if (null == $key) {
            throw new Exception("你没有弄错吧,不提供Key我怎么缓存啊！");
        } elseif (0 == $lifetime) {
            throw new Exception("我靠，你有没有搞错，没有设定有效时间，你缓存个屁呀！！");
        }
        $zcache = self::getInstant();
		//md5 Key的值
        $zcache->_setCache(md5($key), $val, $lifetime);
    }
	//获取缓存
    public static function get($key = null) {
        if (null == $key) {
            throw new Exception("操，你给我空的Key,我去哪里给你找东西啊。");
        }
        $zcache = self::getInstant();
        //同样要先加md5然后才能正常取到值
        return $zcache->_getCache(md5($key));
    }
	//设置缓存
    public function _setCache($key, $val, $lifetime) {
        $db = $this->getConnection();
        $this->_remove($key);
		$exptime=$lifetime+time();
        $sql = "insert into `cache` (`key`,`val`,`lifetime`) values ('".$key."','".sqlite_escape_string(serialize($val))."','".$exptime."');";
		$db->query($sql);
        if ("00000" != $db->errorCode()) {
            throw new Exception($db->errorInfo[2]);
        }
    }
	//删除缓存
    public function _remove($key) {
        $db = $this->getConnection();
        $sql = "delete from cache where key = '".$key."';";
        $db->query($sql);
    }
	//取缓存
    public function _getCache($key) {
        $this->clearCache();
        $db = $this->getConnection();
        $sql = "select * from cache where `key` = '".$key."';";
        $rs = $db->prepare($sql);
        if ($rs) {
            $rs->execute();
            $rt = $rs->fetch(PDO::FETCH_ASSOC);
            return unserialize($rt["val"]);
        } else {
            return null;
        }
    }
	//清除过期缓存
    public function clearCache() {
        $db = $this->getConnection();
        $sql = "delete from `cache` where lifetime < '".time()."';";
        $db->exec($sql);
    }
}