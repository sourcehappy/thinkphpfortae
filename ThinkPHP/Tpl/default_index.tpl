<?php
// ������ϵͳ�Զ����ɣ�����������;
class IndexAction extends Action {
    public function index(){
        header("Content-Type:text/html; charset=gbk");
        echo '<div style="margin:10px auto;font-weight:normal;color:blue;width:950px;text-align:center;border:1px solid silver;padding:8px;font-size:14px;font-family:Tahoma">^_^ Hello,��ӭʹ��<span style="font-weight:bold;color:red">ThinkPHP For Tae</span>(by golove2)</div>';
    }
}