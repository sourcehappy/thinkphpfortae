### \###[帮派的讨论贴有问题欢迎反馈](http://bangpai.taobao.com/group/thread/15146155-278854218.htm)

---
简介：

## 淘宝U站TaeSDK版Thinkphp

**声明:** 本程序派生于[Thinkphp](http://www.thinkphp.cn/)，在此基础上进行了Tae环境支持的处理，转换为GBK编码工作。整体构架与官方基本保持一致，具体使用方法参考官方[thinkphp手册](http://doc.thinkphp.cn)即可。

### 新建项目

项目目录需用户手工创建，或在纯Cli(命令行环境）环境下运行
```php
php index.php
```

生成项目目录

手工建立目录，可参考svn内Example项目目录结构

index.php 项目首页文件的示例

```php
<?php
define('APP_DEBUG', true); //调试模式，显示调试信息，发布前，请设置成false
define("THINK_PATH","./ThinkPHP/");
define("APP_NAME","Index");
define("APP_PATH","./Index/");
require THINK_PATH.'ThinkPHP.php';
?>
```

项目config.php 文件的配置示例（带数据库配置）


```php
<?php
return array(
        'DEFAULT_TIMEZONE'=>'PRC',
        'DB_CHARSET'=>'gbk',
        'DB_TYPE'=>'pdo',
        'DB_USER'=>'root',
        'DB_PWD'=>'xxxxx',
        'DB_PREFIX'=>'pp_',
        'DB_DSN'   => 'mysql:host=localhost;dbname=pinphp_gbk',
        'DEFAULT_THEME'=>"creative",
        'SHOW_PAGE_TRACE'        => true, //是否显示调试信息，正式发布前请设置成false 
        'DATA_CACHE_TIME' => 1600,
 );
?>
```


### 调试方式

SDK下采用原生PHP操作，会因为SDK环境导致错误调试困难，本版利用官方Log服务很好的解决了这个问题，所以本地SDK和线上正式发布前环境，都建议开启调试模式，方便定位错误

本地SDK环境，调试模式下，程序运行日志，可以在SDK下项目目录对应的log文件内找到。

线上环境，程序运行日志，可以在admin.uz.taobao.com对应的版本上查看日志。


### 暂不支持功能

1.模板引擎默认支持官方smarty 引擎，thinkphp自带引擎无法支持

2. 模板的编译缓存目前由于官方SDK exit函数返回异常，程序无法中止，所以并不能很好的支持。

### 对TAE平台底层服务的支持

框架对tae平台的大部服务（cache,filestore,log)提供了集成环境支持，与原快捷函数方法兼容一至

S方法会使用CacheServer服务
```php
S($key,$value,$expire); //设置Cache
$str=S($key); //取$key的Cache值
S($key,null); //删除$key的Cache
```

F方法会使用FileStore服务
```php
F($filename,$content,$path); //将$content保存到文件
$content=F($filename); //取文件的内容
F($filename,null); //删除文件
```

Log::record($message) 方法可以在运行中添加日志，方便程序排错。


### 其它

另补充几个我patch函数如果您在自己PHP代码的碰到同样的问题，可以直接引用, 官方SDK不支持basename,dirname,is_file,这些基本函数，我们就采取自定义的方式来解决
```php
function basename($filepath, $suffix){
   $filepath=str_replace("\\","/",$filepath);
   $split = explode('/',$filepath);
   $count = count($split);
   $basename='';
   for($i=$count-1;$i>0;$i--)
   {
         if ($split[$i] ){
                 $basename=$split[$i];
                 break;
         }
   }
   return str_replace($suffix,'',$basename);
 }

 function dirname($filepath = null) {
   $filepath=str_replace("\\","/",$filepath);
   $split = explode('/',$filepath);
   $count = count($split);
   for($i=$count-1;$i>0;$i--)
   {
         if ($split[$i] ){
                 $count=$i;
                 break;
         }
   }
   if ($count==1 && $split[0]=='')
           return "\\";
   if ($count==1)
           return ".";

   $dirname='';
   for($i=0;$i<$count;$i++)
         $dirname.=$split[$i]."/";
   return substr($dirname,0,-1);
 }

 function is_file($file){
         return (bool)md5_file($file);
 }
```

关于官方的SDK下serialize的数组无法unserialize的问题，可以用这个函数解决
```php
function my_unserialize($serialize_str) {
	return unserialize(str_replace('U:', 's:', $serialize_str ));
} 
```