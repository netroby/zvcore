<?php 
//��phpԭ��̬�ļ�gzipѹ���ļ�
ob_start( 'ob_gzhandler' );

session_start();
//������Ŀ¼
define('zvc_path', dirname(__FILE__));

/**
 * �Զ��������
 * ��Ҫ�ṩ׼ȷ������������ļ������������ǣ�
 * ����.class.php
 * ���磺
 * pager.class.php
 * ������Ƿ�ҳ�����ļ���
 * �����ļ�����/zvcore/ext/Ŀ¼����
 * @return null ������������Ϣ
 * @param object $className ����
 */
function __autoload($className) {
    $filename = zvc_path.'/ext/'.$className.'.class.php';
    if (file_exists($filename)) {
        require_once ($filename);
    }
}

