<?php 

namespace Test\Outhers;
use PHPUnit\Framework\TestCase;
use App\Outhers\Log;

final class LogTest extends TestCase
{
    public function testLogin(){
        $log = new Log('Test Log');
        $this->assertInstanceOf( $log, Log::class );
    }   


}    