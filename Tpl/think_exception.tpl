<div class="notice" style="width:950px;margin:20px auto;padding:10px;color:#666;background:#FCFCFC;border:1px solid #E0E0E0;">
<h2 style="border-bottom:1px solid #DDD;padding:8px 0;font-size:25px;">系统发生错误 </h2>

<?php if(isset($e['file'])) {?>
	<p><strong>错误位置:</strong>　FILE: <span class="red" style="color:red;font-weight:bold;"><?php echo $e['file'] ;?></span>　LINE: <span class="red" style="color:red;font-weight:bold;"><?php echo $e['line'];?></span></p>
<?php }?>
	<p class="title" style="margin:4px 0;color:#F60;font-weight:bold;margin:6px;">[ 错误信息 ]</p>
	<p class="message" style="background:#FFD;color:#2E2E2E;border:1px solid #E0E0E0;margin:6px;padding:10px;"><?php echo strip_tags($e['message']);?></p>
<?php if(isset($e['trace'])) {?>
	<p class="title" style="margin:4px 0;color:#F60;font-weight:bold;">[ TRACE ]</p>
	<p id="trace" style="background:#E7F7FF;border:1px solid #E0E0E0;color:#535353;">
	<?php echo nl2br($e['trace']);?>
	</p>
<?php }?>
</div>