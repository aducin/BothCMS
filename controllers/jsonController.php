<?php

$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
require_once $root_dir.'/config/dbHandlers.php';
$json=new jsonController($linuxPlHandler, $ogicomHandler);

if(isset($_POST["jsonId"])){
	if(!isset($_POST["jsonQuantity"]))
	{
		$data=$json->seachNames($_POST["jsonId"]);
	}
	if(isset($_POST["jsonQuantity"]))
	{
		$data=$json->updateJsonQuantities($_POST["jsonId"], $_POST["jsonQuantity"]);
	}
	echo $data;
}

if(isset($_GET['productQuery'])){
	$jsonArray=array($_GET['productQuery'], $_GET['category'], $_GET['manufacturer']);
	$autoComplete=$json->autoComplete($jsonArray);
	echo json_encode($autoComplete);
}
?>