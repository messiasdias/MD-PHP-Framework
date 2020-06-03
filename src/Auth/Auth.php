<?php  
/**
 * Auth Class
 */
namespace App\Auth;
use App\App;
use App\Auth\Token;
use App\Auth\Rules;
use App\Models\User;


class Auth  {

	public $app, $token;

	public function __construct(App &$app){
		$this->app = $app;
		$this->token = new Token($app);
	}


	public function login(array $data){

		$response = (object) [
			'status' => false, 
			'msg' => 'Login error!', 
		];

		$validations = [
			'username' => 'username|minlen:4|exists:user',
			'pass' => 'string|minlen:8'
		];

		$result = App::validate($data, $validations,'App\Models\User' );

		if ( !$result->errors ){
			$data = (object) $result->data;
			$user = User::findOneBy(['username' => $data->username] );

			if ( password_verify( $data->pass , $user->getPass() )  ) {
							
				if( $user->getStatus() == 1 ) {

					$_SESSION['access_token'] = $this->token->create($user);
					$response->status = true;
					$response->msg = 'Login Successfully!' ;
					$response->access_token = $_SESSION['access_token'];
					$response->user =  $this->user($response->access_token);

				}else{
					$response->errors = [ 'username' => ['Inactive User, contact system support.']]; 
				}

			}else{
				$response->errors = [ 'pass' => ['The password does not match!']];
			}

			
		} else{
			$response->errors = (array) $result->errors;
		}

		return $response;		
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
					'name' =>  $user->getName(),
					'first_name' => $user->getFirstName(), 
					'last_name' => $user->getLastName(),
					'username' => $user->getUsername(),
					'email' => $user->getEmail(),
					'img' => ($user->getImg()) ? $user->getImg() : '/img/default/avatar.png',
					'guard' => $user->getGuard(),
				];
			}		 

		}

		return false;

	}

}
