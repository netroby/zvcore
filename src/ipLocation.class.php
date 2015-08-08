<?php
/**
 * IP ����λ�ò�ѯ��
 * @author ���Ң (coolcode.cn) Modify By Netroby (netroby.cn)
 */
class ipLocation {
	/**
	 * QQWry.Dat�ļ�ָ��
	 * @var resource
	 */
	private $fp;

	/**
	 * ��һ��IP��¼��ƫ�Ƶ�ַ
	 * @var int
	 */
	private $firstIp;

	/**
	 * ���һ��IP��¼��ƫ�Ƶ�ַ
	 * @var int
	 */
	private $lastIp;

	/**
	 * IP��¼�����������������汾��Ϣ��¼��
	 * @var int
	 */
	private  $totalIp;

	/**
	 * ����ʽ
	 */
	private static $ipLoc;

	/**
	 * ���캯������ QQWry.Dat �ļ�����ʼ�����е���Ϣ
	 * @param string $filename
	 * @return IpLocation
	 */
	private function __construct() {
	   $filename=dirname(__FILE__).DIRECTORY_SEPARATOR."QQWry.Dat";
		$this->fp = 0;
		if (($this->fp = @fopen($filename, 'rb')) !== false) {
			$this->firstIp = $this->getLong();
			$this->lastIp = $this->getLong();
			$this->totalIp = ($this->lastIp - $this->firstIp) / 7;
		}
	}

	/**
	 * ȡΨһ����ģʽ
	 * @return object
	 */
	private static function _getInstant(){
		if(null==self::$ipLoc){
			self::$ipLoc=new ipLocation();
		}
		return self::$ipLoc;
	}

	/**
	 * ���ض�ȡ�ĳ�������
	 * @return int
	 */
	private function getLong() {
		//����ȡ��little-endian�����4���ֽ�ת��Ϊ��������
		$result = unpack('Vlong', fread($this->fp, 4));
		return $result['long'];
	}

	/**
	 * ���ض�ȡ��3���ֽڵĳ�������
	 * @return int
	 */
	private function getLong3() {
		//����ȡ��little-endian�����3���ֽ�ת��Ϊ��������
		$result = unpack('Vlong', fread($this->fp, 3).chr(0));
		return $result['long'];
	}

	/**
	 * ����ѹ����ɽ��бȽϵ�IP��ַ
	 * @param string $ip
	 * @return string
	 */
	private function packIp($ip) {
		// ��IP��ַת��Ϊ���������������PHP5�У�IP��ַ�����򷵻�False��
		// ��ʱintval��Flaseת��Ϊ����-1��֮��ѹ����big-endian������ַ���
		return pack('N', intval(ip2long($ip)));
	}

	/**
	 * ���ض�ȡ���ַ���
	 * @param string $data
	 * @return string
	 */
	private function getString($data = "") {
		$char = fread($this->fp, 1);
		while (ord($char) > 0) {        // �ַ�������C��ʽ���棬��\0����
			$data .= $char;             // ����ȡ���ַ����ӵ������ַ���֮��
			$char = fread($this->fp, 1);
		}
		return $data;
	}

	/**
	 * ���ص�����Ϣ
	 * @return string
	 */
	private function getArea() {
		$byte = fread($this->fp, 1);    // ��־�ֽ�
		switch (ord($byte)) {
		case 0:                     // û��������Ϣ
			$area = "";
			break;
		case 1:
			case 2:                     // ��־�ֽ�Ϊ1��2����ʾ������Ϣ���ض���
				fseek($this->fp, $this->getLong3());
				$area = $this->getString();
				break;
			default:                    // ���򣬱�ʾ������Ϣû�б��ض���
				$area = $this->getString($byte);
				break;
		}
		return $area;
	}

	/**
	 * �������� IP ��ַ�������������ڵ�����Ϣ
	 * @param string $ip
	 * @return array
	 */
	public static function getLocation($ip) {
		$ipLOC=self::_getInstant();	  
		if (!$ipLOC->fp) return null;            // ��������ļ�û�б���ȷ�򿪣���ֱ�ӷ��ؿ�
		$location['ip'] = gethostbyname($ip);   // �����������ת��ΪIP��ַ
		$ip = $ipLOC->packip($location['ip']);   // �������IP��ַת��Ϊ�ɱȽϵ�IP��ַ
		// ���Ϸ���IP��ַ�ᱻת��Ϊ255.255.255.255
		// �Է�����
		$l = 0;                         // �������±߽�
		$u = $ipLOC->totalIp;            // �������ϱ߽�
		$findip = $ipLOC->lastIp;        // ���û���ҵ��ͷ������һ��IP��¼��QQWry.Dat�İ汾��Ϣ��
		while ($l <= $u) {              // ���ϱ߽�С���±߽�ʱ������ʧ��
			$i = floor(($l + $u) / 2);  // ��������м��¼
			fseek($ipLOC->fp, $ipLOC->firstIp + $i * 7);
			$beginip = strrev(fread($ipLOC->fp, 4));     // ��ȡ�м��¼�Ŀ�ʼIP��ַ
			// strrev����������������ǽ�little-endian��ѹ��IP��ַת��Ϊbig-endian�ĸ�ʽ
			// �Ա����ڱȽϣ�������ͬ��
			if ($ip < $beginip) {       // �û���IPС���м��¼�Ŀ�ʼIP��ַʱ
				$u = $i - 1;            // ���������ϱ߽��޸�Ϊ�м��¼��һ
			}
			else {
				fseek($ipLOC->fp, $ipLOC->getLong3());
				$endip = strrev(fread($ipLOC->fp, 4));   // ��ȡ�м��¼�Ľ���IP��ַ
				if ($ip > $endip) {     // �û���IP�����м��¼�Ľ���IP��ַʱ
					$l = $i + 1;        // ���������±߽��޸�Ϊ�м��¼��һ
				}
				else {                  // �û���IP���м��¼��IP��Χ��ʱ
					$findip = $ipLOC->firstIp + $i * 7;
					break;              // ���ʾ�ҵ�������˳�ѭ��
				}
			}
		}

		//��ȡ���ҵ���IP����λ����Ϣ
		fseek($ipLOC->fp, $findip);
		$location['beginip'] = long2ip($ipLOC->getLong());   // �û�IP���ڷ�Χ�Ŀ�ʼ��ַ
		$offset = $ipLOC->getLong3();
		fseek($ipLOC->fp, $offset);
		$location['endip'] = long2ip($ipLOC->getLong());     // �û�IP���ڷ�Χ�Ľ�����ַ
		$byte = fread($ipLOC->fp, 1);    // ��־�ֽ�
		switch (ord($byte)) {
		case 1:                     // ��־�ֽ�Ϊ1����ʾ���Һ�������Ϣ����ͬʱ�ض���
			$countryOffset = $ipLOC->getLong3();         // �ض����ַ
			fseek($ipLOC->fp, $countryOffset);
			$byte = fread($ipLOC->fp, 1);    // ��־�ֽ�
			switch (ord($byte)) {
			case 2:             // ��־�ֽ�Ϊ2����ʾ������Ϣ�ֱ��ض���
				fseek($ipLOC->fp, $ipLOC->getLong3());
				$location['country'] = $ipLOC->getString();
				fseek($ipLOC->fp, $countryOffset + 4);
				$location['area'] = $ipLOC->getArea();
				break;
			default:            // ���򣬱�ʾ������Ϣû�б��ض���
				$location['country'] = $ipLOC->getString($byte);
				$location['area'] = $ipLOC->getArea();
				break;
			}
			break;
		case 2:                     // ��־�ֽ�Ϊ2����ʾ������Ϣ���ض���
			fseek($ipLOC->fp, $ipLOC->getLong3());
			$location['country'] = $ipLOC->getString();
			fseek($ipLOC->fp, $offset + 8);
			$location['area'] = $ipLOC->getArea();
			break;
		default:                    // ���򣬱�ʾ������Ϣû�б��ض���
			$location['country'] = $ipLOC->getString($byte);
			$location['area'] = $ipLOC->getArea();
			break;
		}
		if ($location['country'] == " CZ88.NET") {  // CZ88.NET��ʾû����Ч��Ϣ
			$location['country'] = "δ֪";
		}
		if ($location['area'] == " CZ88.NET") {
			$location['area'] = "";
		}
		return $location['country']."(".$location['area'].")";
	}

}



