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
     * 魔术全局变量
     * get_magic_quotes_gpc()的值是否为空。
     * @var string get_magic_quotes_gpc()全局变量
     */

    public $magicQuote;
    /**
     * 模版文件
     * 应用程序的模版文件
     * @var string 模版文件
     */

    public $tplFileName;
    /**
     * URL请求
     * 例如：http://localhost/public-login.html
     * @var string 请求URL
     */

    public $req_uri;
    /**
     * 页面缓存
     * 设置为true是开启缓存，设置为false是关闭缓存
     * @var string 缓存设置（默认不缓存）
     */
    public $_cacheThis = false;
    /**
     * 页面缓存时间
     * 默认设置为5秒，页面缓存有效期限为5秒钟
     * @var string 缓存时间（默认为5秒），需要开启缓存
     */
    public $_cacheTime = 5;
    /**
     * 模板样式设定
     * 默认为default
     * @var string 模板样式设定，默认为（default)
     */
    public $tpl_set = 'default';
    /**
     * 语言设置
     * 默认为gbk
     * @var string 语言样式设定，默认为gbk
     */
    public $lang_set = 'gbk';
    /**
     * @var string 语言文件
     */
    public $lang = array();
    /**
     * @var string 系统语言文件
     */
    public $syslang = array();
    /**
     * @var string 属性存储器；
     */
    public $varHandle = array();
    /**
     * @var string 当前目录;
     */
    public $crtdir = '';

    /**
     * 初始化
     * 设置变量，初始化参数，调用公用方法
     * @param object $actionName 方法名
     * @param object $db 数据库链接
     */

    public function __construct($actionName)
    {
        $viewCacheKey = $_SERVER['REQUEST_URI'];
        $viewCache = zcache::get($viewCacheKey);
        if ($viewCache) {
            echo $viewCache;
            exit();
        }
        //取全局配置变量

        $global_configs = registry::getRegistry('global');
        //如果有配置tpl_set变量，取tpl_set变量
        if (isset($global_configs['tpl_set'])) {
            $this->tpl_set = $global_configs['tpl_set'];
        }
        //如果有配置lang_set变量，取lang_set变量
        if (isset($global_configs['lang_set'])) {
            $this->lang_set = $global_configs['lang_set'];
        }

        //多模板样式检测
        $this->checkTplSet();
        //多语言检测
        $this->checkLangSet();
        //包含语言文件
        $langfile = './lang/' . $this->lang_set . '/lang.php';
        if (file_exists($langfile)) {
            $this->lang = include_once($langfile);
        }
        //包含系统语言文件
        $syslangfile = zvc_path . '/lang/' . $this->lang_set . '/sys.php';
        if (file_exists($syslangfile)) {
            $this->syslang = include_once($syslangfile);
        }


        //设置uri请求变量
        $this->req_uri = $_SERVER['REQUEST_URI'];
        //获取精确的类名
        $className = get_class($this);
        //获取文件的目录
        $fileDir = str_replace('Action', '', $className);
        //前缀目录
        $fullFileDir = './views/' . $this->tpl_set . '/' . $fileDir . '/';
        //完整的文件名
        $fullFileName = $fullFileDir . $actionName . '.html';
        //设置模版文件名
        $this->tplFileName = $fullFileName;
        //get_magic_quote_gpc();
        $this->magicQuote = get_magic_quotes_gpc();

        //如果有_init公用方法，则进行调用
        if (method_exists($this, '_init')) {
            $this->_init();
        }
    }

    /**
     * 检查语言设定
     */
    public function checkLangSet()
    {
        if (isset($_REQUEST['lang'])) {
            $lang_set = $this->doLangSet($_REQUEST['lang']);
        } elseif (isset($_COOKIE['zvc_lang_set'])) {
            $lang_set = $_COOKIE['zvc_lang_set'];
        }
        if (isset($lang_set) && !empty($lang_set)) {
            $this->lang_set = $lang_set;
        }
    }

    /**
     * 设定语言
     */
    public function doLangSet($lang_set = 'gbk')
    {
        $lang_set_dir = './lang/' . $lang_set;
        if (false == is_dir($lang_set_dir)) {
            $lang_set = 'gbk';
        }
        helper::zvc_set_cookie('zvc_lang_set', $lang_set);
        return $lang_set;
    }

    /**
     * 模板样式设定
     * 通过cookie或者session设置默认模板样式
     */
    public function checkTplSet()
    {
        if (isset($_REQUEST['zts'])) {
            $tpl_set = $this->doTplSet($_REQUEST['zts']);
        } elseif (isset($_COOKIE['zvc_tpl_set'])) {
            $tpl_set = $_COOKIE['zvc_tpl_set'];
        }
        if (isset($tpl_set) && !empty($tpl_set)) {
            $this->tpl_set = $tpl_set;
        }
    }

    /**
     * 设定模板主题
     * 设置任意的模板样式主题至cookie中
     * @param object $tpl_set 模板样式名
     */
    public function doTplSet($tpl_set = 'default')
    {
        $tpl_set_dir = './views/' . $tpl_set;
        if (false == is_dir($tpl_set_dir)) {
            $tpl_set = 'default';
        }
        helper::zvc_set_cookie('zvc_tpl_set', $tpl_set);
        return $tpl_set;
    }


    /**
     * 赋值操作
     * 根据提供的参数和值，将他们一一赋值
     * @param object $var 参数
     * @param object $val 值
     */
    public function __set($var, $val)
    {
        $this->varHandle[$var] = $val;
    }

    public function __get($var)
    {
        //数据库链接
        if ($var == 'db') {
            if (!isset($this->varHandle[$var])) {
                $this->varHandle[$var] = App::db();
            }
        }
        if (isset($this->varHandle[$var])) {
            return $this->varHandle[$var];
        } else {
            return null;
        }
    }

    /**
     * 显示视图模版
     * 如果没有指定模版文件，则加载默认模版
     * @param object $tplfile 模版文件名
     */

    public function display($tplfile = null)
    {

        $this->fetchTpl($tplfile);

    }

    /**
     * 加载模版
     * 模版不存在，则报出错误提示信息
     * @param object $tplfile 模版文件
     */

    public function fetchTpl($tplfile = null)
    {


        if (null != $tplfile) {
            //修正可能存在的不法访问
            $tplfile = str_replace('../', '', $tplfile);
            $tplfile = str_replace('./', '', $tplfile);
            $tplfile = str_replace('http://', '', $tplfile);
            include('./views/' . $this->tpl_set . '/' . $tplfile . '.html');
        } else {
            if (!file_exists($this->tplFileName)) {
                throw new Exception($this->syslang['can_not_fetch_tpl_file']);
            }
            include($this->tplFileName);
        }
        //缓存
        $this->saveCache();
    }

    /**
     * 包含模版文件
     * 根据提供的模版文件名，包含模版文件
     * @param object $tplname 模版文件
     */

    public function tplRequire($tplname)
    {

        include('./views/' . $this->tpl_set . '/' . $tplname . '.html');
    }

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

    /**
     * 输出并换行
     * @param object $str [optional]
     * @return
     */
    public function printout($str = ' ')
    {
        echo $str . '<br />\n';
    }

    /**
     * 构建验证码
     * 生成图片验证码直接输出
     */

    public function buildVerify($storeName = 'verify')
    {
        Header('Content-type: image/PNG');
        $im = imagecreate(80, 20); //创建画布
        $bgcolor = ImageColorAllocate($im, 255, 255, 255); //背景颜色
        $TTFfont = zvc_path . '/tpl/mvboli.ttf'; //使用的TTF字体
        $fontColor = imagecolorallocate($im, 0, 0, 220); //字体颜色
        $noiseColor = imagecolorallocate($im, rand(100, 200), rand(100, 200), rand(100, 200)); //噪音颜色
        for ($i = 0; $i < 350; $i++) {
            imagesetpixel($im, mt_rand(0, 80), mt_rand(0, 20), $noiseColor); //画噪点
        }
        $secStr = ''; //验证码存储变量
        for ($i = 0; $i < 4; $i++) {
            $str = rand(0, 9);
            $ron = rand(0, 23);
            ImageTTFText($im, 16, $ron, 6 + ($i * 16), 19, $fontColor, $TTFfont, $str);
            $secStr .= $str; //叠加变量
        }
        $_SESSION[$storeName] = md5($secStr); //存储验证码
        ImagePNG($im);
        ImageDestroy($im);
    }

    /**
     * 自动校验验证码
     * @param object $check
     * @param object $storeName [optional]
     * @return
     */
    public function checkVerify($check, $storeName = 'verify')
    {
        $verifyStored = $_SESSION[$storeName];
        $_SESSION[$storeName] = '';
        if (md5($check) != $verifyStored) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 安全令牌
     * 使用时先调用一下此方法
     * 然后在需要输出的位置添加一个表单项
     * <input type='text' name='safetoken' value='<?php echo $_SESSION['safeToken']; ?>' />
     */

    public function safeToken()
    {
        $sdt = '';
        for ($i = 0; $i < 6; $i++) {
            $str = rand(97, 122);
            $sdt .= chr($str);
        }
        $_SESSION['safeToken'] = md5($sdt);
        $_SESSION['tokenTime'] = time();
        echo '<input type='hidden' name='safeToken' value='' . $_SESSION['safeToken'] . '' />';
    }

    /**
     * 检查安全令牌
     * 检查post过来的表单数据是否为有效期内的表单
     */

    public function checkToken()
    {
        $token = $_POST['safeToken'];
        if (!isset($_SESSION['safeToken']) || $_SESSION['safeToken'] != $token) {
            unset($_SESSION['safeToken']);
            return false;
        } else {
            unset($_SESSION['safeToken']);
            return true;
        }
    }

    /**
     * 调用缓存处理
     * @return
     */
    public function saveCache()
    {
        if ($this->_cacheThis == true) {
            $val = ob_get_contents();
            $key = $_SERVER['REQUEST_URI'];
            zcache::set($key, $val, $this->_cacheTime);
        }
    }
}
