<?php
namespace App\Auth;
use App\App;
use \Firebase\JWT\JWT;
use App\Models\User;
use App\Maker\Maker;
/**
 * Token Class
 */
class Token
{
	private $app;

	/**
	* Method __construct
	*
	* @param App\App $app   A Object of App Class
	* @return  
	*/
	public function __construct(App $app){
		$this->app = $app;
	}


	/**
	* Method create
	*
	* @param App\Models\User $user   A Object of User Class
	* @param array|string|null $data - A Object of User Class
	* @return string token_encoded 
	*/

	public function create(User $user, $data=null){

		$token = array(
		    "iss" => app_description,
		    "iat" =>(int) date( 'mdHis' , strtotime('now')),
		    "nbf" => (int) date( 'mdHis' , strtotime('+1 day') ),
		    "usr" => [
		    "id"=> $user->id,
		    "rol" => $user->rol
			]
		); 

		if ( !is_null($data) ){
			$token['dat'] = $data ;
		}

		return $this->encode( $token ) ; 
	}




	/** 
	* Method check
	* @param string $token - Token Encoded
	* @return boolean  
	*/
	public function check(string $token){
			
		$token_decode = $this->decode($token);

		if ( $token_decode ) {
			return ( $token_decode->nbf > $token_decode->iat ) ? true : false;
		} 

		return false;

	}


	/** 
	* Method Renew Token
	* @param string $token - Token Encoded
	* @param string|array|null $data - data for encode
	* @return boolean  
	*/
	public function renew(string $token, $data=null){

		if ( $this->check($token) ) {
			
			$token_decode =  $this->decode( $token ) ;

			if ($token_decode) {

				$token_decode->iat = (int) date( 'mdHis' , strtotime('now'));
				$token_decode->nbf =  (int) date( 'mdHis' , strtotime("+1 day"));

				if ( !is_null($data) ){
					$token_decode->dat = $data;
				}

				return $this->encode((array) $token_decode) ;
			} 
			   

		}

		return false;
		
			

	}

	/** 
	* Method Encode Token
	*
	* @param array $token
	* @throws \Exception no is set $token or no is a array
	* return false 
	* @return string $token_encoded  
	*/
	public function encode(array $token){
		if ( isset( $token )  && is_array( $token ) ) {
			return JWT::encode( $token ,  $this->getKey() ); 
		}else{
			return false;
		}
	}

	/** 
	* Method Decode Token
	*
	* @param string $token  - Token Encoded
	* @throws \Exception no is set $token or no is a array
	* return false 
	* @return Object $token 
	* 
	*/
	public function decode(string $token){
		//var_dump($token, ( count( explode('.', $token) ) == 3  ) ); exit;
		if ( isset($token) &&  ( count( explode('.', $token) ) == 3  )  ) {
			return  JWT::decode($token, $this->getKey() , array('HS256') );
		}else{
			return false;
		}

	}


	/** 
	* Method generateKey
	*
	* @param 
	* @return String $randstring
	* 
	*/
	public function generateKey()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ&%$#@!*';
        $randstring = '';
        for ($i = 0; $i <= 100; $i++) {
            $randstring .= $characters[rand(0, 65)];
        }
        return $randstring;
	}


	/** 
	* Method setKey
	*
	* @param 
	* @return String $key
	* 
	*/
	public function setKey(){
		
		if( !file_exists( $this->app->path.'config/key.php')  ){
			$maker = new Maker($this->app);
			$maker->file('config:key', [ ['{your_key_here}'], [$this->generateKey()]] );
		}
		include  $this->app->path.'config/key.php'; //Load key
		return $key;
	}
	

	/** 
	* Method getKey
	*
	* @param 
	* @return String $key
	* 
	*/
	public function getKey(){
		
		if( file_exists( $this->app->path.'config/key.php')  ){
			include $this->app->path.'config/key.php'; //Load key
		}else{
			$key = $this->setKey()();
		}

		return $key;
	}



}