<?php 
include "DBMigrate.php";
include "src/functions.php";
include "src/TableBlock.php";
 
$d = new DBMigrate(new PDO('mysql:host=localhost;dbname=test','root','root'));
$d ->bulidBlurPoint("a.php"); 