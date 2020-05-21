<?php

namespace MessiasDias\PHPLibrary\Test\Http;
use PHPUnit\Framework\TestCase;
use MessiasDias\PHPLibrary\Http\Request;

class RequestTest extends TestCase {

    /**
     * getRequest function
     * Return a new instance of MessiasDias\PHPLibrary\Http\Request
     * @return void
     */
    private static function getRequest()
    {
        return new Request();
    }

     /**
     * testContruct function
     * Check if self::Request() is an new instance of MessiasDias\PHPLibrary\Http\Request
     * @return void
     */
    public function testContruct()
    {
        $this->assertInstanceOf(
            Request::class,
            new Request()
        );
    }
}    