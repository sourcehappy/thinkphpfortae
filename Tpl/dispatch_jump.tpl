<div class="message" style="margin:10px auto;clear:both;padding:5px;border:1px solid silver; text-align:center; width:950px;border:0 none;font:14px Tahoma,Verdana;line-height:150%;background:white">
	<div class="msg" style="margin:20px 0px">
	{if $message}
	<span class="success" style="color:blue;font-weight:bold">{$msgTitle}{$message}</span>
	{else}
	<span class="error" style="color:red;font-weight:bold">{$msgTitle}{$error}</span>
	{/if}
	</div>
	<div class="tip">
	{if $closeWin}
		ҳ�潫�� <span class="wait" style="color:blue;font-weight:bold">{$waitSecond}</span> ����Զ��رգ��������ȴ����� <a href="{$jumpUrl}">����</a> �ر�
	{else}
		ҳ�潫�� <span class="wait" style="color:blue;font-weight:bold">{$waitSecond}</span> ����Զ���ת���������ȴ����� <a href="{$jumpUrl}">����</a> ��ת
	{/if}
	</div>
</div>
