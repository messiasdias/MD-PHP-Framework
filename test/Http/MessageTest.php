<?php

namespace MessiasDias\PHPLibrary\Test\Http;
use PHPUnit\Framework\TestCase;
use MessiasDias\PHPLibrary\Http\Message;

class MessageTest extends TestCase {
    /**
     * getMessage function
     * Return a new instance of MessiasDias\PHPLibrary\Http\Message
     * @return void
     */
    private static function getMessage()
    {
        return new Message();
    }

     /**
     * testContruct function
     * Check if self::Message() is an new instance of MessiasDias\PHPLibrary\Http\Message
     * @return void
     */
    public function testContruct()
    {
        $this->assertInstanceOf(
            Message::class,
            self::getMessage()
        );
    }


    public function testProtocolVersion()
    {
        $message = self::getMessage();
        // with Version 1.0
        $this->assertEquals('1.0', $message->getProtocolVersion());

        // with Version 1.1
        $message->withProtocolVersion('1.1');
        $this->assertinstaneceOf(Message::class, $message);
        $this->assertEquals('1.1', $message->getProtocolVersion());
    }

}