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

    public $reqUri;
    /**
     * 页面缓存
     * 设置为true是开启缓存，设置为false是关闭缓存
     * @var string 缓存设置（默认不缓存）
     */
    public $cacheThis = false;
    /**
     * 页面缓存时间
     * 默认设置为5秒，页面缓存有效期限为5秒钟
     * @var string 缓存时间（默认为5秒），需要开启缓存
     */
    public $cacheTime = 5;
    /**
     * 模板样式设定
     * 默认为default
     * @var string 模板样式设定，默认为（default)
     */
    public $tplSet = 'default';
    /**
     * 语言设置
     * 默认为gbk
     * @var string 语言样式设定，默认为gbk
     */
    public $langSet = 'gbk';
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
     * @param string $actionName 方法名
     * @throws \InvalidArgumentException
     */

    public function __construct($actionName)
    {
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
        echo '<input type="hidden" name="safeToken" value="' . $_SESSION['safeToken'] . '" />';
    }

    /**
     * 检查安全令牌
     * 检查post过来的表单数据是否为有效期内的表单
     */

    public function checkToken()
    {
        $token = $_POST['safeToken'];
        if (array_key_exists('safeToken', $_SESSION) && $_SESSION['safeToken'] !== $token) {
            unset($_SESSION['safeToken']);
            return false;
        } else {
            unset($_SESSION['safeToken']);
            return true;
        }
    }

    /**
     * 调用缓存处理
     * @throws \InvalidArgumentException
     */
    public function saveCache()
    {
        if ($this->cacheThis === true) {
            $val = ob_get_contents();
            $key = $_SERVER['REQUEST_URI'];
            zcache::set($key, $val, $this->cacheTime);
        }
    }
}
