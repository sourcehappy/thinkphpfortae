<?php
/* �����ھ�� 2.0��ʽ��
 * ������ȡ�����������Զ��жϱ��룬�Զ�ת��
 * ԭ�����ݴ�����Ȩ��ԭ�����Ƚ�HTML�ֳ����ɸ�С�飬Ȼ���ÿ��С��������֡�
 * ȡ������3�����ϵĴ�����е����ݷ���
 * �ӷ��� 1 ���б�����
 *        2 ����<p>��ǩ
 *        3 ����<br>��ǩ
 * ������ 1 ����li��ǩ
 *        2 �������κα�����
 *        3 ���йؼ���javascript
 *        4 �������κ����ĵģ�ֱ��ɾ��
 *        5 ��<li><a������ǩ
 * ʵ����
 * $he = new HtmlExtractor();
 * $str = $he->text($html);
 * ����$html��ĳ����ҳ��HTML���룬$str�Ƿ��ص����ģ����ı�����utf-8��
 */
class HtmlExtractor {

    /*
     * ȡ�ú��ֵĸ�����Ŀǰ��̫��ȷ)
     */
    function chineseCount($str){
        $count = preg_match_all("/[\xB0-\xF7][\xA1-\xFE]/",$str,$ff);
        return $count;
    }

    /*
     * �ж�һ�������Ƿ���UTF-8��������ǣ���ôҪת��UTF-8
     */
    function getutf8($str){
        if(!$this->is_utf8(substr(strip_tags($str),0,500))){
            $str = $this->auto_charset($str,"gbk","utf-8");
        }
        return $str;
    }

    function is_utf8($string)
	{
		if(preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$string) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$string) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$string) == true){
            return true;
        }else{
            return false;
        }
	}

    /*
     * �Զ�ת���ַ�����֧��������ַ���
     */
    function auto_charset($fContents,$from,$to){
        $from   =  strtoupper($from)=='UTF8'? 'utf-8':$from;
        $to       =  strtoupper($to)=='UTF8'? 'utf-8':$to;
        if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) ){
            //���������ͬ���߷��ַ���������ת��
            return $fContents;
        }
        if(is_string($fContents) ) {
            if(function_exists('mb_convert_encoding')){
                return mb_convert_encoding ($fContents, $to, $from);
            }elseif(function_exists('iconv')){
                return iconv($from,$to,$fContents);
            }else{
                return $fContents;
            }
        }
        elseif(is_array($fContents)){
            foreach ( $fContents as $key => $val ) {
                $_key =     $this->auto_charset($key,$from,$to);
                $fContents[$_key] = $this->auto_charset($val,$from,$to);
                if($key != $_key )
                    unset($fContents[$key]);
            }
            return $fContents;
        }
        else{
            return $fContents;
        }
    }

    /*
     * ����������ȡ����
     */
    function text($str){
        $str = $this->clear($str);
        $str = $this->getutf8($str);
        $divList = $this->divList($str);
        $content = array();
        foreach($divList[0] as $k=>$v){
            //�����жϣ����������ݿ�ĺ�������վ��������һ�뻹�࣬��ô��ֱ�ӱ���
            //��Ҫ�жϣ��ǲ���һ��A��ǩ���������ݶ�����
            if($this->chineseCount($v)/(strlen($v)/3) >= 0.4 && $this->checkHref($v)){
                array_push($content,strip_tags($v,"<p><br>"));
            }else if($this->makeScore($v) >= 3){
                //Ȼ����ݷ����жϣ��������3�ֵģ�����
                array_push($content,strip_tags($v,"<p><br>"));
            }else{
                //��Щ�����ų���������
            }
        }
        return implode("",$content);
    }

    /*
     * �ж��ǲ���һ��A��ǩ���������ݶ�����
     * �жϷ�������A��ǩ���������ݶ�ȥ���󣬿��Ƿ񻹺�������
     */
    private function checkHref($str){
        if(!preg_match("'<a[^>]*?>(.*)</a>'si",$str)){
            //���������A��ǩ���ǲ��ù��ˣ�99%������
            return true;
        }
        $clear_str = preg_replace("'<a[^>]*?>(.*)</a>'si","",$str);
        if($this->chineseCount($clear_str)){
            return true;
        }else{
            return false;
        }
    }

    function makeScore($str){
        $score = 0;
        //������
        $score += $this->score1($str);
        //�жϺ���P��ǩ
        $score += $this->score2($str);
        //�ж��Ƿ���br��ǩ
        $score += $this->score3($str);
        //�ж��Ƿ���li��ǩ
        $score -= $this->score4($str);
        //�ж��Ƿ񲻰����κα�����
        $score -= $this->score5($str);
        //�ж�javascript�ؼ���
        $score -= $this->score6($str);
        //�ж�<li><a�����ı�ǩ
        $score -= $this->score7($str);
        return $score;
    }

    /*
     * �ж��Ƿ��б�����
     */
    private function score1($str){
        //ȡ�ñ����ŵĸ���
        $count = preg_match_all("/(��|��|��|��|��|��|��|��|��|��|��)/si",$str,$out);
        if($count){
            return $count * 2;
        }else{
            return 0;
        }
    }

    /*
     * �ж��Ƿ���P��ǩ
     */
    private function score2($str){
        $count = preg_match_all("'<p[^>]*?>.*?</p>'si",$str,$out);
        return $count * 2;
    }

    /*
     * �ж��Ƿ���BR��ǩ
     */
    private function score3($str){
        $count = preg_match_all("'<br/>'si",$str,$out) + preg_match_all("'<br>'si",$str,$out);
        return $count * 2;
    }

    /*
     * �ж��Ƿ���li��ǩ
     */
    private function score4($str){
        //�ж��٣������ٷ� * 2
        $count = preg_match_all("'<li[^>]*?>.*?</li>'si",$str,$out);
        return $count * 2;
    }

    /*
     * �ж��Ƿ񲻰����κα�����
     */
    private function score5($str){
        if(!preg_match_all("/(��|��|��|��|��|��|��|��|��|��|��|��|��)/si",$str,$out)){
            return 2;
        }else{
            return 0;
        }
    }

    /*
     * �ж��Ƿ����javascript�ؼ��֣��м�����������
     */
    private function score6($str){
        $count = preg_match_all("'javascript'si",$str,$out);
        return $count;
    }

    /*
     * �ж�<li><a�����ı�ǩ���м�����������
     */
    private function score7($str){
        $count = preg_match_all("'<li[^>]*?>.*?<a'si",$str,$out);
        return $count * 2;
    }

    /*
     * ȥ��
     */
    private function clear($str){
        $str = preg_replace("'<script[^>]*?>.*?</script>'si","",$str);
        $str = preg_replace("'<style[^>]*?>.*?</style>'si","",$str);
        $str = preg_replace("'<!--.*?-->'si","",$str);
        return $str;
    }

    /*
     * ȡ�����ݿ�
     */
    private function divList($str){
        preg_match_all("'<[^a][^>]*?>.*?</[^>]*?>'si",$str,$divlist);
        return $divlist;
    }
}