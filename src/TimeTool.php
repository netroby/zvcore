<?php
namespace netroby\zvcore;

/**
 * 时间类库，以后会添加和补充关于时间的处理函数。
 */
class timefair
{
    /**
     * 返回昨天，今天，明天
     * @return Array 时间数组
     */
    public static function fair()
    {

        $timeMagic = array();
        $time = time();
        $today = date("F j,y", $time);
        $timeMagic['today'] = strtotime($today);
        $timeMagic['yesterday'] = $timeMagic['today'] - (3600 * 24);
        $timeMagic['tomorrow'] = $timeMagic['today'] + (3600 * 24);
        return $timeMagic;

    }
}
