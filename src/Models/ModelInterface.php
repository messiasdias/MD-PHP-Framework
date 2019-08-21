<?php
namespace App\Models;

interface ModelInterface {

	public function create (array $data=null);

	public function update (array $data=null);

	public function delete(array $data=null);

	public function save(array $data,array $validations);

	public static function find($id, $value);

	public static function all(array $paginate=null);

}