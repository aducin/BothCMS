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
if(isset($_GET['editcompleteformold'])){
	if ($_POST['text']==''){
		echo 'Musisz podać nazwę produktu!';
		exit();
	}elseif ($_POST['quantity']==''){
		echo 'Musisz podać nową ilość produktu!';
		exit();
	}else try{
		$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		if (isset($_POST['change'])and $_POST['change']== "nameChange"){
			$oldQuery = $product2->insertModyfy($_POST['id'], $_POST['text']);
		}
		if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
			$oldQuery = $product2->deleteImage($_POST['id']);
		}
		$oldQuery = $oldTry->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active']);
	}catch (PDOExceptioon $e){
		echo 'Aktualizacja nazwy nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try{
		$oldQuery = $product2->updateManufacturer($_POST['author'], $_POST['id']);
	}catch (PDOExceptioon $e){
		echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
		exit();
	}
	$oldQuery = $product2->deleteCategory($_POST['id']);
	if(isset($_POST['categories'])){
		$oldQuery = $product2->insertCategory($_POST['categories'], $_POST['id']);
	}else{
		echo'Nie znaleziono kategorii do zapisania!';
	}
	$oldQuery = $product2->deleteWholeTag($_POST['id']);
	foreach ($_POST['tagText'] as $tagText){
		$oldQuery = $product2->checkIfTag($tagText);
		$oldQuery2 = $oldQuery->fetch();
		$checkedTagId= $oldQuery2[0];
		if($checkedTagId!=0){
			$oldQuery = $product2->insertTag($checkedTagId, $_POST['id']);
		}else{
			$oldQuery = $product2->createTag($checkedTagId, $tagText);
			$oldQuery = $product2->checkIfTag($tagText);
			$oldQuery2 = $oldQuery->fetch();
			$checkedTagId= $oldQuery2[0];
			$oldQuery = $product2->insertTag($checkedTagId, $_POST['id']);
		}
	}
	try{
		$oldQuery = $product2->confirmation($_POST['id']);
		$idOld= $oldQuery["id_product"];
		$quantityOld= $oldQuery["quantity"];
	}catch (PDOExceptioon $e){
		echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'both'){
		$product1= new LinuxPlProduct;
		if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
			$newQuery = $product1->deleteImage($_POST['id']);
		}
		$newQuery = $product1->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active']);
		try{
			$newQuery = $product1->updateManufacturer($_POST['author'], $_POST['id']);
		}catch (PDOExceptioon $e){
			echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
			exit();
		}
		$newQuery = $product1->deleteCategory($_POST['id'], $newpdo);
		if(isset($_POST['categories'])){
			$newQuery = $product1->insertDifferentCategory($_POST['categories'], $_POST['id']);	
		}else{
			echo'Nie znaleziono kategorii do zapisania!';
		}
		$newQuery = $newTry->deleteWholeTag($_POST['id']);
		foreach ($_POST['tagText'] as $tagText){
			$newQuery = $product1->checkIfTag($tagText);
			$newQuery2 = $newQuery->fetch();
			$checkedTagId= $newQuery2[0];
			if($checkedTagId!=0){
				$newQuery = $product1->insertTag($checkedTagId, $_POST['id']);
			}else{
				$newQuery = $product1->createTag($checkedTagId, $tagText);
				$newQuery = $product1->checkIfTag($tagText, $newpdo);
				$newQuery2 = $newQuery->fetch();
				$checkedTagId= $newQuery2[0];
				$newQuery = $product1->insertTag($checkedTagId, $_POST['id']);
			}
		}
	}
	include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/confirmation.html.php';
	exit();
}
if(isset($_GET['editcompleteformnew'])){
	if ($_POST['text']==''){
		echo 'Musisz podać nazwę produktu!';
		exit();
	}elseif ($_POST['quantity']==''){
		echo 'Musisz podać nową ilość produktu!';
		exit();
	}else try{
		if (isset($_POST['change'])and $_POST['change']== "nameChange"){
			$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
			$oldQuery = $product2->insertModyfy($_POST['id'], $_POST['text']);
		}
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
			$newQuery = $product1->deleteImage($_POST['id']);
		}
		$newQuery = $product1->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active']);
	}catch (PDOExceptioon $e){
		echo 'Aktualizacja nazwy i ilości nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try{
		$newQuery = $product1->updateManufacturer($_POST['author'], $_POST['id']);
	}catch (PDOExceptioon $e){
		echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
		exit();
	}
	$newQuery = $product1->deleteCategory($_POST['id']);
	if(isset($_POST['categories'])){
		$newQuery = $product1->insertCategory($_POST['categories'], $_POST['id']);
	}else{
		echo'Nie znaleziono kategorii do zapisania!';
	}
	$newQuery = $product1->deleteWholeTag($_POST['id']);
	foreach ($_POST['tagText'] as $tagText){
		$newQuery = $product1->checkIfTag($tagText);
		$newQuery2 = $newQuery->fetch();
		$checkedTagId= $newQuery2[0];
		if($checkedTagId!=0){
			$newQuery = $product1->insertTag($checkedTagId, $_POST['id']);
		}else{
			$newQuery = $product1->createTag($checkedTagId, $tagText);
			$newQuery = $product1->checkIfTag($tagText);
			$newQuery2 = $newQuery->fetch();
			$checkedTagId= $newQuery2[0];
			$newQuery = $product1->insertTag($checkedTagId, $_POST['id']);
		}
	}
	try{
		$newQuery = $product1->confirmation($_POST['id']);
		$idOld= $newQuery["id_product"];
		$quantityNew= $newQuery["quantity"];
	}catch (PDOExceptioon $e){
		echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'both'){
		$order1= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
			$oldQuery = $order1->deleteImage($_POST['id']);
		}
		$oldQuery = $order1->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active']);
		try{
			$oldQuery = $order1->updateManufacturer($_POST['author'], $_POST['id']);
		}catch (PDOExceptioon $e){
			echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
			exit();
		}
		$oldQuery = $order1->deleteCategory($_POST['id']);
		if(isset($_POST['categories'])){
			$oldQuery = $order1->insertDifferentCategory($_POST['categories'], $_POST['id']);
		}else{
			echo'Nie znaleziono kategorii do zapisania!';
		}
		$oldQuery = $order1->deleteWholeTag($_POST['id']);
		foreach ($_POST['tagText'] as $tagText){
			$oldQuery = $order1->checkIfTag($tagText);
			$oldQuery2 = $oldQuery->fetch();
			$checkedTagId= $oldQuery2[0];
			if($checkedTagId!=0){
				$oldQuery = $order1->insertTag($checkedTagId, $_POST['id']);
			}else{
				$oldQuery = $order1->createTag($checkedTagId, $tagText);
				$oldQuery = $order1->checkIfTag($tagText);
				$oldQuery2 = $oldQuery->fetch();
				$checkedTagId= $oldQuery2[0];
				$oldQuery = $order1->insertTag($checkedTagId, $_POST['id']);
			}
		}
	}
	include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/confirmation.html.php';
	exit();
}
if (isset($_GET['shipmentNumber'])){
	include'templates/shipmentMail.html';
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
if (isset($_GET['fullEditionO'])){
	try{
		$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$Query = $product2->getWholeDetailsQuery($_GET['id']);
		$QueryResult = $Query->fetch();
		$Query1 = $product2->getReduction($_GET['id']);
		$Query3 = $Query1->fetch();
		$Query6 = $product2->selectManufacturer($_GET['id']);
		$helper= new OgicomHelper($secondHost, $secondLogin, $secondPassword);
		$result= $helper->selectWholeManufacturer();
		foreach ($result as $row){
			$authors[]= array('id'=> $row['id_manufacturer'], 'name'=> $row['name']);
		}
		$Query8 = $product2->getCategory($_GET['id']);
		foreach ($Query8 as $Query9){
			$this[]=array('id'=>$Query9['id_category'], 'name'=>$Query9['meta_title']);
			$selectedCats[]=$Query9['id_category'];
		}
		$Query10 = $product2->getEveryCategory();
		foreach ($Query10 as $Query11){
			$this2[]=array('id'=>$Query11['id_category'], 'name'=>$Query11['meta_title'], 'selected'=> in_array($Query11['id_category'], $selectedCats));
		}
		$Query12 = $product2->selectTag($_GET['id']);
		foreach ($Query12 as $Query13){
			$this3[]=array('id'=>$Query13['id_tag'], 'name'=>$Query13['name']);
		}
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$Query2 = $product1->getProductQuery($_GET['id']);
		$QueryResult2 = $Query2->fetch();
		$Query4 = $product1->getReduction($_GET['id']);
		$Query5 = $Query4->fetch();
		$baza='- informacje ze starego panelu.';
		$editForm='?editcompleteformold';

		include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/completeForm.html.php';
		exit();
	}catch (PDOExceptioon $e){
		echo 'Pobranie kompletnych danych ze starej bazy nie powiodło się: ' . $e->getMessage();
		exit();
	}
}
if (isset($_GET['fullEditionN'])){
	try{
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$Query = $product1->getWholeDetailsQuery($_GET['id']);
		$QueryResult = $Query->fetch();
		$Query1 = $product1->getReduction($_GET['id']);
		$Query3 = $Query1->fetch();
		$Query6 = $product1->selectManufacturer($_GET['id']);
		$helper= new OgicomHelper($firstHost, $firstLogin, $firstPassword);
		$result= $helper->selectWholeManufacturer();
		foreach ($result as $row){
			$authors[]= array('id'=> $row['id_manufacturer'], 'name'=> $row['name']);
		}
		$Query8 = $product1->getCategory($_GET['id']);
		foreach ($Query8 as $Query9){
			$this[]=array('id'=>$Query9['id_category'], 'name'=>$Query9['meta_title']);
			$selectedCats[]=$Query9['id_category'];
		}
		$Query10 = $product1->getEveryCategory();
		foreach ($Query10 as $Query11){
			$this2[]=array('id'=>$Query11['id_category'], 'name'=>$Query11['meta_title'], 'selected'=> in_array($Query11['id_category'], $selectedCats));
		}
		$Query12 = $product1->selectTag($_GET['id']);
		foreach ($Query12 as $Query13){
			$this3[]=array('id'=>$Query13['id_tag'], 'name'=>$Query13['name']);
		}
		$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$Query2 = $product2->getProductQuery($_GET['id']);
		$QueryResult2 = $Query2->fetch();
		$Query4 = $product2->getReduction($_GET['id']);
		$Query5 = $Query4->fetch();
		$baza='- informacje z nowego panelu.';
		$editForm='?editcompleteformnew';

		include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/completeForm.html.php';
		exit();
	}catch (PDOExceptioon $e){
		echo 'Pobranie kompletnych danych ze starej bazy nie powiodło się: ' . $e->getMessage();
		exit();
	}
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
