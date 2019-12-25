<?php
namespace App\Auth;
use Firebase\JWT\JWT;
use App\Models\User;
/**
 * Token Class
 */
class Token
{

	/**
	* Method create
	*
	* @param App\Models\User $user   A Object of User Class
	* @param array|string|null $data - A Object of User Class
	* @return string token_encoded 
	*/

	public static function create(User $user, $data=null){

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

		return self::encode( $token ) ; 
	}




	/** 
	* Method check
	* @param string $token - Token Encoded
	* @return boolean  
	*/
	public static function check(string $token){
			
		$token_decode = self::decode( $token );

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
	public static function renew(string $token, $data=null){

		if ( self::check($token) ) {
			
			$token_decode =  self::decode( $token ) ;

			if ($token_decode) {

				$token_decode->iat = (int) date( 'mdHis' , strtotime('now'));
				$token_decode->nbf =  (int) date( 'mdHis' , strtotime("+1 day"));

				if ( !is_null($data) ){
					$token_decode->dat = $data;
				}

				return self::encode((array) $token_decode) ;
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
	public static function encode(array $token){
		
		if( file_exists( __DIR__.'/../../../../config/key.php')  ){
			include  __DIR__.'/../../../../config/key.php'; //Load key
		}else{
			echo 'File config/key.php not Found! <br>';
			return false;
		}

		if ( isset( $token )  && is_array( $token ) ) {
			return JWT::encode( $token , $key ); 
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
	* @return object $token 
	* 
	*/

	public static function decode(string $token){
		
		if( file_exists( __DIR__.'/../../../../config/key.php')  ){
			include  __DIR__.'/../../../../config/key.php'; //Load key
		}else{
			echo 'File config/key.php not Found! <br>';
			return false;
		}

		if ( isset($token) &&  ( count( explode('.', $token) ) == 3  )  ) {
			return  JWT::decode($token, $key , array('HS256') );
		}else{
			return false;
		}

	}



}