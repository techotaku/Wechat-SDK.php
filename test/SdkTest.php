<?php
require __DIR__ . '/../src/Wechat.php';

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
    $echostr = '9eabb7918cbad53305f7eae647cf1402e2fc7836';
    $_GET['echostr'] = $echostr;
    $this->expectOutputString($echostr);

    $wechat = new Wechat($this->token);
    $this->assertTrue($wechat->isApiValidation());
  }

  public function testEmptyPOST() {
    $this->expectOutputString('');

    $wechat = new Wechat($this->token);
    $this->assertFalse($wechat->isValid());    
  }

}
?>