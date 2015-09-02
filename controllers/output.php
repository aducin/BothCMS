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
		//'title' => $confirmationMail,
		'result' => 'Poniżej znajduje się treść wysłana do Klienta:',
		'message' => $message,
		'confirmation' => $outputOrderMail,
		));
}elseif(isset($outputOrderOrProduct1)){//                                   orderSearch.php or productSearch.php
	$conf=array('Wykonanie aktualizacji produktu ID '.$outputOrderOrProduct1['first']['id_product'], 'Obecna ilość produktu w edytowanej bazie wynosi: '.$outputOrderOrProduct1['first']['quantity']);
	if(isset($outputOrderOrProduct1['second'])){
		array_push($conf, 'Obecna ilość produktu w drugiej bazie wynosi: '.$outputOrderOrProduct1['second']['quantity']);
	}
	$output = $twig->render('/confirm.html', array(
		'title' => 'Potwierdzenie wykonania operacji',
		'result' => 'Operacja zakończyła się powodzeniem!',
		'message' => $conf,
		));
}elseif(isset($sixthOrderDiscount)){//                                   orderSearch.php
	$output = $twig->render('/discountSearchResult.html', array(
		'result' => $detail,
		'customerData'=>$sixthOrderDiscount,
		));
}elseif(isset($undeliveredOrderConf)){//                                   orderSearch.php
	$output = $twig->render('/undeliveredMailOrder.html', array(
		'result' => $confOrderDetails,
		'customerData'=>$undeliveredOrderConf,
		));
}elseif(isset($voucherNumber)){//                                   orderSearch.php
	$output = $twig->render('/voucherSearchResult.html', array(
		'result' => $voucherNumber,
		'customerData'=>$customerData,
		));
}elseif(isset($ordedSearch)){//                                   orderSearch.php
	$output = $twig->render('/orderSearchResult.html', array(
		'result' => $result,
		'variables'=>$ordedSearch,
		));
}elseif(isset($mergeDetails)){//                                   orderSearch.php
	$output = $twig->render('/orderUpdate.html', array(
		'dates' => $mods,
		'orderDetails'=>$mergeDetails,
		));
}elseif(isset($orderTracking)){//                                   orderSearch.php
	$output = $twig->render('/shipmentNotification.html', array(
		'notDates' => $orderTracking,
		));	
}elseif(isset($productShortEdition)){//                                   productSearch.php
	$output = $twig->render('/editionShortTemplate.html', array(
		'result' => $productShortEdition,
		));
}elseif(isset($productIdSearch)){//                                   productSearch.php
	$output = $twig->render('/idProductResult.html', array(
		'result1' => $productIdSearch,
		'result2' => $oldQueryResult,
		'imageNumber' => $imageNumber,
		));
}elseif(isset($phraseSearchResult)){//                                   productSearch.php
	$output = $twig->render('/phraseResult.html', array(
		'result' => $phraseSearchResult,
		'phrase'=>$productPhraseSearch,
		));
}elseif(isset($completeQueryResult)){//                                   productSearch.php
	$indexArray = array 
	('1'=>array('indexed'=>'0','activeness'=>'Nieaktywny'),
		'2'=>array('indexed'=>'1','activeness'=>'Aktywny'));
	$condArray = array (
		'1'=>array('condition'=>'new','value'=>'Nowy'),
		'2'=>array('condition'=>'used','value'=>'Używany'),
		'3'=>array('condition'=>'refurbished','value'=>'Odnowiony'));
	$output = $twig->render('/completeEditionResult.html', array(
		'editForm' => $editForm,
		'QueryResult' => $completeQueryResult,
		'authors'=> $authors,
		'completeTagNames'=>$completeTagNames,
		'completeCatNames'=>$categoryList,
		'selCategories'=>$selCategories,
		'indexArray'=>$indexArray,
		'condArray'=>$condArray,
		));
}elseif(isset($mods)){
	$output = $twig->render('/productSearch.html', array(
		'authors' => $authors,
		'categories'=>$categories,
		'mods'=>$mods,
		));
}elseif(isset($finalOutput)){
	if($finalOutput=='order'){
		$output = $twig->render('/orderSearch.html');
	}elseif($finalOutput=='product'){
		$output = $twig->render('/productSearch.html', array(
			'authors' => $authors,
			'categories'=>$categories,
			));
	} 
}
try{
	echo $output;
}catch (PDOException $e){
	$error='Nie udało się wyświetlić listy wyszukiwania zamówień: ' . $e->getMessage();
}
