<?php

$DBHandler=parse_ini_file($root_dir.'/config/database.ini',true);
$firstHost=$DBHandler["firstDB"]["host"];
$firstLogin=$DBHandler["firstDB"]["login"];
$firstPassword=$DBHandler["firstDB"]["password"];
$secondHost=$DBHandler["secondDB"]["host"];
$secondLogin=$DBHandler["secondDB"]["login"];
$secondPassword=$DBHandler["secondDB"]["password"];

require_once $root_dir.'/config/bootstrap.php';

$dbHandlerOgicom= new DBHandler($secondHost, $secondLogin, $secondPassword);
$ogicomHandler = $dbHandlerOgicom->getDb();
$dbHandlerLinuxPl= new DBHandler($firstHost, $firstLogin, $firstPassword);
$linuxPlHandler = $dbHandlerLinuxPl->getDb();

/* using Singleton
$linuxPlDbHandler=bothDbHandler::getInstance('linuxPl', $firstHost, $firstLogin, $firstPassword);
$ogicomDbHandler=bothDbHandler::getInstance('ogicom', $secondHost, $secondLogin, $secondPassword);
*/