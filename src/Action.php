<?php
namespace netroby\zvcore;

/**
 * 控制器基类
 * ./controllers/下面所有Action.class类的基类
 * 只涉及模版，页面缓存使用，逻辑及其他，不在Action基类的处理范畴
 * 所有应用程序需要用到的公用方法和参数，都在基类里得以体现。
 */
class Action
{
    /**
     * 页面缓存时间
     * 默认设置为5秒，页面缓存有效期限为5秒钟
     * @var string 缓存时间（默认为5秒），需要开启缓存
     */
    public $cacheTime = 5;




    /**
     * 禁止浏览器缓存文件
     * 比如用户登录页等动态页面，就不能在浏览器端缓存，所以就要设置了禁止客户端缓存
     */

    public function noCache()
    {

        //设置此页面的过期时间(用格林威治时间表示)，只要是已经过去的日期即可。
        header('Expires: Mon, 26 Jul 1970 05:00:00 GMT');

        //设置此页面的最后更新日期(用格林威治时间表示)为当天，可以强制浏览器获取最新资料
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

        //告诉客户端浏览器不使用缓存，HTTP 1.1 协议
        header('Cache-Control: no-cache, must-revalidate');

        //告诉客户端浏览器不使用缓存，兼容HTTP 1.0 协议
        header('Pragma: no-cache');
    }
}
