<?php
namespace App\Console\Controllers;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use App\Console\Console;
use App\Maker\Maker ;

/**
 * Installer Class
 */

/*
    Composer Event Functions
    ./composer_commands.md
*/

class MakerController  {

    public static $console, $maker;

    private static function setConsole(){
        self::$console = new Console();
        self::$maker = new Maker(self::$console);
    }


    public static function maker(Event $event){
        self::setConsole();

        if( count( $event->getArguments() ) >= 1  ){
            $command = false;

            foreach (self::$maker->commands() as $i => $com ) {
                if($com->name == $event->getArguments()[0] ){
                    $command = $event->getArguments()[0];
                }
            } 
            
            $subcommand = $event->getArguments()[1] ?? false;
            $subcommand .= $event->getArguments()[2] ?? '';
           
            if($command && $subcommand){
                $response = (object) self::$maker->$command($subcommand);
                 self::$console->log($response->title);

                if( is_array($response->subtitle)){
                    foreach( $response->subtitle as $subtitle ){
                        if( is_array($subtitle)){
                            self::$console->log($subtitle[0], $subtitle[1]);
                        }else{
                            self::$console->log($subtitle);
                        }
                    }
                 }else{
                     self::$console->log($response->$subtitle);  
                 }
                 self::$console->log("\n");
                return;
            }


        }
        
        self::maker_help();
    
    }



    public static function maker_help(){
        self::setConsole();

        self::$console->log('Maker requires 2 arguments!', 2);
        self::$console->log('$ composer run maker {command} {subcommand}!', 5);
        self::$console->log("Maker | Help \n", 4 );

       foreach ( self::$maker->commands() as $i => $command ) {
            self::$console->log($command->name.' : '.$command->options);
            self::$console->log($command->description);
        }

    }

    

}

