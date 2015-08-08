<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/config/bootstrap.php';

$DBHandler=parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/config/database.ini',true);
$firstHandler=$DBHandler ["firstDB"];
$firstHost=$firstHandler["host"];
$firstLogin=$firstHandler["login"];
$firstPassword=$firstHandler["password"];
$secondHandler=$DBHandler ["secondDB"];
$secondHost=$secondHandler["host"];
$secondLogin=$secondHandler["login"];
$secondPassword=$secondHandler["password"];

session_start();
if(!isset($_SESSION['log'])){
	$userLogin=$_POST['login'];
	$userPassword=$_POST['password'];
}
if (!isset($_SESSION['log'])){
	$db=new db($firstHost, $firstLogin, $firstPassword);
	$dbResult= $db->getUserData($userLogin, $userPassword);
	$resNumb=$dbResult->rowCount();
	if($resNumb>0){
		$resNumb=$result->rowCount();
		if($resNumb>0){
			$finalResult=$result->fetch(PDO::FETCH_ASSOC);
			$login=' Użytkownik: '.$finalResult['login'];
			$_SESSION['log']=1;
			$result->closeCursor();
			echo'Witamy w systemie CMS obu paneli! '.$login;
		}
		if(!isset($_SESSION['log'])){
			header('Location:templates/signIn.html');
			exit();
		} 
	}
}
include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/orderSearch.html';

if(isset($_GET['editformBoth'])){
	if ($_POST['text']==''){
		echo 'Brak aktualnego wpisu: <b>nazwa produktu!</b>';
		exit();
	}elseif ($_POST['quantity']==''){
		echo 'Brak aktualnego wpisu: <b>nowa liczba produktu!</b>';
		exit();
	}else try{
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$newQuery = $product1->updateBoth($_POST['id'], $_POST['nominalPriceNew'], $_POST['text'], $_POST['quantity']);
		$newQuery2 = $product1->confirmation($_POST['id']);
		$quantityNew= $newQuery2["quantity"];
	}catch (PDOExceptioon $e){
		echo 'Aktualizacja nowych danych nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try{
		$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$oldQuery = $product2->updateBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity']);
		$oldQuery2 = $product2->confirmation($_POST['id']);
		$idOld= $oldQuery2["id_product"];
		$quantityOld= $oldQuery2["quantity"];
	}catch (PDOExceptioon $e){
		echo 'Aktualizacja starych danych nie powiodła się: ' . $e->getMessage();
		exit();
	}
	include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/confirmation.html.php';
	exit();
}
if(isset($_GET['editcompleteformnew'])){
	$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
	$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
	if (isset($_POST['change'])and $_POST['change']== "nameChange"){
		$oldQuery = $product2->insertModyfy($_POST['id'], $_POST['text']);
	}
}
if(isset($_GET['editcompleteformold'])){
	$product2= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
	$product1= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
	if (isset($_POST['change'])and $_POST['change']== "nameChange"){
		$oldQuery = $product1->insertModyfy($_POST['id'], $_POST['text']);
	}
}
if(isset($_GET['editcompleteformnew'])OR(isset($_GET['editcompleteformold']))){
	if ($_POST['text']==''){
		echo 'Musisz podać nazwę produktu!';
		exit();
	}elseif ($_POST['quantity']==''){
		echo 'Musisz podać nową ilość produktu!';
		exit();
	}else try{
		if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
			$Query = $product1->deleteImage($_POST['id']);
		}
		$Query = $product1->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active']);
	}catch (PDOExceptioon $e){
		echo 'Aktualizacja nazwy nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try{
		$Query = $product1->updateManufacturer($_POST['author'], $_POST['id']);
	}catch (PDOExceptioon $e){
		echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
		exit();
	}
	$Query = $product1->deleteCategory($_POST['id']);
	if(isset($_POST['categories'])){
		$Query = $product1->insertCategory($_POST['categories'], $_POST['id']);
	}else{
		echo'Nie znaleziono kategorii do zapisania!';
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
	try{
		$Query = $product1->confirmation($_POST['id']);
		$idOld= $Query["id_product"];
		$quantityOld= $Query["quantity"];
	}catch (PDOExceptioon $e){
		echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'both'){
		if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
			$Query = $product2->deleteImage($_POST['id']);
		}
		$Query = $product2->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active']);
		try{
			$Query = $product2->updateManufacturer($_POST['author'], $_POST['id']);
		}catch (PDOExceptioon $e){
			echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
			exit();
		}
		$Query = $product2->deleteCategory($_POST['id']);
		if(isset($_POST['categories'])){
			$Query = $product2->insertDifferentCategory($_POST['categories'], $_POST['id']);	
		}else{
			echo'Nie znaleziono kategorii do zapisania!';
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
	}
	include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/confirmation.html.php';
	exit();
}
if (isset($_GET['shipmentNumber'])){
	include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/shipmentMail.html';
	exit();
}
if(isset($_GET['action'])&&$_GET['action']=='orderSearch'){
	if ($_GET['neworder'] !=''){
		$order2= new LinuxPlOrder($firstHost, $firstLogin, $firstPassword);
		$query = $order2->getQuery($_GET['neworder']);

		foreach ($query as $sOrder){
			$this[]=array('id'=>$sOrder['product_id'], 'name'=>$sOrder['name'], 'onStock'=>$sOrder['product_quantity'], 'quantity'=>$sOrder['quantity']);
		}
	}
	if ($_GET['oldorder'] !=''){
		$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
		$query = $order1->getQuery($_GET['oldorder']);

		foreach ($query as $sOrder){
			$this[]=array('id'=>$sOrder['product_id'], 'name'=>$sOrder['name'], 'onStock'=>$sOrder['product_quantity'], 'quantity'=>$sOrder['quantity']);
		}
	}
	if ($_GET['notification'] !=''){
		if(isset($_GET['send'])&&$_GET['send']=='modele'){
			$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
			$notification = $order1->sendNotification($_GET['notification']);
			$notificationresult = $notification->fetch();
		}
		if(isset($_GET['send'])&&$_GET['send']=='ad9bis'){
			$order2= new LinuxPlOrder($firstHost, $firstLogin, $secondPassword);
			$notification = $order2->sendNotification($_GET['notification']);
			$notificationresult = $notification->fetch();
		}
	}
	if ($_GET['detailorder'] !=''){
		$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
		$details = $order1->getQueryDetails($_GET['detailorder']);
		$detailsCount=$order1->getCount($_GET['detailorder']);
		$detailsCountresult = $detailsCount->fetch();
		$count = $detailsCountresult[0];
		foreach ($details as $sDetail){
			$detail2[]=array('id'=>$sDetail['product_id'], 'name'=>$sDetail['name'], 'price'=>$sDetail['product_price'], 'reduction'=>$sDetail['reduction_amount'], 'quantity'=>$sDetail['product_quantity'], 'total'=>$sDetail['total_price_tax_incl'], 'productSum'=>$sDetail['total_products'], 'totalPaid'=>$sDetail['total_paid'], 'mail'=>$sDetail['email'], 'first'=>$sDetail['firstname'], 'last'=>$sDetail['lastname'], 'reference'=>$sDetail['reference'], 'payment'=>$sDetail['payment']);
		}
	}
	include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/orders.html';
}
if (isset($_GET['BPSQO'])){
	$order1= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
	$oldQuery = $order1->updateQuantity($_GET['quantity'], $_GET['id']);
	$oldQuery = $order1->confirmation($_GET['id']);
	$idOld= $oldQuery["id_product"];
	$quantityOld= $oldQuery["quantity"];
	include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/confirmation.html.php';
	exit();
}
if (isset($_GET['BPSQN'])){
	$order2= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
	$newQuery = $order2->updateQuantity($_GET['quantity'], $_GET['id']);
	$newQuery = $order2->confirmation($_GET['id']);
	$idOld= $newQuery["id_product"];
	$quantityNew= $newQuery["quantity"];
	include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/confirmation.html.php';
	exit();
}
if(isset($_GET['shortEdition'])){
	try{
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$Query = $product1->getProductQuery($_GET['id']);
		$QueryResult = $Query->fetch();
		$Query3= $product1->getReduction($_GET['id']);
		$QueryResult3 = $Query3->fetch();
		$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$Query2 = $product2->getProductQuery($_GET['id']);
		$QueryResult2 = $Query2->fetch();
		$Query4= $product2->getReduction($_GET['id']);
		$QueryResult4 = $Query4->fetch();
		$button= 'Aktualizuj produkt w obu bazach';
		$editForm='?editformBoth';
	}catch (PODException $e){
		echo 'Błąd przy pobieraniu informacji o produkcie: ' . $e->getMessage();
		exit();
	}
	include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/form.html.php';
	exit();
}
if(isset($_GET['fullEditionN'])OR(isset($_GET['fullEditionO']))){
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
	$Query1 = $product1->getReduction($_GET['id']);
	$reduction1 = $Query1->fetch();
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
	$reduction = $product2->getReduction($_GET['id']);
	$reduction2 = $reduction->fetch();
	include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/completeForm.html.php';
	exit();
}
if (isset($_GET['action'])and $_GET['action']== 'Uaktualnij ilości dla całego zamówienia'){
	try{
		$include=0;
		$order1= new LinuxPlOrder($secondHost, $secondLogin, $secondPassword);
		$newQuery = $order1->selectOrderQuantity($_GET['id_number']);
		foreach ($newQuery as $newQuery2){
			$mods[]= array('quantity'=>$newQuery2['quantity'], 'product_id'=>$newQuery2['product_id'], 'id_order'=>$newQuery2['id_order']);
		}
		include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/orderUpgrade.html.php';
	}catch (PDOExceptioon $e){
		echo 'Pobranie ilości w zamówieniu nie powiodło się: ' . $e->getMessage();
		exit();
	}
}
if (isset($_GET['action'])and $_GET['action']== 'Uaktualnij ilości w całym zamówieniu'){
	try{
		$include=1;
		$order2= new OgicomOrder($firstHost, $firstLogin, $firstPassword);
		$oldQuery = $order2->selectOrderQuantity($_GET['id_number']);
		foreach ($oldQuery as $oldQuery2){
			$mods[]= array('quantity'=>$oldQuery2['quantity'], 'product_id'=>$oldQuery2['product_id'], 'id_order'=>$oldQuery2['id_order']);
		}
		include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/orderUpgrade.html.php';
	}catch (PDOExceptioon $e){
		echo 'Pobranie ilości w zamówieniu nie powiodło się: ' . $e->getMessage();
		exit();
	}
}