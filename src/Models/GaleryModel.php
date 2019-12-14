<?php
namespace App\Models;
use App\Model\Model;
use App\Database\DB;
use App\Models\User;


class GaleryModel extends Model {
	
	public $author_id, $author_name, $title, $link, $img, $description, $publish, $git;
	
	public function __construct(array $data=[]){
		parent::__construct($data);
		$this->author();
	}

    public function create(){

		if(is_null($this->img)){
			$this->img = 'img/default/galery_avatar.png';
		}
		
		$this->title = ucwords($this->title);
		$this->author_id = isset($this->author_id) ? $this->author_id : 1;
		$this->link = trim($this->link);
		$this->git = trim($this->git);

		$validations = [
			'title' => 'string|minlen:5|noexists:'.explode("\\", get_called_class() )[ count( explode("\\", get_called_class() ) ) -1],
			'link' => 'string|minlen:0',
			'git' => 'string|minlen:0',
			'img' => 'string',
			'description' => 'string',
			'author_id' => 'int',
			'publish' => 'mincount:1|maxlen:2',
		];

		return self::save( (array) $this, $validations) ;

	}


	public function update (){

		$this->title =  ucwords($this->title);	
		$this->description = ucwords($this->description);
		$this->link = trim($this->link);
		$this->git = trim($this->git);

		$validations = [
			'id' => 'int|mincount:1|exists:'.explode("\\", get_called_class() )[ count( explode("\\", get_called_class() ) ) -1],
			'title' => 'string|minlen:5',
			'link' => 'string|minlen:0',
			'git' => 'string|minlen:0',
			'img' => 'string',
			'description' => 'string',
			'publish' => 'mincount:-1|maxcount:1|maxlen:2',
		];


		return self::save( (array) $this, $validations);

	}



	public function update_img(){

		$validations = [
			'img' => 'string',
		];

		return self::save( (array) $this, $validations);
	}


	public function publish (){
	
		$validations = [
			'id' => 'int|mincount:1|exists:'.explode("\\", get_called_class() )[ count( explode("\\", get_called_class() ) ) -1],
			'publish' => 'maxlen:2|minlen:1',
		];
	
		return self::save( (array) $this, $validations);

	}





	public function author(){
		if(isset( $this->author_id ) ){
			$author = User::find('id', $this->author_id);
			if($author) {
				$this->author_name = $author->first_name." ".$author->last_name;
				return $this->author_name;
			}
		}
		return false; 
	}


	public function is_publish(){
		if( $this->publish == '1' ){
			return true;
		}
		return false;
	}



}