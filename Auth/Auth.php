<?php  
/**
 * Auth Class
 */
namespace App\Auth;
use App\App;
use App\Auth\Token;
use App\Models\User;


class Auth  {

	public $app, $token;

	public function __construct(App $app){
		$this->app = $app;
		$this->token = new Token($app);
	}


	public function login(array $data){

		if( isset($data['username']) && isset($data['pass'])  ) {
			
			$data['username'] = !App::validate($data['username'], 'startwith:@','App\Models\User' ) ? 
			'@'.$data['username'] : $data['username'];
			
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

									$_SESSION['token'] = $this->token->create($this->user);
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
		else{
			return (object) ['status' => false, 'msg' => 'Invalid values ​​for username and/or pass!', 'data' => $data ];
		}	

				
	}



	public function logout(){
		session_unset();
		return session_destroy() ? true : false;
	}



	public function user(string $token){
		if ($this->token->check( $token ) && $this->token->decode($token) ) {

			$user =  User::find('id', $this->token->decode($token)->usr->id );
			if ($user) {		
				return (object) [ 
					'id' => $user->id,
					'name', 'full_name' =>  $user->first_name.' '.$user->last_name,
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
