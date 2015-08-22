<?php
namespace netroby\zvcore;

class HtmlCache
{
    public static $cacheDir = "./cache/html/";
    public static $metaDir = "./cache/html/meta/";

    public static function set($key, $val, $cacheTime = 60)
    {
        $file = self::getCacheFile($key);
        $meta = self::getMetaFile($key);
        file_put_contents($file, $val);
        file_put_contents($meta, time() + $cacheTime);

    }

    public static function load($key)
    {
        $file = self::getCacheFile($key);
        $meta = self::getMetaFile($key);
        if (!file_exists($file) || !file_exists($meta)) {
            return false;
        }
        $exptime = file_get_contents($meta);
        if ($exptime < time()) {
            return false;
        }
        return $file;
    }

    public static function destroy($key)
    {
        $file = self::getCacheFile($key);
        $meta = self::getMetaFile($key);
        unlink($file);
        unlink($meta);

    }

    public static function getCacheFile($key)
    {
        $cacheDir = self::$cacheDir;
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        return $cacheDir . md5($key);
    }

    public static function getMetaFile($key)
    {
        $metaDir = self::$metaDir;
        if (!is_dir($metaDir)) {
            mkdir($metaDir, 0777, true);
        }
        return self::$metaDir . md5($key);
    }

    public static function emptyCache()
    {

        self::clearCache(self::$cacheDir);
        self::clearCache(self::$metaDir);

    }

    /**
     * É¾³ýÊ§Ð§µÄ»º´æ
     * @return null
     */

    public function clearCache($dir)
    {
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $trueFile = $dir . $file;
                    if (is_file($trueFile) && file_exists($trueFile)) {
                        unlink($trueFile);
                    }
                }
            }
            closedir($handle);
            return true;
        }
        return false;
    }
}