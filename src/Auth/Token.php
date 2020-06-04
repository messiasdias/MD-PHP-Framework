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
	public function __construct(){
		App::setEnv();
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
		    "iss" => App::getEnv()->app_description,
		    "iat" =>(int) date( 'mdHis' , strtotime('now')),
		    "nbf" => (int) date( 'mdHis' , strtotime('now +30 min') ),
		    "usr" => [
		    "id"=> $user->getId(),
		    "rol" => $user->getGuard()
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

				if(!is_null($data)) $token_decode->dat = $data;

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
			return JWT::encode( $token ,  App::getEnv()->app_key ); 
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

		if ( isset($token) &&  ( count( explode('.', $token) ) == 3  )  ) {
			return  JWT::decode($token,  App::getEnv()->app_key , array('HS256'));
		}else{
			return false;
		}

	}



}