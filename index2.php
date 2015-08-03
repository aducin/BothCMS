<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/bootstrap.php';

session_start();
if(!isset($_SESSION['zalogowany'])){
	$login=$_POST['login'];
	$password=$_POST['password'];
}
if (!isset($_SESSION['zalogowany'])){
	if($result=$newpdo->query("SELECT * FROM ps_db_user WHERE login='$login' AND password='$password'"))
	{
		$resNumb=$result->rowCount();
		if($resNumb>0)
		{
			$finalResult=$result->fetch(PDO::FETCH_ASSOC);
			$login=' Użytkownik: '.$finalResult['login'];
			$_SESSION['zalogowany']=1;
			$result->closeCursor();
			echo'Witamy w systemie CMS obu paneli! '.$login;
		}
		if(!isset($_SESSION['zalogowany'])){
			header('Location:logowanie.html');
			exit();
		} 
	}
}
include 'orderSearch.html';

if(isset($_GET['editformBoth']))
{
	if ($_POST['text']=='')
	{
		echo 'Brak aktualnego wpisu: <b>nazwa produktu!</b>';
		exit();
	}
	elseif ($_POST['quantity']=='')
	{
		echo 'Brak aktualnego wpisu: <b>nowa liczba produktu!</b>';
		exit();
	}
	else try
	{
		$newTry= new NewProduct;
		$newQuery = $newTry->updateBoth($_POST['id'], $_POST['nominalPriceNew'], $_POST['text'], $_POST['quantity'], $newpdo);
		$newQuery2 = $newTry->confirmation($_POST['id'], $newpdo);
		$quantityNew= $newQuery2["quantity"];
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja nowych danych nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try
	{
		$oldTry= new OldProduct;
		$oldQuery = $oldTry->updateBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'],$oldpdo);
		$oldQuery2 = $oldTry->confirmation($_POST['id'], $oldpdo);
		$idOld= $oldQuery2["id_product"];
		$quantityOld= $oldQuery2["quantity"];
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja starych danych nie powiodła się: ' . $e->getMessage();
		exit();
	}
	include 'confirmation.html.php';
	exit();
}
if(isset($_GET['editcompleteformold']))
{
	if ($_POST['text']=='')
	{
		echo 'Musisz podać nazwę produktu!';
		exit();
	}
	elseif ($_POST['quantity']=='')
	{
		echo 'Musisz podać nową ilość produktu!';
		exit();
	}
	else try
			{
				$oldTry= new OldProduct;
				if (isset($_POST['zmiana'])and $_POST['zmiana']== "zmianaNazwy"){
					$oldQuery = $oldTry->insertModyfy($_POST['id'], $_POST['text'], $oldpdo);
				}
				if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
					$oldQuery = $oldTry->deleteImage($_POST['id'], $oldpdo);
				}
				$oldQuery = $oldTry->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active'], $oldpdo);
			}
			catch (PDOExceptioon $e)
			{
				echo 'Aktualizacja nazwy nie powiodła się: ' . $e->getMessage();
				exit();
			}
	try
	{
		$oldQuery = $oldTry->updateManufacturer($_POST['author'], $_POST['id'], $oldpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
		exit();
	}
	$oldQuery = $oldTry->deleteCategory($_POST['id'], $oldpdo);
	if(isset($_POST['categories']))
	{
		$oldQuery = $oldTry->insertCategory($_POST['categories'], $_POST['id'], $oldpdo);
	} else {echo'Nie znaleziono kategorii do zapisania!';
}
$oldQuery = $oldTry->deleteWholeTag($_POST['id'], $oldpdo);
foreach ($_POST['tagText'] as $tagText){
	$oldQuery = $oldTry->checkIfTag($tagText, $oldpdo);
	$oldQuery2 = $oldQuery->fetch();
	$checkedTagId= $oldQuery2[0];
	if($checkedTagId!=0)
	{
		$oldQuery = $oldTry->insertTag($checkedTagId, $_POST['id'], $oldpdo);
	}
	else{
		$oldQuery = $oldTry->createTag($checkedTagId, $tagText, $oldpdo);
		$oldQuery = $oldTry->checkIfTag($tagText, $oldpdo);
		$oldQuery2 = $oldQuery->fetch();
		$checkedTagId= $oldQuery2[0];
		$oldQuery = $oldTry->insertTag($checkedTagId, $_POST['id'], $oldpdo);
	}
}
try
{
	$oldQuery = $oldTry->confirmation($_POST['id'], $oldpdo);
	$idOld= $oldQuery["id_product"];
	$quantityOld= $oldQuery["quantity"];
}
catch (PDOExceptioon $e)
{
	echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
	exit();
}
if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'obie'){
	$newTry= new NewProduct;
	if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
					$newQuery = $newTry->deleteImage($_POST['id'], $newpdo);
	}
	$newQuery = $newTry->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active'], $newpdo);
	try
	{
		$newQuery = $newTry->updateManufacturer($_POST['author'], $_POST['id'], $newpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
		exit();
	}
	$newQuery = $newTry->deleteCategory($_POST['id'], $newpdo);
	if(isset($_POST['categories'])){
		$newQuery = $newTry->insertDifferentCategory($_POST['categories'], $_POST['id'], $newpdo);	
	}else {echo'Nie znaleziono kategorii do zapisania!';}
	$newQuery = $newTry->deleteWholeTag($_POST['id'], $newpdo);
	foreach ($_POST['tagText'] as $tagText){
		$newQuery = $newTry->checkIfTag($tagText, $newpdo);
		$newQuery2 = $newQuery->fetch();
		$checkedTagId= $newQuery2[0];
		if($checkedTagId!=0)
		{
			$newQuery = $newTry->insertTag($checkedTagId, $_POST['id'], $newpdo);
		}
		else{
			$newQuery = $newTry->createTag($checkedTagId, $tagText, $newpdo);
			$newQuery = $newTry->checkIfTag($tagText, $newpdo);
			$newQuery2 = $newQuery->fetch();
			$checkedTagId= $newQuery2[0];
			$newQuery = $newTry->insertTag($checkedTagId, $_POST['id'], $newpdo);
		}
	}
}
include 'confirmation.html.php';
exit();
}
if(isset($_GET['editcompleteformnew']))
{
	if ($_POST['text']=='')
	{
		echo 'Musisz podać nazwę produktu!';
		exit();
	}
	elseif ($_POST['quantity']=='')
	{
		echo 'Musisz podać nową ilość produktu!';
		exit();
	}
	else try
		{
			if (isset($_POST['zmiana'])and $_POST['zmiana']== "zmianaNazwy"){
				$oldTry= new OldProduct;
				$oldQuery = $oldTry->insertModyfy($_POST['id'], $_POST['text'], $oldpdo);
			}
			$newTry= new NewProduct;
			if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
				$newQuery = $newTry->deleteImage($_POST['id'], $newpdo);
			}
			$newQuery = $newTry->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active'], $newpdo);
		}
		catch (PDOExceptioon $e)
		{
			echo 'Aktualizacja nazwy i ilości nie powiodła się: ' . $e->getMessage();
			exit();
		}
	try
	{
		$newQuery = $newTry->updateManufacturer($_POST['author'], $_POST['id'], $newpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
		exit();
	}
	$newQuery = $newTry->deleteCategory($_POST['id'], $newpdo);
	if(isset($_POST['categories']))
	{
		$newQuery = $newTry->insertCategory($_POST['categories'], $_POST['id'], $newpdo);
	} else {echo'Nie znaleziono kategorii do zapisania!';
}
$newQuery = $newTry->deleteWholeTag($_POST['id'], $newpdo);
foreach ($_POST['tagText'] as $tagText){
	$newQuery = $newTry->checkIfTag($tagText, $newpdo);
	$newQuery2 = $newQuery->fetch();
	$checkedTagId= $newQuery2[0];
	if($checkedTagId!=0)
	{
		$newQuery = $newTry->insertTag($checkedTagId, $_POST['id'], $newpdo);
	}
	else{
		$newQuery = $newTry->createTag($checkedTagId, $tagText, $newpdo);
		$newQuery = $newTry->checkIfTag($tagText, $newpdo);
		$newQuery2 = $newQuery->fetch();
		$checkedTagId= $newQuery2[0];
		$newQuery = $newTry->insertTag($checkedTagId, $_POST['id'], $newpdo);
	}
}
try
{
	$newQuery = $newTry->confirmation($_POST['id'], $newpdo);
	$idOld= $newQuery["id_product"];
	$quantityNew= $newQuery["quantity"];
}
catch (PDOExceptioon $e)
{
	echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
	exit();
}
if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'obie'){
	$oldTry= new OldProduct;
	if (isset($_POST['delete'])and $_POST['delete']== "deleteImages"){
			$oldQuery = $oldTry->deleteImage($_POST['id'], $oldpdo);
		}
	$oldQuery = $oldTry->updateDetailedBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], $_POST['link'], $_POST['condition'], $_POST['active'], $oldpdo);
	try
	{
		$oldQuery = $oldTry->updateManufacturer($_POST['author'], $_POST['id'], $oldpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
		exit();
	}
	$oldQuery = $oldTry->deleteCategory($_POST['id'], $oldpdo);
	if(isset($_POST['categories']))
	{
		$oldQuery = $oldTry->insertDifferentCategory($_POST['categories'], $_POST['id'], $oldpdo);
	} 	else {echo'Nie znaleziono kategorii do zapisania!';
}
$oldQuery = $oldTry->deleteWholeTag($_POST['id'], $oldpdo);
foreach ($_POST['tagText'] as $tagText){
	$oldQuery = $oldTry->checkIfTag($tagText, $oldpdo);
	$oldQuery2 = $oldQuery->fetch();
	$checkedTagId= $oldQuery2[0];
	if($checkedTagId!=0)
	{
		$oldQuery = $oldTry->insertTag($checkedTagId, $_POST['id'], $oldpdo);
	}
	else{
		$oldQuery = $oldTry->createTag($checkedTagId, $tagText, $oldpdo);
		$oldQuery = $oldTry->checkIfTag($tagText, $oldpdo);
		$oldQuery2 = $oldQuery->fetch();
		$checkedTagId= $oldQuery2[0];
		$oldQuery = $oldTry->insertTag($checkedTagId, $_POST['id'], $oldpdo);
	}
}
}	
include 'confirmation.html.php';
exit();
}
if (isset($_GET['shipmentNumber']))
{
	include'shipmentMail.html';exit();
}

if(isset($_GET['action'])&&$_GET['action']=='orderSearch')
{
	if ($_GET['neworder'] !=''){
		$try= new NewOrder;
		$query = $try->getQuery($_GET['neworder'], $newpdo);

		foreach ($query as $sOrder)
		{
			$this[]=array('id'=>$sOrder['product_id'], 'name'=>$sOrder['name'], 'onStock'=>$sOrder['product_quantity'], 'quantity'=>$sOrder['quantity']);
		}
	}
	if ($_GET['oldorder'] !=''){
		$try= new OldOrder;
		$query = $try->getQuery($_GET['oldorder'], $oldpdo);

		foreach ($query as $sOrder)
		{
			$this[]=array('id'=>$sOrder['product_id'], 'name'=>$sOrder['name'], 'onStock'=>$sOrder['product_quantity'], 'quantity'=>$sOrder['quantity']);
		}
	}
	if ($_GET['notification'] !='')
	{
		if(isset($_GET['send'])&&$_GET['send']=='modele'){
			$try= new OldOrder;
			$notification = $try->SendNotification($_GET['notification'], $oldpdo);
			$notificationresult = $notification->fetch();
		}
		if(isset($_GET['send'])&&$_GET['send']=='ad9bis'){
			$try= new NewOrder;	
			$notification = $try->SendNotification($_GET['notification'], $newpdo);
			$notificationresult = $notification->fetch();
		}
	}
	if ($_GET['detailorder'] !='')
	{
		$detail= new OldOrder;
		$details = $detail->getQueryDetails($_GET['detailorder'], $oldpdo);
		$detailsCount=$detail->getCount($_GET['detailorder'], $oldpdo);
		$detailsCountresult = $detailsCount->fetch();
		$count = $detailsCountresult[0];
		foreach ($details as $sDetail)
		{
			$detail2[]=array('id'=>$sDetail['product_id'], 'name'=>$sDetail['name'], 'price'=>$sDetail['product_price'], 'reduction'=>$sDetail['reduction_amount'], 'quantity'=>$sDetail['product_quantity'], 'total'=>$sDetail['total_price_tax_incl'], 'productSum'=>$sDetail['total_products'], 'totalPaid'=>$sDetail['total_paid'], 'mail'=>$sDetail['email'], 'first'=>$sDetail['firstname'], 'last'=>$sDetail['lastname'], 'reference'=>$sDetail['reference'], 'payment'=>$sDetail['payment']);
		}
	}
	include 'products3.html';
}
if(isset($_GET['action'])){
	if ($_GET['action']== 'Wyrównaj ilość w starej bazie')
	{
		$oldTry= new OldProduct;
		$oldQuery = $oldTry->updateQuantity($_GET['quantity'], $_GET['id'], $oldpdo);
		$oldQuery = $oldTry->confirmation($_GET['id'], $oldpdo);
		$idOld= $oldQuery["id_product"];
		$quantityOld= $oldQuery["quantity"];
		include 'confirmation.html.php';
		exit();
	}
	if ($_GET['action']== 'Wyrównaj ilość w nowej bazie')
	{
		$newTry= new NewProduct;
		$newQuery = $newTry->updateQuantity($_GET['quantity'], $_GET['id'], $newpdo);
		$newQuery = $newTry->confirmation($_GET['id'], $newpdo);
		$idOld= $newQuery["id_product"];
		$quantityNew= $newQuery["quantity"];
		include 'confirmation.html.php';
		exit();
	}
	if ($_GET['action']== 'Zmiana obu przez nowy panel' OR $_GET['action']== 'Zmiana obu przez stary panel')
	{
		try
		{
			$newTry= new NewProduct;
			$Query = $newTry->getProductQuery($_GET['id'], $newpdo);
			$QueryResult = $Query->fetch();
			$Query3= $newTry->getReduction($_GET['id'], $newpdo);
			$QueryResult3 = $Query3->fetch();
			$oldTry= new OldProduct;
			$Query2 = $oldTry->getProductQuery($_GET['id'], $oldpdo);
			$QueryResult2 = $Query2->fetch();
			$Query4= $oldTry->getReduction($_GET['id'], $oldpdo);
			$QueryResult4 = $Query4->fetch();
			$button= 'Aktualizuj produkt w obu bazach';
			$editForm='?editformBoth';
		}
		catch (PODException $e)
		{
			echo 'Błąd przy pobieraniu informacji o produkcie: ' . $e->getMessage();
			exit();
		}
		include 'form.html.php';
		exit();
	}
	if (isset($_GET['action'])and $_GET['action']== 'Kompletna edycja w NP')
		try
	{
		$oldTry= new NewProduct;
		$Query = $oldTry->getWholeDetailsQuery($_GET['id'], $newpdo);
		$QueryResult = $Query->fetch();
		$Query1 = $oldTry->getReduction($_GET['id'], $newpdo);
		$Query3 = $Query1->fetch();
		$Query6 = $oldTry->selectManufacturer($_GET['id'], $newpdo);
		$list= new OldLists($newpdo);
		$result= $list->selectWholeManufacturer();
		foreach ($result as $row)
		{
			$authors[]= array('id'=> $row['id_manufacturer'], 'name'=> $row['name']);
		}
		$Query8 = $oldTry->getCategory($_GET['id'], $newpdo);
		foreach ($Query8 as $Query9)
		{
			$this[]=array('id'=>$Query9['id_category'], 'name'=>$Query9['meta_title']);
			$selectedCats[]=$Query9['id_category'];
		}
		$Query10 = $oldTry->getWholeCategory($newpdo);
		foreach ($Query10 as $Query11)
		{
			$this2[]=array('id'=>$Query11['id_category'], 'name'=>$Query11['meta_title'], 'selected'=> in_array($Query11['id_category'], $selectedCats));
		}
		$Query12 = $oldTry->selectTag($_GET['id'], $newpdo);
		foreach ($Query12 as $Query13)
		{
			$this3[]=array('id'=>$Query13['id_tag'], 'name'=>$Query13['name']);
		}
		$newTry= new OldProduct;
		$Query2 = $newTry->getProductQuery($_GET['id'], $oldpdo);
		$QueryResult2 = $Query2->fetch();
		$Query4 = $newTry->getReduction($_GET['id'], $oldpdo);
		$Query5 = $Query4->fetch();
		$baza='- informacje z nowego panelu.';
		$editForm='?editcompleteformnew';

		include 'completeForm.html.php';
		exit();
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie kompletnych danych ze starej bazy nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if (isset($_GET['action'])and $_GET['action']== 'Kompletna edycja w SP')
		try
	{
		$oldTry= new OldProduct;
		$Query = $oldTry->getWholeDetailsQuery($_GET['id'], $oldpdo);
		$QueryResult = $Query->fetch();
		$Query1 = $oldTry->getReduction($_GET['id'], $oldpdo);
		$Query3 = $Query1->fetch();
		$Query6 = $oldTry->selectManufacturer($_GET['id'], $oldpdo);
		$list= new OldLists($oldpdo);
		$result= $list->selectWholeManufacturer();
		foreach ($result as $row)
		{
			$authors[]= array('id'=> $row['id_manufacturer'], 'name'=> $row['name']);
		}
		$Query8 = $oldTry->getCategory($_GET['id'], $oldpdo);
		foreach ($Query8 as $Query9)
		{
			$this[]=array('id'=>$Query9['id_category'], 'name'=>$Query9['meta_title']);
			$selectedCats[]=$Query9['id_category'];
		}
		$Query10 = $oldTry->getWholeCategory($oldpdo);
		foreach ($Query10 as $Query11)
		{
			$this2[]=array('id'=>$Query11['id_category'], 'name'=>$Query11['meta_title'], 'selected'=> in_array($Query11['id_category'], $selectedCats));
		}
		$Query12 = $oldTry->selectTag($_GET['id'], $oldpdo);
		foreach ($Query12 as $Query13)
		{
			$this3[]=array('id'=>$Query13['id_tag'], 'name'=>$Query13['name']);
		}
		$newTry= new NewProduct;
		$Query2 = $newTry->getProductQuery($_GET['id'], $newpdo);
		$QueryResult2 = $Query2->fetch();
		$Query4 = $newTry->getReduction($_GET['id'], $newpdo);
		$Query5 = $Query4->fetch();
		$baza='- informacje ze starego panelu.';
		$editForm='?editcompleteformold';

		include 'completeForm.html.php';
		exit();
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie kompletnych danych ze starej bazy nie powiodło się: ' . $e->getMessage();
		exit();
	}
}
if (isset($_GET['action'])and $_GET['action']== 'Uaktualnij ilości dla całego zamówienia')
	try
{
	$include=0;
	$newTry= new NewOrder;
	$newQuery = $newTry->selectOrderQuantity($_GET['id_number'], $oldpdo);
	foreach ($newQuery as $newQuery2){
		$mods[]= array('quantity'=>$newQuery2['quantity'], 'product_id'=>$newQuery2['product_id'], 'id_order'=>$newQuery2['id_order']);
	}
	include 'orderUpgrade.html.php';
}
catch (PDOExceptioon $e)
{
	echo 'Pobranie ilości w zamówieniu nie powiodło się: ' . $e->getMessage();
	exit();
}

if (isset($_GET['action'])and $_GET['action']== 'Uaktualnij ilości w całym zamówieniu')
	try
{
	$include=1;
	$oldTry= new OldOrder;
	$oldQuery = $oldTry->selectOrderQuantity($_GET['id_number'], $newpdo);
	foreach ($oldQuery as $oldQuery2){
		$mods[]= array('quantity'=>$oldQuery2['quantity'], 'product_id'=>$oldQuery2['product_id'], 'id_order'=>$oldQuery2['id_order']);
	}
	include 'orderUpgrade.html.php';
}
catch (PDOExceptioon $e)
{
	echo 'Pobranie ilości w zamówieniu nie powiodło się: ' . $e->getMessage();
	exit();
}

?>
