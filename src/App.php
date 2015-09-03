<?php
namespace netroby\zvcore;

/**
 * 框架主类
 * 功能是通过调用其他辅助类库，执行调用应用程序类库和方法等
 */
class App
{
    /**
     * 应用程序入口
     * 负责调用其他类库和调用应用程序类库
     * @throws \RuntimeException
     */

    public function run()
    {
        $urlParams = explode('/', $_SERVER['PATH_INFO']);
        $action = array_pop($urlParams);
        $controller = implode('\\', $urlParams);
        //初始化运行
        $c = new $controller();
        //调用方法
        $c->$action();

    }

}
