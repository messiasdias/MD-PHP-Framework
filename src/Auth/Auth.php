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

	public function __construct(App &$app){
		$this->app = $app;
		$this->token = new Token($app);
	}


	public function login(array $data){

		if( isset($data['username']) && isset($data['pass'])  ) {
			
			/*$data['username'] = !App::validate($data['username'], 'startwith:@','App\Models\User' ) ? 
			'@'.$data['username'] : $data['username']; */
			
			$validations = [
				'username' => 'username|minlen:4|exists:user',
				'pass' => 'string|minlen:8'
			];

			$result = App::validate($data, $validations,'App\Models\User' );

			if ( !$result->errors ){
				$data = (object) $result->data;
				$this->user = User::findOneBy(['username' => $data->username] );
				var_dump( $this->user ); 

				if( $this->user ){

					if ( password_verify( $data->pass , $this->user->getPass() )  ) {
							
							if( $this->user->getStatus() == 1 ) {

									$_SESSION['token'] = $this->token->create($this->user);
									return (object) [
										'status' => true, 
										'msg' => 'Login Successfully!', 
										'token' => $_SESSION['token'], 
										'user' => $this->user($_SESSION['token'])
									 ] ;

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

				return (object) ['status' => false, 'msg' => 'Login error!' , 'errors' => (array) $result->errors, 'data' => NULL];
			}

		}
		else{
			return (object) ['status' => false, 'msg' => 'Invalid values ​​for username and/or pass!', 'data' => $data ];
		}		
				
	}



	public function logout(){
		session_unset();
		return session_destroy();
	}



	public function user(string $token){
		if ($this->token->check( $token ) && $this->token->decode($token) ) {

			$user =  User::find( (int) $this->token->decode($token)->usr->id );
			if ($user) {		
				return (object) [ 
					'id' => $user->getId(),
					'name', 'full_name' =>  $user->getFirstName().' '.$user->getLastName(),
					'first_name' => $user->getFirstName(), 
					'last_name' => $user->getLastName(),
					'username' => $user->getUsername(),
					'email' => $user->getEmail(),
					'img' => ($user->getImg()) ? $user->getImg() : '/img/default/avatar.png',
					'rol' => $user->getRol(),
				];
			}		 

		}

		return false;

	}

}
