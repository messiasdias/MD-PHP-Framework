<?php
namespace App\Others;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;


class Log extends Logger
{
    protected $name, $path;

    public function __construct(string $logName = 'App', string $logPath = null){
        
        if(is_null($logPath)) $logPath = getcwd().'../log/'.strtolower($name).'.txt';
        
        $thi->path = $logPath;
        $thi->name = $logName;

        parent::__construct($logName);
    }

    public function emergency($message,array $context = array() ){
        $this->pushHandler(new StreamHandler($this->logPath, Logger::EMERGENCY));
        parent::emergency($message, $context);
    }



}