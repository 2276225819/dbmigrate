<?php 
include "DBMigrate.php";


$conn = 'mysql:charset=utf8'.';dbname=test'; 
$pdo  =  new \PDO($conn,'root','root',array( ));   


$obj = (new DBMigrate($pdo))->sync(function($DBMigrate){
     
    $DBMigrate
    ->table('user')
        ->increment('id')
        ->varchar('username',32)
        ->varchar('password',32)
            ->comment('md5')
    ->table('post')
        ->increment('id')
        ->varchar('title',32)
        ->text('comment')
        ->int('create_at')
        ->int('update_at')
    ; 
    $DBMigrate->table('log',"
        title varchar(32)  ,
        ip varchar(12)  ,  
    ")->int('num'); 
});


print_r($obj->log);
echo 'END';