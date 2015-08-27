<?php
namespace netroby\zvcore;

class IDCard
{

    // 计算身份证校验码，根据国家标准GB 11643-1999
    private static function idcard_verify_number($idcard_base)
    {
        if (strlen($idcard_base) != 17) {
            return false;
        }

        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        // 校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        $checksum = 0;
        $ic_len = strlen($idcard_base);
        for ($i = 0; $i < $ic_len; $i++) {
            $checksum += ((int) substr($idcard_base, $i, 1)) * $factor[$i];
        }

        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];

        return $verify_number;

    }

    // 将15位身份证升级到18位
    private static function idcard_15to18($idcard)
    {
        if (strlen($idcard) != 15) {
            return false;
        } else {
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (in_array((int) substr($idcard, 12, 3), array(996, 997, 998, 999), true) !== false) {
                $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
            } else {
                $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
            }
        }

        $idcard = $idcard . self::idcard_verify_number($idcard);

        return $idcard;
    }

    public static function idcard_gen()
    {
        $front = '422126';
        $y = mt_rand(1980, 1990);
        $m = mt_rand(1, 12);
        if ($m < 10) {
            $m = '0' . $m;
        }
        $d = mt_rand(1, 28);
        if ($d < 10) {
            $d = '0' . $d;
        }
        $sr = mt_rand(111, 999);
        $idcard = $front . $y . $m . $d . $sr;
        $idcard = $idcard . self::idcard_verify_number($idcard);
        return $idcard;
    }

    // 18位身份证校验码有效性检查
    public static function idcardCheck($idcard)
    {
        if (strlen($idcard) !== 18) {
            return false;
        }
        $idcard_base = substr($idcard, 0, 17);

        if (self::idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))) {
            return false;
        } else {
            return true;
        }
    }

}
