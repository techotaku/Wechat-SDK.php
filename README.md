# 微信公众平台SDK

[![Latest Stable Version](https://poser.pugx.org/techotaku/wechat-sdk/v/stable.png)](https://packagist.org/packages/techotaku/wechat-sdk) [![Total Downloads](https://poser.pugx.org/techotaku/wechat-sdk/downloads.png)](https://packagist.org/packages/techotaku/wechat-sdk) [![Build Status](https://travis-ci.org/techotaku/Wechat-SDK.php.png?branch=master)](https://travis-ci.org/techotaku/Wechat-SDK.php) [![Coverage Status](https://coveralls.io/repos/techotaku/Wechat-SDK.php/badge.png?branch=master)](https://coveralls.io/r/techotaku/Wechat-SDK.php?branch=master)

## Overview
PHP版本的微信公众平台SDK。可以很方便地解析请求、发送回复。

## Installation
#### Composer
把下面的配置代码加入你的`composer.json`的`require`段。
```json
"techotaku/wechat-sdk": ">=1.0.1"
```
然后使用[Composer](https://getcomposer.org/)来安装SDK。
```bash
composer install
```
如果[Packagist](https://packagist.org)故障或者不可用导致无法安装SDK的，可以使用[Satis](https://github.com/composer/satis "Satis - Package Repository Generator")或者Artifact来进行本地安装，详见Composer文档中的[Repositories](https://getcomposer.org/doc/05-repositories.md#hosting-your-own)。

#### Manually
复制src/Wechat.php到任意位置，然后`require`或者`require_once`。

## Usage
#### Autoload
如果你使用了Composer来安装SDK，使用以下代码即可完成自动加载的配置。
```php
require 'vendor/autoload.php';
```
SDK位于全局命名空间下。

#### Initialization
实例化`Wechat`即可完成初始化。
```php
define('TOKEN', ''); // 微信通信令牌，在公众平台管理后台设置
define('DEBUG', TRUE); // 调试模式开关，指示是否将错误信息通过文本消息回复（如果可能）。
$wechat = new \Wechat(TOKEN, DEBUG);
```
初始化之后SDK将尝试从`$_GET[]`和`$GLOBALS['HTTP_RAW_POST_DATA']`中读取信息解析请求并进行初步处理。

#### Parsing and processing
SDK提供以下方法对请求进行解析。
* `isApiValidation()`：返回一个bool值，指示当前请求是否为微信公众平台进行开发者验证的echoback请求。当结果为`TRUE`时，SDK已经将`echostr`的内容输出，SDK的调用方在此分支逻辑中请勿继续输出信息，否则会导致验证失败。（SDK并未使用常见的`exit()`来处理echoback，因此在判断为验证请求之后，PHP脚本执行并不会中止，调用方可以继续处理“验证请求”这一分支的剩余逻辑，只要不再进行输出即可。）当结果为`TRUE`时，`isValid()`的结果必定为`FALSE`。
* `isValid()`：返回一个bool值，指示当前请求是否为一个有效的请求。有效的请求是指，当前请求的签名正确、POST数据为可以被解析的XML、解析后的XML数据至少包括消息发送者和消息接收者。
* `getRequestType()`：返回一个字符串，指示请求类型。请求类型的定义如下（建议使用类常量，不要直接使用字符串）： 

```php
  /**
   * 微信公众平台传入消息类
   * 包含传入消息类型常量定义
   */
  class WechatRequest {
    const text = 'text';
    const image = 'image';
    const location = 'location';
    const link = 'link';
    const subscribe = 'subscribe';
    const voice = 'voice';
    const unsubscribe = 'unsubscribe';
    const unknown = 'unknown';
  }
```
* `getRequest([$key])`：根据给定的可选参数key返回请求中携带的数据，若对应的key不存在则返回`FALSE`。若省略参数key，则返回完整的请求信息数组。

SDK提供以下方法回复消息。
* `sendResponse($type, $params)`： 回复指定类型的消息。方法原型及参数说明如下：

```php
    /**
     * 回复消息
     *
     * @param  string  $type     消息类型，在类WechatResponse中定义
     * @param  string  $params   消息参数，与消息类型相关：
     *     WechatResponse::text  文本消息  $params为消息内容
     *         $params                        消息文本
     *     WechatResponse::news  图文消息  $params为数组
     *         $params                        由单条图文消息类型 WechatNewsResponseItem 组成的数组
     *     WechatResponse::music 音乐消息  $params为关联数组
     *         $params['title']
     *         $params['description']
     *         $params['musicUrl']
     *         $params['hqMusicUrl']
     * @return void
     */
    public function sendResponse($type, $params)
```

单条图文消息类`WechatNewsResponseItem`的数组构造示例如下：
```php
$array = array(
            new WechatNewsResponseItem('图文消息标题', '图文消息说明', '图片地址', '点击转向的链接'),
            new WechatNewsResponseItem('图文消息标题', '图文消息说明', '图片地址', '点击转向的链接')
            );
```

## License
The MIT License (MIT)  
Copyright (c) 2013 Ian Li

See LICENSE
