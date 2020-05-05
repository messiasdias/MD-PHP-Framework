<?php 
//declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use App\App;

final class AppTest extends TestCase
{

    public function testNewApp(){

        $this->assertInstanceOf(
             new App(),
             App::class 
        );
    }


}
  