<?php 

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
	$output = $twig->render('/orderSearch.html');
	echo $output;
} catch (PDOException $e){
	$error='Nie udało się wyświetlić listy wyszukiwania produktów: ' . $e->getMessage();
}

if(isset($_POST['sendMessage'])){
	$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
	$customerData= $order1->getOrderCustomerData($_POST['customerNumber']);
	require $mail_dir.'/voucherMail.html';
	exit();
}
if(isset($_GET['action'])AND(($_GET['action'])=='orderSearch')){
	if(($_GET['neworder']!='')OR($_GET['oldorder']!='')){
		if ($_GET['neworder'] !=''){
			$order2= new LinuxPlOrder($firstHost, $firstLogin, $firstPassword);
			$query = $order2->getQuery($_GET['neworder']);
			$product= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
			$variables=array('onstock'=>'Na stanie (NP)', 'ordered'=>'Zamówione (NP)', 'button1'=>'Kompletna edycja w NP', 'button2'=>'Wyrównaj ilość w starej bazie', 'button3'=>'Zmiana obu przez nowy panel', 'button4'=>'Uaktualnij ilości w starej bazie', 'form'=>'fullEditionN', 'action'=>'BPSQO', 'orderNr'=>$_GET['neworder']);
		}elseif ($_GET['oldorder'] !=''){
			$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
			$query = $order1->getQuery($_GET['oldorder']);
			$product= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
			$variables=array('onstock'=>'Na stanie (SP)', 'ordered'=>'Zamówione (SP)', 'button1'=>'Kompletna edycja w SP', 'button2'=>'Wyrównaj ilość w nowej bazie', 'button3'=>'Zmiana obu przez stary panel', 'button4'=>'Uaktualnij ilości w nowej bazie','form'=>'fullEditionO', 'action'=>'BPSQN', 'orderNr'=>$_GET['oldorder']);
		}
		foreach ($query as $sOrder){
			$otherQuery = $product->getProductQuery($sOrder['product_id']);
			$otherQuery2 = $otherQuery->fetch();
			if(($otherQuery2['name']==$sOrder['name'])AND($otherQuery2['quantity']==$sOrder['quantity'])){
				$queryResult="Zgodność ilości i nazw produktu nr ".$otherQuery2['id_product'];
			}elseif(($otherQuery2['name']==$sOrder['name'])AND($otherQuery2['quantity']!=$sOrder['quantity'])){
				$queryResult="Ilość produktu ".$otherQuery2['id_product']." w starym panelu to: ".$otherQuery2['quantity'];
			}elseif(($otherQuery2['name']!=$sOrder['name'])AND($otherQuery2['quantity']==$sOrder['quantity'])){
				$queryResult="Nazwa produktu ".$otherQuery2['id_product']." w starej bazie to: ".$otherQuery2['name'];
			}elseif(($otherQuery2['name']!=$sOrder['name'])AND($otherQuery2['quantity']!=$sOrder['quantity'])){
				$queryResult="Podwójna niezgodność - (SB): ".$otherQuery2['name'].", a ilość to: ".$oldQuery2['quantity'];
			}
			$this[]=array('id'=>$sOrder['product_id'], 'name'=>$sOrder['name'], 'onStock'=>$sOrder['product_quantity'], 'quantity'=>$sOrder['quantity'], 'nameResult'=>$queryResult);
		}
		if(!isset($otherQuery2)){
			$error='W bazie danych nie ma zamówienia o podanym numerze!';
		}else{
			try{
				$output = $twig->render('/orderSearchResult.html', array(
					'result' => $this,
					'variables'=>$variables,
					));
				echo $output;
			} catch (PDOException $e){
				$error='Nie udało się wyświetlić listy wyszukiwania produktów: ' . $e->getMessage();
			}
		}
	}
	if(isset($_GET['orderVoucher'])OR($_GET['notification'])OR($_GET['detailorder'])){	
		if ($_GET['orderVoucher'] !=''){
			$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
			$orderSearch = $order1->checkIfVoucherDue($_GET['orderVoucher']);
			$totalProducts= $orderSearch['total_products'];
			if($totalProducts<50){
				$error='Kwota zamówienia wynosi '.$totalProducts.'zł i jest zbyt mała, aby przyznać kolejny kupon.';
			}else{
				$customerData= $order1->getOrderCustomerData($orderSearch['id_customer']);
				$customer= new LinuxPlCustomer($firstHost, $firstLogin, $firstPassword);
				$customerSearch = $customer->checkIfClientExists($customerData['email']);
				$voucherHistory= $order1->getVoucherNumber($orderSearch['id_customer']);
				$ordNumb=1;
				foreach ($voucherHistory as $custOrder){
					$custOrders[]= array('id'=>$custOrder['id_order'], 'reference'=>$custOrder['reference'], 'total'=>$custOrder['total_products'], 'shipping'=>$custOrder['total_shipping'], 'date'=>$custOrder['date_add'], 'orderNumber'=>$ordNumb++);
				}
				require $root_dir.'/templates/voucherSearch.html';
			}
		}elseif ($_GET['notification'] !=''){
			if(isset($_GET['send'])&&$_GET['send']=='modele'){
				$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
				$notification = $order1->sendNotification($_GET['notification']);
				$notificationresult = $notification->fetch();
				require $root_dir.'/templates/orders.html';
			}
			if(isset($_GET['send'])&&$_GET['send']=='ad9bis'){
				$order2= new LinuxPlOrder($firstHost, $firstLogin, $secondPassword);
				$notification = $order2->sendNotification($_GET['notification']);
				$notificationresult = $notification->fetch();
				require $root_dir.'/templates/orders.html';
			}
		}elseif ($_GET['detailorder'] !=''){
			$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
			$details = $order1->getQueryDetails($_GET['detailorder']);
			$detailsCount=$order1->getCount($_GET['detailorder']);
			$detailsCountresult = $detailsCount->fetch();
			$count = $detailsCountresult[0];
			foreach ($details as $sDetail){
				$detail2[]=array('id'=>$sDetail['product_id'], 'name'=>$sDetail['name'], 'price'=>$sDetail['product_price'], 'reduction'=>$sDetail['reduction_amount'], 'quantity'=>$sDetail['product_quantity'], 'total'=>$sDetail['total_price_tax_incl'], 'productSum'=>$sDetail['total_products'], 'totalPaid'=>$sDetail['total_paid'], 'mail'=>$sDetail['email'], 'first'=>$sDetail['firstname'], 'last'=>$sDetail['lastname'], 'reference'=>$sDetail['reference'], 'payment'=>$sDetail['payment']);
				require $root_dir.'/templates/orders.html';
			}
		}
	}elseif(isset($_GET['shipmentNumber'])){
		require $mail_dir.'/shipmentMail.html';
		exit();
	}
}elseif(isset($_GET['shortEdition'])){
	try{
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$Query = $product1->getProductQuery($_GET['id']);
		$QueryResult = $Query->fetch();
		if($product1->getReductionData($_GET['id'])!=0){
		$QueryResult['countReduction']=$product1->countReduction($QueryResult['price'], $product1->getReductionData($_GET['id']));
		}
		$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$Query2 = $product2->getProductQuery($_GET['id']);
		$QueryResult2 = $Query2->fetch();
		if($product2->getReductionData($_GET['id'])!=0){
		$QueryResult2['countReduction']=$product2->countReduction($QueryResult2['price'], $product2->getReductionData($_GET['id']));
		}
		$helper=array("button"=>"Aktualizuj produkt w obu bazach","editForm"=>"?editformBoth");
	}catch (PODException $e){
		$error='Błąd przy pobieraniu informacji o produkcie: ' . $e->getMessage();
	}
	if(!isset($error)){
		require $root_dir.'/templates/form.html.php';
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
	require $root_dir.'/templates/completeForm.html.php';
	exit();
}elseif(isset($_GET['editformBoth'])){
	if ($_POST['text']==''){
		$error='Brak aktualnego wpisu: nazwa produktu!';
	}elseif ($_POST['quantity']==''){
		$error='Brak aktualnego wpisu: nowa liczba produktu!';
	}else try{
		$product1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$newQuery = $product1->updateBoth($_POST['id'], $_POST['nominalPriceNew'], $_POST['text'], $_POST['quantity']);
		$firstConfirmation = $product1->confirmation($_POST['id']);
	}catch (PDOExceptioon $e){
		$error='Aktualizacja danych nie powiodła się: ' . $e->getMessage();
	}
	if(!isset($error)){
		$product2= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$oldQuery = $product2->updateBoth($_POST['id'], $_POST['nominalPriceOld'], $_POST['text'], $_POST['quantity']);
		$secondConfirmation = $product2->confirmation($_POST['id']);
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
			$firstConfirmation = $product1->confirmation($_POST['id']);
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
}elseif (isset($_GET['BPSQO'])OR(isset($_GET['BPSQN']))){
	if (isset($_GET['BPSQO'])){
		$order1= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
	}
	elseif (isset($_GET['BPSQN'])){
		$order1= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
	}
	$Query = $order1->updateQuantity($_GET['quantity'], $_GET['id']);
	$firstConfirmation = $order1->confirmation($_GET['id']);
}elseif(isset($_GET['mergeQuantities'])){
	if($_GET['mergeQuantities']== 'Uaktualnij ilości w nowej bazie'){
		$include=0;
		$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
		$product= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
		$orderDetails=array('idOrder'=>$_GET['id_number'].' w nowym panelu', 'base'=>'Ilość w SP', 'current'=>'Obecna ilość (NP)', 'changed'=>'Ilość po modyfikacji (NP)');
	}elseif ($_GET['mergeQuantities']== 'Uaktualnij ilości w starej bazie'){
		$include=1;
		$order1= new LinuxPlOrder($firstHost, $firstLogin, $firstPassword);
		$product= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$orderDetails=array('idOrder'=>$_GET['id_number'].' w starym panelu', 'base'=>'Ilość w NP', 'current'=>'Obecna ilość (SP)', 'changed'=>'Ilość po modyfikacji (SP)');
	}
	try{
		$Query = $order1->selectOrderQuantity($_GET['id_number']);
		foreach ($Query as $Query2){
			$oldQuantity=$product->getQuantity($Query2['product_id']);
			$quantityUpdate=$product->updateQuantity($Query2['quantity'], $Query2['product_id']);
			$finalQuantity=$product->getQuantity($Query2['product_id']);
			$mods[]=array('quantity'=>$Query2['quantity'], 'product_id'=>$Query2['product_id'], 'previousQuantity'=>$oldQuantity, 'finalQuantity'=>$finalQuantity, 'result'=>(string)($finalQuantity-$oldQuantity));
		}
	}catch (PDOExceptioon $e){
		$error='Pobranie ilości w zamówieniu nie powiodło się: ' . $e->getMessage();
	}
	try{
		$output = $twig->render('/orderUpdate.html', array(
		'dates' => $mods,
		'orderDetails'=>$orderDetails,
		));
		echo $output;
	}catch (PDOExceptioon $e){
		$error='Wyświetlenie efektów migracji zamówienia nie powiodło się: ' . $e->getMessage();
	}
}

if(isset($error)OR(isset($firstConfirmation))){
	if(isset($error)){
		$output = $twig->render('/index.html', array(
		'title' => 'Niepowodzenie wykonania operacji',
		'result' => 'UWAGA! Operacja zakończona niepowodzeniem!',
		'error' => $error,
		));
	}else{
		$conf=array('Wykonanie aktualizacji produktu ID '.$firstConfirmation["id_product"], 'Obecna ilość produktu w edytowanej bazie wynosi: '.$firstConfirmation["quantity"]);
		if(isset($secondConfirmation)){
			array_push($conf, 'Obecna ilość produktu w drugiej bazie wynosi: '.$secondConfirmation["quantity"]);
		}
		$output = $twig->render('/index.html', array(
		'title' => 'Potwierdzenie wykonania operacji',
		'result' => 'Operacja zakończyła się powodzeniem!',
		'message' => $conf,
		));
	}
	echo $output;
}
