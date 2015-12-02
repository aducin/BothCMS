<?php 

class MailOutput extends OutputController{
	
	public function renderDiscountMail( $firstName, $lastName, $payment, $reference, $rowCount, $totalBeforeDiscount, $totalAfterDiscount ){
		$output = $this->twig->render('/mails/discountEmail.html', array(
			'payment' => $payment,
			'referenceNumber' => $reference,
			'firstname' => $firstName,
			'lastname' => $lastName,
			'rowCount' => $rowCount,
			'totalBeforeDiscount' => $totalBeforeDiscount,
			'totalWithDiscount' => $totalAfterDiscount,
		));
		return $output;
	}

	public function renderShipmentMail( $name, $reference, $surname, $tracking ){
		$output = $this->twig->render('/mails/shipmentEmail.html', array(
			'trackingNumber' => $tracking,
			'reference' => $reference,
			'name' => $name,
			'surname' => $surname,
		));
		return $output;
	}

	public function renderUndeliveredMail( $confOrderData, $confOrderDetails, $payment ){
		if( $confOrderData['total_paid'] == $confOrderData['total_products'] AND $confOrderData['total_products'] < 250 ){
			$cashOnPickup=1;
			$output = $this->twig->render('/mails/undeliveredEmail.html', array(
				'payment' => $payment,
				'result' => $confOrderDetails,
				'customerData'=>$confOrderData,
				'cashOnPickup'=>$cashOnPickup,
			));
		}else{
			$output = $this->twig->render('/mails/undeliveredEmail.html', array(
				'payment' => $payment,
				'result' => $confOrderDetails,
				'customerData'=>$confOrderData,
			));
		}
		$output = $this->twig->render('/orderSearch.html');
		return $output;
	}

	public function renderVoucherMail( $firstName, $lastName, $number, $reference ){
		$output = $this->twig->render('/mails/voucherEmail.html', array(
			'voucherNumber' => $number,
			'reference' => $reference,
			'firstname' => $firstName,
			'lastname' => $lastName,
		));
		return $output;
	}

	public function displayConfirmation( $message, $outputOrderMail ){
		$output = $this->twig->render('/messageConfirmation.html', array(
			'result' => 'Poniżej znajduje się treść wysłana do Klienta:',
			'message' => $message,
			'confirmation' => $outputOrderMail,)
 		);
 		echo $output;
	}
}