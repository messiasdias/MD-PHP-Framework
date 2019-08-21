<?php
namespace App\Database\Seeds;
use App\Database\Seeds\Seeder;
use App\Models\User;
/**
 * UsersSeeder Class
 */
class UsersSeeder extends Seeder
{	
	
	public function __construct(array $users){
		
		
		foreach ($users as $value ) {
				$user = new User($value);
				$user->confirm_pass = $user->pass;
				$this->set_response( $user->create() , 'User '.$user->first_name);
		}

		
	}

	



}


