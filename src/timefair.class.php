<?php 
/**
 * ʱ����⣬�Ժ����ӺͲ������ʱ��Ĵ�������
 */
class timefair {
	/**
	 * �������죬���죬����
	 * @return Array ʱ������
	 */
    public static function fair() {
    
        $timeMagic = array();
        $time = time();
        $today = date("F j,y", $time);
        $timeMagic['today'] = strtotime($today);
        $timeMagic['yesterday'] = $timeMagic['today'] - (3600 * 24);
        $timeMagic['tomorrow'] = $timeMagic['today'] + (3600 * 24);
        return $timeMagic;
        
    }
}
