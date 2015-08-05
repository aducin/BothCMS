<?php
ob_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/controllers/bootstrap.php';

if(isset($_GET['deleterow']))
{
	$list= new OgicomHelper($oldpdo);
	$result= $list->deleteMod($_GET['idMod']);
	unset($list);
	header('Location:.');
	exit();
}
session_start();
if(!isset($_SESSION['log'])){
	$login=$_POST['login'];
	$password=$_POST['password'];
}
if(isset($_POST['logout']))
{
	unset($_SESSION['log']);
	header('Location:index.php');
	exit();
}
if (!isset($_SESSION['log'])){
	$db=new db($newpdo);
	$dbResult= $db->selectUser($login, $password);
	$resNumb=$dbResult->rowCount();
	if($resNumb>0)
	{
		$finalResult=$dbResult->fetch(PDO::FETCH_ASSOC);
		$login=' Użytkownik: '.$finalResult['login'];
		$_SESSION['log']=1;
		$dbResult->closeCursor();
		echo'Witamy w systemie CMS obu paneli! '.$login;
	}
	if(!isset($_SESSION['log'])){
		header('Location:templates/signIn.html');
		exit();
	} 
	unset($db);
}
try
{
	$list= new OgicomHelper($oldpdo);
	$result= $list->selectWholeManufacturer();
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
	$result= $list->selectWholeCategory2();
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
$result= $list->selectModyfy();
foreach ($result as $mod)
{
	$mods[]= array('id'=>$mod['id_number'], 'nazwa'=>$mod['name'], 'data'=>$mod['date'], 'cena'=>$mod['price']);
}
unset($list);
include $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS/templates/searchForm.html.php';
if(isset($_POST['orders']))
{
	header('Location:controllers/index2.php');
	exit();
}

if(isset($_GET['changeTabOldAdd']))
{
	$oldTry= new OgicomProduct;
	$Query = $oldTry->checkIfTag($_POST['newtagText'], $oldpdo);
	$QueryResult = $Query->fetch();
	if($QueryResult[0]!=0){
		$Query2 = $oldTry->insertTag($QueryResult[0], $_POST['id'], $oldpdo);
	} else {
		$Query2 = $oldTry->createTag($QueryResult[0], $_POST['newtagText'], $oldpdo);
		$Query3 = $oldTry->checkIfTag($_POST['newtagText'], $oldpdo);
		$QueryResult = $Query3->fetch();
		$Query4 = $oldTry->insertTag($QueryResult[0], $_POST['id'], $oldpdo);
	}
	include 'templates/confirmation.html.php';
}
if(isset($_GET['changeTabNewAdd']))
{
	$oldTry= new LinuxPlProduct;
	$Query = $oldTry->checkIfTag($_POST['newtagText'], $newpdo);
	$QueryResult = $Query->fetch();
	if($QueryResult[0]!=0){
		$Query2 = $oldTry->insertTag($QueryResult[0], $_POST['id'], $newpdo);
	} else {
		$Query2 = $oldTry->createTag($QueryResult[0], $_POST['newtagText'], $newpdo);
		$Query3 = $oldTry->checkIfTag($_POST['newtagText'], $newpdo);
		$QueryResult = $Query3->fetch();
		$Query4 = $oldTry->insertTag($QueryResult[0], $_POST['id'], $newpdo);
	}
	include 'templates/confirmation.html.php';
}
if(isset($_GET['changeTabNewCut']))
{
	$Try= new LinuxPlProduct;
	$Query = $Try->checkIfTag($_POST['textCut'], $newpdo);
	$QueryResult = $Query->fetch();
	if($QueryResult[0]!=0){
		$Query2 = $Try->deleteTag($QueryResult[0], $_POST['id'], $newpdo);
	} else{
		echo'<b>'.'Chciałeś usunąć TAG, którego nie ma w bazie, pustaku...'.'</b>';}	
	}
	if(isset($_GET['changeTabOldCut']))
	{
		$Try= new OgicomProduct;
		$Query = $Try->checkIfTag($_POST['textCut'], $oldpdo);
		$QueryResult = $Query->fetch();
		if($QueryResult[0]!=0){
			$Query2 = $Try->deleteTag($QueryResult[0], $_POST['id'], $oldpdo);
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
				$newTry= new LinuxPlProduct;
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
				$oldTry= new OgicomProduct;
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
			include 'templates/confirmation.html.php';
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
				$oldTry= new OgicomProduct;
				if (isset($_POST['change'])and $_POST['change']== "nameChange"){
					echo'jestem';
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
		if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'both'){
			$newTry= new LinuxPlProduct;
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

		include 'templates/confirmation.html.php';
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
			if (isset($_POST['change'])and $_POST['change']== "nameChange"){
				$oldTry= new OgicomProduct;
				$oldQuery = $oldTry->insertModyfy($_POST['id'], $_POST['text'], $oldpdo);
			}
			$newTry= new LinuxPlProduct;
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
	if (isset($_POST['howManyBases'])and $_POST['howManyBases']== 'both'){
		$oldTry= new OgicomProduct;
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
include 'templates/confirmation.html.php';
exit();
}
if(isset($_GET['shortEdition'])){
	try
	{
		$newTry= new LinuxPlProduct;
		$Query = $newTry->getProductQuery($_GET['id'], $newpdo);
		$QueryResult = $Query->fetch();
		$Query3= $newTry->getReduction($_GET['id'], $newpdo);
		$QueryResult3 = $Query3->fetch();
		$oldTry= new OgicomProduct;
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
	include 'templates/form.html.php';
	exit();
}
if (isset($_GET['fullEditionN']))
	try
	{
		$oldTry= new LinuxPlProduct;
		$Query = $oldTry->getWholeDetailsQuery($_GET['id'], $newpdo);
		$QueryResult = $Query->fetch();
		$Query1 = $oldTry->getReduction($_GET['id'], $newpdo);
		$Query3 = $Query1->fetch();
		$Query6 = $oldTry->selectManufacturer($_GET['id'], $newpdo);
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
		$newTry= new OgicomProduct;
		$Query2 = $newTry->getProductQuery($_GET['id'], $oldpdo);
		$QueryResult2 = $Query2->fetch();
		$Query4 = $newTry->getReduction($_GET['id'], $oldpdo);
		$Query5 = $Query4->fetch();
		$baza='- informacje z nowego panelu.';
		$editForm='?editcompleteformnew';

		include 'templates/completeForm.html.php';
		exit();
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie kompletnych danych ze starej bazy nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if (isset($_GET['fullEditionO']))
		try
	{
		$oldTry= new OgicomProduct;
		$Query = $oldTry->getWholeDetailsQuery($_GET['id'], $oldpdo);
		$QueryResult = $Query->fetch();
		$Query1 = $oldTry->getReduction($_GET['id'], $oldpdo);
		$Query3 = $Query1->fetch();
		$Query6 = $oldTry->selectManufacturer($_GET['id'], $oldpdo);
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
		$newTry= new LinuxPlProduct;
		$Query2 = $newTry->getProductQuery($_GET['id'], $newpdo);
		$QueryResult2 = $Query2->fetch();
		$Query4 = $newTry->getReduction($_GET['id'], $newpdo);
		$Query5 = $Query4->fetch();
		$baza='- informacje ze starego panelu.';
		$editForm='?editcompleteformold';

		include 'templates/completeForm.html.php';
		exit();
	}
	catch (PDOExceptioon $e)
	{
		echo 'Pobranie kompletnych danych ze starej bazy nie powiodło się: ' . $e->getMessage();
		exit();
	}
	if(isset($_GET['action'])and $_GET['action']=='idsearch')
		{
		$newTry= new LinuxPlProduct;
		$newQuery = $newTry->getProductQuery($_GET['idnr'], $newpdo);
		$newQueryResult = $newQuery->fetch();
		$newQuery2= $newTry->getReduction($_GET['idnr'], $newpdo);
		$newQueryResult2 = $newQuery2->fetch();
		$oldTry= new OgicomProduct;
		$oldQuery = $oldTry->getProductQuery($_GET['idnr'], $oldpdo);
		$oldQueryResult = $oldQuery->fetch();
		$oldQuery2= $oldTry->getReduction($_GET['idnr'], $oldpdo);
		$oldQueryResult2 = $oldQuery2->fetch();
		include 'templates/products.html.php';
		}
		if(isset($_GET['action'])and $_GET['action']=='search')
		{
			if ($_GET['text'] !='' AND $_GET['category'] =='' AND $_GET['author'] =='')
			{
				$newTry= new LinuxPlProduct;
				$newQuery = $newTry->getLoopTextQuery(' WHERE ps_product_lang.name LIKE :name ORDER BY ps_product_lang.id_product','%'.$_GET['text'].'%', $newpdo);
				foreach ($newQuery as $newQuery2)
				{
					$newQuery3[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>$newQuery2['price']);
				}
			}
			if ($_GET['author'] !='' AND $_GET['category'] =='' AND $_GET['text'] =='')
			{
				$newTry= new LinuxPlProduct;
				$newQuery = $newTry->getLoopManufacturerQuery(" WHERE id_manufacturer= :id_manufacturer",$_GET['author'], $newpdo);
				foreach ($newQuery as $newQuery2)
				{
					$newQuery3[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>$newQuery2['price']);
				}
			}
			if ($_GET['text'] !='' AND $_GET['author'] !=''AND $_GET['category'] =='')
			{
				$newTry= new LinuxPlProduct;
				$newQuery = $newTry->getLoopBothQuery(" WHERE id_manufacturer= :id_manufacturer AND ps_product_lang.name LIKE :name ORDER BY ps_product_lang.id_product",'%'.$_GET['text'].'%',$_GET['author'], $newpdo);
				foreach ($newQuery as $newQuery2)
				{
					$newQuery3[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>$newQuery2['price']);
				}
			}
			if ($_GET['category'] !='' AND$_GET['text'] =='' AND $_GET['author'] =='')
			{
				$newTry= new LinuxPlProduct;
				$newQuery = $newTry->getLoopCategoryQuery(' WHERE id_category= :id_category',$_GET['category'], $newpdo);
				foreach ($newQuery as $newQuery2)
				{
					$newQuery3[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>$newQuery2['price']);
				}
			}
			if ($_GET['category'] !='' AND$_GET['text'] !='' AND $_GET['author'] =='')
			{
				$newTry= new LinuxPlProduct;
				$newQuery = $newTry->getLoopBoth2Query(' WHERE id_category= :id_category AND ps_product_lang.name LIKE :name ORDER BY ps_product_lang.id_product','%'.$_GET['text'].'%',$_GET['category'], $newpdo);
				foreach ($newQuery as $newQuery2)
				{
					$newQuery3[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>$newQuery2['price']);
				}
			}
			if ($_GET['category'] !='' AND$_GET['text'] =='' AND $_GET['author'] !='')
			{
				$newTry= new LinuxPlProduct;
				$newQuery = $newTry->getLoopBoth3Query(' WHERE id_category= :id_category AND id_manufacturer= :id_manufacturer', $_GET['category'], $_GET['author'], $newpdo);
				foreach ($newQuery as $newQuery2)
				{
					$newQuery3[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>$newQuery2['price']);
				}
			}
			if ($_GET['category'] !='' AND$_GET['text'] !='' AND $_GET['author'] !='')
			{
				$newTry= new LinuxPlProduct;
				$newQuery = $newTry->getLoopTripleQuery(' WHERE id_category LIKE :id_category AND id_manufacturer LIKE :id_manufacturer AND ps_product_lang.name LIKE :name GROUP BY id_category, id_product','%'.$_GET['text'].'%','%','%'.$_GET['category'].'%'.$_GET['author'].'%', $newpdo);
				foreach ($newQuery as $newQuery2)
				{
					$newQuery3[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>$newQuery2['price']);

				}
			}
			if ($_GET['text'] =='' AND $_GET['category'] =='' AND $_GET['author'] ==''){
				echo'<b>Nie chcesz chyba szukać wszystkich wyników w bazie...?</b><br>Zaznacz chociaż 1 kryterium wyszukiwania!';
				exit();
			}
			include 'templates/products.html.php';
		}
	ob_end_flush();
	exit();
