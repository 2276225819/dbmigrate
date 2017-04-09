<?php 
include "DBMigrate.php";
 
$conn = 'mysql:charset=utf8'.';dbname=test'; 
$pdo  =  new \PDO($conn,'root','root',array( ));   
$obj = (new DBMigrate($pdo,['engine'=>'innodb']))->sync(function($DBMigrate){
     

    $DBMigrate//->check()
    ->table('user') ->comment('用户表') 
        ->increment('id')
        ->varchar('username',32)
            ->comment('md5')
        ->varchar('password',32)
    ->table('post') ->engine('myisam')
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
print_r($obj->tables);
echo 'END';