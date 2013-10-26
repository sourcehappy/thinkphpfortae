<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: String.class.php 2661 2012-01-26 03:00:18Z liu21st $

class String {

    /**
     +----------------------------------------------------------
     * ����UUID ����ʹ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	 static public function uuid() {
        $charid = md5(uniqid(mt_rand(), true));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
               .substr($charid, 0, 8).$hyphen
               .substr($charid, 8, 4).$hyphen
               .substr($charid,12, 4).$hyphen
               .substr($charid,16, 4).$hyphen
               .substr($charid,20,12)
               .chr(125);// "}"
        return $uuid;
   }

	/**
	 +----------------------------------------------------------
	 * ����Guid����
	 +----------------------------------------------------------
	 * @return Boolean
	 +----------------------------------------------------------
	 */
	static public function keyGen() {
		return str_replace('-','',substr(String::uuid(),1,-1));
	}

	/**
	 +----------------------------------------------------------
	 * ����ַ����Ƿ���UTF8����
	 +----------------------------------------------------------
	 * @param string $string �ַ���
	 +----------------------------------------------------------
	 * @return Boolean
	 +----------------------------------------------------------
	 */
	static public function isUtf8($str) {
		$c=0; $b=0;
		$bits=0;
		$len=strlen($str);
		for($i=0; $i<$len; $i++){
			$c=ord($str[$i]);
			if($c > 128){
				if(($c >= 254)) return false;
				elseif($c >= 252) $bits=6;
				elseif($c >= 248) $bits=5;
				elseif($c >= 240) $bits=4;
				elseif($c >= 224) $bits=3;
				elseif($c >= 192) $bits=2;
				else return false;
				if(($i+$bits) > $len) return false;
				while($bits > 1){
					$i++;
					$b=ord($str[$i]);
					if($b < 128 || $b > 191) return false;
					$bits--;
				}
			}
		}
		return true;
	}

	/**
	 +----------------------------------------------------------
	 * �ַ�����ȡ��֧�����ĺ���������
	 +----------------------------------------------------------
	 * @static
	 * @access public
	 +----------------------------------------------------------
	 * @param string $str ��Ҫת�����ַ���
	 * @param string $start ��ʼλ��
	 * @param string $length ��ȡ����
	 * @param string $charset �����ʽ
	 * @param string $suffix �ض���ʾ�ַ�
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	static public function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
        if(function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
        }else{
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice.'...' : $slice;
    }

	/**
	 +----------------------------------------------------------
	 * ��������ִ����������Զ���������
	 * Ĭ�ϳ���6λ ��ĸ�����ֻ�� ֧������
	 +----------------------------------------------------------
	 * @param string $len ����
	 * @param string $type �ִ�����
	 * 0 ��ĸ 1 ���� ���� ���
	 * @param string $addChars �����ַ�
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	static public function randString($len=6,$type='',$addChars='') {
		$str ='';
		switch($type) {
			case 0:
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
				break;
			case 1:
				$chars= str_repeat('0123456789',3);
				break;
			case 2:
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
				break;
			case 3:
				$chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
				break;
			case 4:
				$chars = "�����ҵ�������ʱҪ��������һ�ǹ�������巢�ɲ���ɳ��ܷ������˲����д�����������Ϊ����������ѧ�¼��ظ���ͬ����˵�ֹ����ȸ�����Ӻ������С��Ҳ�����߱������������ʵ�Ҷ������ˮ������������������ʮս��ũʹ��ǰ�ȷ���϶�·ͼ�ѽ�������¿���֮��ӵ���Щ�������¶�����������˼�����ȥ�����������ѹԱ��ҵ��ȫ�������ڵ�ƽ��������ëȻ��Ӧ�����������ɶ������ʱ�չ�������û���������ϵ������Ⱥͷ��ֻ���ĵ����ϴ���ͨ�����Ͽ��ֹ�����������ϯλ����������ԭ�ͷ�������ָ��������ںܽ̾��ش˳�ʯǿ�������Ѹ���ֱ��ͳʽת�����о���ȡ������������־�۵���ôɽ�̰ٱ��������汣��ί�ָĹܴ�������֧ʶ�������Ϲ�רʲ���;�ʾ������ÿ�����������Ϲ����ֿƱ�������Ƹ��������������༯������װ����֪���е�ɫ����ٷ�ʷ����������֯�������󴫿ڶϿ��ɾ����Ʒ�вβ�ֹ��������ȷ������״��������Ŀ����Ȩ�Ҷ����֤��Խ�ʰ��Թ�˹��ע�첼�����������ر��̳�������ǧʤϸӰ�ð׸�Ч���ƿ��䵶Ҷ������ѡ���»������ʼƬʩ���ջ�������������ҩ����Ѵ��ʿ���Һ��׼��ǽ�ά�������������״����ƶ˸������ش幹���ݷǸ���ĥ�������ʽ���ֵ��̬���ױ�������������̨���û������ܺ���ݺ����ʼ��������Ͼ��ݼ���ҳ�����Կ�Ӣ��ƻ���Լ�Ͳ�ʡ�������źӵ۽�����ֲ������������ץ���縱����̸Χʳ��Դ�������ȴ����̻����������׳߲��зۼ������濼�̿�������ʧ��ס��֦�־����ܻ���ʦ������Ԫ����ɰ�⻻̫ģƶ�����ｭ��Ķľ����ҽУ���ص�����Ψ�们վ�����ֹĸ�д��΢�Է�������ĳ�����������൹�������ù�Զ���Ƥ����ռ����Ȧΰ��ѵ�ؼ��ҽ��ƻ���������ĸ�����ֶ���˫��������ʴ����˿Ůɢ��������Ժ�䳹����ɢ�����������������Ѫ��ȱ��ò�����ǳ���������������̴���������������Ͷ��ū����ǻӾഥ�����ͻ��˶��ٻ����δͻ�ܿ���ʪƫ�Ƴ�ִ����կ�����ȶ�Ӳ��Ŭ�����Ԥְ������Э�����ֻ���ì������ٸ�������������ͣ����Ӫ�ո���Ǯ��������ɳ�˳��ַ�е�ذ����İ��������۵��յ���ѽ�ʰɿ��ֽ�������������ĩ������ڱ������������������𾪶ټ�����ķ��ɭ��ʥ���մʳٲ��ھؿ��������԰ǻ�����������������ӡ�伱�����˷�¶��Ե�������������Ѹ��������ֽҹ������׼�����ӳ��������ɱ���׼辧�尣ȼ��������ѿ��������̼��������ѿ����б��ŷ��˳������͸˾Σ������Ц��β��׳����������������ţ��Ⱦ�����������Ƽ�ֳ�����ݷô���ͭ��������ٺ�����Դ��ظ���϶¯����úӭ��ճ̽�ٱ�Ѯ�Ƹ�������Ը���������̾䴿������������³�෱�������׶ϣ�ذܴ�����ν�л��ܻ���ڹ��ʾ����ǳ���������Ϣ������������黭�������������躮ϲ��ϴʴ���ɸ���¼������֬ׯ��������ҡ���������������Ű²��ϴ�;�������Ұ�ž�ıŪ�ҿ�����ʢ��Ԯ���Ǽ���������Ħæ�������˽����������������Ʊܷ�������Ƶ�������Ҹ�ŵ����Ũ��Ϯ˭��л�ڽ���Ѷ���鵰�պ������ͽ˽������̹����ù�����ո��伨���ܺ���ʹ�������������ж�����׷���ۺļ���������о�Ѻպ��غ���Ĥƪ��פ������͹�ۼ���ѩ�������������߲��������ڽ������˹�̿������������ǹ���ð������Ͳ���λ�����Ϳζ����Ϻ�½�����𶹰�Ī��ɣ�·쾯���۱�����ɶ���ܼ��Ժ��浤�ɶ��ٻ���ϡ���������ǳӵѨ������ֽ����������Ϸ��������ò�����η��ɰ���������ˢ�ݺ���������©�������Ȼľ��з������Բ����ҳ�����ײ����ȳ����ǵ������������ɨ������оү���ؾ����Ƽ��ڿ��׹��ð��ѭ��ף���Ͼ����������ݴ���ι�������Ź�ó����ǽ���˽�ī������ж����������ƭ�ݽ�".$addChars;
				break;
			default :
				// Ĭ��ȥ�������׻������ַ�oOLl������01��Ҫ�����ʹ��addChars����
				$chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
				break;
		}
		if($len>10 ) {//λ�������ظ��ַ���һ������
			$chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
		}
		if($type!=4) {
			$chars   =   str_shuffle($chars);
			$str     =   substr($chars,0,$len);
		}else{
			// ���������
			for($i=0;$i<$len;$i++){
			  $str.= self::msubstr($chars, floor(mt_rand(0,mb_strlen($chars,'utf-8')-1)),1,'utf-8',false);
			}
		}
		return $str;
	}

	/**
	 +----------------------------------------------------------
	 * ����һ������������������Ҳ��ظ�
	 +----------------------------------------------------------
	 * @param integer $number ����
	 * @param string $len ����
	 * @param string $type �ִ�����
	 * 0 ��ĸ 1 ���� ���� ���
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	static public function buildCountRand ($number,$length=4,$mode=1) {
			if($mode==1 && $length<strlen($number) ) {
				//����������һ�������Ĳ��ظ�����
				return false;
			}
			$rand   =  array();
			for($i=0; $i<$number; $i++) {
				$rand[] =   self::randString($length,$mode);
			}
			$unqiue = array_unique($rand);
			if(count($unqiue)==count($rand)) {
				return $rand;
			}
			$count   = count($rand)-count($unqiue);
			for($i=0; $i<$count*3; $i++) {
				$rand[] =   self::randString($length,$mode);
			}
			$rand = array_slice(array_unique ($rand),0,$number);
			return $rand;
	}

	/**
	 +----------------------------------------------------------
	 *  ����ʽ��������ַ� ֧����������
	 *  �����ܴ����ظ�
	 +----------------------------------------------------------
	 * @param string $format �ַ���ʽ
	 *     # ��ʾ���� * ��ʾ��ĸ������ $ ��ʾ��ĸ
	 * @param integer $number ��������
	 +----------------------------------------------------------
	 * @return string | array
	 +----------------------------------------------------------
	 */
	static public function buildFormatRand($format,$number=1) {
		$str  =  array();
		$length =  strlen($format);
		for($j=0; $j<$number; $j++) {
			$strtemp   = '';
			for($i=0; $i<$length; $i++) {
				$char = substr($format,$i,1);
				switch($char){
					case "*"://��ĸ�����ֻ��
						$strtemp   .= String::randString(1);
						break;
					case "#"://����
						$strtemp  .= String::randString(1,1);
						break;
					case "$"://��д��ĸ
						$strtemp .=  String::randString(1,2);
						break;
					default://������ʽ����ת��
						$strtemp .=   $char;
						break;
			   }
			}
			$str[] = $strtemp;
		}

		return $number==1? $strtemp : $str ;
	}

	/**
	 +----------------------------------------------------------
	 * ��ȡһ����Χ�ڵ�������� λ�����㲹��
	 +----------------------------------------------------------
	 * @param integer $min ��Сֵ
	 * @param integer $max ���ֵ
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	static public function randNumber ($min, $max) {
		return sprintf("%0".strlen($max)."d", mt_rand($min,$max));
	}

    // �Զ�ת���ַ��� ֧������ת��
    static public function autoCharset($string, $from='gbk', $to='utf-8') {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($string) || (is_scalar($string) && !is_string($string))) {
            //���������ͬ���߷��ַ���������ת��
            return $string;
        }
        if (is_string($string)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($string, $to, $from);
            } elseif (function_exists('iconv')) {
                return iconv($from, $to, $string);
            } else {
                return $string;
            }
        } elseif (is_array($string)) {
            foreach ($string as $key => $val) {
                $_key = self::autoCharset($key, $from, $to);
                $string[$_key] = self::autoCharset($val, $from, $to);
                if ($key != $_key)
                    unset($string[$key]);
            }
            return $string;
        }
        else {
            return $string;
        }
    }
}