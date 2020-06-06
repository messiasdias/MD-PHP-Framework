<?php
namespace App\Others;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;


class Log extends Logger
{
    private $log;

    public function __construct(string $log_name = 'App'){
        parent::__construct($name);
    }



}