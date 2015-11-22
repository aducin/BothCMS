<?php

session_start();
if(isset($_GET['logout'])){
	unset($_SESSION['log']);
	header('Location:/Ad9bisCMS/templates/signIn.html');
	exit();
}
if(!isset($_SESSION['log'])){
	if(isset($_POST['login'])AND isset($_POST['password'])){
		$userLogin=trim(htmlspecialchars($_POST['login']));
		$userPassword=trim(htmlspecialchars($_POST['password']));
		$dbHandlerLinuxPl= new DBHandler($firstHost, $firstLogin, $firstPassword);
		$dbResult= $dbHandlerLinuxPl->getUserData($userLogin, $userPassword);
		$resNumb=$dbResult->rowCount();
		if($resNumb>0){
			$finalResult=$dbResult->fetch(PDO::FETCH_ASSOC);
			$_SESSION['log']=1;
			$dbResult->closeCursor();
		}else{
			header('Location:templates/signin2.html');
		}
	}else{
		$userLogin=$_POST['login'];
		$userPassword=$_POST['password'];
		$dbHandlerLinuxPl= new DBHandler($firstHost, $firstLogin, $firstPassword);
		$dbResult= $dbHandlerLinuxPl->getUserData($userLogin, $userPassword);
		$resNumb=$dbResult->rowCount();
		if($resNumb>0){
			$finalResult=$dbResult->fetch(PDO::FETCH_ASSOC);
			$_SESSION['log']=1;
			$dbResult->closeCursor();
		}else{
			header('Location:templates/signIn.html');
		}
	}
}

unset($dbHandlerLinuxPl);