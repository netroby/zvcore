<?php 
/**
 * 控制器基类
 * 所有应用程序需要用到的公用方法和参数，都在基类里得以体现。
 */
class Action {

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
     * 数据库链
     * 初始化一个数据库类之后，就可以通过变量来传递这个链
     * @var string 数据库链
     */
     
    public $db;
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
    public $tpl_set = "default";
    /**
     * 语言设置
     * 默认为gbk
     * @var string 语言样式设定，默认为gbk
     */
    public $lang_set = "gbk";
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
    public $crtdir = "";
    /**
     * 初始化
     * 设置变量，初始化参数，调用公用方法
     * @param object $actionName 方法名
     * @param object $db 数据库链接
     */
     
    public function __construct($actionName, $db = null) {
        $viewCacheKey = $_SERVER['REQUEST_URI'];
        $viewCache = zcache::get($viewCacheKey);
        if ($viewCache != null) {
            echo $viewCache;
            exit(1);
        }
        //多模板样式检测
        $this->checkTplSet();
        //多语言检测
        $this->checkLangSet();
        //包含语言文件
        $langfile = "./lang/".$this->lang_set."/lang.php";
        if (file_exists($langfile)) {
            $this->lang = include_once ($langfile);
        }
        //包含系统语言文件
        $syslangfile = zvc_path."/lang/".$this->lang_set."/sys.php";
        if (file_exists($syslangfile)) {
            $this->syslang = include_once ($syslangfile);
        }

        
        //设置uri请求变量
        $this->req_uri = $_SERVER['REQUEST_URI'];
        //获取精确的类名
        $className = get_class($this);
        //获取文件的目录
        $fileDir = str_replace('Action', '', $className);
        //前缀目录
        $fullFileDir = "./views/".$this->tpl_set."/".$fileDir."/";
        //完整的文件名
        $fullFileName = $fullFileDir.$actionName.".html";
        //设置模版文件名
        $this->tplFileName = $fullFileName;
        //get_magic_quote_gpc();
        $this->magicQuote = get_magic_quotes_gpc();
        //检测设置数据库对象链接
        if (null != $db) {
            $this->db = $db;
        }
        //如果有_init公用方法，则进行调用
        if (method_exists($this, "_init")) {
            $this->_init();
        }
    }
    /**
     * 检查语言设定
     */
    public function checkLangSet() {
        if (isset($_REQUEST['lang'])) {
            $lang_set = $this->doLangSet($_REQUEST['lang']);
        } elseif (isset($_COOKIE['zvc_lang_set'])) {
            $lang_set = $_COOKIE['zvc_lang_set'];
        } elseif (!isset($lang_set)) {
            $lang_set = "gbk";
        }
        if (null != $lang_set) {
            $this->lang_set = $lang_set;
        }
    }
    /**
     * 设定语言
     */
    public function doLangSet($lang_set = "gbk") {
        $lang_set_dir = "./lang/".$lang_set;
        if (false == is_dir($lang_set_dir)) {
            $lang_set = "gbk";
        }
        helper::zvc_set_cookie('zvc_lang_set', $lang_set);
        return $lang_set;
    }
    /**
     * 模板样式设定
     * 通过cookie或者session设置默认模板样式
     */
    public function checkTplSet() {
        if (isset($_REQUEST['zts'])) {
            $tpl_set = $this->doTplSet($_REQUEST['zts']);
        } elseif (isset($_COOKIE['zvc_tpl_set'])) {
            $tpl_set = $_COOKIE['zvc_tpl_set'];
        } elseif (!isset($tpl_set)) {
            $tpl_set = "default";
        }
        if (null != $tpl_set) {
            $this->tpl_set = $tpl_set;
        }
    }
    /**
     * 设定模板主题
     * 设置任意的模板样式主题至cookie中
     * @param object $tpl_set 模板样式名
     */
    public function doTplSet($tpl_set = "default") {
        $tpl_set_dir = "./views/".$tpl_set;
        if (false == is_dir($tpl_set_dir)) {
            $tpl_set = "default";
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
    public function __set($var, $val) {
        $this->varHandle[$var] = $val;
    }
    public function __get($var) {
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
     
    public function display($tplfile = null) {
    
        $this->fetchTpl($tplfile);
        
    }
    
    /**
     * 加载模版
     * 模版不存在，则报出错误提示信息
     * @param object $tplfile 模版文件
     */
     
    public function fetchTpl($tplfile = null) {

    
        if (null != $tplfile) {
            //修正可能存在的不法访问
            $tplfile = str_replace("../", '', $tplfile);
            $tplfile = str_replace("./", '', $tplfile);
            $tplfile = str_replace("http://", '', $tplfile);
            include ('./views/'.$this->tpl_set."/".$tplfile.'.html');
        } else {
            if (!file_exists($this->tplFileName)) {
                throw new Exception($this->syslang['can_not_fetch_tpl_file']);
            }
            include ($this->tplFileName);
        }
        if ($this->_cacheThis == true) {
            $val = ob_get_contents();
            $key = $_SERVER['REQUEST_URI'];
            zcache::set($key,$val, $this->_cacheTime);
        }
    }
    /**
     * 包含模版文件
     * 根据提供的模版文件名，包含模版文件
     * @param object $tplname 模版文件
     */
     
    public function tplRequire($tplname) {
    
        include ('./views/'.$this->tpl_set."/".$tplname.'.html');
    }
    
    /**
     * 禁止浏览器缓存文件
     * 比如用户登录页等动态页面，就不能在浏览器端缓存，所以就要设置了禁止客户端缓存
     */
     
    public function noCache() {
    
        //设置此页面的过期时间(用格林威治时间表示)，只要是已经过去的日期即可。
        header("Expires: Mon, 26 Jul 1970 05:00:00 GMT");
        
        //设置此页面的最后更新日期(用格林威治时间表示)为当天，可以强制浏览器获取最新资料
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        
        //告诉客户端浏览器不使用缓存，HTTP 1.1 协议
        header("Cache-Control: no-cache, must-revalidate");
        
        //告诉客户端浏览器不使用缓存，兼容HTTP 1.0 协议
        header("Pragma: no-cache");
    }
    
    /**
     * 构建验证码
     * 生成图片验证码直接输出
     */
     
    public function buildVerify() {
        Header("Content-type: image/PNG");
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $image_x = 110;
        $image_y = 30;
        $im = imagecreate($image_x, $image_y);
        $bkg = ImageColorAllocate($im, 255, 245, 255);
        $fnt = zvc_path."/tpl/lsansd.ttf"; //显示的字体样式
        
        $font_color = imagecolorallocate($im, 25, 25, 255);
        //噪音
        $noise_num = 350;
        $line_num = 35;
        $noise_color = imagecolorallocate($im, 225, 0, 200);
        $line_color = imagecolorallocate($im, 225, 0, 200);
        for ($i = 0; $i < $noise_num; $i++) {
            imagesetpixel($im, mt_rand(0, $image_x), mt_rand(0, $image_y), $noise_color);
        }
        for ($i = 0; $i < $line_num; $i++) {
            imageline($im, mt_rand(0, $image_x), mt_rand(0, $image_y), mt_rand(0, $image_x), mt_rand(0, $image_y), $line_color);
        }
        
        //字符总数
        $total_str = strlen($str);
        $str2 = "";
        for ($i = 0; $i < 4; $i++) {
            $randnum = rand(1, $total_str - 1);
            $stn = substr($str, $randnum, 1);
            $rnn = rand(-25, 25);
            ImageTTFText($im, 20, $rnn, 5 + ($i * 25), 22, $font_color, $fnt, $stn);
            $str2 .= $stn;
        }
        $secstr = $str2;
        $_SESSION['verify'] = md5($secstr);
        ImagePNG($im);
        ImageDestroy($im);
    }
    /**
     * 自动校验验证码
     */
    public function checkVerify() {
        if (md5($_POST['verify']) != $_SESSION['verify']) {
            $_SESSION['verify'] = "";
            helper::goback("对不起，您没有输入验证码或者验证码不正确!");
            exit();
        }
    }
    /**
     * 安全令牌
     * 使用时先调用一下此方法
     * 然后在需要输出的位置添加一个表单项
     * <input type="text" name="safetoken" value="<?php echo $_SESSION['safeToken']; ?>" />
     */
    
    public function safeToken() {
        $sdt = "";
        for ($i = 0; $i < 6; $i++) {
            $str = rand(97, 122);
            $sdt .= chr($str);
        }
        $_SESSION['safeToken'] = md5($sdt);
        $_SESSION['tokenTime'] = time();
        echo '<input type="hidden" name="safeToken" value="'.$_SESSION['safeToken'].'" />';
        
    }
    /**
     * 检查安全令牌
     * 检查post过来的表单数据是否为有效期内的表单
     */
     
    public function checkToken() {
    
        $token = $_POST['safeToken'];
        $validtime = time() - 900;
        if (!$_SESSION['safeToken'] && $_SESSION['safeToken'] != $token && $_SESSION['tokenTime'] <= $validtime) {
            $_SESSION['safeToken'] = "";
            helper::goback("对不起，表单已失效!");
        }
    }
    
}
