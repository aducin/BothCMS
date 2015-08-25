<?php

if(isset($error)AND(!isset($authors))){
	$output = $twig->render('/orderSearch.html', array(
		'title' => 'Niepowodzenie wykonania operacji',
		'result' => 'UWAGA! Operacja zakończona niepowodzeniem!',
		'message' => $error,
		));
}elseif(isset($error)AND(isset($authors))){
		$output = $twig->render('/productSearch.html', array(
			'authors' => $authors,
			'categories'=>$categories,
			'title' => 'Niepowodzenie wykonania operacji',
			'result' => 'UWAGA! Operacja zakończona niepowodzeniem!',
			'message' => $error,
			));
}elseif(isset($outputOrderMail)){
	$output = $twig->render('/messageConfirmation.html', array(
		'title' => $confirmationMail,
		'result' => 'Poniżej znajduje się treść wysłana do Klienta:',
		'message' => $message,
		'confirmation' => $outputOrderMail,
		));
}elseif(isset($outputOrder1)){
	$conf=array('Wykonanie aktualizacji produktu ID '.$firstConfirmation["id_product"], 'Obecna ilość produktu w edytowanej bazie wynosi: '.$firstConfirmation["quantity"]);
	if(isset($secondConfirmation)){
		array_push($conf, 'Obecna ilość produktu w drugiej bazie wynosi: '.$secondConfirmation["quantity"]);
	}
	$output = $twig->render('/confirm.html', array(
		'title' => 'Potwierdzenie wykonania operacji',
		'result' => 'Operacja zakończyła się powodzeniem!',
		'message' => $conf,
		));
}elseif(isset($outputOrder2)){
	$output = $twig->render('/discountSearchResult.html', array(
		'result' => $detail2,
		'customerData'=>$nameDetails,
		));
}elseif(isset($outputOrder3)){
	$output = $twig->render('/undeliveredMailOrder.html', array(
		'result' => $confOrderDetails,
		'customerData'=>$confOrderData,
		));
}elseif(isset($outputOrder4)){
	$output = $twig->render('/voucherSearchResult.html', array(
		'result' => $custOrders,
		'customerData'=>$customerData,
		));
}elseif(isset($outputOrder5)){
	$output = $twig->render('/orderSearchResult.html', array(
		'result' => $this,
		'variables'=>$ordSearch,
		));
}elseif(isset($outputOrder6)){
	$output = $twig->render('/orderUpdate.html', array(
		'dates' => $mods,
		'orderDetails'=>$mergeDetails,
		));
}elseif(isset($outputOrder7)){
	$output = $twig->render('/shipmentNotification.html', array(
		'notDates' => $notificationresult,
		));	
}elseif(isset($outputProduct1)){
			$output = $twig->render('/editionShortTemplate.html', array(
				'result' => $bothEdit,
				));
		}elseif(isset($outputProduct2)){
			$output = $twig->render('/idProductResult.html', array(
				'result1' => $newQueryResult,
				'result2' => $oldQueryResult,
				));
		}elseif(isset($outputProduct3)){
			$output = $twig->render('/phraseResult.html', array(
				'result' => $searchResult,
				'phrase'=>$phrase,
				));
		}elseif(isset($outputProduct4)){
			$output = $twig->render('/completeEditionResult.html', array(
				'editForm' => $editForm,
				'QueryResult' => $QueryResult,
				'authors'=> $authors,
				'completeTagNames'=>$completeTagNames,
				'completeCatNames'=>$this2,
				'selCategories'=>$selCategories,
				'indexArray'=>$indexArray,
				'condArray'=>$condArray,
				));
		}
try{
	echo $output;
}catch (PDOException $e){
	$error='Nie udało się wyświetlić listy wyszukiwania zamówień: ' . $e->getMessage();
}