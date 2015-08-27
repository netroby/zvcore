<?php
namespace netroby\zvcore;

/**
 * 全局变量设置
 */
class Registry
{
    /**
     * 类的实例
     * @var object  类的实例
     */
    private static $_registry = null;
    /**
     * 变量数组
     * @var array 变量数组
     */
    private $reg = array();

    /**
     * 屏蔽初始化方法
     */
    private function __construct()
    {
    }

    /**
     * 获取类实例
     */
    private static function getInstant()
    {
        if (null == self::$_registry) {
            self::setInstant();
        }
        return self::$_registry;
    }

    /**
     * 设定类实例
     */
    private static function setInstant()
    {
        if (null == self::$_registry) {
            self::$_registry = new registry();
        }
    }

    /**
     * 设置变量
     * @param object $key 变量
     * @param object $val 变量的值
     */
    public static function setRegistry($key, $val)
    {
        $registry = self::getInstant();
        $registry->registrySet($key, $val);
    }

    /**
     * 获取变量
     * @param object $key 变量的key
     */
    public static function getRegistry($key)
    {
        $registry = self::getInstant();
        if ($registry->keyExists($key)) {
            return $registry->registryGet($key);
        } else {
            return null;
        }
    }

    /**
     * 设置变量
     * 仅供类里的方法调用
     * @param object $key 变量
     * @param object $val 变量的值
     */
    private function registrySet($key, $val)
    {
        $this->reg[$key] = $val;
    }

    /**
     * 获取变量
     * @param object $key 变量名
     */
    private function registryGet($key)
    {
        return $this->reg[$key];
    }

    /**
     * 变量是否存在
     * @param object $key 变量名
     */
    private function keyExists($key)
    {
        return array_key_exists($key, $this->reg);
    }
}
