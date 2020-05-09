<?php
namespace App\Console\Controllers;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use App\Console\Console;

/**
 * Installer Class
 */

/*
    Composer Event Functions
    ./composer_commands.md
*/
//vendor/bin/doctrine orm:generate-entities -- ./src/Models
class DoctrineController  {

    public static $console;

    private static function setConsole(){
        self::$console = new Console();
    }


    public static function generate(Event $event){
        self::setConsole();
        self::$console->log('Test generate! ');
    }



}