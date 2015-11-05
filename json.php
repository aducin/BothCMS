<?php

header("Content-Type: application/json");
$Id = $_POST["jsonId"];
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
$sth=$product2->getEveryName();
if(!isset($_POST["jsonQuantity"]))
{
	$Name=$product1->getName($_POST["jsonId"]);
	$Quantity1=$product1->getQuantity($_POST["jsonId"]);
	$Quantity2=$product2->getQuantity($_POST["jsonId"]);
	$data='{ "nameLinux":"'.$Name.'", "quantityLinux":"'.$Quantity1.'", "quantityOgicom":"'.$Quantity2.'" }';
}
if(isset($_POST["jsonId"]) AND (isset($_POST["jsonQuantity"])))
{
	$Quantity = intval($_POST["jsonQuantity"]);
	$previousQuantity=$product1->getQuantity($_POST["jsonId"]);
	$product1->updateQuantity($_POST["jsonId"], $_POST["jsonQuantity"]);
	$product2->updateQuantity($_POST["jsonId"], $_POST["jsonQuantity"]);
	$newQuantity=$product1->getQuantity($_POST["jsonId"]);
	$data='{"obj1":{ "propertyA":"'.$Id.'", "propertyB":"'.$newQuantity.'", "propertyC":"'.$previousQuantity.'"} }';
}
if(isset($_GET['query'])){
	$names=$product2->getTypedName($_GET['query']);
	$total = $names->rowCount();
	if($total>0){
		$data= '<ul class="autoSuggest">';
		foreach ($names as $result){
			$results[]=array('name'=>$result['name'], 'id'=>$result['id_product']);
			$items.= '<li>'.$result['name'].' - ID: <b>'.$result['id_product'].'</b></li>';
		}
		$data.= '</ul>';
	}else{
		$data = 'Nie znaleziono żadnego produktu z nazwą: <b>'.$typed.'</b>!';
	}
}
echo $data;
?>