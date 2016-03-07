<?php

/**
 * code test case.
 */
class codeTester extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \Yaf\Application
     */
    private $yaf;
    private static $_send_code;
    private static $_close_hash;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated codeTester::setUp()
        $this->yaf = \Init::start();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated codeTester::tearDown()
        $this->yaf = null;
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }


    public function testSendCode(){
        $_POST = [
            'msisdn' =>  '18926472855',
            'code_type' => 1,
            'code_len' => 4,
            'code_use' => '测试',
            'return_code' => 1
        ];

        $response = $this->yaf->getDispatcher()->dispatch(new \Yaf\Request\Simple(NULL, 'index', 'code', 'send'));
        $json = $response->getBody();
        $data = json_decode($json, true);

        $this->assertArrayHasKey('code', $data['data'], $json);
        self::$_send_code = $data['data']['code'];

        echo "pass: ".__METHOD__, PHP_EOL;
    }

    public function testVerify(){

        if (!self::$_send_code){
            $this->assertEmpty(self::$_send_code, '没有接收到验证码');
            return ;
        }

        $_POST = [
            'msisdn' => '18926472855',
            'code' => self::$_send_code,
        ];

        $response = $this->yaf->getDispatcher()->dispatch(new \Yaf\Request\Simple(NULL, 'index', 'code', 'verify'));
        $json = $response->getBody();
        $data = json_decode($json, true);

        $this->assertEquals(0, $data['code'], $json);

        self::$_close_hash = $data['data']['hash'];

        echo "pass: ".__METHOD__, ', hash: ', self::$_close_hash, PHP_EOL;
    }

    public function testClose(){
        if (!self::$_close_hash){
            $this->assertEmpty(self::$_close_hash, '没有接收到关闭hash');
            return;
        }

        $_POST = [
            'hash' => urldecode(self::$_close_hash)
        ];

        $response = $this->yaf->getDispatcher()->dispatch(new \Yaf\Request\Simple(NULL, 'index', 'code', 'close'));
        $json = $response->getBody();
        $data = json_decode($json, true);

        $this->assertEquals(0, $data['code'], $json);

        echo "pass: ".__METHOD__, PHP_EOL;
    }

    public function testSendCode2(){
        $_POST = [
            'msisdn' =>  '18911404267',
            'code_type' => 0,
            'code_len' => 4,
            'code_use' => '测试2',
            'return_code' => 1
        ];

        $response = $this->yaf->getDispatcher()->dispatch(new \Yaf\Request\Simple(NULL, 'index', 'code', 'send'));
        $json = $response->getBody();
        $data = json_decode($json, true);

        $this->assertArrayHasKey('code', $data['data'], $json);
        self::$_send_code = $data['data']['code'];

        echo "pass: ".__METHOD__, PHP_EOL;
    }


    public function testVerify2(){

        if (!self::$_send_code){
            $this->assertEmpty(self::$_send_code, '没有接收到验证码');
            return ;
        }

        $_POST = [
            'msisdn' => '18911404267',
            'code' => self::$_send_code,
            'close_success' => 1
        ];

        $response = $this->yaf->getDispatcher()->dispatch(new \Yaf\Request\Simple(NULL, 'index', 'code', 'verify'));
        $json = $response->getBody();
        $data = json_decode($json, true);

        $this->assertEquals(0, $data['code'], $json);

        echo "pass: ".__METHOD__, PHP_EOL;
    }

    public function testVerifyError(){

        if (!self::$_send_code){
            $this->assertEmpty(self::$_send_code, '没有接收到验证码');
            return ;
        }

        $_POST = [
            'msisdn' => '18911404267',
            'code' => '123409',
            'close_success' => 1
        ];

        $response = $this->yaf->getDispatcher()->dispatch(new \Yaf\Request\Simple(NULL, 'index', 'code', 'verify'));
        $json = $response->getBody();
        $data = json_decode($json, true);
        $_tmp = $data['code'] > 200 ? false : true;

        $this->assertEquals(false, $_tmp, $json);

        echo "pass: ".__METHOD__, PHP_EOL;
    }

}

