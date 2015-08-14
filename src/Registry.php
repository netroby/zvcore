<?php
/**
 * ȫ�ֱ�������
 */
class registry{
    /**
     * ���ʵ��
     * @var object  ���ʵ��
     */
    private static $_registry=null;
    /**
     * ��������
     * @var array ��������
     */
    private $reg=array();
    /**
     * ���γ�ʼ������
     */
    private function __construct(){}
    /**
     * ��ȡ��ʵ��
     */
    private static function getInstant(){
        if(null==self::$_registry){
            self::setInstant();
        }
        return self::$_registry;
    }
    /**
     * �趨��ʵ��
     */
    private static function setInstant(){
        if(null==self::$_registry){
        self::$_registry=new registry();
        }
    }
    /**
     * ���ñ���
     * @param object $key ����
     * @param object $val ������ֵ
     */
    public static function setRegistry($key,$val){
        $registry=self::getInstant();
        $registry->registrySet($key,$val);
    }
    /**
     * ��ȡ����
     * @param object $key ������key
     */
    public static function getRegistry($key){
        $registry=self::getInstant();
        if($registry->keyExists($key)){
        return $registry->registryGet($key);
        }else{
            return null;
        }
    }
    /**
     * ���ñ���
     * ��������ķ�������
     * @param object $key ����
     * @param object $val ������ֵ
     */
    private  function registrySet($key,$val){
        $this->reg[$key]=$val;
    }
    /**
     * ��ȡ����
     * @param object $key ������
     */
    private function registryGet($key){
        return $this->reg[$key];
    }
    /**
     * �����Ƿ����
     * @param object $key ������
     */
    private function keyExists($key){
        return array_key_exists($key, $this->reg);
    }
}
