<?php
namespace netroby\zvcore;

class SafeBox
{
    /**
     * 根据指定的错误验证信息，验证表单是否完整。
     * @param object $errMsg 错误提示信息数组
     */
    public static function validPost($errMsg)
    {

        $safe_POST = array();
        foreach ($_POST as $key => $val) {
            if (empty($val)) {
                helper::goback($errMsg[$key]);
            } else {
                $safe_POST[$key] = self::safeChar($val);
            }

        }

        unset ($_POST);
        $_POST = $safe_POST;
    }

    /**
     * 字符串安全过滤器
     * @param array $mixed 待处理的自符串
     * @return array
     */
    public static function safeChar(array $mixed)
    {

        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = self::safeChar($value);
            }
        } elseif (!is_numeric($mixed)) {
            $mixed = trim($mixed);
        }

        if (false == get_magic_quotes_gpc()) {
            $mixed = addslashes($mixed);
        }
        return $mixed;
    }

    /**
     * 安全包含文件
     * @param string $file 需要包含的文件
     * @return mixed
     */
    public function safeInclude($file)
    {
        $file = str_replace(
            ['../', './', 'http://'],
            '',
            $file
        );
        if (file_exists($file)) {
            return include $file;
        } else {
            return false;
        }
    }
}