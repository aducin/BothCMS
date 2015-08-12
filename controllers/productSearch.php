<?php
ob_start();
require_once $rootDir.'/config/bootstrap.php';

$DBHandler=parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/config/database.ini',true);
$firstHost=$DBHandler["firstDB"]["host"];
$firstLogin=$DBHandler["firstDB"]["login"];
$firstPassword=$DBHandler["firstDB"]["password"];
$secondHost=$DBHandler["secondDB"]["host"];
$secondLogin=$DBHandler["secondDB"]["login"];
$secondPassword=$DBHandler["secondDB"]["password"];

session_start();
if(isset($_POST['logout'])){
	unset($_SESSION['log']);
	header('Location:templates/signIn.html');

}
if(!isset($_SESSION['log'])){
	$userLogin=$_POST['login'];
	$userPassword=$_POST['password'];
	$db=new db($firstHost, $firstLogin, $firstPassword);
	$dbResult= $db->getUserData($userLogin, $userPassword);
	$resNumb=$dbResult->rowCount();
	if($resNumb>0){
		$finalResult=$dbResult->fetch(PDO::FETCH_ASSOC);
		$_SESSION['log']=1;
		$dbResult->closeCursor();
	} 
	unset($db);
}
try{
	$helper= new OgicomHelper($secondHost, $secondLogin, $secondPassword);
	$result= $helper->selectWholeManufacturer();
}catch (PDOException $e){
	$error='Pobieranie listy producentów nie powiodło się: ' . $e->getMessage();
}
foreach ($result as $row){
	$authors[]= array('id'=> $row['id_manufacturer'], 'name'=> $row['name']);
}try{
	$result= $helper->getCategoryData();
}catch (PDOException $e){
	$error='Pobieranie listy kategorii nie powiodło się: ' . $e->getMessage();
}
foreach ($result as $row){
	$categories[]= array('id'=>$row['id_category'], 'name'=>$row['meta_title']);
}
$result= $helper->getModyfiedData();
foreach ($result as $mod){
	$mods[]= array('id'=>$mod['id_number'], 'nazwa'=>$mod['name'], 'data'=>$mod['date'], 'cena'=>$mod['price']);
}
unset($helper);
require $rootDir.'/templates/searchForm.html.php';

if(isset($_POST['orders'])){
	header('Location:controllers/orderSearch.php');
}elseif(isset($_GET['deleterow'])){
	$helper= new OgicomHelper($secondHost, $secondLogin, $secondPassword);
	$result= $helper->deleteModyfied($_GET['idMod']);
	header('Location:.');
	unset($helper);
}elseif(isset($_GET['editformBoth'])){
	if ($_POST['text']==''){
		$error='Brak aktualnego wpisu: nazwa produktu!';
	}elseif ($_POST['quantity']==''){
		$error='Brak aktualnego wpisu: nowa liczba produktu!';
	}else try{
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$newQuery = $product1->updateBoth($_POST['id'], $_POST['nominalPriceNew'], $_POST['text'], $_POST['quantity']);
		$newQuery2 = $product1->confirmation($_POST['id']);
		$quantityNew= $newQuery2["quantity"];
	}catch (PDOExceptioon $e){
		$error='Aktualizacja danych nie powiodła się: ' . $e->getMessage();
	}
	if(!isset($error)){
			$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
			$oldQuery = $product2->updateBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity']);
			$oldQuery2 = $product2->confirmation($_POST['id']);
			$idOld= $oldQuery2["id_product"];
			$quantityOld= $oldQuery2["quantity"];
		require $rootDir.'/templates/confirmation.html.php';
		exit();
	}
}elseif(isset($_GET['editcompleteformnew'])OR(isset($_GET['editcompleteformold']))){
	if(isset($_GET['editcompleteformnew'])){
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		if (isset($_POST['change'])and $_POST['change']== "nameChange"){
			$oldQuery = $product2->insertModyfy($_POST['id'], $_POST['text']);
		}
	}elseif(isset($_GET['editcompleteformold'])){
		$product2= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$product1= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		if (isset($_POST['change'])and $_POST['change']== "nameChange"){
			$oldQuery = $product1->insertModyfy($_POST['id'], $_POST['text']);
		}
	}
	if ($_POST['text']==''){
		$error='Musisz podać nazwę produktu!';
	}elseif ($_POST['quantity']==''){
		$error='Musisz podać nową ilość produktu!';
	}else try{
		if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
			$Query = $product1->deleteImage($_POST['id']);
		}
		$Query = $product1->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], str_replace(" ","-", $_POST['link']), $_POST['condition'], $_POST['active']);
		$sth=str_replace(" ", "-", $_POST['link']);
		var_dump($sth);
		$Query = $product1->updateManufacturer($_POST['author'], $_POST['id']);
		$Query = $product1->deleteCategory($_POST['id']);
		if(isset($_POST['categories'])){
			$Query = $product1->insertCategory($_POST['categories'], $_POST['id']);
		}else{
			$error1='Nie znaleziono kategorii do zapisania!';
		}
		$Query = $product1->deleteWholeTag($_POST['id']);
		foreach (explode(", ", $_POST['tagsText']) as $tagText){
			$Query = $product1->checkIfTag($tagText);
			$Query2 = $Query->fetch();
			$checkedTagId= $Query2[0];
			if($checkedTagId!=0){
				$Query = $product1->insertTag($checkedTagId, $_POST['id']);
			}else{
				$Query = $product1->createTag($checkedTagId, $tagText);
				$Query = $product1->checkIfTag($tagText);
				$Query2 = $Query->fetch();
				$checkedTagId= $Query2[0];
				$Query = $product1->insertTag($checkedTagId, $_POST['id']);
			}
		}
	}catch (PDOExceptioon $e){
		$error='Aktualizacja produktu w edytowanej bazie nie powiodła się: ' . $e->getMessage();
	}
	if(!isset($error)){
		try{
			$Query = $product1->confirmation($_POST['id']);
			$idOld= $Query["id_product"];
			$quantityOld= $Query["quantity"];
		}catch (PDOExceptioon $e){
			$error='Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
		}
	}
	if(!isset($error)){
		if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'both'){
			try{
				if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
					$Query = $product2->deleteImage($_POST['id']);
				}
				$Query = $product2->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], str_replace(" ","-", $_POST['link']), $_POST['condition'], $_POST['active']);
				$Query = $product2->updateManufacturer($_POST['author'], $_POST['id']);
				$Query = $product2->deleteCategory($_POST['id']);
				if(isset($_POST['categories'])){
					$Query = $product2->insertDifferentCategory($_POST['categories'], $_POST['id']);	
				}else{
					$error1='Nie znaleziono kategorii do zapisania!';
				}
				$Query = $product2->deleteWholeTag($_POST['id']);
				foreach (explode(", ", $_POST['tagsText']) as $tagText){
					$Query = $product2->checkIfTag($tagText);
					$Query2 = $Query->fetch();
					$checkedTagId= $Query2[0];
					if($checkedTagId!=0){
						$Query = $product2->insertTag($checkedTagId, $_POST['id']);
					}else{
						$Query = $product2->createTag($checkedTagId, $tagText);
						$Query = $product2->checkIfTag($tagText);
						$Query2 = $Query->fetch();
						$checkedTagId= $Query2[0];
						$Query = $product2->insertTag($checkedTagId, $_POST['id']);
					}
				}
			}catch (PDOExceptioon $e){
				$error='Aktualizacja produktu w drugiej bazie nie powiodła się: ' . $e->getMessage();
			}
		}
	}
	if(!isset($error)){
		require $rootDir.'/templates/confirmation.html.php';
		exit();
	}
}elseif(isset($_GET['shortEdition'])){
	try{
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$Query = $product1->getProductQuery($_GET['id']);
		$QueryResult = $Query->fetch();
		$Query3= $product1->getReductionData($_GET['id']);
		$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$Query2 = $product2->getProductQuery($_GET['id']);
		$QueryResult2 = $Query2->fetch();
		$Query4= $product2->getReductionData($_GET['id']);
		$button= 'Aktualizuj produkt w obu bazach';
		$editForm='?editformBoth';
	}catch (PODException $e){
		$error='Błąd przy pobieraniu informacji o produkcie: ' . $e->getMessage();
	}
	if(!isset($error)){
		require $rootDir.'/templates/form.html.php';
		exit();
	}
}elseif(isset($_GET['fullEditionN'])OR(isset($_GET['fullEditionO']))){
	if (isset($_GET['fullEditionN'])){
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$editForm='?editcompleteformnew';
	}elseif(isset($_GET['fullEditionO'])){
		$product1= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$product2= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$editForm='?editcompleteformold';
	}
	$Query = $product1->getWholeDetailsQuery($_GET['id']);
	$QueryResult = $Query->fetch();
	$reduction1= $product1->getReductionData($_GET['id']);
	$manufacturer = $product1->selectManufacturer($_GET['id']);
	$category = $product1->getCategory($_GET['id']);
	foreach ($category as $category1){
		$this[]=array('id'=>$category1['id_category'], 'name'=>$category1['meta_title']);
		$selectedCats[]=$category1['id_category'];
	}
	$result= $product1->getEveryCategory();
	foreach ($result as $result2){
		$this2[]=array('id'=>$result2['id_category'], 'name'=>$result2['meta_title'], 'selected'=> in_array($result2['id_category'], $selectedCats));
	}
	$selectTag = $product1->selectTag($_GET['id']);
	foreach ($selectTag as $tag){
		if($tag!=''){
			$this3[]=array('id'=>$tag['id_tag'],'name'=>$tag['name']);
		}else{
			$tag='';
		}
	}
	foreach ($this3 as $this4){
		$tagNames[]=$this4['name']; 
		$completeTagNames=implode(", ", $tagNames);
	}
	$secondPrice = $product2->getPrice($_GET['id']);
	$reduction2= $product2->getReductionData($_GET['id']);
	require $rootDir.'/templates/completeForm.html.php';
	exit();
}elseif(isset($_GET['action'])and $_GET['action']=='idsearch'){
	$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
	$newQuery = $product1->getProductQuery($_GET['idnr']);
	$newQueryResult = $newQuery->fetch();
	$newQuery2= $product1->getReductionData($_GET['idnr']);
	$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
	$oldQuery = $product2->getProductQuery($_GET['idnr']);
	$oldQueryResult = $oldQuery->fetch();
	$oldQuery2= $product2->getReductionData($_GET['idnr']);
	require $rootDir.'/templates/products.html.php';
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
			$implodeSelect=' WHERE'.implode(" AND",$prequery).' ORDER BY id_product';
			$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
			$newQuery = $product1->getProductData($implodeSelect);
			foreach ($newQuery as $newQuery2){
			$newQuery3[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>$newQuery2['price']);
		}
	require $rootDir.'/templates/products.html.php';
	}
}

if(isset($error)){
	require $rootDir.'/templates/error.html';
}
ob_end_flush();
exit();