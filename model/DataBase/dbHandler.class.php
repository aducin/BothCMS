<?php

class DBHandler{

	private $pdo;
	
	function __construct($host, $login, $password){
		try
		{
		$this->pdo=new PDO($host, $login, $password);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('SET NAMES "utf8"');
		}
		catch(PDOException $e)
   		{
      		$error='Połączenie z bazą danych nie mogło zostać utworzone: ' . $e->getMessage();
   		}
	}

	public function getDb() {
    	if ($this->pdo instanceof PDO) {
    		return $this->pdo;
        }
	}

	public function __destruct()
    {
        if(is_resource($this->pdo)) {
            $this->pdo -> closeCursor();
        }
    }

    function getUserData($login, $password){
		$sql='SELECT * FROM ps_db_user WHERE login=:login AND password=:password';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':login', $login);
		$s->bindValue(':password', $password);
		$s->execute();
		return $s;
	}
	 function getUserLogin($login){
		$sql='SELECT login FROM ps_db_user WHERE login=:login';
		$s=$this->pdo->prepare($sql);
		$s->bindValue(':login', $login);
		$s->execute();
		return $s;
	}
}