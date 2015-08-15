<?php
namespace netroby\zvcore;

class zcache
{
    //缓存类句柄

    private static $_zcache = null;
    //缓存文件目录
    private $_cache_base_dir = "./cache/";

    /**
     * 控制访问权限
     * @return
     */

    private function __construct()
    {
    }

    /**
     * 取唯一实例
     * @return instance
     */

    private static function getInstant()
    {
        if (null == self::$_zcache) {
            self::$_zcache = new zcache();
        }
        return self::$_zcache;
    }

    /**
     * 设置缓存方法
     * @param object $key [optional]
     * @param object $val [optional]
     * @param object $lifetime [optional]
     * @return
     */

    public static function set($key = null, $val = null, $lifetime = 0)
    {
        if (null == $key) {
            throw new Exception("你没有弄错吧,不提供Key我怎么缓存啊！");
        } elseif (0 == $lifetime) {
            throw new Exception("我靠，你有没有搞错，没有设定有效时间，你缓存个屁呀！！");
        }
        $zcache = self::getInstant();
        //md5 Key的值
        $zcache->_setCache(md5($key), $val, $lifetime);
    }

    /**
     * 获取缓存
     * @param object $key [optional]
     * @return
     */

    public static function get($key = null)
    {
        if (null == $key) {
            throw new Exception("操，你给我空的Key,我去哪里给你找东西啊。");
        }
        $zcache = self::getInstant();
        //同样要先加md5然后才能正常取到值
        return $zcache->_getCache(md5($key));
    }

    /**
     * 设置缓存
     * @param object $key
     * @param object $val
     * @param object $lifetime
     * @return
     */

    public function _setCache($key, $val, $lifetime)
    {
        //清除旧的缓存文件
        $this->_remove($key);

        $exptime = $lifetime + time();
        //缓存文件
        $cacheFile = $this->_cache_base_dir . $key;
        //缓存头文件
        $metaCacheFile = $cacheFile . ".meta";
        //写入缓存
        $statA = file_put_contents($cacheFile, serialize($val));
        //写入缓存头文件
        $statB = file_put_contents($metaCacheFile, $exptime);
        if ($statA == 0 || $statB == 0) {
            throw new Exception("写入缓存文件出错！");
        }
    }

    /**
     * 删除缓存
     * @param object $key
     * @return
     */

    public function _remove($key)
    {

        //缓存文件
        $cacheFile = $this->_cache_base_dir . $key;
        //缓存头文件
        $metaCacheFile = $cacheFile . ".meta";

        if (file_exists($cacheFile)) {

            $statA = unlink($cacheFile);
        }

        if (file_exists($metaCacheFile)) {
            $statB = unlink($metaCacheFile);
        }

    }

    /**
     * 取缓存
     * @param object $key
     * @return
     */

    public function _getCache($key)
    {
        //缓存文件
        $cacheFile = $this->_cache_base_dir . $key;
        //缓存文件头信息
        $metaCacheFile = $cacheFile . ".meta";

        if (file_exists($metaCacheFile)) {
            $exptime = file_get_contents($metaCacheFile);
            if ($exptime < time()) {
                $this->_remove($key);
                return false;
            }
        }


        if (file_exists($cacheFile)) {
            $rf = file_get_contents($cacheFile);
            return unserialize($rf);
        }
    }

    /**
     * 删除失效的缓存
     * @return null
     */

    public function clearCache()
    {
        if ($handle = opendir($this->_cache_base_dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    //缓存文件名
                    $trueFile = $this->_cache_base_dir . $file;
                    //缓存文件头信息
                    $metaTrueFile = $trueFile . ".meta";
                    //如果缓存文件头信息存在，下一步
                    if (file_exists($metaTrueFile)) {
                        //取过期时间
                        $exptime = file_get_contents($metaTrueFile);
                        //过期时间比对
                        if ($exptime < time()) {
                            //删除失效的文件
                            unlink($trueFile);
                            unlink($metaTrueFile);
                        }

                    }
                }
            }
            closedir($handle);
        }
    }
}
