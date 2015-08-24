<?php
namespace netroby\zvcore;

/**
 * 框架主类
 * 功能是通过调用其他辅助类库，执行调用应用程序类库和方法等
 */
class App
{
    /**
     * 全局配置
     * 用来存储全局配置变量数组
     * @var array 全局配置变量
     */
    public $config = [];
    /**
     * URL请求变量
     * 例如：http://localhost/public-login.html
     * @var string url请求变量
     */
    public $req_url;

    /**
     * 数据库对象链接
     * @var object 数据库的对象链接
     */
    public static $db;

    /**
     * 初始化
     */
    public function __construct()
    {
        helper::checkHTAC();
    }

    /**
     * 应用程序入口
     * 负责调用其他类库和调用应用程序类库
     * @throws \RuntimeException
     */

    public function run()
    {

        try {
            //载入其他配置文件
            $this->loadConfig();

            //取uri并解析
            $this->get_req_url()->prase_uri();
            //导入控制器
            $class_file = $this->getClassFile($_REQUEST['controller']);
            //虽然可以用include，不过防止多次载入，还是用Include_once加以限制
            include_once($class_file);
            //设置类名和方法名
            $class_name = $_REQUEST['controller'] . "Action";
            $Act_name = $_REQUEST['action'];

            //初始化运行
            $Act = new $class_name($Act_name);
            //检查是否存在该方法
            $class_methods = get_class_methods($class_name);
            //如果方法不存在，并且不存在魔术重载，那么就提示访问的页面不存在
            if (!in_array($Act_name, $class_methods, true) && !in_array("__call", $class_methods, true)) {
                throw new \RuntimeException("您访问的页面不存在");
            }
            //调用方法
            $Act->$Act_name();

            //flush buffer
            ob_end_flush();
        } catch (\RuntimeException $e) {
            helper::traceOut($e);
        }

    }

    /**
     * 取得请求uri
     * 规范化url，或设置默认url为默认控制器index的index方法
     */

    public function get_req_url()
    {

        //如果REQUEST_URI变量存在，那么就直接取，不然就设为默认的index
        //URL:http://www.domain.com/app/index.php/test.html
        //Request URI sample :/app/index.php/test.html
        $request_uri = explode("/", $_SERVER['REQUEST_URI']);
        //Explode是从0开始的。那么取值自然要减去一位
        $cru = count($request_uri) - 1;
        $lru = $request_uri[$cru];
        //xhtml MP修正
        if (false !== strpos($lru, "?")) {
            $lru = false;
        }

        if (!empty($lru)) {
            $this->req_url = $lru;
        } else {

            if (!file_exists(".htaccess")) {

                header("location:index.php/index.html");
                exit(1);
            } else {
                $this->req_url = "index-index.html";
            }
        }
        return $this;
    }

    /**
     * 解析uri
     * 重组$_REQUEST变量数组
     */

    public function prase_uri()
    {

        $uri = $this->req_url;
        $hashtml = strripos($uri, ".html");
        if (false === $hashtml) {
            header("location:" . $_SERVER['REQUEST_URI'] . ".html");
        }
        $exp_uri = explode('.', $uri);
        $sub_uri = $exp_uri[0];
        //防止恶意的攻击，我们替换掉 /
        $sub_uri = str_replace('/', '', $sub_uri);
        $hasl = strpos($sub_uri, "-");
        //如果没有提供方法名，我们就默认为它设置index方法
        if (false === $hasl) {
            if (is_numeric($sub_uri)) {
                $_REQUEST['controller'] = 'index';
                $_REQUEST['action'] = $sub_uri;
            } else {
                $_REQUEST['controller'] = $sub_uri;
                $_REQUEST['action'] = "index";
            }

        } else {
            $esub = explode("-", $sub_uri);
            $count_esub = count($esub);
            $_REQUEST['controller'] = $esub[0];
            $_REQUEST['action'] = $esub[1];
            for ($i = 2; $i < $count_esub; $i++) {
                $k = $i + 1;
                if ($k >= $count_esub) {
                    $k = $count_esub;
                }
                if ($i % 2 === 0) {
                    $key = $esub[$i];
                    $val = $esub[$k];
                    $_REQUEST[$key] = $val;
                }
            }
        }
    }

    /**
     * 取控制器文件
     * 控制器文件的位置：./controllers/类名Aciton.class.php
     * @return string 控制器文件位置
     * @param object $className 类名
     */

    public function getClassFile($className)
    {
        $trueClassFile = "./controllers/" . $className . "Action.class.php";
        if (!file_exists($trueClassFile)) {
            throw new \RuntimeException("您访问的请求不存在");
        }
        return $trueClassFile;
    }

    /**
     * 导入配置文件
     */
    public function loadConfig()
    {
        $config_preload = array('db', 'global', 'path', 'secure', 'private', 'log', 'soap');
        $config_path = "./config/";
        foreach ($config_preload as $val) {
            $configFile = $config_path . $val . ".php";
            if (file_exists($configFile)) {
                registry::setRegistry($val, include($configFile));
            }
        }
    }

    public static function Model($modelName)
    {


        $modelDir = "./models/";
        $modelClassName = $modelName . "Model";
        $modelFile = $modelDir . $modelClassName . ".class.php";

        if (!file_exists($modelFile)) {
            throw new \RuntimeException("对不起，Model文件不存在！");
        } else {
            require $modelFile;
        }
        $md = new $modelClassName($modelName);

        return $md;
    }

    /**
     * 获取数据库对象
     * @param object $dbConfig
     * @return
     */
    public static function db($dbConfig = null)
    {
        if (null !== static::$db) {
            if (!$dbConfig) {
                static::$db = new db(registry::getRegistry('db'));
            } else {
                static::$db = new db($dbConfig);
            }

        }
        return static::$db;
    }
}
