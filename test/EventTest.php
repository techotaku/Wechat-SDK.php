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
   * Event Test
   */
  class WechatSdkEventTest extends WechatSdkTestBase
  {
    protected function setUp()
    {
      parent::setUp();
    }

    public function testGeneralFields()
    {
      $this->fillTextMsg('填充消息');
      $wechat = new Wechat($this->token);
      $this->expectOutputString('');

      $this->assertFalse($wechat->isApiValidation(), "Shouldn't be api validation request.");
      $this->assertTrue($wechat->isValid(), 'Request should be valid.');

      $this->assertEquals($this->toUser, $wechat->getRequest('tousername'));
      $this->assertEquals($this->fromUser, $wechat->getRequest('fromusername'));
      $this->assertEquals($this->time, $wechat->getRequest('createtime'));
      $this->assertEquals($this->msgid, $wechat->getRequest('msgid'));
    }

    public function testEventOnSubscribe()
    {
      $this->fillEvent('subscribe');
      $wechat = new Wechat($this->token);
      $this->expectOutputString('');

      $this->assertFalse($wechat->isApiValidation(), "Shouldn't be api validation request.");
      $this->assertTrue($wechat->isValid(), 'Request should be valid.');

      $this->assertEquals(WechatRequest::subscribe, $wechat->getRequestType());
      $this->assertEquals('', $wechat->getRequest('eventkey'));
    }

    public function testEventOnUnsubscribe()
    {
      $this->fillEvent('unsubscribe');
      $wechat = new Wechat($this->token);
      $this->expectOutputString('');

      $this->assertFalse($wechat->isApiValidation(), "Shouldn't be api validation request.");
      $this->assertTrue($wechat->isValid(), 'Request should be valid.');

      $this->assertEquals(WechatRequest::unsubscribe, $wechat->getRequestType());
      $this->assertEquals('', $wechat->getRequest('eventkey'));
    }

    public function testEventOnUnknown()
    {
      $this->fillUnknown('unknown info');
      $wechat = new Wechat($this->token);
      $this->expectOutputString('');

      $this->assertFalse($wechat->isApiValidation(), "Shouldn't be api validation request.");
      $this->assertTrue($wechat->isValid(), 'Request should be valid.');

      $this->assertEquals(WechatRequest::unknown, $wechat->getRequestType());
      $this->assertEquals('unknown info', $wechat->getRequest('unknown'));
    }

    public function testEventOnText()
    {
      $this->fillTextMsg('填充文本消息');
      $wechat = new Wechat($this->token);
      $this->expectOutputString('');

      $this->assertFalse($wechat->isApiValidation(), "Shouldn't be api validation request.");
      $this->assertTrue($wechat->isValid(), 'Request should be valid.');

      $this->assertEquals(WechatRequest::text, $wechat->getRequestType());
      $this->assertEquals('填充文本消息', $wechat->getRequest('content'));
    }

    public function testEventOnImage()
    {
      $this->fillImageMsg('https://travis-ci.org/techotaku/Wechat-SDK.php.png');
      $wechat = new Wechat($this->token);
      $this->expectOutputString('');

      $this->assertFalse($wechat->isApiValidation(), "Shouldn't be api validation request.");
      $this->assertTrue($wechat->isValid(), 'Request should be valid.');

      $this->assertEquals(WechatRequest::image, $wechat->getRequestType());
      $this->assertEquals('https://travis-ci.org/techotaku/Wechat-SDK.php.png', $wechat->getRequest('picurl'));
    }

    public function testEventOnLocation()
    {
      $this->fillLocationMsg('23.134521', '113.358803');
      $wechat = new Wechat($this->token);
      $this->expectOutputString('');

      $this->assertFalse($wechat->isApiValidation(), "Shouldn't be api validation request.");
      $this->assertTrue($wechat->isValid(), 'Request should be valid.');

      $this->assertEquals(WechatRequest::location, $wechat->getRequestType());

      $this->assertEquals('23.134521', $wechat->getRequest('location_x'));
      $this->assertEquals('113.358803', $wechat->getRequest('location_y'));
    }

    public function testEventOnLink()
    {
      $this->fillLinkMsg('techotaku/Wechat-SDK.php', '微信公众平台 PHP SDK', 'https://github.com/techotaku/Wechat-SDK.php');
      $wechat = new Wechat($this->token);
      $this->expectOutputString('');

      $this->assertFalse($wechat->isApiValidation(), "Shouldn't be api validation request.");
      $this->assertTrue($wechat->isValid(), 'Request should be valid.');

      $this->assertEquals(WechatRequest::link, $wechat->getRequestType());

      $this->assertEquals('techotaku/Wechat-SDK.php', $wechat->getRequest('title'));
      $this->assertEquals('微信公众平台 PHP SDK', $wechat->getRequest('description'));
      $this->assertEquals('https://github.com/techotaku/Wechat-SDK.php', $wechat->getRequest('url'));
    }

  }
