<?php
namespace App\Models;
use App\Models\GaleryModel;
class Demo extends GaleryModel {
    public $table = 'demos';

    public function __construct(array $data=[]){
		  parent::__construct($data);
    }
    
}