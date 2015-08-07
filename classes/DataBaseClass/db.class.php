<?php

class db{

	function __construct($host, $login, $password){
		$this->pdo=new PDO($host, $login, $password);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('SET NAMES "utf8"');}
	
	function getUserData($login, $password){
	$sql='SELECT * FROM ps_db_user WHERE login=:login AND password=:password';
	$s=$this->pdo->prepare($sql);
	$s->bindValue(':login', $login);
	$s->bindValue(':password', $password);
			$s->execute();
			return $s;
	}
}
