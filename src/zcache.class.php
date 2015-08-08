<?php 
class zcache
{
    //��������

    private static $_zcache = null;
    //�����ļ�Ŀ¼
    private $_cache_base_dir = "./cache/";
    /**
     * ���Ʒ���Ȩ��
     * @return
     */

    private function __construct()
    {
    }
    /**
     * ȡΨһʵ��
     * @return instance
     */

    private static function getInstant()
    {
        if (null == self::$_zcache)
        {
            self::$_zcache = new zcache();
        }
        return self::$_zcache;
    }
    /**
     * ���û��淽��
     * @param object $key [optional]
     * @param object $val [optional]
     * @param object $lifetime [optional]
     * @return
     */

    public static function set($key = null, $val = null, $lifetime = 0)
    {
        if (null == $key)
        {
            throw new Exception("��û��Ū���,���ṩKey����ô���氡��");
        } elseif (0 == $lifetime)
        {
            throw new Exception("�ҿ�������û�и��û���趨��Чʱ�䣬�㻺���ƨѽ����");
        }
        $zcache = self::getInstant();
        //md5 Key��ֵ
        $zcache->_setCache(md5($key), $val, $lifetime);
    }
    /**
     * ��ȡ����
     * @param object $key [optional]
     * @return
     */

    public static function get($key = null)
    {
        if (null == $key)
        {
            throw new Exception("�٣�����ҿյ�Key,��ȥ��������Ҷ�������");
        }
        $zcache = self::getInstant();
        //ͬ��Ҫ�ȼ�md5Ȼ���������ȡ��ֵ
        return $zcache->_getCache(md5($key));
    }
    /**
     * ���û���
     * @param object $key
     * @param object $val
     * @param object $lifetime
     * @return
     */

    public function _setCache($key, $val, $lifetime)
    {
        //����ɵĻ����ļ�
        $this->_remove($key);
        
        $exptime = $lifetime + time();
        //�����ļ�
        $cacheFile = $this->_cache_base_dir.$key;
        //����ͷ�ļ�
        $metaCacheFile = $cacheFile.".meta";
        //д�뻺��
        $statA = file_put_contents($cacheFile, serialize($val));
        //д�뻺��ͷ�ļ�
        $statB = file_put_contents($metaCacheFile, $exptime);
        if ($statA == 0 || $statB == 0)
        {
            throw new Exception("д�뻺���ļ�����");
        }
    }
    /**
     * ɾ������
     * @param object $key
     * @return
     */

    public function _remove($key)
    {
    
        //�����ļ�
        $cacheFile = $this->_cache_base_dir.$key;
        //����ͷ�ļ�
        $metaCacheFile = $cacheFile.".meta";
        
        if (file_exists($cacheFile))
        {
        
            $statA = unlink($cacheFile);
        }
        
        if (file_exists($metaCacheFile))
        {
            $statB = unlink($metaCacheFile);
        }
        
    }
    /**
     * ȡ����
     * @param object $key
     * @return
     */

    public function _getCache($key)
    {
        //�����ļ�
        $cacheFile = $this->_cache_base_dir.$key;
        //�����ļ�ͷ��Ϣ
        $metaCacheFile = $cacheFile.".meta";
        
        if (file_exists($metaCacheFile))
        {
            $exptime = file_get_contents($metaCacheFile);
            if ($exptime < time())
            {
                $this->_remove($key);
                return false;
            }
        }

        
        if (file_exists($cacheFile))
        {
            $rf = file_get_contents($cacheFile);
            return unserialize($rf);
        }
    }
    /**
     * ɾ��ʧЧ�Ļ���
     * @return null
     */

    public function clearCache()
    {
        if ($handle = opendir($this->_cache_base_dir))
        {
            while (false !== ($file = readdir($handle)))
            {
                if ($file != "." && $file != "..")
                {
                    //�����ļ���
                    $trueFile = $this->_cache_base_dir.$file;
                    //�����ļ�ͷ��Ϣ
                    $metaTrueFile = $trueFile.".meta";
                    //��������ļ�ͷ��Ϣ���ڣ���һ��
                    if (file_exists($metaTrueFile))
                    {
                        //ȡ����ʱ��
                        $exptime = file_get_contents($metaTrueFile);
                        //����ʱ��ȶ�
                        if ($exptime < time())
                        {
                            //ɾ��ʧЧ���ļ�
                            unlink($trueFile);
                            unlink($metaTrueFile);
                        }
                        
                    }
                }
            }
            closedir($handle);
        }
    }
}
