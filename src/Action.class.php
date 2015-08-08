<?php 
/**
 * ���������� 
 * ./controllers/��������Action.class��Ļ���
 * ֻ�漰ģ�棬ҳ�滺��ʹ�ã��߼�������������Action����Ĵ�����
 * ����Ӧ�ó�����Ҫ�õ��Ĺ��÷����Ͳ��������ڻ�����������֡�
 */
class Action {

    /**
     * ħ��ȫ�ֱ���
     * get_magic_quotes_gpc()��ֵ�Ƿ�Ϊ�ա�
     * @var string get_magic_quotes_gpc()ȫ�ֱ���
     */
     
    public $magicQuote;
    /**
     * ģ���ļ�
     * Ӧ�ó����ģ���ļ�
     * @var string ģ���ļ�
     */
     
    public $tplFileName;
    /**
     * URL����
     * ���磺http://localhost/public-login.html
     * @var string ����URL
     */
     
    public $req_uri;
    /**
     * ҳ�滺��
     * ����Ϊtrue�ǿ������棬����Ϊfalse�ǹرջ���
     * @var string �������ã�Ĭ�ϲ����棩
     */
    public $_cacheThis = false;
    /**
     * ҳ�滺��ʱ��
     * Ĭ������Ϊ5�룬ҳ�滺����Ч����Ϊ5����
     * @var string ����ʱ�䣨Ĭ��Ϊ5�룩����Ҫ��������
     */
    public $_cacheTime = 5;
    /**
     * ģ����ʽ�趨
     * Ĭ��Ϊdefault
     * @var string ģ����ʽ�趨��Ĭ��Ϊ��default)
     */
    public $tpl_set = "default";
    /**
     * ��������
     * Ĭ��Ϊgbk
     * @var string ������ʽ�趨��Ĭ��Ϊgbk
     */
    public $lang_set = "gbk";
    /**
     * @var string �����ļ�
     */
    public $lang = array();
    /**
     * @var string ϵͳ�����ļ�
     */
    public $syslang = array();
    /**
     * @var string ���Դ洢����
     */
    public $varHandle = array();
    /**
     * @var string ��ǰĿ¼;
     */
    public $crtdir = "";
    /**
     * ��ʼ��
     * ���ñ�������ʼ�����������ù��÷���
     * @param object $actionName ������
     * @param object $db ���ݿ�����
     */
     
    public function __construct($actionName) {
        $viewCacheKey = $_SERVER['REQUEST_URI'];
        $viewCache = zcache::get($viewCacheKey);
        if ($viewCache) {
            echo $viewCache;
            exit();
        }
        //ȡȫ�����ñ���
        
        $global_configs = registry::getRegistry('global');
		//���������tpl_set������ȡtpl_set����
        if (isset($global_configs["tpl_set"])) {
            $this->tpl_set = $global_configs["tpl_set"];
        }
		//���������lang_set������ȡlang_set����
        if(isset($global_configs["lang_set"])){
        	$this->lang_set=$global_configs["lang_set"];
        }
        
        //��ģ����ʽ���
        $this->checkTplSet();
        //�����Լ��
        $this->checkLangSet();
        //���������ļ�
        $langfile = "./lang/".$this->lang_set."/lang.php";
        if (file_exists($langfile)) {
            $this->lang = include_once ($langfile);
        }
        //����ϵͳ�����ļ�
        $syslangfile = zvc_path."/lang/".$this->lang_set."/sys.php";
        if (file_exists($syslangfile)) {
            $this->syslang = include_once ($syslangfile);
        }

        
        //����uri�������
        $this->req_uri = $_SERVER['REQUEST_URI'];
        //��ȡ��ȷ������
        $className = get_class($this);
        //��ȡ�ļ���Ŀ¼
        $fileDir = str_replace('Action', '', $className);
        //ǰ׺Ŀ¼
        $fullFileDir = "./views/".$this->tpl_set."/".$fileDir."/";
        //�������ļ���
        $fullFileName = $fullFileDir.$actionName.".html";
        //����ģ���ļ���
        $this->tplFileName = $fullFileName;
        //get_magic_quote_gpc();
        $this->magicQuote = get_magic_quotes_gpc();
        
        //�����_init���÷���������е���
        if (method_exists($this, "_init")) {
            $this->_init();
        }
    }
    /**
     * ��������趨
     */
    public function checkLangSet() {
        if (isset($_REQUEST['lang'])) {
            $lang_set = $this->doLangSet($_REQUEST['lang']);
        } elseif (isset($_COOKIE['zvc_lang_set'])) {
            $lang_set = $_COOKIE['zvc_lang_set'];
        } 
        if (isset($lang_set) && !empty( $lang_set)) {
            $this->lang_set = $lang_set;
        }
    }
    /**
     * �趨����
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
     * ģ����ʽ�趨
     * ͨ��cookie����session����Ĭ��ģ����ʽ
     */
    public function checkTplSet() {
        if (isset($_REQUEST['zts'])) {
            $tpl_set = $this->doTplSet($_REQUEST['zts']);
        } elseif (isset($_COOKIE['zvc_tpl_set'])) {
            $tpl_set = $_COOKIE['zvc_tpl_set'];
        } 
        if (isset($tpl_set) && !empty( $tpl_set)) {
            $this->tpl_set = $tpl_set;
        }
    }
    /**
     * �趨ģ������
     * ���������ģ����ʽ������cookie��
     * @param object $tpl_set ģ����ʽ��
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
     * ��ֵ����
     * �����ṩ�Ĳ�����ֵ��������һһ��ֵ
     * @param object $var ����
     * @param object $val ֵ
     */
    public function __set($var, $val) {
        $this->varHandle[$var] = $val;
    }
    public function __get($var) {
        //���ݿ�����
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
     * ��ʾ��ͼģ��
     * ���û��ָ��ģ���ļ��������Ĭ��ģ��
     * @param object $tplfile ģ���ļ���
     */
     
    public function display($tplfile = null) {
    
        $this->fetchTpl($tplfile);
        
    }
    
    /**
     * ����ģ��
     * ģ�治���ڣ��򱨳�������ʾ��Ϣ
     * @param object $tplfile ģ���ļ�
     */
     
    public function fetchTpl($tplfile = null) {

    
        if (null != $tplfile) {
            //�������ܴ��ڵĲ�������
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
        //����
        $this->saveCache();
    }
    /**
     * ����ģ���ļ�
     * �����ṩ��ģ���ļ���������ģ���ļ�
     * @param object $tplname ģ���ļ�
     */
     
    public function tplRequire($tplname) {
    
        include ('./views/'.$this->tpl_set."/".$tplname.'.html');
    }
    
    /**
     * ��ֹ����������ļ�
     * �����û���¼ҳ�ȶ�̬ҳ�棬�Ͳ�����������˻��棬���Ծ�Ҫ�����˽�ֹ�ͻ��˻���
     */
     
    public function noCache() {
    
        //���ô�ҳ��Ĺ���ʱ��(�ø�������ʱ���ʾ)��ֻҪ���Ѿ���ȥ�����ڼ��ɡ�
        header("Expires: Mon, 26 Jul 1970 05:00:00 GMT");
        
        //���ô�ҳ�������������(�ø�������ʱ���ʾ)Ϊ���죬����ǿ���������ȡ��������
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        
        //���߿ͻ����������ʹ�û��棬HTTP 1.1 Э��
        header("Cache-Control: no-cache, must-revalidate");
        
        //���߿ͻ����������ʹ�û��棬����HTTP 1.0 Э��
        header("Pragma: no-cache");
    }
    /**
     * ���������
     * @param object $str [optional]
     * @return
     */
    public function printout($str = " ") {
        echo $str."<br />\n";
    }
    
    /**
     * ������֤��
     * ����ͼƬ��֤��ֱ�����
     */
     
    public function buildVerify($storeName = "verify") {
        Header("Content-type: image/PNG");
        $im = imagecreate(80, 20); //��������
        $bgcolor = ImageColorAllocate($im, 255, 255, 255); //������ɫ
        $TTFfont = zvc_path."/tpl/mvboli.ttf"; //ʹ�õ�TTF����
        $fontColor = imagecolorallocate($im, 0, 0, 220); //������ɫ
        $noiseColor = imagecolorallocate($im, rand(100, 200), rand(100, 200), rand(100, 200)); //������ɫ
        for ($i = 0; $i < 350; $i++) {
            imagesetpixel($im, mt_rand(0, 80), mt_rand(0, 20), $noiseColor); //�����
        }
        $secStr = ""; //��֤��洢����
        for ($i = 0; $i < 4; $i++) {
            $str = rand(0, 9);
            $ron = rand(0, 23);
            ImageTTFText($im, 16, $ron, 6 + ($i * 16), 19, $fontColor, $TTFfont, $str);
            $secStr .= $str; //���ӱ���
        }
        $_SESSION[$storeName] = md5($secStr); //�洢��֤��
        ImagePNG($im);
        ImageDestroy($im);
    }
    /**
     * �Զ�У����֤��
     * @param object $check
     * @param object $storeName [optional]
     * @return
     */
    public function checkVerify($check, $storeName = 'verify') {
        $verifyStored = $_SESSION[$storeName];
        $_SESSION[$storeName] = "";
        if (md5($check) != $verifyStored) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * ��ȫ����
     * ʹ��ʱ�ȵ���һ�´˷���
     * Ȼ������Ҫ�����λ�����һ������
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
     * ��鰲ȫ����
     * ���post�����ı������Ƿ�Ϊ��Ч���ڵı�
     */
     
    public function checkToken() {
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
     * ���û��洦��
     * @return
     */
    public function saveCache() {
        if ($this->_cacheThis == true) {
            $val = ob_get_contents();
            $key = $_SERVER['REQUEST_URI'];
            zcache::set($key, $val, $this->_cacheTime);
        }
    }
}
