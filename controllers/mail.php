<?php 

$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
$vendor_dir = $root_dir.'/vendor';
$cache_dir = $root_dir.'/cache'; // remember to `chmod 777 cache` (make this directory writable)
$templates_dir = $root_dir.'/templates';
$mail_dir = $templates_dir.'/mails';

$twig_lib = $vendor_dir.'/Twig/lib/Twig';
require_once $twig_lib . '/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem($templates_dir);
$twig = new Twig_Environment($loader, array(
	'cache' => $cache_dir,
));
$DBHandler=parse_ini_file($root_dir.'/config/database.ini',true);
$secondHost=$DBHandler["secondDB"]["host"];
$secondLogin=$DBHandler["secondDB"]["login"];
$secondPassword=$DBHandler["secondDB"]["password"];
$ogicomDbHandler=bothDbHandler::getInstance('ogicom', $secondHost, $secondLogin, $secondPassword);

$to=$_POST['email'];
if(isset($_POST['sendVoucherMessage'])){
	if($_POST['changedVoucherNumber']!=''){
		$number=$_POST['changedVoucherNumber'];
	}elseif($_POST['voucherNumber']!=''){
		$number=$_POST['voucherNumber'];
	}
	$subject='Modele-ad9bis - informacja o kuponie rabatowym';
	$message = $twig->render('/mails/voucherEmail.html', array(
		'voucherNumber' => $number,
		'reference' => $_POST['reference'],
		'firstname' => $_POST['firstname'],
		'lastname' => $_POST['lastname'],
		));
}elseif(isset($_POST['sendDiscountMessage'])){
	if($_POST['paymentOption']=="Przelew bankowy"){
		$payment='bankwire';
	}elseif($_POST['paymentOption']=="Płatność przy odbiorze"){
		$payment='cashOnDelivery';
	}
	$subject='Ad9bis - zamówienie z 15% rabatem';
	$message = $twig->render('/mails/discountEmail.html', array(
		'payment' => $payment,
		'referenceNumber' => $_POST['referenceNumber'],
		'firstname' => $_POST['customerFirstName'],
		'lastname' => $_POST['customerLastName'],
		'rowCount' => $_POST['rowCount'],
		'totalBeforeDiscount' => $_POST['totalBeforeDiscount'],
		'totalWithDiscount' => $_POST['totalWithDiscount'],
		));
}elseif(isset($_POST['sendUndeliveredMessage'])){
	$order= new OgicomOrder($ogicomDbHandler);
	$confOrderData = $order->getQueryLessDetails($_POST['ordNumb']);
	$confOrderDetail = $order->getQueryDetails($_POST['ordNumb']);
	foreach ($confOrderDetail as $detail){
				$confOrderDetails[]=array('id'=>$detail['product_id'], 'name'=>$detail['name'], 'price'=>number_format($detail['product_price'], 2,'.',''), 'reduction'=>number_format($detail['reduction_amount'], 2,'.',''), 'quantity'=>$detail['product_quantity']);
	}
	if($_POST['paymentOption']=="Przelew bankowy"){
		$payment='bankwire';
	}elseif($_POST['paymentOption']=="Płatność przy odbiorze"){
		$payment='cashOnDelivery';
	}
	$subject='Ad9bis - powtórne potwierdzenie zamówienia';
	if($confOrderData['total_paid']==$confOrderData['total_products']AND$confOrderData['total_products']<250){
		$cashOnPickup=1;
		$message = $twig->render('/mails/undeliveredEmail.html', array(
			'payment' => $payment,
			'result' => $confOrderDetails,
			'customerData'=>$confOrderData,
			'cashOnPickup'=>$cashOnPickup,
		));
	}else{
		$message = $twig->render('/mails/undeliveredEmail.html', array(
			'payment' => $payment,
			'result' => $confOrderDetails,
			'customerData'=>$confOrderData,
		));
	}
}
elseif(isset($_POST['shipmentNumber'])){
	$subject='Modele-ad9bis - śledzenie przesyłki';
	$message = $twig->render('/mails/shipmentEmail.html', array(
		'trackingNumber' => $_POST['trackingNumber'],
		'reference' => $_POST['reference'],
		'name' => $_POST['name'],
		'surname' => $_POST['surname'],
		));
}
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: ad9bis@gmail.com' . "\r\n";

if(mail($to, $subject, $message, $headers)){
 	$outputOrderMail= 'Wiadomość została wysłana na adres: '.($_POST['email']).'.';
 	require_once $root_dir.'/controllers/output.php'; 
}else{
  	$error= 'Nie udało się wysłać wiadomości e-mail';
}
