<?php
$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
$vendor_dir = $root_dir.'/vendor';

$twig_lib = $vendor_dir.'/Twig/lib/Twig';
$twig_templates = $root_dir.'/templates';
$twig_cache = $root_dir.'/cache'; // remember to `chmod 777 cache` (make this directory writable)
require_once $twig_lib . '/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem($twig_templates);
$twig = new Twig_Environment($loader, array(
	'cache' => $twig_cache,
));

require_once $root_dir.'/config/bootstrap.php';
require_once $root_dir.'/controllers/productSearch.php';
require_once $root_dir.'/controllers/orderSearch.php';
