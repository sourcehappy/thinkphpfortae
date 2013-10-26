### \###[���ɵ������������⻶ӭ����](http://bangpai.taobao.com/group/thread/15146155-278854218.htm)

---
��飺

## �Ա�UվTaeSDK��Thinkphp

**����:** ������������[Thinkphp](http://www.thinkphp.cn/)���ڴ˻����Ͻ�����Tae����֧�ֵĴ���ת��ΪGBK���빤�������幹����ٷ���������һ�£�����ʹ�÷����ο��ٷ�[thinkphp�ֲ�](http://doc.thinkphp.cn)���ɡ�

### �½���Ŀ

��ĿĿ¼���û��ֹ����������ڴ�Cli(�����л���������������
```php
php index.php
```

������ĿĿ¼

�ֹ�����Ŀ¼���ɲο�svn��Example��ĿĿ¼�ṹ

index.php ��Ŀ��ҳ�ļ���ʾ��

```php
<?php
define('APP_DEBUG', true); //����ģʽ����ʾ������Ϣ������ǰ�������ó�false
define("THINK_PATH","./ThinkPHP/");
define("APP_NAME","Index");
define("APP_PATH","./Index/");
require THINK_PATH.'ThinkPHP.php';
?>
```

��Ŀconfig.php �ļ�������ʾ���������ݿ����ã�


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
        'SHOW_PAGE_TRACE'        => true, //�Ƿ���ʾ������Ϣ����ʽ����ǰ�����ó�false 
        'DATA_CACHE_TIME' => 1600,
 );
?>
```


### ���Է�ʽ

SDK�²���ԭ��PHP����������ΪSDK�������´���������ѣ��������ùٷ�Log����ܺõĽ����������⣬���Ա���SDK��������ʽ����ǰ�����������鿪������ģʽ�����㶨λ����

����SDK����������ģʽ�£�����������־��������SDK����ĿĿ¼��Ӧ��log�ļ����ҵ���

���ϻ���������������־��������admin.uz.taobao.com��Ӧ�İ汾�ϲ鿴��־��


### �ݲ�֧�ֹ���

1.ģ������Ĭ��֧�ֹٷ�smarty ���棬thinkphp�Դ������޷�֧��

2. ģ��ı��뻺��Ŀǰ���ڹٷ�SDK exit���������쳣�������޷���ֹ�����Բ����ܺܺõ�֧�֡�

### ��TAEƽ̨�ײ�����֧��

��ܶ�taeƽ̨�Ĵ󲿷���cache,filestore,log)�ṩ�˼��ɻ���֧�֣���ԭ��ݺ�����������һ��

S������ʹ��CacheServer����
```php
S($key,$value,$expire); //����Cache
$str=S($key); //ȡ$key��Cacheֵ
S($key,null); //ɾ��$key��Cache
```

F������ʹ��FileStore����
```php
F($filename,$content,$path); //��$content���浽�ļ�
$content=F($filename); //ȡ�ļ�������
F($filename,null); //ɾ���ļ�
```

Log::record($message) ���������������������־����������Ŵ�


### ����

���伸����patch������������Լ�PHP���������ͬ�������⣬����ֱ������, �ٷ�SDK��֧��basename,dirname,is_file,��Щ�������������ǾͲ�ȡ�Զ���ķ�ʽ�����
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

���ڹٷ���SDK��serialize�������޷�unserialize�����⣬����������������
```php
function my_unserialize($serialize_str) {
	return unserialize(str_replace('U:', 's:', $serialize_str ));
} 
```