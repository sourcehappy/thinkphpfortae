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
		页面将在 <span class="wait" style="color:blue;font-weight:bold">{$waitSecond}</span> 秒后自动关闭，如果不想等待请点击 <a href="{$jumpUrl}">这里</a> 关闭
	{else}
		页面将在 <span class="wait" style="color:blue;font-weight:bold">{$waitSecond}</span> 秒后自动跳转，如果不想等待请点击 <a href="{$jumpUrl}">这里</a> 跳转
	{/if}
	</div>
</div>
