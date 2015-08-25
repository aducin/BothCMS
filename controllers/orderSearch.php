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

if(isset($_POST['sendVoucherMessage'])OR(isset($_POST['sendDiscountMessage'])OR(isset($_POST['shipmentNumber'])OR(isset($_POST['sendUndeliveredMessage']))))){
	require_once $root_dir.'/controllers/mail.php'; 
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
		$outputOrder5=1;
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
			$outputOrder4=1;
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
			$order= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
			$notificationresult = $order->sendNotification($_GET['notification']);
		}elseif(isset($_GET['send'])&&$_GET['send']=='linuxPl'){
			$order= new LinuxPlOrder($firstHost, $firstLogin, $firstPassword);
			$notificationresult = $order->sendNotification($_GET['notification']);
		}
		if($notificationresult==''){
			$error='W wybranej bazie danych brak zamówienia o podanym numerze!';
		}
		$outputOrder7=1;
	}elseif (isset($_GET['detailorder'])AND($_GET['detailorder']) !=''){
		$order1= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
		$nameDetails = $order1->getQueryLessDetails($_GET['detailorder']);
		$nameDetails['reducedTotalProduct']=$nameDetails['total_products']*0.85;
		$nameDetails['orderNumber']=$_GET['detailorder'];
		$outputOrder2=1;
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
	}elseif (isset($_GET['undeliveredConfirmation'])AND($_GET['undeliveredConfirmation']) !=''){
		$order= new OgicomOrder($secondHost, $secondLogin, $secondPassword);
		$confOrderData = $order->getQueryLessDetails($_GET['undeliveredConfirmation']);
		$confOrderData['ordNumb']=$_GET['undeliveredConfirmation'];
		$confOrderDetail = $order->getQueryDetails($_GET['undeliveredConfirmation']);
		$outputOrder3=1;
		if(!isset($confOrderData['total_paid'])){
			$error="W starej bazie nie ma zamówienia o podanym numerze!";
		}else{
			foreach ($confOrderDetail as $detail){
				$confOrderDetails[]=array('id'=>$detail['product_id'], 'name'=>$detail['name'], 'price'=>number_format($detail['product_price'], 2,'.',''), 'reduction'=>number_format($detail['reduction_amount'], 2,'.',''), 'quantity'=>$detail['product_quantity']);
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
	$outputOrderOrProduct1 = $order1->confirmation($_GET['id']);
	$outputOrder1=1;
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
	$outputOrder6=1;
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
require_once $root_dir.'/controllers/output.php'; 
