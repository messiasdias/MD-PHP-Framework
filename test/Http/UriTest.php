<?php

namespace MessiasDias\PHPLibrary\Test\Http;
use PHPUnit\Framework\TestCase;
use MessiasDias\PHPLibrary\Http\Uri;

class UriTest extends TestCase {

    /**
     * getUri function
     * Return a new instance of MessiasDias\PHPLibrary\Http\Uri
     * @return void
     */
    private static function getUri()
    {
        return new Uri();
    }

     /**
     * testContruct function
     * Check if self::Uri() is an new instance of MessiasDias\PHPLibrary\Http\Uri
     * @return void
     */
    public function testContruct()
    {
        $this->assertInstanceOf(
            Uri::class,
            new Uri()
        );
    }

    /**
     * testScheme function
     * Check Uri::getScreme()
     * @return void
     */
    public function testScheme()
    {
        $uri  = self::getUri();
        
        //without scheme especification  
        $this->assertEquals('http', $uri->getScheme());

        //with scheme and port especification  
        $uri->withPort(9090);
        $this->assertEquals('http', $uri->getScheme());

        //without scheme https especification  
        $uri->withPort(443);
        $this->assertEquals('https', $uri->getScheme());
    }

    /**
     * testWithScheme function
     * Check Uri::withScreme()
     * @return void
     */
    public function testWithScheme()
    {
        $uri  = self::getUri();

        //Empty argument
        $newUri = $uri->withScheme('');
        $this->assertInstanceOf(Uri::class, $newUri);
        $this->assertEquals($newUri->getScheme(), '' );

        //String scheme argument http
        $newUri = $uri->withScheme('http');
        $this->assertInstanceOf(Uri::class, $newUri);
        $this->assertEquals($newUri->getScheme(), 'http' );

        //String scheme argument https
        $newUri2 = $uri->withScheme('https');
        $this->assertInstanceOf(Uri::class, $newUri2);
        $this->assertEquals($newUri->getScheme(), 'https' );
    }

    /**
     * testAuthority function
     * Check Uri::getAuthority()
     * @return void
     */
    public function testAuthority()
    {
        $uri  = self::getUri();

        //from host 'local' and port '80'
        $uri->withHost('local');
        $uri->withPort(80);
        $this->assertEquals( $uri->getAuthority(true), 'local:80' );
        
        //from host 'localhost' and port '443'
        $uri->withHost('localhost');
        $uri->withPort(443);
        $this->assertEquals( 'localhost', $uri->getAuthority() );
        $this->assertEquals( 'https', $uri->getScheme() );

         //from por '9090'
         $uri->withPort(9090);
         $this->assertEquals( 'localhost:9090', $uri->getAuthority() );
    } 



     /**
     * testGetUserInfo function
     * Check Uri::getUserInfo()
     * @return void
     */
    public function testGetUserInfo ()
    {
        $uri  = self::getUri();

        //With user and password credencials
        $this->assertinstanceOf(Uri::class, $uri->withUserinfo('messias', '12345') );
        $this->assertEquals('messias:12345', $uri->getUserInfo());
        
        //With user and without password credencials
        $uri->withUserinfo('messias');
        $this->assertEquals('messias', $uri->getUserInfo());

        //Without user or password credencials
        $uri->withUserinfo('');
        $this->assertEquals('', $uri->getUserInfo());
    }


    /**
     * testHost function
     * Check Uri::getHost()
     * @return void
     */
    public function testHost()
    {   
        $uri  = self::getUri();

        //without host especification
        $this->assertEquals('localhost', $uri->getHost());

        //with host especification
        $uri->withHost('local');
        $this->assertEquals('local', $uri->getHost());
    }


     /**
     * testPort function
     * Check Uri::getPort()
     * @return void
     */
    public function testPort()
    {   
        $uri  = self::getUri();
        
        //with http Port especification
        $uri->withPort(80);
        $this->assertEquals(80 ,$uri->getPort(true));

        //with https Port especification
        $uri->withPort(443);
        $this->assertEquals(443, $uri->getPort(true));

        //with outher Port especification
        $uri->withPort(9090);
        $this->assertEquals(9090, $uri->getPort());
    }




     /**
     * testPath function
     * Check Uri::getPath()
     * @return void
     */
    public function testPath()
    {   
        $uri  = self::getUri();

        //Check home as '/' Path
        $this->assertEquals('/', $uri->getPath());

         //Check test as '/test' Path
         $uri->withPath('/test');
         $this->assertInstanceOf(Uri::class, $uri);
         $this->assertEquals('/test', $uri->getPath());
    }




    /**
     * testFragment function
     * Check Uri::getFragment()
     * @return void
     */
    public function testFragment()
    {   
        $uri  = self::getUri();

        //Check Fragment ''
        $this->assertEquals('', $uri->getFragment());

         //Check Fragment test as '#test' 
         $uri->withFragment('test');
         $this->assertInstanceOf(Uri::class, $uri);
         $this->assertEquals('test', $uri->getFragment());
    }


     /**
     * testQuery function
     * Check Uri::getQuery()
     * @return void
     */
    public function testQuery()
    {   
        $uri  = self::getUri();

        //Check with empty query string 
        $this->assertEquals('', $uri->getQuery());

        //Check with 'test=1234' query string 
        $uri->withQuery('test=1234&');
        $this->assertEquals('test=1234', $uri->getQuery());
    }


}