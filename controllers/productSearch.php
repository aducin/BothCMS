<?php

session_start();
if(isset($_POST['logout'])){
	unset($_SESSION['log']);
	header('Location:templates/signIn.html');
}
if(!isset($_SESSION['log'])){
	$userLogin=$_POST['login'];
	$userPassword=$_POST['password'];
	$dbHandlerLinuxPl= new DBHandler($firstHost, $firstLogin, $firstPassword);
	$dbResult= $dbHandlerLinuxPl->getUserData($userLogin, $userPassword);
	$resNumb=$dbResult->rowCount();

	if($resNumb>0){
		$finalResult=$dbResult->fetch(PDO::FETCH_ASSOC);
		$_SESSION['log']=1;
		$dbResult->closeCursor();
	}
}
if($_SESSION['log']==0){
	header('Location:templates/signIn.html');
}
unset($db);

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

if(isset($_GET['deleterow'])){
	$helper= new OgicomHelper($ogicomHandler);
	$result= $helper->deleteModyfied($_GET['idMod']);
	executeController('product');
	unset($helper);
}elseif(isset($_GET['editformBoth'])){
	if ($_POST['text']==''){
		$error='Brak aktualnego wpisu: nazwa produktu!';
	}elseif ($_POST['quantity']==''){
		$error='Brak aktualnego wpisu: nowa liczba produktu!';
	}else try{
		$product1= new LinuxPlProduct($linuxPlHandler);
		$newQuery = $product1->updateBoth($_POST['id'], $_POST['nominalPriceNew'], $_POST['text'], $_POST['quantity']);
		$outputOrderOrProduct1 = $product1->confirmation($_POST['id']);
	}catch (PDOExceptioon $e){
		$error='Aktualizacja danych nie powiodła się: ' . $e->getMessage();
	}
	if(!isset($error)){
		$product2= new OgicomProduct($ogicomHandler);
		$oldQuery = $product2->updateBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity']);
		$secondConfirmation = $product2->confirmation($_POST['id']);
	}
}elseif(isset($_GET['editcompleteformnew'])OR(isset($_GET['editcompleteformold']))){
	if(isset($_GET['editcompleteformnew'])){
		$product1= new LinuxPlProduct($linuxPlHandler);
		$product2= new OgicomProduct($ogicomHandler);
		if (isset($_POST['change'])and $_POST['change']== "nameChange"){
			$oldQuery = $product2->insertModyfy($_POST['id'], $_POST['text']);
		}
	}elseif(isset($_GET['editcompleteformold'])){
		$product2= new LinuxPlProduct($linuxPlHandler);
		$product1= new OgicomProduct($ogicomHandler);
		$priceNew=($_POST['nominalPriceNew']);
		$priceOld=($_POST['nominalPriceOld']);
		$_POST['nominalPriceNew']=$priceOld;
		$_POST['nominalPriceOld']=$priceNew;
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
		$Query = $product1->updateDetailedBoth($_POST['id'], $_POST['nominalPriceNew'], $_POST['text'], $_POST['quantity'], $_POST['description'], $_POST['description_short'], $_POST['meta_title'], $_POST['meta_description'], str_replace(" ","-", $_POST['link']), $_POST['condition'], $_POST['active']);
		$sth=str_replace(" ", "-", $_POST['link']);
		$Query = $product1->updateManufacturer($_POST['author'], $_POST['id']);
		$Query = $product1->deleteCategory($_POST['id']);
		if(isset($_POST['categories'])){
			$Query = $product1->insertCategory($_POST['categories'], $_POST['id']);
		}else{
			$error='Nie znaleziono kategorii do zapisania!';
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
			$outputOrderOrProduct1 = $product1->confirmation($_POST['id']);
			$secondConfirmation = $product2->confirmation($_POST['id']);
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
				$secondConfirmation = $product2->confirmation($_POST['id']);
				$Query = $product2->deleteCategory($_POST['id']);
				if(isset($_POST['categories'])){
					$Query = $product2->insertDifferentCategory($_POST['categories'], $_POST['id']);	
				}else{
					$error='Nie znaleziono kategorii do zapisania!';
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
}elseif(isset($_GET['shortEdition'])){
	try{
		$product1= new LinuxPlProduct($linuxPlHandler);
		$bothEdit = $product1->getProductDetailedData($_GET['id']);
		if($product1->getReductionData($_GET['id'])!=0){
			$bothEdit['countReduction']=$product1->countReduction($bothEdit['price'], $product1->getReductionData($_GET['id']));
		}
		$product2= new OgicomProduct($ogicomHandler);
		$bothEdit['price2']= $product2->getPrice($_GET['id']);
		if($product2->getReductionData($_GET['id'])!=0){
			$bothEdit['countReduction2']=$product2->countReduction($bothEdit['price2'], $product2->getReductionData($_GET['id']));
		}
		$outputProduct1=1;
	}catch (PODException $e){
		$error='Błąd przy pobieraniu informacji o produkcie: ' . $e->getMessage();
	}
}elseif(isset($_GET['fullEditionN'])OR(isset($_GET['fullEditionO']))){
	if (isset($_GET['fullEditionN'])){
		$product1= new LinuxPlProduct($linuxPlHandler);
		$product2= new OgicomProduct($ogicomHandler);
		$editForm='?editcompleteformnew';
	}elseif(isset($_GET['fullEditionO'])){
		$product1= new OgicomProduct($ogicomHandler);
		$product2= new LinuxPlProduct($linuxPlHandler);
		$editForm='?editcompleteformold';
	}
	$Query = $product1->getWholeDetailsQuery($_GET['id']);
	$QueryResult = $Query->fetch();
	$reduction1= $product1->getReductionData($_GET['id']);
	$manufacturer = $product1->selectManufacturer($_GET['id']);
	$category = $product1->getCategory($_GET['id']);
	$QueryResult['reduction']=$product1->getReductionData($_GET['id']);
	$QueryResult['manufacturer'] = $product1->selectManufacturer($_GET['id']);
	$QueryResult['secondPrice'] = $product2->getPrice($_GET['id']);
	$QueryResult['reduction2']= $product2->getReductionData($_GET['id']);
	foreach ($category as $category1){
		$selCategories[]=array('name'=>$category1['meta_title']);
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
	$indexArray = array 
		('1'=>array('indexed'=>'0','activeness'=>'Nieaktywny'),
		'2'=>array('indexed'=>'1','activeness'=>'Aktywny'));
	$condArray = array (
				'1'=>array('condition'=>'new','value'=>'Nowy'),
				'2'=>array('condition'=>'used','value'=>'Używany'),
				'3'=>array('condition'=>'refurbished','value'=>'Odnowiony'));
	$secondPrice = $product2->getPrice($_GET['id']);
	$reduction2= $product2->getReductionData($_GET['id']);
	$outputProduct4=1;
}elseif(isset($_GET['action'])AND(isset($_GET['idnr']))){

	$product1= new LinuxPlProduct($linuxPlHandler);
	$newQueryResult = $product1->getProductDetailedData($_GET['idnr']);
	if($newQueryResult['name']==''){
		$error='Brak produktu o podanym ID.';
	}else{
		if($product1->getReductionData($_GET['idnr'])!=''){
			$newQueryResult['reduct']=$product1->countRealPrice($newQueryResult['price'],$product1->getReductionData($_GET['idnr']));
		}
		$newQueryResult['price']=number_format($newQueryResult['price'], 2,'.','').'zł';
		$product2= new OgicomProduct($ogicomHandler);
		$oldQueryResult = $product2->getProductDetailedData($_GET['idnr']);
		if($product2->getReductionData($_GET['idnr'])!=''){
			$oldQueryResult['reduct']=$product2->countRealPrice($oldQueryResult['price'],$product2->getReductionData($_GET['idnr']));
		}
		$oldQueryResult['price']=number_format($oldQueryResult['price'], 2,'.','').'zł';
		$imageNumber= $product2->image($_GET['idnr']);
		$outputProduct2=1;
	}
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
			$product1= new LinuxPlProduct($linuxPlHandler);
			$newQuery = $product1->getProductData($implodeSelect);
			$product2= new OgicomProduct($ogicomHandler);
			foreach ($newQuery as $newQuery2){
				$imageNumber= $product2->image($newQuery2['id_product']);
				$reduction=$product1->getReductionData($newQuery2['id_product']);
				$oldQuery = $product2->getProductQuery($newQuery2['id_product']);
				$oldQuery2 = $oldQuery->fetch();
				$reduction2=$product2->getReductionData($oldQuery2['id_product']);
				if(($oldQuery2['name']==$newQuery2['name'])AND($oldQuery2['quantity']==$newQuery2['quantity'])){
					$queryResult=array('confirmation'=>'Zgodność ilości i nazw produktu nr '.$oldQuery2['id_product'], 'coherence'=>1);
				}elseif(($oldQuery2['name']==$newQuery2['name'])AND($oldQuery2['quantity']!=$newQuery2['quantity'])){
					$queryResult="Ilość produktu ".$oldQuery2['id_product']." w starym panelu to: ".$oldQuery2['quantity'];
				}elseif(($oldQuery2['name']!=$newQuery2['name'])AND($oldQuery2['quantity']==$newQuery2['quantity'])){
					$queryResult="Nazwa produktu ".$oldQuery2['id_product']." w starej bazie to: ".$oldQuery2['name'];
				}elseif(($oldQuery2['name']!=$newQuery2['name'])AND($oldQuery2['quantity']!=$newQuery2['quantity'])){
					$queryResult="Podwójna niezgodność - (SB): ".$oldQuery2['name'].", a ilość to: ".$oldQuery2['quantity'];
				}
				if(($reduction!=0)AND($reduction2!=0)){
					$searchResult[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'priceRed'=>($product1->countRealPrice($newQuery2['price'], $reduction)), 'result'=>$queryResult, 'priceRed2'=>($product2->countRealPrice($oldQuery2['price'], $reduction2)), 'priceResult'=>1, 'imgNumber'=>$imageNumber);
				}elseif(($reduction==0)AND($reduction2!=0)){
					$searchResult[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>number_format($newQuery2['price'], 2,'.','').'zł', 'result'=>$queryResult, 'priceRed2'=>($product2->countRealPrice($oldQuery2['price'], $reduction2)),'priceResult'=>1, 'imgNumber'=>$imageNumber);
				}elseif(($reduction!=0)AND($reduction2==0)){
					$searchResult[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'priceRed'=>($product1->countRealPrice($newQuery2['price'], $reduction)), 'result'=>$queryResult, 'price2'=>number_format($oldQuery2['price'], 2,'.','').'zł', 'priceResult'=>1, 'imgNumber'=>$imageNumber);
				}else{
					$searchResult[]=array('id'=>$newQuery2['id_product'], 'name'=>$newQuery2['name'], 'quantity'=>$newQuery2['quantity'], 'price'=>number_format($newQuery2['price'], 2,'.','').'zł', 'result'=>$queryResult, 'price2'=>number_format($oldQuery2['price'], 2,'.','').'zł', 'imgNumber'=>$imageNumber);
				}
				$outputProduct3=1;
			}
			if(!isset($searchResult)){
				$error='W bazie nie znaleziono produktów spełniających podane kryteria!';
			}else{
				$phrase=($_GET['text']);
			}	
		}
	}

	$finalOutput='product';
	require_once $root_dir.'/controllers/output.php'; 
