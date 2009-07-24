<?php 
/**
 * 助手类
 * 提供了常用的辅助功能，如操作成功提示，操作失败提示，操作错误返回，调试功能等！
 * 此类是静态类，不需要初始化就可以直接调用！
 * 例如：helper::success("恭喜你，登录成功了");
 */
class helper {

    /**
     * 调试信息
     * 根据提供的对象，数组等，打印调试信息
     * @param object $dd 需要调试的内容，可以是数组，对象和其他的
     */
     
    public static function dump($dd) {
    
        echo "<pre style=\"font-size:10pt;border:#003377 1px dashed;padding:10px;\">";
        var_dump($dd);
        echo "</pre>";
    }
    /**
     * 操作成功提示信息
     * 只需要指定信息内容，跳转地址，自动调用消息模版输出提示信息
     * @param object $msg 成功提示信息
     * @param object $url 返回跳转的url
     */
     
    public static function success($msg, $url = null) {
    
        $msgtype = "Oh Yeah";
        if (null != $url) {
            $jumpTo = $url;
        } else {
            $jumpTo = $_SERVER['HTTP_REFERER'];
        }
        $msg = $msg;
        include (zvc_path."/tpl/zvcmsg.html");
        exit(1);
    }

    
    /**
     * 操作错误提示信息
     * 只需要指定信息内容，跳转地址，自动调用消息模版输出提示信息
     * @param object $msg 操作错误的提示信息
     * @param object $url 返回跳转的url
     */
    public static function error($msg, $url = null) {
    
        $msgtype = "oh No!";
        if (null != $url) {
            $jumpTo = $url;
        } else {
            $jumpTo = $_SERVER['HTTP_REFERER'];
        }
        $msg = $msg;
        include (zvc_path."/tpl/zvcmsg.html");
        exit(1);
    }

    
    /**
     * 错误返回
     * 只需要指定信息内容，跳转地址，自动调用消息模版输出提示信息
     * @param object $msg 错误返回提示信息
     */
    public static function goback($msg) {
    
        echo '<html>';
        echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
        echo '<head>';
        echo '<title>Sorry</title>';
        echo '<body><script type="text/javascript" >';
        echo 'alert("'.$msg.'");';
        echo 'history.go(-1);';
        echo '</script></body></html>';
        exit(1);
    }
    /**
     * 设置cookie
     * 需要提供cookie名，cookie的值,过期时间(非必须),域名(非必须)
     * @param object $name    cookie名
     * @param object $cookie    cookie的值
     * @param object $exp    cookie失效的时间
     */
    public static function zvc_set_cookie($name, $cookie, $exp = 3600) {
        setcookie($name, $cookie, time() + $exp, '/');
    }
    /**
     * 删除cookie
     * 需要提供cookie名
     * @param object $name    cookie名
     */
    public static function zvc_delete_cookie($name) {
        setcookie($name, '');
    }
    /**
     * 检查.htaccess文件是否存在，不存在，则手动给它写入
     */
    public static function checkHTAC() {
        $protected_dir = array('cache', 'config', 'controllers', 'data', 'lang', 'views', 'zvcore');
        foreach ($protected_dir as $val) {
            if (is_dir($val) && !file_exists($val."/.htaccess")) {
                $protected_put = "Deny from all ";
                file_put_contents($val."/.htaccess", $protected_put);
            }
        }
        
    }
    /**
     * 转向新的地址
     */
    public static function redirect($url = "/") {
        header('location:'.$url);
        exit(1);
    }
    
    /**
     * Trace抛出
     */
    public static function traceOut(Exception $e) {
        if (!file_exists('./config/global.php')) {
            self::throwTrace($e);
        } else {
            $global_setting = registry::getRegistry('global');
            
            switch ($global_setting['run_mode']) {
                case 'product':
                    self::error($e->getMessage(), $_SERVER['HTTP_REFERER']);
                    break;
                case 'dev':
                    self::throwTrace($e);
                    break;
            }
            
        }
    }
    public static function throwTrace(Exception $e) {
        echo '<html><head><title>出错啦！</title></head><body>';
        echo "<h3>出错信息:</h3><pre>";
        echo $e->getMessage()."&nbsp;(出错代码:".$e->getCode().")";
        echo "</pre><h3>出错位置:</h3><pre>";
        echo $e->getFile()."&nbsp;";
        echo "第".$e->getLine()."行</h3>";
        echo "</pre><h3>出错trace信息</h3><pre>";
        echo $e->getTraceAsString();
        echo "</pre><h3>REQUEST信息</h3><pre>";
        echo self::dump($_REQUEST);
        echo "</pre><h3>POST信息</h3><pre>";
        echo self::dump($_POST);
        echo '</pre></body></html>';
    }
}
