<?php

return [

    /*
    |----------------------------------------------------------------------
    | Auto backup mode
    |----------------------------------------------------------------------
    |
    | This value is used when you save your file content. If value is true,
    | the original file will be backed up before save.
    */

    'autoBackup' => env('DOTENV_AUTO_BACKUP', true),

    /*
    |----------------------------------------------------------------------
    | Backup location
    |----------------------------------------------------------------------
    |
    | This value is used when you backup your file. This value is the sub
    | path from root folder of project application.
    */

    'backupPath' => env('DOTENV_BACKUP_PATH', storage_path('dotenv-editor/backups/')),

    'classes' => [
        /*
        |----------------------------------------------------------------------
        | Formatter Class
        |----------------------------------------------------------------------
        |
        | The class that handles formatting environment keys and values.
        | It must implement the contract class
        | \GrantHolle\DotenvEditor\Contracts\DotenvFormatter
        */

        'formatter' => \GrantHolle\DotenvEditor\DotenvFormatter::class,

        /*
        |----------------------------------------------------------------------
        | Writer Class
        |----------------------------------------------------------------------
        |
        | The class that handles writing files.
        | It must implement the contract class
        | \GrantHolle\DotenvEditor\Contracts\DotenvWriter
        */

        'writer' => \GrantHolle\DotenvEditor\DotenvWriter::class,

        /*
        |----------------------------------------------------------------------
        | Reader Class
        |----------------------------------------------------------------------
        |
        | The class that handles reading environment files
        | It must implement the contract class
        | \GrantHolle\DotenvEditor\Contracts\DotenvReader
        */

        'reader' => \GrantHolle\DotenvEditor\DotenvReader::class,

    ],

];
