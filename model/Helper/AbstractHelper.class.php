<?php

abstract class Helper{
	
	public function __construct($DBHandler){
		$this->pdo=$DBHandler;
	}

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
