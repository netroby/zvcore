<?php 
/**
 * Soap�����࣬��װ�������飬�����м���Ҫ�ֶ����õĻ���
 */
class soapHelper {
	/**
	 * Soap���ñ���
	 * @var string $soapCofing
	 */
    private static $soapConfig;
	/**
	 * ��ȡȫ�ֱ�������洢��Soap����
	 * @return array 
	 */
    public static function getSoapConfig() {
        return registry::getRegistry('soap');
    }
	/**
	 * ���ؿͻ��˱���
	 * @param string $module ģ����
	 * @return array �ͻ��˱���
	 */
    public static function clientConfig($module) {
        $clientConfig = self::getSoapConfig();
        if ($clientConfig) {
            $clientConfig['location'] = sprintf($clientConfig["location"], $module);
			$clientConfig['uri']=sprintf($clientConfig["uri"],$module);
			$clientConfig['trace']=true;
            return $clientConfig;
        } else {
            return false;
        }        
    }
	/**
	 * ���ط������˱���
	 * @param string $module ģ����
	 * @return array �������˱���
	 */
	public static function serverConfig($module){
		$clientConfig = self::getSoapConfig();
        if ($clientConfig) {
			$serverConfig['uri']=sprintf($clientConfig["uri"],$module);
            return $serverConfig;
        } else {
            return false;
        }       
	}
	/**
	 * �½�һ��Soap�ͻ���
	 * @param object $module
	 * @return 
	 */
	public static function initClient($module){
		$client=new SoapClient(null, self::clientConfig($module));
		return $client;
	}
}
