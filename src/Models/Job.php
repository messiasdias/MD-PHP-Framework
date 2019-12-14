<?php
namespace App\Models;
use App\Models\GaleryModel;
class Job extends GaleryModel {

	public $table = 'jobs';

    public function __construct(array $data=[]){
		parent::__construct($data);
	}


}