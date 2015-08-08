<?php 
/**
 * Soap助手类，封装配置数组，减少中间需要手动配置的环节
 */
class soapHelper {
	/**
	 * Soap配置变量
	 * @var string $soapCofing
	 */
    private static $soapConfig;
	/**
	 * 获取全局变量里面存储的Soap变量
	 * @return array 
	 */
    public static function getSoapConfig() {
        return registry::getRegistry('soap');
    }
	/**
	 * 返回客户端变量
	 * @param string $module 模块名
	 * @return array 客户端变量
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
	 * 返回服务器端变量
	 * @param string $module 模块名
	 * @return array 服务器端变量
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
	 * 新建一个Soap客户端
	 * @param object $module
	 * @return 
	 */
	public static function initClient($module){
		$client=new SoapClient(null, self::clientConfig($module));
		return $client;
	}
}
