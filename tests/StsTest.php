<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2018/9/20
 * Time: 下午1:15
 */


use Dongkaipo\Aliyun\Sts;
use PHPUnit\Framework\TestCase;

class StsTest extends TestCase
{

    public function testGetToken()
    {
        $sts = new Sts();
        $token = $sts->getToken();
        $this->assertEquals('hello', $token);
    }


}