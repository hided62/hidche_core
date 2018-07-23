<?php
namespace sammo;
//SQLite + catfan\Medoo

use \Medoo\Medoo;

class FileDB{
    public static function db(string $path, ?string $schemaPath = null) : Medoo{
        //Note: reference count 적용. MySQL의 것과 다르게 동작함.
        $db = new Medoo([
            'database_type' => 'sqlite',
	        'database_file' => $path
        ]);
        if($schemaPath){
            $db->query(\file_get_contents($schemaPath));
        }

        return $db;
    }
}