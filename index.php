<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/ProjektCap3/prestapdo.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/ProjektCap3/helpers.inc.php';
if(isset($_GET['action'])and $_GET['action']=='Usuń wpis')
{
	deleteMod($_GET['idMod'], $oldpdo);
	header('Location:.');
	exit();
}
session_start();
if(!isset($_SESSION['zalogowany'])){
	$login=$_POST['login'];
	$password=$_POST['password'];
}
if(isset($_POST['action'])&&$_POST['action']=='Wyloguj')
{
	unset($_SESSION['zalogowany']);
	header('Location:index.php');
	exit();
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
			$text= 'Witamy w systemie CMS obu paneli!';
			htmlout($text);
			htmlout($login);
		}
		if(!isset($_SESSION['zalogowany'])){
			header('Location:logowanie.html');
			exit();
		} 
	}
}
try
{
	$result =selectWholeManufacturer($oldpdo);
}
catch (PDOException $e)
{
	echo 'Pobieranie listy producentów nie powiodło się: ' . $e->getMessage();
	exit();
}
foreach ($result as $row)
{
	$authors[]= array('id'=> $row['id_manufacturer'], 'name'=> $row['name']);
}
try
{
	$result =selectWholeCategory($newpdo);
}
catch (PDOException $e)
{
	echo 'Pobieranie listy kategorii nie powiodło się: ' . $e->getMessage();
	exit();
}
foreach ($result as $row)
{
	$categories[]= array('id'=>$row['id_category'], 'name'=>$row['meta_title']);
}
$modyfy=selectModyfy($oldpdo);
foreach ($modyfy as $mod)
{
	$mods[]= array('id'=>$mod['id_number'], 'nazwa'=>$mod['name'], 'data'=>$mod['date']);
}
include 'Searchform.html.php';

if(isset($_GET['changeTabOldAdd']))
{
	$row=checkIfTag($_POST['newtagText'], $oldpdo);
	$checkedTagId=$row['id_tag'];
	if($checkedTagId!=0)
	{
		insertTag($checkedTagId, $_POST['id'], $oldpdo);
	}
	else{
		createTag($checkedTagId, $_POST['newtagText'], $oldpdo);
		$row=checkIfTag($_POST['newtagText'], $oldpdo);
		$checkedTagId=$row['id_tag'];
		insertTag($checkedTagId, $_POST['id'], $oldpdo);
	}
	include 'confirmation.html.php';
}
if(isset($_GET['changeTabNewAdd']))
{
	$row=checkIfTag($_POST['newtagText'], $newpdo);
	$checkedTagId=$row['id_tag'];
	if($checkedTagId!=0)
	{
		insertTag($checkedTagId, $_POST['id'], $newpdo);
	}
	else{
		createTag($checkedTagId, $_POST['newtagText'], $newpdo);
		$row=checkIfTag($_POST['newtagText'], $newpdo);
		$checkedTagId=$row['id_tag'];
		insertTag($checkedTagId, $_POST['id'], $newpdo);
	}
	include 'confirmation.html.php';
}
if(isset($_GET['changeTabNewCut']))
{
	$row=checkIfTag($_POST['textCut'], $newpdo);
	$checkedTagId=$row['id_tag'];
	if($checkedTagId!=0){
	deleteTag($checkedTagId, $_POST['id'], $newpdo);
	include 'confirmation.html.php';
	} else{
	echo'<b>'.'Chciałeś usunąć TAG, którego nie ma w bazie, pustaku...'.'</b>';}
}
if(isset($_GET['changeTabOldCut']))
{
	$row=checkIfTag($_POST['textCut'], $oldpdo);
	$checkedTagId=$row['id_tag'];
	if($checkedTagId!=0){
	deleteTag($checkedTagId, $_POST['id'], $oldpdo);
	include 'confirmation.html.php';
	} else{
	echo'<b>'.'Chciałeś usunąć TAG, którego nie ma w bazie, pustaku...'.'</b>';}
}
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
		updatePrice($_POST['id'], $_POST['nominalPriceNew'], $newpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja nowej ceny nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try 
	{
		updateQuantity($_POST['quantity'], $_POST['id'], $newpdo);

	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja stanu w nowej bazie nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try
	{
		updateProductName($_POST['text'], $_POST['id'], $newpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja nazwy w nowej bazie nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try
	{
	$row=confirmation($_POST['id'], $newpdo);
	$quantity= $row['quantity'];
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
		exit();
	}
	try
	{
		updatePrice($_POST['id'], $_POST['nominalPriceOld'], $oldpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja starej ceny nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try
	{
		updateQuantity($_POST['quantity'], $_POST['id'], $oldpdo);
		
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja stanu w starej bazie nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try
	{
		updateProductName($_POST['text'], $_POST['id'], $oldpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja nazwy w starej bazie nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try
	{
	$row=confirmation($_POST['id'], $oldpdo);
	$idOld= $row['id_product'];
	$quantityOld= $row['quantity'];
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
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
		if (isset($_POST['zmiana'])and $_POST['zmiana']== "zmianaNazwy"){
			insertModyfy($_POST['id'], $_POST['text'], $oldpdo);
		}
		updateProductName($_POST['text'], $_POST['id'], $oldpdo);
		updateDesc($_POST['description'], $_POST['id'], $oldpdo);
		updateDesc_short($_POST['description_short'], $_POST['id'], $oldpdo);
		updateLink($_POST['link'], $_POST['id'], $oldpdo);
		updateMeta_title($_POST['meta_title'], $_POST['id'], $oldpdo);
		updateMeta_desc($_POST['meta_description'], $_POST['id'], $oldpdo);
		updateQuantity($_POST['quantity'], $_POST['id'], $oldpdo);
		updateIndex($_POST['active'], $_POST['id'], $oldpdo);
		updateIndexShop($_POST['active'], $_POST['id'], $oldpdo);
		updateActive($_POST['active'], $_POST['id'], $oldpdo);
		updateActiveShop($_POST['active'], $_POST['id'], $oldpdo);
		updateCondition($_POST['condition'], $_POST['id'], $oldpdo);
		updateConditionShop($_POST['condition'], $_POST['id'], $oldpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja nazwy nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try
	{
		updateManufacturer($_POST['author'], $_POST['id'], $oldpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try
	{
		updatePrice($_POST['id'], $_POST['nominalPriceOld'], $oldpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja starej ceny nie powiodła się: ' . $e->getMessage();
		exit();
	}
	deleteCategory($_POST['id'], $oldpdo);
	if(isset($_POST['categories']))
	{
		insertCategory($_POST['categories'], $_POST['id'], $oldpdo);
	} else {echo'Nie znaleziono kategorii do zapisania!';
	}
	deleteWholeTag($_POST['id'], $oldpdo);
	foreach ($_POST['tagText'] as $tagText){
		$row=checkIfTag($tagText, $oldpdo);
		$checkedTagId=$row['id_tag'];
		if($checkedTagId!=0)
		{
			insertTag($checkedTagId, $_POST['id'], $oldpdo);
		}
		else{
			createTag($checkedTagId, $tagText, $oldpdo);
			$row=checkIfTag($tagText, $oldpdo);
			$checkedTagId=$row['id_tag'];
			insertTag($checkedTagId, $_POST['id'], $oldpdo);
		}
	}
	try
	{
	$row=confirmation($_POST['id'], $oldpdo);
	$idOld= $row['id_product'];
	$quantityOld= $row['quantity'];
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'obie'){
		updateProductName($_POST['text'], $_POST['id'], $newpdo);
		updateDesc($_POST['description'], $_POST['id'], $newpdo);
		updateDesc_short($_POST['description_short'], $_POST['id'], $newpdo);
		updateLink($_POST['link'], $_POST['id'], $newpdo);
		updateMeta_title($_POST['meta_title'], $_POST['id'], $newpdo);
		updateMeta_desc($_POST['meta_description'], $_POST['id'], $newpdo);
		updateQuantity($_POST['quantity'], $_POST['id'], $newpdo);
		updateIndex($_POST['active'], $_POST['id'], $newpdo);
		updateIndexShop($_POST['active'], $_POST['id'], $newpdo);
		updateActive($_POST['active'], $_POST['id'], $newpdo);
		updateActiveShop($_POST['active'], $_POST['id'], $newpdo);
		updateCondition($_POST['condition'], $_POST['id'], $newpdo);
		updateConditionShop($_POST['condition'], $_POST['id'], $newpdo);
		updateManufacturer($_POST['author'], $_POST['id'], $newpdo);
		updatePrice($_POST['id'], $_POST['nominalPriceNew'], $newpdo);
		deleteCategory($_POST['id'], $newpdo);
		if(isset($_POST['categories']))
		{
			insertDifferentCategory($_POST['categories'], $_POST['id'], $newpdo);
		} else {echo'Nie znaleziono kategorii do zapisania!';}
		deleteWholeTag($_POST['id'], $newpdo);
		foreach ($_POST['tagText'] as $tagText){
			$row=checkIfTag($tagText, $newpdo);
			$checkedTagId=$row['id_tag'];
			if($checkedTagId!=0)
			{
				insertTag($checkedTagId, $_POST['id'], $newpdo);
			}
			else{
				createTag($checkedTagId, $tagText, $newpdo);
				$row=checkIfTag($tagText, $newpdo);
				$checkedTagId=$row['id_tag'];
				insertTag($checkedTagId, $_POST['id'], $newpdo);
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
			insertModyfy($_POST['id'], $_POST['text'], $oldpdo);
		}
		updateProductName($_POST['text'], $_POST['id'], $newpdo);
		updateDesc($_POST['description'], $_POST['id'], $newpdo);
		updateDesc_short($_POST['description_short'], $_POST['id'], $newpdo);
		updateLink($_POST['link'], $_POST['id'], $newpdo);
		updateMeta_title($_POST['meta_title'], $_POST['id'], $newpdo);
		updateMeta_desc($_POST['meta_description'], $_POST['id'], $newpdo);
		updateQuantity($_POST['quantity'], $_POST['id'], $newpdo);
		updateIndex($_POST['active'], $_POST['id'], $newpdo);
		updateIndexShop($_POST['active'], $_POST['id'], $newpdo);
		updateActive($_POST['active'], $_POST['id'], $newpdo);
		updateActiveShop($_POST['active'], $_POST['id'], $newpdo);
		updateCondition($_POST['condition'], $_POST['id'], $newpdo);
		updateConditionShop($_POST['condition'], $_POST['id'], $newpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja nazwy nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try
	{
		updateManufacturer($_POST['author'], $_POST['id'], $newpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja producenta nie powiodła się: ' . $e->getMessage();
		exit();
	}
	try
	{
		updatePrice($_POST['id'], $_POST['nominalPriceNew'], $newpdo);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Aktualizacja starej ceny nie powiodła się: ' . $e->getMessage();
		exit();
	}
	deleteCategory($_POST['id'], $newpdo);
	if(isset($_POST['categories']))
	{
		insertCategory($_POST['categories'], $_POST['id'], $newpdo);
	} else {echo'Nie znaleziono kategorii do zapisania!';
	}
	deleteWholeTag($_POST['id'], $newpdo);
	foreach ($_POST['tagText'] as $tagText){
		$row=checkIfTag($tagText, $newpdo);
		$checkedTagId=$row['id_tag'];
		if($checkedTagId!=0)
		{
			insertTag($checkedTagId, $_POST['id'], $newpdo);
		}
		else{
			createTag($checkedTagId, $tagText, $newpdo);
			$row=checkIfTag($tagText, $newpdo);
			$checkedTagId=$row['id_tag'];
			insertTag($checkedTagId, $_POST['id'], $newpdo);
		}
	}
	try
	{
	$row=confirmation($_POST['id'], $newpdo);
	$id= $row['id_product'];
	$quantity= $row['quantity'];
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'obie'){
		updateProductName($_POST['text'], $_POST['id'], $oldpdo);
		updateDesc($_POST['description'], $_POST['id'], $oldpdo);
		updateDesc_short($_POST['description_short'], $_POST['id'], $oldpdo);
		updateLink($_POST['link'], $_POST['id'], $oldpdo);
		updateMeta_title($_POST['meta_title'], $_POST['id'], $oldpdo);
		updateMeta_desc($_POST['meta_description'], $_POST['id'], $oldpdo);
		updateQuantity($_POST['quantity'], $_POST['id'], $oldpdo);
		updateIndex($_POST['active'], $_POST['id'], $oldpdo);
		updateIndexShop($_POST['active'], $_POST['id'], $oldpdo);
		updateActive($_POST['active'], $_POST['id'], $oldpdo);
		updateActiveShop($_POST['active'], $_POST['id'], $oldpdo);
		updateCondition($_POST['condition'], $_POST['id'], $oldpdo);
		updateConditionShop($_POST['condition'], $_POST['id'], $oldpdo);
		updateManufacturer($_POST['author'], $_POST['id'], $oldpdo);
		updatePrice($_POST['id'], $_POST['nominalPriceOld'], $oldpdo);
		deleteCategory($_POST['id'], $oldpdo);
		if(isset($_POST['categories']))
		{
			insertDifferentCategory($_POST['categories'], $_POST['id'], $oldpdo);
		} else {echo'Nie znaleziono kategorii do zapisania!';}
		deleteWholeTag($_POST['id'], $oldpdo);
		foreach ($_POST['tagText'] as $tagText){
			$row=checkIfTag($tagText, $oldpdo);
			$checkedTagId=$row['id_tag'];
			if($checkedTagId!=0)
			{
				insertTag($checkedTagId, $_POST['id'], $oldpdo);
			}
			else{
				createTag($checkedTagId, $tagText, $oldpdo);
				$row=checkIfTag($tagText, $oldpdo);
				$checkedTagId=$row['id_tag'];
				insertTag($checkedTagId, $_POST['id'], $oldpdo);
			}
		}
	}	
	include 'confirmation.html.php';
	exit();
}
if (isset($_GET['action'])and $_GET['action']== 'Zmiana obu przez nowy panel')
{
	try
	{
	$row=selectProduct($_GET['id'], $newpdo);
	$baza='- informacje z nowego panelu.';
	$text= $row['name'];
	$quantity= $row['quantity'];
	$id= $row['id_product'];
	$priceNew=$row['price'];
	$button= 'Aktualizuj produkt w obu bazach';
	$editForm='?editformBoth';

	$oldPrice=getPrice($_GET['id'], $oldpdo);
	$oldPrice2=$oldPrice['price'];
	}
	catch (PODException $e)
	{
		echo 'Błąd przy pobieraniu informacji o produkcie: ' . $e->getMessage();
		exit();
	}
	include 'form.html.php';
	exit();
	}
	if (isset($_GET['action'])and $_GET['action']== 'Zmiana obu przez stary panel')
{	
	try
	{
	$row=selectProduct($_GET['id'], $oldpdo);
	$baza='- informacje ze starego panelu.';
	$text= $row['name'];
	$quantity= $row['quantity'];
	$id= $row['id_product'];
	$oldPrice2=$row['price'];
	$button= 'Aktualizuj produkt w obu bazach';
	$editForm='?editformBoth';

	$newPrice=getPrice($_GET['id'], $newpdo);
	$priceNew=$newPrice['price'];
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
		$row=selectWholeDetails($_GET['id'],$newpdo);
		$baza='- informacje z nowego panelu.';
		$text= $row['name'];
		$quantity= $row['quantity'];
		$id= $row['id_product'];
		$description_short=$row['description_short'];
		$description=$row['description'];
		$link=$row['link_rewrite'];
		$meta_title=$row['meta_title'];
		$meta_description=$row['meta_description'];
		$condition=$row['condition'];
		$active=$row['active'];
		$priceNew=$row['price'];
		$oldPrice=getPrice($_GET['id'], $newpdo);$oldPrice2=$oldPrice['price'];
		$button= 'Aktualizuj produkt w nowej bazie';
		$editForm='?editcompleteformnew';
		$completeButton="Uaktualnij produkt (NB)";

		$row=selectManufacturer($_GET['id'], $newpdo);
		$authorid =$row['id_manufacturer'];
	
		foreach ($result as $manufactList) //$authors[] zadeklarowane w line 46
		{
			$authors[]=array('id'=> $manufactList['id_manufacturer'], 'name'=> $manufactList['name']);
		}
		try
		{
			$t=selectCategory($id, $newpdo);
		}
		catch (PODException $e)
		{
			echo 'Błąd przy pobieraniu listy wybranych kategorii: ' . $e->getMessage();
			exit();
		}
		
		foreach ($t as $row)
		{
			$selectedCats[]=$row['id_category'];
			$selectedNames[]=array(
				'name'=>$row['meta_title']);
		}
		try
		{
			$result =selectWholeCategory($newpdo);
		}
		catch (PODException $e)
		{
			echo 'Błąd przy pobieraniu listy kategorii: ' . $e->getMessage();
			exit();
		}
		foreach ($result as $row)
		{
			$cat[]= array(
				'id'=>$row['id_category'],
				'name'=>$row['meta_title'],
				'selected'=> in_array($row['id_category'], $selectedCats));
		}
		try
		{
			$tag=selectTag($id, $newpdo);
		}
		catch (PODException $e)
		{
			echo 'Błąd przy pobieraniu listy tagów wybranego produktu: ' . $e->getMessage();
			exit();
		}
		foreach ($tag as $row)
		{
			$productTag[]=array(
				'id'=>$row['id_tag'],
				'name'=>$row['name']);
		}
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
		$row=selectWholeDetailsOld($_GET['id'],$oldpdo);
		$baza='- informacje ze starego panelu.';
		$text= $row['name'];
		$quantity= $row['quantity'];
		$id= $row['id_product'];
		$description_short=$row['description_short'];
		$description=$row['description'];
		$link=$row['link_rewrite'];
		$meta_title=$row['meta_title'];
		$meta_description=$row['meta_description'];
		$condition=$row['condition'];
		$active=$row['active'];
		$indexed=$row['indexed'];
		$oldPrice2=$row['price'];
		$newPrice=getPrice($_GET['id'], $newpdo);$priceNew=$newPrice['price'];
		$button= 'Aktualizuj produkt w starej bazie';
		$editForm='?editcompleteformold';
		$completeButton="Uaktualnij produkt (SB)";

		$row=selectManufacturer($_GET['id'], $oldpdo);
		$authorid =$row['id_manufacturer'];
		
		foreach ($result as $manufactList) //$authors[] zadeklarowane w line 46
		{
			$authors[]=array('id'=> $manufactList['id_manufacturer'], 'name'=> $manufactList['name']);
		}
		try
		{
			$s=selectCategoryOld($id, $oldpdo);
		}
		catch (PODException $e)
		{
			echo 'Błąd przy pobieraniu listy wybranych kategorii: ' . $e->getMessage();
			exit();
		}

		foreach ($s as $row)
		{
			$selectedCat[]=$row['id_category'];
			$selectedNames[]= array(
				'name'=>$row['meta_title']);
		}
		try
		{
			$result =selectWholeCategoryOld($oldpdo);
		}
		catch (PODException $e)
		{
			echo 'Błąd przy pobieraniu listy kategorii: ' . $e->getMessage();
			exit();
		}
		foreach ($result as $row)
		{
			$cat[]= array(
				'id'=>$row['id_category'],
				'name'=>$row['meta_title'],
				'selected'=> in_array($row['id_category'], $selectedCat));
		}
		try
		{
			$tag=selectTag($id, $oldpdo);
		}
		catch (PODException $e)
		{
			echo 'Błąd przy pobieraniu listy tagów wybranego produktu: ' . $e->getMessage();
			exit();
		}
		foreach ($tag as $row)
		{
			$productTag[]=array(
				'id'=>$row['id_tag'],
				'name'=>$row['name']);
		}
		include 'completeForm.html.php';
		exit();
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie kompletnych danych ze starej bazy nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if (isset($_GET['action'])and $_GET['action']== 'Uaktualnij ilości dla całego zamówienia')
		try
	{
		selectOrderQuantity($_GET['id_number'], $oldpdo, $newpdo, 0);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie ilości w zamówieniu nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if (isset($_GET['action'])and $_GET['action']== 'Uaktualnij ilości w całym zamówieniu')
		try
	{
		selectOrderQuantity($_GET['id_number'], $newpdo, $oldpdo, 1);
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie ilości w zamówieniu nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if (isset($_GET['action'])and $_GET['action']== 'Wyrównaj ilość w starej bazie')
	try
	{
		updateQuantity($_GET['quantity'], $_GET['id'], $oldpdo);
		try
	{
	$row=confirmation($_GET['id'], $oldpdo);
	$id= $row['id_product'];
	$quantity= $row['quantity'];
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
		exit();
	}
		include 'confirmation2.html.php';
	}
	catch (PODException $e)
	{
		echo 'Błąd przy wyrównywaniu ilości - nowy->stary panel: ' . $e->getMessage();
		exit();
	}
	if (isset($_GET['action'])and $_GET['action']== 'Wyrównaj ilość w nowej bazie')
	try
	{
		updateQuantity($_GET['quantity'], $_GET['id'], $newpdo);
		try
	{
	$row=confirmation($_GET['id'], $newpdo);
	$id= $row['id_product'];
	$quantity= $row['quantity'];
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie uaktualnionych danych nie powiodło się: ' . $e->getMessage();
		exit();
	}
		include 'confirmation.html.php';
	}
	catch (PODException $e)
	{
		echo 'Błąd przy pobieraniu wyrównywaniu ilości - nowy->stary panel: ' . $e->getMessage();
		exit();
	}
	if(isset($_GET['action'])and $_GET['action']=='search')
	{
		$select = 'SELECT ps_product_lang.id_product, ps_product_lang.name, ps_stock_available.quantity';
		$from = ' FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product = ps_stock_available.id_product';
		$newwhere = ' WHERE TRUE';
		$oldwhere = ' WHERE ps_product_lang.id_lang=3';
		$placeholders = array();
		if ($_GET['author'] !='')
		{
			$select = 'SELECT ps_product_lang.id_product, ps_product_lang.name, ps_stock_available.quantity, ps_product.id_manufacturer';
			$from = ' FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product = ps_stock_available.id_product
			INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product' ;
			$newwhere .=" AND id_manufacturer= :id_manufacturer";
			$oldwhere .=" AND id_manufacturer= :id_manufacturer";
			$placeholders[':id_manufacturer'] = $_GET['author'];
		}
		if ($_GET['category'] !=''){
			$from .=' INNER JOIN ps_category_product ON ps_product_lang.id_product= ps_category_product.id_product';
			$newwhere .=" AND id_category= :id_category";
			$oldwhere .=" AND id_category= :id_category";
			$placeholders[':id_category'] = $_GET['category'];
		}
		if ($_GET['idnr'] !='')
		{
			$newwhere .=" AND ps_product_lang.id_product LIKE :id_product";
			$oldwhere .=" AND ps_product_lang.id_product LIKE :id_product";
			$placeholders[':id_product'] =$_GET['idnr'];
		}
		if ($_GET['neworder'] !=''){
			$select = 'SELECT ps_product_lang.id_product, ps_product_lang.name, ps_stock_available.quantity, ps_order_detail.id_order, ps_order_detail.product_quantity, ps_product.price';
			$from = ' FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product = ps_stock_available.id_product
			INNER JOIN ps_order_detail ON ps_stock_available.id_product=ps_order_detail.product_id
			INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product';
			$newwhere =" AND id_order= :id_order";
			$placeholders[':id_order'] = $_GET['neworder'];
			try
			{
				$newsql= $select. $from. $newwhere;
				$s=$newpdo->prepare($newsql);
				$s->execute($placeholders);
			}
			catch (PDOException $e)
			{
				echo 'Błąd przy pobieraniu nowych produktów: ' . $e->getMessage();
				exit();
			}
			foreach ($s as $row)
			{
				$products[]=array('id'=>$row['id_product'], 'text'=>$row['name'], 'quantity'=>$row['quantity'], 'orderedQuantity'=>$row['product_quantity']);
			}
			$panel='Nowy panel: ';
			$stan='Na stanie (NP)';
			$stan2='Zamówione (NP)';
			$uaktualnij='Wyrównaj ilość w starej bazie';
			$edycja='Kompletna edycja w NP';
			$uaktualnij2='Uaktualnij ilości w całym zamówieniu';
			$uaktualnij3='Zmiana obu przez nowy panel';
			$uaktualnij4="neworder";
			include 'products2.html.php';
			exit();
		}
		if ($_GET['oldorder'] !=''){
			$select = 'SELECT ps_product_lang.id_product, ps_product_lang.name, ps_stock_available.quantity, ps_order_detail.id_order, ps_order_detail.product_quantity';
			$from = ' FROM ps_product_lang INNER JOIN ps_stock_available ON ps_product_lang.id_product = ps_stock_available.id_product
			INNER JOIN ps_order_detail ON ps_stock_available.id_product=ps_order_detail.product_id' ;
			$oldwhere =" WHERE ps_product_lang.id_lang=3 AND id_order= :id_order";
			$placeholders[':id_order'] = $_GET['oldorder'];
			try
			{
				$oldsql= $select. $from. $oldwhere;
				$s=$oldpdo->prepare($oldsql);
				$s->execute($placeholders);
			}
			catch (PDOException $e)
			{
				echo 'Błąd przy pobieraniu starych produktów: ' . $e->getMessage();
				exit();
			}
			foreach ($s as $row)
			{
				$products[]=array('id'=>$row['id_product'], 'text'=>$row['name'], 'quantity'=>$row['quantity'], 'orderedQuantity'=>$row['product_quantity']);
			}
			$panel='Stary panel: ';
			$stan='Na stanie (SP)';
			$stan2='Zamówione (SP)';
			$uaktualnij='Wyrównaj ilość w nowej bazie';
			$edycja='Kompletna edycja w SP';
			$uaktualnij2='Uaktualnij ilości dla całego zamówienia';
			$uaktualnij3='Zmiana obu przez stary panel';
			$uaktualnij4="oldorder";
			include 'products2.html.php';
			exit();
		}

		if ($_GET['text'] !='')
		{
			$newwhere .=" AND name LIKE :name ORDER BY ps_product_lang.id_product";
			$oldwhere .=" AND name LIKE :name";
			$placeholders[':name'] ='%'.$_GET['text'].'%';
		}
		try
		{
			$newsql= $select. $from. $newwhere;
			$s=$newpdo->prepare($newsql);
			$s->execute($placeholders);
			$oldsql= $select. $from. $oldwhere;
			$t=$oldpdo->prepare($oldsql);
			$t->execute($placeholders);
		}
		catch (PDOException $e)
		{
			echo 'Błąd przy pobieraniu produktów: ' . $e->getMessage();
			exit();
		}
		foreach ($s as $row)
		{
			$products[]=array('id'=>$row['id_product'], 'text'=>$row['name'], 'quantity'=>$row['quantity']);
		}
		foreach ($t as $rows)
		{
			$oldproducts[]=array('oldId'=>$rows['id_product'], 'oldText'=>$rows['name'], 'oldQuantity'=>$rows['quantity']);
		}
			include 'products.html.php';
			exit();
	}
	exit();
