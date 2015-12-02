<?php

class MailController extends Controller{

	private $ogicomOrder;
	private $headers;
	private $message;
	private $subject;
	private $to;
	
	public function __construct( $mail, $ogicomHandler ){

		$this->ogicomOrder = new OgicomOrder( $ogicomHandler );
		$this->output = new MailOutput();
		$this->to = $mail;
		$this->headers = 'MIME-Version: 1.0' . "\r\n";
		$this->headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$this->headers .= 'From: ad9bis@gmail.com' . "\r\n";
	}

	public function discount(){

		if($_POST['paymentOption']=="Przelew bankowy"){
			$payment='bankwire';
		}elseif($_POST['paymentOption']=="Płatność przy odbiorze"){
			$payment='cashOnDelivery';
		}
		$this->message = $this->output->renderDiscountMail( $_POST['customerFirstName'], $_POST['customerLastName'], $payment, 
			$_POST['referenceNumber'], $_POST['rowCount'], $_POST['totalBeforeDiscount'], $_POST['totalWithDiscount'] );
		$this->subject = 'Ad9bis - zamówienie z 15% rabatem';
		$this->mailSend( $this->headers, $this->message, $this->subject, $this->to );
	}

	public function shipment(){

		$this->message = $this->output->renderShipmentMail( $_POST['name'], $_POST['reference'], $_POST['surname'], $_POST['trackingNumber'] );
		$this->subject = 'Modele-ad9bis - śledzenie przesyłki';
		$this->mailSend( $this->headers, $this->message, $this->subject, $this->to );
	}

	public function undelivered(){

		$confOrderData = $this->ogicomOrder->getQueryLessDetails( $_POST['ordNumb'] );
		$confOrderDetail = $this->ogicomOrder->getQueryDetails( $_POST['ordNumb'] );
		foreach ( $confOrderDetail as $detail ){
			$confOrderDetails[]=array('id'=>$detail['product_id'], 'name'=>$detail['name'], 'price'=>number_format($detail['product_price'], 2,'.',''), 'reduction'=>number_format($detail['reduction_amount'], 2,'.',''), 'quantity'=>$detail['product_quantity']);
		}
		if( $_POST['paymentOption'] == "Przelew bankowy" ){
			$payment = 'bankwire';
		}elseif( $_POST['paymentOption'] == "Płatność przy odbiorze" ){
			$payment = 'cashOnDelivery';
		}
		$headers = $this->headers;
		$this->message = $this->output->renderUndeliveredMail( $confOrderData, $confOrderDetails, $payment );
		$this->subject = 'Ad9bis - powtórne potwierdzenie zamówienia';
		$this->mailSend( $this->headers, $this->message, $this->subject, $this->to );
	}

	public function voucher(){

		if($_POST['changedVoucherNumber']!=''){
			$number=$_POST['changedVoucherNumber'];
		}elseif($_POST['voucherNumber']!=''){
			$number=$_POST['voucherNumber'];
		}
		$headers = $this->headers;
		$this->message = $this->output->renderVoucherMail( $_POST['firstname'], $_POST['lastname'], $number, $_POST['reference'] );
		$this->subject = 'Modele-ad9bis - informacja o kuponie rabatowym';
		$this->mailSend( $this->headers, $this->message, $this->subject, $this->to );
	}

	private function mailSend( $headers, $message, $subject, $to ){

		if(mail($to, $subject, $message, $headers)){
			$outputOrderMail= 'Wiadomość została wysłana na adres: '.($_POST['email']).'.';
			$this->output->displayConfirmation( $message, $outputOrderMail );
		} else {
			$error= 'Nie udało się wysłać wiadomości e-mail';
  			$output = new OrderOutput();
  			$output->renderOrderError( $error );
		}
	}
}