<?php

$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
require_once $root_dir.'/config/dbHandlers.php';

ob_start();
// http://localhost/Ad9bisCMS/
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'product';

require_once $root_dir.'/controllers/login.php';

if ($controller == 'product') {
	$controller=new ProductsController($linuxPlHandler, $ogicomHandler);
} elseif ($controller == 'order'){
	$order=new OrdersController($linuxPlHandler, $ogicomHandler);
} else {
	throw new Exception("Invalid controller name");
}
ob_end_flush();
