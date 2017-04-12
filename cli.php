<?php //example
include __DIR__."/vendor/autoload.php";
$sync = new \DBMigrate\DBSync(); 
$sync->setPDO(new PDO('mysql:dbname=test','root','root'));
$sync->command($argv);

