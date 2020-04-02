<?php
namespace App\Bin;

/**
 * Installer Class
 */

/*
    Composer Event Functions
    ./composer_commands.md
*/

class Installer {
    public static function PostCreateProject(){
        echo '\n\n#### Projeto criado com Sucesso!! ####';
    }
}