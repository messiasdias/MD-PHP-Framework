<?php

/* Config Constants values

 Defining: 
 define(name, value);

 Using:
 $value = constant("name"); 

 */





//* App description  *//
define('app_description', 'Messias Dias -  Portfolio');


//*  Debug  *//
//Debug_msg
define("debug_msg", false);


//* Views Filesystem  *//
//Define views root dir on filesytem of app 
// default: '../assets/views/'.$this->theme
define('views_dir', '../assets/views/'.$this->theme);


//* Maker Commands  *//
define("maker_commands",[

	/* [ 'name' => 'maker',
	  'options' => 'always use',
	  'description' => 'Help - Index page command List.' ], */

	[ 'name' => 'migrate',
	  'options' => 'create|drop|reset|seed|spoon:[table_name|all]', 
	  'description' => 'Execute migrations Tables: create and drop. In single or multimode.' ],

	 [ 'name' => 'file',
	   'options' => 'controller|model|migration:[class_name]', 
	   'description' => 'Create file script models.' ],

]);


//* Default users  *//
define ("default_users", array (

	// --> Default Admin User
	"admin"=> array ( 
			
				"first_name" => "Admin",
				"last_name" => "{Teste}",
				"email" => "admin@teste.ex",
				"username" => "@admin",
				"pass" => "12345678",
				"img"=> "/img/default/avatar.png",
				"rol"=> 1,
				"status"=> 1
			),
	// --> Default Manager User
	"manager"=> array ( 
			
				"first_name" => "Manager",
				"last_name" => "{Teste}",
				"email" => "manager@teste.ex",
				"username" => "@manager",
				"pass" => "12345678",
				"img"=> "/img/default/avatar.png" ,
				"rol"=> 2,
				"status"=> 1 
			),

	// --> Messias
	"messias" => array ( 
				"first_name" => "Messias",
				"last_name" => "Dias",
				"email" => "messiasdias.ti@gmail.com",
				"username" => "@messiasdias",
				"pass" => "P@55w0rd123",
				"img"=> '/img/default/eu.jpg',
				"rol"=> 1,
				"status"=> 1
			)
)) ;


//* Maker Arguments  *//
define("maker_args",  array (

	'jobs' => 100, //count
	'demos' => 100,//count
	'users' => default_users

 ) );










?>