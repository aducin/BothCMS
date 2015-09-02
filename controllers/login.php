<?php

session_start();
if(isset($_POST['logout'])){
	unset($_SESSION['log']);
	header('Location:templates/signIn.html');
}
if(!isset($_SESSION['log'])){
	$userLogin=$_POST['login'];
	$userPassword=$_POST['password'];
	$dbHandlerLinuxPl= new DBHandler($firstHost, $firstLogin, $firstPassword);
	$dbResult= $dbHandlerLinuxPl->getUserData($userLogin, $userPassword);
	$resNumb=$dbResult->rowCount();

	if($resNumb>0){
		$finalResult=$dbResult->fetch(PDO::FETCH_ASSOC);
		$_SESSION['log']=1;
		$dbResult->closeCursor();
	}
}
if($_SESSION['log']==0){
	header('Location:templates/signIn.html');
}
unset($dbHandlerLinuxPl);