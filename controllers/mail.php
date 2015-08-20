<?php 

$to='ad9bis@gmail.com';//$_POST['email'];
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
}elseif(isset($_POST['shipmentNumber'])){
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
 	$confirmationMail= 'Wiadomość została wysłana na adres: '.($_POST['email']).'.';
}else{
  	$error= 'Nie udało się wysłać wiadomości e-mail';
}
