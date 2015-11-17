<?php

class OutputController{

	private $twig;

	public function __construct(){
		$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
		$vendor_dir = $root_dir.'/vendor';
		$cache_dir = $root_dir.'/cache';
		$templates_dir = $root_dir.'/templates';
		$twig_lib = $vendor_dir.'/Twig/lib/Twig';
		require_once $twig_lib . '/Autoloader.php';
		Twig_Autoloader::register();
		$loader = new Twig_Loader_Filesystem($templates_dir);
		$this->twig = new Twig_Environment($loader, array(
			'cache' => $cache_dir,
		));
	}

	public function renderView($path, $firstParam = null, $secondParam = null){
		if($firstParam === null){
			$output = $this->twig->render('/'.$path.'.html');
		}elseif($secondParam===null){
			$output = $this->twig->render('/'.$path.'.html', array(
				'result' => $firstParam,
			));
		}else{
			$output = $this->twig->render('/'.$path.'.html', array(
				'result' => $firstParam,
				'customerData'=>$secondParam,
			));	
		}
		echo $output;
	}
}