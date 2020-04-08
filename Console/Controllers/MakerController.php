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


    public static function PostCreateProject(Event $event){
        $console = new Console();
        $console->log('Project successfully created!', 1);
    }


    public static function maker(Event $event){
        $console = new Console();

        if( count( $event->getArguments() ) >= 1  ){
            $maker = new Maker($console);
            $command = false;

            foreach ($maker->commands() as $i => $com ) {
                if($com->name == $event->getArguments()[0] ){
                    $command = $event->getArguments()[0];
                }
            } 
            
            $subcommand = isset($event->getArguments()[1]) ? $event->getArguments()[1] : false;
            $subcommand .= isset($event->getArguments()[2]) ? $event->getArguments()[2] : '';
           
            if($command && $subcommand){
                $response = (object)  $maker->$command($subcommand);
                 $console->log($response->title);

                if( is_array($response->subtitle)){
                    foreach( $response->subtitle as $subtitle ){
                         $console->log($subtitle);
                    }
                 }else{
                     $console->log($response->$subtitle);  
                 }
                 $console->log("\n");
                return;
            }


        }
        
        self::maker_help();
    
    }



    public static function maker_help(){
        $console = new Console();
        $maker = new Maker($console);
        $console->log('Maker requires 2 arguments!', 2);
        $console->log('$ composer run maker {command} {subcommand}!', 5);
        $console->log("Maker | Help \n", 4 );

       foreach ( $maker->commands() as $i => $command ) {
            $console->log( $command->name.' : '.$command->options );
            $console->log( $command->description."\n" );
        }

    }


}