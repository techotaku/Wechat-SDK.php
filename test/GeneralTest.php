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
   * General Test
   */
  class WechatSdkGeneralTest extends WechatSdkTestBase
  {
    protected function setUp()
    {
      parent::setUp();
    }

    public function testApiValidation()
    {
      $echostr = '9eabb7918cbad53305f7eae647cf1402e2fc7836';
      $_GET['echostr'] = $echostr;
      $this->expectOutputString($echostr);

      $wechat = new Wechat($this->token);
      $this->assertTrue($wechat->isApiValidation());
    }

    public function testBlankSignature()
    {
      $_GET['signature'] = '';
      $this->expectOutputString('');

      $wechat = new Wechat($this->token);
      $this->assertFalse($wechat->isValid());
    }

    public function testEmptyPOST()
    {
      $this->expectOutputString('');

      $wechat = new Wechat($this->token);
      $this->assertFalse($wechat->isValid());
    }

  }
