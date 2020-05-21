<?php 
//declare(strict_types=1);
namespace Test\App;

use PHPUnit\Framework\TestCase;
use App\App;

final class AppTest extends TestCase
{

    public static $app;

    public function __construct(){
        parent::__construct();
        self::$app = self::instacieApp();
    }

    public static function instacieApp(){
        $app = new App();
        $app->request->url = '/';
        $app->request->method = 'get';
        return $app;
    }


    public function testStart(){
        $this->assertInstanceOf(
            App::class,
            new App()
        );
    }


    


}
  