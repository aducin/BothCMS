<?php

abstract class Helper{
	
	function __construct($pdo){
		$this->pdo=$pdo;}

		function selectWholeManufacturer(){
			$sql='SELECT id_manufacturer, name
			FROM ps_manufacturer';
			$s=$this->pdo->prepare($sql);
			$s->execute();
			return $s;
		}

		private function selectWholeCategory1(){
			return 'SELECT id_category, meta_title FROM ps_category_lang';
		}
		
		abstract protected function selectWhere();

		public function selectWholeCategory2() {
			$sql=$this->selectWholeCategory1() . $this->selectWhere();
			$c=$this->pdo->prepare($sql);
			$c->execute();
			return $c;
		}
	}
