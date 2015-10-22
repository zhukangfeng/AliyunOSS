#AliyunOSS

> 最近更新：AliyunOSS v1.1 发布，增加内外网配置分离。


```
    ___     __    _                                    ____    _____   _____
   /   |   / /   (_)   __  __  __  __   ____          / __ \  / ___/  / ___/
  / /| |  / /   / /   / / / / / / / /  / __ \        / / / /  \__ \   \__ \
 / ___ | / /   / /   / /_/ / / /_/ /  / / / /       / /_/ /  ___/ /  ___/ /
/_/  |_|/_/   /_/    \__, /  \__,_/  /_/ /_/        \____/  /____/  /____/
                    /____/
```

AliyunOSS 是阿里云 OSS 官方 SDK 的 Composer 封装，支持任何 PHP 项目，包括 Laravel、Symfony、TinyLara 等等。


##更新记录
* 2015-10-22 `Release v1.3` 添加阿里云OSS中object的删除，复制，移动的函数接口
* 2015-08-07 `Release v1.2` 修复内存泄露 bug。
* 2015-01-12 `Release v1.1` 增加内外网配置分离。
* 2015-01-09 `Release v1.0` 完善功能，增加 Laravel 框架详细使用教程及代码。

##安装

将以下内容增加到 composer.json：

```json
require: {
    "zhu/aliyun-oss": "1.2"
}
```

然后运行 `composer update`。

##使用（以 Laravel 为例）

###构建 Service 文件

新建 `app/services/OSS.php`，内容可参考：[OSSExample.php](https://github.com/zhukangfeng/AliyunOSS/blob/master/OSSExample.php)：

```php
<?php

namespace App\Services;

use Zhu\AliyunOSS\AliyunOSS;

use Config;

class OSS {

  private $ossClient;

  public function __construct($isInternal = false)
  {
    $serverAddress = $isInternal ? Config::get('app.ossServerInternal') : Config::get('app.ossServer');
    $this->ossClient = AliyunOSS::boot(
      $serverAddress,
      Config::get('app.AccessKeyId'),
      Config::get('app.AccessKeySecret')
    );
  }

  public static function upload($ossKey, $filePath)
  {
    $oss = new OSS(true); // 上传文件使用内网，免流量费
    $oss->ossClient->setBucket('提前设置好的Bucket的名称');
    $oss->ossClient->uploadFile($ossKey, $filePath);
  }

  public static function getUrl($ossKey)
  {
    $oss = new OSS();
    $oss->ossClient->setBucket('提前设置好的Bucket的名称');
    return $oss->ossClient->getUrl($ossKey, new \DateTime("+1 day"));
  }

  public static function createBucket($bucketName)
  {
    $oss = new OSS();
    return $oss->ossClient->createBucket($bucketName);
  }

  public static function getAllObjectKey($bucketName)
  {
    $oss = new OSS();
    return $oss->ossClient->getAllObjectKey($bucketName);
  }

}
```

###放入自动加载

在 `composer.json` 中 `autoload -> classmap` 处增加配置：

```json
"autoload": {
    "classmap": [
      "app/services"
    ]
  }
```
然后运行 `composer dump-autoload`。

###增加相关配置
在 app/config/app.php 中增加四项配置：

```php
'ossServer' => '服务器外网地址', //青岛为 http://oss-cn-qingdao.aliyuncs.com
'ossServerInternal' => '服务器内网地址', //青岛为 http://oss-cn-qingdao-internal.aliyuncs.com
'AccessKeyId' => '阿里云给的AccessKeyId',
'AccessKeySecret' => '阿里云给的AccessKeySecret',
```

###使用

```php
use App\Services\OSS;

OSS::upload('文件名', '本地路径'); // 上传一个文件

echo OSS::getUrl('某个文件的名称'); // 打印出某个文件的外网链接

OSS::createBucket('一个字符串'); // 新增一个 Bucket。注意，Bucket 名称具有全局唯一性，也就是说跟其他人的 Bucket 名称也不能相同。

OSS::getAllObjectKey('某个 Bucket 名称'); // 获取该 Bucket 中所有文件的文件名，返回 Array。
```
##反馈

有问题请到 http://lvwenhan.com/laravel/425.html 下面留言。

##License
除 “版权所有（C）阿里云计算有限公司” 的代码文件外，遵循 [MIT license](http://opensource.org/licenses/MIT) 开源。
