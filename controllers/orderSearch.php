<?php 

if(isset($_POST['sendVoucherMessage'])OR(isset($_POST['sendDiscountMessage'])OR(isset($_POST['shipmentNumber'])OR(isset($_POST['sendUndeliveredMessage']))))){
	require_once $root_dir.'/controllers/mail.php'; 
}

$controller = new OrderController($linuxPlHandler, $ogicomHandler);
if(isset($_GET['action'])AND(($_GET['action'])=='orderSearch')){
	if(($_GET['neworder']!='')OR($_GET['oldorder']!='')){
		if ($_GET['neworder'] !=''){
			$result=$controller->getOrderInformations(1, $_GET['neworder']);
			$ordedSearch=array('onstock'=>'Na stanie (NP)', 'ordered'=>'Zamówione (NP)', 'button1'=>'Kompletna edycja w NP', 'button2'=>'Wyrównaj ilość w starej bazie', 'button3'=>'Zmiana obu przez nowy panel', 'button4'=>'Uaktualnij ilości w starej bazie', 'form'=>'fullEditionN', 'action'=>'BPSQO', 'orderNr'=>$_GET['neworder']);
		}elseif ($_GET['oldorder'] !=''){
			$result=$controller->getOrderInformations(2, $_GET['oldorder']);
			$ordedSearch=array('onstock'=>'Na stanie (SP)', 'ordered'=>'Zamówione (SP)', 'button1'=>'Kompletna edycja w SP', 'button2'=>'Wyrównaj ilość w nowej bazie', 'button3'=>'Zmiana obu przez stary panel', 'button4'=>'Uaktualnij ilości w nowej bazie','form'=>'fullEditionO', 'action'=>'BPSQN', 'orderNr'=>$_GET['oldorder']);
		}
		if($result==0){
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
			$controller->setExistingClient(new LinuxPlCustomer($linuxPlHandler));
			//$controller->existingClient=new LinuxPlCustomer($linuxPlHandler);
			$customerData=$controller->getCustomerData($totalProducts['idCustomer']);
			$voucherNumber=$controller->getVoucherHistory($totalProducts['idCustomer']);
			foreach ($voucherNumber as $custOrder){
				$customerData['voucher']=$custOrder['orderNumber'];
			}
		}	
	}elseif (isset($_GET['notification'])AND($_GET['notification']) !=''){
		$orderTracking=$controller-> sendNotification($_GET['send'], $_GET['notification']);
		if($orderTracking==''){
			$error='W wybranej bazie danych brak zamówienia o podanym numerze!';
		}
	}elseif (isset($_GET['detailorder'])AND($_GET['detailorder']) !=''){
		$sixthOrderDiscount=$controller-> checkOrderDetail($_GET['detailorder']);
		if(!isset($sixthOrderDiscount['total_paid'])){
			$error="W starej bazie nie ma zamówienia o podanym numerze!";
		}else{
			$detail=$controller-> getOrderDetails($_GET['detailorder']);
		}
	}elseif (isset($_GET['undeliveredConfirmation'])AND($_GET['undeliveredConfirmation']) !=''){
		$undeliveredOrderConf=$controller->checkUndeliveredData($_GET['undeliveredConfirmation']);
		if(!isset($undeliveredOrderConf['total_paid'])){
			$error="W starej bazie nie ma zamówienia o podanym numerze!";
		}else{
			$confOrderDetails = $controller->getUndeliveredData($_GET['undeliveredConfirmation']);
		}
	}
}elseif (isset($_GET['BPSQO'])OR(isset($_GET['BPSQN']))){
	if (isset($_GET['BPSQO'])){
		$outputOrderOrProduct1['first']=$controller-> MergeSingleQuantity(1, $_GET['id'], $_GET['quantity']);
	}elseif (isset($_GET['BPSQN'])){
		$outputOrderOrProduct1['first']=$controller-> MergeSingleQuantity(2, $_GET['id'], $_GET['quantity']);
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
}else{
$order='1';
}
unset($controller);
require_once $root_dir.'/controllers/output.php'; 
