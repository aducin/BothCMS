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
	unset($dbHandlerLinuxPl);
}
if($_SESSION['log']==0){
	header('Location:templates/signIn.html');
}

if(isset($_POST['sendVoucherMessage'])OR(isset($_POST['sendDiscountMessage'])OR(isset($_POST['shipmentNumber'])OR(isset($_POST['sendUndeliveredMessage']))))){
	require_once $root_dir.'/controllers/mail.php'; 
}

$controller = new OrderController($linuxPlHandler, $ogicomHandler);
if(isset($_GET['action'])AND(($_GET['action'])=='orderSearch')){
	if(($_GET['neworder']!='')OR($_GET['oldorder']!='')){
		if ($_GET['neworder'] !=''){
			$result=$controller->getOrderInformations(1, $_GET['neworder']);
			$ordSearch=array('onstock'=>'Na stanie (NP)', 'ordered'=>'Zamówione (NP)', 'button1'=>'Kompletna edycja w NP', 'button2'=>'Wyrównaj ilość w starej bazie', 'button3'=>'Zmiana obu przez nowy panel', 'button4'=>'Uaktualnij ilości w starej bazie', 'form'=>'fullEditionN', 'action'=>'BPSQO', 'orderNr'=>$_GET['neworder']);
		}elseif ($_GET['oldorder'] !=''){
			$result=$controller->getOrderInformations(2, $_GET['oldorder']);
			$ordSearch=array('onstock'=>'Na stanie (SP)', 'ordered'=>'Zamówione (SP)', 'button1'=>'Kompletna edycja w SP', 'button2'=>'Wyrównaj ilość w nowej bazie', 'button3'=>'Zmiana obu przez stary panel', 'button4'=>'Uaktualnij ilości w nowej bazie','form'=>'fullEditionO', 'action'=>'BPSQN', 'orderNr'=>$_GET['oldorder']);
		}
		$outputOrder5=1;
		if(!isset($result)){
			$error='W bazie danych nie ma zamówienia o podanym numerze!';
		}
	}elseif (isset($_GET['orderVoucher'])AND ($_GET['orderVoucher'])!=''){
		$totalProducts=$controller->orderCheck($_GET['orderVoucher']);
		if($totalProducts['total']<50){
			$error='Kwota zamówienia wynosi '.$totalProducts['total'].'zł i jest zbyt mała, aby przyznać kolejny kupon.';
		}
		if($totalProducts['total']==''){
			$error='W bazie nie znaleziono zamówienia nr '.$_GET['orderVoucher'].'.';
		}
		if($totalProducts['total']>=50){
			$customerData=$controller->getCustomerData($totalProducts['idCustomer']);
			$custOrders=$controller->getVoucherHistory($totalProducts['idCustomer']);
			foreach ($custOrders as $custOrder){
				$customerData['voucher']=$custOrder['orderNumber'];
			}
			$outputOrder4=1;
		}	
	}elseif (isset($_GET['notification'])AND($_GET['notification']) !=''){
		$notificationresult=$controller-> sendNotification($_GET['send'], $_GET['notification']);
		if($notificationresult==''){
			$error='W wybranej bazie danych brak zamówienia o podanym numerze!';
		}else{
			$outputOrder7=1;
		}
	}elseif (isset($_GET['detailorder'])AND($_GET['detailorder']) !=''){
		$nameDetails=$controller-> checkOrderDetail($_GET['detailorder']);
		$outputOrder2=1;
		if(!isset($nameDetails['total_paid'])){
			$error="W starej bazie nie ma zamówienia o podanym numerze!";
		}else{
			$detail=$controller-> getOrderDetails($_GET['detailorder']);
		}
	}elseif (isset($_GET['undeliveredConfirmation'])AND($_GET['undeliveredConfirmation']) !=''){
		$confOrderData=$controller->checkUndeliveredData($_GET['undeliveredConfirmation']);
		$order= new OgicomOrder($ogicomHandler);
		if(!isset($confOrderData['total_paid'])){
			$error="W starej bazie nie ma zamówienia o podanym numerze!";
		}else{
			$confOrderDetails = $controller->getUndeliveredData($_GET['undeliveredConfirmation']);
			$outputOrder3=1;
		}
	}
}elseif (isset($_GET['BPSQO'])OR(isset($_GET['BPSQN']))){
	if (isset($_GET['BPSQO'])){
		$outputOrderOrProduct1=$controller-> MergeSingleQuantity(1, $_GET['id'], $_GET['quantity']);
	}elseif (isset($_GET['BPSQN'])){
		$outputOrderOrProduct1=$controller-> MergeSingleQuantity(2, $_GET['id'], $_GET['quantity']);
	}
}elseif(isset($_GET['mergeQuantities'])){
	try{
		if($_GET['mergeQuantities']== 'Uaktualnij ilości w nowej bazie'){
			$mergeDetails=$controller->mergeIntoNewHelper($_GET['id_number']);
			$mods=$controller->mergeIntoNew($_GET['id_number']);
		}elseif ($_GET['mergeQuantities']== 'Uaktualnij ilości w starej bazie'){
			$mergeDetails=$controller->mergeIntoOldHelper($_GET['id_number']);
			$mods=$controller->mergeIntoOld($_GET['id_number']);
		}
	}catch (PDOExceptioon $e){
		$error='Pobranie ilości w zamówieniu nie powiodło się: ' . $e->getMessage();
	}
	$outputOrder6=1;
}else{
$finalOutput='order';	
}
unset($controller);
require_once $root_dir.'/controllers/output.php'; 
