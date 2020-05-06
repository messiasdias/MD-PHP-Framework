<?php
namespace App\Model;

interface ModelInterface {

	public function create();

	public function update();

	public function delete();

	public function save(array $data,array $validations=null);

	public function remove(array $validations=null);

	public static function find($id, $value);

	public static function all(array $paginate=null);

}