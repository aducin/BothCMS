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
$firstHost=$DBHandler["firstDB"]["host"];
$firstLogin=$DBHandler["firstDB"]["login"];
$firstPassword=$DBHandler["firstDB"]["password"];
$secondHost=$DBHandler["secondDB"]["host"];
$secondLogin=$DBHandler["secondDB"]["login"];
$secondPassword=$DBHandler["secondDB"]["password"];

require_once $root_dir.'/config/bootstrap.php';

ob_start();
// http://localhost/Ad9bisCMS/
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'product';

$dbHandlerOgicom= new DBHandler($secondHost, $secondLogin, $secondPassword);
$ogicomHandler = $dbHandlerOgicom->getDb();
$dbHandlerLinuxPl= new DBHandler($firstHost, $firstLogin, $firstPassword);
$linuxPlHandler = $dbHandlerLinuxPl->getDb();

//$linuxPlDbHandler=bothDbHandler::getInstance('linuxPl', $firstHost, $firstLogin, $firstPassword);
//$ogicomDbHandler=bothDbHandler::getInstance('ogicom', $secondHost, $secondLogin, $secondPassword);

require_once $root_dir.'/controllers/login.php';

if ($controller == 'product') {
	// http://localhost/Ad9bisCMS/?controller=product
	require_once $root_dir.'/controllers/productSearch.php';
} elseif ($controller == 'order'){
	// http://localhost/Ad9bisCMS/?controller=order
	require_once $root_dir.'/controllers/orderSearch.php';
} else {
	throw new Exception("Invalid controller name");
}
ob_end_flush();
