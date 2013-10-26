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
    // 默认分页变量名
    protected $varPage;
    // 起始行数
    public $firstRow	;
    // 列表每页显示行数
    public $listRows	;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页栏每页显示的页数
    protected $rollPage   ;
	// 分页显示定制
    protected $config  =	array(
												'header'=>'条记录',
												'prev'=>'&nbsp;&nbsp;',
												'next'=>'下一页',
												'theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage%  %linkPage%  %downPage%',
												'small_theme'=>'%nowPage%/%totalPage% %upPage%  %downPage%'
												);

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows,$maxPage=999999999,$parameter='') {
        $this->varPage = C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;

		$this->listRows = !empty($listRows)?$listRows:C('PAGE_LISTROWS');
        $this->totalRows = $totalRows;
        $this->nowPage  = !empty($_GET[$this->varPage])?$_GET[$this->varPage]:1;
        $this->parameter = $parameter;
        $this->totalPages = ceil($this->totalRows/$this->listRows)>$maxPage ? $maxPage : ceil($this->totalRows/$this->listRows);     //总页数
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
     * 分页显示输出
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
     * 分页显示输出
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