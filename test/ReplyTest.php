<?php
/**
 * 微信公众平台 PHP SDK
 *
 * @author     Ian Li <i@techotaku.net>
 * @copyright  Ian Li <i@techotaku.net>, All rights reserved.
 * @link       https://github.com/techotaku/Wechat-SDK.php
 * @license    MIT License
 */

  require_once __DIR__ . '/SdkTestBase.php';

  /**
   * Reply Test
   */
  class WechatSdkReplyTest extends WechatSdkTestBase
  {
    protected function setUp()
    {
      parent::setUp();
    }

    private function prepareResponse($response)
    {
      $xml = (array) simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
      $array = array_change_key_case($xml, CASE_LOWER);

      // 回复的to、from与填充的传入消息的to、from相反
      $this->assertEquals($this->fromUser, $array['tousername']);
      $this->assertEquals($this->toUser, $array['fromusername']);

      return $array;
    }

    public function testReplyText()
    {
      $this->fillTextMsg('收到文本消息');
      $this->setOutputCallback(array(&$this, 'outputCallbackText'));
      $wechat = new Wechat($this->token);

      // 发出回复
      $wechat->sendResponse(WechatResponse::text, '回复文本消息');
    }

    protected function outputCallbackText($output)
    {
      $response = $this->prepareResponse($output);

      $this->assertEquals(WechatResponse::text, $response['msgtype']);
      $this->assertEquals('回复文本消息', $response['content']);
    }

    public function testReplyMusic()
    {
      $this->fillTextMsg('收到消息');
      $this->setOutputCallback(array(&$this, 'outputCallbackMusic'));
      $wechat = new Wechat($this->token);

      $music = array('title' => '音乐标题',
                     'description' => '音乐说明',
                     'musicurl' => 'http://sample.net/music.mp3',
                     'hqmusicurl' => 'http://sample.net/hqmusic.mp3');
      // 回复音乐消息
      $wechat->sendResponse(WechatResponse::music, $music);
    }

    protected function outputCallbackMusic($output)
    {
      $response = $this->prepareResponse($output);

      $this->assertEquals(WechatResponse::music, $response['msgtype']);

      $expect = array('title' => '音乐标题',
                     'description' => '音乐说明',
                     'musicurl' => 'http://sample.net/music.mp3',
                     'hqmusicurl' => 'http://sample.net/hqmusic.mp3');

      $actual = array_change_key_case((array) $response['music'], CASE_LOWER);
      $this->assertEquals($expect, $actual);
    }

    public function testReplyNews()
    {
      $this->fillTextMsg('收到消息');
      $this->setOutputCallback(array(&$this, 'outputCallbackNews'));
      $wechat = new Wechat($this->token);

      $items = array(
        new WechatNewsResponseItem('Travis CI',
                                   'Free Hosted Continuous Integration Platform for the Open Source Community',
                                   'https://travis-ci.org/techotaku/Wechat-SDK.php.png',
                                   'https://travis-ci.org/techotaku/Wechat-SDK.php'),
        new WechatNewsResponseItem('Travis CI 2',
                                   '2 Free Hosted Continuous Integration Platform for the Open Source Community',
                                   'https://travis-ci.org/techotaku/Wechat-SDK.php.png',
                                   'https://travis-ci.org/techotaku/Wechat-SDK.php')
      );

      // 回复图文消息
      $wechat->sendResponse(WechatResponse::news, $items);
    }

    protected function outputCallbackNews($output)
    {
      $response = $this->prepareResponse($output);

      $this->assertEquals(WechatResponse::news, $response['msgtype']);
      $this->assertEquals('2', $response['articlecount']);

      $articles = (array) $response['articles'];

      $expect = array('title' => 'Travis CI',
                      'description' => 'Free Hosted Continuous Integration Platform for the Open Source Community',
                      'picurl' => 'https://travis-ci.org/techotaku/Wechat-SDK.php.png',
                      'url' => 'https://travis-ci.org/techotaku/Wechat-SDK.php');
      $actual = array_change_key_case((array) $articles['item'][0], CASE_LOWER);
      $this->assertEquals($expect, $actual);

      $expect = array('title' => 'Travis CI 2',
                      'description' => '2 Free Hosted Continuous Integration Platform for the Open Source Community',
                      'picurl' => 'https://travis-ci.org/techotaku/Wechat-SDK.php.png',
                      'url' => 'https://travis-ci.org/techotaku/Wechat-SDK.php');
      $actual = array_change_key_case((array) $articles['item'][1], CASE_LOWER);
      $this->assertEquals($expect, $actual);
    }

  }
