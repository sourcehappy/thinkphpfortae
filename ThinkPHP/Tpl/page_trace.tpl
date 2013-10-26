<div id="think_page_trace" style="width:950px;background:white;margin:6px auto;font-size:14px;border:1px dashed silver;padding:8px">
<fieldset id="querybox" style="margin:5px;">
<legend style="color:gray;font-weight:bold">“≥√ÊTrace–≈œ¢</legend>
<div style="overflow:auto;height:300px;text-align:left;">
<?php $_trace = trace();foreach ($_trace as $key=>$info){
echo $key.' : '.$info.'<br/>';
}?>
</div>
</fieldset>
</div>