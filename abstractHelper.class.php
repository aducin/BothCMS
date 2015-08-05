<?php

abstract class Helper{
	
	function __construct($host, $login, $password){
		$this->pdo=new PDO($host, $login, $password);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('SET NAMES "utf8"');}

		public function selectWholeManufacturer(){
			$sql='SELECT id_manufacturer, name
			FROM ps_manufacturer';
			$s=$this->pdo->prepare($sql);
			$s->execute();
			return $s;
		}

		private function getCategorySelectSubquery(){
			return 'SELECT id_category, meta_title FROM ps_category_lang';
		}
		
		abstract protected function getWhereSubquery();

		public function getCategoryData() {
			$sql=$this->getCategorySelectSubquery() . $this->getWhereSubquery();
			$c=$this->pdo->prepare($sql);
			$c->execute();
			return $c;
		}
	}
