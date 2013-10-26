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
// $Id: CodeSwitch.class.php 2504 2011-12-28 07:35:29Z liu21st $

class CodeSwitch {
    // ������Ϣ
    static private $error = array();
    // ��ʾ��Ϣ
    static private $info = array();
    // ��¼����
    static private function error($msg) {
        self::$error[]   =  $msg;
    }
    // ��¼��Ϣ
    static private function info($info) {
        self::$info[]     = $info;
    }
	/**
     +----------------------------------------------------------
     * ����ת������,�������ļ����б���ת��
	 * ֧������ת��
	 * GB2312��UTF-8 WITH BOMת��ΪUTF-8
	 * UTF-8��UTF-8 WITH BOMת��ΪGB2312
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $filename		�ļ���
	 * @param string $out_charset	ת������ļ�����,��iconvʹ�õĲ���һ��
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	static function DetectAndSwitch($filename,$out_charset) {
		$fpr = fopen($filename,"r");
		$char1 = fread($fpr,1);
		$char2 = fread($fpr,1);
		$char3 = fread($fpr,1);

		$originEncoding = "";

		if($char1==chr(239) && $char2==chr(187) && $char3==chr(191))//UTF-8 WITH BOM
			$originEncoding = "UTF-8 WITH BOM";
		elseif($char1==chr(255) && $char2==chr(254))//UNICODE LE
		{
			self::error("��֧�ִ�UNICODE LEת����UTF-8��GB����");
			fclose($fpr);
			return;
		}
		elseif($char1==chr(254) && $char2==chr(255))//UNICODE BE
		{
			self::error("��֧�ִ�UNICODE BEת����UTF-8��GB����");
			fclose($fpr);
			return;
		}
		else//û���ļ�ͷ,������GB��UTF-8
		{
			if(rewind($fpr)===false)//�ص��ļ���ʼ����,׼�����ֽڶ�ȡ�жϱ���
			{
				self::error($filename."�ļ�ָ�����ʧ��");
				fclose($fpr);
				return;
			}

			while(!feof($fpr))
			{
				$char = fread($fpr,1);
				//����Ӣ��,GB��UTF-8���ǵ��ֽڵ�ASCII��С��128��ֵ
				if(ord($char)<128)
					continue;

				//���ں���GB�����һ���ֽ���110*****�ڶ����ֽ���10******(������,��������)
				//UTF-8�����һ���ֽ���1110****�ڶ����ֽ���10******�������ֽ���10******
				//��λ��������Ҫ��������Ǻ���ͬ,����Ӧ�����ж�UTF-8
				//��Ϊʹ��GB�����밴λ��,UTF-8��111�ó�����Ҳ��110,����Ҫ���ж�UTF-8
				if((ord($char)&224)==224)
				{
					//��һ���ֽ��ж�ͨ��
					$char = fread($fpr,1);
					if((ord($char)&128)==128)
					{
						//�ڶ����ֽ��ж�ͨ��
						$char = fread($fpr,1);
						if((ord($char)&128)==128)
						{
							$originEncoding = "UTF-8";
							break;
						}
					}
				}
				if((ord($char)&192)==192)
				{
					//��һ���ֽ��ж�ͨ��
					$char = fread($fpr,1);
					if((ord($char)&128)==128)
					{
						//�ڶ����ֽ��ж�ͨ��
						$originEncoding = "GB2312";
						break;
					}
				}
			}
		}

		if(strtoupper($out_charset)==$originEncoding)
		{
			self::info("�ļ�".$filename."ת�������,ԭʼ�ļ�����".$originEncoding);
			fclose($fpr);
		}
		else
		{
			//�ļ���Ҫת��
			$originContent = "";

			if($originEncoding == "UTF-8 WITH BOM")
			{
				//���������ֽ�,�Ѻ�������ݸ���һ��õ�utf-8������
				fseek($fpr,3);
				$originContent = fread($fpr,filesize($filename)-3);
				fclose($fpr);
			}
			elseif(rewind($fpr)!=false)//������UTF-8����GB2312,�ص��ļ���ʼ����,��ȡ����
			{
				$originContent = fread($fpr,filesize($filename));
				fclose($fpr);
			}
			else
			{
				self::error("�ļ����벻��ȷ��ָ�����ʧ��");
				fclose($fpr);
				return;
			}

			//ת�벢�����ļ�
			$content = iconv(str_replace(" WITH BOM","",$originEncoding),strtoupper($out_charset),$originContent);
			$fpw = fopen($filename,"w");
			fwrite($fpw,$content);
			fclose($fpw);

			if($originEncoding!="")
				self::info("���ļ�".$filename."ת�����,ԭʼ�ļ�����".$originEncoding.",ת�����ļ�����".strtoupper($out_charset));
			elseif($originEncoding=="")
				self::info("�ļ�".$filename."��û�г�������,���ǿ��Զ϶����Ǵ�BOM��UTF-8����,û�н��б���ת��,��Ӱ��ʹ��");
		}
	}

	/**
     +----------------------------------------------------------
     * Ŀ¼��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $path		Ҫ������Ŀ¼��
     * @param string $mode		����ģʽ,һ��ȡFILES,����ֻ���ش�·�����ļ���
     * @param array $file_types		�ļ���׺��������
	 * @param int $maxdepth		�������,-1��ʾ��������ײ�
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	static function searchdir($path,$mode = "FULL",$file_types = array(".html",".php"),$maxdepth = -1,$d = 0)
	{
	   if(substr($path,strlen($path)-1) != '/')
		   $path .= '/';
	   $dirlist = array();
	   if($mode != "FILES")
			$dirlist[] = $path;
	   if($handle = @opendir($path))
	   {
		   while(false !== ($file = readdir($handle)))
		   {
			   if($file != '.' && $file != '..')
			   {
				   $file = $path.$file ;
				   if(!is_dir($file))
				   {
						if($mode != "DIRS")
						{
							$extension = "";
							$extpos = strrpos($file, '.');
							if($extpos!==false)
								$extension = substr($file,$extpos,strlen($file)-$extpos);
							$extension=strtolower($extension);
							if(in_array($extension, $file_types))
								$dirlist[] = $file;
						}
				   }
				   elseif($d >= 0 && ($d < $maxdepth || $maxdepth < 0))
				   {
					   $result = self::searchdir($file.'/',$mode,$file_types,$maxdepth,$d + 1) ;
					   $dirlist = array_merge($dirlist,$result);
				   }
			   }
		   }
		   closedir ( $handle ) ;
	   }
	   if($d == 0)
		   natcasesort($dirlist);

	   return($dirlist) ;
	}

	/**
     +----------------------------------------------------------
     * ��������ĿĿ¼�е�PHP��HTML�ļ��н�����ת��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $app		Ҫ��������Ŀ·��
     * @param string $mode		����ģʽ,һ��ȡFILES,����ֻ���ش�·�����ļ���
     * @param array $file_types		�ļ���׺��������
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	static function CodingSwitch($app = "./",$charset='UTF-8',$mode = "FILES",$file_types = array(".html",".php"))
	{
		self::info("ע��: ����ʹ�õ��ļ��������㷨���ܶ�ĳЩ�����ַ�������");
		$filearr = self::searchdir($app,$mode,$file_types);
		foreach($filearr as $file)
			self::DetectAndSwitch($file,$charset);
	}

    static public function getError() {
        return self::$error;
    }

    static public function getInfo() {
        return self::$info;
    }
}