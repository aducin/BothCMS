<?php

header( 'Content-Type: text/html; charset=utf-8' );
$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
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
$product1= new LinuxPlProduct($linuxPlHandler);
$product2= new OgicomProduct($ogicomHandler);

if(isset($_POST["jsonId"])){
	if(!isset($_POST["jsonQuantity"]))
	{
		$Name=$product1->getName($_POST["jsonId"]);
		$Quantity1=$product1->getQuantity($_POST["jsonId"]);
		$Quantity2=$product2->getQuantity($_POST["jsonId"]);
		$data='{ "nameLinux":"'.$Name.'", "quantityLinux":"'.$Quantity1.'", "quantityOgicom":"'.$Quantity2.'" }';
	}
	if(isset($_POST["jsonQuantity"]))
	{
		$Quantity = intval($_POST["jsonQuantity"]);
		$previousQuantity=$product1->getQuantity($_POST["jsonId"]);
		$product1->updateQuantity($_POST["jsonId"], $_POST["jsonQuantity"]);
		$product2->updateQuantity($_POST["jsonId"], $_POST["jsonQuantity"]);
		$newQuantity=$product1->getQuantity($_POST["jsonId"]);
		$data='{"obj1":{ "propertyA":"'.$_POST["jsonId"].'", "propertyB":"'.$newQuantity.'", "propertyC":"'.$previousQuantity.'"} }';
	}
	echo $data;
}

if(isset($_GET['productQuery'])){
	if(preg_match('/^[a-zA-z0-9]$/D',$_GET['productQuery'])){
		$data='tooShort';
	}else{
		$names=$product2->getTypedName($_GET['productQuery']);
		$total = $names->rowCount();
		if($total>0){
			foreach ($names as $result){
				$data[]=array('id'=>$result['id_product'], 'name'=>$result['name']);
				$jsonData[]=json_encode(array('namenew'=>$result['name'],'idnew'=>$result['id_product']));
			}
		}else{
			$data = 'null';
		}
	}
	echo json_encode($data);
}
?>