<?php
/**
 * 微信公众平台 PHP SDK
 *
 * @author     Ian Li <i@techotaku.net>
 * @copyright  Ian Li <i@techotaku.net>, All rights reserved.
 * @link       https://github.com/techotaku/Wechat-SDK.php
 * @license    MIT License
 */

  /**
   * 微信公众平台处理类
   */
  class Wechat {

    /**
     * 调试模式，将内部错误通过文本消息回复显示
     *
     * @var boolean
     */
    private $debug;

    /**
     * 以数组的形式保存微信服务器每次发来的请求
     *
     * @var array
     */
    private $request;

    /**
     * 初始化Wechat对象，尝试解析请求并保存数据
     * 若此次请求为验证请求，则自动处理并中止脚本执行
     *
     * @param string $token 令牌
     */
    public function __construct($token, $debug = FALSE) {
      $this->debug = $debug;
      $this->request = NULL;

      // 验证签名
      if ($this->validateSignature($token)) {

        // 判断是否为Api接入验证请求
        if ($this->isApiValidation()) {

          // 处理接入验证
          echo $_GET['echostr'];

        } else {

          // 正常请求
          if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {

            try {
              // 尝试解析请求数据
              $xml = (array) simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'], 'SimpleXMLElement', LIBXML_NOCDATA);
              // 将数组键名转换为小写，提高健壮性，减少因大小写不同而出现的问题
              $this->request = array_change_key_case($xml, CASE_LOWER);
            } catch (Exception $e) {
              // 解析过程中发生异常，清空数据表示解析失败
              $this->request = NULL;
            }

          }

        }

      }
    }

    /**
     * 判断是否成功保存了被解析的请求
     *
     * @return boolean
     */
    public function isValid() {
      // 确保请求非空，确保fromusername、tousername存在（确保sendError不会引用到空数据）
      return !is_null($this->request) && isset($this->request['fromusername']) && isset($this->request['tousername']);
    }

    /**
     * 获取本次请求中的参数，不区分大小写
     *
     * @param  string $param 参数名，默认为无参
     * @return mixed
     */
    public function getRequest($param = FALSE) {

      if ($param === FALSE) {
        return $this->request;
      }

      $param = strtolower($param);

      if (isset($this->request[$param])) {
        return $this->request[$param];
      }

      // 对应的参数不存在
      return FALSE;
    }

    /**
     * 获取传入消息的类型
     * 当请求无效时返回 WechatRequest::unknown
     *
     * @return string 代表消息类型的常量字符串，在类WechatRequest中定义。
     */
    public function getRequestType() {
      if ($this->isValid()) {
        //请求有效，继续分析类型
        try {
          switch ($this->getRequest('msgtype')) {

            case 'event':
              switch ($this->getRequest('event')) {

                case 'subscribe':
                  return WechatRequest::subscribe;
                  break;
                case 'unsubscribe':
                  return WechatRequest::unsubscribe;
                  break;
              }
              break;

            case 'voice':
              return WechatRequest::voice;
              break;

            case 'text':
              return WechatRequest::text;
              break;

            case 'image':
              return WechatRequest::image;
              break;

            case 'location':
              return WechatRequest::location;
              break;

            case 'link':
              return WechatRequest::link;
              break;

            default:
              return WechatRequest::unknown;
              break;
          }
        } catch (Exception $ex) {
          return WechatRequest::unknown;
        }
      } else {
        // 请求无效
        return WechatRequest::unknown;
      }
    }

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
    public function sendResponse($type, $params) {
      if ($this->isValid()) {
        // 仅在请求有效时回复
        try {

          switch ($type) {

            case WechatResponse::news:
              $response = new WechatNewsResponse($this->getRequest('fromusername'), $this->getRequest('tousername'), $params);
              break;

            case WechatResponse::music:
              $response = new WechatMusicResponse($this->getRequest('fromusername'), $this->getRequest('tousername'), $params['title'], $params['description'], $params['musicurl'], $params['hqmusicurl']);
              break;

            case WechatResponse::text:

            default:
              // 默认作为文本消息回复，$params视为文本内容
              $response = new WechatTextResponse($this->getRequest('fromusername'), $this->getRequest('tousername'), $params);
              break;
          }
          // 发送回复，停止脚本执行
          echo $response;

        } catch (Exception $ex) {
          $this->sendError($ex);
        }
      }
    }

    /**
     * 判断此次请求是否为Api验证请求
     *
     * @return boolean
     */
    public function isApiValidation() {
      return isset($_GET['echostr']);
    }

    /**
     * 验证此次请求的签名信息
     *
     * @param  string $token 验证信息
     * @return boolean
     */
    private function validateSignature($token) {
      if ( ! (isset($_GET['signature']) && isset($_GET['timestamp']) && isset($_GET['nonce']))) {
        return FALSE;
      }
      
      $signature = $_GET['signature'];
      $timestamp = $_GET['timestamp'];
      $nonce = $_GET['nonce'];

      $signatureArray = array($token, $timestamp, $nonce);
      sort($signatureArray);

      return sha1(implode($signatureArray)) == $signature;
    }

    /**
     * 以文本消息返回错误信息，并给引起异常的消息加星标
     *
     * @param  string  $content  错误信息
     * @return void
     */
    protected function sendError($content) {
      if ($this->debug) {
        if ($content instanceof Exception) {
          $ex = $content;
          $template = <<<ERR
微信SDK 异常

%s
文件： %s
行号： %s
ERR;
          $content = sprintf($template, $ex->getMessage(), $ex->getFile(), $ex->getLine());
        }
        echo new WechatTextResponse($this->getRequest('fromusername'), $this->getRequest('tousername'), $content);
      }
    }

  }

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

  /**
   * 微信公众平台回复消息类抽象基类
   * 包含回复消息类型常量定义
   */
  abstract class WechatResponse {

    const text = 'text';
    const news = 'news';
    const music = 'music';

    protected $toUserName;
    protected $fromUserName;
    protected $template;

    public function __construct($toUserName, $fromUserName) {
      $this->toUserName = $toUserName;
      $this->fromUserName = $fromUserName;
    }

    abstract public function __toString();

  }

  /**
   * 用于回复的文本消息类型
   */
  class WechatTextResponse extends WechatResponse {

    protected $content;

    public function __construct($toUserName, $fromUserName, $content) {
      parent::__construct($toUserName, $fromUserName);

      $this->content = $content;
      $this->template = <<<XML
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[text]]></MsgType>
  <Content><![CDATA[%s]]></Content>
</xml>
XML;
    }

    public function __toString() {
      return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        $this->content
      );
    }

  }

  /**
   * 用于回复的音乐消息类型
   */
  class WechatMusicResponse extends WechatResponse {

    protected $title;
    protected $description;
    protected $musicUrl;
    protected $hqMusicUrl;

    public function __construct($toUserName, $fromUserName, $title, $description, $musicUrl, $hqMusicUrl) {
      parent::__construct($toUserName, $fromUserName);

      $this->title = $title;
      $this->description = $description;
      $this->musicUrl = $musicUrl;
      $this->hqMusicUrl = $hqMusicUrl;
      $this->template = <<<XML
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[music]]></MsgType>
  <Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
  </Music>
</xml>
XML;
    }

    public function __toString() {
      return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        $this->title,
        $this->description,
        $this->musicUrl,
        $this->hqMusicUrl
      );
    }

  }

  /**
   * 用于回复的图文消息类型
   */
  class WechatNewsResponse extends WechatResponse {

    protected $items = array();

    public function __construct($toUserName, $fromUserName, $items) {
      parent::__construct($toUserName, $fromUserName);

      $this->items = $items;
      $this->template = <<<XML
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[news]]></MsgType>
  <ArticleCount>%s</ArticleCount>
  <Articles>
    %s
  </Articles>
</xml>
XML;
    }

    public function __toString() {
      return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        count($this->items),
        implode($this->items)
      );
    }

  }

  /**
   * 单条图文消息类型
   */
  class WechatNewsResponseItem {

    protected $title;
    protected $description;
    protected $picUrl;
    protected $url;
    protected $template;

    public function __construct($title, $description, $picUrl, $url) {
      $this->title = $title;
      $this->description = $description;
      $this->picUrl = $picUrl;
      $this->url = $url;
      $this->template = <<<XML
<item>
  <Title><![CDATA[%s]]></Title>
  <Description><![CDATA[%s]]></Description>
  <PicUrl><![CDATA[%s]]></PicUrl>
  <Url><![CDATA[%s]]></Url>
</item>
XML;
    }

    public function __toString() {
      return sprintf($this->template,
        $this->title,
        $this->description,
        $this->picUrl,
        $this->url
      );
    }

  }
