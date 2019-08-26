<?php

/**
 *  User Class
 */

namespace App\Models;
use App\App;
use App\Models\Model;
use App\Database\DB;



class User extends Model
{	
	public $first_name, $last_name, $username, $pass, $confirm_pass, $email, $confirm_email,  $remember_token, $rol, $status, $img;	


	public function create (array $data=null){	


		if( !is_null($data) && is_null($data['img']) ){
			$data['img'] = 'img/default/avatar.png';
		}
		elseif(is_null($data) && is_null($this->img)){
			$this->img = 'img/default/avatar.png';
		}


		$validations = [
			'username' => 'username|minlen:5|noexists:users',
			'pass' => 'string|minlen:6',
			'confirm_pass' => 'compare_hash:pass',
			'first_name' => 'string|minlen:4',
			'last_name' => 'string|minlen:4',
			'email' => 'email|noexists:users',
			'status' => 'int|minlen:1|maxlen:1',
			'img' => 'minlen:0',
			'rol' => 'int|minlen:1|maxlen:1'
		];

		return self::save( is_null($data) ? (array) $this: $data , (array) $validations);
	

	}




	public function update (array $data=null){

		$validations = [
			'id' => 'int|mincount:1|exists:users',
			'first_name' => 'string|minlen:4',
			'last_name' => 'string|minlen:4',
			'username' => 'username|minlen:5|exists:users',
			'email' => 'email',
			'confirm_email' => 'confirm:email',
			'status' => 'int|mincount:-1|maxlen:2',
			'rol' => 'int|mincount:1|maxlen:1',
			'pass' => 'string|minlen:8',
			'confirm_pass' => 'confirm:pass',
			'img' => 'string|minlen:0',
		];

		return self::save( is_null($data) ? (array) $this: $data , $validations);

	}



	


}



?>