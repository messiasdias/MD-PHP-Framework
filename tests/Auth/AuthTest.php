<?php 

namespace Tests\Auth;
use PHPUnit\Framework\TestCase;
use App\Auth\Auth;
use App\App;

final class AuthTest extends TestCase
{   

    public function testLogin(){
        $app = new App();
        $result = $app->auth()->login(['username'=> '@messiasdias', 'pass' => 'P@55w0rd123']);
        $this->assertObjectHasAttribute('status' , $result);
        $this->assertObjectHasAttribute('msg', $result);
        $this->assertObjectHasAttribute('status', $result);
        return $result;
    }

    public function testLoginSuccess(){
        $result = $this->testLogin();
        $this->assertEquals( true, $result->status);
        $this->assertObjectHasAttribute('token', $result);
    }

    public function testLoginError(){
        $result = $this->testLogin();
        $this->assertEquals(false, $result->status );
        return $result;
    }

    public function testLogout(){
        $app = new App();
        $this->assertEquals( true, $app->auth()->logout() );
    }

    public function testUserAuthenticated() {
        $app = new App();
        $user = $app->user();

        if( is_object($user) ){
            $this->assertObjectHasAttribute('id', $user);
            $this->assertObjectHasAttribute('name', $user);
            $this->assertObjectHasAttribute('rol', $user);
            $this->assertObjectHasAttribute('username', $user);
            $this->assertObjectHasAttribute('email', $user);
        }else{
            $this->testUserNoAuthenticated();
        }


    }

    public function testUserNoAuthenticated(){
        $app = new App();
        $user = $app->user();
        $this->assertEquals(false,$user);
    }
    

}