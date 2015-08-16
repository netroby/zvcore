<?php
namespace netroby\zvcore;

class zcache
{
    //缓存类句柄

    private static $zcache = null;
    //缓存文件目录
    private $_cache_base_dir = './cache/';

    /**
     * 控制访问权限
     */

    private function __construct()
    {
    }

    /**
     * 取唯一实例
     * @return object
     */

    private static function getInstant()
    {
        if (null === self::$zcache) {
            self::$zcache = new zcache();
        }
        return self::$zcache;
    }

    /**
     * 设置缓存方法
     * @param string $key [optional]
     * @param string $val [optional]
     * @param integer $lifetime [optional]
     * @throws \InvalidArgumentException
     */

    public static function set($key = null, $val = null, $lifetime = 0)
    {
        if (null === $key) {
            throw new \InvalidArgumentException('你没有弄错吧,不提供Key我怎么缓存啊！');
        } elseif (0 === $lifetime) {
            throw new \InvalidArgumentException('我靠，你有没有搞错，没有设定有效时间，你缓存个屁呀！！');
        }
        $zcache = self::getInstant();
        //md5 Key的值
        $zcache->setCache(md5($key), $val, $lifetime);
    }

    /**
     * 获取缓存
     * @param string $key [optional]
     * @return  mixed
     * @throws \InvalidArgumentException
     */

    public static function get($key = null)
    {
        if (null === $key) {
            throw new \InvalidArgumentException('操，你给我空的Key,我去哪里给你找东西啊。');
        }
        $zcache = self::getInstant();
        //同样要先加md5然后才能正常取到值
        return $zcache->getCache(md5($key));
    }

    /**
     * 设置缓存
     * @param string $key
     * @param string $val
     * @param integer $lifetime
     * @throws \InvalidArgumentException
     */

    public function setCache($key, $val, $lifetime)
    {
        //清除旧的缓存文件
        $this->remove($key);

        $exptime = $lifetime + time();
        //缓存文件
        $cacheFile = $this->_cache_base_dir . $key;
        //缓存头文件
        $metaCacheFile = $cacheFile . '.meta';
        //写入缓存
        $statA = file_put_contents($cacheFile, serialize($val));
        //写入缓存头文件
        $statB = file_put_contents($metaCacheFile, $exptime);
        if ($statA === 0 || $statB === 0) {
            throw new \InvalidArgumentException('写入缓存文件出错！');
        }
    }

    /**
     * 删除缓存
     * @param object $key
     */

    public function remove($key)
    {

        //缓存文件
        $cacheFile = $this->_cache_base_dir . $key;
        //缓存头文件
        $metaCacheFile = $cacheFile . '.meta';

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
     * @return mixed
     */

    public function getCache($key)
    {
        //缓存文件
        $cacheFile = $this->_cache_base_dir . $key;
        //缓存文件头信息
        $metaCacheFile = $cacheFile . '.meta';

        if (file_exists($metaCacheFile)) {
            $exptime = file_get_contents($metaCacheFile);
            if ($exptime < time()) {
                $this->remove($key);
                return false;
            }
        }


        if (file_exists($cacheFile)) {
            $rf = file_get_contents($cacheFile);
            return unserialize($rf);
        }
        return false;
    }

    /**
     * 删除失效的缓存
     * @return null
     */

    public function clearCache()
    {
        if ($handle = opendir($this->_cache_base_dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    //缓存文件名
                    $trueFile = $this->_cache_base_dir . $file;
                    //缓存文件头信息
                    $metaTrueFile = $trueFile . '.meta';
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
