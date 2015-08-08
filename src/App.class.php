<?php 
/**
* �������
* ������ͨ����������������⣬ִ�е���Ӧ�ó������ͷ�����
*/
class App {
	/**
	* ȫ������
	* �����洢ȫ�����ñ�������
	* @var array ȫ�����ñ���
	*/
	public $config = array();
	/**
	* URL�������
	* ���磺http://localhost/public-login.html
	* @var string url�������
	*/
	public $req_url;
	
	/**
	* ���ݿ��������
	* @var object ���ݿ�Ķ�������
	*/
	public static $db;
	/**
	* ��ʼ��
	*/
	public function __construct() {
		helper::checkHTAC();
	}
	
	/**
	* Ӧ�ó������
	* ��������������͵���Ӧ�ó������
	*/
	
	public function run() {
		
		try {
			
			
			//�������������ļ�
			$this->loadConfig();
			
			//ȡuri������
			$this->get_req_url()->prase_uri();
			//���������
			$class_file = $this->getClassFile($_REQUEST['controller']);
			//��Ȼ������include��������ֹ������룬������Include_once��������
			include_once ($class_file);
			//���������ͷ�����
			$class_name = $_REQUEST['controller']."Action";
			$Act_name = $_REQUEST['action'];
			
			//��ʼ������
			$Act = new $class_name($Act_name);
			//����Ƿ���ڸ÷���
			$class_methods = get_class_methods($class_name);
			//������������ڣ����Ҳ�����ħ�����أ���ô����ʾ���ʵ�ҳ�治����
			if (!in_array($Act_name, $class_methods) && !in_array("__call", $class_methods)) {
				throw new Exception("�����ʵ�ҳ�治����");
			}
			//���÷���
			$Act->$Act_name();
			
			//flush buffer
			ob_end_flush();
		}
		catch(Exception $e) {
			helper::traceOut($e);
		}
		
	}
	/**
	* ȡ������uri
	* �淶��url��������Ĭ��urlΪĬ�Ͽ�����index��index����
	*/
	
	public function get_req_url() {
		
		//���REQUEST_URI�������ڣ���ô��ֱ��ȡ����Ȼ����ΪĬ�ϵ�index
		//URL:http://www.domain.com/app/index.php/test.html
		//Request URI sample :/app/index.php/test.html
		$request_uri = explode("/", $_SERVER['REQUEST_URI']);
		//Explode�Ǵ�0��ʼ�ġ���ôȡֵ��ȻҪ��ȥһλ
		$cru = count($request_uri) - 1;
		$lru = $request_uri[$cru];
		//xhtml MP����
		if(strstr( $lru,"?")){
			$lru=false;
		}
		
		if (! empty($lru)) {			
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
	* ����uri
	* ����$_REQUEST��������
	*/
	
	public function prase_uri() {
		
		$uri = $this->req_url;
		$hashtml = strripos($uri, ".html");
		if (false == $hashtml) {
			header("location:".$_SERVER['REQUEST_URI'].".html");
		}
		$exp_uri = explode('.', $uri);
		$sub_uri = $exp_uri[0];
		//��ֹ����Ĺ����������滻�� /
		$sub_uri = str_replace('/', '', $sub_uri);
		$hasl = strpos($sub_uri, "-");
		//���û���ṩ�����������Ǿ�Ĭ��Ϊ������index����
		if (false == $hasl) {
			if(is_numeric($sub_uri)){
				$_REQUEST['controller']='index';
				$_REQUEST['action']=$sub_uri;
			}else{
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
				if ($i % 2 == 0) {
					$key = $esub[$i];
					$val = $esub[$k];
					$_REQUEST[$key] = $val;
				}
			}
		}
	}
	/**
	* ȡ�������ļ�
	* �������ļ���λ�ã�./controllers/����Aciton.class.php
	* @return string �������ļ�λ��
	* @param object $className ����
	*/
	
	public function getClassFile($className) {
		$trueClassFile = "./controllers/".$className."Action.class.php";
		if (!file_exists($trueClassFile)) {
			throw new Exception("�����ʵ����󲻴���");
		}
		return $trueClassFile;
	}
	/**
	* ���������ļ�
	*/
	public function loadConfig() {
		$config_preload = array('db', 'global', 'path', 'secure', 'private', 'log','soap');
		$config_path = "./config/";
		foreach ($config_preload as $val) {
			$configFile = $config_path.$val.".php";
			if (file_exists($configFile)) {
				registry::setRegistry($val, include ($configFile));
			}
		}
	}
	public static function Model($modelName) {

		
		$modelDir = "./models/";
		$modelClassName = $modelName."Model";
		$modelFile = $modelDir.$modelClassName.".class.php";
		
		if (!file_exists($modelFile)) {
			throw new Exception("�Բ���Model�ļ������ڣ�");
		}
		require_once ($modelFile);
		$md = new $modelClassName($modelName);
		
		return $md;
	}
	/**
	* ��ȡ���ݿ����
	* @param object $dbConfig
	* @return
	*/
	public static function db($dbConfig = null) {
		if (!isset(self::$db)) {
			if (!$dbConfig) {
				self::$db = new db(registry::getRegistry('db'));
			} else {
				self::$db = new db($dbConfig);
			}
			
		}
		return self::$db;
	}
}
