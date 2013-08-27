<?php
require __DIR__ . '/../src/Wechat.php';
require __DIR__ . '/ExitTestHelper.php';

class WechatSdkTest extends PHPUnit_Framework_TestCase {
  protected $token;

  protected function setUp() {
    $this->token = 'wechat-SDK.php';

    $_GET['timestamp'] = time();
    $_GET['nonce'] = rand(10000000, 99999999);
    $signatureArray = array($this->token, $_GET['timestamp'], $_GET['nonce']);
    sort($signatureArray);
    $_GET['signature'] = sha1(implode($signatureArray));
  }

  public function testApiValidation() {
    ExitTestHelper::init();
    $echostr = '9eabb7918cbad53305f7eae647cf1402e2fc7836';
    $_GET['echostr'] = $echostr;
    $wechat = new Wechat($this->token);
    $this->assertEquals($echostr, ExitTestHelper::getFirstExitOutput(), 'ApiValidation Fail.');
    ExitTestHelper::clean();
  }

  public function testEmptyPOST() {
    $wechat = new Wechat($this->token);
    $this->assertFalse($wechat->isValid());
    $this->expectOutputString('');
  }

}
?>