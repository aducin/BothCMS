<?php

class bothDbHandler{

	private $pdo;
	private static $instance=array('linuxPl'=>'', 'ogicom'=>'');
	
	private function __construct(){}

	private function __clone(){}

	public static function getInstance($baseName, $host, $login, $password){
		if($baseName=='linuxPl'){
			if (self::$instance['linuxPl'] === '') {
				self::$instance['linuxPl'] = new PDO($host, $login, $password);
				self::$instance['linuxPl']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$instance['linuxPl']->exec('SET NAMES "utf8"');
			}
		}elseif($baseName=='ogicom'){
			if (self::$instance['ogicom'] === '') {
				self::$instance['ogicom'] = new PDO($host, $login, $password);
				self::$instance['ogicom']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$instance['ogicom']->exec('SET NAMES "utf8"');
			}
		}
        return self::$instance[$baseName];
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
}