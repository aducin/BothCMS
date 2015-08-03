<?php

abstract class Lists{
	
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

class OldLists extends Lists{

	protected function selectWhere() {
	return " WHERE id_lang=3 AND id_category NOT IN (1,6)"; }

	function selectModyfy(){
	$sql='SELECT ps_modyfy.id_number, ps_product_lang.name, ps_modyfy.date, ps_product.price
	FROM ps_modyfy INNER JOIN ps_product_lang ON ps_modyfy.id_number=ps_product_lang.id_product
	INNER JOIN ps_product ON ps_modyfy.id_number=ps_product.id_product
	WHERE ps_product_lang.id_lang=3
	ORDER BY id_number';
	$s=$this->pdo->prepare($sql);
	$s->execute();
	return $s;
}

	function deleteMod($id){
	$sql='DELETE FROM ps_modyfy
	WHERE id_number = :id';
	$s=$this->pdo->prepare($sql);
	$s->bindValue(':id', $id);
	$s->execute();
}

	function __destruct(){
	}

}
class NewLists extends Lists{

	protected function selectWhere() {
		return " WHERE id_category NOT IN (1,2,10,11,12,14, 15, 19, 20, 21,22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 38, 39, 40, 42,43,46,47,52,53)";
	}

	function __destruct(){
	}
}
