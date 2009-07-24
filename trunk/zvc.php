<?php 
//用php原生态的简单gzip压缩文件
ob_start( 'ob_gzhandler' );

session_start();
//定义框架目录
define('zvc_path', dirname(__FILE__));
/**
 * 自动载入类库
 * 需要提供准确的类名，类库文件的命名规则是：
 * 类名.class.php
 * 例如：
 * pager.class.php
 * 代表就是分页类库的文件名
 * 类库的文件置于/zvcore/ext/目录下面
 * @return null 不返回其他信息
 * @param object $className 类名
 */
function __autoload($className) {
    $filename = zvc_path.'/ext/'.$className.'.class.php';
    if (file_exists($filename)) {
        require_once ($filename);
    }
}

