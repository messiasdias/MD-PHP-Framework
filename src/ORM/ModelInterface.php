<?php

namespace App\ORM;

interface ModelInterface{
    
    public function save();
    public function delete();
    public function extract();

}