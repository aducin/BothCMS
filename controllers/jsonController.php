<?php

$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
require_once $root_dir.'/config/dbHandlers.php';
$linux=new LinuxPlProduct($linuxPlHandler);
$ogicom=new OgicomProduct($ogicomHandler); 

if(isset($_POST["jsonId"])){
	if(!isset($_POST["jsonQuantity"]))
	{
		$linuxName=$linux->getName($_POST["jsonId"]);
		$Quantity1=$linux->getQuantity($_POST["jsonId"]);
		$Quantity2=$ogicom->getQuantity($_POST["jsonId"]);
		$data='{ "nameLinux":"'.$linuxName.'", "quantityLinux":"'.$Quantity1.'", "quantityOgicom":"'.$Quantity2.'" }';
	}
	if(isset($_POST["jsonQuantity"]))
	{
		$intQuantity = intval($_POST["jsonQuantity"]);
		$previousQuantity=$linux->getQuantity($_POST["jsonId"]);
		$linux->updateQuantity($_POST["jsonId"], $_POST["jsonQuantity"]);
		$ogicom->updateQuantity($_POST["jsonId"], $_POST["jsonQuantity"]);
		$newQuantity=$linux->getQuantity($_POST["jsonId"]);
		$data='{"obj1":{ "propertyA":"'.$_POST["jsonId"].'", "propertyB":"'.$newQuantity.'", "propertyC":"'.$previousQuantity.'"} }';
	}
	echo $data;
}

if(isset($_GET['productQuery'])){
	if(preg_match('/^[a-zA-z0-9]$/D',$_GET['productQuery'])){
		$data='tooShort';
	}else{
		$params[]=array('text'=>$_GET['productQuery'],'category'=>$_GET['category'],'author'=>$_GET['manufacturer']);
		foreach ($params as $result){
			$prequery[]=" AND name LIKE '%".$_GET['productQuery']."%'";
			if ($result['category'] == "notSelected"){ 
				unset($result['category']);
			}else{
				$prequery[]= " id_category =".$_GET['category'];
			}
			if ($result['author'] =="notSelected") {
				unset($result['author']);
			}else{
				$prequery[]= " id_manufacturer =".$_GET['manufacturer'];
			}
		}
		$implodeSelect=" WHERE id_lang=3".implode(" AND",$prequery)." GROUP BY ps_product_lang.id_product ORDER BY ps_product_lang.id_product";
		$names= $ogicom->getTypedProductsQuery($implodeSelect);
		$total = $names->rowCount();
		if($total>0){
			foreach ($names as $result){
				$quantity=$ogicom->getQuantity($result['id_product']);
				$data[]=array('id'=>$result['id_product'], 'name'=>$result['name'], 'quantity'=>$quantity);
			}
		}else{
			$data = 'null';
		}
	}
	echo json_encode($data);
}
	//$autoComplete=$json->autoComplete($jsonArray);
?>