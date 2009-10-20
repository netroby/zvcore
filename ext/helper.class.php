<?php 
/**
 * ������
 * �ṩ�˳��õĸ������ܣ�������ɹ���ʾ������ʧ����ʾ���������󷵻أ����Թ��ܵȣ�
 * �����Ǿ�̬�࣬����Ҫ��ʼ���Ϳ���ֱ�ӵ��ã�
 * ���磺helper::success("��ϲ�㣬��¼�ɹ���");
 */
class helper {

    /**
     * ������Ϣ
     * �����ṩ�Ķ�������ȣ���ӡ������Ϣ
     * @param object $dd ��Ҫ���Ե����ݣ����������飬�����������
     */
     
    public static function dump($dd) {
    
        echo "<pre style=\"font-size:10pt;border:#003377 1px dashed;padding:10px;\">";
        var_dump($dd);
        echo "</pre>";
    }
    /**
     * ��ʾ��Ϣ
     * ֻ��Ҫָ����Ϣ���ݣ���ת��ַ���Զ�������Ϣģ�������ʾ��Ϣ
     * @param string $msg �ɹ���ʾ��Ϣ
     * @param string $url ������ת��url
     * @param string $enableRedirect �����Ƿ�������ת��Ĭ������ :enableRedirect ,�����ã�disableRedirect
     */
     
    public static function message($msg, $url = "", $enableRedirect = "enableRedirect") {
    
        if (! empty($url)) {
            $jumpTo = $url;
        } else {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $jumpTo = $_SERVER['HTTP_REFERER'];
            } else {
                $jumpTo = "/";
            }
            
        }
        include (zvc_path."/tpl/zvcmsg.html");
        exit(1);
    }
    /**
     * ��ǰ�����˵�������ʾ
     * ����Cotroller��Action��ƥ��URL��ַ
     * @param object $controller
     * @param object $action
     * @param object $activeClass [optional]
     * @return
     */
    public static function currentNav($controller, $action = null, $activeClass = "active") {
        if (!isset($action) || empty($action)) {
            $action = "index";
        }
        if (is_array($action)) {
            if ($_REQUEST["controller"] == $controller && in_array($_REQUEST["action"], $action)) {
                echo " class=\"active\" ";
            }
        } else {
            if ($_REQUEST["controller"] == $controller && $_REQUEST["action"] == $action) {
                echo " class=\"active\" ";
            }
        }
        
    }
    /**
     * ����cookie
     * ��Ҫ�ṩcookie����cookie��ֵ,����ʱ��(�Ǳ���),����(�Ǳ���)
     * @param object $name    cookie��
     * @param object $cookie    cookie��ֵ
     * @param object $exp    cookieʧЧ��ʱ��
     */
    public static function zvc_set_cookie($name, $cookie, $exp = 3600) {
        setcookie($name, $cookie, time() + $exp, '/');
    }
    /**
     * ɾ��cookie
     * ��Ҫ�ṩcookie��
     * @param object $name    cookie��
     */
    public static function zvc_delete_cookie($name) {
        setcookie($name, '');
    }
    /**
     * ���.htaccess�ļ��Ƿ���ڣ������ڣ����ֶ�����д��
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
     * ת���µĵ�ַ
     */
    public static function redirect($url = "/") {
        header('location:'.$url);
        exit(1);
    }
    /**
     * Ԥ��������
     * @param object $array [optional]
     * @return
     */
    public static function preArray($array = array()) {
        foreach ($array as $key=>$val) {
            if (is_numeric($val)) {
                $array[$key] = intval($val);
            }
            if (is_string($val)) {
                $array[$key] = trim($val);
            }
            
        }
        return $array;
    }
    /**
     * ���gbk���룬��ת����utf-8
     * @param string $string
     * @return string $string
     */
    public static function gbk2utf8($string) {
        if (mb_check_encoding($string, "gbk")) {
            return mb_convert_encoding($string, "utf8", "gbk");
        } else {
            return $string;
        }
        
    }
    /**
     * ���utf8���룬ת����gbk
     * @param string $string
     * @return  string $string
     */
    public static function utf82gbk($string) {
        if (mb_check_encoding($string, "utf8")) {
            return mb_convert_encoding($string, "gbk", "utf8");
        } else {
            return $string;
        }
    }
    /**
     * Tracer������Ϣ
     * @param object $e
     * @return
     */
    public static function traceOut(Exception $e) {
        if (!file_exists('./config/global.php')) {
            self::throwTrace($e);
        } else {
            $global_setting = registry::getRegistry('global');
            
            switch ($global_setting['run_mode']) {
                case 'product':
                    self::message($e->getMessage(), null, 'disableRedirect');
                    break;
                case 'dev':
                    self::throwTrace($e);
                    break;
            }
            
        }
    }
    /**
     * ��ӡ������Ϣ
     * @param object $e
     * @return
     */
    public static function throwTrace(Exception $e) {
        echo '<html><head><title>��������</title></head><body>';
        echo "<h3>������Ϣ:</h3><pre>";
        echo $e->getMessage()."&nbsp;(�������:".$e->getCode().")";
        echo "</pre><h3>����λ��:</h3><pre>";
        echo $e->getFile()."&nbsp;";
        echo "��".$e->getLine()."��</h3>";
        echo "</pre><h3>����trace��Ϣ</h3><pre>";
        echo $e->getTraceAsString();
        echo "</pre><h3>REQUEST��Ϣ</h3><pre>";
        echo self::dump($_REQUEST);
        echo "</pre><h3>POST��Ϣ</h3><pre>";
        echo self::dump($_POST);
        echo '</pre></body></html>';
    }
}
