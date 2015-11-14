<?php

class OgicomProduct extends Product
{
	protected function getWhereProductSubquery() {
		return " WHERE ps_product_lang.id_lang=3 AND ps_product_lang.id_product= :id_number";
	}

	protected function getWhereCategorySubquery() {
		return " WHERE ps_category_lang.id_lang=3 AND ps_category_product.id_product= :id";
	}

	protected function getWhereEveryCategorySubquery() {
		return " WHERE id_lang=3 AND id_category NOT IN (1,6)";
	}

	public function countReduction($price, $reduction) {
		$new0=number_format($price, 2,'.','');
		$new=floatval($reduction);
		$new2=number_format($new, 2,'.','');
		$oldQueryResult1=$new0-$new;
		$oldQueryResult2=number_format($oldQueryResult1, 2,'.','');
		return$oldQueryResult2.'zł. W tym rabat: '.$new2.'zł.';
	}
	public function countRealPrice($price, $reduction) {
		$new0=number_format($price, 2,'.','');
		$new=floatval($reduction);
		$new2=number_format($new, 2,'.','');
		$oldQueryResult1=$new0-$new;
		$oldQueryResult2=number_format($oldQueryResult1, 2,'.','');
		$result=array('reduction'=>'Rabat: '.$new2.'zł', 'realPrice'=>'Cena rzeczywista: '.$oldQueryResult2.'zł');
		return$result;
	}

	public function getEveryName(){
		$sql='SELECT name, id_product FROM ps_product_lang
		WHERE id_lang=3 ORDER BY name';
		$c= $this->pdo->prepare($sql);
		$c->execute();
		foreach ($c as $result){
			$results[]=array('name'=>$result['name'], 'id'=>$result['id_product']);
		}
		return $results;
	}

	protected function getTypedProductSubquery(){
		$sql="SELECT ps_product_lang.name, ps_product_lang.id_product, ps_product_lang.link_rewrite FROM ps_product_lang
		INNER JOIN ps_product ON ps_product_lang.id_product=ps_product.id_product
		INNER JOIN ps_category_product ON ps_product_lang.id_product=ps_category_product.id_product";
		return $sql;
	}

	public function getTypedProductsQuery($where) {
		$sql=$this->getTypedProductSubquery().$where;
		$r= $this->pdo->prepare($sql);
		$r->execute();
		return $r;
	}
}
