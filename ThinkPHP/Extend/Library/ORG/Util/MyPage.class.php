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
// $Id$

class MyPage extends Think {
    // Ĭ�Ϸ�ҳ������
    protected $varPage;
    // ��ʼ����
    public $firstRow	;
    // �б�ÿҳ��ʾ����
    public $listRows	;
    // ҳ����תʱҪ���Ĳ���
    public $parameter  ;
    // ��ҳ��ҳ����
    protected $totalPages  ;
    // ������
    protected $totalRows  ;
    // ��ǰҳ��
    protected $nowPage    ;
    // ��ҳ��������ҳ��
    protected $coolPages   ;
    // ��ҳ��ÿҳ��ʾ��ҳ��
    protected $rollPage   ;
	// ��ҳ��ʾ����
    protected $config  =	array(
												'header'=>'����¼',
												'prev'=>'&nbsp;&nbsp;',
												'next'=>'��һҳ',
												'theme'=>' %totalRow% %header% %nowPage%/%totalPage% ҳ %upPage%  %linkPage%  %downPage%',
												'small_theme'=>'%nowPage%/%totalPage% %upPage%  %downPage%'
												);

    /**
     +----------------------------------------------------------
     * �ܹ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  �ܵļ�¼��
     * @param array $listRows  ÿҳ��ʾ��¼��
     * @param array $parameter  ��ҳ��ת�Ĳ���
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows,$maxPage=999999999,$parameter='') {
        $this->varPage = C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;

		$this->listRows = !empty($listRows)?$listRows:C('PAGE_LISTROWS');
        $this->totalRows = $totalRows;
        $this->nowPage  = !empty($_GET[$this->varPage])?$_GET[$this->varPage]:1;
        $this->parameter = $parameter;
        $this->totalPages = ceil($this->totalRows/$this->listRows)>$maxPage ? $maxPage : ceil($this->totalRows/$this->listRows);     //��ҳ��
        $this->rollPage = 5; //C('PAGE_ROLLPAGE');
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     +----------------------------------------------------------
     * ��ҳ��ʾ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function show() {
        if(0 == $this->totalRows) return '';
		$url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }else{
			$url .= 'a=index';
		}

    	if($this->totalRows > $this->listRows) {

		$offset = floor($this->rollPage * 0.5);

		if($this->rollPage > $this->totalPages ) {
			$from = 1;
			$to = $this->totalPages;
		} else {
			$from = $this->nowPage - $offset;
			$to = $from + $this->rollPage - 1;
			if($from < 1) {
				$to = $this->nowPage + 1 - $from;
				$from = 1;
				if($to - $from < $this->rollPage) {
					$to = $this->rollPage;
				}
			} elseif($to > $this->totalPages) {
				$from = $this->totalPages - $this->rollPage + 1;
				$to = $this->totalPages;
			}
		}
		$dot = '...';

		$upPage = ($this->nowPage - $offset > 1 && $this->totalPages > $this->rollPage ? '<a href="'.$url.'&'.$this->varPage.'=1" class="first">1 '.$dot.'</a>' : '').
		($this->nowPage > 1 ? '<a href="'.$url.'&'.$this->varPage.'='.($this->nowPage - 1).'" class="prev">'.$this->config['prev'].'</a>' : '');

		for($i = $from; $i <= $to; $i++) {
			$linkPage .= $i == $this->nowPage ? '<span class="current">'.$i.'</span>' :
			'<a href="'.$url.'&'.$this->varPage.'='.$i.'">'.$i.'</a>';
		}

		$downPage = ($to < $this->totalPages ? '<a href="'.$url.'&'.$this->varPage.'='.$this->totalPages.'" class="last">'.$dot.' '.$this->totalPages.'</a>' : '').
		($this->nowPage < $this->totalPages ? '<a href="'.$url.'&'.$this->varPage.'='.($this->nowPage + 1).'" class="nxt">'.$this->config['next'].'</a>' : '');
		//.	( $this->totalPages > $this->rollPage  ? '<input type="text" name="custompage" size="3" onkeydown="if(event.keyCode==13) {window.location=\''.$url.'&'.$p.'=\'+this.value; }" />' : '');
		}

		$pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%linkPage%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$linkPage),$this->config['theme']);
        return $pageStr;
    }

    /**
     +----------------------------------------------------------
     * ��ҳ��ʾ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function smallShow() {
        if(0 == $this->totalRows) return '';
		echo $_SERVER['REQUEST_URI'];
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }else{
			$url .= 'a=index';
		}

    	if($this->totalRows > $this->listRows) {

		$offset = floor($this->rollPage * 0.5);

		$dot = '...';

		$upPage =($this->nowPage > 1 ? '<a href="'.$url.'&'.$this->varPage.'='.($this->nowPage - 1).'" class="prev">'.$this->config['prev'].'</a>' : '');

		$downPage =($this->nowPage < $this->totalPages ? '<a href="'.$url.'&'.$this->varPage.'='.($this->nowPage + 1).'" class="nxt">'.$this->config['next'].'</a>' : '');

		}

		$pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%linkPage%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$linkPage),$this->config['small_theme']);
        return $pageStr;
    }


}
?>