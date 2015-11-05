<?php
header( 'Content-Type: text/html; charset=utf-8' );
$root_dir = $_SERVER['DOCUMENT_ROOT'].'/Ad9bisCMS';
$DBHandler=parse_ini_file($root_dir.'/config/database.ini',true);
$firstHost=$DBHandler["firstDB"]["host"];
$firstLogin=$DBHandler["firstDB"]["login"];
$firstPassword=$DBHandler["firstDB"]["password"];
$secondHost=$DBHandler["secondDB"]["host"];
$secondLogin=$DBHandler["secondDB"]["login"];
$secondPassword=$DBHandler["secondDB"]["password"];
require_once $root_dir.'/config/bootstrap.php';
$dbHandlerOgicom= new DBHandler($secondHost, $secondLogin, $secondPassword);
$ogicomHandler = $dbHandlerOgicom->getDb();
$dbHandlerLinuxPl= new DBHandler($firstHost, $firstLogin, $firstPassword);
$linuxPlHandler = $dbHandlerLinuxPl->getDb();
$product1= new LinuxPlProduct($linuxPlHandler);
$product2= new OgicomProduct($ogicomHandler);
if(isset($_GET['query'])){
	if(preg_match('/^[a-zA-z0-9]$/D',$_GET['query'])){
		$data='Zbyt krótka fraza do wyszukania!';
	}else{
		$names=$product2->getTypedName($_GET['query']);
		$total = $names->rowCount();
		if($total>0){
			$data= '<ul class="autoSuggest">';
			foreach ($names as $result){
				$results[]=array('name'=>$result['name'], 'id'=>$result['id_product']);
				$data.= '<li><a href="http://modele-ad9bis.pl/akcerosia-tt/'.$result['id_product'].'-'.$result['link_rewrite'].'.html" target="_blank">'.$result['name'].' - ID: <b>'.$result['id_product'].'</b></a></li>';
			}
			$data.= '</ul>';
		}else{
			$data = 'Nie znaleziono żadnego produktu z nazwą: <b>'.$_GET['query'].'</b>!';
		}
	}
}

if(isset($_GET['productQuery'])){
	if(preg_match('/^[a-zA-z0-9]$/D',$_GET['productQuery'])){
		$data='tooShort';
	}else{
		$names=$product2->getTypedName($_GET['productQuery']);
		$total = $names->rowCount();
		if($total>0){
			foreach ($names as $result){
				$data[]=array('name'=>$result['name']);
				$jsonData[]=json_encode(array('namenew'=>$result['name'],'idnew'=>$result['id_product']));
			}
		}else{
			$data = 'null';
		}
	}
}
echo json_encode($data);
?>