<?php 
include __DIR__."/vendor/autoload.php";
$sync = new \xlx\DBSync(); 
$sync->setPDO(new PDO('mysql:dbname=test','root','root'));
$sync->pull();
//example
if (PHP_SAPI === 'cli'){ 
	include __DIR__.'/assets/cli.php';
}else{
	include __DIR__.'/assets/web.php';
}

 