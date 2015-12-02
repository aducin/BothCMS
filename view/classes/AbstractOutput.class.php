<?php

abstract class OutputController{

	protected $twig;

	public function __construct(){
		$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
		$vendor_dir = $root_dir.'/vendor';
		$cache_dir = $root_dir.'/cache';
		$templates_dir = $root_dir.'/view/templates';
		$twig_lib = $vendor_dir.'/Twig/lib/Twig';
		require_once $twig_lib . '/Autoloader.php';
		Twig_Autoloader::register();
		$loader = new Twig_Loader_Filesystem($templates_dir);
		$this->twig = new Twig_Environment($loader, array(
			'cache' => $cache_dir,
		));
	}

	public function renderSingleUpdate( $updateDetails ){
		$conf=array(
			'Wykonanie aktualizacji produktu ID '.$updateDetails['first']['id_product'], 
			'Obecna ilość produktu w edytowanej bazie wynosi: '.$updateDetails['first']['quantity'],
			'controller' => 'order',
		);
		if(isset($updateDetails['second'])){
			array_push($conf, 'Obecna ilość produktu w drugiej bazie wynosi: '.$updateDetails['second']['quantity']);
		}
		$output = $this->twig->render('/confirm.html', array(
			'title' => 'Potwierdzenie wykonania operacji',
			'result' => 'Operacja zakończyła się powodzeniem!',
			'message' => $conf,
			'controller' => 'order',
		));
		echo $output;
	}
}