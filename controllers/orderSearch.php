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
}
if($_SESSION['log']==0){
	header('Location:templates/signIn.html');
}
unset($db);

if(isset($_POST['sendMessage'])){
	$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
	$customerData= $order1->getOrderCustomerData($_POST['customerNumber']);
	if($_POST['changedVoucherNumber']!=''){
		$number=$_POST['changedVoucherNumber'];
	}elseif($_POST['voucherNumber']!=''){
		$number=$_POST['voucherNumber'];
	}
	require $mail_dir.'/voucherMail.html';
	exit();
}elseif(isset($_POST['sendDiscountMessage'])){
	if($_POST['paymentOption']=='Przelew bankowy'){
		$option=1;
	}elseif($_POST['paymentOption']=='Płatność przy odbiorze'){
		$option=2;
	}
	require $mail_dir.'/discountMail.html';
	exit();
}elseif(isset($_GET['shipmentNumber'])){
	require $mail_dir.'/shipmentMail.html';
	exit();
}
if(isset($_GET['action'])AND(($_GET['action'])=='orderSearch')){
	if(($_GET['neworder']!='')OR($_GET['oldorder']!='')){
		if ($_GET['neworder'] !=''){
			$order2= new LinuxPlOrder($firstHost, $firstLogin, $firstPassword);
			$query = $order2->getQuery($_GET['neworder']);
			$product= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
			$ordSearch=array('onstock'=>'Na stanie (NP)', 'ordered'=>'Zamówione (NP)', 'button1'=>'Kompletna edycja w NP', 'button2'=>'Wyrównaj ilość w starej bazie', 'button3'=>'Zmiana obu przez nowy panel', 'button4'=>'Uaktualnij ilości w starej bazie', 'form'=>'fullEditionN', 'action'=>'BPSQO', 'orderNr'=>$_GET['neworder']);
		}elseif ($_GET['oldorder'] !=''){
			$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
			$query = $order1->getQuery($_GET['oldorder']);
			$product= new LinuxPlProduct($firstHost, $firstLogin, $firstPassword);
			$ordSearch=array('onstock'=>'Na stanie (SP)', 'ordered'=>'Zamówione (SP)', 'button1'=>'Kompletna edycja w SP', 'button2'=>'Wyrównaj ilość w nowej bazie', 'button3'=>'Zmiana obu przez stary panel', 'button4'=>'Uaktualnij ilości w nowej bazie','form'=>'fullEditionO', 'action'=>'BPSQN', 'orderNr'=>$_GET['oldorder']);
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
		}
	}elseif (isset($_GET['orderVoucher'])AND ($_GET['orderVoucher'])!=''){
		$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
		$orderSearch = $order1->checkIfVoucherDue($_GET['orderVoucher']);
		$totalProducts= array('total'=>$orderSearch['total_products'], 'idCustomer'=>$orderSearch['id_customer']);
		if($totalProducts['total']<50){
			$error='Kwota zamówienia wynosi '.$totalProducts['total'].'zł i jest zbyt mała, aby przyznać kolejny kupon.';
		}else{
			$customerData= $order1->getOrderCustomerData($orderSearch['id_customer']);
			$customer= new LinuxPlCustomer($firstHost, $firstLogin, $firstPassword);
			if($customer->checkIfClientExists($customerData['email'])==6){
				$customerData['new'] = $customer->checkIfClientExists($customerData['email']);
			}
			$voucherHistory= $order1->getVoucherNumber($orderSearch['id_customer']);
			$customerData['voucherLast']= $order1->getLastVoucherNumber($orderSearch['id_customer']);
			$customerData['idCustomer']=$totalProducts['idCustomer'];
			$ordNumb=0;
			foreach ($voucherHistory as $custOrder){
				$ordNumb++;
				$custOrders[]= array('id'=>$custOrder['id_order'], 'reference'=>$custOrder['reference'], 'total'=>$custOrder['total_products'], 'shipping'=>$custOrder['total_shipping'], 'date'=>$custOrder['date_add'], 'orderNumber'=>((($ordNumb-1) % 6) + 1 ));
			}
			foreach ($custOrders as $custOrder){
				$customerData['voucher']=$custOrder['orderNumber'];
			}
		}	
	}elseif (isset($_GET['notification'])AND($_GET['notification']) !=''){
		if(isset($_GET['send'])&&$_GET['send']=='ogicom'){
			$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
			$notificationresult = $order1->sendNotification($_GET['notification']);
			//require $root_dir.'/templates/orders.html';
		}elseif(isset($_GET['send'])&&$_GET['send']=='linuxPl'){
			$order2= new LinuxPlOrder($firstHost, $firstLogin, $secondPassword);
			$notificationresult = $order2->sendNotification($_GET['notification']);
			//require $root_dir.'/templates/orders.html';
		}
		if($notificationresult==''){
			$error='W wybranej bazie danych brak zamówienia o podanym numerze!';
		}
	}elseif (isset($_GET['detailorder'])AND($_GET['detailorder']) !=''){
		$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
		$nameDetails = $order1->getQueryLessDetails($_GET['detailorder']);
		$nameDetails['reducedTotalProduct']=$nameDetails['total_products']*0.85;
		$nameDetails['orderNumber']=$_GET['detailorder'];
		if(!isset($nameDetails['total_paid'])){
			$error="W starej bazie nie ma zamówienia o podanym numerze!";
		}else{
			$nameDetails['reducedTotal']=$nameDetails['total_paid']*0.85;
			$details = $order1->getQueryDetails($_GET['detailorder']);
			$detailsCount=$order1->getCount($_GET['detailorder']);
			$nameDetails['count'] = $detailsCount['COUNT(product_name)'];
			foreach ($details as $sDetail){
				$detail2[]=array('id'=>$sDetail['product_id'], 'name'=>$sDetail['name'], 'price'=>number_format($sDetail['product_price'], 2,'.',''), 'reduction'=>number_format($sDetail['reduction_amount'], 2,'.',''), 'reducedPrice'=>($sDetail['product_price']-$sDetail['reduction_amount'])*0.85, 'quantity'=>$sDetail['product_quantity'], 'reducedTotalPrice'=>($sDetail['product_price']-$sDetail['reduction_amount'])*$sDetail['product_quantity']*0.85);
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
		$mergeDetails=array('idOrder'=>$_GET['id_number'].' w nowym panelu', 'base'=>'Ilość w SP', 'current'=>'Obecna ilość (NP)', 'changed'=>'Ilość po modyfikacji (NP)');
	}elseif ($_GET['mergeQuantities']== 'Uaktualnij ilości w starej bazie'){
		$include=1;
		$order1= new LinuxPlOrder($firstHost, $firstLogin, $firstPassword);
		$product= new OgicomProduct($secondHost, $secondLogin, $secondPassword);
		$mergeDetails=array('idOrder'=>$_GET['id_number'].' w starym panelu', 'base'=>'Ilość w NP', 'current'=>'Obecna ilość (SP)', 'changed'=>'Ilość po modyfikacji (SP)');
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
}else{
$output = $twig->render('/orderSearch.html');
}

if(isset($error)){
	$output = $twig->render('/orderSearch.html', array(
		'title' => 'Niepowodzenie wykonania operacji',
		'result' => 'UWAGA! Operacja zakończona niepowodzeniem!',
		'message' => $error,
		));
}elseif(isset($firstConfirmation)){
	$conf=array('Wykonanie aktualizacji produktu ID '.$firstConfirmation["id_product"], 'Obecna ilość produktu w edytowanej bazie wynosi: '.$firstConfirmation["quantity"]);
	if(isset($secondConfirmation)){
		array_push($conf, 'Obecna ilość produktu w drugiej bazie wynosi: '.$secondConfirmation["quantity"]);
	}
	$output = $twig->render('/index.html', array(
		'title' => 'Potwierdzenie wykonania operacji',
		'result' => 'Operacja zakończyła się powodzeniem!',
		'message' => $conf,
		));
}elseif(isset($nameDetails)){
	$output = $twig->render('/discountSearchResult.html', array(
		'result' => $detail2,
		'customerData'=>$nameDetails,
		));
}elseif(isset($custOrders)){
	$output = $twig->render('/voucherSearchResult.html', array(
		'result' => $custOrders,
		'customerData'=>$customerData,
		));
}elseif(isset($ordSearch)){
	$output = $twig->render('/orderSearchResult.html', array(
		'result' => $this,
		'variables'=>$ordSearch,
		));
}elseif(isset($mergeDetails)){
	$output = $twig->render('/orderUpdate.html', array(
		'dates' => $mods,
		'orderDetails'=>$mergeDetails,
		));
}elseif(isset($notificationresult)){
	$output = $twig->render('/shipmentNotification.html', array(
		'notDates' => $notificationresult,
		));	
}
try{
	echo $output;
}catch (PDOException $e){
	$error='Nie udało się wyświetlić listy wyszukiwania zamówień: ' . $e->getMessage();
}	
