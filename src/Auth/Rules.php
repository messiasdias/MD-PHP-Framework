<?php  
namespace App\Auth;
use App\App;
use App\Auth\Token;
use App\Models\User;
/**
 * Rules Class
 **/
class Rules
{
    public $guest = 0;
    public $admin = 1;
    public $manager = 2;
    public $user = 3;

    public static function getById(int $id){
        $rules = new Rules();
        foreach( get_object_vars ($rules ) as $key =>  $value ){
            if( $value == $id ){
                return $key;
            }
        }
    }

    public static function getByName(string $name){
        $rules = new Rules();
        $name = strtolower($name);
        if( property_exists( $rules , $name)){
            return $rules->$name;
        }
    }

}