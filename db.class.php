<?php

class db{

	function __construct($pdo){
		$this->pdo=$pdo;}
	
	function selectUser($login, $password){
	$sql='SELECT * FROM ps_db_user WHERE login=:login AND password=:password';
	$s=$this->pdo->prepare($sql);
	$s->bindValue(':login', $login);
	$s->bindValue(':password', $password);
			$s->execute();
			return $s;
	}
}
