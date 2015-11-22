<?php

header('Content-Type: text/xml');
echo'<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
echo'<response>';

$userLogin=$_GET['userInput'];
$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
$DBHandler=parse_ini_file($root_dir.'/config/database.ini',true);
$firstHost=$DBHandler["firstDB"]["host"];
$firstLogin=$DBHandler["firstDB"]["login"];
$firstPassword=$DBHandler["firstDB"]["password"];
require_once $root_dir.'/config/bootstrap.php';
$dbHandlerLinuxPl= new DBHandler($firstHost, $firstLogin, $firstPassword);
$dbResult= $dbHandlerLinuxPl->getUserLogin(trim($_GET['userInput']));
$resNumb=$dbResult->rowCount();

if($_GET['userInput']==''){
	echo'Podaj swój login!';
}else{
	if($resNumb>0){
		echo'Login "'.$_GET['userInput'].'" znaleziony w bazie!';
	}else{
		echo'W bazie danych nie występuje użytkownik: "'.$_GET['userInput'].'"!';
	}
}

echo'</response>';
?>