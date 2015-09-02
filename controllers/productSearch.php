<?php

try{

	$helper= new OgicomHelper($ogicomHandler);
	$result= $helper->selectWholeManufacturer();
	foreach ($result as $row){
		$authors[]= array('id'=> $row['id_manufacturer'], 'name'=> $row['name']);
	}
} catch (PDOException $e){
	$error='Pobieranie listy producentów nie powiodło się: ' . $e->getMessage();
}
try{
	$result= $helper->getCategoryData();
	foreach ($result as $row){
		$categories[]= array('id'=>$row['id_category'], 'name'=>$row['meta_title']);
	}
} catch (PDOException $e){
	$error='Pobieranie listy kategorii nie powiodło się: ' . $e->getMessage();
}
$result= $helper->getModyfiedData();
$product= new OgicomProduct($ogicomHandler);
foreach ($result as $mod){
	$productReduction=$product->getReductionData($mod['id_number']);
	$mods[]= array('id'=>$mod['id_number'], 'nazwa'=>$mod['name'], 'data'=>$mod['date'], 'cena'=>number_format($mod['price'], 2,'.','').'zł', 'reduction'=>$productReduction);
}
unset($helper);
unset($product);

$controller = new ProductController($linuxPlHandler, $ogicomHandler);
if(isset($_GET['deleterow'])){
	$helper=$controller->productSearchRowDeletion($_GET['idMod']);
}elseif(isset($_GET['editformBoth'])){
	if ($_POST['text']==''){
		$error='Brak aktualnego wpisu: nazwa produktu!';
	}elseif ($_POST['quantity']==''){
		$error='Brak aktualnego wpisu: nowa liczba produktu!';
	}else try{
		$outputOrderOrProduct1=$controller->productDoubleUpdate($_POST['id'], $_POST['nominalPriceNew'], $_POST['text'], $_POST['quantity']);
	}catch (PDOExceptioon $e){
		$error='Aktualizacja danych nie powiodła się: ' . $e->getMessage();
	}
}elseif(isset($_GET['editcompleteformnew'])OR(isset($_GET['editcompleteformold']))){
	if(isset($_GET['editcompleteformnew'])){
		$completeUpdate="LinuxPl";
	}elseif(isset($_GET['editcompleteformold'])){
		$completeUpdate="Ogicom";
	}
	if ($_POST['text']==''){
		$error='Musisz podać nazwę produktu!';
	}elseif ($_POST['quantity']==''){
		$error='Musisz podać nową ilość produktu!';
	}elseif ($_POST['categories']==''){
		$error='Nie znaleziono kategorii do zapisania!';
	}else try{
		if(!isset($_POST['delete'])){
			$_POST['delete']='';
		if(!isset($_POST['change']))
			$_POST['change']='';
		}
		$updateResult['first']=$controller->productCompleteUpdate($completeUpdate, $_POST['id'], $_POST['nominalPriceNew'],  $_POST['nominalPriceOld'], $_POST['delete'], $_POST['change'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], 
		$_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active'], $_POST['author'], $_POST['categories'], $_POST['tagsText']);
	}catch (PDOExceptioon $e){
		$error='Aktualizacja produktu w edytowanej bazie nie powiodła się: ' . $e->getMessage();
	}
	if(!isset($error)){
		if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'both'){
			try{
				$updateResult['second']=$controller->productCompleteSecondUpdate($completeUpdate, $_POST['id'], $_POST['nominalPriceNew'],  $_POST['nominalPriceOld'], $_POST['delete'], $_POST['change'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], 
				$_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active'], $_POST['author'], $_POST['categories'], $_POST['tagsText']);
				$outputOrderOrProduct1=$updateResult;
			}catch (PDOExceptioon $e){
				$error='Aktualizacja produktu w drugiej bazie nie powiodła się: ' . $e->getMessage();
			}
		}
	}
}elseif(isset($_GET['shortEdition'])){
	try{
		$productShortEdition=$controller->productShortEdition($_GET['id']);
	}catch (PODException $e){
		$error='Błąd przy pobieraniu informacji o produkcie: ' . $e->getMessage();
	}
}elseif(isset($_GET['fullEditionN'])OR(isset($_GET['fullEditionO']))){
	if (isset($_GET['fullEditionN'])){
		$fullEdition='new';
		$editForm='?editcompleteformnew';
	}elseif(isset($_GET['fullEditionO'])){
		$fullEdition='old';
		$editForm='?editcompleteformold';
	}
	$completeQueryResult=$controller->productCompleteEdition($fullEdition, $_GET['id']);
	$selCategories=$controller->productSelectedCategories($fullEdition, $_GET['id']);
	$categoryList=$controller->productCategoryList($fullEdition, $_GET['id']);
	$completeTagNames=$controller->productTag($fullEdition, $_GET['id']);
}elseif(isset($_GET['action'])AND(isset($_GET['idnr']))){
	$productIdSearch=$controller->productIdSearchLinuxPl($_GET['idnr']);
	if($productIdSearch['name']==''){
		$error='Brak produktu o podanym ID.';
	}else{
		$oldQueryResult=$controller->productIdSearchOgicom($_GET['idnr']);
	}
	$imageNumber= $controller->getOgicomImage($_GET['idnr']);
}elseif(isset($_GET['action'])and $_GET['action']=='search'){
	if ($_GET['text'] =='' AND $_GET['category'] =='' AND $_GET['author'] ==''){
		$error='Nie chcesz chyba wypisywać wszystkich produktów z bazy...? Zaznacz chociaż z 1 kryterium wyszukiwania!';
	}else{
		$params[]=array('text'=>$_GET['text'],'category'=>$_GET['category'],'author'=>$_GET['author']);
		foreach ($params as $result)
			if ($result['text'] == "") {
				unset($result['text']);
			}else{
				$prequery[]= " name LIKE '"."%".$_GET['text']."%'";
			}
			if ($result['category'] == ""){ 
				unset($result['category']);
			}else{
				$prequery[]= " id_category =".$_GET['category'];
			}
			if ($result['author'] == "") {
				unset($result['author']);
			}else{
				$prequery[]= " id_manufacturer =".$_GET['author'];
			}
			$implodeSelect=' WHERE'.implode(" AND",$prequery).' GROUP BY id_product ORDER BY id_product';
			$phraseSearchResult=$controller->productPhraseSearch($implodeSelect);
			if(!isset($phraseSearchResult)){
				$error='W bazie nie znaleziono produktów spełniających podane kryteria!';
			}else{
				$productPhraseSearch=($_GET['text']);
			}	
		}
	}

	$finalOutput='product';
	require_once $root_dir.'/controllers/output.php'; 
