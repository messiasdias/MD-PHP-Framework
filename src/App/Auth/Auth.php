<?php  
/**
 * Auth Class
 */
namespace App\Auth;
use App\App;
use App\Models\User;
use App\Database\DB;
use App\Auth\Roles;
use App\Auth\Token;


class Auth  {

	public $app, $user;


	public function login(array $data){

		$validations = [
			'username' => 'username|minlen:4|exists:user',
			'pass' => 'string|minlen:8'
			];
	
		$result = App::validate($data, $validations,'App\Models\User' );

		if ( !$result->errors ){
			$data = (object) $result->data;
			$this->user = User::find('username', $data->username );

				if( $this->user ){

					if ( password_verify( $data->pass , $this->user->pass )  ) {
						

						if( $this->user->status == 1 ) {

							$_SESSION['token'] = Token::create($this->user);

							 return (object) [ 'status' =>  ['msg' => 'Login Successfully!', 
							 	'code' => 202 ] , 'token' => $_SESSION['token'], 
							 	'user' => $this->user($_SESSION['token']) ] ;



					  	}else{

							return (object) ['status' => false, 'msg' => 'Inactive User, contact system support.', 'data' => NULL];
				
					  	}

					}else{

						return (object) ['status' => false, 'msg' => 'Username or Password is Incorrect!', 
							 'data' => NULL  ];
				
					}

				}else {
					
					return (object) ['status' => false, 'msg' => 'The User '. $data->username.' does not exist in the Database!', 'data' => NULL];
					
				 }
			
			} else{

				return (object) ['status' => false, 'msg' => implode(',', (array) $result->errors),'data' => NULL];
			}

				
	}



	public function social_login(array $data){

		//"https://graph.facebook.com/v5.0/object-id?access_token=your-access-token"
		$api_url=false; $api_user=false;
		switch( strtolower( $data['social_network'] ) ){
			case "facebook" :
				$api_url = "https://graph.facebook.com/v5.0/".$data['username']."/?fields=email,id,last_name,first_name,last_name?access_token=".$data['access_token'];
			break;

			default :
				//$api_url = 'https://graph.facebook.com/me?fields=email,id,last_name,first_name?access_token='.$data["access_token"];
			break;

		}

		$validations = [
			'username' => 'username|exists:user',
			'email' => 'email|exists:user'
			];
	

 
		$result = App::validate( $data,$validations ,'App\Models\User' );
		$user = User::find('email', $data['email'] );
		

		if( !$result->errors && ( $user != false) ){
			$data = (object) $result->data;
			$this->user = User::find('username', $data->username );
			$token = Token::create($this->user);
			return (object) [ 'status' => false,  'token' => $token, 'user' =>  $this->user($token)  ];
		} 
		
		$this->logout();
		return (object) [ 'status' => false, 'token' => false ] ;
	
	}


	public function logout(){
		session_unset();
		session_destroy();
		return true;
	}



	public function user(string $token){

		if (Token::check( $token ) && Token::decode($token) ) {

			$user =  User::find('id', Token::decode($token)->usr->id );
			if ($user) {		
				return (object) [ 
					'id' => $user->id,
					'first_name' => $user->first_name, 
					'last_name' => $user->last_name,
					'username' => $user->username,
					'email' => $user->email,
					'img' => ($user->img) ? $user->img : '/img/default/avatar.png',
					'rol' => $user->rol,
				];
			}		 

		}

		return false;

	}


	

}
