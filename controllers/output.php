<?php

// the file has to replaced by Output.class.php - asap

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

if(isset($error)AND(isset($authors))){
		$output = $twig->render('/productSearch.html', array(
			'authors' => $helper[0],
			'categories'=>$helper[1],
			'title' => 'Niepowodzenie wykonania operacji',
			'result' => 'UWAGA! Operacja zakończona niepowodzeniem!',
			'message' => $error,
			));
}elseif(isset($outputOrderMail)){
	$output = $twig->render('/messageConfirmation.html', array(
		'result' => 'Poniżej znajduje się treść wysłana do Klienta:',
		'message' => $message,
		'confirmation' => $outputOrderMail,
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
		'authors'=> $categoryAndAuthorList[1],
		'completeTagNames'=>$completeTagNames,
		'completeCatNames'=>$categoryAndAuthorList[0],
		'selCategories'=>$selCategories,
		'indexArray'=>$indexArray,
		'condArray'=>$condArray,
		));
}elseif(isset($helper[2])){
	$output = $twig->render('/productSearch.html', array(
		'authors' => $helper[0],
		'categories'=>$helper[1],
		'mods'=>$helper[2],
		));
}elseif(isset($helper)){
		$output = $twig->render('/productSearch.html', array(
			'authors' => $helper[0],
			'categories'=>$helper[1],
			));
}
try{
	echo $output;
}catch (PDOException $e){
	$error='Nie udało się wyświetlić listy wyszukiwania zamówień: ' . $e->getMessage();
}
